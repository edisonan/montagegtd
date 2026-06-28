<?php

namespace App\Repositories;

use App\Models\ArticleAiProfile;
use Carbon\Carbon;

class ArticleAiProfileRepository
{
    public function findByArticleId($articleId)
    {
        return ArticleAiProfile::where('article_id', $articleId)->first();
    }

    public function create(array $data)
    {
        return ArticleAiProfile::create($data);
    }

    public function updateOrCreateByArticleId($articleId, array $data)
    {
        return ArticleAiProfile::updateOrCreate(
            array('article_id' => $articleId),
            $data
        );
    }

    public function paginateByFilters(array $filters = array(), $perPage = 20)
    {
        $query = ArticleAiProfile::with('article')->orderBy('updated_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['primary_category'])) {
            $query->where('primary_category', $filters['primary_category']);
        }

        if (!empty($filters['article_id'])) {
            $query->where('article_id', $filters['article_id']);
        }

        return $query->paginate($perPage);
    }

    public function getRecentProfilesForDigest($days = 7, $limit = 100)
    {
        $start = Carbon::now()->subDays(max(1, (int)$days))->toDateTimeString();

        return ArticleAiProfile::with('article.feed')
            ->where('status', 'success')
            ->whereHas('article', function ($query) use ($start) {
                $query->where('published', '>=', $start);
            })
            ->orderBy('analyzed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLatestProfiles($limit = 100)
    {
        return ArticleAiProfile::with('article.feed')
            ->where('status', 'success')
            ->orderBy('analyzed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
