<?php

namespace App\Repositories;

use App\Models\LlmModel;

class LlmModelRepository
{
    /**
     * 获取所有模型列表
     */
    public function getAllModels($withTrashed = false, $userId = null)
    {
        $query = LlmModel::with('provider');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('sort_order', 'asc')
                   ->orderBy('provider_id')
                   ->orderBy('name')
                   ->get();
    }

    /**
     * 获取用户可用的模型列表（用户自己的 + 全局的）
     */
    public function getUserAvailableModels($userId, $withTrashed = false)
    {
        $query = LlmModel::with('provider')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                   ->orWhereNull('user_id');
            });
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('sort_order', 'asc')
                   ->orderBy('provider_id')
                   ->orderBy('name')
                   ->get();
    }

    /**
     * 根据ID获取模型
     */
    public function getModelById($id, $withTrashed = false)
    {
        $query = LlmModel::with('provider')->where('id', $id);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->first();
    }

    /**
     * 根据供应商ID获取模型列表
     */
    public function getModelsByProviderId($providerId, $withTrashed = false)
    {
        $query = LlmModel::where('provider_id', $providerId);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('sort_order', 'asc')->orderBy('name')->get();
    }

    /**
     * 创建模型
     */
    public function createModel(array $data)
    {
        return LlmModel::create($data);
    }

    /**
     * 更新模型
     */
    public function updateModel($id, array $data)
    {
        $model = LlmModel::where('id', $id)->first();
        if ($model) {
            $model->update($data);
        }
        return $model;
    }

    /**
     * 删除模型
     */
    public function deleteModel($id, $force = false)
    {
        $model = LlmModel::where('id', $id)->first();
        if ($model) {
            if ($force) {
                return $model->forceDelete();
            } else {
                return $model->delete();
            }
        }
        return false;
    }

    /**
     * 恢复已软删除的模型
     */
    public function restoreModel($id)
    {
        return LlmModel::withTrashed()->where('id', $id)->restore();
    }
}