<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章简报配置
 *
 * @author edison.an
 */
class BriefingConfig extends Model
{
    protected $table = 'briefing_configs';

    protected $fillable = array(
        'user_id',
        'name',
        'enabled',
        'pull_hours',
        'schedule_time',
        'scope',
        'feed_ids_json',
        'category_ids_json',
        'supplement',
        'last_generated_at',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'enabled' => 'boolean',
        'pull_hours' => 'integer',
        'feed_ids_json' => 'array',
        'category_ids_json' => 'array',
        'last_generated_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pages()
    {
        return $this->hasMany(BriefingPage::class, 'config_id');
    }

    public function getFeedIdsAttribute()
    {
        return (array)$this->feed_ids_json;
    }
}
