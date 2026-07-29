<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Services\ArticleAiClassificationService;
use App\Services\LlmStructuredTaskService;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArticleAiClassificationServiceTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_batch_size_uses_max_tokens_and_defaults_to_ten()
    {
        $service = new LlmStructuredTaskService();

        $this->assertSame(10, $service->calculateBatchSize(null));
        $this->assertSame(5, $service->calculateBatchSize(1024));
        $this->assertSame(1, $service->calculateBatchSize(100));
        $this->assertSame(50, $service->calculateBatchSize(20000));
    }

    public function test_classify_batch_sends_multiple_titles_in_one_request()
    {
        $llmService = Mockery::mock(LlmStructuredTaskService::class);
        $llmService->shouldReceive('runTask')
            ->once()
            ->withArgs(function ($taskType, $messages, $options) {
                $this->assertSame('article_classification', $taskType);
                $this->assertNotFalse(strpos($messages[1]['content'], 'PHP 批处理实践'));
                $this->assertNotFalse(strpos($messages[1]['content'], 'Vue 组件设计'));
                $this->assertSame('json_object', $options['response_format']['type']);
                return true;
            })
            ->andReturn(array(
                'success' => true,
                'content' => json_encode(array(
                    'articles' => array(
                        array(
                            'article_id' => 101,
                            'primary_category' => '后端',
                            'secondary_category' => 'PHP',
                            'tags' => array('PHP'),
                            'keywords' => array('批处理'),
                            'content_type' => '教程',
                            'quality_score' => 80,
                        ),
                        array(
                            'article_id' => 102,
                            'primary_category' => '前端',
                            'secondary_category' => 'Vue',
                            'tags' => array('Vue'),
                            'keywords' => array('组件'),
                            'content_type' => '教程',
                            'quality_score' => 85,
                        ),
                    ),
                ), JSON_UNESCAPED_UNICODE),
                'error' => null,
                'meta' => array('model_name' => 'test-model'),
            ));

        $first = new Article();
        $first->setAttribute('id', 101);
        $first->setAttribute('subject', 'PHP 批处理实践');

        $second = new Article();
        $second->setAttribute('id', 102);
        $second->setAttribute('subject', 'Vue 组件设计');

        $service = new ArticleAiClassificationService($llmService);
        $results = $service->classifyBatch(array($first, $second));

        $this->assertCount(2, $results);
        $this->assertSame('后端', $results[101]['result']['primary_category']);
        $this->assertSame('前端', $results[102]['result']['primary_category']);
        $this->assertSame('article_classification:v2', $results[101]['meta']['prompt_version']);
    }
}
