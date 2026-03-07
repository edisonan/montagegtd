<?php

namespace App\Services;

use App\Repositories\PointEventLogRepository;
use App\Repositories\PointRuleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointGrantService
{
    protected $pointRuleRepository;
    protected $pointEventLogRepository;
    protected $pointAccountService;
    protected $pointRecordService;
    protected $achievementAutoUnlockService;

    public function __construct(
        PointRuleRepository $pointRuleRepository,
        PointEventLogRepository $pointEventLogRepository,
        PointAccountService $pointAccountService,
        PointRecordService $pointRecordService,
        AchievementAutoUnlockService $achievementAutoUnlockService
    ) {
        $this->pointRuleRepository = $pointRuleRepository;
        $this->pointEventLogRepository = $pointEventLogRepository;
        $this->pointAccountService = $pointAccountService;
        $this->pointRecordService = $pointRecordService;
        $this->achievementAutoUnlockService = $achievementAutoUnlockService;
    }

    public function grantByEvent(
        int $userId,
        string $eventType,
        string $sourceType,
        $sourceId = null,
        array $extra = array()
    ): array {
        $rules = $this->pointRuleRepository->getEnabledByEventType($eventType);
        if (empty($rules) || $rules->count() === 0) {
            return array('granted' => false, 'count' => 0);
        }

        $today = date('Y-m-d');
        $grantCount = 0;

        foreach ($rules as $rule) {
            $baseEventKey = !empty($extra['event_key'])
                ? (string)$extra['event_key']
                : $eventType . ':' . $sourceType . ':' . (string)($sourceId ?: 0);
            $eventKey = $baseEventKey . ':rule:' . $rule->id;

            if ($this->pointEventLogRepository->existsByEventKey($userId, $eventKey)) {
                continue;
            }

            if ((int)$rule->daily_max_grants > 0) {
                $todayCount = $this->pointEventLogRepository->countTodayByRule($userId, (int)$rule->id, $today);
                if ($todayCount >= (int)$rule->daily_max_grants) {
                    continue;
                }
            }

            try {
                DB::transaction(function () use ($userId, $eventType, $sourceType, $sourceId, $rule, $eventKey, $today) {
                    $account = $this->pointAccountService->getOrCreateAccount($userId);

                    $pointType = strtoupper((string)$rule->point_type);
                    $grantValue = (int)$rule->point_value;
                    if ($grantValue <= 0) {
                        return;
                    }

                    if ($pointType === 'AP') {
                        $account->ap_balance += $grantValue;
                        $balanceAfter = (int)$account->ap_balance;
                    } else {
                        $pointType = 'GP';
                        $account->gp_balance += $grantValue;
                        $balanceAfter = (int)$account->gp_balance;
                    }
                    $account->save();

                    $description = $rule->description ?: ('事件奖励：' . $rule->name);
                    $this->pointRecordService->record(
                        $userId,
                        $pointType,
                        $grantValue,
                        $balanceAfter,
                        'event:' . $eventType,
                        $sourceId,
                        $description
                    );

                    $this->pointEventLogRepository->create(array(
                        'user_id' => $userId,
                        'rule_id' => (int)$rule->id,
                        'event_type' => $eventType,
                        'event_key' => $eventKey,
                        'source_type' => $sourceType,
                        'source_id' => $sourceId,
                        'point_type' => $pointType,
                        'granted_points' => $grantValue,
                        'balance_after' => $balanceAfter,
                        'occurred_on' => $today,
                    ));
                });

                $grantCount++;
            } catch (\Throwable $e) {
                Log::warning('point grant failed', array(
                    'user_id' => $userId,
                    'event_type' => $eventType,
                    'rule_id' => $rule->id,
                    'error' => $e->getMessage(),
                ));
            }
        }

        try {
            $this->achievementAutoUnlockService->evaluateForUser($userId);
        } catch (\Throwable $e) {
            Log::warning('auto unlock achievements failed', array(
                'user_id' => $userId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ));
        }

        return array(
            'granted' => $grantCount > 0,
            'count' => $grantCount,
        );
    }
}
