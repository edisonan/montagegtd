<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRecord extends Model
{
    protected $table = 'point_record';

    protected $fillable = [
        'user_id',
        'point_type',
        'change_amount',
        'balance_after',
        'source_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'user_id'       => 'int',
        'change_amount' => 'int',
        'balance_after' => 'int',
        'source_id'     => 'int',
    ];

    /**
     * ÓÃ»§
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
