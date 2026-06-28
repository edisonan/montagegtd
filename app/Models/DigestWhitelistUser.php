<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigestWhitelistUser extends Model
{
    protected $table = 'digest_whitelist_users';

    protected $fillable = array(
        'user_id',
        'enabled',
        'expires_at',
        'remark',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'enabled' => 'boolean',
        'expires_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
