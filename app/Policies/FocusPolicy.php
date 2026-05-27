<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Focus;
use Illuminate\Auth\Access\HandlesAuthorization;

class FocusPolicy {
	use HandlesAuthorization;
	
	/**
	 * Determine if the given user can delete the given focus.
	 *
	 * @param User $user        	
	 * @param Task $focus        	
	 * @return bool
	 */
	public function destroy(User $user, Focus $focus) {
		return $user->id === $focus->user_id;
	}
}
