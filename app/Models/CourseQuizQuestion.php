<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'question_type', 'question', 'explanation', 'points', 'order_index', 'source_type'];

    protected $casts = ['quiz_id' => 'integer', 'points' => 'float', 'order_index' => 'float'];

    public function quiz()
    {
        return $this->belongsTo(CourseQuiz::class, 'quiz_id');
    }

    public function options()
    {
        return $this->hasMany(CourseQuizOption::class, 'question_id')->orderBy('order_index');
    }
}
