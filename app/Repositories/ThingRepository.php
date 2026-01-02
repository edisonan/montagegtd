<?php

namespace App\Repositories;

use App\Models\Thing;

class ThingRepository {
	/**
	 * 获取记事列表
	 *
	 * @return Collection
	 */
	public function getUserList($userId) {
		return Thing::where ( 'user_id', $userId )->orderBy ( 'updated_at', 'desc' )->paginate ( 50 );
	}
	
	/**
	 * 为总结获取记事列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getListForSummary($userId, $startTime, $endTime) {
		return Thing::where ( 'user_id', $userId )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}

    /**
     * 通过id获取事情信息
     *
     * @param int $id
     * @return unknown
     */
    public function getThingById($id) {
        return Thing::where ( 'id', $id )->first ();
    }
}
