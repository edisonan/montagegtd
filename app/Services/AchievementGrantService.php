<?php

namespace App\Services;

use App\Models\Achievement;
use App\Repositories\UserAchievementRepository;

class AchievementGrantService {

    protected $userAchievementRepository;
    protected $pointAccountService;
    protected $pointRecordService;

    public function __construct(
        UserAchievementRepository $userAchievementRepository,
        PointAccountService $pointAccountService,
        PointRecordService $pointRecordService
    ) {
        $this->userAchievementRepository = $userAchievementRepository;
        $this->pointAccountService       = $pointAccountService;
        $this->pointRecordService        = $pointRecordService;
    }

    /**
     * 发放成就
     */
    public function grant(int $userId, Achievement $achievement) {
        $this->userAchievementRepository->create(
            $userId,
            $achievement->code
        );

        if ($achievement->point_value > 0) {
            $account = $this->pointAccountService->increaseGP(
                $userId,
                $achievement->point_value
            );

            $this->pointRecordService->record(
                $userId,
                'GP',
                $achievement->point_value,
                $account->gp_balance,
                'achievement',
                $achievement->id,
                '获得成就：' . $achievement->name
            );
        }
    }
}
