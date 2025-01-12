<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Third;

class ThirdRepository {
	
	/**
	 * 获取该用户第三方信息
	 * 
	 * @param unknown $userId        	
	 * @param unknown $thirdId        	
	 * @param unknown $source        	
	 */
	public function getUserThirdByThirdIdAndSource($userId, $thirdId, $source) {
		return Third::where ( 'user_id', $userId )->where ( 'third_id', $thirdId )->where ( 'source', $source )->orderBy ( 'created_at', 'asc' )->first ();
	}
	/**
	 * 获取该用户第三方信息
	 * 
	 * @param unknown $userId        	
	 * @param unknown $source        	
	 */
	public function getUserThirdBySource($userId, $source) {
		return Third::where ( 'user_id', $userId )->where ( 'source', $source )->orderBy ( 'created_at', 'asc' )->first ();
	}
	/**
	 * 获取该用户第三方信息
	 * 
	 * @param unknown $userId        	
	 * @param unknown $source        	
	 */
	public function getThirdByThirdId($thirdId) {
		return Third::where ( 'third_id', $thirdId )->first ();
	}
	
	/**
	 * Get all of the tasks for a given user.
	 *
	 * @param User $user        	
	 * @return Collection
	 */
	public function forUser(User $user) {
		return Third::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	}
	public function forUserThirdId(User $user, $third_id, $source = 'fanfou') {
		return Third::where ( 'user_id', $user->id )->where ( 'third_id', $third_id )->where ( 'source', $source )->orderBy ( 'created_at', 'asc' )->first ();
	}
	public function forUserSource(User $user, $source = 'fanfou') {
		return Third::where ( 'user_id', $user->id )->where ( 'source', $source )->orderBy ( 'created_at', 'asc' )->first ();
	}
	public function forThirdId($third_id) {
		return Third::where ( 'third_id', $third_id )->first ();
	}
}
