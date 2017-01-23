<?php

namespace App\Repositories;

use App\User;
use App\Pomo;

class PomoRepository
{
    /**
     * Get all of the pomos for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return Pomo::where('user_id', $user->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
    }
}
