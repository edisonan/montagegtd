<?php

namespace App\Repositories;

use App\Models\UserDigestProfile;

class UserDigestProfileRepository
{
    public function findById($id)
    {
        return UserDigestProfile::with('user')->where('id', $id)->first();
    }

    public function findEnabledByUserId($userId)
    {
        return UserDigestProfile::where('user_id', $userId)
            ->where('enabled', true)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function create(array $data)
    {
        return UserDigestProfile::create($data);
    }

    public function update($id, array $data)
    {
        $profile = UserDigestProfile::where('id', $id)->first();
        if ($profile) {
            $profile->update($data);
        }

        return $profile;
    }

    public function updateOrCreateEnabledByUserId($userId, array $data)
    {
        $profile = $this->findEnabledByUserId($userId);
        if ($profile) {
            $profile->update($data);
            return $profile;
        }

        $data['user_id'] = $userId;
        if (!isset($data['enabled'])) {
            $data['enabled'] = true;
        }

        return UserDigestProfile::create($data);
    }

    public function findDueProfiles($frequency, $beforeTime, $limit = 50)
    {
        return UserDigestProfile::with('user')
            ->where('enabled', true)
            ->where('frequency', $frequency)
            ->where(function ($query) use ($beforeTime) {
                $query->whereNull('last_generated_at')
                    ->orWhere('last_generated_at', '<=', $beforeTime);
            })
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }
}
