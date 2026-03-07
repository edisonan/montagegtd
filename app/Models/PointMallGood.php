<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointMallGood extends Model
{
    protected $table = 'point_mall_goods';

    protected $fillable = array(
        'code',
        'name',
        'scene',
        'delivery_type',
        'image_url',
        'description',
        'point_cost',
        'stock',
        'status',
        'sort',
        'payload',
    );
}

