<?php

namespace App\Repositories;

use App\Models\Journal;

class JournalRepository {
	/**
	 * 获取记事列表
	 *
	 * @return Collection
	 */
	public function getUserList($userId) {
		return Journal::where ( 'user_id', $userId )->orderBy ( 'updated_at', 'desc' )->paginate ( 50 );
	}
	
	/**
	 * 获取记事列表(支持分页参数)
	 *
	 * @param array $filters
	 * @param int $pageSize
	 * @return Collection
	 */
	public function getJournalListWithPagination($filters, $pageSize = 10) {
		$journal = Journal::orderBy ( 'updated_at', 'desc' );
        if(isset($filters["user_id"])) {
            $journal = $journal->where("user_id", $filters['user_id']);
        }
        return $journal->paginate($pageSize);
	}
	
	/**
	 * 为总结获取记事列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getListForSummary($userId, $startTime, $endTime) {
		return Journal::where ( 'user_id', $userId )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}

    /**
     * 通过id获取手账信息
     *
     * @param int $id
     * @return unknown
     */
    public function getJournalById($id) {
        return Journal::where ( 'id', $id )->first ();
    }
}
