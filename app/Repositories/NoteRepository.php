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
    
    public function forUserByArticle(User $user,$article_id,$needPage=false)
    {
    	$note = Note::with(['noteTagMaps.tag','user'])
    	->where('user_id', $user->id)
    	->where('article_id', $article_id)
    	->orderBy('created_at', 'desc');
    	if($needPage){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
    
    public function forUserByTask(User $user,$task_id,$needPage=false)
    {
    	$note = Note::with(['noteTagMaps.tag','user'])
    	->where('user_id', $user->id)
    	->where('task_id', $task_id)
    	->orderBy('created_at', 'desc');
    	if($needPage){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
    
    public function forUserByPomo(User $user,$pomo_id,$needPage=false)
    {
    	$note = Note::with(['noteTagMaps.tag','user'])
    	->where('user_id', $user->id)
    	->where('pomo_id', $pomo_id)
    	->orderBy('created_at', 'desc');
    	if($needPage){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
}
