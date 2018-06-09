<?php

namespace App\Repositories;

use App\Cal;

class CalRepository
{
    
    /**
     * Get all of the cals for a given status.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forByNameAndStatus(string $theme, string $status,$need_page=false)
    {
    	$cal = Cal::where('status',$status)->where('theme',$theme)->orderBy('id','asc');
    	
    	if($need_page){
    		return $cal->paginate(10);
    	} else {
    		return $cal->get();
    	}
    }
    
}
