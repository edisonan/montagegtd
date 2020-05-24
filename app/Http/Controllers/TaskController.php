<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorCodeUtil;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskTagMap;
use App\Models\Thing;
use App\Repositories\TaskService;
use Illuminate\Http\Request;
use App\Services\TagService;

/**
 * 待办控制器
 *
 * @author edison.an
 *        
 */
class TaskController extends Controller {
	
	/**
	 * TaskService 实例.
	 *
	 * @var TaskService
	 */
	protected $taskService;
	
	/**
	 * TagService 实例.
	 *
	 * @var TagService
	 */
	protected $tagService;
	
	/**
	 * GoalService 实例.
	 *
	 * @var GoalService
	 */
	protected $goalService;
	
	/**
	 * 构造方法
	 *
	 * @param TaskService $taskService        	
	 * @return void
	 */
	public function __construct(TaskService $taskService, TagService $tagService, GoalService $goalService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'ics' 
				] 
		] );
		
		$this->taskService = $taskService;
		$this->tagService = $tagService;
		$this->goalService = $goalService;
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		if ($request->has ( 'status' )) {
			$tasks = $this->taskService->forUserByStatus ( $request->user (), $request->status );
		} else {
			$tasks = $this->taskService->forUser ( $request->user (), $needPage = true );
		}
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $tasks );
			return response ( $resp );
		} else {
			return view ( 'tasks.index', [ 
					'tasks' => $tasks 
			] );
		}
	}
	
	/**
	 * 新建
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'name' => 'required|max:255',
				'remindtime' => 'nullable|date_format:Y-m-d H:i:s',
				'deadline' => 'nullable|date_format:Y-m-d H:i:s' 
		] );
		
		$params = array ();
		$params ['name'] = $request->name;
		
		if ($request->has ( 'priority' ) && in_array ( $request->priority, array (
				1,
				2,
				3,
				4 
		) )) {
			$params ['priority'] = $request->priority;
		}
		
		if ($request->has ( 'remindtime' ) && strtotime ( $request->remindtime ) > time ()) {
			$params ['remindtime'] = $request->remindtime;
		}
		
		if ($request->has ( 'deadline' ) && strtotime ( $request->deadline ) > time ()) {
			$params ['deadline'] = $request->deadline;
		}
		
		if ($request->has ( 'parent_task_id' )) {
			$parent_task = $this->taskService->forUserById ( $request->user (), $request->parent_task_id );
			if (! empty ( $parent_task )) {
				$params ['parent_task_id'] = $request->parent_task_id;
			}
		}
		
		if (isset ( $request->goal_id )) {
			$goal = $this->goalService->forGoalId ( $request->user (), $request->goal_id );
			if (! empty ( $goal )) {
				$params ['goal_id'] = $request->goal_id;
			}
		}
		
		$task = $request->user ()->tasks ()->create ( $params );
		
		preg_match_all ( '/#(.*?)#/i', $request->name, $match );
		foreach ( $match [0] as $item ) {
			$tag_name = trim ( $item, '#' );
			if (empty ( $tag_name )) {
				continue;
			}
			
			$tag = $this->tagService->forTagName ( $tag_name );
			if (empty ( $tag )) {
				$tag = Tag::create ( array (
						'name' => $tag_name 
				) );
			}
			
			$taskNote = new TaskTagMap ();
			$taskNote->create ( array (
					'tag_id' => $tag->id,
					'task_id' => $task->id 
			) );
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $task );
			return response ( $resp );
		} else {
			return redirect ( '/index' );
		}
	}
	
	/**
	 * 删除
	 *
	 * @param Request $request        	
	 * @param Task $task        	
	 */
	public function destroy(Request $request, Task $task) {
		$this->authorize ( 'destroy', $task );
		
		$params = array ();
		
		if ($request->type == 'finish') {
			$params ['status'] = 2;
			
			$thing = new Thing ();
			$thing->user_id = $request->user ()->id;
			$thing->type = 2;
			$thing->name = $task->name;
			$thing->start_time = date ( 'Y-m-d H:i:s' );
			$thing->save ();
		} else {
			$params ['status'] = 3;
		}
		$flag = $task->update ( $params );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' )->with ( 'message', 'IT WORKS!' );
		}
	}
	
	/**
	 * 更新
	 * 
	 * @param Request $request        	
	 * @param Task $task        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function update(Request $request, Task $task) {
		$this->authorize ( 'destroy', $task );
		
		$flag = $task->update ( $request->all () );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/index' )->with ( 'message', 'IT WORKS!' );
		}
	}
}
