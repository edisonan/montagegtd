<?php

namespace App\Repositories;

use App\Models\BriefingConfig;

/**
 * 文章简报配置 Repository
 *
 * @author edison.an
 */
class BriefingConfigRepository
{
    public function findById($id)
    {
        return BriefingConfig::where('id', $id)->first();
    }

    public function findEnabledByUserId($userId)
    {
        return BriefingConfig::where('user_id', $userId)
            ->where('enabled', true)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findFirstEnabledForUser($userId)
    {
        return BriefingConfig::where('user_id', $userId)
            ->where('enabled', true)
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * 查找到达定时时间的配置（按 Asia/Shanghai 定时时间匹配）
     */
    public function findDueConfigs($nowMinutes, $limit = 50)
    {
        return BriefingConfig::where('enabled', true)
            ->where('schedule_time', $nowMinutes)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * 查找所有已启用配置（带分页），用于人工管理
     */
    public function paginateByUserId($userId, $perPage = 20)
    {
        return BriefingConfig::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return BriefingConfig::create($data);
    }

    public function update($id, array $data)
    {
        $config = $this->findById($id);
        if (!$config) {
            return null;
        }
        $config->update($data);
        return $config->fresh();
    }

    public function updateOrCreateForUser($userId, array $data)
    {
        // 若存在同 id（编辑），则复用；否则新建
        if (!empty($data['id'])) {
            $config = $this->findById($data['id']);
            if ($config && (int)$config->user_id === (int)$userId) {
                $config->update($data);
                return $config->fresh();
            }
        }

        return $this->create(array_merge($data, array('user_id' => $userId)));
    }

    public function destroy($id, $userId)
    {
        $config = $this->findById($id);
        if (!$config || (int)$config->user_id !== (int)$userId) {
            return false;
        }
        $config->delete();
        return true;
    }
}
