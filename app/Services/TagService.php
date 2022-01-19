<?php

namespace App\Services;

use App\Models\Tag;

/**
 * 标签业务逻辑
 *
 * @author edison.an
 *        
 */
class TagService {
	/**
	 * get tag by name
	 *
	 * @param unknown $name        	
	 */
	public function getByTagName($name, $needAutoCreate = false) {
		$tag = Tag::where ( 'name', $name )->first ();
		if (empty ( $tag ) && $needAutoCreate) {
			$tag = Tag::create ( array (
					'name' => $name 
			) );
		}
		return $tag;
	}
}
