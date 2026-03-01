<?php

namespace App\Services;

use App\Repositories\PersonalAccessTokenRepository;
use App\Models\PersonalAccessToken;

class PersonalAccessTokenService
{
    protected $repository;

    public function __construct(PersonalAccessTokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 创建个人访问令牌
     */
    public function createToken(array $data): array
    {
        $tokenPayload = $this->repository->createToken($data);
        $token = $tokenPayload['token'];

        // 返回令牌信息，但不包含哈希值
        return [
            'id' => $token->id,
            'name' => $token->name,
            'token' => $tokenPayload['plain_text_token'], // 实际的令牌值，只在创建时返回
            'scopes' => $token->scopes,
            'expires_at' => $token->expires_at,
            'created_at' => $token->created_at,
        ];
    }

    /**
     * 获取用户的所有令牌
     */
    public function getUserTokens(int $userId)
    {
        return $this->repository->getUserTokens($userId);
    }

    /**
     * 删除令牌
     */
    public function deleteToken(int $id, int $userId): bool
    {
        return $this->repository->deleteToken($id, $userId);
    }

    /**
     * 撤销令牌（保留记录）
     */
    public function revokeToken(int $id, int $userId): bool
    {
        return $this->repository->revokeToken($id, $userId);
    }

    /**
     * 验证令牌并返回令牌对象
     */
    public function validateToken(string $token)
    {
        return $this->repository->validateToken($token);
    }

    /**
     * 检查令牌是否具有指定权限
     */
    public function tokenCan(PersonalAccessToken $token, string $scope): bool
    {
        return $token->can($scope);
    }

    /**
     * 记录令牌使用日志
     */
    public function logTokenUsage(PersonalAccessToken $token, string $endpoint, $ip = null, $userAgent = null)
    {
        $this->repository->logTokenUsage($token, $endpoint, $ip, $userAgent);
    }
}
