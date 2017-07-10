<?php

namespace App\Repositories;

use App\User;
use App\ArticleSub;

class ArticleSubRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return ArticleSub::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->get();
    }
    
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByStatus(User $user,string $status,$need_page=false)
    {
    	$article = ArticleSub::where('user_id', $user->id)
		    	->where('status',$status)->orderBy('id','desc');
    	
    	if($need_page){
    		return $article->paginate(10);
    	} else {
    		return $article->get();
    	}
    }
    
    public function forUserByStatusFeedId(User $user,string $status,$feed_id,$need_page=false)
    {
    	$article = ArticleSub::where('user_id', $user->id)
    	->where('status',$status)
    	->where('feed_id',$feed_id)->orderBy('id','desc');
    	if($need_page){
    		return $article->paginate(10);
    	} else {
    		return $article->get();
    	}
    }
    
}
