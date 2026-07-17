<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'user_id', 'user_course_id', 'score', 'correct_count',
        'total_count', 'passed', 'answers', 'completed_at'
    ];

    protected $casts = [
        'quiz_id' => 'integer', 'user_id' => 'integer', 'user_course_id' => 'integer',
        'score' => 'float', 'correct_count' => 'integer', 'total_count' => 'integer',
        'passed' => 'boolean', 'answers' => 'array', 'completed_at' => 'datetime'
    ];

    public function quiz()
    {
        return $this->belongsTo(CourseQuiz::class, 'quiz_id');
    }
}
