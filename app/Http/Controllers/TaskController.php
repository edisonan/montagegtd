<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;
use App\Models\Tag;
use App\Models\TaskTagMap;
use App\Repositories\TaskRepository;
use App\Repositories\TagRepository;
use App\Repositories\GoalRepository;
use App\Models\Thing;


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
    
}
