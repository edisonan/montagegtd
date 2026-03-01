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
        'revoked_at',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
        'token_hash',
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
        return !$this->isExpired() && !$this->isRevoked();
    }

    /**
     * 检查令牌是否已撤销
     */
    public function isRevoked(): bool
    {
        return !empty($this->revoked_at);
    }

    /**
     * 检查令牌是否具有指定权限
     */
    public function can(string $scope): bool
    {
        $scopes = is_array($this->scopes) ? $this->scopes : [];
        if (in_array('*', $scopes)) {
            return true;
        }
        return in_array($scope, $scopes);
    }

    /**
     * 更新最后使用时间
     */
    public function updateLastUsedAt()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * 撤销令牌
     */
    public function revoke()
    {
        $this->update(['revoked_at' => now()]);
    }
}
