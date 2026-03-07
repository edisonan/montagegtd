<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    protected $table = 'point_rule';

    protected $fillable = array(
        'event_type',
        'name',
        'point_type',
        'point_value',
        'daily_max_grants',
        'enabled',
        'description',
    );

    protected $casts = array(
        'point_value' => 'int',
        'daily_max_grants' => 'int',
        'enabled' => 'int',
    );
}

