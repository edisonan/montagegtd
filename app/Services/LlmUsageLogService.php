<?php

namespace App\Services;

use App\Repositories\LlmUsageLogRepository;

class LlmUsageLogService
{
    protected $repository;

    public function __construct(LlmUsageLogRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取所有使用记录
     */
    public function getAllUsageLogs($withTrashed = false)
    {
        return $this->repository->getAllUsageLogs($withTrashed);
    }

    /**
     * 根据ID获取使用记录
     */
    public function getUsageLogById($id, $withTrashed = false)
    {
        return $this->repository->getUsageLogById($id, $withTrashed);
    }

    /**
     * 根据条件获取使用记录分页
     */
    public function getUsageLogsByFilters($filters = [], $perPage = 20, $userId = null)
    {
        return $this->repository->getUsageLogsByFilters($filters, $perPage, $userId);
    }

    /**
     * 创建使用记录
     */
    public function createUsageLog(array $data)
    {
        // 如果用户已认证，自动添加user_id
        if (auth()->check() && !isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }
        
        return $this->repository->createUsageLog($data);
    }

    /**
     * 更新使用记录
     */
    public function updateUsageLog($id, array $data)
    {
        // 检查是否存在
        $log = $this->repository->getUsageLogById($id);
        if (!$log) {
            throw new \Exception('使用记录不存在');
        }

        return $this->repository->updateUsageLog($id, $data);
    }

    /**
     * 删除使用记录
     */
    public function deleteUsageLog($id, $force = false)
    {
        // 检查是否存在
        $log = $this->repository->getUsageLogById($id);
        if (!$log) {
            throw new \Exception('使用记录不存在');
        }

        return $this->repository->deleteUsageLog($id, $force);
    }

    /**
     * 获取统计信息
     */
    public function getUsageStatistics($filters = [])
    {
        return $this->repository->getUsageStatistics($filters);
    }
}