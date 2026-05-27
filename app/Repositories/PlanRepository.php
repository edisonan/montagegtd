<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Plan;

class PlanRepository {
	
	/**
	 * 根据id获取目标
	 * 
	 * @param unknown $id        	
	 * @return unknown
	 */
	public function getPlanById($id) {
		return Plan::where ( 'id', $id )->first ();
	}
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return Plan::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, string $status, $needPage) {
	// $plans = Plan::where ( 'user_id', $user->id )->where ( 'status', $status );
	
	// if ($needPage) {
	// return $plans->paginate ( 20 );
	// } else {
	// return $plans->get ();
	// }
	// }
	
	// /**
	// * Get plan for plan id.
	// *
	// * @param User $user
	// * @param int $plan_id
	// * @return Collection
	// */
	// public function forPlanId(User $user, $plan_id) {
	// return Plan::where ( 'user_id', $user->id )->where ( 'id', $plan_id )->get ();
	// }
}
