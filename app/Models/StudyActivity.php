<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyActivity extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_item_id',
        'activity_type',
        'metadata'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'course_item_id' => 'integer',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseItem()
    {
        return $this->belongsTo(CourseItem::class, 'course_item_id');
    }
}