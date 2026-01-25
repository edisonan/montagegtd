<?php

namespace App\Services;

use App\Repositories\LlmAgentRepository;
use App\Repositories\LlmAgentVersionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class LlmAgentService
{
    protected $repository;
    protected $versionRepository;

    public function __construct(LlmAgentRepository $repository, LlmAgentVersionRepository $versionRepository = null)
    {
        $this->repository = $repository;
        $this->versionRepository = $versionRepository;
    }

    /**
     * 获取智能体列表
     */
    public function getAgentsList(array $filters = [], int $perPage = 15, bool $withTrashed = false)
    {
        try {
            return $this->repository->getAgentsList($filters, $perPage, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@getAgentsList error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取用户的智能体列表
     */
    public function getUserAgents(int $userId, array $filters = [], int $perPage = 15, bool $withTrashed = false)
    {
        try {
            return $this->repository->getUserAgents($userId, $filters, $perPage, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@getUserAgents error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取公开的智能体列表
     */
    public function getPublicAgents(array $filters = [], int $perPage = 15)
    {
        try {
            return $this->repository->getPublicAgents($filters, $perPage);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@getPublicAgents error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取指定智能体
     */
    public function getAgentById(int $id, bool $withTrashed = false)
    {
        try {
            return $this->repository->getAgentById($id, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@getAgentById error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建智能体
     */
    public function createAgent(array $data)
    {
        try {
            // 设置默认值：普通用户创建的智能体默认为私有
            $data['is_public'] = $data['is_public'] ?? 0;
            $data['builtin_slug'] = $data['builtin_slug'] ?? null;
            
            // 如果不是管理员，则确保builtin_slug为null
            if (!auth()->check() || !in_array(auth()->user()->email ?? '', config('admin.super_users', []))) {
                $data['builtin_slug'] = null;
                $data['is_public'] = 0; // 普通用户不能创建公开智能体
            }
            
            return $this->repository->createAgent($data);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@createAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建智能体及其初始草稿版本
     */
    public function createAgentWithDraftVersion(array $agentData, array $versionData)
    {
        try {
            if (!$this->versionRepository) {
                throw new Exception('Version repository not available');
            }

            return DB::transaction(function () use ($agentData, $versionData) {
                // 创建智能体
                $agent = $this->repository->createAgent([
                    'user_id' => $agentData['user_id'],
                    'name' => $agentData['name'],
                    'description' => $agentData['description'] ?? '',
                    'avatar' => $agentData['avatar'] ?? null,
                    'is_public' => $agentData['is_public'] ?? 0,
                    'is_active' => $agentData['is_active'] ?? 1,
                    'builtin_slug' => $agentData['builtin_slug'] ?? null
                ]);

                // 创建初始草稿版本
                $version = $this->versionRepository->createAgentVersion([
                    'agent_id' => $agent->id,
                    'version_name' => 'draft',
                    'version_number' => 1,
                    'model_id' => $versionData['model_id'],
                    'system_prompt' => $versionData['system_prompt'] ?? '',
                    'temperature' => $versionData['temperature'] ?? 0.7,
                    'top_p' => $versionData['top_p'] ?? 0.9,
                    'max_tokens' => $versionData['max_tokens'] ?? null,
                    'context_length' => $versionData['context_length'] ?? 4000,
                    'tools_config' => $versionData['tools_config'] ?? null,
                    'is_default' => true, // 草稿版本作为默认版本
                    'is_active' => true,
                    'created_by' => $versionData['created_by'],
                    'change_log' => $versionData['change_log'] ?? 'Initial draft version'
                ]);

                // 更新智能体的 current_version_id
                $agent->update(['current_version_id' => $version->id]);

                return $agent;
            });
        } catch (Exception $e) {
            \Log::error('LlmAgentService@createAgentWithDraftVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新智能体
     */
    public function updateAgent(int $id, array $data)
    {
        try {
            // 如果不是管理员，则不允许更新builtin_slug和is_public
            if (!auth()->check() || !in_array(auth()->user()->email ?? '', config('admin.super_users', []))) {
                unset($data['builtin_slug']);
                unset($data['is_public']);
            }
            
            return $this->repository->updateAgent($id, $data);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@updateAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除智能体
     */
    public function deleteAgent(int $id, bool $force = false)
    {
        try {
            return $this->repository->deleteAgent($id, $force);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@deleteAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 恢复已删除的智能体
     */
    public function restoreAgent(int $id)
    {
        try {
            return $this->repository->restoreAgent($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@restoreAgent error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 增加使用次数
     */
    public function incrementUsageCount(int $id)
    {
        try {
            return $this->repository->incrementUsageCount($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@incrementUsageCount error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 增加收藏次数
     */
    public function incrementFavoriteCount(int $id)
    {
        try {
            return $this->repository->incrementFavoriteCount($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@incrementFavoriteCount error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 减少收藏次数
     */
    public function decrementFavoriteCount(int $id)
    {
        try {
            return $this->repository->decrementFavoriteCount($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentService@decrementFavoriteCount error: ' . $e->getMessage());
            throw $e;
        }
    }
}