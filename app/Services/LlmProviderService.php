<?php

namespace App\Services;

use App\Repositories\LlmProviderRepository;

class LlmProviderService
{
    protected $repository;

    public function __construct(LlmProviderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取所有供应商列表
     */
    public function getAllProviders($withTrashed = false, $userId = null)
    {
        return $this->repository->getAllProviders($withTrashed, $userId);
    }

    /**
     * 根据ID获取单个供应商（仅限用户自己的）
     */
    public function getProviderByIdForUser($id, $userId, $withTrashed = false)
    {
        $query = $this->repository->getProviderById($id, $withTrashed);
        
        if ($query && $query->user_id == $userId) {
            return $query;
        }
        
        return null;
    }

    /**
     * 获取用户可用的供应商列表（用户自己的 + 全局的）
     */
    public function getUserAvailableProviders($userId, $withTrashed = false)
    {
        return $this->repository->getUserAvailableProviders($userId, $withTrashed);
    }

    /**
     * 根据ID获取供应商
     */
    public function getProviderById($id, $withTrashed = false)
    {
        return $this->repository->getProviderById($id, $withTrashed);
    }

    /**
     * 创建供应商
     */
    public function createProvider(array $data)
    {
        // 验证必需字段
        if (empty($data['name']) || empty($data['slug']) || empty($data['api_type'])) {
            throw new \Exception('名称、标识符和API类型为必填项');
        }

        // 检查slug是否已存在
        $existing = $this->repository->getProviderById($data['slug']);
        if ($existing) {
            throw new \Exception('供应商标识符已存在');
        }

        // 如果用户已认证，自动添加user_id
        if (auth()->check() && !isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $this->repository->createProvider($data);
    }

    /**
     * 更新供应商
     */
    public function updateProvider($id, array $data)
    {
        // 检查是否存在
        $provider = $this->repository->getProviderById($id);
        if (!$provider) {
            throw new \Exception('供应商不存在');
        }

        return $this->repository->updateProvider($id, $data);
    }

    /**
     * 删除供应商
     */
    public function deleteProvider($id, $force = false)
    {
        // 检查是否存在
        $provider = $this->repository->getProviderById($id);
        if (!$provider) {
            throw new \Exception('供应商不存在');
        }

        // 检查是否有相关模型或凭据，避免删除有关联数据的供应商
        if ($provider->models()->count() > 0) {
            throw new \Exception('无法删除有关联模型的供应商，请先删除相关模型');
        }

        return $this->repository->deleteProvider($id, $force);
    }

    /**
     * 恢复已软删除的供应商
     */
    public function restoreProvider($id)
    {
        return $this->repository->restoreProvider($id);
    }
}
