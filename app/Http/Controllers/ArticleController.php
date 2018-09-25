<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\CategoryRepository;
use App\Models\Article;
use App\Models\ArticleMark;
use App\Repositories\ArticleRepository;
use App\Models\Feed;
use App\Models\ArticleSub;
use App\Repositories\FeedSubRepository;
use App\Repositories\ArticleSubRepository;
use App\Http\Utils\AipSpeech;
use App\Services\ArticleService;

/**
 * 文章管理Controller
 * @author edison.an
 *        
 */
class ArticleController extends Controller {
	/**
	 * ArticleService 实例
	 *
	 * @var ArticleService
	 */
	protected $articleService;
	
	/**
	 * 构造方法
	 *
	 * @param ArticleService $articleService        	
	 * @return void
	 */
	public function __construct(ArticleService $articleService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome',
						'view' 
				] 
		] );
		
		$this->$articleService = $articleService;
	}
	
	/**
	 * 欢迎页
	 * 
	 * @param Request $request        	
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function welcome(Request $request) {
		return view ( 'articles.welcome', [ ] );
	}
	
	/**
	 * 订阅文章列表
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request) {
		// 获取分类id 默认为空
		$category_id = $request->get('category_id', '');
		
		// 获取订阅id 默认为空
		$feed_id = $request->get('feed_id', '');
		
		// 获取状态参数，默认参数值为 未读
		$status = $request->get('status', 'unread');
		
		// 获取每页数量，默认参数值为 20
		$page_count = $request->get('page_count', 20);
		
		// 获取当前状态下，每个订阅的数量
		$counts_info = $this->articleService->getCountInfos($request->user (), $status);
		
		// 获取导航信息和下个推荐订阅
		$result = $this->articleService->getNavInfoAndNextRecommend($request->user(), $status);
		$nav_infos = $result['nav_infos'];
		$next_recommend_feed = $result['next_recommend_feed'];
		
		// 获取订阅文章
		$articleSubs = $this->articleService->getArticleSubs($request->user(), $status, $page_count, $feed_id, $category_id);
		
		// if article subs empty ,get recommend feeds
		if (count ( $articleSubs ) == 0) {
			$recommend_feeds = Feed::where ( 'user_id', '!=', $request->user ()->id )->where ( 'updated_at', '>', date ( 'Y-m-d' ) )->where ( 'is_recommend', 1 )->orderBy ( DB::raw ( 'RAND()' ) )->take ( 8 )->get ();
		} else {
			$recommend_feeds = array ();
		}
		
		// attr： advoid img load
		$unable_img = isset ( $_COOKIE ['unable_img'] ) ? $_COOKIE ['unable_img'] : "false";
		// attr: advoid desc load
		$unable_desc = isset ( $_COOKIE ['unable_desc'] ) ? $_COOKIE ['unable_desc'] : "false";
		
		// page params
		$page_params = array(
			'page_count' => $page_count,	
			'status' => $status,	
			'category_id' => $category_id,	
			'feed_id' => $feed_id,	
		);
		
		return view ( 'articles.index', [ 
				'nav_infos' => $nav_infos,
				'articleSubs' => $articleSubs,
				'status' => $status,
				'feed_id' => $feed_id,
				'page_params' => $page_params,
				'counts_info' => $counts_info,
				'recommend_feeds' => $recommend_feeds,
				'next_recommend_feed' => $next_recommend_feed,
				'unable_img' => $unable_img,
				'unable_desc' => $unable_desc 
		] );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function list(Request $request) {
		$page_params = array ();
		
		if ($request->has ( 'page_count' ) && is_int ( $request->page_count ) && $request->page_count < 500) {
			$page_count = $request->page_count;
		} else {
			$page_count = 50;
		}
		$page_params ['page_count'] = $page_count;
		
		if ($request->has ( 'feed_id' )) {
			$articles = $this->articles->forUserByFeedId ( $request->user (), $request->feed_id, $need_page = true, $page_count );
			$page_params ['feed_id'] = $request->feed_id;
		} else {
			echo 'error param';
			exit ();
		}
		
		$feed = Feed::where ( 'id', $request->feed_id )->first ();
		
		return view ( 'articles.list', [ 
				'articles' => $articles,
				'feed' => $feed,
				'page_params' => $page_params 
		] );
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param Article $article        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function view(Request $request, Article $article) {
		if (empty ( $article )) {
			echo 'error article';
			exit ();
		}
		
		$is_feed = false;
		if (Auth::check ()) {
			$articleSub = $this->articleSubs->forUserByStatusFeedId ( $request->user (), '1', $article->feed->id );
			if (count ( $articleSub ) > 0) {
				$is_feed = true;
			}
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, $article );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $article,
					'is_feed' => $is_feed 
			] );
		}
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function star(Request $request, ArticleSub $articleSub) {
		$this->authorize ( 'destroy', $articleSub );
		
		if (! $articleSub->active) {
			$articleSub->status = 'read';
			$articleSub->update ();
		} else {
			$articleSub->status = 'star';
			$articleSub->update ();
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, $articleSub->article );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $articleSub->article 
			] );
		}
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function read_later(Request $request, ArticleSub $articleSub) {
		$this->authorize ( 'destroy', $articleSub );
		
		if ($articleSub->status == 'star') {
			$articleSub->status = 'read';
			$articleSub->update ();
		} else {
			$articleSub->status = 'star';
			$articleSub->update ();
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, $articleSub->article );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $articleSub->article 
			] );
		}
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function status(Request $request, ArticleSub $articleSub) {
		if ($request->has ( 'ids' )) {
			$id_arr = explode ( ',', $request->ids );
			foreach ( $id_arr as $id ) {
				$articleSub = ArticleSub::where ( 'id', $id )->where ( 'user_id', $request->user ()->id )->first ();
				if (empty ( $articleSub )) {
					continue;
				} else {
					if ($articleSub->status == 'unread') {
						$articleSub->status = 'read';
						$articleSub->updated_at = date ( 'Y-m-d H:i:s' );
						$articleSub->update ();
					}
				}
			}
		} else if ($request->has ( 'feed_id' )) {
			$articleSubs = ArticleSub::where ( 'user_id', $request->user ()->id )->where ( 'status', 'unread' )->where ( 'feed_id', $request->feed_id )->get ();
			foreach ( $articleSubs as $articleSub ) {
				if (empty ( $articleSub )) {
					continue;
				} else {
					$articleSub->status = 'read';
					$articleSub->updated_at = date ( 'Y-m-d H:i:s' );
					$articleSub->update ();
				}
			}
		} else {
			$this->authorize ( 'destroy', $articleSub );
			if (in_array ( $request->status, array (
					'read',
					'unread',
					'read_later',
					'star' 
			) )) {
				$articleSub->status = $request->status;
				$articleSub->updated_at = date ( 'Y-m-d H:i:s' );
				$articleSub->update ();
			}
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, $articleSub->article );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $articleSub->article 
			] );
		}
	}
	
	/**
	 * Destroy the given task.
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 */
	public function destroy(Request $request, ArticleSub $articleSub) {
		$this->authorize ( 'destroy', $articleSub );
		
		$articleSub->delete ();
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/articles' )->with ( 'message', 'IT WORKS!' );
		}
	}
	
	/**
	 *
	 * @param Request $request        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function mark(Request $request) {
		$this->validate ( $request, [ 
				'content' => 'required',
				'article_id' => 'required' 
		] );
		
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
		
		$resp = $this->responseJson ( self::OK_CODE, null, '标注成功' );
		return response ( $resp );
	}
	
	/**
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
	
	/**
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 */
	public function getArticleRecord(Request $request, ArticleSub $articleSub) {
		if ($articleSub->user_id == $request->user ()->id) {
			$article = $articleSub->article;
			
			if (file_exists ( config ( "app.storage_path" ) . 'article_records/' . $article->id . '.mp3' )) {
				header ( 'Content-type: audio/mp3' );
				readfile ( config ( "app.storage_path" ) . 'article_records/' . $article->id . '.mp3' );
			} else {
				$aipSpeech = new AipSpeech ( env ( 'BD_APP_ID', '' ), env ( 'BD_API_KEY', '' ), env ( 'BD_SECRET_KEY', '' ) );
				$result = $aipSpeech->synthesis ( strip_tags ( $article->content ), 'zh', 1, array (
						'per' => 3 
				) );
				// 识别正确返回语音二进制 错误则返回json 参照下面错误码
				if (! is_array ( $result )) {
					file_put_contents ( config ( "app.storage_path" ) . 'article_records/' . $article->id . '.mp3', $result );
					header ( 'Content-type: audio/mp3' );
					readfile ( config ( "app.storage_path" ) . 'article_records/' . $article->id . '.mp3' );
				} else {
					Log::info ( 'create article record error' . serialize ( $result ) );
				}
			}
		} else {
			echo 'error' . $request->user ()->user_id;
			exit ();
		}
	}
}
