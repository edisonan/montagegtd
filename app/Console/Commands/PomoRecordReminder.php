<?php

namespace App\Console\Commands;

use App\Services\FocusService;
use Illuminate\Console\Command;

/**
 * 专注记录提醒
 *
 * @author edison.an
 *
 */
class FocusRecordReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'focus_record_reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Focus record Reminder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /**
         *
         * @var FocusService $focusService
         */
        $focusService = app(FocusService::class);

        // 立即（专注结束后 1 分钟内）：保证首个提醒及时，原实现最早也要 5 分钟后才提醒
        $startTime = date('Y-m-d H:i:s', time() - 60);
        $endTime = date('Y-m-d H:i:s', time());
        $focusService->scheduleFocusRecordReminder($startTime, $endTime);

        // 5分钟
        $startTime = date('Y-m-d H:i:s', time() - 300);
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + 60);
        $focusService->scheduleFocusRecordReminder($startTime, $endTime);

        // 30分钟
        $startTime = date('Y-m-d H:i:s', time() - 1800);
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + 60);
        $focusService->scheduleFocusRecordReminder($startTime, $endTime);

        // 60分钟
        $startTime = date('Y-m-d H:i:s', time() - 3600);
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + 60);
        $focusService->scheduleFocusRecordReminder($startTime, $endTime);
    }
}

