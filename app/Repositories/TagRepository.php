<?php

namespace App\Repositories;

use App\User;
use App\Tag;

class TagRepository
{
	/**
	 * get tag by name
	 * @param unknown $name
	 */
    public function forTagName($name){
    	return Tag::where('name', $name)
    	->first();
    }
}
