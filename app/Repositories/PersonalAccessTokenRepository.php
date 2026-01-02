<?php

namespace App\Repositories;

use App\Models\PersonalAccessToken;
use App\Models\TokenUsageLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PersonalAccessTokenRepository
{
    /**
     * 创建个人访问令牌
     */
    public function createToken(array $data): PersonalAccessToken
    {
        $token = Str::random(64);
        $tokenHash = Hash::make($token);

        $personalAccessToken = PersonalAccessToken::create([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'token' => $token,
            'token_hash' => $tokenHash,
            'scopes' => $data['scopes'] ?? [],
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return $personalAccessToken;
    }

    /**
     * 根据ID获取令牌
     */
    public function findTokenById(int $id, int $userId): ?PersonalAccessToken
    {
        return PersonalAccessToken::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * 根据令牌值获取令牌
     */
    public function findTokenByValue(string $token): ?PersonalAccessToken
    {
        $tokenHash = Hash::make($token);
        
        return PersonalAccessToken::where('token_hash', $tokenHash)
            ->first();
    }

    /**
     * 验证令牌并返回令牌对象
     */
    public function validateToken(string $token): ?PersonalAccessToken
    {
        $personalAccessToken = PersonalAccessToken::where('token', $token)->first();
        
        if (!$personalAccessToken) {
            return null;
        }

        if (!$personalAccessToken->isValid()) {
            return null;
        }

        return $personalAccessToken;
    }

    /**
     * 获取用户的所有令牌
     */
    public function getUserTokens(int $userId)
    {
        return PersonalAccessToken::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 删除令牌
     */
    public function deleteToken(int $id, int $userId): bool
    {
        $token = $this->findTokenById($id, $userId);
        if (!$token) {
            return false;
        }

        return $token->delete() === true;
    }

    /**
     * 记录令牌使用日志
     */
    public function logTokenUsage(PersonalAccessToken $token, string $endpoint, ?string $ip = null, ?string $userAgent = null): void
    {
        TokenUsageLog::create([
            'token_id' => $token->id,
            'api_endpoint' => $endpoint,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        // 更新令牌的最后使用时间
        $token->updateLastUsedAt();
    }
}