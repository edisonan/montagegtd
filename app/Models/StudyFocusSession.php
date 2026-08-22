<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyFocusSession extends Model
{
    protected $table = 'study_focus_sessions';

    protected $fillable = array(
        'user_id',
        'task_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'completed',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'task_id' => 'integer',
        'duration_seconds' => 'integer',
        'completed' => 'integer',
    );

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}