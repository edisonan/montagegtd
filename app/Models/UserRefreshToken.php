<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRefreshToken extends Model
{
    protected $fillable = array(
        'user_id',
        'access_token_id',
        'token_hash',
        'device_id',
        'client_type',
        'expires_at',
        'revoked_at',
    );

    protected $hidden = array(
        'token_hash',
    );

    protected $casts = array(
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessToken()
    {
        return $this->belongsTo(UserAccessToken::class, 'access_token_id');
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
