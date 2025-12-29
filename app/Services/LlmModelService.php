<?php

namespace App\Services;

use App\Repositories\LlmModelRepository;

class LlmModelService
{
    protected $repository;

    public function __construct(LlmModelRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取所有模型列表
     */
    public function getAllModels($withTrashed = false, $userId = null)
    {
        return $this->repository->getAllModels($withTrashed, $userId);
    }

    /**
     * 根据ID获取单个模型（仅限用户自己的）
     */
    public function getModelByIdForUser($id, $userId, $withTrashed = false)
    {
        $query = $this->repository->getModelById($id, $withTrashed);
        
        if ($query && $query->user_id == $userId) {
            return $query;
        }
        
        return null;
    }

    /**
     * 获取用户可用的模型列表（用户自己的 + 全局的）
     */
    public function getUserAvailableModels($userId, $withTrashed = false)
    {
        return $this->repository->getUserAvailableModels($userId, $withTrashed);
    }

    /**
     * 根据ID获取模型
     */
    public function getModelById($id, $withTrashed = false)
    {
        return $this->repository->getModelById($id, $withTrashed);
    }

    /**
     * 根据供应商ID获取模型列表
     */
    public function getModelsByProviderId($providerId, $withTrashed = false)
    {
        return $this->repository->getModelsByProviderId($providerId, $withTrashed);
    }

    /**
     * 创建模型
     */
    public function createModel(array $data)
    {
        // 验证必需字段
        if (empty($data['provider_id']) || empty($data['name']) || empty($data['model_type'])) {
            throw new \Exception('供应商ID、名称和模型类型为必填项');
        }

        // 检查模型名称在供应商下是否已存在
        $existing = $this->repository->getModelsByProviderId($data['provider_id'])
                                    ->where('name', $data['name'])
                                    ->first();
        if ($existing) {
            throw new \Exception('该供应商下已存在同名模型');
        }

        // 如果用户已认证，自动添加user_id
        if (auth()->check() && !isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $this->repository->createModel($data);
    }

    /**
     * 更新模型
     */
    public function updateModel($id, array $data)
    {
        // 检查是否存在
        $model = $this->repository->getModelById($id);
        if (!$model) {
            throw new \Exception('模型不存在');
        }

        return $this->repository->updateModel($id, $data);
    }

    /**
     * 删除模型
     */
    public function deleteModel($id, $force = false)
    {
        // 检查是否存在
        $model = $this->repository->getModelById($id);
        if (!$model) {
            throw new \Exception('模型不存在');
        }

        // 检查是否有使用记录，避免删除有关联数据的模型
        if ($model->usageLogs()->count() > 0) {
            throw new \Exception('无法删除有关联使用记录的模型');
        }

        return $this->repository->deleteModel($id, $force);
    }

    /**
     * 恢复已软删除的模型
     */
    public function restoreModel($id)
    {
        return $this->repository->restoreModel($id);
    }
}