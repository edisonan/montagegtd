<?php

namespace App\Repositories;

use App\User;
use App\Article;

class ArticleRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return Article::where('user_id', $user->id)
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
    	$article = Article::where('user_id', $user->id)
		    	->where('status',$status)->orderBy('published','desc');
    	
    	if($need_page){
    		return $article->paginate(10);
    	} else {
    		return $article->get();
    	}
    }
    
    public function forUserByStatusFeedId(User $user,string $status,$feed_id,$need_page=false)
    {
    	$article = Article::where('user_id', $user->id)
    	->where('status',$status)
    	->where('feed_id',$feed_id)->orderBy('published','desc');
    	if($need_page){
    		return $article->paginate(10);
    	} else {
    		return $article->get();
    	}
    }
    
    public function forUserByFeedId(User $user,$feed_id,$need_page=false)
    {
    	$article = Article::where('feed_id',$feed_id)->orderBy('published','desc');
    	if($need_page){
    		return $article->paginate(10);
    	} else {
    		return $article->get();
    	}
    }
    
    /**
     * Get goal for goal id.
     *
     * @param  User  $user
     * @param  int  $goal_id
     * @return Collection
     */
    public function forArticleId(User $user,$article_id)
    {
    	return Article::where('user_id', $user->id)
    	->where('id',$article_id)
    	->get();
    }
}
