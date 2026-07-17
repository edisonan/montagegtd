<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseGenerationRun extends Model
{
    protected $fillable = [
        'course_id', 'mode', 'status', 'source_url', 'items_count', 'error',
        'metadata', 'started_at', 'finished_at'
    ];

    protected $casts = [
        'course_id' => 'integer', 'items_count' => 'integer', 'metadata' => 'array',
        'started_at' => 'datetime', 'finished_at' => 'datetime'
    ];
}
