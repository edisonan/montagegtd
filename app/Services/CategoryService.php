<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CategoryRepository;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

/**
 * 分类相关Service
 *
 * @author edison.an
 *        
 */
class CategoryService {
	
	/**
	 *
	 * @var CategoryRepository
	 */
	protected $categoryRepository;
	
	/**
	 *
	 * @param CategoryRepository $categories        	
	 */
	public function __construct(CategoryRepository $categoryRepository) {
		$this->categoryRepository = $categoryRepository;
	}
	
	/**
	 * 获取分类列表
	 *
	 * @param boolean $needAutoCreate        	
	 * @return
	 *
	 */
	public function getList($needAutoCreate = false) {
		$categories = $this->categoryRepository->getUserList ( Auth::id () );
		if ($needAutoCreate && count ( $categories ) == 0) {
			$category = $this->quickCreateCategory ( '未分类' );
			
			$categories = array (
					$category 
			);
		}
		return $categories;
	}
	
	/**
	 * 对分类进行排序
	 *
	 * @param array $categoryIds        	
	 * @return boolean
	 */
	public function setCategorySort($categoryIds) {
		$sort = 0;
		foreach ( $categoryIds as $categoryId ) {
			$category = $this->categoryRepository->getUserCategoryById ( Auth::id (), $categoryId );
			if (! empty ( $category )) {
				$category->update ( array (
						'category_order' => $sort ++ 
				) );
			}
		}
		return true;
	}
	
	/**
	 * 根据分类id获取分类信息
	 *
	 * @param int $categoryId        	
	 * @return
	 *
	 */
	public function getByCategoryId($categoryId) {
		$category = $this->categoryRepository->getUserCategoryById ( Auth::id (), $categoryId );
		return $category;
	}
	
	/**
	 * 根据分类id获取分类信息
	 *
	 * @param string $categoryName        	
	 * @param boolean $needAutoCreate        	
	 * @return
	 *
	 */
	public function getByCategoryName($categoryName, $needAutoCreate = false) {
		$category = $this->categoryRepository->getUserCategoryByName ( Auth::id (), $categoryName );
		if (empty ( $category ) && $needAutoCreate) {
			$category = $this->quickCreateCategory ( $categoryName );
		}
		return $category;
	}
	
	/**
	 * 快速创建分类
	 *
	 * @param string $categoryName        	
	 * @return \App\Models\Category
	 */
	public function quickCreateCategory($categoryName) {
		$category = new Category ();
		$category->name = $categoryName;
		$category->category_order = (int)Category::where ( 'user_id', Auth::id () )->max ( 'category_order' ) + 1;
		$category->user_id = Auth::id ();
		$category->save ();
		
		return $category;
	}
}
