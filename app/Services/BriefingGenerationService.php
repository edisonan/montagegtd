<?php

namespace App\Services;

use App\Models\ArticleSub;
use App\Models\BriefingConfig;
use App\Models\FeedSub;
use App\Repositories\BriefingConfigRepository;
use App\Repositories\BriefingPageRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * 文章简报生成服务
 *
 * @author edison.an
 */
class BriefingGenerationService
{
    const MAX_PULL_HOURS = 24;

    protected $configRepository;
    protected $pageRepository;
    protected $llmStructuredTaskService;

    public function __construct(
        BriefingConfigRepository $configRepository,
        BriefingPageRepository $pageRepository,
        LlmStructuredTaskService $llmStructuredTaskService
    ) {
        $this->configRepository = $configRepository;
        $this->pageRepository = $pageRepository;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    /**
     * 为指定用户/配置生成简报。仅取 config->user_id 对应的订阅文章。
     *
     * @param int $configId
     * @param int|null $userId 校验用
     * @return array
     */
    public function generateForConfig($configId, $userId = null)
    {
        $config = $this->configRepository->findById($configId);
        if (!$config) {
            return array('status' => 'failed', 'message' => 'config not found');
        }
        if ($userId !== null && (int)$config->user_id !== (int)$userId) {
            return array('status' => 'failed', 'message' => 'config not owned by user');
        }
        if (!$config->enabled) {
            return array('status' => 'failed', 'message' => 'config disabled');
        }

        $candidates = $this->getCandidatesForConfig($config);

        $content = $this->generateBriefingContent($config, $candidates);

        $coverStart = $this->coverStart($config);
        $coverEnd = Carbon::now();

        $page = $this->pageRepository->create(array(
            'user_id' => $config->user_id,
            'config_id' => $config->id,
            'title' => $content['title'],
            'topic_count' => (int)$content['topic_count'],
            'time_window' => $this->formatTimeWindow($coverStart, $coverEnd),
            'model_name' => $content['model_name'],
            'cover_time_start' => $coverStart->toDateTimeString(),
            'cover_time_end' => $coverEnd->toDateTimeString(),
            'hot_topics_json' => $content['hot_topics'],
            'trends_json' => $content['trends'],
            'signals_json' => $content['signals'],
            'tag_aggregation_json' => $content['tag_aggregation'],
            'article_ids_json' => $candidates->pluck('article_id')->values()->all(),
            'status' => 'success',
            'generated_at' => $coverEnd->toDateTimeString(),
        ));

        $this->configRepository->update($config->id, array(
            'last_generated_at' => $coverEnd->toDateTimeString(),
        ));

        return array('status' => 'success', 'page_id' => $page->id);
    }

    /**
     * 调度使用：按定时时间将到期的配置逐一生成简报任务
     */
    public function generateDueConfigs($nowMinutes = null, $limit = 50)
    {
        $nowMinutes = $nowMinutes === null ? Carbon::now('Asia/Shanghai')->format('H:i') : $nowMinutes;
        $configs = $this->configRepository->findDueConfigs($nowMinutes, $limit);
        $generated = 0;
        $failed = 0;

        foreach ($configs as $config) {
            $result = $this->generateForConfig($config->id);
            if (!empty($result['status']) && $result['status'] === 'success') {
                $generated++;
            } else {
                $failed++;
            }
        }

        return array('generated' => $generated, 'failed' => $failed);
    }

    protected function coverStart(BriefingConfig $config)
    {
        return Carbon::now()->subHours(max(1, min(self::MAX_PULL_HOURS, (int)$config->pull_hours)));
    }

    /**
     * 获取候选文章（用户订阅 + 发布时间窗口 + 范围过滤）
     */
    protected function getCandidatesForConfig($config)
    {
        $pullHours = max(1, min(self::MAX_PULL_HOURS, (int)$config->pull_hours));
        $start = Carbon::now()->subHours($pullHours)->toDateTimeString();

        $query = ArticleSub::with('article.feed', 'article.aiProfile')
            ->where('article_subs.user_id', $config->user_id)
            ->where('article_subs.published', '>=', $start);

        $scope = (string)$config->scope;
        if ($scope === 'feeds' || $scope === 'exclude_feeds') {
            $feedIds = array_filter(array_map('intval', (array)$config->feed_ids_json));
            if (!empty($feedIds)) {
                if ($scope === 'feeds') {
                    $query->whereIn('article_subs.feed_id', $feedIds);
                } else {
                    $query->whereNotIn('article_subs.feed_id', $feedIds);
                }
            }
        } elseif ($scope === 'by_category') {
            $categoryIds = array_filter(array_map('intval', (array)$config->category_ids_json));
            $feedIds = $this->resolveFeedIdsFromCategories((int)$config->user_id, $categoryIds);
            if (!empty($feedIds)) {
                $query->whereIn('article_subs.feed_id', $feedIds);
            }
        }

        $subs = $query->orderBy('article_subs.published', 'desc')->limit(200)->get();

        // 仅保留有正文/标题的有效候选，并附加 AI 画像
        return $subs->filter(function ($sub) {
            $article = $sub->article;
            return $article && trim((string)$article->subject) !== '';
        })->values();
    }

    /**
     * 根据分类 ids 解析出该用户已订阅的订阅源 ids（按订阅源分类拉取）
     */
    protected function resolveFeedIdsFromCategories($userId, array $categoryIds)
    {
        $categoryIds = array_filter(array_map('intval', $categoryIds));
        if (empty($categoryIds)) {
            return array();
        }

        return FeedSub::where('user_id', (int)$userId)
            ->where('status', 1)
            ->whereIn('category_id', $categoryIds)
            ->pluck('feed_id')
            ->map(function ($feedId) {
                return (int)$feedId;
            })
            ->all();
    }

    protected function generateBriefingContent($config, Collection $candidates)
    {
        $sourceLines = array();
        foreach ($candidates as $sub) {
            $article = $sub->article;
            $profile = optional($article)->aiProfile;
            $sourceLines[] = sprintf(
                "article_id=%d | 标题:%s | 分类:%s | 标签:%s | 摘要:%s",
                (int)$sub->article_id,
                trim((string)$article->subject),
                (string)optional($profile)->primary_category,
                implode(',', (array)optional($profile)->tags_json),
                trim((string)optional($profile)->summary)
            );
        }

        $supplement = trim((string)$config->supplement);
        $pullHours = max(1, min(self::MAX_PULL_HOURS, (int)$config->pull_hours));

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是资深内容编辑。请根据给定候选文章生成一份结构化的中文文章简报，只输出 JSON，不要输出其他内容。'
                    . ' JSON 字段如下：'
                    . ' {"title": string(简报标题), "hot_topics": string[](本次主要包含的热点内容关键词，3~6个), '
                    . ' "trends": object[](今日趋势，最多5条，每条 {"title": string, "summary": string(2句话简述), "article_id": int}), '
                    . ' "signals": object[](待观察信号，最多6条，每条 {"title": string, "summary": string, "article_id": int}), '
                    . ' "tag_aggregation": object[](标签聚合，每条 {"tag": string, "article_ids": int[]})}',
            ),
            array(
                'role' => 'user',
                'content' => "拉取时间范围：前 {$pullHours} 小时。\n"
                    . ($supplement !== '' ? "补充要求：{$supplement}\n" : '')
                    . "候选文章（每行一条）：\n" . implode("\n", $sourceLines)
                    . "\n\n请生成简报 JSON。今日趋势与待观察信号的 article_id 必须来自上面给出的候选文章。",
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'briefing_generation',
            $messages,
            array(
                'response_format' => array('type' => 'json_object'),
                'timeout' => 120,
            )
        );

        $modelName = isset($llmResult['meta']['model_name']) ? (string)$llmResult['meta']['model_name'] : 'fallback-local';

        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            try {
                $decoded = json_decode(trim((string)$llmResult['content']), true);
            } catch (\Throwable $e) {
                $decoded = null;
            }
            if (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
                return $this->buildResultFromDecoded($config, $decoded, $candidates, $modelName);
            }
        }

        return $this->buildFallbackResult($config, $candidates, $modelName);
    }

    protected function buildResultFromDecoded($config, array $decoded, Collection $candidates, $modelName)
    {
        // 将具备 article 数据的候选索引化，便于补全标题/摘要/状态
        $byId = array();
        foreach ($candidates as $candidate) {
            $byId[(int)$candidate->article_id] = $candidate;
        }

        $trends = $this->normalizeArticleItems($decoded['trends'] ?? array(), $byId);
        $signals = $this->normalizeArticleItems($decoded['signals'] ?? array(), $byId);

        $hotTopics = array();
        foreach ((array)($decoded['hot_topics'] ?? array()) as $topic) {
            $topic = trim((string)$topic);
            if ($topic !== '') {
                $hotTopics[] = $topic;
            }
        }

        $tagAggregation = array();
        foreach ((array)($decoded['tag_aggregation'] ?? array()) as $tagGroup) {
            $tag = trim((string)($tagGroup['tag'] ?? ''));
            if ($tag === '') {
                continue;
            }
            $articleIds = array();
            foreach ((array)($tagGroup['article_ids'] ?? array()) as $aid) {
                $aid = (int)$aid;
                if ($aid > 0 && isset($byId[$aid])) {
                    $articleIds[] = $aid;
                }
            }
            if (!empty($articleIds)) {
                $tagAggregation[] = array('tag' => $tag, 'article_ids' => array_values($articleIds));
            }
        }

        // 若 LLM 未给出标签聚合，则按候选的 AI 标签自动聚合兜底
        if (empty($tagAggregation)) {
            $tagAggregation = $this->buildTagAggregationFromCandidates($candidates);
        }

        $title = trim((string)($decoded['title'] ?? ''));
        if ($title === '') {
            $title = '前' . max(1, (int)$config->pull_hours) . '小时文章简报';
        }

        return array(
            'title' => mb_substr($title, 0, 120),
            'hot_topics' => array_slice($hotTopics, 0, 8),
            'topic_count' => count($hotTopics),
            'trends' => array_slice($trends, 0, 5),
            'signals' => array_slice($signals, 0, 6),
            'tag_aggregation' => $tagAggregation,
            'model_name' => $modelName,
        );
    }

    protected function buildFallbackResult($config, Collection $candidates, $modelName)
    {
        $topics = array();
        foreach ($candidates->take(5) as $candidate) {
            $subject = trim((string)$candidate->article->subject);
            if ($subject !== '') {
                $topics[] = mb_substr($subject, 0, 40);
            }
        }

        $count = $candidates->count();
        return array(
            'title' => '前' . max(1, (int)$config->pull_hours) . '小时文章简报',
            'hot_topics' => $topics,
            'topic_count' => $count,
            'trends' => $this->buildFallbackItems($candidates->take(5)),
            'signals' => array(),
            'tag_aggregation' => $this->buildTagAggregationFromCandidates($candidates),
            'model_name' => $modelName,
        );
    }

    protected function buildFallbackItems(Collection $subs)
    {
        $items = array();
        foreach ($subs as $sub) {
            $items[] = $this->articleItem($sub);
        }
        return $items;
    }

    protected function normalizeArticleItems(array $rawItems, array $byId)
    {
        $items = array();
        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }
            $articleId = (int)($item['article_id'] ?? 0);
            if ($articleId <= 0 || !isset($byId[$articleId])) {
                continue;
            }
            $items[] = array(
                'title' => trim((string)($item['title'] ?? '') ?: (string)$byId[$articleId]->article->subject),
                'summary' => trim((string)($item['summary'] ?? '')),
                'article_id' => $articleId,
            );
        }
        return $items;
    }

    protected function articleItem($sub)
    {
        $profile = optional($sub->article)->aiProfile;
        return array(
            'title' => trim((string)$sub->article->subject),
            'summary' => trim((string)optional($profile)->summary),
            'article_id' => (int)$sub->article_id,
        );
    }

    protected function buildTagAggregationFromCandidates(Collection $candidates)
    {
        $groups = array();
        foreach ($candidates as $candidate) {
            $profile = optional($candidate->article)->aiProfile;
            $tags = (array)optional($profile)->tags_json;
            foreach ($tags as $tag) {
                $tag = trim((string)$tag);
                if ($tag === '') {
                    continue;
                }
                if (!isset($groups[$tag])) {
                    $groups[$tag] = array();
                }
                $groups[$tag][] = (int)$candidate->article_id;
            }
        }

        $result = array();
        foreach ($groups as $tag => $ids) {
            $result[] = array('tag' => $tag, 'article_ids' => array_values(array_unique($ids)));
        }
        usort($result, function ($a, $b) {
            return count($b['article_ids']) - count($a['article_ids']);
        });
        return $result;
    }

    protected function formatTimeWindow(Carbon $start, Carbon $end)
    {
        return $start->format('Y-m-d H:i') . ' ~ ' . $end->format('Y-m-d H:i');
    }
}
