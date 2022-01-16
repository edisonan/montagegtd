<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Repositories\TaskRepository;
use DB;
use App\Models\Note;
use App\Task;
use App\Pomo;
use App\Statistics;
use App\Repositories\FeedRepository;
use App\Feed;
use App\Article;
use App\Http\Utils\SpideUtil;
use App\Models\Setting;
use App\KindleLog;
use App\ArticleSub;
use App\Repositories\SettingRepository;
use Develpr\Phindle\Phindle;
use Develpr\Phindle\Content;
use Develpr\Phindle\OpfRenderer;

class Kernel extends ConsoleKernel {
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
			// Commands\Inspire::class,
			Commands\FanfouPublish::class,
			Commands\FeedCommon::class,
			Commands\KindlePush::class,
			Commands\StatisticsDaily::class,
			Commands\TaskReminder::class,
			Commands\PomoDailyReminder::class,
			Commands\PomoRecordReminder::class,
			Commands\PomoRestedReminder::class 
	];
	
	/**
	 * Define the application's command schedule.
	 *
	 * @param \Illuminate\Console\Scheduling\Schedule $schedule        	
	 * @return void
	 */
	protected function schedule(Schedule $schedule) {
		date_default_timezone_set ( "Asia/Shanghai" );
		
		$schedule->command ( 'fanfou_publish' )->daily ();
		$schedule->command ( 'task_reminder' )->everyMinute ();
		
		// 上午提醒
		$schedule->command ( 'pomo_daily_reminder', array (
				1 
		) )->dailyAt ( '10:10' );
		// 下午提醒
		$schedule->command ( 'pomo_daily_reminder', array (
				1 
		) )->dailyAt ( '13:40' );
		$schedule->command ( 'pomo_record_reminder' )->everyMinute ();
		$schedule->command ( 'pomo_rested_reminder' )->everyMinute ();
		
		$schedule->command ( 'statistics_daily', array (
				1 
		) )->dailyAt ( '00:30' );
		$schedule->command ( 'feed_common', array (
				1 
		) )->everyTenMinutes ();
		$schedule->command ( 'feed_common', array (
				2 
		) )->hourly ();
		$schedule->command ( 'feed_common', array (
				3 
		) )->daily ();
		$schedule->command ( 'feed_common', array (
				4 
		) )->daily ();
		
		$schedule->command ( 'kindle_push' )->dailyAt ( '18:00' );
		$schedule->command ( 'daily_summary_reminder' )->dailyAt ( '18:10' );
		
		// $schedule->command ( 'backup2qiniu', array (
		// env ( 'TASK_SQL_FILE_PATH' )
		// ) )->dailyAt ( '18:00' );
		// $schedule->command ( 'backup2qiniu', array (
		// env ( 'WWW_SQL_FILE_PATH' )
		// ) )->dailyAt ( '18:00' );
	}
	
	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands() {
		$this->load ( __DIR__ . '/Commands' );
		
		require base_path ( 'routes/console.php' );
	}
}
