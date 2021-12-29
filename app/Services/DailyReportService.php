<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailyReport;

/**
 * 业务逻辑
 *
 * @author edison.an
 *        
 */
class DailyReportService {
	/**
	 * Get all of the tasks for a given user.
	 *
	 * @param User $user        	
	 * @return Collection
	 */
	public function forUser(User $user) {
		return DailyReport::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	}
	
	/**
	 * Get all of the tasks for a given user.
	 *
	 * @param User $user        	
	 * @return Collection
	 */
	public function forUserByStatus(User $user, string $status, $needPage) {
		$goals = DailyReport::where ( 'user_id', $user->id )->where ( 'status', $status );
		
		if ($needPage) {
			return $goals->paginate ( 20 );
		} else {
			return $goals->get ();
		}
	}
	
	/**
	 * Get goal for goal id.
	 *
	 * @param User $user        	
	 * @param int $report_id        	
	 * @return Collection
	 */
	public function forDailyReportId(User $user, $report_id) {
		return DailyReport::where ( 'user_id', $user->id )->where ( 'id', $report_id )->get ();
	}

	public function forDailyReportDate(User $user, $report_date) {
    		return DailyReport::where ( 'user_id', $user->id )->where ( 'report_date', $report_date )->get ();
    	}
}
