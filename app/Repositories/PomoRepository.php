<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Pomo;
use DB;

class PomoRepository {
	/**
	 * 根据id获取番茄
	 * 
	 * @param unknown $id        	
	 * @return unknown
	 */
	public function getPomoById($id) {
		return Pomo::where ( 'id', $id )->first ();
	}
	
	/**
	 * 根据状态获取该用户番茄列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $status        	
	 * @return unknown
	 */
	public function getUserListByStatus($userId, $status) {
		$pomos = Pomo::where ( 'user_id', $userId )->where ( 'status', $status )->orderBy ( 'id', 'desc' );
		return $pomos->paginate ( 50 );
	}
	
	/**
	 * 根据结束时间和状态获取该用户所有番茄列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $status        	
	 * @param unknown $endTime        	
	 * @return unknown
	 */
	public function getUserAllListByStatusAndEndTime($userId, $status, $endTime) {
		return Pomo::where ( 'user_id', $userId )->where ( 'status', $status )->where ( 'end_time', '>', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}
	
	/**
	 * 获取该用户最近一个番茄
	 * 
	 * @param unknown $userId        	
	 * @return unknown
	 */
	public function getUserRecentPomo($userId) {
		return Pomo::where ( 'user_id', \Auth::id () )->orderBy ( 'id', 'desc' )->first ();
	}
	
	/**
	 * 是否存在此番茄后的新番茄
	 * 
	 * @param unknown $pomoId        	
	 * @param unknown $userId        	
	 * @return boolean
	 */
	public function existNewPomoById($pomoId, $userId) {
		$pomo = Pomo::where ( 'user_id', $userId )->where ( 'id', '>', $pomoId )->first ();
		return empty ( $pomo ) ? false : true;
	}
	
	/**
	 * 是否存在此时间后的新番茄
	 * 
	 * @param unknown $pomoId        	
	 * @param unknown $userId        	
	 * @return boolean
	 */
	public function existNewPomoByAfterStartTime($startTime, $userId) {
		$pomo = Pomo::where ( 'user_id', $userId )->where ( 'start_time', '>', $startTime )->first ();
		return empty ( $pomo ) ? false : true;
	}
	
	/**
	 * 获取该用户时间区间内未记录的番茄列表
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @return unknown
	 */
	public function getAllListByBetweenEndTime($startTime, $endTime) {
		return Pomo::where ( 'status', 1 )->where ( 'end_time', '>', $startTime )->where ( 'end_time', '<', $endTime )->get ();
	}
	
	/**
	 * 获取该用户时间区间内休息后未开启的番茄列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @return unknown
	 */
	public function getAllListByRestBetweenEndTime($startTime, $endTime) {
		return Pomo::where ( 'status', 2 )->where ( 'rest_end_time', '>', $startTime )->where ( 'rest_end_time', '<', $endTime )->get ();
	}
	
	/**
	 * 获取时间段内统计情况
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getStatisticCounts($startTime, $endTime) {
		return Pomo::select ( 'user_id', DB::raw ( 'count(*) as total' ) )->where ( 'status', 2 )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->groupBy ( 'user_id' )->get ();
	}
	
	// /**
	// * Get all of the pomos for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user, $needPage = false) {
	// $pomo = Pomo::where ( 'user_id', $user->id )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $pomo->paginate ( 50 );
	// } else {
	// return $pomo->get ();
	// }
	// }
	// public function forUserByStatus(User $user, $status, $needPage = false) {
	// $pomo = Pomo::where ( 'user_id', $user->id )->where ( 'status', $status )->orderBy ( 'updated_at', 'desc' );
	
	// if ($needPage) {
	// return $pomo->paginate ( 50 );
	// } else {
	// return $pomo->get ();
	// }
	// }
	// public function forUserActivePomo(User $user) {
	// return Pomo::where ( 'user_id', $user->id )->where ( 'status', 1 )->first ();
	// }
	
	// /**
	// * Get all of the pomos for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByTime(User $user, $time) {
	// return Pomo::where ( 'user_id', $user->id )->where ( 'status', 2 )->where ( 'created_at', '>', $time )->orderBy ( 'created_at', 'desc' )->get ();
	// }
	// public function create($attr) {
	// return Pomo::create ( $attr );
	// }
}
