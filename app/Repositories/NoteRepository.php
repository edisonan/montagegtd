<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Note;

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
    public function forUserByStatus(User $user,$status,$needPage=false)
    {
    	$note = Note::with(['noteTagMaps.tag','user'])->where('status', $status)
    	->orWhere('user_id', $user->id)
    	->orderBy('created_at', 'desc');
    	if($needPage){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
    
    public function getAll($conditions,$pages = array('need_page' => true, 'page_count' => 50))
    {
    	$note = Note::with(['noteTagMaps.tag','user'])->where('status', 1)->orWhere('user_id', $conditions['user_id']);
    	if(isset($conditions['keyword'])){
	    	$note = $note->where('name','like' , "%".$conditions['keyword']."%");
    	}
    	
    	$note = $note->orderBy('created_at', 'desc');
    	if($pages['need_page']){
    		return $note->paginate($pages['page_count']);
    	} else {
    		return $note->get();
    	}
    }
    
}
