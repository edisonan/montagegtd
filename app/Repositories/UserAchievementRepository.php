<?php

namespace App\Repositories;

use App\Models\UserAchievement;

class UserAchievementRepository {

    public function exists(int $userId, string $code): bool {
        return UserAchievement::where('user_id', $userId)
            ->where('achievement_code', $code)
            ->exists();
    }

    public function create(int $userId, string $code) {
        return UserAchievement::create([
            'user_id'          => $userId,
            'achievement_code' => $code,
            'achieved_at'      => now(),
        ]);
    }
}
