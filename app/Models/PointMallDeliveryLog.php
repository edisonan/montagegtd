<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointMallDeliveryLog extends Model
{
    protected $table = 'point_mall_delivery_logs';

    protected $fillable = array(
        'order_id',
        'handler',
        'status',
        'message',
        'request_payload',
        'response_payload',
    );
}

