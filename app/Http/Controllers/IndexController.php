<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pomo;
use App\Repositories\TaskRepository;
use App\Repositories\PomoRepository;
use App\Repositories\GoalRepository;

class IndexController extends Controller
{
	
    protected $pomoService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PomoService $pomoService,PomoRepository $pomos,GoalRepository $goals)
    {
        $this->middleware('auth');

        $this->pomoService = $pomoService;
    }

    /**
     *
     * @param  Request  $request
     */
    public function index(Request $request)
    {
    	// 获取当前活动信息
    	$currentPomoInfo = $this->pomoService->getCurrentPomoInfo($request->user());
    	
    	// 相关提示
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
    	
        return view('index.index', array_merge($currentPomoInfo,[
        	'tip_type' => $tip_type,
        	'tip_message' => $tip_message,
        ]));
    }
    
}
