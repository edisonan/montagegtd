<?php

namespace App\Repositories;

use App\Models\LlmAgentVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class LlmAgentVersionRepository
{
    protected $model;

    public function __construct(LlmAgentVersion $model)
    {
        $this->model = $model;
    }

    /**
     * 获取智能体版本列表
     */
    public function getAgentVersionsList(int $agentId, array $filters = [], int $perPage = 15, bool $withTrashed = false)
    {
        try {
            $query = $this->buildQueryWithFilters($filters, $withTrashed);
            $query = $query->where('agent_id', $agentId);

            return $query->orderBy('version_number', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@getAgentVersionsList error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的所有版本
     */
    public function getAgentVersions(int $agentId, bool $withTrashed = false)
    {
        try {
            $query = $withTrashed ? $this->model->withTrashed() : $this->model->newQuery();
            return $query->where('agent_id', $agentId)
                ->orderBy('version_number', 'desc')
                ->get();
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@getAgentVersions error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的特定版本
     */
    public function getAgentVersionById(int $id, bool $withTrashed = false)
    {
        try {
            $query = $withTrashed ? $this->model->withTrashed() : $this->model->newQuery();
            return $query->with(['agent', 'model', 'createdBy'])->find($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@getAgentVersionById error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的特定版本通过版本号
     */
    public function getAgentVersionByNumber(int $agentId, int $versionNumber, bool $withTrashed = false)
    {
        try {
            $query = $withTrashed ? $this->model->withTrashed() : $this->model->newQuery();
            return $query->where('agent_id', $agentId)
                ->where('version_number', $versionNumber)
                ->first();
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@getAgentVersionByNumber error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的默认版本
     */
    public function getDefaultVersion(int $agentId)
    {
        try {
            return $this->model->where('agent_id', $agentId)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@getDefaultVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建智能体版本
     */
    public function createAgentVersion(array $data)
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@createAgentVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新智能体版本
     */
    public function updateAgentVersion(int $id, array $data)
    {
        try {
            $version = $this->model->find($id);
            if ($version) {
                $version->update($data);
            }
            return $version;
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@updateAgentVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除智能体版本
     */
    public function deleteAgentVersion(int $id, bool $force = false)
    {
        try {
            $version = $this->model->find($id);
            if ($version) {
                if ($force) {
                    return $version->forceDelete();
                } else {
                    return $version->delete();
                }
            }
            return false;
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@deleteAgentVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 设置为默认版本
     */
    public function setAsDefaultVersion(int $id)
    {
        try {
            $version = $this->model->find($id);
            if ($version) {
                // 将其他版本的is_default设为false
                $this->model->where('agent_id', $version->agent_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
                
                // 设置当前版本为默认版本
                $version->update(['is_default' => true]);
            }
            return $version;
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@setAsDefaultVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 构建带过滤条件的查询
     */
    private function buildQueryWithFilters(array $filters, bool $withTrashed): Builder
    {
        try {
            $query = $withTrashed ? $this->model->withTrashed() : $this->model->newQuery();

            if (!empty($filters['search'])) {
                $query->where(function ($subQuery) use ($filters) {
                    $subQuery->where('version_name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('change_log', 'like', '%' . $filters['search'] . '%');
                });
            }

            if (isset($filters['agent_id'])) {
                $query->where('agent_id', $filters['agent_id']);
            }

            if (isset($filters['is_default'])) {
                $query->where('is_default', $filters['is_default']);
            }

            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }

            return $query;
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionRepository@buildQueryWithFilters error: ' . $e->getMessage());
            throw $e;
        }
    }
}