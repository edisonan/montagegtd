<?php

namespace App\Services;

use App\Repositories\BriefingConfigRepository;
use App\Repositories\BriefingV2PageRepository;
use Illuminate\Support\Facades\DB;

/**
 * 文章简报 v2 业务服务（配置 + 结果序列化 + 文章状态快照）
 *
 * 纯只读复用 briefing_configs（不写配置表），v2 结果独立存 briefing_v2_pages。
 *
 * @author edison.an
 */
class BriefingV2Service
{
    protected $configRepository;
    protected $pageRepository;

    public function __construct(
        BriefingConfigRepository $configRepository,
        BriefingV2PageRepository $pageRepository
    ) {
        $this->configRepository = $configRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * 序列化一条配置，附带 v2 最新生成历史（只读，不影响 v1）
     */
    public function serializeConfig($config)
    {
        if (!$config) {
            return null;
        }

        $configId = (int)$config->id;
        $latest = $this->pageRepository->latestByConfigId($configId);

        return array(
            'id' => $configId,
            'name' => $config->name,
            'enabled' => (bool)$config->enabled,
            'pull_hours' => (int)$config->pull_hours,
            'schedule_time' => $config->schedule_time,
            'scope' => $config->scope,
            'feed_ids' => (array)$config->feed_ids_json,
            'category_ids' => (array)$config->category_ids_json,
            'supplement' => $config->supplement,
            'v2_page_count' => $this->pageRepository->countByConfigId($configId),
            'latest_v2_page' => $latest ? $this->serializePageMeta($latest) : null,
        );
    }

    /**
     * 序列化 v2 简报结果（含可点击文章详情与实时阅读状态）
     */
    public function serializePage($page)
    {
        if (!$page) {
            return null;
        }

        $config = $page->config;
        $articleMap = $this->resolveArticleMap((int)$page->user_id, (array)$page->article_ids_json);

        return array(
            'id' => (int)$page->id,
            'config_id' => (int)$page->config_id,
            'config_name' => $config ? $config->name : null,
            'title' => $page->title,
            'topic_count' => (int)$page->topic_count,
            'candidate_count' => (int)$page->candidate_count,
            'time_window' => $page->time_window,
            'model_name' => $page->model_name,
            'fallback' => (int)$page->fallback,
            'error_message' => $page->error_message,
            'hot_topics' => (array)$page->hot_topics_json,
            'trends' => $this->attachArticleDetail((array)$page->trends_json, $articleMap),
            'signals' => $this->attachArticleDetail((array)$page->signals_json, $articleMap),
            'tag_aggregation' => $this->serializeTagAggregation((array)$page->tag_aggregation_json, $articleMap),
            'article_ids' => (array)$page->article_ids_json,
            'generated_at' => $page->generated_at ? $page->generated_at->toDateTimeString() : null,
        );
    }

    public function serializePageMeta($page)
    {
        return array(
            'id' => (int)$page->id,
            'title' => $page->title,
            'topic_count' => (int)$page->topic_count,
            'candidate_count' => (int)$page->candidate_count,
            'time_window' => $page->time_window,
            'model_name' => $page->model_name,
            'fallback' => (int)$page->fallback,
            'generated_at' => $page->generated_at ? $page->generated_at->toDateTimeString() : null,
        );
    }

    public function getConfigsByUserId($userId)
    {
        return $this->configRepository->findEnabledByUserId($userId);
    }

    /**
     * 组装所有候选文章的展示快照（article + aiProfile + 用户阅读状态）
     */
    protected function resolveArticleMap($userId, array $articleIds)
    {
        if (empty($articleIds)) {
            return array();
        }

        $subs = DB::table('article_subs as a')
            ->join('articles as ar', 'a.article_id', '=', 'ar.id')
            ->leftJoin('feeds as f', 'a.feed_id', '=', 'f.id')
            ->leftJoin('article_ai_profiles as p', 'a.article_id', '=', 'p.article_id')
            ->where('a.user_id', $userId)
            ->whereIn('a.article_id', $articleIds)
            ->select(
                'a.article_id',
                'a.id as article_sub_id',
                'a.status',
                'a.star_ind',
                'ar.subject',
                'ar.url',
                'ar.image_url',
                'ar.published',
                'f.feed_name',
                'p.summary',
                'p.tags_json',
                'p.primary_category'
            )
            ->get();

        $map = array();
        foreach ($subs as $sub) {
            $map[(int)$sub->article_id] = array(
                'article_id' => (int)$sub->article_id,
                'article_sub_id' => (int)$sub->article_sub_id,
                'status' => $sub->status,
                'star_ind' => (int)$sub->star_ind,
                'read' => $sub->status === 'read',
                'starred' => $sub->status === 'star',
                'read_later' => $sub->status === 'read_later',
                'subject' => $sub->subject,
                'url' => $sub->url,
                'image_url' => $sub->image_url ?: null,
                'published' => $sub->published,
                'feed_name' => $sub->feed_name,
                'summary' => $sub->summary,
                'tags' => $sub->tags_json ? json_decode($sub->tags_json, true) : array(),
                'primary_category' => $sub->primary_category,
            );
        }

        return $map;
    }

    /**
     * 组装趋势/信号条目的文章详情（支持多佐证）。
     * 兼容两种存储形状：article_ids（int[]，多佐证）与 article_id（int，旧/兜底）。
     */
    protected function attachArticleDetail(array $items, array $articleMap)
    {
        $result = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $ids = array();
            foreach ((array)($item['article_ids'] ?? array()) as $aid) {
                $aid = (int)$aid;
                if ($aid > 0) {
                    $ids[$aid] = $aid;
                }
            }
            $single = (int)($item['article_id'] ?? 0);
            if ($single > 0) {
                $ids[$single] = $single;
            }
            $ids = array_values($ids);

            $articles = array();
            $firstArticle = null;
            foreach ($ids as $articleId) {
                if (isset($articleMap[$articleId])) {
                    $articles[] = $articleMap[$articleId];
                    if ($firstArticle === null) {
                        $firstArticle = $articleMap[$articleId];
                    }
                }
            }

            $entry = array(
                'title' => isset($item['title']) ? (string)$item['title'] : ($firstArticle ? $firstArticle['subject'] : ''),
                'summary' => isset($item['summary']) ? (string)$item['summary'] : '',
                'article_id' => $articles ? (int)$articles[0]['article_id'] : (int)($item['article_id'] ?? 0),
                'article_ids' => array_map(function ($a) {
                    return (int)$a['article_id'];
                }, $articles),
                'articles' => $articles,
            );
            if ($firstArticle) {
                $entry['article'] = $firstArticle;
            }
            $result[] = $entry;
        }
        return $result;
    }

    protected function serializeTagAggregation(array $groups, array $articleMap)
    {
        $result = array();
        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $tag = (string)($group['tag'] ?? '');
            if ($tag === '') {
                continue;
            }
            $articles = array();
            foreach ((array)($group['article_ids'] ?? array()) as $articleId) {
                $articleId = (int)$articleId;
                if (isset($articleMap[$articleId])) {
                    $articles[] = $articleMap[$articleId];
                }
            }
            $result[] = array('tag' => $tag, 'articles' => $articles, 'count' => count($articles));
        }
        return $result;
    }
}