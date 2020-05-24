<?php

namespace App\Services;

use App\Models\User;
use App\Models\Category;

/**
 * 文章分类业务逻辑
 *
 * @author edison.an
 *        
 */
class CategoryService {
	
	
	/**
	 * 构造方法
	 */
	public function __construct() {
	}
	
	/**
	 *
	 * @param User $user        	
	 * @param boolean $needPage        	
	 * @return
	 *
	 */
	public function getList(User $user, $needPage = true, $needAutoCreate = false) {
		$categories = Category::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->paginate ( 50 );
		
		if ($needAutoCreate && count ( $categories ) == 0) {
			$category = $user->categorys ()->create ( [ 
					'name' => '未分类',
					'category_order' => 0 
			] );
			$categories = array (
					$category 
			);
		}
		return $categories;
	}
	
	/**
	 *
	 * @param User $user        	
	 * @param boolean $needPage        	
	 * @return
	 *
	 */
	public function getByCategoryId(User $user, $categoryId) {
		$category = Category::where ( 'user_id', $user->id )->where ( 'id', $categoryId )->first ();
		;
		return $category;
	}
	
	/**
	 *
	 * @param User $user        	
	 * @param array $categoryIds        	
	 * @return boolean
	 */
	public function setCategorySort($user, $categoryIds) {
		$sort = 0;
		foreach ( $categoryIds as $categoryId ) {
			$category = Category::where ( 'user_id', $user->id )->where ( 'id', $categoryId )->first ();
			if (! empty ( $category )) {
				$category->update ( array (
						'category_order' => $sort ++ 
				) );
			}
		}
		return true;
	}
}
