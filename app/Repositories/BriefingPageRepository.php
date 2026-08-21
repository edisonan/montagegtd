<?php

namespace App\Repositories;

use App\Models\BriefingPage;

/**
 * 文章简报结果 Repository
 *
 * @author edison.an
 */
class BriefingPageRepository
{
    public function findById($id)
    {
        return BriefingPage::where('id', $id)->first();
    }

    public function paginateByUserId($userId, $perPage = 20, $configId = null)
    {
        $query = BriefingPage::where('user_id', $userId);
        if (!empty($configId) && (int)$configId > 0) {
            $query->where('config_id', (int)$configId);
        }
        return $query->orderBy('generated_at', 'desc')->paginate($perPage);
    }

    public function latestByConfigId($configId)
    {
        return BriefingPage::where('config_id', $configId)
            ->where('status', 'success')
            ->orderBy('generated_at', 'desc')
            ->first();
    }

    public function create(array $data)
    {
        return BriefingPage::create($data);
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
