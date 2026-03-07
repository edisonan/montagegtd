<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserNotification;

class UserNotificationService
{
    const TYPE_ACHIEVEMENT_UNLOCKED = 'achievement_unlocked';

    public function createAchievementUnlocked(int $userId, Achievement $achievement)
    {
        return UserNotification::create(array(
            'user_id' => $userId,
            'type' => self::TYPE_ACHIEVEMENT_UNLOCKED,
            'title' => '解锁新成就',
            'content' => '你已解锁成就：' . $achievement->name,
            'data' => array(
                'achievement_code' => $achievement->code,
                'achievement_name' => $achievement->name,
                'achievement_icon' => $achievement->icon,
                'point_value' => (int)$achievement->point_value,
                'category' => $achievement->category,
            ),
        ));
    }

    public function getRecentByUser(int $userId, int $limit = 20)
    {
        $safeLimit = max(1, min(100, $limit));

        return UserNotification::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit($safeLimit)
            ->get();
    }

    public function getUnreadCount(int $userId): int
    {
        return (int) UserNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markRead(int $userId, int $notificationId): bool
    {
        $notification = UserNotification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        return true;
    }

    public function markAllRead(int $userId): int
    {
        return (int) UserNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(array(
                'read_at' => now(),
            ));
    }
}

