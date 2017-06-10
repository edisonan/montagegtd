<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Repositories\TaskRepository;
use App\Repositories\ThirdRepository;
use App\Http\Utils\FFClient;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    	$schedule->call(function () {
    		$config = config('services.fanfou');
    		
    		$thirdRepository = new ThirdRepository();
    		$third = $thirdRepository->forThirdId(env('FANFOU_ID'));
    		if(empty($third)){
    			//return redirect('third/fanfouIndex');
    		}
    		
    		$oauth_token = $third['token_value'];
    		$oauth_token_secret = $third['token_secret'];
    		
    		$message = env('FANFOU_MESSAGE');
    		
    		$message_arr = explode('|', $message);
    		
    		foreach ($message_arr as $item){
	    		$ff_user = new FFClient( $config['key'] , $config['secret'] , $oauth_token , $oauth_token_secret );
	    		$result = $ff_user -> update($item.rand(1,9));
	    		file_put_contents(env('CRON_LOG'),$result);
    		}
    		
    	})->daily()->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    	
    	$schedule->call(function () {
    		$start_time = date('Y-m-d H:i:s');
    		$end_time = date('Y-m-d H:i:s',strtotime($start_time)+60);
    		
    		$taskRepository = new TaskRepository();
    		$tasks = $taskRepository->forUserByRemindTime($start_time, $end_time);
    		
    		foreach ($tasks as $task){
    			$user = $task->user;
	    		\Mail::send('emails.reminder', ['user' => $user, 'task'=>$task], function ($m) use ($user, $task) {
	    			$m->to($user->email, $user->name)->subject('Task Reminder for :'.$task->name);
	    		});
    		}
    	})->everyMinute()->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    }
}
