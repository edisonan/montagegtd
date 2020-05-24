<?php

namespace App\Services;

use App\Http\Utils\AipSpeech;
use App\Models\User;
use App\Models\Article;
use App\Models\ArticleMark;
use App\Models\ArticleSub;
use App\Repositories\CategoryRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\FeedSubRepository;
use App\Repositories\ArticleSubRepository;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Http\Requests\Request;

/**
 * 文章管理相关业务逻辑
 *
 * @author edison.an
 *        
 */
class ArticleService {
	
	/**
	 * 构造方法
	 */
	public function __construct() {
	}
	
	/**
	 * 获取当前状态下，每个订阅的数量
	 *
	 * @param User $user        	
	 * @param int $status        	
	 * @return array like feed_id=>count
	 */
	public function getCountInfos(User $user, $status) {
		// 获取每个订阅的数量
		$temp_counts = ArticleSub::select ( 'feed_id', DB::raw ( 'count(*) as total' ) )->where ( 'user_id', $user->id )->where ( 'status', $status )->groupBy ( 'feed_id' )->get ();
		
		// 将订阅组合成如下结构: feed_id=>count
		$counts_info = array ();
		foreach ( $temp_counts as $temp_count ) {
			$counts_info [$temp_count ['feed_id']] = $temp_count ['total'];
		}
		
		return $counts_info;
	}
	
	/**
	 * 获取文章分类导航和下一篇推荐信息
	 *
	 * @param User $user        	
	 * @param string $feedId        	
	 * @return number[][]|array[][]|NULL[][]
	 */
	public function getNavInfoAndNextRecommend(User $user, $status, $feedId = '') {
		// 通过订阅ID获取分类信息.
		$category_feed_infos = DB::select ( 'select c.id as category_id,c.name as category_name,f.feed_id as feed_id,f.feed_name as feed_name from feed_subs f,categories c where f.category_id = c.id and f.user_id = :user_id and f.status =1 order by c.category_order asc,f.feed_order asc', [ 
				':user_id' => $user->id 
		] );
		
		// $counts_info = $this->getCountInfos($user, $status);
		// TODO
		$counts_info = array ();
		
		// 导航信息，结构如下:
		// category_id category_info => category_name category_id
		$nav_infos = array ();
		
		// 推荐订阅信息，结构如下:
		// feed_id feed_name feed_count
		$next_recommend_feed = array ();
		
		foreach ( $category_feed_infos as $item ) {
			$nav_infos [$item->category_id] ['category_info'] = array (
					'category_name' => $item->category_name,
					'category_id' => $item->category_id 
			);
			
			$feed = array (
					'feed_id' => $item->feed_id,
					'feed_name' => $item->feed_name,
					'feed_count' => isset ( $counts_info [$item->feed_id] ) ? $counts_info [$item->feed_id] : 0 
			);
			
			if ($feed ['feed_count'] != 0 && empty ( $next_recommend_feed )) {
				if (! empty ( $feedId )) {
					$feedId != $feed ['feed_id'] ? $next_recommend_feed = $feed : '';
				} else {
					$next_recommend_feed = $feed;
				}
			}
			
			$nav_infos [$item->category_id] ['list'] [] = $feed;
		}
		
		foreach ( $nav_infos as $key => $val ) {
			$nav_infos [$key] ['list'] = $this->sortFeed ( $nav_infos [$key] ['list'] );
		}
		return array (
				'nav_infos' => $nav_infos,
				'next_recommend_feed' => $next_recommend_feed 
		);
	}
	
	/**
	 * 根据不同条件 获取相关文章信息
	 *
	 * @param string $feedId        	
	 * @param string $categoryId        	
	 * @return array
	 */
	public function getArticleSubs(User $user, $status, $pageCount, $feedId = '', $categoryId = '') {
		$feedIdArr = array ();
		if (! empty ( $feedId )) {
			$feedIdArr [] = $feedId;
		} else if (! empty ( $categoryId )) {
			// 通过分类ID获取订阅文章集
			$feedsubs = DB::table ( 'feed_subs' )->select ( 'feed_id' )->where ( 'category_id', $categoryId )->where ( 'status', 1 )->get ();
			
			$feedIdArr = array ();
			foreach ( $feedsubs as $feedsub ) {
				$feedIdArr [] = $feedsub->feed_id;
			}
		}
		
		$articleSubs = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status );
		if (! empty ( $feedIdArr )) {
			$articleSubs = $articleSubs->whereIn ( 'feed_id', $feedIdArr );
		}
		
		$articleSubs = $articleSubs->orderBy ( 'updated_at', 'desc' )->simplePaginate ( $pageCount );
		return $articleSubs;
	}
	
	/**
	 * 获取文章列表
	 *
	 * @param string $user        	
	 * @param string $feedId        	
	 * @param boolean $needPage        	
	 * @param int $pageCount        	
	 * @return array
	 */
	public function forUserByFeedId($user, $feedId, $needPage = true, $pageCount) {
		// 查看订阅源
		$feed = Feed::where ( 'id', $feedId )->first ();
		if (empty ( $feed )) {
			abort ( 404 );
		}
		return ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'feed_id', $feedId )->orderBy ( 'updated_at', 'desc' )->simplePaginate ( $pageCount );
	}
	
	/**
	 * 判断是否订阅
	 *
	 * @param User $user        	
	 * @param string $status        	
	 * @param string $feedId        	
	 * @return boolean
	 */
	public function isFeedArticle($user, $status, $feedId) {
		$feedSub = FeedSub::where ( 'user_id', $user->id )->where ( 'feed_id', $feedId )->where ( 'status', $status )->first ();
		return empty ( $feedSub ) ? false : true;
	}
	
	/**
	 * 获取语音地址
	 *
	 * @param Article $article        	
	 * @return string
	 */
	public function getActiveRecordUrl($article) {
		$path = config ( "app.storage_path" ) . 'article_records/' . $article->id . '.mp3';
		if (file_exists ( $path )) {
			return $path;
		} else {
			// 识别正确返回语音二进制 错误则返回json 参照下面错误码
			$aipSpeech = new AipSpeech ( env ( 'BD_APP_ID', '' ), env ( 'BD_API_KEY', '' ), env ( 'BD_SECRET_KEY', '' ) );
			$result = $aipSpeech->synthesis ( strip_tags ( $article->content ), 'zh', 1, array (
					'per' => 3 
			) );
			if (! is_array ( $result )) {
				file_put_contents ( $path, $result );
				return $path;
			} else {
				Log::info ( 'create article record error::' . json_encode ( $result ) );
				return '';
			}
		}
	}
	
	/**
	 * 设置文章阅读状态
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function setArticleSubStatusByIds($ids, $status = 'read') {
		return ArticleSub::whereIn ( 'id', $ids )->where ( 'user_id', Auth::user ()->id )->where ( 'status', 'unread' )->update ( [ 
				'status' => $status,
				'updated_at' => date ( 'Y-m-d H:i:s' ) 
		] );
	}
	
	/**
	 * 设置文章阅读状态
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function setArticleSubStatusByFeedId($feedId, $status = 'read') {
		return ArticleSub::where ( 'feed_id', $feedId )->where ( 'user_id', Auth::user ()->id )->where ( 'status', 'unread' )->update ( [ 
				'status' => $status,
				'updated_at' => date ( 'Y-m-d H:i:s' ) 
		] );
	}
	
	/**
	 * 设置文章阅读状态
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function setArticleSubStatus($articleSub, $status = 'read') {
		$articleSub->status = $status;
		$articleSub->updated_at = date ( 'Y-m-d H:i:s' );
		return $articleSub->update ();
	}
	public function mark(Request $request) {
		$article = Article::where ( 'user_id', $request->user ()->id )->where ( 'id', $request->article_id )->first ();
		
		if (empty ( $article )) {
			echo 'error article_id';
			exit ();
		}
		
		$articleMark = new ArticleMark ();
		$articleMark->user_id = $request->user ()->id;
		$articleMark->article_id = $request->article_id;
		$articleMark->content = $request->content;
		$articleMark->save ();
	}
	
	/**
	 * 按照数量针对订阅进行排序
	 *
	 * @param array $feeds        	
	 * @return array
	 */
	private function sortFeed($feeds) {
		foreach ( $feeds as $key => $feed ) {
			if ($feed ['feed_count'] == 0) {
				$feeds [] = $feed;
				unset ( $feeds [$key] );
			}
		}
		return $feeds;
	}
}
