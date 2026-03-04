<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessToken extends Model
{
    protected $fillable = array(
        'user_id',
        'token_hash',
        'capabilities',
        'expires_at',
        'last_used_at',
        'revoked_at',
    );

    protected $hidden = array(
        'token_hash',
    );

    protected $casts = array(
        'capabilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid()
    {
        if (!empty($this->revoked_at)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
