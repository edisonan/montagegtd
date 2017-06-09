<?php

namespace App\Policies;

use App\User;
use App\Goal;
use Illuminate\Auth\Access\HandlesAuthorization;

class GoalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can delete the given task.
     *
     * @param  User  $user
     * @param  Goal  $goal
     * @return bool
     */
    public function destroy(User $user, Goal $goal)
    {
        return $user->id === $goal->user_id;
    }
}
