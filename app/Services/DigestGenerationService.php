<?php

namespace App\Services;

use App\Repositories\ArticleAiProfileRepository;
use App\Repositories\DigestPageRepository;
use App\Repositories\DigestTaskRepository;
use App\Repositories\UserDigestProfileRepository;
use Illuminate\Support\Collection;

class DigestGenerationService
{
    protected $profileRepository;
    protected $taskRepository;
    protected $pageRepository;
    protected $articleAiProfileRepository;
    protected $llmStructuredTaskService;
    protected $whitelistService;

    public function __construct(
        UserDigestProfileRepository $profileRepository,
        DigestTaskRepository $taskRepository,
        DigestPageRepository $pageRepository,
        ArticleAiProfileRepository $articleAiProfileRepository,
        LlmStructuredTaskService $llmStructuredTaskService,
        DigestWhitelistService $whitelistService
    ) {
        $this->profileRepository = $profileRepository;
        $this->taskRepository = $taskRepository;
        $this->pageRepository = $pageRepository;
        $this->articleAiProfileRepository = $articleAiProfileRepository;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
        $this->whitelistService = $whitelistService;
    }

    public function createManualTaskForUser($userId)
    {
        $this->whitelistService->isUserEnabled($userId) || abort(403, 'digest not enabled');
        $profile = $this->profileRepository->findEnabledByUserId($userId);
        if (!$profile) {
            return null;
        }

        $openTask = $this->taskRepository->findOpenTaskByProfileId($profile->id);
        if ($openTask) {
            return $openTask;
        }

        return $this->taskRepository->create(array(
            'user_id' => $userId,
            'profile_id' => $profile->id,
            'status' => 'pending',
            'scheduled_at' => date('Y-m-d H:i:s'),
            'prompt_version' => 'digest_generation:v1',
        ));
    }

    public function enqueueDueTasks($limitPerFrequency = 50)
    {
        $created = 0;
        $dailyBefore = date('Y-m-d H:i:s', strtotime('-1 day'));
        $weeklyBefore = date('Y-m-d H:i:s', strtotime('-7 day'));

        foreach ($this->profileRepository->findDueProfiles('daily', $dailyBefore, $limitPerFrequency) as $profile) {
            $created += $this->enqueueProfileTask($profile) ? 1 : 0;
        }

        foreach ($this->profileRepository->findDueProfiles('weekly', $weeklyBefore, $limitPerFrequency) as $profile) {
            $created += $this->enqueueProfileTask($profile) ? 1 : 0;
        }

        return $created;
    }

    public function processPendingTasks($limit = 10)
    {
        $tasks = $this->taskRepository->findPendingTasks($limit);
        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($tasks as $task) {
            $processed++;
            $result = $this->processTask($task->id);

            if ($result['status'] === 'success') {
                $succeeded++;
            } elseif ($result['status'] === 'skipped') {
                $skipped++;
            } else {
                $failed++;
            }
        }

        return array(
            'processed' => $processed,
            'succeeded' => $succeeded,
            'failed' => $failed,
            'skipped' => $skipped,
        );
    }

    public function processTask($taskId)
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task || !$task->profile) {
            return array('status' => 'failed', 'message' => 'task/profile not found');
        }

        $profile = $task->profile;
        $this->taskRepository->update($task->id, array(
            'status' => 'processing',
            'started_at' => date('Y-m-d H:i:s'),
            'error_message' => null,
        ));

        $candidates = $this->getCandidatesForProfile($profile);
        if ($candidates->isEmpty()) {
            $this->taskRepository->update($task->id, array(
                'status' => 'skipped',
                'finished_at' => date('Y-m-d H:i:s'),
                'error_message' => 'no matched articles',
            ));

            return array('status' => 'skipped', 'message' => 'no matched articles');
        }

        $generated = $this->generateDigestContent($profile, $candidates);

        $page = $this->pageRepository->create(array(
            'user_id' => $task->user_id,
            'profile_id' => $profile->id,
            'task_id' => $task->id,
            'title' => $generated['title'],
            'cover_time_start' => date('Y-m-d H:i:s', strtotime('-' . max(1, (int)$profile->time_window_days) . ' day')),
            'cover_time_end' => date('Y-m-d H:i:s'),
            'intro' => $generated['intro'],
            'content_markdown' => $generated['content_markdown'],
            'source_article_ids_json' => $candidates->pluck('article_id')->values()->all(),
            'status' => 'success',
            'generated_at' => date('Y-m-d H:i:s'),
            'model_name' => $generated['model_name'],
            'prompt_version' => 'digest_generation:v1',
        ));

        $this->profileRepository->update($profile->id, array(
            'last_generated_at' => date('Y-m-d H:i:s'),
        ));

        $this->taskRepository->update($task->id, array(
            'status' => 'success',
            'finished_at' => date('Y-m-d H:i:s'),
            'model_name' => $generated['model_name'],
            'prompt_version' => 'digest_generation:v1',
        ));

        return array('status' => 'success', 'page_id' => $page->id);
    }

    protected function enqueueProfileTask($profile)
    {
        if (!$this->whitelistService->isUserEnabled($profile->user_id)) {
            return false;
        }

        $openTask = $this->taskRepository->findOpenTaskByProfileId($profile->id);
        if ($openTask) {
            return false;
        }

        $this->taskRepository->create(array(
            'user_id' => $profile->user_id,
            'profile_id' => $profile->id,
            'status' => 'pending',
            'scheduled_at' => date('Y-m-d H:i:s'),
            'prompt_version' => 'digest_generation:v1',
        ));

        return true;
    }

    protected function getCandidatesForProfile($profile)
    {
        $profiles = $this->articleAiProfileRepository->getRecentProfilesForDigest(
            max(1, (int)$profile->time_window_days),
            max(5, (int)$profile->max_articles) * 5
        );

        if ($profiles->isEmpty()) {
            $profiles = $this->articleAiProfileRepository->getLatestProfiles(
                max(5, (int)$profile->max_articles) * 5
            );
        }

        $topics = array_map('mb_strtolower', (array)$profile->topics_json);
        $includeKeywords = array_map('mb_strtolower', (array)$profile->include_keywords_json);
        $excludeKeywords = array_map('mb_strtolower', (array)$profile->exclude_keywords_json);
        $preferredCategories = (array)$profile->preferred_categories_json;
        $maxArticles = max(5, (int)$profile->max_articles);

        $filtered = $profiles->filter(function ($item) use ($topics, $includeKeywords, $excludeKeywords, $preferredCategories) {
            $subject = mb_strtolower((string)optional($item->article)->subject);
            $summary = mb_strtolower((string)$item->summary);
            $text = $subject . ' ' . $summary . ' ' . mb_strtolower(implode(' ', (array)$item->tags_json));

            foreach ($excludeKeywords as $keyword) {
                if ($keyword !== '' && mb_strpos($text, $keyword) !== false) {
                    return false;
                }
            }

            if (!empty($preferredCategories) && !in_array($item->primary_category, $preferredCategories, true)) {
                $matchedByCategory = false;
            } else {
                $matchedByCategory = !empty($preferredCategories);
            }

            $matchedByTopic = empty($topics);
            foreach ($topics as $topic) {
                if ($topic !== '' && mb_strpos($text, $topic) !== false) {
                    $matchedByTopic = true;
                    break;
                }
            }

            $matchedByInclude = empty($includeKeywords);
            foreach ($includeKeywords as $keyword) {
                if ($keyword !== '' && mb_strpos($text, $keyword) !== false) {
                    $matchedByInclude = true;
                    break;
                }
            }

            return $matchedByCategory || $matchedByTopic || $matchedByInclude;
        })->sortByDesc(function ($item) {
            return array(
                (int)($item->quality_score ?? 0),
                strtotime((string)optional($item->article)->published),
            );
        })->take($maxArticles)->values();

        if ($filtered->isEmpty()) {
            return $profiles->sortByDesc(function ($item) {
                return array(
                    (int)($item->quality_score ?? 0),
                    strtotime((string)optional($item->article)->published),
                );
            })->take($maxArticles)->values();
        }

        return $filtered;
    }

    protected function generateDigestContent($profile, Collection $candidates)
    {
        $sourceLines = array();
        foreach ($candidates as $candidate) {
            $sourceLines[] = sprintf(
                "- [%s] 分类:%s 标签:%s 摘要:%s",
                (string)optional($candidate->article)->subject,
                (string)$candidate->primary_category,
                implode(',', (array)$candidate->tags_json),
                trim((string)$candidate->summary)
            );
        }

        $topics = implode('、', (array)$profile->topics_json);
        $windowDays = max(1, (int)$profile->time_window_days);
        $title = $topics !== '' ? '最近' . $windowDays . '天「' . $topics . '」内容汇总' : '最近' . $windowDays . '天内容汇总';

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是内容编辑助手。请根据候选文章生成一篇简洁的中文汇合页，输出 JSON，字段包含 title, intro, content_markdown。',
            ),
            array(
                'role' => 'user',
                'content' => "用户关注主题：" . $topics . "\n候选文章：\n" . implode("\n", $sourceLines),
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'digest_generation',
            $messages,
            array(
                'response_format' => array('type' => 'json_object'),
                'timeout' => 120,
            )
        );

        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $decoded = json_decode(trim((string)$llmResult['content']), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array(
                    'title' => $decoded['title'] ?? $title,
                    'intro' => $decoded['intro'] ?? '基于最近一段时间的相关文章自动生成。',
                    'content_markdown' => $decoded['content_markdown'] ?? $this->buildFallbackMarkdown($title, $candidates),
                    'model_name' => $llmResult['meta']['model_name'] ?? null,
                );
            }
        }

        return array(
            'title' => $title,
            'intro' => '基于最近一段时间的相关文章自动生成。',
            'content_markdown' => $this->buildFallbackMarkdown($title, $candidates),
            'model_name' => $llmResult['meta']['model_name'] ?? 'fallback-local',
        );
    }

    protected function buildFallbackMarkdown($title, Collection $candidates)
    {
        $lines = array('# ' . $title, '', '## 概览', '本页基于最近命中的文章自动汇总生成。', '');

        foreach ($candidates as $candidate) {
            $lines[] = '## ' . (string)optional($candidate->article)->subject;
            $lines[] = '- 分类：' . (string)$candidate->primary_category;
            $lines[] = '- 标签：' . implode('、', (array)$candidate->tags_json);
            $lines[] = '- 摘要：' . trim((string)$candidate->summary);
            if (!empty(optional($candidate->article)->url)) {
                $lines[] = '- 原文：' . (string)$candidate->article->url;
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
