<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleAiTask extends Model
{
    protected $table = 'article_ai_tasks';

    protected $fillable = array(
        'article_id',
        'status',
        'retry_count',
        'scheduled_at',
        'started_at',
        'finished_at',
        'error_message',
        'model_name',
        'prompt_version',
    );

    protected $casts = array(
        'article_id' => 'integer',
        'retry_count' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    );

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
