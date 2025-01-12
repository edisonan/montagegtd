<?php

namespace App\Repositories;

use App\Models\Article;

/**
 * 文章Repository
 * 
 * @author edison.an
 *        
 */
class ArticleRepository {
	
	/**
	 * 根据文章id获取文章信息
	 * 
	 * @param int $id
	 *        	文章id
	 */
	public function getArticleById($id) {
		return Article::where ( 'id', $id )->first ();
	}
	
	/**
	 * 获取某订阅源最近发布的文章列表
	 * 
	 * @param int $feedId
	 *        	订阅源id
	 */
	public function getRecentPublishArticleListByFeedId($feedId, $publishedCount = 30) {
		return Article::where ( 'feed_id', $feedId )->orderBy ( 'published', 'desc' )->take ( $publishedCount )->get ();
	}
	
	/**
	 *
	 * @param unknown $feedId        	
	 * @param unknown $url        	
	 * @return boolean
	 */
	public function existArticleByFeedIdAndUrl($feedId, $url) {
		$count = Article::where ( [ 
				'feed_id' => $feedId,
				'url' => $url 
		] )->count ();
		return $count > 0;
	}
	
	/**
	 *
	 * @param unknown $feedId        	
	 * @param unknown $subject        	
	 * @return boolean
	 */
	public function existArticleByFeedIdAndSubject($feedId, $subject) {
		$count = Article::where ( [ 
				'feed_id' => $feedId,
				'subject' => $subject 
		] )->count ();
		return $count > 0;
	}
	
	// public function forUser(User $user) {
	// return Article::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return Article::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, string $status, $needPage = false) {
	// $article = Article::with ( 'feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->orderBy ( 'published', 'desc' );
	
	// if ($needPage) {
	// return $article->paginate ( 10 );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function forUserByStatusFeedId(User $user, string $status, $feedId, $needPage = false) {
	// $article = Article::where ( 'user_id', $user->id )->where ( 'status', $status )->where ( 'feed_id', $feedId )->orderBy ( 'published', 'desc' );
	// if ($needPage) {
	// return $article->paginate ( 10 );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function forUserByFeedId(User $user, $feedId, $needPage = false) {
	// $article = Article::where ( 'feed_id', $feedId )->orderBy ( 'published', 'desc' );
	// if ($needPage) {
	// return $article->paginate ( 10 );
	// } else {
	// return $article->get ();
	// }
	// }
	
	// /**
	// * Get goal for goal id.
	// *
	// * @param User $user
	// * @param int $goal_id
	// * @return Collection
	// */
	// public function forArticleId(User $user, $article_id) {
	// return Article::where ( 'user_id', $user->id )->where ( 'id', $article_id )->get ();
	// }
}
