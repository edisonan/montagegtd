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
use App\Article;
use App\Http\Utils\SpideUtil;
use App\Setting;
use App\KindleLog;
use App\Repositories\SettingRepository;

use Develpr\Phindle\Phindle;
use Develpr\Phindle\Content;
use Develpr\Phindle\OpfRenderer;


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
    		
    	})->daily();
    	
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
    	})->everyMinute();
    	
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
    	
    	})->dailyAt('00:30');
    	
    	$schedule->call(function () {
    		date_default_timezone_set("Asia/Shanghai");
    		
    		$feeds = Feed::where('type',1)->where('status',1)->get();
    		$feedRepository = new FeedRepository();
    		foreach ($feeds as $feed){
    			$feedRepository->checkFeed($feed);
    			\Log::info('process feed ! url:'.$feed->url);
    		}
    	})->everyTenMinutes();
    	
    	$schedule->call(function () {
    		date_default_timezone_set("Asia/Shanghai");
    	
    		$feeds = Feed::where('type',2)->where('status',1)->get();
    		$spideUtil = new SpideUtil();
    		foreach ($feeds as $feed){
    			$spideUtil->processFeed($feed);
    			\Log::info('process feed ! url:'.$feed->url);
    		}
    	})->hourly();
    	
    	$schedule->call(function () {
    		date_default_timezone_set("Asia/Shanghai");
    		
    		$settings = Setting::where('is_start_kindle',1)->get();
    		foreach ($settings as $setting){
    			$user = $setting->user;
    			$kindleLog = new KindleLog();
    			$kindleLog->user_id = $user->id;
    			$kindleLog->type = 2;
    			$kindleLog->status = 1;
    			$kindleLog->save();
    			
    			$phindle = new Phindle(array(
    					'title' => "Montage GTD每日订阅推送".date('Y-m-d'),
    					'publisher' => "Montage GTD ".$user->id,
    					'creator' => $user->name,
    					'language' => 'zh-CN',
    					'subject' => 'Montage GTD每日订阅', //@see https://www.bisg.org/complete-bisac-subject-headings-2013-edition
    					'description' => 'Montage GTD每日订阅推送'.date('Y-m-d'),
    					'path'	=> config("app.storage_path") . '/ebooks', //The path that temp files will be stored, as well as the location of the final ebook mobi file
    					'isbn'  => '666666666666666',
    					'staticResourcePath' => config("app.storage_path").'/ebooks/static', //The absolute path to your static resources referenced in html (images, css, etc)
    					'cover'	=> '/images/cover.png' , //The relative path of your cover image
    					'kindlegenPath' => '/usr/local/bin/kindlegen', //The path to the kindlegen utility
    					'downloadImages' => true, //Should images be downloaded from the web if found in your html?
    			));
    			
    			$now = date('Y-m-d H:i:s');
    			$start_time = date('Y-m-d H:i:s',strtotime($now)-86400);
    			
    			$articleSubs = ArticleSub::where('user_id',$user->id)->where('status','unread')->where('published','<',$now)->where('published','>',$start_time)->orderBy('feed_id')->limit(300)->get();
    			$feed_info = array();
    			
    			$chapter_count = 0;
    			$article_count = 0;
    			
    			foreach($articleSubs as $articleSub)
    			{
    				$article = $articleSub->article;
    				if(!isset($feed_info[$article->feed_id])){
    					//文章数清零 章节数加1
    					$article_count = 0;
    					$chapter_count++;
    					$feed_info[$article->feed_id] = $article->feed;
    					
    					$content = new Content();
    					$content->setHtml('<meta http-equiv="Content-Type" content="text/html;charset=utf-8"><h2>'.$article->feed->feed_name.'</h2>'.$article->feed->feed_desc);
    					$content->setTitle($chapter_count.' '.$article->feed->feed_name);
    					$content->setPosition($chapter_count*1000+$article_count);
    					$phindle->addContent($content);
    				}
    				//文章数递增 大于20篇时不再执行
    				if($article_count > 20) continue;
    				$article_count++;
    				
    				if($setting->with_image_push == 1){
    					$spideUtil = new SpideUtil();
    					$article_content = $spideUtil->processKindleImgContent($article->content, config("app.storage_path").'/ebooks/temp');
    				} else {
    					$article_content = preg_replace("#<img.*>#iUs", "", $article->content); //无图
    				}
    				/** @var Illuminate\View\View $html */
    				$content = new Content();
    				$content->setHtml('<meta http-equiv="Content-Type" content="text/html;charset=utf-8"><h3>'.$article->subject.'</h3>'.$article_content.'<a href="'.$article->url.'">查看原文</a>');
    				$content->setTitle($chapter_count.'.'.$article_count.' '.$article->subject);
    				$content->setPosition($chapter_count*1000+$article_count);
    				$phindle->addContent($content);
    			}
    			
    			//This is where all of the magic happens and the mobi file is actually generated
    			$phindle->process();
//     			$path = config("app.storage_path") . '/ebooks/' . $phindle->getAttribute('uniqueId') . '.mobi';
    			$path = $phindle->getMobiPath();
    			
    			$kindleLog->path = $path;
    			$kindleLog->status = 2;
    			$kindleLog->save();
    			
    			\Log::info('send to kindle:'.$user->id.'|'.count($articleSubs).'|'.$path);
    			\Mail::send('emails.kindle', ['setting'=>$setting,'path'=>$path], function ($m) use ($setting,$path) {
    				$m->to($setting->kindle_email, 'user')->subject('Send To Kindle');
    				$m->attach($path);
    			});
    			
    			$kindleLog->status = 3;
    			$kindleLog->save();
    		}
    	})->dailyAt('18:00');
    }
    
     
}
