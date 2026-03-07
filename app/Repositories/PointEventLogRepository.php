<?php

namespace App\Repositories;

use App\Models\PointEventLog;

class PointEventLogRepository
{
    public function existsByEventKey(int $userId, string $eventKey): bool
    {
        return PointEventLog::where('user_id', $userId)
            ->where('event_key', $eventKey)
            ->exists();
    }

    public function countTodayByRule(int $userId, int $ruleId, string $dateYmd): int
    {
        return (int)PointEventLog::where('user_id', $userId)
            ->where('rule_id', $ruleId)
            ->where('occurred_on', $dateYmd)
            ->count();
    }

    public function create(array $attributes)
    {
        return PointEventLog::create($attributes);
    }
}

