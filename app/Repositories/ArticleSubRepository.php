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
    public function forUserByStatus(User $user,string $status,$need_page=false,$page_size=30)
    {
    	$article = ArticleSub::where('user_id', $user->id)
		    	->where('status',$status)->orderBy('updated_at','desc');
    	
    	if($need_page){
    		return $article->paginate($page_size);
    	} else {
    		return $article->get();
    	}
    }
	/**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByCategoryStatusFeedId(User $user,string $status,$category_id,$need_page=false,$page_size=30)
    {
    	$article = ArticleSub::where('user_id', $user->id)
		->whereIn('feed_id', function($query) use($category_id){
			$query->select('feed_id')
			->from('feed_subs')
			->where('category_id', $category_id)
			->where('status', 1);
		})
    	->where('status',$status)
    	->orderBy('updated_at','desc');
    	if($need_page){
    		return $article->paginate($page_size);
    	} else {
    		return $article->get();
    	}
    }
    
    public function forUserByStatusFeedId(User $user,string $status,$feed_id,$need_page=false,$page_size=30)
    {
		echo $page_size; exit;
    	$article = ArticleSub::where('user_id', $user->id)
    	->where('status',$status)
    	->where('feed_id',$feed_id)->orderBy('updated_at','desc');
    	if($need_page){
    		return $article->paginate($page_size);
    	} else {
    		return $article->get();
    	}
    }
    
    public function getRecentPublishList(User $user,string $status,$start_time,$end_time,$limit){
    	return ArticleSub::where('user_id',$user->id)->where('status',$status)->where('published','<',$end_time)->where('published','>',$start_time)->orderBy('feed_id')->limit($limit)->get();
    }
    
}
