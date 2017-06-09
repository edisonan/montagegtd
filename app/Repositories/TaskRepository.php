<?php

namespace App\Repositories;

use App\User;
use App\Task;

class TaskRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return Task::where('user_id', $user->id)
                    ->orderBy('updated_at', 'desc')
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
    	return Task::where('user_id', $user->id)
    	->where('status',$status)
    	->orderBy('priority', 'desc')
    	->orderBy('updated_at', 'desc')
    	->get();
    }
    
    public function forUserByRemindTime($start_time, $end_time)
    {
    	return Task::where('remindtime', '>', $start_time)
    	->where('remindtime', '<', $end_time)
    	->where('status',1)
    	->orderBy('priority', 'desc')
    	->orderBy('updated_at', 'desc')
    	->get();
    }
}
