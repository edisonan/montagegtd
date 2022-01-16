<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PomoService;

/**
 * 番茄记录提醒
 *
 * @author edison.an
 *        
 */
class PomoRecordReminder extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'pomo_record_reminder';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Pomo record Reminder';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		
		/**
		 *
		 * @var PomoService $pomoService
		 */
		$pomoService = app ( PomoService::class );
		
		// 5分钟
		$startTime = date ( 'Y-m-d H:i:s', time () - 300 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		$pomoService->schedulePomoRecordReminder ( $startTime, $endTime );
		
		// 30分钟
		$startTime = date ( 'Y-m-d H:i:s', time () - 1800 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		$pomoService->schedulePomoRecordReminder ( $startTime, $endTime );
		
		// 60分钟
		$startTime = date ( 'Y-m-d H:i:s', time () - 3600 );
		$endTime = date ( 'Y-m-d H:i:s', strtotime ( $startTime ) + 60 );
		$pomoService->schedulePomoRecordReminder ( $startTime, $endTime );
	}
}

