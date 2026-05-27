<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Journal;
use Illuminate\Auth\Access\HandlesAuthorization;

class JournalPolicy {
	use HandlesAuthorization;
	
	/**
	 * Determine if the given user can delete the given task.
	 *
	 * @param User $user        	
	 * @param Task $task        	
	 * @return bool
	 */
	public function destroy(User $user, Journal $journal) {
		return $user->id === $journal->user_id;
	}
}
