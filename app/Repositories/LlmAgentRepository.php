<?php

namespace App\Repositories;

use App\Models\LlmAgent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class LlmAgentRepository
{
    protected $model;

    public function __construct(LlmAgent $model)
    {
        $this->model = $model;
    }

    /**
     * 获取智能体列表
     */
    public function getAgentsList(array $filters = [], int $perPage = 15, bool $withTrashed = false)
    {
        try {
            $query = $this->buildQueryWithFilters($filters, $withTrashed);

            return $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@getAgentsList error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取用户的智能体列表
     */
    public function getUserAgents(int $userId, array $filters = [], int $perPage = 15, bool $withTrashed = false)
    {
        try {
            $query = $this->buildQueryWithFilters($filters, $withTrashed);
            $query = $query->where('user_id', $userId);

            return $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@getUserAgents error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取公开的智能体列表
     */
    public function getPublicAgents(array $filters = [], int $perPage = 15)
    {
        try {
            $query = $this->buildQueryWithFilters($filters, false);
            $query = $query->where('is_public', true)
                ->where('is_active', true);

            return $query->orderBy('favorite_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@getPublicAgents error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 根据ID获取智能体
     */
    public function getAgentById(int $id, bool $withTrashed = false)
    {
        try {
            $query = $withTrashed ? $this->model->withTrashed() : $this->model->newQuery();
            
            return $query->with(['user', 'model'])->find($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@getAgentById error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建智能体
     */
    public function createAgent(array $data)
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@createAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新智能体
     */
    public function updateAgent(int $id, array $data)
    {
        try {
            $agent = $this->model->find($id);
            if ($agent) {
                $agent->update($data);
            }
            return $agent;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@updateAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除智能体
     */
    public function deleteAgent(int $id, bool $force = false)
    {
        try {
            $agent = $this->model->find($id);
            if ($agent) {
                if ($force) {
                    return $agent->forceDelete();
                } else {
                    return $agent->delete();
                }
            }
            return false;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@deleteAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 恢复已删除的智能体
     */
    public function restoreAgent(int $id)
    {
        try {
            $agent = $this->model->withTrashed()->find($id);
            if ($agent) {
                return $agent->restore();
            }
            return false;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@restoreAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 增加使用次数
     */
    public function incrementUsageCount(int $id)
    {
        try {
            $agent = $this->model->find($id);
            if ($agent) {
                $agent->increment('usage_count');
                $agent->touch('last_used_at'); // 更新最后使用时间
            }
            return $agent;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@incrementUsageCount error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 增加收藏次数
     */
    public function incrementFavoriteCount(int $id)
    {
        try {
            $agent = $this->model->find($id);
            if ($agent) {
                return $agent->increment('favorite_count');
            }
            return false;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@incrementFavoriteCount error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 减少收藏次数
     */
    public function decrementFavoriteCount(int $id)
    {
        try {
            $agent = $this->model->find($id);
            if ($agent) {
                return $agent->decrement('favorite_count');
            }
            return false;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@decrementFavoriteCount error: ' . $e->getMessage());
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
                    $subQuery->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (isset($filters['is_public'])) {
                $query->where('is_public', $filters['is_public']);
            }

            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }

            if (isset($filters['builtin_slug'])) {
                $query->where('builtin_slug', $filters['builtin_slug']);
            }

            return $query;
        } catch (Exception $e) {
            \Log::error('LlmAgentRepository@buildQueryWithFilters error: ' . $e->getMessage());
            throw $e;
        }
    }
}