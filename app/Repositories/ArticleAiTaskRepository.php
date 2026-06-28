<?php

namespace App\Repositories;

use App\Models\ArticleAiTask;

class ArticleAiTaskRepository
{
    public function create(array $data)
    {
        return ArticleAiTask::create($data);
    }

    public function findById($id)
    {
        return ArticleAiTask::with('article')->where('id', $id)->first();
    }

    public function findPendingTasks($limit = 20)
    {
        return ArticleAiTask::with('article')
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', date('Y-m-d H:i:s'));
            })
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }

    public function findByArticleId($articleId)
    {
        return ArticleAiTask::where('article_id', $articleId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function update($id, array $data)
    {
        $task = ArticleAiTask::where('id', $id)->first();
        if ($task) {
            $task->update($data);
        }

        return $task;
    }

    public function paginateByFilters(array $filters = array(), $perPage = 20)
    {
        $query = ArticleAiTask::with('article')->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['article_id'])) {
            $query->where('article_id', $filters['article_id']);
        }

        return $query->paginate($perPage);
    }
}
