<?php

namespace App\Repositories;

use App\Models\Achievement;
use Illuminate\Support\Facades\DB;

class AchievementRepository {

    public function getByCode(string $code) {
        return Achievement::where('code', $code)->firstOrFail();
    }

    public function getUserAchievementView(int $userId) {
        return DB::table('achievement as a')
            ->leftJoin('user_achievement as ua', function ($join) use ($userId) {
                $join->on('a.code', '=', 'ua.achievement_code')
                    ->where('ua.user_id', $userId);
            })
            ->where('a.visible', 1)
            ->select(
                'a.*',
                DB::raw('ua.id IS NOT NULL as achieved'),
                'ua.achieved_at'
            )
            ->get();
    }
}
