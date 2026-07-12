<?php

namespace App\Services;

use App\Models\Article;

class ArticleAiClassificationService
{
    protected $llmStructuredTaskService;

    public function __construct(LlmStructuredTaskService $llmStructuredTaskService)
    {
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    public function classify(Article $article)
    {
        $cleanText = $this->buildArticleText($article);
        if ($cleanText === '') {
            return array(
                'success' => false,
                'error' => '文章标题为空',
                'result' => null,
                'meta' => array(
                    'status' => 'skipped',
                    'model_name' => null,
                    'prompt_version' => 'article_classification:v1',
                ),
            );
        }

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个文章分类助手。仅根据文章标题分类。请严格输出 JSON，不要输出 markdown 代码块。字段包含 primary_category, secondary_category, tags, keywords, content_type, quality_score。',
            ),
            array(
                'role' => 'user',
                'content' => "请根据下面的文章标题返回 JSON：\n" . $cleanText,
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
            if ($parsed !== null) {
                return array(
                    'success' => true,
                    'error' => null,
                    'result' => $this->normalizeResult($parsed),
                    'meta' => array(
                        'status' => 'success',
                        'model_name' => $llmResult['meta']['model_name'] ?? null,
                        'prompt_version' => 'article_classification:v1',
                    ),
                );
            }
        }

        $fallback = $this->fallbackClassify($article, $cleanText);

        return array(
            'success' => true,
            'error' => $llmResult['error'] ?? null,
            'result' => $fallback,
            'meta' => array(
                'status' => 'success',
                'model_name' => $llmResult['meta']['model_name'] ?? 'fallback-local',
                'prompt_version' => 'article_classification:v1',
                'fallback_used' => true,
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
