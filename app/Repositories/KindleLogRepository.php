<?php

namespace App\Repositories;

use App\User;
use App\KindleLog;

class KindleLogRepository
{
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return KindleLog::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
    
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByStatus(User $user,$status,$is_root,$need_page=false)
    {
    	$note = KindleLog::where('status', $status)
    	->where('user_id', $user->id)
    	->orderBy('created_at', 'desc');
    	
    	if($need_page){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
    
}
