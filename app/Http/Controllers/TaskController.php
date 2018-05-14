<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Task;
use App\Tag;
use App\TaskTagMap;
use App\Repositories\TaskRepository;
use App\Repositories\TagRepository;
use App\Repositories\GoalRepository;
use App\Thing;

use App\Http\Utils\ICSUtil;

class TaskController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected $tasks;
    protected $goals;
    protected $tags;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(TaskRepository $tasks, GoalRepository $goals,   TagRepository $tags)
    {
        $this->middleware('auth', ['except' => ['ics']]);

        $this->tasks = $tasks;
        $this->goals = $goals;
        $this->tags = $tags;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('tasks.index', [
            'tasks' => $this->tasks->forUser($request->user(), $need_page=true),
        ]);
    }

    /**
     * Create a new task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
    	
        $this->validate($request, [
            'name' => 'required|max:255',
            'remindtime' => 'nullable|date_format:Y-m-d H:i:s',
            'deadline' => 'nullable|date_format:Y-m-d H:i:s',
        ]);
        
        $params = array();
        $params['name'] = $request->name;
        
        if($request->has('priority') && in_array($request->priority, array(1,2,3,4))){
        	$params['priority'] = $request->priority;
        }
        
        if($request->has('remindtime') && strtotime($request->remindtime) > time()){
        	$params['remindtime'] = $request->remindtime;
        }
        
        if($request->has('deadline') && strtotime($request->deadline) > time()){
        	$params['deadline'] = $request->deadline;
        }
        
        if($request->has('parent_task_id')){
        	$parent_task = $this->tasks->forUserById($request->user(),$request->parent_task_id);
        	if(!empty($parent_task)){
        		$params['parent_task_id'] = $request->parent_task_id;
        	}
        }
        
        if(isset($request->goal_id)){
        	$goal = $this->goals->forGoalId($request->user(), $request->goal_id);
        	if(!empty($goal)){
        		$params['goal_id'] = $request->goal_id;
        	}
        }
        
        $task = $request->user()->tasks()->create($params);
        
        preg_match_all('/#(.*?)#/i',$request->name,$match);
        foreach ($match[0] as $item){
        	$tag_name = trim($item,'#');
        	if(empty($tag_name)){
        		continue;
        	}
        	 
        	$tag = $this->tags->forTagName($tag_name);
        	if(empty($tag)){
        		$tag = Tag::create(array('name'=>$tag_name));
        	}
        	 
        	$taskNote = new TaskTagMap();
        	$taskNote->create(array('tag_id'=>$tag->id, 'task_id'=>$task->id));
        }

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/index');
        }
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function destroy(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);
        
        $params = array();
        
        if($request->type == 'finish'){
        	$params['status'] = 2;
        	
        	$thing = new Thing();
        	$thing->user_id = $request->user()->id;
        	$thing->type = 2;
        	$thing->name = $task->name;
        	$thing->start_time = date('Y-m-d H:i:s');
        	$thing->save();
        } else {
        	$params['status'] = 3;
        }
        $flag = $task->update($params);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/index')->with('message', 'IT WORKS!');
        }
    }
    
    public function update(Request $request, Task $task)
    {
    	$this->authorize('destroy', $task);
    
    	$flag = $task->update($request->all());
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/index')->with('message', 'IT WORKS!');
    	}
    }
    
    public function ics(Request $request,String $user_id)
    {
    	$start_time = date('Y-m-d H:i:s');
    	$end_time = date('Y-m-d H:i:s',strtotime($start_time)+31536000);
    	 
    	$tasks = $this->tasks->forUserByUserIdRemindTime($user_id, $start_time, $end_time);
    	
    	$task_props = array();
    	foreach ($tasks as $task){
    		$task_props[] = array(
    			'description'=>$task->name,
    			'dtend'=>$task->remindtime,
    			'dtstart'=>$task->remindtime,
    			'location'=>'',
    			'summary'=>$task->name,
    			'url'=>'https://task.congcong.us/tasks',
    		);
    	}
    	
    	$ics = new App\Http\Utils\ICSUtil($task_props);
    	$ics_file_contents = $ics->to_string();
    	
    	file_put_contents(config("app.storage_path").'/task_ics_'.$user_id, $ics_file_contents);
    	
    	header("Content-type:application/octet-stream");
    	header("Content-Disposition:attachment;filename = task_ics_".$user_id.'.ics');
    	header("Accept-ranges:bytes");
    	header("Accept-length:".filesize(config("app.storage_path").'/task_ics_'.$user_id));
    	
    	readfile(config("app.storage_path").'/task_ics_'.$user_id);
    }
    
}
