<?php

namespace App\Repositories;

use App\User;
use App\Note;

class NoteRepository
{
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return Note::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
    
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forStatus($status)
    {
        return Note::where('status', $status)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
    
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByStatus(User $user,$status,$need_page=false)
    {
    	$note = Note::with(['noteTagMaps.tag','user'])->where('status', $status)
    	->orWhere('user_id', $user->id)
    	->orderBy('created_at', 'desc');
    	if($need_page){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
    
}
