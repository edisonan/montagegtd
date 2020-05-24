<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorCodeUtil;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Models\Feed;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 文章管理控制器
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
		
		// 页面参数
		$pageParams = array (
				'page_count' => $pageCount,
				'status' => $status,
				'category_id' => $categoryId,
				'feed_id' => $feedId 
		);
		
		// 避免图片加载
		$unableImg = isset ( $_COOKIE ['unable_img'] ) ? $_COOKIE ['unable_img'] : "false";
		// 避免描述加载
		$unableDesc = isset ( $_COOKIE ['unable_desc'] ) ? $_COOKIE ['unable_desc'] : "false";
		
		return view ( 'articles.index', [ 
				'article_subs' => $articleSubs,
				'status' => $status,
				'feed_id' => $feedId,
				'page_params' => $pageParams,
				'unable_img' => $unableImg,
				'unable_desc' => $unableDesc 
		] );
	}
	
	/**
	 * 订阅导航
	 *
	 * @param Request $request        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function navinfo(Request $request) {
		// 获取状态参数，默认参数值为 未读
		$status = $request->get ( 'status', 'unread' );
		
		// 获取分类文章数
		$navInfo = $this->articleService->getNavInfoAndNextRecommend ( $request->user (), $status );
		
		$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $navInfo );
		return response ( $resp );
	}
	
	/**
	 * 订阅导航数量获取
	 *
	 * @param Request $request        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function navcountinfo(Request $request) {
		// 获取状态参数，默认参数值为 未读
		$status = $request->get ( 'status', 'unread' );
		
		// 获取分类文章数
		$countInfo = $this->articleService->getCountInfos ( $request->user (), $status );
		
		$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $countInfo );
		return response ( $resp );
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
		
		// 查询文章集合
		$articles = $this->articleService->forUserByFeedId ( $request->user (), $feedId, $needPage = true, $pageCount );
		
		// 页面参数
		$pageParams = array ();
		$pageParams ['page_count'] = $pageCount;
		$pageParams ['feed_id'] = $feedId;
		
		return view ( 'articles.list', [ 
				'articles' => $articles,
				'feed' => $feed,
				'page_params' => $pageParams 
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
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $article );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $article,
					'is_feed' => $isFeed 
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
		$responseData = array ();
		$count = 0;
		
		if ($request->has ( 'ids' )) {
			$idArr = explode ( ',', $request->ids );
			$count = $this->articleService->setArticleSubStatusByIds ( $idArr );
		} else if ($request->has ( 'feed_id' )) {
			$count = $this->articleService->setArticleSubStatusByFeedId ( $request->feed_id );
		} else {
			$this->authorize ( 'destroy', $articleSub );
			if (in_array ( $request->status, array (
					'read',
					'unread',
					'read_later',
					'star' 
			) )) {
				$count = $this->articleService->setArticleSubStatus ( $articleSub, $request->status );
			}
		}
		
		$responseData ['count'] = $count;
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, $responseData );
			return response ( $resp );
		} else {
			return view ( 'articles.view', [ 
					'article' => $articleSub->article 
			] );
		}
	}
	
	/**
	 * 删除文章
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 */
	public function destroy(Request $request, ArticleSub $articleSub) {
		$this->authorize ( 'destroy', $articleSub );
		
		$articleSub->delete ();
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/articles' )->with ( 'message', 'IT WORKS!' );
		}
	}
	
	/**
	 * 标注文章笔记
	 *
	 * @param Request $request        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function mark(Request $request) {
		$this->validate ( $request, [ 
				'content' => 'required',
				'article_id' => 'required' 
		] );
		$this->articleService->mark ( $request );
		$resp = $this->responseJson ( ErrorCodeUtil::OK_CODE, null, '标注成功' );
		return response ( $resp );
	}
	
	/**
	 * 获取文章语音
	 *
	 * @param Request $request        	
	 * @param ArticleSub $articleSub        	
	 */
	public function getArticleRecord(Request $request, ArticleSub $articleSub) {
		if ($articleSub->user_id != $request->user ()->id) {
			$resp = $this->responseJson ( ErrorCodeUtil::SYSTEM_ERROR_CODE );
			return response ( $resp );
		}
		
		$url = $this->articleService->getArticleRecordUrl ( $articleSub->article );
		if (empty ( $url )) {
			$resp = $this->responseJson ( ErrorCodeUtil::SYSTEM_ERROR_CODE );
			return response ( $resp );
		} else {
			header ( 'Content-type: audio/mp3' );
			readfile ( $url );
		}
	}
}
