<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleAiProfile extends Model
{
    protected $table = 'article_ai_profiles';

    protected $fillable = array(
        'article_id',
        'status',
        'primary_category',
        'secondary_category',
        'tags_json',
        'keywords_json',
        'summary',
        'content_type',
        'audience',
        'quality_score',
        'risk_flags_json',
        'model_name',
        'prompt_version',
        'analyzed_at',
    );

    protected $casts = array(
        'article_id' => 'integer',
        'tags_json' => 'array',
        'keywords_json' => 'array',
        'quality_score' => 'integer',
        'risk_flags_json' => 'array',
        'analyzed_at' => 'datetime',
    );

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
