<?php

namespace App\Repositories;

use App\Models\LlmSession;
use Illuminate\Support\Facades\Auth;

class LlmSessionRepository
{
    protected $model;

    public function __construct(LlmSession $model)
    {
        $this->model = $model;
    }

    /**
     * 获取用户的所有会话
     *
     * @param int|null $userId
     * @param array $with
     * @param string $orderBy
     * @param string $orderDirection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserSessions($userId = null, $with = [], $orderBy = 'last_message_at', $orderDirection = 'desc')
    {
        $userId = $userId ?: Auth::id();
        
        $query = $this->model->where('user_id', $userId);
        
        if (!empty($with)) {
            $query = $query->with($with);
        }
        
        return $query->orderBy('is_pinned', 'desc')
                     ->orderBy($orderBy, $orderDirection)
                     ->get();
    }

    /**
     * 根据ID查找会话
     *
     * @param int $id
     * @param int|null $userId
     * @param array $with
     * @return LlmSession|null
     */
    public function findById($id, $userId = null, $with = [])
    {
        $userId = $userId ?: Auth::id();
        
        $query = $this->model->where('id', $id)->where('user_id', $userId);
        
        if (!empty($with)) {
            $query = $query->with($with);
        }
        
        return $query->first();
    }

    /**
     * 根据UUID查找会话
     *
     * @param string $uuid
     * @param int|null $userId
     * @param array $with
     * @return LlmSession|null
     */
    public function findByUuid($uuid, $userId = null, $with = [])
    {
        $userId = $userId ?: Auth::id();
        
        $query = $this->model->where('uuid', $uuid)->where('user_id', $userId);
        
        if (!empty($with)) {
            $query = $query->with($with);
        }
        
        return $query->first();
    }

    /**
     * 创建新会话
     *
     * @param array $attributes
     * @return LlmSession
     */
    public function create(array $attributes)
    {
        $attributes['user_id'] = $attributes['user_id'] ?? Auth::id();
        return $this->model->create($attributes);
    }

    /**
     * 更新会话
     *
     * @param int $id
     * @param array $attributes
     * @param int|null $userId
     * @return bool
     */
    public function update($id, array $attributes, $userId = null)
    {
        $session = $this->findById($id, $userId);
        if (!$session) {
            return false;
        }
        
        return $session->update($attributes);
    }

    /**
     * 删除会话
     *
     * @param int $id
     * @param int|null $userId
     * @return bool
     */
    public function delete($id, $userId = null)
    {
        $session = $this->findById($id, $userId);
        if (!$session) {
            return false;
        }
        
        return $session->delete();
    }

    /**
     * 恢复软删除的会话
     *
     * @param int $id
     * @param int|null $userId
     * @return bool
     */
    public function restore($id, $userId = null)
    {
        $userId = $userId ?: Auth::id();
        
        $session = $this->model->onlyTrashed()
                              ->where('id', $id)
                              ->where('user_id', $userId)
                              ->first();
        
        if (!$session) {
            return false;
        }
        
        return $session->restore();
    }

    /**
     * 永久删除会话
     *
     * @param int $id
     * @param int|null $userId
     * @return bool
     */
    public function forceDelete($id, $userId = null)
    {
        $session = $this->findById($id, $userId);
        if (!$session) {
            return false;
        }
        
        return $session->forceDelete();
    }

    /**
     * 获取固定会话数量
     *
     * @param int|null $userId
     * @return int
     */
    public function getPinnedSessionsCount($userId = null)
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                           ->where('is_pinned', true)
                           ->count();
    }

    /**
     * 获取会话总数
     *
     * @param int|null $userId
     * @return int
     */
    public function getTotalSessionsCount($userId = null)
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)->count();
    }

    /**
     * 获取最近活动的会话
     *
     * @param int|null $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentSessions($userId = null, $limit = 10)
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                           ->orderBy('last_message_at', 'desc')
                           ->limit($limit)
                           ->get();
    }
}