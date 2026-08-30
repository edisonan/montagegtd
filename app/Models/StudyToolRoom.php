<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyToolRoom extends Model
{
    protected $fillable = array(
        'code',
        'kind',
        'owner_user_id',
        'state',
        'expires_at',
        'meta',
    );

    protected $casts = array(
        'state' => 'integer',
    );

    const STATE_WAITING = 0;
    const STATE_ACTIVE = 1;
    const STATE_CLOSED = 2;

    public function isExpired()
    {
        if ($this->expires_at && strtotime((string)$this->expires_at) < time()) {
            return true;
        }
        return false;
    }
}