<?php

namespace App\Repositories;

use App\Models\DigestWhitelistUser;

class DigestWhitelistUserRepository
{
    public function findByUserId($userId)
    {
        return DigestWhitelistUser::where('user_id', $userId)->first();
    }

    public function isEnabledForUser($userId)
    {
        $record = DigestWhitelistUser::where('user_id', $userId)
            ->where('enabled', true)
            ->first();

        if (!$record) {
            return false;
        }

        if ($record->expires_at && $record->expires_at->lt(now())) {
            return false;
        }

        return true;
    }

    public function create(array $data)
    {
        return DigestWhitelistUser::create($data);
    }

    public function updateOrCreateByUserId($userId, array $data)
    {
        return DigestWhitelistUser::updateOrCreate(
            array('user_id' => $userId),
            $data
        );
    }

    public function deleteByUserId($userId)
    {
        return DigestWhitelistUser::where('user_id', $userId)->delete();
    }

    public function paginate(array $filters = array(), $perPage = 20)
    {
        $query = DigestWhitelistUser::with('user')->orderBy('created_at', 'desc');

        if (isset($filters['enabled']) && $filters['enabled'] !== '') {
            $query->where('enabled', (bool) $filters['enabled']);
        }

        return $query->paginate($perPage);
    }
}
