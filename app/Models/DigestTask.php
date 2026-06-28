<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigestTask extends Model
{
    protected $table = 'digest_tasks';

    protected $fillable = array(
        'user_id',
        'profile_id',
        'status',
        'scheduled_at',
        'started_at',
        'finished_at',
        'retry_count',
        'error_message',
        'model_name',
        'prompt_version',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'profile_id' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'retry_count' => 'integer',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(UserDigestProfile::class, 'profile_id');
    }
}
