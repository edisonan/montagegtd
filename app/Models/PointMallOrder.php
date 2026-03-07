<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointMallOrder extends Model
{
    protected $table = 'point_mall_orders';

    protected $fillable = array(
        'order_no',
        'user_id',
        'goods_id',
        'goods_snapshot',
        'quantity',
        'point_cost_each',
        'point_cost_total',
        'status',
        'delivery_status',
        'delivery_type',
        'delivery_message',
        'delivery_payload',
        'paid_at',
        'fulfilled_at',
    );

    protected $dates = array(
        'paid_at',
        'fulfilled_at',
        'created_at',
        'updated_at',
    );
}

