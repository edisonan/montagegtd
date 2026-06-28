<?php

namespace App\Repositories;

use App\Models\ArticleAiRender;

class ArticleAiRenderRepository
{
    public function findByArticleId($articleId)
    {
        return ArticleAiRender::where('article_id', $articleId)->first();
    }

    public function updateOrCreateByArticleId($articleId, array $data)
    {
        return ArticleAiRender::updateOrCreate(
            array('article_id' => $articleId),
            $data
        );
    }
}
