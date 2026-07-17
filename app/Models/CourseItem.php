<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseItem extends Model
{
    protected $fillable = [
        'course_id',
        'parent_id',
        'title',
        'item_type',
        'duration',
        'external_url',
        'description',
        'content',
        'order_index',
        'source_type',
        'source_key',
        'content_hash',
        'generated_at',
        'content_status',
        'avg_rating',
        'avg_study_time',
        'completion_count'
    ];

    protected $casts = [
        'course_id' => 'integer',
        'parent_id' => 'integer',
        'duration' => 'integer',
        'order_index' => 'float',
        'generated_at' => 'datetime',
        'avg_rating' => 'decimal:2',
        'avg_study_time' => 'integer',
        'completion_count' => 'integer'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function parent()
    {
        return $this->belongsTo(CourseItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CourseItem::class, 'parent_id');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class, 'course_item_id');
    }

    public function discussions()
    {
        return $this->hasMany(PublicDiscussion::class, 'course_item_id');
    }

    public function activities()
    {
        return $this->hasMany(StudyActivity::class, 'course_item_id');
    }
}
