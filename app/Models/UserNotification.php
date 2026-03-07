<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = array(
        'user_id',
        'type',
        'title',
        'content',
        'data',
        'read_at',
    );

    protected $casts = array(
        'data' => 'array',
        'read_at' => 'datetime',
    );
}

