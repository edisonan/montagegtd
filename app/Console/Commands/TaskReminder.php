<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaskService;

/**
 * 待办提醒
 *
 * @author edison.an
 *        
 */
class TaskReminder extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'task_reminder';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Task Reminder';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$startTime = date ( 'Y-m-d H:i:s' );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		
		/**
		 *
		 * @var TaskService $taskService
		 */
		$taskService = app ( TaskService::class );
		$taskService->scheduleTaskReminder ( 1, $startTime, $endTime );
		$taskService->scheduleTaskReminder ( 2, $startTime, $endTime );
	}
}
