<?php

namespace App\Services;

use App\Models\PointAccount;
use App\Models\StudyCheckin;
use App\Models\Plan;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StudyService
{
    const STUDY_MODE = 3;

    protected $pointRecordService;

    public function __construct(PointRecordService $pointRecordService)
    {
        $this->pointRecordService = $pointRecordService;
    }

    public function getOverview(int $userId, string $date = ''): array
    {
        $currentDate = $this->resolveDate($date);
        $weekStart = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekDays = array();
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $weekDays[] = array(
                'date' => $day->format('Y-m-d'),
                'day_label' => '周' . array('一', '二', '三', '四', '五', '六', '日')[$i],
                'day_of_month' => $day->format('m-d'),
                'is_today' => $day->isSameDay(Carbon::today()),
                'is_selected' => $day->isSameDay($currentDate),
            );
        }

        $tasks = Task::where('user_id', $userId)
            ->where('mode', self::STUDY_MODE)
            ->where(function ($q) use ($currentDate) {
                $q->whereDate('study_scheduled_date', $currentDate->format('Y-m-d'))
                    ->orWhere(function ($q2) use ($currentDate) {
                        $q2->whereNull('study_scheduled_date')
                            ->whereDate('planned_start_time', $currentDate->format('Y-m-d'));
                    });
            })
            ->orderBy('status', 'asc')
            ->orderBy('planned_start_time', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $taskIds = $tasks->pluck('id')->all();
        $checkins = empty($taskIds) ? collect() : StudyCheckin::where('user_id', $userId)
            ->whereIn('task_id', $taskIds)
            ->whereDate('checkin_date', $currentDate->format('Y-m-d'))
            ->get()
            ->keyBy('task_id');

        $taskList = array();
        $estimatedMinutesTotal = 0;
        $learnedMinutesTotal = 0;
        $goldRewardTotal = 0;
        $energyRewardTotal = 0;
        foreach ($tasks as $task) {
            $checkin = isset($checkins[$task->id]) ? $checkins[$task->id] : null;
            $estimatedMinutes = 0;
            if (!empty($task->planned_start_time) && !empty($task->planned_end_time)) {
                try {
                    $startAt = Carbon::parse((string)$task->planned_start_time);
                    $endAt = Carbon::parse((string)$task->planned_end_time);
                    if ($endAt->gt($startAt)) {
                        $estimatedMinutes = (int)$endAt->diffInMinutes($startAt);
                    }
                } catch (\Throwable $e) {
                    $estimatedMinutes = 0;
                }
            }
            if ($estimatedMinutes < 0) {
                $estimatedMinutes = 0;
            }
            $taskList[] = array(
                'id' => (int)$task->id,
                'name' => (string)$task->name,
                'content' => (string)($task->content ?: ''),
                'status' => (int)$task->status,
                'planned_start_time' => $task->planned_start_time ? (string)$task->planned_start_time : '',
                'estimated_minutes' => $estimatedMinutes,
                'sp_points' => (int)($task->study_sp_points ?: 0),
                'is_checked_in' => $checkin ? true : false,
                'checkin' => $checkin ? array(
                    'id' => (int)$checkin->id,
                    'content' => (string)($checkin->content ?: ''),
                    'audio_path' => (string)($checkin->audio_path ?: ''),
                    'image_path' => (string)($checkin->image_path ?: ''),
                    'video_path' => (string)($checkin->video_path ?: ''),
                ) : null,
            );
            $estimatedMinutesTotal += $estimatedMinutes;
            if ($checkin) {
                $learnedMinutesTotal += $estimatedMinutes;
                $goldRewardTotal += (int)($task->study_sp_points ?: 0);
                $energyRewardTotal += 1;
            }
        }

        return array(
            'selected_date' => $currentDate->format('Y-m-d'),
            'week_label' => $currentDate->format('Y') . '年第' . $currentDate->weekOfYear . '周',
            'days' => $weekDays,
            'dashboard' => array(
                'learned_minutes' => $learnedMinutesTotal,
                'estimated_minutes' => $estimatedMinutesTotal,
                'gold_reward' => $goldRewardTotal,
                'energy_reward' => $energyRewardTotal,
            ),
            'tasks' => $taskList,
        );
    }

    public function getFocusTask(int $userId, int $taskId): Task
    {
        return Task::where('id', $taskId)
            ->where('user_id', $userId)
            ->where('mode', self::STUDY_MODE)
            ->firstOrFail();
    }

    public function listPlans(int $userId): array
    {
        $plans = Plan::where('user_id', $userId)
            ->where('plan_type', 'study')
            ->orderBy('status', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        if ($plans->isEmpty()) {
            return array();
        }

        $planIds = $plans->pluck('id')->all();
        $statsRows = DB::table('tasks')
            ->selectRaw('study_source_task_id as plan_id, COUNT(*) as task_total, SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as task_done')
            ->where('mode', self::STUDY_MODE)
            ->whereIn('study_source_task_id', $planIds)
            ->groupBy('study_source_task_id')
            ->get()
            ->keyBy('plan_id');

        $list = array();
        foreach ($plans as $plan) {
            $stats = isset($statsRows[$plan->id]) ? $statsRows[$plan->id] : null;
            $list[] = $this->serializePlan($plan, $stats);
        }

        return $list;
    }

    public function getPlanDetail(Plan $plan): array
    {
        $stats = DB::table('tasks')
            ->selectRaw('COUNT(*) as task_total, SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as task_done')
            ->where('mode', self::STUDY_MODE)
            ->where('study_source_task_id', (int)$plan->id)
            ->first();

        $tasks = Task::where('mode', self::STUDY_MODE)
            ->where('study_source_task_id', (int)$plan->id)
            ->orderBy('planned_start_time', 'asc')
            ->orderBy('id', 'asc')
            ->limit(30)
            ->get();

        $taskList = array();
        foreach ($tasks as $task) {
            $taskList[] = array(
                'id' => (int)$task->id,
                'name' => (string)$task->name,
                'status' => (int)$task->status,
                'planned_start_time' => !empty($task->planned_start_time) ? (string)$task->planned_start_time : '',
                'study_scheduled_date' => !empty($task->study_scheduled_date) ? (string)$task->study_scheduled_date : '',
            );
        }

        return array(
            'plan' => $this->serializePlan($plan, $stats),
            'tasks' => $taskList,
        );
    }

    public function setPlanStatus(Plan $plan, int $status): Plan
    {
        $plan->status = $status === 1 ? 1 : 0;
        $plan->save();
        return $plan->fresh();
    }

    public function deletePlan(Plan $plan): array
    {
        return DB::transaction(function () use ($plan) {
            $planId = (int)$plan->id;
            $plan->status = 0;
            $plan->save();
            $plan->delete();

            return array(
                'plan_id' => $planId,
                'deleted_pending_tasks' => 0,
            );
        });
    }

    public function createPlan(int $userId, array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));
        $startAt = Carbon::parse((string)($data['start_time'] ?? ''));
        $repeatType = strtolower((string)($data['repeat_type'] ?? 'none'));
        $repeatDays = (array)($data['repeat_days'] ?? array());
        $spPoints = max(0, (int)($data['sp_points'] ?? 0));
        $contentMode = $this->normalizePlanMode((string)($data['content_mode'] ?? 'fixed'));
        $estimatedTimeMode = $this->normalizePlanMode((string)($data['estimated_time_mode'] ?? 'fixed'));
        $estimatedMinutes = $this->clampEstimatedMinutes((int)($data['estimated_minutes'] ?? 0));
        $contentBySlot = $this->normalizeContentMap((array)($data['content_by_slot'] ?? array()));
        $estimatedBySlot = $this->normalizeEstimatedMap((array)($data['estimated_by_slot'] ?? array()));
        $meta = array(
            'seed_date' => $startAt->format('Y-m-d'),
            'content_mode' => $contentMode,
            'estimated_time_mode' => $estimatedTimeMode,
            'fixed_estimated_minutes' => $estimatedMinutes,
            'content_by_slot' => $contentBySlot,
            'estimated_by_slot' => $estimatedBySlot,
        );

        $plan = new Plan();
        $plan->user_id = $userId;
        $plan->name = $name;
        $plan->plan_type = 'study';
        $plan->content = $content;
        $plan->start_time = $startAt->format('Y-m-d H:i:s');
        $plan->repeat_type = $this->normalizeRepeatType($repeatType);
        $plan->repeat_days = empty($repeatDays) ? '' : implode(',', $repeatDays);
        $plan->repeat_meta = json_encode($meta, JSON_UNESCAPED_UNICODE);
        $plan->sp_points = $spPoints;
        $plan->status = 1;
        $plan->save();

        $generated = $this->generatePlanTasks($plan, $startAt->format('Y-m-d'), $startAt->copy()->addDays(14)->format('Y-m-d'));

        return array(
            'plan' => $plan->fresh(),
            'count' => count($generated),
            'tasks' => $generated,
        );
    }

    public function generatePlanTasks(Plan $plan, string $dateFrom, string $dateTo): array
    {
        $from = $this->resolveDate($dateFrom)->startOfDay();
        $to = $this->resolveDate($dateTo)->startOfDay();
        if ($from->gt($to)) {
            $swap = $from;
            $from = $to;
            $to = $swap;
        }

        $startAt = Carbon::parse((string)$plan->start_time);
        $occurrences = $this->buildOccurrencesBetween($startAt, (string)$plan->repeat_type, (string)$plan->repeat_days, $from, $to);
        if (empty($occurrences)) {
            return array();
        }

        $planMeta = $this->parsePlanMeta($plan);
        $created = array();
        DB::transaction(function () use ($plan, $startAt, $occurrences, $planMeta, &$created) {
            foreach ($occurrences as $occurrence) {
                $date = $occurrence['date'];
                $slotKey = $occurrence['slot_key'];
                $scheduled = $date->format('Y-m-d');
                $exists = Task::where('user_id', (int)$plan->user_id)
                    ->where('mode', self::STUDY_MODE)
                    ->where('study_source_task_id', (int)$plan->id)
                    ->whereDate('study_scheduled_date', $scheduled)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $plannedTime = $date->copy()->setTime($startAt->hour, $startAt->minute, 0);
                $resolvedContent = $this->resolveContentByMeta($plan, $planMeta, $slotKey);
                $estimatedMinutes = $this->resolveEstimatedMinutesByMeta($planMeta, $slotKey);
                $task = new Task();
                $task->user_id = (int)$plan->user_id;
                $task->name = (string)$plan->name;
                $task->content = $resolvedContent;
                $task->priority = 1;
                $task->status = 1;
                $task->mode = self::STUDY_MODE;
                $task->planned_start_time = $plannedTime->format('Y-m-d H:i:s');
                if ($estimatedMinutes > 0) {
                    $task->planned_end_time = $plannedTime->copy()->addMinutes($estimatedMinutes)->format('Y-m-d H:i:s');
                }
                $task->study_scheduled_date = $scheduled;
                $task->study_repeat_type = (string)$plan->repeat_type;
                $task->study_repeat_days = (string)($plan->repeat_days ?: '');
                $task->study_repeat_meta = (string)($plan->repeat_meta ?: '');
                $task->study_sp_points = (int)$plan->sp_points;
                $task->study_source_task_id = (int)$plan->id;
                $task->save();
                $created[] = $task;
            }

            $lastOccurrence = $occurrences[count($occurrences) - 1];
            $plan->last_generated_date = $lastOccurrence['date']->format('Y-m-d');
            $plan->save();
        });

        return $created;
    }

    public function generateTasksForUser(int $userId, string $dateFrom = '', string $dateTo = ''): array
    {
        $from = !empty($dateFrom) ? $this->resolveDate($dateFrom) : Carbon::today();
        $to = !empty($dateTo) ? $this->resolveDate($dateTo) : $from->copy()->addDays(14);
        $plans = Plan::where('user_id', $userId)
            ->where('plan_type', 'study')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $total = 0;
        $byPlan = array();
        foreach ($plans as $plan) {
            $tasks = $this->generatePlanTasks($plan, $from->format('Y-m-d'), $to->format('Y-m-d'));
            $count = count($tasks);
            $byPlan[] = array(
                'plan_id' => (int)$plan->id,
                'plan_name' => (string)$plan->name,
                'generated' => $count,
            );
            $total += $count;
        }

        return array(
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'total_generated' => $total,
            'plans' => $byPlan,
        );
    }

    public function listCheckins(int $userId, string $dateFrom = '', string $dateTo = '', int $page = 1, int $pageSize = 20): array
    {
        $page = max(1, $page);
        $pageSize = min(100, max(1, $pageSize));

        $query = StudyCheckin::query()
            ->where('user_id', $userId);

        if (!empty($dateFrom)) {
            $query->whereDate('checkin_date', '>=', $this->resolveDate($dateFrom)->format('Y-m-d'));
        }
        if (!empty($dateTo)) {
            $query->whereDate('checkin_date', '<=', $this->resolveDate($dateTo)->format('Y-m-d'));
        }

        $paginator = $query
            ->with(array('task' => function ($q) {
                $q->select(array('id', 'name', 'study_scheduled_date', 'planned_start_time'));
            }))
            ->orderBy('checkin_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($pageSize, array('*'), 'page', $page);

        $rows = array();
        foreach ($paginator->items() as $item) {
            $task = $item->task;
            $rows[] = array(
                'id' => (int)$item->id,
                'task_id' => (int)$item->task_id,
                'checkin_date' => !empty($item->checkin_date) ? (string)$item->checkin_date : '',
                'content' => (string)($item->content ?: ''),
                'audio_path' => (string)($item->audio_path ?: ''),
                'image_path' => (string)($item->image_path ?: ''),
                'video_path' => (string)($item->video_path ?: ''),
                'task_name' => $task ? (string)$task->name : '',
                'study_scheduled_date' => $task && !empty($task->study_scheduled_date) ? (string)$task->study_scheduled_date : '',
                'planned_start_time' => $task && !empty($task->planned_start_time) ? (string)$task->planned_start_time : '',
                'created_at' => !empty($item->created_at) ? (string)$item->created_at : '',
            );
        }

        return array(
            'items' => $rows,
            'pagination' => array(
                'current_page' => (int)$paginator->currentPage(),
                'per_page' => (int)$paginator->perPage(),
                'total' => (int)$paginator->total(),
                'last_page' => (int)$paginator->lastPage(),
            ),
        );
    }

    public function checkin(
        int $userId,
        int $taskId,
        string $date,
        string $content = '',
        UploadedFile $audio = null,
        UploadedFile $image = null,
        UploadedFile $video = null
    ): array {
        return DB::transaction(function () use ($userId, $taskId, $date, $content, $audio, $image, $video) {
            $task = $this->getFocusTask($userId, $taskId);
            $checkinDate = $this->resolveDate($date)->format('Y-m-d');

            $audioPath = '';
            $imagePath = '';
            $videoPath = '';
            if ($audio) {
                $audioPath = $this->storeUpload($audio, 'audio');
            }
            if ($image) {
                $imagePath = $this->storeUpload($image, 'image');
            }
            if ($video) {
                $videoPath = $this->storeUpload($video, 'video');
            }

            $checkin = StudyCheckin::where('user_id', $userId)
                ->where('task_id', $taskId)
                ->whereDate('checkin_date', $checkinDate)
                ->first();
            $isNewCheckin = empty($checkin);
            if (!$checkin) {
                $checkin = new StudyCheckin();
                $checkin->user_id = $userId;
                $checkin->task_id = $taskId;
                $checkin->checkin_date = $checkinDate;
            }
            $checkin->content = $content;
            if (!empty($audioPath)) {
                $checkin->audio_path = $audioPath;
            }
            if (!empty($imagePath)) {
                $checkin->image_path = $imagePath;
            }
            if (!empty($videoPath)) {
                $checkin->video_path = $videoPath;
            }
            $checkin->save();

            if ($task->status != 2) {
                $task->status = 2;
                $task->save();
            }

            $spGranted = ($isNewCheckin ? max(0, (int)$task->study_sp_points) : 0);
            $account = PointAccount::where('user_id', $userId)->lockForUpdate()->first();
            if (!$account) {
                $account = PointAccount::create(array('user_id' => $userId));
            }
            if (!isset($account->sp_balance)) {
                $account->sp_balance = 0;
            }
            if ($spGranted > 0) {
                $account->sp_balance = (int)$account->sp_balance + $spGranted;
                $account->save();
                $this->pointRecordService->record(
                    $userId,
                    'SP',
                    $spGranted,
                    (int)$account->sp_balance,
                    'study_checkin',
                    $taskId,
                    '学习任务打卡奖励'
                );
            }

            return array(
                'checkin' => $checkin,
                'task' => $task->fresh(),
                'sp_granted' => $spGranted,
                'sp_balance' => (int)$account->sp_balance,
            );
        });
    }

    protected function resolveDate(string $date = ''): Carbon
    {
        if (empty($date)) {
            return Carbon::today();
        }
        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return Carbon::today();
        }
    }

    protected function normalizeRepeatType(string $repeatType): string
    {
        return in_array($repeatType, array('none', 'daily', 'weekly', 'ebbinghaus'), true)
            ? $repeatType
            : 'none';
    }

    protected function serializePlan(Plan $plan, $stats = null): array
    {
        $repeatDays = array_values(array_filter(explode(',', (string)($plan->repeat_days ?: ''))));
        $meta = $this->parsePlanMeta($plan);
        return array(
            'id' => (int)$plan->id,
            'name' => (string)$plan->name,
            'content' => (string)($plan->content ?: ''),
            'status' => (int)$plan->status,
            'start_time' => !empty($plan->start_time) ? (string)$plan->start_time : '',
            'repeat_type' => (string)($plan->repeat_type ?: 'none'),
            'repeat_days' => $repeatDays,
            'sp_points' => (int)($plan->sp_points ?: 0),
            'content_mode' => (string)$meta['content_mode'],
            'estimated_time_mode' => (string)$meta['estimated_time_mode'],
            'estimated_minutes' => (int)$meta['fixed_estimated_minutes'],
            'content_by_slot' => (array)$meta['content_by_slot'],
            'estimated_by_slot' => (array)$meta['estimated_by_slot'],
            'last_generated_date' => !empty($plan->last_generated_date) ? (string)$plan->last_generated_date : '',
            'created_at' => !empty($plan->created_at) ? (string)$plan->created_at : '',
            'updated_at' => !empty($plan->updated_at) ? (string)$plan->updated_at : '',
            'task_total' => (int)($stats->task_total ?? 0),
            'task_done' => (int)($stats->task_done ?? 0),
        );
    }

    protected function buildOccurrencesBetween(
        Carbon $startAt,
        string $repeatType,
        string $repeatDays,
        Carbon $from,
        Carbon $to
    ): array {
        $repeatType = $this->normalizeRepeatType($repeatType);
        $seed = $startAt->copy()->startOfDay();
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();
        $dates = array();

        if ($to->lt($seed)) {
            return $dates;
        }

        if ($repeatType === 'none') {
            if ($seed->between($from, $to, true)) {
                $dates[] = array('date' => $seed, 'slot_key' => 'default');
            }
            return $dates;
        }

        if ($repeatType === 'daily') {
            $cursor = $seed->copy();
            if ($cursor->lt($from)) {
                $cursor = $from->copy();
            }
            while ($cursor->lte($to)) {
                if ($cursor->gte($seed)) {
                    $dates[] = array('date' => $cursor->copy(), 'slot_key' => 'default');
                }
                $cursor->addDay();
            }
            return $dates;
        }

        if ($repeatType === 'weekly') {
            $selected = array_filter(explode(',', (string)$repeatDays));
            if (empty($selected)) {
                $selected = array((string)$seed->dayOfWeekIso);
            }
            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                if ($cursor->gte($seed) && in_array((string)$cursor->dayOfWeekIso, $selected, true)) {
                    $dates[] = array('date' => $cursor->copy(), 'slot_key' => (string)$cursor->dayOfWeekIso);
                }
                $cursor->addDay();
            }
            return $dates;
        }

        $intervals = array(0, 1, 2, 4, 7, 15, 30);
        foreach ($intervals as $d) {
            $candidate = $seed->copy()->addDays($d);
            if ($candidate->between($from, $to, true)) {
                $dates[] = array('date' => $candidate, 'slot_key' => 'd' . $d);
            }
        }
        return $dates;
    }

    protected function parsePlanMeta(Plan $plan): array
    {
        $meta = array();
        if (!empty($plan->repeat_meta)) {
            $parsed = json_decode((string)$plan->repeat_meta, true);
            if (is_array($parsed)) {
                $meta = $parsed;
            }
        }

        return array(
            'content_mode' => $this->normalizePlanMode((string)($meta['content_mode'] ?? 'fixed')),
            'estimated_time_mode' => $this->normalizePlanMode((string)($meta['estimated_time_mode'] ?? 'fixed')),
            'fixed_estimated_minutes' => $this->clampEstimatedMinutes((int)($meta['fixed_estimated_minutes'] ?? 0)),
            'content_by_slot' => $this->normalizeContentMap((array)($meta['content_by_slot'] ?? array())),
            'estimated_by_slot' => $this->normalizeEstimatedMap((array)($meta['estimated_by_slot'] ?? array())),
        );
    }

    protected function normalizePlanMode(string $mode): string
    {
        return in_array($mode, array('fixed', 'by_repeat'), true) ? $mode : 'fixed';
    }

    protected function clampEstimatedMinutes(int $minutes): int
    {
        if ($minutes < 0) {
            return 0;
        }
        if ($minutes > 1440) {
            return 1440;
        }
        return $minutes;
    }

    protected function normalizeContentMap(array $contentBySlot): array
    {
        $out = array();
        foreach ($contentBySlot as $k => $v) {
            $key = trim((string)$k);
            $value = trim((string)$v);
            if ($key === '' || $value === '') {
                continue;
            }
            $out[$key] = substr($value, 0, 3000);
        }
        return $out;
    }

    protected function normalizeEstimatedMap(array $estimatedBySlot): array
    {
        $out = array();
        foreach ($estimatedBySlot as $k => $v) {
            $key = trim((string)$k);
            if ($key === '') {
                continue;
            }
            $out[$key] = $this->clampEstimatedMinutes((int)$v);
        }
        return $out;
    }

    protected function resolveContentByMeta(Plan $plan, array $meta, string $slotKey): string
    {
        $base = trim((string)($plan->content ?: ''));
        if ($meta['content_mode'] !== 'by_repeat') {
            return $base;
        }
        if (isset($meta['content_by_slot'][$slotKey]) && trim((string)$meta['content_by_slot'][$slotKey]) !== '') {
            return trim((string)$meta['content_by_slot'][$slotKey]);
        }
        if (isset($meta['content_by_slot']['default']) && trim((string)$meta['content_by_slot']['default']) !== '') {
            return trim((string)$meta['content_by_slot']['default']);
        }
        return $base;
    }

    protected function resolveEstimatedMinutesByMeta(array $meta, string $slotKey): int
    {
        if ($meta['estimated_time_mode'] === 'by_repeat') {
            if (isset($meta['estimated_by_slot'][$slotKey])) {
                return $this->clampEstimatedMinutes((int)$meta['estimated_by_slot'][$slotKey]);
            }
            if (isset($meta['estimated_by_slot']['default'])) {
                return $this->clampEstimatedMinutes((int)$meta['estimated_by_slot']['default']);
            }
        }
        return $this->clampEstimatedMinutes((int)$meta['fixed_estimated_minutes']);
    }

    protected function storeUpload(UploadedFile $file, string $type): string
    {
        $dir = rtrim(config('app.storage_path'), '/') . '/study_checkins/' . date('Ymd') . '/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $ext = strtolower((string)$file->getClientOriginalExtension());
        $name = $type . '_' . date('His') . '_' . mt_rand(1000, 9999) . ($ext ? ('.' . $ext) : '');
        $file->move($dir, $name);

        return 'study_checkins/' . date('Ymd') . '/' . $name;
    }
}
