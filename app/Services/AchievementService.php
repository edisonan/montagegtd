<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\DailySummary;
use App\Models\Note;
use App\Models\Pomo;
use App\Models\Task;
use App\Repositories\AchievementRepository;
use App\Repositories\UserAchievementRepository;

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

    public function getUserAchievements(int $userId) {
        $list = $this->achievementRepository->getUserAchievementView($userId);
        $metrics = $this->getUserMetrics($userId);

        foreach ($list as $item) {
            $code = isset($item->code) ? (string) $item->code : '';
            $progress = $this->resolveProgressByCode($code, $metrics);

            $item->progress_current = (int) $progress['current'];
            $item->progress_target = (int) $progress['target'];
            $item->progress_percent = (int) $progress['percent'];
            $item->progress_text = (string) $progress['text'];

            if (isset($item->category) && (string)$item->category === 'badge') {
                $item->badge_claimable = (bool) $progress['claimable'];
                $item->badge_requirement_text = (string) $progress['requirement'];
            } else {
                $item->badge_claimable = false;
                $item->badge_requirement_text = '';
            }
        }

        return $list;
    }

    public function claimBadge(int $userId, string $achievementCode) {
        $achievement = $this->achievementRepository->getByCode($achievementCode);

        if ($achievement->category !== 'badge') {
            throw new CustomException('该成就不是可手动领取的勋章');
        }

        if ($this->userAchievementRepository->exists($userId, $achievementCode)) {
            return;
        }

        $metrics = $this->getUserMetrics($userId);
        $progress = $this->resolveProgressByCode($achievementCode, $metrics);
        if (empty($progress['claimable'])) {
            $tip = !empty($progress['requirement']) ? $progress['requirement'] : '未满足领取条件';
            throw new CustomException('勋章领取条件未达成：' . $tip);
        }

        $this->achievementGrantService->grant($userId, $achievement);
    }

    protected function getUserMetrics(int $userId): array
    {
        return array(
            'task_done_count' => (int) Task::where('user_id', $userId)->where('status', 2)->count(),
            'pomo_done_count' => (int) Pomo::where('user_id', $userId)->where('status', 2)->count(),
            'daily_summary_count' => (int) DailySummary::where('user_id', $userId)->where('status', '!=', 2)->count(),
            'note_count' => (int) Note::where('user_id', $userId)->count(),
        );
    }

    protected function resolveProgressByCode(string $code, array $metrics): array
    {
        $taskDone = (int) $metrics['task_done_count'];
        $pomoDone = (int) $metrics['pomo_done_count'];
        $summaryDone = (int) $metrics['daily_summary_count'];
        $noteCount = (int) $metrics['note_count'];

        $defaults = array(
            'current' => 0,
            'target' => 0,
            'percent' => 0,
            'text' => '',
            'claimable' => false,
            'requirement' => '',
        );

        switch ($code) {
            case 'achievement_first_task_done':
                return $this->simpleProgress($taskDone, 1, '完成 1 个任务');
            case 'achievement_task_done_10':
                return $this->simpleProgress($taskDone, 10, '完成 10 个任务');
            case 'achievement_task_done_100':
                return $this->simpleProgress($taskDone, 100, '完成 100 个任务');
            case 'achievement_pomo_done_10':
                return $this->simpleProgress($pomoDone, 10, '完成 10 个番茄');
            case 'achievement_pomo_done_100':
                return $this->simpleProgress($pomoDone, 100, '完成 100 个番茄');
            case 'achievement_daily_summary_7':
                return $this->simpleProgress($summaryDone, 7, '完成 7 次日报');
            case 'achievement_daily_summary_30':
                return $this->simpleProgress($summaryDone, 30, '完成 30 次日报');
            case 'achievement_note_50':
                return $this->simpleProgress($noteCount, 50, '创建 50 条笔记');

            case 'badge_early_bird':
                return $this->simpleProgress($summaryDone, 7, '完成 7 次日报');
            case 'badge_deep_work':
                return $this->simpleProgress($pomoDone, 50, '完成 50 个番茄');
            case 'badge_consistency':
                return $this->compositeProgress(
                    array($taskDone, $summaryDone),
                    array(50, 14),
                    array('任务 50', '日报 14')
                );
            case 'badge_knowledge_collector':
                return $this->simpleProgress($noteCount, 100, '创建 100 条笔记');
            case 'badge_focus_master':
                return $this->simpleProgress($pomoDone, 300, '完成 300 个番茄');
            case 'badge_productivity_architect':
                return $this->compositeProgress(
                    array($taskDone, $pomoDone, $summaryDone, $noteCount),
                    array(200, 200, 30, 100),
                    array('任务 200', '番茄 200', '日报 30', '笔记 100')
                );
            default:
                return $defaults;
        }
    }

    protected function simpleProgress(int $current, int $target, string $requirement): array
    {
        $currentDisplay = min($current, $target);
        $percent = $target > 0 ? min(100, (int) floor(($current / $target) * 100)) : 0;

        return array(
            'current' => $currentDisplay,
            'target' => $target,
            'percent' => $percent,
            'text' => $currentDisplay . '/' . $target,
            'claimable' => $current >= $target,
            'requirement' => $requirement,
        );
    }

    protected function compositeProgress(array $currents, array $targets, array $labels): array
    {
        $parts = array();
        $totalPercent = 0;
        $metCount = 0;
        $n = count($targets);

        for ($i = 0; $i < $n; $i++) {
            $cur = isset($currents[$i]) ? (int) $currents[$i] : 0;
            $tar = isset($targets[$i]) ? (int) $targets[$i] : 1;
            $label = isset($labels[$i]) ? (string) $labels[$i] : ('条件' . ($i + 1));
            $percent = $tar > 0 ? min(100, (int) floor(($cur / $tar) * 100)) : 0;
            $totalPercent += $percent;
            if ($cur >= $tar) {
                $metCount++;
            }
            $parts[] = $label . '（' . min($cur, $tar) . '/' . $tar . '）';
        }

        return array(
            'current' => $metCount,
            'target' => $n,
            'percent' => $n > 0 ? (int) floor($totalPercent / $n) : 0,
            'text' => $metCount . '/' . $n . ' 项达成',
            'claimable' => $n > 0 ? ($metCount === $n) : false,
            'requirement' => implode('，', $parts),
        );
    }
}
