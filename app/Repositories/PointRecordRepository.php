<?php

namespace App\Repositories;

use App\Models\PointRecord;

class PointRecordRepository {

    public function create(
        int $userId,
        string $pointType,
        int $changeAmount,
        int $balanceAfter,
        string $sourceType,
        $sourceId,
        string $description
    ) {
        return PointRecord::create([
            'user_id'       => $userId,
            'point_type'    => $pointType,
            'change_amount' => $changeAmount,
            'balance_after' => $balanceAfter,
            'source_type'   => $sourceType,
            'source_id'     => $sourceId,
            'description'   => $description,
        ]);
    }

    public function getByUserId(int $userId) {
        return PointRecord::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
