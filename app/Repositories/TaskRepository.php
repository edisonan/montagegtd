<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Task;
use DB;

class TaskRepository {
	/**
	 * 通过id获取待办任务信息
	 * 
	 * @param int $id        	
	 * @return unknown
	 */
	public function getTaskById($id) {
		return Task::where ( 'id', $id )->first ();
	}
	
	/**
	 * 根据状态获取该用户待办列表
	 * 
	 * @param int $userId         
	 * @param string $status         
	 * @return unknown
	 */
	public function getUserList($userId, $status = '') {
		$tasks = Task::where ( 'user_id', $userId )->orderBy ( 'updated_at', 'desc' );
		if (! empty ( $status )) {
			$tasks->where ( 'status', $status );
		}
		return $tasks->paginate ( 50 );
	}
		
	/**
	 * 根据状态获取该用户待办列表(支持分页参数)
	 * 
	 * @param int $userId
	 * @param string $status
	 * @param int $page
	 * @param int $pageSize
	 * @return unknown
	 */
	public function getTaskListWithPagination($filters = [], $pageSize = 10) {
        $tasks = Task::orderBy ( 'updated_at', 'desc' );
        if (isset($filters['user_id'])){
            $tasks = $tasks->where ( 'user_id', $filters['user_id'] );
        }
        if (isset($filters['status']) && !empty($filters['status']) && $filters['status'] != 'all'){
            $tasks = $tasks->where ( 'status', $filters['status'] );
        }
        return $tasks->simplePaginate($pageSize);
	}

	/**
	 * 获取用户任务状态计数
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getUserStatusCounts($userId) {
		$rows = Task::select('status', DB::raw('count(*) as total'))
			->where('user_id', $userId)
			->groupBy('status')
			->get();

		$counts = array(
			'1' => 0,
			'2' => 0,
			'3' => 0,
			'all' => 0,
		);

		foreach ($rows as $row) {
			$key = (string)$row->status;
			$val = (int)$row->total;
			if (array_key_exists($key, $counts)) {
				$counts[$key] = $val;
			}
			$counts['all'] += $val;
		}

		return $counts;
	}
	
	/**
	 * 通过状态和模式获取该用户所有列表
	 * 
	 * @param string $status        	
	 * @param string $mode        	
	 * @return unknown
	 */
	public function getUserAllListByStatusMode($userId, $status = '', $mode = '') {
		$tasks = Task::with ( 'plan' )->where ( 'user_id', $userId );
		
		if (! empty ( $status )) {
			$tasks->where ( 'status', $status );
		}
		if (! empty ( $mode )) {
			$tasks->where ( 'mode', $mode );
		}
		$tasks->orderBy ( 'is_top', 'desc' )->orderBy ( 'priority', 'desc' )->orderBy ( 'updated_at', 'desc' );
		return $tasks->get ();
	}
	
	/**
	 * 获取用户的所有非子任务（用于父任务选择）
	 * 
	 * @param int $userId
	 * @param int|null $excludeTaskId 要排除的任务ID（避免任务成为自己的父任务）
	 * @return Collection
	 */
	public function getUserParentTasks($userId, $excludeTaskId = null) {
		$query = Task::where('user_id', $userId)
			->where('status', 1)  // 只获取状态为1（进行中）的任务
			->where(function($query) {
				$query->whereNull('parent_task_id')
					  ->orWhere('parent_task_id', 0);
			});
			
		// 排除当前任务本身，防止任务成为自己的父任务
		if ($excludeTaskId) {
			$query->where('id', '!=', $excludeTaskId);
		}
		
		return $query->orderBy('updated_at', 'desc')
			->get();
	}
	
	/**
	 * 通过提醒时间获取所有待办任务列表
	 * 
	 * @param string $startTime        	
	 * @param string $endTime        	
	 * @return unknown
	 */
	public function getAllListByRemindtime($startTime, $endTime) {
		return Task::where ( 'remindtime', '>', $startTime )->where ( 'remindtime', '<', $endTime )->where ( 'status', 1 )->orderBy ( 'priority', 'desc' )->orderBy ( 'updated_at', 'desc' )->get ();
	}
	
	/**
	 * 通过截止时间获取所有待办任务列表
	 * 
	 * @param string $startTime        	
	 * @param string $endTime        	
	 * @return unknown
	 */
	public function getAllListByDeadline($startTime, $endTime) {
		return Task::where ( 'deadline', '>', $startTime )->where ( 'deadline', '<', $endTime )->where ( 'status', 1 )->orderBy ( 'priority', 'desc' )->orderBy ( 'updated_at', 'desc' )->get ();
	}

	/**
	 * 获取指定用户在提醒时间范围内的任务（用于个人日历订阅）
	 *
	 * @param int $userId
	 * @param string $startTime
	 * @param string $endTime
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getListByRemindTime($userId, $startTime, $endTime) {
		return Task::where ( 'user_id', $userId )
			->where ( 'remindtime', '>', $startTime )
			->where ( 'remindtime', '<', $endTime )
			->where ( 'status', 1 )
			->orderBy ( 'remindtime', 'asc' )
			->get ();
	}
	
	/**
	 * 获取时间段内统计情况
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getStatisticCounts($startTime, $endTime) {
		return Task::select ( 'user_id', DB::raw ( 'count(*) as total' ) )->where ( 'status', 2 )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->groupBy ( 'user_id' )->get ();
	}
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user, $needPage) {
	// $task = Task::where ( 'user_id', $user->id )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $task->paginate ( 50 );
	// } else {
	// return $task->get ();
	// }
	// }
	// public function forUserById(User $user, $id) {
	// $task = Task::where ( 'user_id', $user->id )->where ( 'id', $id );
	// return $task->first ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, string $status, $needPage = false, $mode = '') {
	// $task = Task::with ( 'plan' )->where ( 'user_id', $user->id )->where ( 'status', $status );
	
	// if (! empty ( $mode )) {
	// $task = $task->where ( 'mode', $mode );
	// }
	// $task = $task->orderBy ( 'is_top', 'desc' )->orderBy ( 'priority', 'desc' )->orderBy ( 'updated_at', 'desc' );
	
	// if ($needPage) {
	// return $task->paginate ( 50 );
	// } else {
	// return $task->get ();
	// }
	// }
	// public function forUserByRemindTime($startTime, $endTime) {
	// return Task::where ( 'remindtime', '>', $startTime )->where ( 'remindtime', '<', $endTime )->where ( 'status', 1 )->orderBy ( 'priority', 'desc' )->orderBy ( 'updated_at', 'desc' )->get ();
	// }
	// public function forUserByDeadline($startTime, $endTime) {
	// return Task::where ( 'deadline', '>', $startTime )->where ( 'deadline', '<', $endTime )->where ( 'status', 1 )->orderBy ( 'priority', 'desc' )->orderBy ( 'updated_at', 'desc' )->get ();
	// }
	// public function forUserByUserIdRemindTime($user_id, $startTime, $endTime) {
	// return Task::where ( 'user_id', $user_id )->where ( 'remindtime', '>', $startTime )->where ( 'remindtime', '<', $endTime )->where ( 'status', 1 )->get ();
	// }
}
