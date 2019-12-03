<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PomoNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $user;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $message, $delay)
    {
        $this->user = $user;
        $this->message = $message;
        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    	$needPomo = \Cache::store('file')->get('NEED_POMO'.$user->id);
    	
    	if(!empty($needPomo)){
    		if(isset($user->setting->ifttt_notify)){
    			$notifyResult = CommonUtil::iftttnotify('做番茄',$message,'https://task.congcong.us/',$user->setting->ifttt_notify);
    			\Log::info('notify result:'.$notifyResult.'|message:'.$message.'|user:'.$user->name);
    		}
    	}
    	return true;
    }
}