<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebpageRssSource extends Model
{
    protected $table = 'webpage_rss_sources';

    protected $fillable = array(
        'user_id',
        'feed_id',
        'category_id',
        'name',
        'list_url',
        'rss_token',
        'item_selector',
        'title_selector',
        'url_selector',
        'published_selector',
        'image_selector',
        'summary_source',
        'summary_selector',
        'detail_enabled',
        'detail_summary_selector',
        'content_selector',
        'exclude_selector',
        'author_selector',
        'max_content_length',
        'failure_strategy',
        'refresh_interval',
        'dedupe_key',
        'encoding',
        'last_debug_result',
        'last_error',
        'last_checked_at',
        'status',
    );

    protected $casts = array(
        'user_id' => 'int',
        'feed_id' => 'int',
        'category_id' => 'int',
        'detail_enabled' => 'int',
        'max_content_length' => 'int',
        'refresh_interval' => 'int',
        'status' => 'int',
    );

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }
}
