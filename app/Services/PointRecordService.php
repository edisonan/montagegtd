<?php

namespace App\Services;

use App\Repositories\PointRecordRepository;

class PointRecordService {

    protected $pointRecordRepository;

    public function __construct(PointRecordRepository $pointRecordRepository) {
        $this->pointRecordRepository = $pointRecordRepository;
    }

    /**
     * 记录积分流水
     */
    public function record(
        int $userId,
        string $pointType,
        int $changeAmount,
        int $balanceAfter,
        string $sourceType,
        $sourceId = null,
        string $description = ''
    ) {
        return $this->pointRecordRepository->create(
            $userId,
            $pointType,
            $changeAmount,
            $balanceAfter,
            $sourceType,
            $sourceId,
            $description
        );
    }

    /**
     * 获取用户积分流水
     */
    public function getUserPointRecords(int $userId) {
        return $this->pointRecordRepository->getByUserId($userId);
    }
}
