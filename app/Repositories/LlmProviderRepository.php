<?php

namespace App\Repositories;

use App\Models\LlmProvider;

class LlmProviderRepository
{
    /**
     * 获取所有供应商列表
     */
    public function getAllProviders($withTrashed = false, $userId = null)
    {
        $query = LlmProvider::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('priority', 'desc')->orderBy('name')->get();
    }

    /**
     * 获取用户可用的供应商列表（用户自己的 + 全局的）
     */
    public function getUserAvailableProviders($userId, $withTrashed = false)
    {
        $query = LlmProvider::query()
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                   ->orWhereNull('user_id');
            });
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('priority', 'desc')->orderBy('name')->get();
    }

    /**
     * 根据ID获取供应商
     */
    public function getProviderById($id, $withTrashed = false)
    {
        $query = LlmProvider::where('id', $id);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->first();
    }

    /**
     * 创建供应商
     */
    public function createProvider(array $data)
    {
        return LlmProvider::create($data);
    }

    /**
     * 更新供应商
     */
    public function updateProvider($id, array $data)
    {
        $provider = LlmProvider::where('id', $id)->first();
        if ($provider) {
            $provider->update($data);
        }
        return $provider;
    }

    /**
     * 删除供应商
     */
    public function deleteProvider($id, $force = false)
    {
        $provider = LlmProvider::where('id', $id)->first();
        if ($provider) {
            if ($force) {
                return $provider->forceDelete();
            } else {
                return $provider->delete();
            }
        }
        return false;
    }

    /**
     * 恢复已软删除的供应商
     */
    public function restoreProvider($id)
    {
        return LlmProvider::withTrashed()->where('id', $id)->restore();
    }
}