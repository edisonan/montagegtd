<?php

namespace App\Console\Commands;

use App\Services\DailySummaryService;
use Illuminate\Console\Command;

/**
 * 每日日报提醒
 *
 * @author edison.an
 *
 */
class DailySummaryReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily_summary_reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Summary Reminder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /**
         *
         * @var DailySummaryService $dailyService
         */
        $dailyService = app(DailySummaryService::class);
        $dailyService->scheduleDailySummaryReminder();
    }
}
