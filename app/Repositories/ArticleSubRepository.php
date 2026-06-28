<?php

namespace App\Repositories;

use App\Models\ArticleSub;
use Illuminate\Support\Facades\DB;

/**
 * 文章订阅关系Repository
 * 
 * @author edison.an
 *        
 */
class ArticleSubRepository {
	
	/**
	 * 根据状态用户id及订阅源等不同信息获取文章列表
	 *
	 * @param int $userId
	 *        	用户id
	 * @param int $status
	 *        	状态
	 * @param array $feedIds
	 *        	订阅源ids
	 * @param number $pageCount
	 *        	每页数量
	 * @return array
	 */
	public function getArticleSubList($userId, $status, $feedIds = array(), $pageCount = 20) {
		$articleSubs = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $userId )->where ( 'status', $status );
		if (! empty ( $feedIds )) {
			$articleSubs = $articleSubs->whereIn ( 'feed_id', $feedIds );
		}
		$articleSubs = $articleSubs->orderBy ( 'updated_at', 'desc' )->simplePaginate ( $pageCount );
		return $articleSubs;
	}
	
	/**
	 * 获取某用户所有订阅源该状态下的数量
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param string $status
	 *        	状态
	 * @return unknown
	 */
	public function getFeedCountInfos($userId, $status) {
		$originalCountInfos = DB::select ( "select feed_id,count(*) as count from article_subs where user_id = :user_id and status = :status group by feed_id", [ 
				':user_id' => $userId,
				':status' => $status 
		] );
		return $originalCountInfos;
	}
	
	/**
	 * 通过订阅源id获取某用户文章列表
	 *
	 * @param int $userId
	 *        	用户id
	 * @param int $feedId
	 *        	订阅源id
	 * @param int $pageCount
	 *        	每页数量
	 */
	public function getArticleListByFeedId($userId, $feedId, $pageCount = 20) {
		return ArticleSub::with ( 'article.feed' )->where ( 'user_id', $userId )->where ( 'feed_id', $feedId )->orderBy ( 'updated_at', 'desc' )->simplePaginate ( $pageCount );
	}

    public function findByUserIdAndArticleId($userId, $articleId)
    {
        return ArticleSub::with('article.feed')
            ->where('user_id', $userId)
            ->where('article_id', $articleId)
            ->orderBy('updated_at', 'desc')
            ->first();
    }
	
	/**
	 * 通过文章订阅ids调整文章阅读状态
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param array $ids
	 *        	文章订阅关系ids
	 * @param string $status
	 *        	状态
	 */
	public function setArticleSubStatusByIds($userId, $ids, $status) {
		return ArticleSub::whereIn ( 'id', $ids )->where ( 'user_id', $userId )->where ( 'status', 'unread' )->update ( [ 
				'status' => $status,
				'updated_at' => date ( 'Y-m-d H:i:s' ) 
		] );
	}
	
	/**
	 * 通过订阅源id调整文章阅读状态
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param int $feedId
	 *        	订阅源id
	 * @param string $status
	 *        	状态
	 */
	public function setArticleSubStatusByFeedId($userId, $feedId, $status) {
		return ArticleSub::where ( 'feed_id', $feedId )->where ( 'user_id', $userId )->where ( 'status', 'unread' )->update ( [ 
				'status' => $status,
				'updated_at' => date ( 'Y-m-d H:i:s' ) 
		] );
	}
	
	/**
	 * 获取时间段内统计情况
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getStatisticCounts($startTime, $endTime) {
		return ArticleSub::select ( 'user_id', DB::raw ( 'count(*) as total' ) )->where ( 'status', 'read' )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->groupBy ( 'user_id' )->get ();
		;
	}
	
	/**
	 * 为总结获取记事列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getListForSummary($userId, $startTime, $endTime) {
		return ArticleSub::where ( 'user_id', $userId )->whereIn ( 'status', array (
				'star',
				'read_later' 
		) )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return ArticleSub::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, string $status, $needPage = false, $pageCount = 30) {
	// // $article = DB::table('article_subs')->join('articles', 'article_subs.article_id', '=', 'articles.id')
	// // ->join('feeds', 'articles.feed_id', '=', 'feeds.id')->where('article_subs.status',$status)->where('article_subs.user_id', $user->id)
	// // ->orderBy('article_subs.updated_at', 'desc')->limit($pageCount)->get();
	
	// // return $article;
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->orderBy ( 'id', 'desc' );
	
	// if ($needPage) {
	// return $article->simplePaginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByCategoryStatusFeedId(User $user, string $status, $category_id, $needPage = false, $pageCount = 30) {
	// $feedsubs = DB::table ( 'feed_subs' )->select ( 'feed_id' )->where ( 'category_id', $category_id )->where ( 'status', 1 )->get ();
	
	// $feedId_arr = array ();
	// foreach ( $feedsubs as $feedsub ) {
	// $feedId_arr [] = $feedsub->feed_id;
	// }
	
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->whereIn ( 'feed_id', $feedId_arr )->where ( 'status', $status );
	
	// // $article = ArticleSub::with('article.feed')->where('user_id', $user->id)
	// // ->whereIn('feed_id', function($query) use($category_id){
	// // \Log::info('sub query start:'.time());
	// // $query->select('feed_id')
	// // ->from('feed_subs')
	// // ->where('category_id', $category_id)
	// // ->where('status', 1);
	// // \Log::info('sub query end:'.time());
	// // })
	// // ->where('status',$status);
	// /*
	// * $article = \DB::table('article_subs')->with('articles')
	// * ->where(['article_subs.user_id'=>$user->id])
	// * ->where(['article_subs.status'=>$status])
	// * ->join('articles', 'articles.id', '=', 'article_subs.article_id')
	// * ->leftJoin("feed_subs",'feed_subs.feed_id','=','article_subs.feed_id')
	// * ->where(['feed_subs.category_id'=>$category_id])
	// * ->where(['feed_subs.status'=>1]);
	// */
	
	// if ($needPage) {
	// return $article->paginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function forUserByStatusFeedId(User $user, string $status, $feedId, $needPage = false, $pageCount = 30) {
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->where ( 'feed_id', $feedId )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $article->paginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function forUserByFeedId(User $user, $feedId, $needPage = false, $pageCount = 30) {
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'feed_id', $feedId )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $article->simplePaginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function getRecentPublishList(User $user, string $status, $start_time, $end_time, $limit) {
	// return ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->where ( 'published', '<', $end_time )->where ( 'published', '>', $start_time )->orderBy ( 'feed_id' )->limit ( $limit )->get ();
	// }
}
