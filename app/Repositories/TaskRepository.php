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
	 * 通过状态和模式获取该用户所有列表
	 * 
	 * @param string $status        	
	 * @param string $mode        	
	 * @return unknown
	 */
	public function getUserAllListByStatusMode($userId, $status = '', $mode = '') {
		$tasks = Task::with ( 'goal' )->where ( 'user_id', $userId );
		
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
	// $task = Task::with ( 'goal' )->where ( 'user_id', $user->id )->where ( 'status', $status );
	
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
