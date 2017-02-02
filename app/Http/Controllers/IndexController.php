<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Task;
use App\Pomo;
use App\Repositories\TaskRepository;
use App\Repositories\PomoRepository;

class IndexController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected $tasks;
    
    
    protected $pomos;

    const DEFAULT_INTERVAL = 1500;//25min
    
    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(TaskRepository $tasks,PomoRepository $pomos)
    {
        $this->middleware('auth');

        $this->tasks = $tasks;
        $this->pomos = $pomos;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	//1默认未开启 2进行中 3已经完成
    	$runing_pomo_status = 1;
    	$runing_pomo_remain = 0;
    	 
    	$pomo_start_time = $request->session()->get('pomo_start_time');
    	if(isset($pomo_start_time) && !empty($pomo_start_time)){
    		if(time() < $pomo_start_time + self::DEFAULT_INTERVAL){
    			$runing_pomo_status = 2;
    			$runing_pomo_remain = $pomo_start_time + self::DEFAULT_INTERVAL - time();
    		} else {
    			$runing_pomo_status = 3;
    		}
    	}
    	$tasks = $this->tasks->forUserByStatus($request->user(), 1);
    	$pomos = $this->pomos->forUserByTime($request->user(), date('Y-m-d H:i:s',strtotime(date('Y-m-d'))));
    	
        return view('index.index', [
            'tasks' => $tasks,
            'pomos' => $pomos,
        	'runing_pomo_status' => $runing_pomo_status,
        	'runing_pomo_remain' => $runing_pomo_remain,
        ]);
    }
}
