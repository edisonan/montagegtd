<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Focus;
use DB;

class FocusRepository {
	/**
	 * 根据id获取专注
	 * 
	 * @param unknown $id        	
	 * @return unknown
	 */
	public function getFocusById($id) {
		return Focus::where ( 'id', $id )->first ();
	}
	
	/**
	 * 根据状态获取该用户专注列表
	 * 
	 * @param unknown $userId         
	 * @param unknown $status         
	 * @return unknown
	 */
	public function getUserListByStatus($userId, $status) {
        $focus_datas = Focus::where ( 'user_id', $userId )->where ( 'status', $status )->orderBy ( 'id', 'desc' );
		return $focus_datas->paginate ( 50 );
	}
		
	/**
	 * 根据状态获取该用户专注列表(支持分页参数)
	 * 
	 * @param unknown $userId
	 * @param unknown $status
	 * @return unknown
	 */
	public function getFocusListWithPagination($filters = [], $pageSize = 10) {
		$focus = Focus::orderBy ( 'id', 'desc' );
        if (isset($filters['user_id'])){
            $focus = $focus->where ( 'user_id', $filters['user_id'] );
        }
        if (isset($filters['status'])){
            $focus = $focus->where ( 'status', $filters['status'] );
        }
		return $focus->simplePaginate ($pageSize);
	}

	/**
	 * 获取用户专注统计：总完成数 + 今日完成数
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getUserDoneCounts($userId) {
		$todayStart = date('Y-m-d 00:00:00');
		$baseQuery = Focus::where('user_id', $userId)->where('status', 2);

		return array(
			'total' => (int)(clone $baseQuery)->count(),
			'today' => (int)(clone $baseQuery)->where('end_time', '>=', $todayStart)->count(),
		);
	}
	
	/**
	 * 根据结束时间和状态获取该用户所有专注列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $status        	
	 * @param unknown $endTime        	
	 * @return unknown
	 */
	public function getUserAllListByStatusAndEndTime($userId, $status, $endTime) {
		return Focus::where ( 'user_id', $userId )->where ( 'status', $status )->where ( 'end_time', '>', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}
	
	/**
	 * 获取该用户最近一个专注
	 * 
	 * @param unknown $userId        	
	 * @return unknown
	 */
	public function getUserRecentFocus($userId) {
		return Focus::where ( 'user_id', \Auth::id () )->orderBy ( 'id', 'desc' )->first ();
	}
	
	/**
	 * 是否存在此专注后的新专注
	 * 
	 * @param unknown $focusId        	
	 * @param unknown $userId        	
	 * @return boolean
	 */
	public function existNewFocusById($focusId, $userId) {
		$focus = Focus::where ( 'user_id', $userId )->where ( 'id', '>', $focusId )->first ();
		return empty ( $focus ) ? false : true;
	}
	
	/**
	 * 是否存在此时间后的新专注
	 * 
	 * @param unknown $focusId        	
	 * @param unknown $userId        	
	 * @return boolean
	 */
	public function existNewFocusByAfterStartTime($startTime, $userId) {
		$focus = Focus::where ( 'user_id', $userId )->where ( 'start_time', '>', $startTime )->first ();
		return empty ( $focus ) ? false : true;
	}
	
	/**
	 * 获取该用户时间区间内未记录的专注列表
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @return unknown
	 */
	public function getAllListByBetweenEndTime($startTime, $endTime) {
		return Focus::where ( 'status', 1 )->where ( 'end_time', '>', $startTime )->where ( 'end_time', '<', $endTime )->get ();
	}
	
	/**
	 * 获取该用户时间区间内休息后未开启的专注列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @return unknown
	 */
	public function getAllListByRestBetweenEndTime($startTime, $endTime) {
		return Focus::where ( 'status', 2 )->where ( 'rest_end_time', '>', $startTime )->where ( 'rest_end_time', '<', $endTime )->get ();
	}
	
	/**
	 * 获取时间段内统计情况
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getStatisticCounts($startTime, $endTime) {
		return Focus::select ( 'user_id', DB::raw ( 'count(*) as total' ) )->where ( 'status', 2 )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->groupBy ( 'user_id' )->get ();
	}
	
	// /**
	// * Get all of the focuss for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user, $needPage = false) {
	// $focus = Focus::where ( 'user_id', $user->id )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $focus->paginate ( 50 );
	// } else {
	// return $focus->get ();
	// }
	// }
	// public function forUserByStatus(User $user, $status, $needPage = false) {
	// $focus = Focus::where ( 'user_id', $user->id )->where ( 'status', $status )->orderBy ( 'updated_at', 'desc' );
	
	// if ($needPage) {
	// return $focus->paginate ( 50 );
	// } else {
	// return $focus->get ();
	// }
	// }
	// public function forUserActiveFocus(User $user) {
	// return Focus::where ( 'user_id', $user->id )->where ( 'status', 1 )->first ();
	// }
	
	// /**
	// * Get all of the focuss for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByTime(User $user, $time) {
	// return Focus::where ( 'user_id', $user->id )->where ( 'status', 2 )->where ( 'created_at', '>', $time )->orderBy ( 'created_at', 'desc' )->get ();
	// }
	// public function create($attr) {
	// return Focus::create ( $attr );
	// }
}
