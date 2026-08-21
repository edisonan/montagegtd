<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Repositories\BriefingConfigRepository;
use App\Repositories\BriefingPageRepository;
use Illuminate\Support\Facades\DB;

/**
 * 文章简报业务服务（配置 + 结果序列化 + 文章状态快照）
 *
 * @author edison.an
 */
class BriefingService
{
    protected $configRepository;
    protected $pageRepository;

    public function __construct(
        BriefingConfigRepository $configRepository,
        BriefingPageRepository $pageRepository
    ) {
        $this->configRepository = $configRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * 序列化一条配置，附带生成历史
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
            'last_generated_at' => $config->last_generated_at ? $config->last_generated_at->toDateTimeString() : null,
            'latest_page' => $latest ? $this->serializePageMeta($latest) : null,
        );
    }

    /**
     * 序列化简报结果（含可点击文章详情与实时阅读状态）
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
            'time_window' => $page->time_window,
            'model_name' => $page->model_name,
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
            'time_window' => $page->time_window,
            'model_name' => $page->model_name,
            'generated_at' => $page->generated_at ? $page->generated_at->toDateTimeString() : null,
        );
    }

    public function getConfigsByUserId($userId)
    {
        return $this->configRepository->findEnabledByUserId($userId);
    }

    public function saveConfigByUserId($userId, array $data)
    {
        $pullHours = (int)($data['pull_hours'] ?? 6);
        if ($pullHours < 1 || $pullHours > 24) {
            throw new CustomException('拉取时间范围必须在1~24小时之间');
        }

        $scheduleTime = (string)($data['schedule_time'] ?? '08:00');
        if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $scheduleTime)) {
            throw new CustomException('定时获取时间格式错误（HH:MM）');
        }

        $scope = (string)($data['scope'] ?? 'all');
        if (!in_array($scope, array('all', 'feeds', 'exclude_feeds', 'by_category'), true)) {
            $scope = 'all';
        }

        $feedIds = array();
        foreach ((array)($data['feed_ids'] ?? array()) as $feedId) {
            $feedId = (int)$feedId;
            if ($feedId > 0) {
                $feedIds[] = $feedId;
            }
        }

        $categoryIds = array();
        foreach ((array)($data['category_ids'] ?? array()) as $categoryId) {
            $categoryId = (int)$categoryId;
            if ($categoryId > 0) {
                $categoryIds[] = $categoryId;
            }
        }

        $payload = array(
            'name' => trim((string)($data['name'] ?? '默认简报')) ?: '默认简报',
            'enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : true,
            'pull_hours' => $pullHours,
            'schedule_time' => $scheduleTime,
            'scope' => $scope,
            'feed_ids_json' => $feedIds,
            'category_ids_json' => $categoryIds,
            'supplement' => isset($data['supplement']) ? (string)$data['supplement'] : null,
        );

        return $this->configRepository->updateOrCreateForUser($userId, $payload);
    }

    public function destroyConfig($id, $userId)
    {
        return $this->configRepository->destroy($id, $userId);
    }

    public function destroyPage($id, $userId)
    {
        return $this->pageRepository->destroy($id, $userId);
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

    protected function attachArticleDetail(array $items, array $articleMap)
    {
        $result = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $articleId = (int)($item['article_id'] ?? 0);
            $entry = array(
                'title' => isset($item['title']) ? (string)$item['title'] : '',
                'summary' => isset($item['summary']) ? (string)$item['summary'] : '',
                'article_id' => $articleId,
            );
            if (isset($articleMap[$articleId])) {
                $entry['article'] = $articleMap[$articleId];
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
