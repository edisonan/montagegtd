<?php

namespace App\Repositories;

use App\Models\LlmProviderCredential;

class LlmProviderCredentialRepository
{
    /**
     * 获取所有凭据列表
     */
    public function getAllCredentials($withTrashed = false, $userId = null)
    {
        $query = LlmProviderCredential::with('provider');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('provider_id')->orderBy('name')->get();
    }

    /**
     * 获取用户可用的凭据列表（用户自己的 + 全局的）
     */
    public function getUserAvailableCredentials($userId, $withTrashed = false)
    {
        $query = LlmProviderCredential::with('provider')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                   ->orWhereNull('user_id');
            });
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('provider_id')->orderBy('name')->get();
    }

    /**
     * 根据ID获取凭据
     */
    public function getCredentialById($id, $withTrashed = false)
    {
        $query = LlmProviderCredential::with('provider')->where('id', $id);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->first();
    }

    /**
     * 根据供应商ID获取凭据列表
     */
    public function getCredentialsByProviderId($providerId, $withTrashed = false)
    {
        $query = LlmProviderCredential::where('provider_id', $providerId);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('is_default', 'desc')->orderBy('name')->get();
    }

    /**
     * 获取供应商的默认凭据
     */
    public function getDefaultCredentialByProviderId($providerId)
    {
        return LlmProviderCredential::where('provider_id', $providerId)
                                   ->where('is_default', true)
                                   ->where('is_active', true)
                                   ->first();
    }

    /**
     * 创建凭据
     */
    public function createCredential(array $data)
    {
        return LlmProviderCredential::create($data);
    }

    /**
     * 更新凭据
     */
    public function updateCredential($id, array $data)
    {
        $credential = LlmProviderCredential::where('id', $id)->first();
        if ($credential) {
            $credential->update($data);
        }
        return $credential;
    }

    /**
     * 删除凭据
     */
    public function deleteCredential($id, $force = false)
    {
        $credential = LlmProviderCredential::where('id', $id)->first();
        if ($credential) {
            if ($force) {
                return $credential->forceDelete();
            } else {
                return $credential->delete();
            }
        }
        return false;
    }

    /**
     * 恢复已软删除的凭据
     */
    public function restoreCredential($id)
    {
        return LlmProviderCredential::withTrashed()->where('id', $id)->restore();
    }
}