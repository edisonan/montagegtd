<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleAiProfileRepository;
use App\Repositories\ArticleAiTaskRepository;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Log;

class ArticleAiService
{
    protected $articleRepository;
    protected $taskRepository;
    protected $profileRepository;
    protected $classificationService;

    public function __construct(
        ArticleRepository $articleRepository,
        ArticleAiTaskRepository $taskRepository,
        ArticleAiProfileRepository $profileRepository,
        ArticleAiClassificationService $classificationService
    ) {
        $this->articleRepository = $articleRepository;
        $this->taskRepository = $taskRepository;
        $this->profileRepository = $profileRepository;
        $this->classificationService = $classificationService;
    }

    public function ensurePendingTaskForArticle($articleId, array $extra = array())
    {
        $existingTasks = $this->taskRepository->findByArticleId($articleId);
        foreach ($existingTasks as $task) {
            if (in_array($task->status, array('pending', 'processing'), true)) {
                return $task;
            }
        }

        return $this->taskRepository->create(array_merge(array(
            'article_id' => $articleId,
            'status' => 'pending',
            'retry_count' => 0,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'prompt_version' => 'article_classification:v2',
        ), $extra));
    }

    public function backfillPendingTasks($limit = 100)
    {
        $created = 0;
        $articles = Article::whereDoesntHave('aiProfile')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        foreach ($articles as $article) {
            $task = $this->ensurePendingTaskForArticle($article->id);
            if ($task) {
                $created++;
            }
        }

        return $created;
    }

    public function processPendingTasks($limit = 20)
    {
        $batchSize = min(max(1, (int)$limit), $this->classificationService->getRecommendedBatchSize());
        $tasks = $this->taskRepository->findPendingTasks($batchSize);

        return $this->processTaskBatch($tasks);
    }

    protected function processTaskBatch($tasks)
    {
        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $skipped = 0;
        $throttled = 0;
        $validTasks = array();
        $articles = array();

        foreach ($tasks as $task) {
            $processed++;

            $article = $task->article instanceof Article ? $task->article : null;
            if (!$article) {
                $this->taskRepository->update($task->id, array(
                    'status' => 'failed',
                    'error_message' => 'article not found',
                    'finished_at' => date('Y-m-d H:i:s'),
                ));
                $failed++;
                continue;
            }

            $this->markTaskProcessing($task);
            $validTasks[] = $task;
            $articles[] = $article;
        }

        try {
            $classifications = $this->classificationService->classifyBatch($articles);
        } catch (\Throwable $e) {
            foreach ($validTasks as $task) {
                $this->handleTaskException($task, $e);
                $failed++;
            }

            return array(
                'processed' => $processed,
                'succeeded' => $succeeded,
                'failed' => $failed,
                'skipped' => $skipped,
                'throttled' => $throttled,
                'batch_size' => count($articles),
            );
        }

        foreach ($validTasks as $task) {
            $article = $task->article;
            $articleId = (string)$article->id;
            $classification = $classifications[$articleId] ?? array(
                'success' => false,
                'error' => 'classification result missing',
                'result' => null,
                'meta' => array('status' => 'failed'),
            );

            try {
                $result = $this->completeTaskWithClassification($task, $article, $classification);
            } catch (\Throwable $e) {
                $result = $this->handleTaskException($task, $e);
            }

            if ($result['status'] === 'success') {
                $succeeded++;
            } elseif ($result['status'] === 'skipped') {
                $skipped++;
            } elseif ($result['status'] === 'throttled') {
                $throttled++;
            } else {
                $failed++;
            }
        }

        return array(
            'processed' => $processed,
            'succeeded' => $succeeded,
            'failed' => $failed,
            'skipped' => $skipped,
            'throttled' => $throttled,
            'batch_size' => count($articles),
        );
    }

    public function processTask($taskId)
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return array('status' => 'failed', 'message' => 'task not found');
        }

        $article = $task->article instanceof Article ? $task->article : null;
        if (!$article) {
            $this->taskRepository->update($task->id, array(
                'status' => 'failed',
                'error_message' => 'article not found',
                'finished_at' => date('Y-m-d H:i:s'),
            ));

            return array('status' => 'failed', 'message' => 'article not found');
        }

        $this->markTaskProcessing($task);

        try {
            $classification = $this->classificationService->classify($article);
            return $this->completeTaskWithClassification($task, $article, $classification);
        } catch (\Throwable $e) {
            return $this->handleTaskException($task, $e);
        }
    }

    protected function markTaskProcessing($task)
    {
        $this->taskRepository->update($task->id, array(
            'status' => 'processing',
            'started_at' => date('Y-m-d H:i:s'),
            'error_message' => null,
        ));
    }

    protected function completeTaskWithClassification($task, Article $article, array $classification)
    {
        if (!$classification['success'] || empty($classification['result'])) {
            $status = $classification['meta']['status'] ?? 'failed';

            if ($status === 'throttled') {
                $this->taskRepository->update($task->id, array(
                    'status' => 'pending',
                    'scheduled_at' => date('Y-m-d H:i:s', time() + 300),
                    'started_at' => null,
                    'error_message' => $classification['error'] ?? 'classification throttled',
                ));

                return array('status' => 'throttled', 'message' => $classification['error'] ?? 'classification throttled');
            }

            $this->taskRepository->update($task->id, array(
                'status' => $status,
                'retry_count' => $task->retry_count + 1,
                'error_message' => $classification['error'] ?? 'classification failed',
                'finished_at' => date('Y-m-d H:i:s'),
            ));

            return array('status' => $status, 'message' => $classification['error'] ?? 'classification failed');
        }

        $profileData = $classification['result'];
        $profileData['article_id'] = $article->id;
        $profileData['status'] = 'success';
        $profileData['model_name'] = $classification['meta']['model_name'] ?? null;
        $profileData['prompt_version'] = $classification['meta']['prompt_version'] ?? 'article_classification:v2';
        $profileData['analyzed_at'] = date('Y-m-d H:i:s');

        $this->profileRepository->updateOrCreateByArticleId($article->id, $profileData);

        $this->taskRepository->update($task->id, array(
            'status' => 'success',
            'model_name' => $classification['meta']['model_name'] ?? null,
            'prompt_version' => $classification['meta']['prompt_version'] ?? 'article_classification:v2',
            'finished_at' => date('Y-m-d H:i:s'),
            'error_message' => $classification['error'] ?? null,
        ));

        return array('status' => 'success', 'message' => null);
    }

    protected function handleTaskException($task, \Throwable $e)
    {
        Log::error('article ai classification failed', array(
            'task_id' => $task->id,
            'article_id' => $task->article_id,
            'error' => $e->getMessage(),
        ));

        $retryCount = (int)$task->retry_count + 1;
        $nextStatus = $retryCount >= 3 ? 'failed' : 'pending';

        $this->taskRepository->update($task->id, array(
            'status' => $nextStatus,
            'retry_count' => $retryCount,
            'error_message' => mb_substr($e->getMessage(), 0, 255),
            'finished_at' => date('Y-m-d H:i:s'),
        ));

        return array('status' => $nextStatus, 'message' => $e->getMessage());
    }
}
