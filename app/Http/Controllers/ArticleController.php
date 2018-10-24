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
 * 
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
		
		$this->articleService = $articleService;
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
		$categoryId = $request->get ( 'category_id', '' );
		// 获取订阅id 默认为空
		$feedId = $request->get ( 'feed_id', '' );
		// 获取状态参数，默认参数值为 未读
		$status = $request->get ( 'status', 'unread' );
		// 获取每页数量，默认参数值为 20
		$pageCount = $request->get ( 'page_count', 20 );
		
		// 获取订阅文章
		$articleSubs = $this->articleService->getArticleSubs ( $request->user (), $status, $pageCount, $feedId, $categoryId );
		
		// 避免图片加载
		$unable_img = isset ( $_COOKIE ['unable_img'] ) ? $_COOKIE ['unable_img'] : "false";
		
		// 避免描述加载
		$unable_desc = isset ( $_COOKIE ['unable_desc'] ) ? $_COOKIE ['unable_desc'] : "false";
		
		// 页面参数
		$page_params = array (
				'page_count' => $pageCount,
				'status' => $status,
				'category_id' => $categoryId,
				'feed_id' => $feedId 
		);
		
		return view ( 'articles.index', [ 
				'article_subs' => $articleSubs,
				'status' => $status,
				'feed_id' => $feedId,
				'page_params' => $page_params,
				'unable_img' => $unable_img,
				'unable_desc' => $unable_desc 
		] );
	}
	
	/**
	 * 根据feedId展示文章列表
	 * 
	 * @param Request $request        	
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function list(Request $request) {
		// 获取每页数量，默认参数值为 20
		$pageCount = $request->get ( 'page_count', 20 );
		// 获取订阅id
		$feedId = $request->get ( 'feed_id' );
		
		// 查看订阅源
		$feed = Feed::where ( 'id', $feedId )->first ();
		if (empty ( $feed )) {
			abort ( 404 );
		}
		
		// 查询文章集合
		$articles = $this->articleService->forUserByFeedId ( $request->user (), $feedId, $needPage = true, $pageCount );
		
		// 页面参数
		$page_params = array ();
		$page_params ['page_count'] = $pageCount;
		$page_params ['feed_id'] = $request->feed_id;
		
		return view ( 'articles.list', [ 
				'articles' => $articles,
				'feed' => $feed,
				'page_params' => $page_params 
		] );
	}
	
	/**
	 * 文章详情
	 * 
	 * @param Request $request        	
	 * @param Article $article        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function view(Request $request, Article $article) {
		// 是否已订阅此源
		$isFeed = false;
		
		// 查看是否登录, 如果登录, 查看是否已经订阅
		if (Auth::check ()) {
			$isFeed = $this->articleService->isFeedArticle ( $request->user (), '1', $article->feed->id );
		}
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE, $article );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $article,
					'is_feed' => $isFeed 
			] );
		}
	}
	
	/**
	 * 收藏文章
	 * 
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function star(Request $request, ArticleSub $articleSub) {
		$this->authorize ( 'destroy', $articleSub );
		
		if (! $articleSub->active) {
			$articleSub->status = 'read';
		} else {
			$articleSub->status = 'star';
		}
		$articleSub->update ();
		
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
	 * 稍后阅读文章
	 * 
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function read_later(Request $request, ArticleSub $articleSub) {
		$this->authorize ( 'destroy', $articleSub );
		
		$articleSub->status = in_array($articleSub->status, array('read_later', 'unread')) ? 'read_later' : $articleSub->status;
		$articleSub->update ();
		
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
	 * 设置文章状态
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
