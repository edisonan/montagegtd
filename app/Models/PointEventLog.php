<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointEventLog extends Model
{
    protected $table = 'point_event_log';

    protected $fillable = array(
        'user_id',
        'rule_id',
        'event_type',
        'event_key',
        'source_type',
        'source_id',
        'point_type',
        'granted_points',
        'balance_after',
        'occurred_on',
    );

    protected $casts = array(
        'user_id' => 'int',
        'rule_id' => 'int',
        'source_id' => 'int',
        'granted_points' => 'int',
        'balance_after' => 'int',
        'occurred_on' => 'date',
    );
}

