<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Repositories\ArticleAiProfileRepository;
use App\Repositories\ArticleAiTaskRepository;
use App\Repositories\ArticleRepository;
use App\Services\ArticleAiClassificationService;
use App\Services\ArticleAiService;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArticleAiServiceTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_pending_tasks_are_classified_in_one_model_batch()
    {
        $articles = array(
            $this->makeArticle(201, 'Laravel 队列实践'),
            $this->makeArticle(202, 'React 状态管理'),
        );
        $tasks = array(
            $this->makeTask(301, $articles[0]),
            $this->makeTask(302, $articles[1]),
        );

        $articleRepository = Mockery::mock(ArticleRepository::class);
        $taskRepository = Mockery::mock(ArticleAiTaskRepository::class);
        $profileRepository = Mockery::mock(ArticleAiProfileRepository::class);
        $classificationService = Mockery::mock(ArticleAiClassificationService::class);

        $taskRepository->shouldReceive('findPendingTasks')->once()->with(5)->andReturn($tasks);
        $taskRepository->shouldReceive('update')->times(4)->andReturn(null);
        $profileRepository->shouldReceive('updateOrCreateByArticleId')->twice()->andReturn(null);
        $classificationService->shouldReceive('getRecommendedBatchSize')->once()->andReturn(5);
        $classificationService->shouldReceive('classifyBatch')
            ->once()
            ->with(Mockery::on(function ($batch) {
                return count($batch) === 2;
            }))
            ->andReturn(array(
                201 => $this->classificationResult('后端'),
                202 => $this->classificationResult('前端'),
            ));

        $service = new ArticleAiService(
            $articleRepository,
            $taskRepository,
            $profileRepository,
            $classificationService
        );
        $result = $service->processPendingTasks(20);

        $this->assertSame(2, $result['batch_size']);
        $this->assertSame(2, $result['processed']);
        $this->assertSame(2, $result['succeeded']);
        $this->assertSame(0, $result['failed']);
    }

    protected function makeArticle($id, $subject)
    {
        $article = new Article();
        $article->setAttribute('id', $id);
        $article->setAttribute('subject', $subject);
        return $article;
    }

    protected function makeTask($id, Article $article)
    {
        $task = new \stdClass();
        $task->id = $id;
        $task->article_id = $article->id;
        $task->article = $article;
        $task->retry_count = 0;
        return $task;
    }

    protected function classificationResult($category)
    {
        return array(
            'success' => true,
            'error' => null,
            'result' => array(
                'primary_category' => $category,
                'secondary_category' => null,
                'tags_json' => array(),
                'keywords_json' => array(),
                'summary' => null,
                'content_type' => '资讯',
                'audience' => null,
                'quality_score' => 80,
                'risk_flags_json' => array(),
            ),
            'meta' => array(
                'model_name' => 'test-model',
                'prompt_version' => 'article_classification:v2',
            ),
        );
    }
}
