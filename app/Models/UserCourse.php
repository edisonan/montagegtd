<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'status',
        'goal',
        'show_progress',
        'show_notes',
        'show_study_time',
        'progress_percent',
        'last_activity_at',
        'order_index',
        'start_date',
        'target_end_date',
        'completed_date'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'show_progress' => 'boolean',
        'show_notes' => 'boolean',
        'show_study_time' => 'boolean',
        'progress_percent' => 'decimal:2',
        'order_index' => 'integer',
        'last_activity_at' => 'datetime',
        'start_date' => 'date',
        'target_end_date' => 'date',
        'completed_date' => 'date'
    ];

    protected $dates = [
        'last_activity_at',
        'start_date',
        'target_end_date',
        'completed_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class, 'user_course_id');
    }

    public function activities()
    {
        return $this->hasMany(StudyActivity::class, 'user_course_id');
    }
}