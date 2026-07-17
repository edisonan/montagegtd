<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuiz extends Model
{
    protected $fillable = ['course_item_id', 'passing_score', 'attempts_allowed', 'status'];

    protected $casts = [
        'course_item_id' => 'integer',
        'passing_score' => 'float',
        'attempts_allowed' => 'integer',
    ];

    public function courseItem()
    {
        return $this->belongsTo(CourseItem::class, 'course_item_id');
    }

    public function questions()
    {
        return $this->hasMany(CourseQuizQuestion::class, 'quiz_id')->orderBy('order_index');
    }

    public function attempts()
    {
        return $this->hasMany(CourseQuizAttempt::class, 'quiz_id');
    }
}
