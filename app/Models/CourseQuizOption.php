<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizOption extends Model
{
    protected $fillable = ['question_id', 'option_key', 'content', 'is_correct', 'order_index'];

    protected $casts = ['question_id' => 'integer', 'is_correct' => 'boolean', 'order_index' => 'float'];

    public function question()
    {
        return $this->belongsTo(CourseQuizQuestion::class, 'question_id');
    }
}
