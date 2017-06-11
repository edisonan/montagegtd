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
	    		$result = $ff_user -> update($item.rand(0,4));
	    		
	    		file_put_contents(env('CRON_LOG'),$result);
    		}
    		
    	})->dailyAt('13:00')->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    	
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
    		
    		$start_time = date('Y-m-d',strtotime('-1 days'));
    		$end_time = date('Y-m-d');
    		
    		\Log::info('statistics:'.$start_time.'|'.$end_time);
    		
    		$note_counts = Note::select('user_id','count(*) as count')->where('updated_at','>', $start_time)->where('updated_at','<=', $end_time)->groupBy('user_id')->count();
    		$task_counts = Task::select('user_id','count(*) as count')->where('status',2)->where('updated_at','>', $start_time)->where('updated_at','<=', $end_time)->groupBy('user_id')->count();
    		$pomo_counts = Pomo::select('user_id','count(*) as count')->where('status',2)->where('updated_at','>', $start_time)->where('updated_at','<=', $end_time)->groupBy('user_id')->count();
    		
    		
    		foreach ($note_counts as $note_count){
    			$note = Note::where(['user_id'=>$note_count['user_id'], 'data_type' => 'note', 'date_type' => $date_type, 'static_date' => $start_time])->first();
    			$note->save(['user_id'=>$note_count['user_id'], 'data_type' => 'note', 'date_type' => $date_type, 'static_date' => $start_time]);
    			\Log::info($note_count['user_id'].$note_count['count']);
    		}
    		
    		foreach ($task_counts as $task_count){
    			$pomo = Note::where(['user_id'=>$task_count['user_id'], 'data_type' => 'task', 'date_type' => $date_type, 'static_date' => $start_time])->first();
    			$pomo->save(['user_id'=>$task_count['user_id'], 'data_type' => 'task', 'date_type' => $date_type, 'static_date' => $start_time, 'count' => $task_count['count']]);
    			\Log::info($task_count['user_id'].$task_count['count']);
    		}
    		
    		foreach ($pomo_counts as $pomo_count){
    			$pomo = Pomo::where(['user_id'=>$task_count['user_id'], 'data_type' => 'pomo', 'date_type' => $date_type, 'static_date' => $start_time])->first();
    			$pomo->save(['user_id'=>$task_count['user_id'], 'data_type' => 'pomo', 'date_type' => $date_type, 'static_date' => $start_time, 'count' => $task_count['count']]);
    			\Log::info($pomo_count['user_id'].$pomo_count['count']);
    		}
    	
    	})->everyMinute()->appendOutputTo(env('CRON_LOG'))->emailOutputTo(env('CRON_EMAIL'));
    	 
    }
    
     
}
