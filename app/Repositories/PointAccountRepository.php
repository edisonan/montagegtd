<?php

namespace App\Repositories;

use App\Models\PointAccount;

class PointAccountRepository {

    public function getByUserId(int $userId) {
        return PointAccount::where('user_id', $userId)->first();
    }

    public function create(int $userId) {
        return PointAccount::create([
            'user_id' => $userId,
        ]);
    }
}
