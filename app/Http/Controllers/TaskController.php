<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Task;
use App\Repositories\TaskRepository;

class TaskController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected $tasks;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(TaskRepository $tasks)
    {
        $this->middleware('auth');

        $this->tasks = $tasks;
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
            'tasks' => $this->tasks->forUser($request->user()),
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
            'remindtime' => 'date_format:Y-m-d H:i:s',
            'deadline' => 'date_format:Y-m-d H:i:s',
        ]);
        
        $params = array();
        $params['name'] = $request->name;
        
        echo $request->priority;
        if(isset($request->priority) && in_array($request->priority, array(1,2,3,4))){
        	$params['priority'] = $request->priority;
        }
        
        if(isset($request->remindtime) && strtotime($request->remindtime) > time()){
        	$params['remindtime'] = $request->remindtime;
        }
        
        if(isset($request->deadline) && strtotime($request->deadline) > time()){
        	$params['deadline'] = $request->deadline;
        }
        $request->user()->tasks()->create($params);

        return redirect('/index');
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
        } else {
        	$params['status'] = 3;
        }
        $flag = $task->update($params);
//         print_r($params);
//         print_r($flag);exit;

        return redirect('/index');
    }
    
}
