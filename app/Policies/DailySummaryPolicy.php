<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DailySummary;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailySummaryPolicy {
	use HandlesAuthorization;
	
	/**
	 * Determine if the given user can delete the given task.
	 *
	 * @param User $user        	
	 * @param Task $task        	
	 * @return bool
	 */
	public function destroy(User $user, DailySummary $dailySummary) {
		return $user->id === $dailySummary->user_id;
	}
}
