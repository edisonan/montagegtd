<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PomoService;

/**
 * 番茄休息过后开启新番茄提醒
 *
 * @author edison.an
 *        
 */
class PomoRestedReminder extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'pomo_rested_reminder';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Pomo Rested Reminder';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		
		/**
		 *
		 * @var PomoService $taskService
		 */
		$pomoService = app ( PomoService::class );
		
		// 5分钟
		$startTime = date ( 'Y-m-d H:i:s', time () - 300 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		$pomoService->schedulePomoRestedReminder ( $startTime, $endTime );
		
		// 30分钟
		$startTime = date ( 'Y-m-d H:i:s', time () - 1800 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		$pomoService->schedulePomoRestedReminder ( $startTime, $endTime );
		
		// 60分钟
		$startTime = date ( 'Y-m-d H:i:s', time () - 3600 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		$pomoService->schedulePomoRestedReminder ( $startTime, $endTime );
	}
}

