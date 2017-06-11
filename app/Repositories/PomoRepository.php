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
    public function forUser(User $user,$need_page=false)
    {
        $pomo = Pomo::where('user_id', $user->id)
                    ->orderBy('created_at', 'asc');
        if($need_page){
        	return $pomo->paginate(50);
        } else {
        	return $pomo->get();
        }
    }
    
    /**
     * Get all of the pomos for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByTime(User $user, $time)
    {
        return Pomo::where('user_id', $user->id)
        			->where('created_at', '>' ,$time)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
}
