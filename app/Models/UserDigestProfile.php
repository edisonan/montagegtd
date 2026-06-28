<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDigestProfile extends Model
{
    protected $table = 'user_digest_profiles';

    protected $fillable = array(
        'user_id',
        'enabled',
        'topics_json',
        'include_keywords_json',
        'exclude_keywords_json',
        'preferred_categories_json',
        'time_window_days',
        'frequency',
        'max_articles',
        'output_style',
        'last_generated_at',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'enabled' => 'boolean',
        'topics_json' => 'array',
        'include_keywords_json' => 'array',
        'exclude_keywords_json' => 'array',
        'preferred_categories_json' => 'array',
        'time_window_days' => 'integer',
        'max_articles' => 'integer',
        'last_generated_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
