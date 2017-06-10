<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Task;
use App\Pomo;
use App\Repositories\TaskRepository;
use App\Repositories\PomoRepository;
use App\Repositories\GoalRepository;

class IndexController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected $tasks;
    
    protected $pomos;
    
    protected $goals;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(TaskRepository $tasks,PomoRepository $pomos,GoalRepository $goals)
    {
        $this->middleware('auth');

        $this->tasks = $tasks;
        $this->pomos = $pomos;
        $this->goals = $goals;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	//1默认等待中 2进行中 3已经完成 4休息中 5休息结束
    	$runing_pomo_status = $request->session()->get('pomo_status', 1);
    	$runing_pomo_remain = 0;
    	 
    	$pomo_start_time = $request->session()->get('pomo_start_time', time());
    	
    	//判断是否正在进行中或者休息中，如果尚未完成则进行展示剩余时间
    	if($runing_pomo_status == Pomo::STATUS_PROCESSING || $runing_pomo_status == Pomo::STATUS_RESTING){
    		$interval_time = ($runing_pomo_status == Pomo::STATUS_PROCESSING) ? Pomo::DEFAULT_INTERVAL:Pomo::DEFAULT_REST_INTERVAL;
    		$remain_time = $pomo_start_time + $interval_time - time();
    		
    		//如果已经完成，或者休息完成，那么自动设置状态
    		if($remain_time < 0){
    			if($runing_pomo_status == Pomo::STATUS_PROCESSING){
	    			$request->session()->set('pomo_status', Pomo::STATUS_FINISHED);
	    			$runing_pomo_status = Pomo::STATUS_FINISHED;
    			} else {
    				$request->session()->set('pomo_status', Pomo::STATUS_INIT);
    				$runing_pomo_status = Pomo::STATUS_INIT;
    			}
    		} else {
    			$runing_pomo_remain = $remain_time;
    		}
    	}
    	
    	$tasks = $this->tasks->forUserByStatus($request->user(), 1);
    	$pomos = $this->pomos->forUserByTime($request->user(), date('Y-m-d H:i:s',strtotime(date('Y-m-d'))));
    	$goals = $this->goals->forUser($request->user());
    	
    	foreach ($tasks as $key => $task){
    		if(!empty($task->taskTagMaps)){
    			foreach ($task->taskTagMaps as $taskTagMap){
    				$url = "/index?tag_id=".$taskTagMap->tag->id;
    				$tag_name = '#'.$taskTagMap->tag->name.'#';
    	
    				$task->name = str_replace($tag_name, "<a href='$url' target='_blank'>".$tag_name."</a>", $task->name);
    				$tasks[$key] = $task;
    			}
    		}
    	}
    	
    	//tips
    	$tip_type = 0;
    	$tip_message = '';
    	
    	if($runing_pomo_status == 3){
    		$tip_type = 1;
    		$tip_message = '您已经完成了一个小目标，快来记录一下吧~';
    	} else {
    		$hour = date('H');
    		if($hour < 10 && $hour > 6 && !isset($_COOKIE[date('Ymd').'morning_tip'])){
    			$tip_type = 2;
    			$tip_message = '一日之计在于晨，写个<a href="'.url('/notes',array('add_content','#今日小目标#')).'">今日小目标</a>吧';
    		} else if($hour > 18 && $hour < 22 && !isset($_COOKIE[date('Ymd').'afternoon_tip'])){
    			$tip_type = 3;
    			$tip_message = '今天过得怎么样，写个<a href="'.url('/notes',array('add_content','#每日总结#')).'">每日总结</a>吧';
    		}
    	}
    	
        return view('index.index', [
            'tasks' => $tasks,
            'pomos' => $pomos,
            'goals' => $goals,
        	'runing_pomo_status' => $runing_pomo_status,
        	'runing_pomo_remain' => $runing_pomo_remain,
        	'tip_type' => $tip_type,
        	'tip_message' => $tip_message,
        ]);
    }
    
    public function test(Request $request)
    {
    	 return view('index.test');
    }
}
