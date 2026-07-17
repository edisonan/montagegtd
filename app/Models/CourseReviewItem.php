<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseReviewItem extends Model
{
    protected $fillable = [
        'user_id', 'user_course_id', 'course_item_id', 'status', 'review_count',
        'interval_days', 'last_score', 'last_reviewed_at', 'next_review_at'
    ];

    protected $casts = [
        'user_id' => 'integer', 'user_course_id' => 'integer', 'course_item_id' => 'integer',
        'review_count' => 'integer', 'interval_days' => 'integer', 'last_score' => 'float',
        'last_reviewed_at' => 'datetime', 'next_review_at' => 'datetime'
    ];

    public function courseItem()
    {
        return $this->belongsTo(CourseItem::class, 'course_item_id');
    }
}
