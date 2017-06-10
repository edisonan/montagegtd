<?php

namespace App\Repositories;

use App\User;
use App\Category;

class CategoryRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return Category::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->get();
    }
    
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByStatus(User $user,string $status)
    {
    	return Category::where('user_id', $user->id)
		    	->where('status',$status)
		    	->get();
    }
    
    /**
     * Get goal for goal id.
     *
     * @param  User  $user
     * @param  int  $goal_id
     * @return Collection
     */
    public function forCategoryId(User $user,$category_id)
    {
    	return Category::where('user_id', $user->id)
    	->where('id',$category_id)
    	->get();
    }
}
