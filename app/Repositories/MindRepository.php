<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Mind;
use DB;

class MindRepository {
	/**
	 * 获取时间段内统计情况
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getStatisticCounts($startTime, $endTime) {
		return Mind::select ( 'user_id', DB::raw ( 'count(*) as total' ) )->where ( 'created_at', '>', $startTime )->where ( 'created_at', '<=', $endTime )->groupBy ( 'user_id' )->get ();
	}
	
	/**
	 * 获取思维导图列表
	 * 
	 * @param unknown $userId        	
	 */
	public function getUserRootMindList($userId, $tagId='', $name='') {
		$query = Mind::with ( [
            		'mindTagMaps.tag',
        	] )->where ( 'status', 1 )->where ( 'is_root', 1 )->where ( 'user_id', $userId );
		if(!empty($tagId)) {
			$maps = DB::table ( 'mind_tag_maps' )->select ( array (
                    		'mind_tag_maps.mind_id'
                	) )->where ( 'tag_id', $tagId )->get ();
                	$mindids = array ();
                	foreach ( $maps as $map ) {
                    		$mindids [] = $map->mind_id;
                	}
                	$query->whereIn ( 'id', $mindids );
		}
		if(!empty($name)) {
			$query->where('name', 'like', '%'.$name.'%');	
		}
		
		return $query->orderBy('id', 'desc')->paginate(50);
	}
	
	/**
	 * 根据id获取思维导图节点
	 * 
	 * @param unknown $userId        	
	 * @param unknown $id        	
	 */
	public function getUserMindById($userId, $id) {
		return Mind::where ( 'id', $id )->where ( 'user_id', $userId )->first ();
	}
	
	/**
	 * 为总结获取记事列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getListForSummary($userId, $startTime, $endTime) {
		return Mind::where ( 'user_id', $userId )->where ( 'is_root', 1 )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return Mind::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' )->get ();
	// }
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forStatus($status) {
	// return Mind::where ( 'status', $status )->orderBy ( 'created_at', 'desc' )->get ();
	// }
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, $status, $is_root, $needPage = false) {
	// $note = Mind::where ( 'status', $status )->where ( 'is_root', $is_root )->where ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' );
	
	// if ($needPage) {
	// return $note->paginate ( 50 );
	// } else {
	// return $note->get ();
	// }
	// }
}
