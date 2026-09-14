<?php

namespace App\Repositories;

use App\Models\BriefingV2Page;

/**
 * 文章简报 v2 结果 Repository
 *
 * @author edison.an
 */
class BriefingV2PageRepository
{
    public function findById($id)
    {
        return BriefingV2Page::where('id', $id)->first();
    }

    public function paginateByUserId($userId, $perPage = 20, $configId = null)
    {
        $query = BriefingV2Page::where('user_id', $userId);
        if (!empty($configId) && (int)$configId > 0) {
            $query->where('config_id', (int)$configId);
        }
        return $query->orderBy('generated_at', 'desc')->paginate($perPage);
    }

    public function latestByConfigId($configId)
    {
        return BriefingV2Page::where('config_id', $configId)
            ->where('status', 'success')
            ->orderBy('generated_at', 'desc')
            ->first();
    }

    public function countByConfigId($configId)
    {
        return BriefingV2Page::where('config_id', $configId)
            ->where('status', 'success')
            ->count();
    }

    public function create(array $data)
    {
        return BriefingV2Page::create($data);
    }

    public function destroy($id, $userId)
    {
        $page = $this->findById($id);
        if (!$page || (int)$page->user_id !== (int)$userId) {
            return false;
        }
        $page->delete();
        return true;
    }
}