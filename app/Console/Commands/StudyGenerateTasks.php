<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\StudyService;
use Illuminate\Console\Command;

class StudyGenerateTasks extends Command
{
    protected $signature = 'study:generate-tasks {--days=14 : Generate days ahead}';
    protected $description = 'Generate study tasks from active study plans';

    protected $studyService;

    public function __construct(StudyService $studyService)
    {
        parent::__construct();
        $this->studyService = $studyService;
    }

    public function handle()
    {
        $days = max(1, (int)$this->option('days'));
        $from = date('Y-m-d');
        $to = date('Y-m-d', strtotime('+' . $days . ' day'));

        $userIds = Plan::where('plan_type', 'study')
            ->where('status', 1)
            ->distinct()
            ->pluck('user_id')
            ->all();
        $total = 0;

        foreach ($userIds as $userId) {
            $result = $this->studyService->generateTasksForUser((int)$userId, $from, $to);
            $count = (int)($result['total_generated'] ?? 0);
            $total += $count;
            $this->line('user=' . $userId . ' generated=' . $count);
        }

        $this->info('done. total_generated=' . $total . ' from=' . $from . ' to=' . $to);
        return 0;
    }
}
