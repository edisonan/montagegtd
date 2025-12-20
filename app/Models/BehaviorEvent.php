<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehaviorEvent extends Model
{
    protected $table = 'behavior_event';

    protected $fillable = [
        'user_id',
        'event_type',
        'event_key',
        'event_value',
        'occurred_at',
    ];

    protected $casts = [
        'user_id'     => 'int',
        'event_value' => 'int',
    ];

    /**
     * ÓÃ»§
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
