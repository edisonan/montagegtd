<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointAccount extends Model
{
    protected $table = 'point_account';

    protected $fillable = [
        'user_id',
        'gp_balance',
        'ap_balance',
        'ap_frozen',
    ];

    protected $casts = [
        'user_id'    => 'int',
        'gp_balance' => 'int',
        'ap_balance' => 'int',
        'ap_frozen'  => 'int',
    ];

    /**
     * ÓÃ»§
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
