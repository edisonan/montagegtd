<?php

namespace App\Repositories;

use App\Models\DailySummary;

class DailySummaryRepository {
	
	/**
	 * 获取该用户的日报列表（分页）
	 * 
	 * @param int $userId
	 *        	用户id
	 * @return unknown
	 */
	public function getUserList($userId) {
		return DailySummary::where ( 'user_id', $userId )->where ( 'status', 1 )->orderBy ( 'id', 'desc' )->paginate ( 20 );
	}
	
	/**
	 * 根据日报时间获取该用户日报信息
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param int $summaryDate
	 *        	日报时间
	 * @return unknown
	 */
	public function getUserDailySummaryByDate($userId, $summaryDate) {
		return DailySummary::where ( 'user_id', $userId )->where ( 'summary_date', $summaryDate )->first ();
	}
}
