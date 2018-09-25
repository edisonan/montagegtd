<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\FeedSub;

class FeedSubRepository
{
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return FeedSub::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
    
    public function forUserByFeedId(User $user,$feed_id,$status)
    {
        return FeedSub::where('user_id', $user->id)->where('feed_id', $feed_id)->where('status',$status)
                    ->first();
    }
    
    public function forUserByFeedSubId(User $user,$feed_sub_id,$status)
    {
    	return FeedSub::where('user_id', $user->id)->where('id', $feed_sub_id)->where('status',$status)
    	->first();
    }
    
    /**
     * Get all of the notes for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByStatus(User $user,$status,$need_page=false)
    {
    	$note = FeedSub::with(['feed','category'])->where('status', $status)
    	->where('user_id', $user->id)
    	->orderBy('created_at', 'desc');
    	
    	if($need_page){
    		return $note->paginate(50);
    	} else {
    		return $note->get();
    	}
    }
    
}
