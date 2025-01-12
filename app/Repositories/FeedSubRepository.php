<?php

namespace App\Repositories;

use App\Models\FeedSub;
use Illuminate\Support\Facades\DB;

class FeedSubRepository {
	
	/**
	 * 通过分类ID获取订阅源ids
	 *
	 * @return NULL[]
	 */
	public function getFeedIdsByCategoryId($categoryId) {
		$feedSubs = DB::table ( 'feed_subs' )->select ( 'feed_id' )->where ( 'category_id', $categoryId )->where ( 'status', 1 )->get ();
		
		$feedIds = array ();
		foreach ( $feedSubs as $feedsub ) {
			$feedIds [] = $feedsub->feed_id;
		}
		return $feedIds;
	}
	
	/**
	 * 获取用户带分类信息的订列表 inner join
	 * 
	 * @param unknown $userId        	
	 * @return unknown
	 */
	public function getCategoryFeedInfos($userId) {
		$categoryFeedInfos = DB::select ( 'select c.id as category_id,c.name as category_name,f.feed_id as feed_id,f.feed_name as feed_name from feed_subs f,categories c where f.category_id = c.id and f.user_id = :user_id and f.status =1 order by c.category_order asc,f.feed_order asc', [ 
				':user_id' => $userId 
		] );
		return $categoryFeedInfos;
	}
	
	/**
	 * 通过订阅源id和状态获取该用户单条订阅信息
	 * 
	 * @param unknown $userId        	
	 * @param unknown $feedId        	
	 * @param number $status        	
	 * @return unknown
	 */
	public function getUserFeedSubByFeedIdStatus($userId, $feedId, $status = 1) {
		return FeedSub::where ( 'user_id', $userId )->where ( 'feed_id', $feedId )->where ( 'status', $status )->first ();
	}
	
	/**
	 * 获取用户订阅列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $feedId        	
	 * @param number $status        	
	 * @return unknown
	 */
	public function getUserFeedSubList($userId, $status = 1) {
		return FeedSub::with ( [ 
				'feed',
				'category' 
		] )->where ( 'status', $status )->where ( 'user_id', $userId )->orderBy ( 'id', 'desc' )->paginate ( 50 );
	}
	
	/**
	 * 获取用户带分类信息的订列表 right join
	 * 
	 * @param unknown $userId        	
	 * @return unknown
	 */
	public function getUserFeedSubListWithCategory($userId) {
		$sql = 'select c.id as category_id,c.name as category_name,f.feed_id as feed_id,f.feed_name as feed_name,f.id as feed_sub_id ';
		$sql .= ' from feed_subs f right join categories c on f.category_id = c.id ';
		$sql .= ' where c.user_id = :user_id2 and f.user_id = :user_id  and f.status =1 ';
		$sql .= ' order by c.category_order asc,f.feed_order asc';
		$categoryFeedInfos = DB::select ( $sql, [ 
				':user_id' => $userId,
				':user_id2' => $userId 
		] );
		return $categoryFeedInfos;
	}
	
	/**
	 * 根据订阅源id获取该用户订阅信息
	 * 
	 * @param unknown $userId        	
	 * @param unknown $feedId        	
	 * @return unknown
	 */
	public function getUserFeedByFeedId($userId, $feedId) {
		return FeedSub::where ( 'user_id', $userId )->where ( 'feed_id', $feedId )->where ( 'status', 1 )->first ();
	}
	
	/**
	 * 根据id获取该用户订阅信息
	 * 
	 * @param unknown $userId        	
	 * @param unknown $id        	
	 * @return unknown
	 */
	public function getUserFeedById($userId, $id) {
		return FeedSub::where ( 'user_id', $userId )->where ( 'id', $id )->first ();
	}
	
	/**
	 * 根据订阅源和状态获取所有订阅信息
	 * 
	 * @param unknown $feedId        	
	 * @param unknown $status        	
	 * @return unknown
	 */
	public function getFeedSubAllListByFeedIdAndStatus($feedId, $status) {
		return FeedSub::where ( 'feed_id', $feedId )->where ( 'status', $status )->get ();
		;
	}
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return FeedSub::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' )->get ();
	// }
	// public function forUserByFeedId(User $user, $feedId, $status) {
	// return FeedSub::where ( 'user_id', $user->id )->where ( 'feed_id', $feedId )->where ( 'status', $status )->first ();
	// }
	// public function forUserByFeedSubId(User $user, $feed_sub_id, $status) {
	// return FeedSub::where ( 'user_id', $user->id )->where ( 'id', $feed_sub_id )->where ( 'status', $status )->first ();
	// }
	
	// /**
	// * Get all of the notes for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, $status, $needPage = false) {
	// $note = FeedSub::with ( [
	// 'feed',
	// 'category'
	// ] )->where ( 'status', $status )->where ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' );
	
	// if ($needPage) {
	// return $note->paginate ( 50 );
	// } else {
	// return $note->get ();
	// }
	// }
}
