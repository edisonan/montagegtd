<?php

namespace App\Repositories;

use App\Models\BehaviorEvent;

class BehaviorEventRepository {

    public function create(
        int $userId,
        string $eventType,
        string $eventKey = null,
        int $eventValue = 1
    ) {
        return BehaviorEvent::create([
            'user_id'     => $userId,
            'event_type'  => $eventType,
            'event_key'   => $eventKey,
            'event_value' => $eventValue,
            'occurred_at' => now(),
        ]);
    }
}
