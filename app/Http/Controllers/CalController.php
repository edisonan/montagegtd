<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Cal;
use App\Setting;
use App\Repositories\CalRepository;

use App\Http\Utils\ICSUtil;
use App\Http\Utils\ICSUtil2;
use App\Repositories\SettingRepository;
use App\Repositories\TaskRepository;

class CalController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected $cals;
    protected $settings;
    protected $tasks;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(CalRepository $cals, SettingRepository $settings, TaskRepository $tasks)
    {
        $this->middleware('auth', ['except' => ['ics','taskics']]);

        $this->cals = $cals;
        $this->settings = $settings;
        $this->tasks = $tasks;
    }
    
    public function index(Request $request)
    {
    	//处理个人日历提醒相关内容
    	$cal_token = '';
    	$setting = $this->settings->forUser($request->user());
    	if(isset($setting['cal_token']) && !empty($setting['cal_token'])){
    		$cal_token = $setting['cal_token'];
    	} else {
	    	$default_cal_token = md5($request->user()->id.'_'.time());
	    	if(empty($setting)){
	    		$setting = new Setting();
	    		$setting->user_id = $request->user()->id;
	    		$setting->save(array('cal_token'=>$default_cal_token));
	    	} else if(empty($setting['cal_token'])){
	    		$setting->update(array('cal_token'=>$default_cal_token));
	    	}
	    	$cal_token = $default_cal_token;
    	}
    	
    	$person_cal_url = 'webcal://task.congcong.us/taskics/'.$cal_token;
    	
    	//处理公共日历相关内容
    	$cals = array(
    		array(
    			'theme' => '2018 世界杯',
    			'url' => 'webcal://task.congcong.us/calics/worldcup',
    		)	
    	);
    	
    	return view('cals.index', ['person_cal_url' => $person_cal_url,'cals'=>$cals]);
    }

    
    public function ics(Request $request,String $theme)
    {
        date_default_timezone_set("Asia/Shanghai");
    	 
    	$cals = $this->cals->forByThemeAndStatus($theme,1);
    	
    	$task_props = array();
    	foreach ($cals as $cal){
    		$task_props[] = array(
    			'description'=>$cal->desc,
    			'dtend'=>$cal->dtend,
    			'dtstart'=>$cal->dtstart,
    			'location'=>$cal->location,
    			'summary'=>$cal->summary,
    			'url'=>$cal->url,
    		);
    	}
    	
    	$ics = new ICSUtil($task_props);
    	$ics->cal_name = $theme;
    	$ics_file_contents = $ics->to_string();
    	
    	$file_name = 'task_ics_'.md5($theme);
    	
    	file_put_contents(config("app.storage_path").'/'.$file_name, $ics_file_contents);
    	
    	header("Content-type:application/octet-stream");
    	header("Content-Disposition:attachment;filename = ".$file_name.'.ics');
    	header("Accept-ranges:bytes");
    	header("Accept-length:".filesize(config("app.storage_path").'/'.$file_name));
    	
    	readfile(config("app.storage_path").'/'.$file_name);
    }
    
    public function taskics(Request $request,String $cal_token)
    {
    	date_default_timezone_set("Asia/Shanghai");
    
    	//获取该用户user_id
    	$setting = $this->settings->forCalToken($cal_token);
    	if(isset($setting['user_id'])){
	    	$user_id = $setting['user_id'];
    	} else {
    		echo 'error cal_token';exit;
    	}
    
    	//根据开始时间和结束时间，查询需要提醒内容
    	$start_time = date('Y-m-d H:i:s',time()-15768000);
    	$end_time = date('Y-m-d H:i:s',strtotime($start_time)+31536000);
    	$tasks = $this->tasks->forUserByUserIdRemindTime($user_id, $start_time, $end_time);
    	 
    	$task_props = array();
    	foreach ($tasks as $task){
    		$task_props[] = array(
    				'dtend'=>$task->remindtime,
    				'dtstart'=>$task->remindtime,
    				'due'=>$task->remindtime,
    				'completed'=>$task->remindtime,
    				'summary'=>$task->name,
    				'repeat'=>1,
    				'status'=>'NEEDS-ACTION',
    		);
    	}
    	 
    	$ics = new ICSUtil2($task_props);
    	$ics_file_contents = $ics->to_string();
    	 
    	file_put_contents(config("app.storage_path").'/task_ics_'.$user_id, $ics_file_contents);
    	 
    	header("Content-type:application/octet-stream");
    	header("Content-Disposition:attachment;filename = task_ics_".$user_id.'.ics');
    	header("Accept-ranges:bytes");
    	header("Accept-length:".filesize(config("app.storage_path").'/task_ics_'.$user_id));
    	 
    	readfile(config("app.storage_path").'/task_ics_'.$user_id);
    }
    
}
