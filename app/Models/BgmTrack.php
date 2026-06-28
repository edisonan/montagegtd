<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BgmTrack extends Model
{
    protected $table = 'bgm_tracks';

    protected $fillable = array(
        'title',
        'artist',
        'audio_url',
        'source_url',
        'cover_color',
        'source_type',
        'search_keyword',
        'sort_order',
        'is_active',
        'metadata_json',
    );

    protected $casts = array(
        'sort_order' => 'integer',
        'is_active' => 'integer',
        'metadata_json' => 'array',
    );
}
