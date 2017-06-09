<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Repositories\ThirdRepository;

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
	    		$result = $ff_user -> update($item);
    		}
    		
    	})->daily();
    }
}
