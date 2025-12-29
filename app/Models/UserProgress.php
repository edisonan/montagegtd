<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    protected $fillable = [
        'user_id',
        'user_course_id',
        'course_item_id',
        'status',
        'completed_at',
        'last_accessed_at',
        'time_spent',
        'rating',
        'notes',
        'note_updated_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'user_course_id' => 'integer',
        'course_item_id' => 'integer',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'time_spent' => 'integer',
        'rating' => 'integer',
        'note_updated_at' => 'datetime'
    ];

    protected $dates = [
        'completed_at',
        'last_accessed_at',
        'note_updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class, 'user_course_id');
    }

    public function courseItem()
    {
        return $this->belongsTo(CourseItem::class, 'course_item_id');
    }
}