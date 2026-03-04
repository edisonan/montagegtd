<?php

namespace App\Repositories;

use App\Models\PersonalAccessToken;
use App\Models\TokenUsageLog;
use Illuminate\Support\Str;

class PersonalAccessTokenRepository
{
    /**
     * 创建个人访问令牌
     */
    public function createToken(array $data): array
    {
        $plainTextToken = 'pat_' . Str::random(64);
        $tokenHash = hash('sha256', $plainTextToken);
        $storedToken = 'pat_' . Str::random(60);

        $personalAccessToken = PersonalAccessToken::create([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            // 不存储可直接用于认证的明文token，降低库泄漏风险
            'token' => $storedToken,
            'token_hash' => $tokenHash,
            'scopes' => $data['scopes'] ?? [],
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return [
            'token' => $personalAccessToken,
            'plain_text_token' => $plainTextToken,
        ];
    }

    /**
     * 根据ID获取令牌
     */
    public function findTokenById(int $id, int $userId)
    {
        return PersonalAccessToken::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * 根据令牌值获取令牌
     */
    public function findTokenByValue(string $token)
    {
        $tokenHash = hash('sha256', $token);

        return PersonalAccessToken::where('token_hash', $tokenHash)
            ->first();
    }

    /**
     * 验证令牌并返回令牌对象
     */
    public function validateToken(string $token)
    {
        // 优先使用确定性哈希校验
        $tokenHash = hash('sha256', $token);
        $personalAccessToken = PersonalAccessToken::with('user')
            ->where('token_hash', $tokenHash)
            ->first();

        // 向后兼容：老数据可能仍在token字段存明文
        if (!$personalAccessToken) {
            $legacyToken = PersonalAccessToken::with('user')
                ->where('token', $token)
                ->first();
            if ($legacyToken) {
                $legacyToken->token_hash = $tokenHash;
                $legacyToken->token = 'legacy_' . Str::random(57);
                $legacyToken->save();
                $personalAccessToken = $legacyToken;
            }
        }

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
     * 撤销令牌（保留记录）
     */
    public function revokeToken(int $id, int $userId): bool
    {
        $token = $this->findTokenById($id, $userId);
        if (!$token) {
            return false;
        }

        $token->revoke();
        return true;
    }

    /**
     * 记录令牌使用日志
     */
    public function logTokenUsage(PersonalAccessToken $token, string $endpoint, $ip = null, $userAgent = null)
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
