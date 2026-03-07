<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointMallEntitlement extends Model
{
    protected $table = 'point_mall_entitlements';

    protected $fillable = array(
        'order_id',
        'user_id',
        'entitlement_type',
        'quantity',
        'status',
        'meta_payload',
    );
}

