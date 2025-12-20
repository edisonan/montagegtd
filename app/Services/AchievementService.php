<?php

namespace App\Services;

use App\Repositories\AchievementRepository;
use App\Repositories\UserAchievementRepository;
use App\Services\AchievementGrantService;

class AchievementService {

    protected $achievementRepository;
    protected $userAchievementRepository;
    protected $achievementGrantService;

    public function __construct(
        AchievementRepository $achievementRepository,
        UserAchievementRepository $userAchievementRepository,
        AchievementGrantService $achievementGrantService
    ) {
        $this->achievementRepository      = $achievementRepository;
        $this->userAchievementRepository  = $userAchievementRepository;
        $this->achievementGrantService    = $achievementGrantService;
    }

    /**
     * 获取用户成就列表（含是否已达成）
     */
    public function getUserAchievements(int $userId) {
        return $this->achievementRepository->getUserAchievementView($userId);
    }

    /**
     * 手动领取勋章
     */
    public function claimBadge(int $userId, string $achievementCode) {
        $achievement = $this->achievementRepository->getByCode($achievementCode);

        if ($achievement->category !== 'badge') {
            throw new \Exception('非法领取类型');
        }

        if ($this->userAchievementRepository->exists($userId, $achievementCode)) {
            return;
        }

        $this->achievementGrantService->grant($userId, $achievement);
    }
}
