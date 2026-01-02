<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenUsageLog extends Model
{
    protected $fillable = [
        'token_id',
        'api_endpoint',
        'ip_address',
        'user_agent',
    ];

    /**
     * 与令牌的关系
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'token_id');
    }
}