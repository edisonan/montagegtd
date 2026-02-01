<?php

namespace App\Http\Controllers;

use App\Http\Utils\PaginationHelper;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

/**
 * 待办事项控制器
 *
 * @author edison.an
 *        
 */
class TaskController extends Controller {
	
	/**
	 * The task service instance.
	 *
	 * @var TaskService
	 */
	protected $taskService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param TaskService $taskService        	
	 * @return void
	 */
	public function __construct(TaskService $taskService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'ics' 
				] 
		] );
		
		$this->taskService = $taskService;
	}
	
	/**
	 * 首页.
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		$status = $request->input ( 'status', '' );
		$pageSize = PaginationHelper::getPageSize($request);
		$tasks = $this->taskService->getTaskListWithPagination ( $status, $pageSize );
		
		return $this->jsonAndViewAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( [ 
				'tasks' => $tasks 
		] ), 'tasks.index' );
	}
	
	/**
	 * 根据模式获取所有待办任务
	 * 
	 * @param Request $request        	
	 * @return unknown
	 */
	public function getAllList(Request $request) {
		$status = $request->input ( 'status', 1 );
		$mode = $request->input ( 'mode', 1 );
		$formatTasks = $this->taskService->getAllList ( $status, $mode );
		
		return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( $formatTasks ) );
	}
	
	/**
	 * 根据优先级获取待办任务列表
	 * 
	 * @param Request $request        	
	 * @return unknown
	 */
	public function priority(Request $request) {
		$status = $request->input ( 'status', 1 );
		$mode = $request->input ( 'mode', 1 );
		$tasks = $this->taskService->getPriorityList ( $status, $mode );
		
		// todo check
		return $this->jsonAndViewAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( [ 
				'tasks' => $tasks 
		] ), 'tasks.priority' );
	}
	
	/**
	 * 创建.
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'name' => 'required|max:255',
				'mode' => 'required',
				'remindtime' => 'nullable|date_format:Y-m-d H:i:s',
				'deadline' => 'nullable|date_format:Y-m-d H:i:s' 
		] );
		
		$name = $request->name;
		$mode = $request->mode;
		$priority = $request->input ( 'priority', 1 );
		$remindtime = $request->input ( 'remindtime', null );
		$deadline = $request->input ( 'deadline', null );
		$parentTaskId = $request->input ( 'parent_task_id', null );
		$goalId = $request->input ( 'goal_id', null );
		
		if (! in_array ( $priority, array (
				1,
				2,
				3,
				4 
		) )) {
			throw new CustomException ( "错误的优先级！" );
		}
		
		if (! empty ( $remindtime ) && strtotime ( $remindtime ) > time ()) {
			throw new CustomException ( "错误的提醒时间！" );
		}
		
		if (! empty ( $deadline ) && strtotime ( $deadline ) > time ()) {
			throw new CustomException ( "错误的截止时间！" );
		}
		
		$task = $this->taskService->store ( $name, $mode, $priority, $remindtime, $deadline, $parentTaskId, $goalId );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( $task ), '/index' );
	}
	
	/**
	 * 删除.
	 *
	 * @param Request $request        	
	 * @param Task $task        	
	 */
	public function destroy(Request $request, Task $task) {
		$this->authorize ( 'destroy', $task );
		
		$this->taskService->updateTaskByType ( $task, $request->input ( 'type', '' ) );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
	
	/**
	 * 更新
	 *
	 * @param Request $request        	
	 * @param Task $task        	
	 * @return
	 *
	 */
	public function update(Request $request, Task $task) {
		$this->authorize ( 'destroy', $task );
		
		if ($request->method () == 'GET') {
			return view ( 'tasks.update', array (
					'task' => $task 
			) );
		}
		
		$flag = $task->update ( $request->all () );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/index' );
	}
	
	/**
	 * 获取单个任务数据 (用于弹窗编辑)
	 * 
	 * @param Task $task
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(Task $task) {
		$this->authorize('destroy', $task);
		
		return response()->json([
			'code' => 9999,
			'result' => $task
		]);
	}
	
	/**
	 * 获取用户父级任务列表（用于下拉框选择）
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getParentTasks(Request $request) {
		$userId = auth()->id();
		
		// 检查用户是否已认证
		if (!$userId) {
			return response()->json([
				'code' => 9998,
				'msg' => '用户未认证',
				'result' => []
			]);
		}
		
		$excludeTaskId = $request->input('exclude_task_id');
		$parentTasks = $this->taskService->getUserParentTasks($userId, $excludeTaskId);
		
		return response()->json([
			'code' => 9999,
			'result' => $parentTasks
		]);
	}
}