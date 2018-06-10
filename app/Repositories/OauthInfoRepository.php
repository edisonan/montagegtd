<?php

namespace App\Repositories;

use App\User;
use App\OauthInfo;

class OauthInfoRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user, $need_page)
    {
        $oauth_info = OauthInfo::where('user_id', $user->id)
                    ->orderBy('updated_at', 'desc');
        if($need_page){
        	return $oauth_info->paginate(50);
        } else {
        	return $oauth_info->get();
        }
    }
    
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forByThirdUidAndDriver(string $third_uid,string $driver)
    {
    	return OauthInfo::where('third_uid', $third_uid)->where('driver', $driver)
    	->orderBy('updated_at', 'desc')
    	->first();
    }
    
}
