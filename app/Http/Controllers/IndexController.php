<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     */
    public function index(Request $request)
    {
    	//1默认等待中 2进行中 3已经完成 4休息中 5休息结束
    	$runing_pomo_status = Pomo::STATUS_INIT;
    	$runing_pomo_remain = 0;
    	
    	$setting = $request->user()->setting;
    	$pomo_time = isset($setting->pomo_time)&&!empty($setting->pomo_time)?$setting->pomo_time*60:Pomo::DEFAULT_INTERVAL;
    	$pomo_rest_time = isset($setting->pomo_time)&&!empty($setting->pomo_rest_time)?$setting->pomo_rest_time*60:Pomo::DEFAULT_REST_INTERVAL;
    	 
    	$active_pomo = $this->pomos->forUserActivePomo($request->user());
    	if(!empty($active_pomo)){
    		$pomo_start_time = strtotime($active_pomo->created_at);
    		$remain_time = $pomo_start_time + $pomo_time - time();
    		if($remain_time > 0){
    			$runing_pomo_status = Pomo::STATUS_PROCESSING;
    			$runing_pomo_remain = $remain_time;
    		} else {
    			$runing_pomo_status = Pomo::STATUS_FINISHED;
    		}
    	} else {
    		$rest_start_time = $request->session()->get('rest_start_time', 0);
    		if(!empty($rest_start_time)){
    			$remain_time = $rest_start_time + $pomo_rest_time - time();
    			if($remain_time > 0){
    				$runing_pomo_status = Pomo::STATUS_RESTING;
    				$runing_pomo_remain = $remain_time;
    			}
    		}
    	}
    	
    	$tasks = $this->tasks->forUserByStatus($request->user(), 1);
    	$pomos = $this->pomos->forUserByTime($request->user(), date('Y-m-d H:i:s',strtotime(date('Y-m-d'))));
    	$goals = $this->goals->forUserByStatus($request->user(),1,false);
    	
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
    		$tip_message = '您已经完成了一个番茄，快来记录一下吧~';
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
        	'active_pomo' => empty($active_pomo)?new Pomo():$active_pomo,
        	'runing_pomo_status' => $runing_pomo_status,
        	'runing_pomo_remain' => $runing_pomo_remain,
        	'tip_type' => $tip_type,
        	'tip_message' => $tip_message,
        ]);
    }
    
    public function feedback(Request $request)
    {
    	return view('index.feedback',[
    		'from'=>$request->has('from')?$request->from:'',
    	]);
    }
    
    public function feedbackStore(Request $request){
    	$this->validate($request, [
    			'content' => 'required',
    	]);
    	
    	$feedback = new \App\Feedback();
    	$feedback->from = $request->from;
    	$feedback->content = $request->content;
    	$feedback->save();
    	
    	if ($request->ajax() || $request->wantsJson() || $request->has('json_wants')) {
    		$resp = $this->responseJson(self::OK_CODE,array());
    		return response($resp);
    	} else {
    		redirect('/index/feedback')->with('message', 'IT WORKS!');
    	}
    }
    
    public function test(Request $request)
    {
    	 return view('index.test');
    }
}
