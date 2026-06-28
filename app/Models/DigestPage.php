<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigestPage extends Model
{
    protected $table = 'digest_pages';

    protected $fillable = array(
        'user_id',
        'profile_id',
        'task_id',
        'title',
        'cover_time_start',
        'cover_time_end',
        'intro',
        'content_markdown',
        'source_article_ids_json',
        'status',
        'generated_at',
        'model_name',
        'prompt_version',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'profile_id' => 'integer',
        'task_id' => 'integer',
        'cover_time_start' => 'datetime',
        'cover_time_end' => 'datetime',
        'source_article_ids_json' => 'array',
        'generated_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(UserDigestProfile::class, 'profile_id');
    }

    public function task()
    {
        return $this->belongsTo(DigestTask::class, 'task_id');
    }
}
