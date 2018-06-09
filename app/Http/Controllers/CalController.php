<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Cal;
use App\Repositories\CalRepository;

use App\Http\Utils\ICSUtil;

class TaskController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected $cals;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(CalRepository $cals)
    {
        $this->middleware('auth', ['except' => ['ics']]);

        $this->cals = $cals;
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
    
}
