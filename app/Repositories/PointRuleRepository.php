<?php

namespace App\Repositories;

use App\Models\PointRule;

class PointRuleRepository
{
    public function getEnabledByEventType(string $eventType)
    {
        return PointRule::where('event_type', $eventType)
            ->where('enabled', 1)
            ->orderBy('id', 'asc')
            ->get();
    }
}

