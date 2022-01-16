<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PomoService;

/**
 * 番茄每日提醒
 *
 * @author edison.an
 *        
 */
class PomoDailyReminder extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'pomo_daily_reminder {type}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Pomo daily Reminder';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$w = date ( "w" );
		if ($w == 0 || $w == 6) {
			Log::info ( 'not work time!' );
			return;
		}
		
		$type = $this->argument ( 'type' );
		if ($type != 1 || $type != 2) {
			Log::error ( 'pomo daily type wrong' );
			return;
		}
		
		/**
		 *
		 * @var PomoService $pomoService
		 */
		$pomoService = app ( PomoService::class );
		$pomoService->schedulePomoDailyReminder ( $type );
	}
}

