<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanPolicy {
	use HandlesAuthorization;
	
	/**
	 * Determine if the given user can delete the given task.
	 *
	 * @param User $user        	
	 * @param Plan $plan        	
	 * @return bool
	 */
	public function destroy(User $user, Plan $plan) {
		return $user->id === $plan->user_id;
	}
}
