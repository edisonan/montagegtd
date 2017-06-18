<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Repositories\TaskRepository;
use App\Repositories\ThirdRepository;
use App\Http\Utils\FFClient;

use DB;
use App\Note;
use App\Task;
use App\Pomo;
use App\Statistics;
use App\Repositories\FeedRepository;
use App\Feed;


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
    		date_default_timezone_set("Asia/Shanghai");
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
    		
    		$imojs = array('(｀･ω･´) (´･ω･｀)','(ÒωÓױ)呃！！！！','(￣▽￣")','(。-`ω´-)','╮(￣▽￣)╭');
    		
    		foreach ($message_arr as $item){
	    		$ff_user = new FFClient( $config['key'] , $config['secret'] , $oauth_token , $oauth_token_secret );
	    		$result = $ff_user -> update($item.$imojs[rand(0,4)]);
	    		
	    		file_put_contents(env('CRON_LOG'),$result);
    		}
    		
    	})->daily()->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    	
    	$schedule->call(function () {
    		date_default_timezone_set("Asia/Shanghai");
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
    	})->everyMinute()->appendOutputTo(env('CRON_LOG'));
    	
    	$schedule->call(function () {
    		date_default_timezone_set("Asia/Shanghai");
    		
    		$date_type = 'day';
    		
    		$now = date('Y-m-d');
    		
    		$start_time = date('Y-m-d',strtotime($now)-86400);
    		$end_time = $now;
    		
    		\Log::info('statistics:'.$start_time.'|'.$end_time);
    		
    		$note_counts = Note::select('user_id',DB::raw('count(*) as total'))->where('updated_at','>', $start_time)->where('updated_at','<=', $end_time)->groupBy('user_id')->get();
    		$task_counts = Task::select('user_id',DB::raw('count(*) as total'))->where('status',2)->where('updated_at','>', $start_time)->where('updated_at','<=', $end_time)->groupBy('user_id')->get();
    		$pomo_counts = Pomo::select('user_id',DB::raw('count(*) as total'))->where('status',2)->where('updated_at','>', $start_time)->where('updated_at','<=', $end_time)->groupBy('user_id')->get();
    		
    		
    		foreach ($note_counts as $count_info){
    			$param_arr = ['user_id'=>$count_info['user_id'], 'data_type' => 'note', 'date_type' => $date_type, 'statistic_date' => $start_time];
    			
    			$statistics = Statistics::where($param_arr)->first();
    			$param_arr['total'] = $count_info['total'];
    			
    			if(empty($statistics)) {
    				$statistics = new Statistics();
    				$statistics->create($param_arr);
    			} else {
    				$statistics->update($param_arr);
    			}
    			\Log::info($count_info['user_id'].$count_info['total']);
    		}
    		
    		foreach ($task_counts as $count_info){
    			$param_arr = ['user_id'=>$count_info['user_id'], 'data_type' => 'task', 'date_type' => $date_type, 'statistic_date' => $start_time];
    			
    			$statistics = Statistics::where($param_arr)->first();
    			$param_arr['total'] = $count_info['total'];
    			
    			if(empty($statistics)) {
    				$statistics = new Statistics();
    				$statistics->create($param_arr);
    			} else {
    				$statistics->update($param_arr);
    			}
    			\Log::info($count_info['user_id'].$count_info['total']);
    		}
    		
    		foreach ($pomo_counts as $count_info){
    			$param_arr = ['user_id'=>$count_info['user_id'], 'data_type' => 'pomo', 'date_type' => $date_type, 'statistic_date' => $start_time];
    			
    			$statistics = Statistics::where($param_arr)->first();
    			$param_arr['total'] = $count_info['total'];
    			
    			if(empty($statistics)) {
    				$statistics = new Statistics();
    				$statistics->create($param_arr);
    			} else {
    				$statistics->update($param_arr);
    			}
    			\Log::info($count_info['user_id'].$count_info['total']);
    		}
    	
    	})->dailyAt('00:30')->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    	
    	$schedule->call(function () {
    		date_default_timezone_set("Asia/Shanghai");
    		
    		$feeds = Feed::get();
    		$feedRepository = new FeedRepository();
    		foreach ($feeds as $feed){
    			$feedRepository->checkFeed($feed);
    			\Log::info('process feed ! url:'.$feed->url);
    		}
    	})->everyTenMinutes()->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    	 
    }
    
     
}
