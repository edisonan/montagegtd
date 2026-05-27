<?php

namespace App\Repositories;

use App\Models\Feed;
use Illuminate\Support\Facades\Log;

class FeedRepository {

    /**
     * 获取推荐订阅源列表（分页）
     * @return mixed
     */
	public function getRecommendedList() {
		return Feed::where ( 'is_recommend', 1 )->orderBy ( 'recommend_order', 'desc' )->paginate ( 9 );
	}
	
	/**
	 * 根据推荐分类id获取订阅源
	 * 
	 * @param int $recommendCategoryId
	 * @return array
	 */
	public function getListByRecommendCategoryId($recommendCategoryId) {
		return Feed::where ( 'recommend_category_id', $recommendCategoryId )->orderBy ( 'recommend_order', 'desc' )->paginate ( 48 );
	}
	
	/**
	 * 根据订阅源名称获取订阅源
	 * 
	 * @param unknown $feedName        	
	 * @return unknown
	 */
	public function getListByFeedName($feedName) {
		return Feed::where ( 'feed_name', 'like', '%' . $feedName . '%' )->orderBy ( 'recommend_order', 'desc' )->paginate ( 48 );
	}
	
	/**
	 * 根据活跃顶级获取全部订阅源
	 * 
	 * @param unknown $activeLevel        	
	 * @return unknown
	 */
	public function getAllListByActiveLevel($activeLevel) {
		return Feed::where ( 'status', 1 )->where ( 'active_level', $activeLevel )->get ();
	}
	
	/**
	 * 根据订阅源url获取订阅源
	 * 
	 * @param unknown $url        	
	 * @return unknown
	 */
	public function getFeedByUrl($url) {
		return Feed::where ( 'url', $url )->first ();
	}
	/**
	 * 根据订阅源id获取订阅源
	 * 
	 * @param unknown $id        	
	 * @return unknown
	 */
	public function getFeedById($id) {
		return Feed::where ( 'id', $id )->first ();
	}
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user, $needPage = false) {
	// $feed = Feed::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' );
	
	// if ($needPage) {
	// return $feed->paginate ( 50 );
	// } else {
	// return $feed->get ();
	// }
	// }
	// public function forIsRecommend($is_recommend, $needPage = false) {
	// $feed = Feed::where ( 'is_recommend', $is_recommend )->orderBy ( 'recommend_order', 'desc' );
	
	// if ($needPage) {
	// return $feed->paginate ( 9 );
	// } else {
	// return $feed->get ();
	// }
	// }
	// public function findByRecommendCategoryId($recommend_category_id, $needPage = false) {
	// $feed = Feed::where ( 'recommend_category_id', $recommend_category_id )->orderBy ( 'recommend_order', 'desc' );
	
	// if ($needPage) {
	// return $feed->paginate ( 48 );
	// } else {
	// return $feed->get ();
	// }
	// }
	// public function findByName($name, $needPage = false) {
	// $feed = Feed::where ( 'feed_name', 'like', '%' . $name . '%' )->orderBy ( 'recommend_order', 'desc' );
	
	// if ($needPage) {
	// return $feed->paginate ( 48 );
	// } else {
	// return $feed->get ();
	// }
	// }
	
	// /**
	// * get feed list by type and status
	// *
	// * @param unknown $type
	// * @param unknown $status
	// */
	// public function getListByActiveLevelStatus($active_level, $status) {
	// return Feed::where ( 'status', $status )->where ( 'active_level', $active_level )->get ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, string $status) {
	// return Feed::where ( 'user_id', $user->id )->where ( 'status', $status )->get ();
	// }
	
	// /**
	// * Get plan for plan id.
	// *
	// * @param User $user
	// * @param int $plan_id
	// * @return Collection
	// */
	// public function forFeedId(User $user, $feedId) {
	// return Feed::where ( 'user_id', $user->id )->where ( 'id', $feedId )->get ();
	// }
}
