<?php

namespace App\Console;

use App\Article;
use App\ArticleSub;
use App\Feed;
use App\KindleLog;
use App\Focus;
use App\Statistics;
use App\Task;
use DB;
use Develpr\Phindle\Content;
use Develpr\Phindle\OpfRenderer;
use Develpr\Phindle\Phindle;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        Commands\FanfouPublish::class,
        Commands\FeedCommon::class,
//        Commands\KindlePush::class,
        Commands\StatisticsDaily::class,
        Commands\TaskReminder::class,
        Commands\FocusDailyReminder::class,
        Commands\FocusRecordReminder::class,
        Commands\FocusRestedReminder::class,
        Commands\DailySummaryReminder::class,
        Commands\StudyGenerateTasks::class,
        Commands\ArticlesClassifyPending::class,
        Commands\DigestGenerateScheduled::class,
        Commands\DigestWhitelistUserCommand::class,
        Commands\DigestUpsertProfileCommand::class,
//        Commands\AddLlmMenuItems::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        date_default_timezone_set("Asia/Shanghai");

//        $schedule->command('fanfou_publish')->daily();
        $schedule->command('task_reminder')->everyMinute();

        // 上午提醒
        $schedule->command('focus_daily_reminder', array(
            1
        ))->dailyAt('10:10')->weekdays();
        // 下午提醒
        $schedule->command('focus_daily_reminder', array(
            1
        ))->dailyAt('13:40')->weekdays();
        $schedule->command('focus_record_reminder')->cron('* 9-19 * * *');
        $schedule->command('focus_rested_reminder')->cron('* 9-19 * * *');

        $schedule->command('statistics_daily', array(
            1
        ))->dailyAt('00:30');
        $schedule->command('feed_common', array(
            1
        ))->everyTenMinutes();
        $schedule->command('articles:classify-pending --limit=20 --backfill=50')->everyFiveMinutes();
        $schedule->command('feed_common', array(
            2
        ))->hourly();
        $schedule->command('digest:generate-scheduled --limit=10')->hourly();
        $schedule->command('feed_common', array(
            3
        ))->daily();
        $schedule->command('feed_common', array(
            4
        ))->daily();

//        $schedule->command('kindle_push')->dailyAt('18:00');
        $schedule->command('daily_summary_reminder')->dailyAt('18:10');
        $schedule->command('study:generate-tasks --days=14')->dailyAt('00:05');

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
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
