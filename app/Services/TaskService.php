<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskTagMap;
use App\Exceptions\CustomException;
use Mail;
use Auth;
use App\Repositories\PlanRepository;
use App\Repositories\TaskRepository;

/**
 * 待办事项业务逻辑
 *
 * @author edison.an
 *        
 */
class TaskService {
	
	/**
	 * The task repository instance.
	 *
	 * @var TaskRepository
	 */
	protected $taskRepository;
	/**
	 * The plan repository instance.
	 *
	 * @var PlanRepository
	 */
	protected $planRepository;
	
	/**
	 * The tag service instance.
	 *
	 * @var TagService
	 */
	protected $tagService;
	
	/**
	 * The journal service instance.
	 *
	 * @var JournalService
	 */
	protected $journalService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param TaskRepository $taskRepository        	
	 * @param PlanRepository $planRepository        	
	 * @param TagService $tagService        	
	 * @param PlanService $planService        	
	 * @return void
	 */
	public function __construct(TaskRepository $taskRepository, PlanRepository $planRepository, TagService $tagService, JournalService $journalService) {
		$this->taskRepository = $taskRepository;
		$this->planRepository = $planRepository;
		$this->tagService = $tagService;
		$this->journalService = $journalService;
	}
	
	/**
	 * 获取首页待办列表
	 * 
	 * @param string $status
	 * @param int $pageSize
	 * @return unknown
	 */
	public function getTaskListWithPagination($filters, $pageSize = 10) {
		$tasks = $this->taskRepository->getTaskListWithPagination ( $filters, $pageSize );
		return $tasks;
	}

	/**
	 * 获取用户任务tab计数
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getStatusCounts($userId) {
		return $this->taskRepository->getUserStatusCounts($userId);
	}
	
	/**
	 * 获取所有待办任务
	 * 
	 * @param string $status        	
	 * @param string $mode        	
	 * @return unknown[]
	 */
	public function getAllList($status, $mode) {
		$tasks = $this->taskRepository->getUserAllListByStatusMode ( Auth::id (), $status, $mode );
		// 组装子待办
		$temp = array ();
		foreach ( $tasks as $task ) {
			if ($task->parent_task_id != null) {
				$temp [$task->parent_task_id] [] = $task;
			}
		}
		
		// 格式化待办顺序
		$formatTasks = array ();
		foreach ( $tasks as $task ) {
			if ($task->parent_task_id == null) {
				$formatTasks [] = $task;
				if (isset ( $temp [$task->id] )) {
					foreach ( $temp [$task->id] as $val ) {
						$formatTasks [] = $val;
					}
				}
			}
		}
		return $formatTasks;
	}
	
	/**
	 * 按照优先级获取列表
	 * 
	 * @param string $status        	
	 * @param string $mode        	
	 * @return array[]|\App\Services\unknown
	 */
	public function getPriorityList($status, $mode) {
		$models = $this->getAllList ( $status, $mode );
		$tasks = array (
				1 => array (),
				2 => array (),
				3 => array (),
				4 => array () 
		);
		foreach ( $models as $model ) {
			$tasks [$model->priority] [] = $model;
		}
		return $tasks;
	}
	
	/**
	 * 保存待办
	 * 
	 * @param string $name        	
	 * @param string $mode        	
	 * @param int $priority        	
	 * @param string $remindtime        	
	 * @param string $deadline        	
	 * @param int $parentTaskId        	
	 * @param int $planId        	
	 * @throws CustomException
	 * @return \App\Models\Task
	 */
	public function store($name, $mode, $priority, $remindtime, $deadline, $parentTaskId, $planId) {
		if (! empty ( $parentTaskId )) {
			$parentTask = $this->taskRepository->getTaskById ( $parentTaskId );
			if (empty ( $parentTask ) || $parentTask->user_id != Auth::id ()) {
				throw new CustomException ( "错误的父类任务信息上送" );
			}
		}
		
		if (! empty ( $planId )) {
			$plan = $this->planRepository->getPlanById ( $planId );
			if (empty ( $plan ) || $plan->user_id != Auth::id ()) {
				throw new CustomException ( "错误的目标信息上送" );
			}
		}
		
		$task = new Task ();
		$task->user_id = Auth::id ();
		$task->name = $name;
		$task->mode = $mode;
		$task->priority = $priority;
		$task->remindtime = $remindtime;
		$task->deadline = $deadline;
		$task->parent_task_id = $parentTaskId;
		$task->plan_id = $planId;
		$task->save ();
		
		preg_match_all ( '/#(.*?)#/i', $name, $match );
		foreach ( $match [0] as $item ) {
			$tagName = trim ( $item, '#' );
			if (empty ( $tagName )) {
				continue;
			}
			
			$tag = $this->tagService->forTagName ( $tagName, true );
			
			$taskNote = new TaskTagMap ();
			$taskNote->create ( array (
					'tag_id' => $tag->id,
					'task_id' => $task->id 
			) );
		}
		
		return $task;
	}
	/**
	 * 根据类型更新待办任务
	 * 
	 * @param unknown $task        	
	 * @param unknown $type
	 *        	（finish 完成 restore 恢复 fold/collapse 折叠 其他 删除）
	 */
	public function updateTaskByType($task, $type) {
		$params = array ();
		
		if ($type == 'finish') {
			$params ['status'] = 2;
			$params ['is_doing'] = 0;
			
			$this->journalService->storeJournal ( 2, $task->name, $task->created_at, date ( 'Y-m-d H:i:s' ) );
		} elseif ($type == 'restore') {
			$params ['status'] = 1;
			$params ['is_doing'] = 0;
		} elseif ($type == 'fold' || $type == 'collapse') {
			$params ['status'] = 4;
			$params ['is_doing'] = 0;
		} else {
			$params ['status'] = 3;
			$params ['is_doing'] = 0;
		}
		$flag = $task->update ( $params );
	}
	
	/**
	 * 待办任务定时提醒
	 * 
	 * @param unknown $type        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function scheduleTaskReminder($type, $startTime, $endTime) {
		if ($type == 1) {
			$tasks = $this->taskRepository->getAllListByRemindTime ( $startTime, $endTime );
			$title = '待办提醒';
		} else {
			$tasks = $this->taskRepository->getAllListByDeadline ( $startTime, $endTime );
			$title = '待办截止提醒';
		}
		foreach ( $tasks as $task ) {
			$user = $task->user;
			// 邮件通知
			Mail::send ( 'emails.reminder', [ 
					'user' => $user,
					'task' => $task,
					'title' => $title 
			], function ($m) use ($user, $task, $title) {
				$m->to ( $user->email, $user->name )->subject ( $title . $task->name );
			} );
			app(NotificationChannelService::class)->sendToUser($task->user, $title, $task->name, config('app.url'));
		}
	}
	
	/**
	 * 获取用户的所有父级任务列表
	 * 
	 * @param int $userId
	 * @param int|null $excludeTaskId 要排除的任务ID（避免任务成为自己的父任务）
	 * @return Collection
	 */
	public function getUserParentTasks($userId, $excludeTaskId = null) {
		return $this->taskRepository->getUserParentTasks($userId, $excludeTaskId);
	}
}
