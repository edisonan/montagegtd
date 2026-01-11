<?php

namespace App\Services;

use App\Repositories\LlmProviderCredentialRepository;

class LlmProviderCredentialService
{
    protected $repository;

    public function __construct(LlmProviderCredentialRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取所有凭据列表
     */
    public function getAllCredentials($withTrashed = false, $userId = null)
    {
        return $this->repository->getAllCredentials($withTrashed, $userId);
    }

    /**
     * 根据ID获取单个凭据（仅限用户自己的）
     */
    public function getCredentialByIdForUser($id, $userId, $withTrashed = false)
    {
        $query = $this->repository->getCredentialById($id, $withTrashed);
        
        if ($query && $query->user_id == $userId) {
            return $query;
        }
        
        return null;
    }

    /**
     * 获取用户可用的凭据列表（用户自己的 + 全局的）
     */
    public function getUserAvailableCredentials($userId, $withTrashed = false)
    {
        return $this->repository->getUserAvailableCredentials($userId, $withTrashed);
    }

    /**
     * 根据ID获取凭据
     */
    public function getCredentialById($id, $withTrashed = false)
    {
        return $this->repository->getCredentialById($id, $withTrashed);
    }

    /**
     * 根据供应商ID获取凭据列表
     */
    public function getCredentialsByProviderId($providerId, $withTrashed = false)
    {
        return $this->repository->getCredentialsByProviderId($providerId, $withTrashed);
    }

    /**
     * 获取供应商的默认凭据
     */
    public function getDefaultCredentialByProviderId($providerId)
    {
        return $this->repository->getDefaultCredentialByProviderId($providerId);
    }

    /**
     * 创建凭据
     */
    public function createCredential(array $data)
    {
        // 验证必需字段
        if (empty($data['provider_id']) || empty($data['name']) || empty($data['api_key'])) {
            throw new \Exception('供应商ID、凭据名称和API Key为必填项');
        }

        // 检查是否已有默认凭据，如果设置为默认则取消其他默认凭据
        if (!empty($data['is_default']) && $data['is_default']) {
            $this->repository->getCredentialsByProviderId($data['provider_id'])
                            ->where('is_default', true)
                            ->each(function ($credential) {
                                $credential->update(['is_default' => false]);
                            });
        }

        // 如果用户已认证，自动添加user_id
        if (auth()->check() && !isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $this->repository->createCredential($data);
    }

    /**
     * 更新凭据
     */
    public function updateCredential($id, array $data)
    {
        // 检查是否存在
        $credential = $this->repository->getCredentialById($id);
        if (!$credential) {
            throw new \Exception('凭据不存在');
        }

        // 如果设置为默认凭据，取消同供应商下其他默认凭据
        if (isset($data['is_default']) && $data['is_default']) {
            $this->repository->getCredentialsByProviderId($credential->provider_id)
                            ->where('is_default', true)
                            ->where('id', '!=', $id)
                            ->each(function ($cred) {
                                $cred->update(['is_default' => false]);
                            });
        }

        return $this->repository->updateCredential($id, $data);
    }

    /**
     * 删除凭据
     */
    public function deleteCredential($id, $force = false)
    {
        // 检查是否存在
        $credential = $this->repository->getCredentialById($id);
        if (!$credential) {
            throw new \Exception('凭据不存在');
        }

        // 检查是否有使用记录，避免删除有关联数据的凭据
        if ($credential->usageLogs()->count() > 0) {
            throw new \Exception('无法删除有关联使用记录的凭据');
        }

        return $this->repository->deleteCredential($id, $force);
    }

    /**
     * 恢复已软删除的凭据
     */
    public function restoreCredential($id)
    {
        return $this->repository->restoreCredential($id);
    }

    /**
     * 获取用户默认凭据
     */
    public function getUserDefaultCredentialByModel($model)
    {
        return $this->repository->getUserDefaultCredentialByModel($model);
    }
}