<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 文章管理控制器
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
     * @return
     *
     */
    public function welcome(Request $request)
    {
        return view('articles.welcome', []);
    }

    /**
     * 文章列表
     *
     * @param Request $request
     * @return
     *
     */
    public function index(Request $request)
    {
        // 页面参数
        $pageCount = $request->input('page_count', 20);
        $status = $request->input('status', 'unread');
        $categoryId = $request->input('category_id', '');
        $feedId = $request->input('feed_id', '');

        // 获取订阅文章
        $articleSubs = $this->articleService->getArticleSubList($status, $pageCount, $feedId, $categoryId);

        return view('articles.index', [
            'article_subs' => $articleSubs,
            'page_params' => [
                'page_count',
                'status',
                'category_id',
                'feed_id'
            ],
            'status' => $status,
            'feed_id' => $feedId,
            'unable_img' => isset ($_COOKIE ['unable_img']) ? $_COOKIE ['unable_img'] : "false",
            'unable_desc' => isset ($_COOKIE ['unable_desc']) ? $_COOKIE ['unable_desc'] : "false"
        ]);
    }

    /**
     * 分类信息
     *
     * @param Request $request
     * @return
     *
     */
    public function navinfo(Request $request)
    {
        // 获取状态参数，默认参数值为 未读
        $status = $request->input('status', 'unread');

        // 获取分类文章数
        $navInfo = $this->articleService->getNavInfo($status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'nav_infos' => $navInfo
        )));
    }

    /**
     * 分类文章量信息
     *
     * @param Request $requests
     * @return
     */
    public function navcountinfo(Request $request)
    {
        $status = $request->input('status', 'unread'); // 获取状态参数，默认参数值为 未读

        // 获取分类文章数
        $feedCountInfos = $this->articleService->getFeedCountInfos(Auth::id(), $status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($feedCountInfos));
    }

    /**
     * 根据feedId展示文章列表
     *
     * @param Request $request
     * @return
     *
     */
    public function list(Request $request)
    {
        $feedId = $request->get('feed_id'); // 获取订阅id
        $pageCount = $request->input('page_count', 20); // 获取每页数量，默认参数值为 20

        // 查询文章集合
        $articleInfos = $this->articleService->getArticleListByFeedId($feedId, $pageCount);

        return view('articles.list', array_merge($articleInfos, [
            'page_params' => [
                'page_count' => $pageCount,
                'feed_id' => $feedId
            ]
        ]));
    }

    /**
     * 文章详情
     * @param Request $request
     * @param Article $article
     * @return mixed
     * @throws CustomException
     */
    public function view(Request $request, Article $article)
    {
        // 是否已订阅此源
        $isFeed = false;

        // 查看是否登录, 如果登录, 查看是否已经订阅
        if (Auth::check()) {
            $isFeed = $this->articleService->isFeed($article->feed->id);
        }

        if ($isFeed == false && $article->feed->audit_status == 0) {
            throw new CustomException("订阅源审核中,审核通过后可正常分享");
        }

        return $this->jsonAndViewAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'article' => $article,
            'is_feed' => $isFeed
        ]), 'articles.view');
    }

    /**
     * 设置文章状态
     * @param Request $request
     * @param ArticleSub $articleSub
     * @return mixed
     * @throws CustomException
     */
    public function status(Request $request, ArticleSub $articleSub)
    {
        $processCount = 0;

        if ($request->has('ids')) {
            $processCount = $this->articleService->setArticleSubStatusByIds(explode(',', $request->ids));
        } else if ($request->has('feed_id')) {
            $processCount = $this->articleService->setArticleSubStatusByFeedId($request->feed_id);
        } else {
            $this->authorize('destroy', $articleSub);
            if (!in_array($request->status, array(
                'read',
                'unread',
                'read_later',
                'star'
            ))) {
                throw new CustomException ("status状态上送错误");
            }
            $processCount = $this->articleService->setArticleSubStatus($articleSub, $request->status);
        }

        return $this->jsonAndViewAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'article' => $articleSub->article,
            'count' => $processCount
        ]), 'articles.view');
    }

    /**
     * 删除文章
     * @param Request $request
     * @param ArticleSub $articleSub
     * @return mixed
     */
    public function destroy(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);

        $articleSub->delete();

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'article' => $articleSub->article
        ]), '/articles');
    }

    /**
     * 标注文章笔记
     *
     * @param Request $request
     * @return
     *
     */
    public function mark(Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
            'article_id' => 'required'
        ]);

        $this->articleService->mark($request->article_id, $request->content);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    /**
     * 获取文章语音
     * @param Request $request
     * @param ArticleSub $articleSub
     * @throws CustomException
     */
    public function getArticleRecord(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);

        $url = $this->articleService->getActiveRecordUrl($articleSub->article);

        if (empty ($url)) {
            throw new CustomException ('未获取到语音url');
        } else {
            header('Content-type: audio/mp3');
            readfile($url);
        }
    }
}
