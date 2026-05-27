<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyCheckin extends Model
{
    protected $table = 'study_checkins';

    protected $fillable = array(
        'user_id',
        'task_id',
        'checkin_date',
        'content',
        'audio_path',
        'image_path',
        'video_path',
    );

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
