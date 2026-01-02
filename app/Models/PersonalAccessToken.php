<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalAccessToken extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'token',
        'token_hash',
        'scopes',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * 与用户的关系
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 令牌使用日志
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(TokenUsageLog::class);
    }

    /**
     * 检查令牌是否已过期
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * 检查令牌是否有效（未过期且未被撤销）
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * 检查令牌是否具有指定权限
     */
    public function can(string $scope): bool
    {
        $scopes = $this->scopes;
        if (in_array('*', $scopes)) {
            return true;
        }
        return in_array($scope, $scopes);
    }

    /**
     * 更新最后使用时间
     */
    public function updateLastUsedAt(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}