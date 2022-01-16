<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaskService;
use App\Services\DailySummaryService;

/**
 * 每日日报提醒
 *
 * @author edison.an
 *        
 */
class TaskReminderReminder extends Command {
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
	public function handle() {
		/**
		 *
		 * @var DailySummaryService $dailyService
		 */
		$dailyService = app ( DailySummaryService::class );
		$dailyService->scheduleDailySummaryReminder ();
	}
}
