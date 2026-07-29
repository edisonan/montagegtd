<?php

namespace App\Services;

use App\Models\Article;

class ArticleAiClassificationService
{
    const ESTIMATED_OUTPUT_TOKENS_PER_ARTICLE = 160;

    protected $llmStructuredTaskService;

    public function __construct(LlmStructuredTaskService $llmStructuredTaskService)
    {
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    public function classify(Article $article)
    {
        $results = $this->classifyBatch(array($article));

        return $results[$article->id] ?? $this->skippedResult('文章标题为空');
    }

    public function getRecommendedBatchSize()
    {
        return $this->llmStructuredTaskService->getRecommendedBatchSize(
            self::ESTIMATED_OUTPUT_TOKENS_PER_ARTICLE,
            LlmStructuredTaskService::DEFAULT_BATCH_SIZE
        );
    }

    public function classifyBatch(array $articles)
    {
        $results = array();
        $articleMap = array();
        $inputArticles = array();

        foreach ($articles as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $cleanText = $this->buildArticleText($article);
            if ($cleanText === '') {
                $results[$article->id] = $this->skippedResult('文章标题为空');
                continue;
            }

            $articleMap[(string)$article->id] = $article;
            $inputArticles[] = array(
                'article_id' => (int)$article->id,
                'title' => trim((string)$article->subject),
            );
        }

        if (empty($inputArticles)) {
            return $results;
        }

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个文章分类助手。仅根据文章标题逐篇分类。请严格输出 JSON 对象，不要输出 markdown 代码块。顶层字段为 articles，值为数组；数组每项必须原样返回 article_id，并包含 primary_category, secondary_category, tags, keywords, content_type, quality_score。不得遗漏任何输入文章。',
            ),
            array(
                'role' => 'user',
                'content' => "请对下面这些文章标题批量分类：\n" . json_encode($inputArticles, JSON_UNESCAPED_UNICODE),
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'article_classification',
            $messages,
            array(
                'response_format' => array('type' => 'json_object'),
                'timeout' => 120,
                'throttle_minutes' => 5,
            )
        );

        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $parsed = $this->parseStructuredJson($llmResult['content']);
            $parsedArticles = is_array($parsed) ? ($parsed['articles'] ?? array()) : array();

            foreach ($parsedArticles as $parsedArticle) {
                $articleId = (string)($parsedArticle['article_id'] ?? '');
                if ($articleId === '' || !isset($articleMap[$articleId])) {
                    continue;
                }

                $results[$articleId] = array(
                    'success' => true,
                    'error' => null,
                    'result' => $this->normalizeResult($parsedArticle),
                    'meta' => array(
                        'status' => 'success',
                        'model_name' => $llmResult['meta']['model_name'] ?? null,
                        'prompt_version' => 'article_classification:v2',
                    ),
                );
            }
        }

        foreach ($articleMap as $articleId => $article) {
            if (isset($results[$articleId])) {
                continue;
            }

            $results[$articleId] = array(
                'success' => true,
                'error' => $llmResult['error'] ?? '模型批量响应缺少该文章',
                'result' => $this->fallbackClassify($article, $this->buildArticleText($article)),
                'meta' => array(
                    'status' => 'success',
                    'model_name' => $llmResult['meta']['model_name'] ?? 'fallback-local',
                    'prompt_version' => 'article_classification:v2',
                    'fallback_used' => true,
                ),
            );
        }

        return $results;
    }

    protected function skippedResult($message)
    {
        return array(
            'success' => false,
            'error' => $message,
            'result' => null,
            'meta' => array(
                'status' => 'skipped',
                'model_name' => null,
                'prompt_version' => 'article_classification:v2',
            ),
        );
    }

    protected function buildArticleText(Article $article)
    {
        $subject = trim((string)$article->subject);
        return $subject !== '' ? '标题：' . $subject : '';
    }

    protected function parseStructuredJson($content)
    {
        $content = trim((string)$content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    protected function normalizeResult(array $result)
    {
        return array(
            'primary_category' => $result['primary_category'] ?? null,
            'secondary_category' => $result['secondary_category'] ?? null,
            'tags_json' => array_values(array_slice((array)($result['tags'] ?? array()), 0, 8)),
            'keywords_json' => array_values(array_slice((array)($result['keywords'] ?? array()), 0, 10)),
            'summary' => null,
            'content_type' => $result['content_type'] ?? null,
            'audience' => null,
            'quality_score' => $this->normalizeQualityScore($result['quality_score'] ?? null),
            'risk_flags_json' => array_values((array)($result['risk_flags'] ?? array())),
        );
    }

    protected function normalizeQualityScore($qualityScore)
    {
        if ($qualityScore === null || $qualityScore === '') {
            return null;
        }

        $score = (int)$qualityScore;
        if ($score < 0) {
            $score = 0;
        }
        if ($score > 100) {
            $score = 100;
        }

        return $score;
    }

    protected function fallbackClassify(Article $article, $text)
    {
        $haystack = mb_strtolower((string)$article->subject);

        $categoryMap = array(
            'AI' => array('ai', 'agent', 'gpt', 'llm', 'prompt', '大模型', '智能体'),
            '后端' => array('php', 'laravel', 'mysql', 'redis', 'api', 'backend', '后端'),
            '前端' => array('javascript', 'typescript', 'react', 'vue', 'css', '前端'),
            '产品' => array('product', '增长', '需求', '用户研究', '产品'),
        );

        $primaryCategory = '其他';
        foreach ($categoryMap as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($haystack, mb_strtolower($keyword)) !== false) {
                    $primaryCategory = $category;
                    break 2;
                }
            }
        }

        $tagPool = array();
        foreach ($categoryMap as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($haystack, mb_strtolower($keyword)) !== false) {
                    $tagPool[] = $keyword;
                }
            }
        }
        $tagPool = array_values(array_unique(array_slice($tagPool, 0, 8)));

        return array(
            'primary_category' => $primaryCategory,
            'secondary_category' => null,
            'tags_json' => $tagPool,
            'keywords_json' => $tagPool,
            'summary' => null,
            'content_type' => '资讯',
            'audience' => null,
            'quality_score' => 60,
            'risk_flags_json' => array(),
        );
    }
}
