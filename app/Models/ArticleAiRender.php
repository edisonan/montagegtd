<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleAiRender extends Model
{
    protected $table = 'article_ai_renders';

    protected $fillable = array(
        'article_id',
        'status',
        'render_mode',
        'template_style',
        'summary',
        'outline_json',
        'html_content',
        'model_name',
        'prompt_version',
        'generated_at',
        'error_message',
    );

    protected $casts = array(
        'article_id' => 'integer',
        'outline_json' => 'array',
        'generated_at' => 'datetime',
    );

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
