<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章简报 v2（对比版：一次生成的简报结果）
 *
 * @author edison.an
 */
class BriefingV2Page extends Model
{
    protected $table = 'briefing_v2_pages';

    protected $fillable = array(
        'user_id',
        'config_id',
        'title',
        'topic_count',
        'candidate_count',
        'time_window',
        'model_name',
        'cover_time_start',
        'cover_time_end',
        'hot_topics_json',
        'trends_json',
        'signals_json',
        'tag_aggregation_json',
        'article_ids_json',
        'status',
        'fallback',
        'error_message',
        'generated_at',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'config_id' => 'integer',
        'topic_count' => 'integer',
        'candidate_count' => 'integer',
        'hot_topics_json' => 'array',
        'trends_json' => 'array',
        'signals_json' => 'array',
        'tag_aggregation_json' => 'array',
        'article_ids_json' => 'array',
        'fallback' => 'integer',
        'cover_time_start' => 'datetime',
        'cover_time_end' => 'datetime',
        'generated_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function config()
    {
        return $this->belongsTo(BriefingConfig::class, 'config_id');
    }
}