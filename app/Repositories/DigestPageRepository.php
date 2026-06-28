<?php

namespace App\Repositories;

use App\Models\DigestPage;

class DigestPageRepository
{
    public function create(array $data)
    {
        return DigestPage::create($data);
    }

    public function findById($id)
    {
        return DigestPage::with(array('user', 'profile', 'task'))->where('id', $id)->first();
    }

    public function paginateByUserId($userId, $perPage = 20)
    {
        return DigestPage::where('user_id', $userId)
            ->orderBy('generated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function paginateByFilters(array $filters = array(), $perPage = 20)
    {
        $query = DigestPage::with(array('user', 'profile', 'task'))
            ->orderBy('generated_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['profile_id'])) {
            $query->where('profile_id', $filters['profile_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }
}
