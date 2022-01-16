<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Statistics;
use Log;
use App\Services\StatisticsService;

/**
 * statistics pomo note task etc.
 *
 * @author edison.an
 *        
 */
class StatisticsDaily extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'statistics_daily {day}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Statistic Task Pomo Note etc.';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$day = $this->argument ( 'day', 1 );
		
		$startTime = date ( 'Y-m-d', strtotime ( "-$day day" ) );
		$endTime = date ( 'Y-m-d', strtotime ( $startTime ) + 86400 );
		
		Log::info ( 'statistics:' . $startTime . '|' . $endTime );
		
		$statisticsService = app ( StatisticsService::class );
		$statisticsService->dailyStatistic ( $startTime, $endTime );
	}
}
