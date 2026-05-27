<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\DailySummary;
use App\Models\Note;
use App\Models\Focus;
use App\Models\Task;
use App\Repositories\UserAchievementRepository;

class AchievementAutoUnlockService
{
    protected $userAchievementRepository;
    protected $achievementGrantService;
    protected $userNotificationService;

    public function __construct(
        UserAchievementRepository $userAchievementRepository,
        AchievementGrantService $achievementGrantService,
        UserNotificationService $userNotificationService
    ) {
        $this->userAchievementRepository = $userAchievementRepository;
        $this->achievementGrantService = $achievementGrantService;
        $this->userNotificationService = $userNotificationService;
    }

    public function evaluateForUser(int $userId): array
    {
        if ($userId <= 0) {
            return array('granted' => 0, 'codes' => array());
        }

        $taskDoneCount = (int)Task::where('user_id', $userId)->where('status', 2)->count();
        $focusDoneCount = (int)Focus::where('user_id', $userId)->where('status', 2)->count();
        $dailySummaryCount = (int)DailySummary::where('user_id', $userId)->where('status', '!=', 2)->count();
        $noteCount = (int)Note::where('user_id', $userId)->count();

        $conditions = array(
            'achievement_first_task_done' => $taskDoneCount >= 1,
            'achievement_task_done_10' => $taskDoneCount >= 10,
            'achievement_task_done_100' => $taskDoneCount >= 100,
            'achievement_focus_done_10' => $focusDoneCount >= 10,
            'achievement_focus_done_100' => $focusDoneCount >= 100,
            'achievement_daily_summary_7' => $dailySummaryCount >= 7,
            'achievement_daily_summary_30' => $dailySummaryCount >= 30,
            'achievement_note_50' => $noteCount >= 50,
        );

        $grantCodes = array();

        foreach ($conditions as $code => $met) {
            if (!$met) {
                continue;
            }
            if ($this->userAchievementRepository->exists($userId, $code)) {
                continue;
            }

            $achievement = Achievement::where('code', $code)
                ->where('category', 'achievement')
                ->where('enabled', 1)
                ->first();

            if (!$achievement) {
                continue;
            }

            $this->achievementGrantService->grant($userId, $achievement);
            $this->userNotificationService->createAchievementUnlocked($userId, $achievement);
            $grantCodes[] = $code;
        }

        return array(
            'granted' => count($grantCodes),
            'codes' => $grantCodes,
        );
    }
}
