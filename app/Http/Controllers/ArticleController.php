<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;
use App\Models\ArticleMark;
use App\Models\Feed;
use App\Models\ArticleSub;
use App\Services\ArticleService;

/**
 * 文章管理Controller
 *
 * @author edison.an
 *        
 */
class ArticleController extends Controller
{

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
    public function __construct(ArticleService $articleService)
    {
        $this->middleware('auth', [
            'except' => [
                'welcome',
                'view'
            ]
        ]);
        
        $this->articleService = $articleService;
    }

    /**
     * 欢迎页
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function welcome(Request $request)
    {
        return view('articles.welcome', []);
    }

    /**
     * 订阅文章列表
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        // 获取分类id 默认为空
        $categoryId = $request->get('category_id', '');
        // 获取订阅id 默认为空
        $feedId = $request->get('feed_id', '');
        // 获取状态参数，默认参数值为 未读
        $status = $request->get('status', 'unread');
        // 获取每页数量，默认参数值为 20
        $pageCount = $request->get('page_count', 20);
        
        // 获取订阅文章
        $articleSubs = $this->articleService->getArticleSubs($request->user(), $status, $pageCount, $feedId, $categoryId);
        
        // 避免图片加载
        $unable_img = isset($_COOKIE['unable_img']) ? $_COOKIE['unable_img'] : "false";
        
        // 避免描述加载
        $unable_desc = isset($_COOKIE['unable_desc']) ? $_COOKIE['unable_desc'] : "false";
        
        // 页面参数
        $page_params = array(
            'page_count' => $pageCount,
            'status' => $status,
            'category_id' => $categoryId,
            'feed_id' => $feedId
        );
        
        return view('articles.index', [
            'article_subs' => $articleSubs,
            'status' => $status,
            'feed_id' => $feedId,
            'page_params' => $page_params,
            'unable_img' => $unable_img,
            'unable_desc' => $unable_desc
        ]);
    }
    
    public function navinfo(Request $request)
    {
    	// 获取状态参数，默认参数值为 未读
    	$status = $request->get('status', 'unread');
    
    	// 获取分类文章数
    	$navInfo = $this->articleService->getNavInfoAndNextRecommend($request->user(),$status);
    
    	$resp = $this->responseJson(self::OK_CODE, $navInfo);
    	return response($resp);
    }

    /**
     * 根据feedId展示文章列表
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function list(Request $request)
    {
        // 获取每页数量，默认参数值为 20
        $pageCount = $request->get('page_count', 20);
        // 获取订阅id
        $feedId = $request->get('feed_id');
        
        // 查看订阅源
        $feed = Feed::where('id', $feedId)->first();
        if (empty($feed)) {
            abort(404);
        }
        
        // 查询文章集合
        $articles = $this->articleService->forUserByFeedId($request->user(), $feedId, $needPage = true, $pageCount);
        
        // 页面参数
        $page_params = array();
        $page_params['page_count'] = $pageCount;
        $page_params['feed_id'] = $feedId;
        
        return view('articles.list', [
            'articles' => $articles,
            'feed' => $feed,
            'page_params' => $page_params
        ]);
    }

    /**
     * 文章详情
     *
     * @param Request $request
     * @param Article $article
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function view(Request $request, Article $article)
    {
        // 是否已订阅此源
        $isFeed = false;
        
        // 查看是否登录, 如果登录, 查看是否已经订阅
        if (Auth::check()) {
            $isFeed = $this->articleService->isFeedArticle($request->user(), '1', $article->feed->id);
        }
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE, $article);
            return response($resp);
        } else {
            return view('articles.view', [
                'article' => $article,
                'is_feed' => $isFeed
            ]);
        }
    }

    /**
     * 设置文章状态
     *
     * @param Request $request
     * @param ArticleSub $articleSub
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function status(Request $request, ArticleSub $articleSub)
    {
        $responseData = array();
        $count = 0;
        
        if ($request->has('ids')) {
            $id_arr = explode(',', $request->ids);
            $count = $this->articleService->setArticleSubStatusByIds($id_arr);
        } else if ($request->has('feed_id')) {
            $count = $this->articleService->setArticleSubStatusByIds($id_arr);
        } else {
            $this->authorize('destroy', $articleSub);
            if (in_array($request->status, array(
                'read',
                'unread',
                'read_later',
                'star'
            ))) {
                $count = $this->articleService->setArticleSubStatus($articleSub, $request->status);
            }
        }
        
        $responseData['count'] = $count;
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE, $responseData);
            return response($resp);
        } else {
            return view('articles.view', [
                'article' => $articleSub->article
            ]);
        }
    }

    /**
     * 删除文章
     *
     * @param Request $request
     * @param ArticleSub $articleSub
     */
    public function destroy(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        
        $articleSub->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/articles')->with('message', 'IT WORKS!');
        }
    }

    /**
     * 标注文章笔记
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function mark(Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
            'article_id' => 'required'
        ]);
        
        $article = Article::where('user_id', $request->user()->id)->where('id', $request->article_id)->first();
        
        if (empty($article)) {
            echo 'error article_id';
            exit();
        }
        
        $articleMark = new ArticleMark();
        $articleMark->user_id = $request->user()->id;
        $articleMark->article_id = $request->article_id;
        $articleMark->content = $request->content;
        $articleMark->save();
        
        $resp = $this->responseJson(self::OK_CODE, null, '标注成功');
        return response($resp);
    }

    /**
     * 获取文章语音
     *
     * @param Request $request
     * @param ArticleSub $articleSub
     */
    public function getArticleRecord(Request $request, ArticleSub $articleSub)
    {
        if ($articleSub->user_id == $request->user()->id) {
            $article = $articleSub->article;
            
            $url = $this->articleService->getArticleRecordUrl($article);
            if (empty($url)) {
                $resp = $this->responseJson(self::SYSTEM_ERROR_CODE);
                return response($resp);
            } else {
                header('Content-type: audio/mp3');
                readfile($url);
            }
        } else {
            $resp = $this->responseJson(self::SYSTEM_ERROR_CODE);
            return response($resp);
        }
    }
}
