<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\FeedSub;
use App\Services\CategoryService;
use App\Services\FeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


/**
 * 订阅源控制器
 *
 * @author edison.an
 */
class FeedController extends Controller
{

    /**
     * CategoryService 实例.
     *
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * FeedService 实例.
     *
     * @var FeedService
     */
    protected $feedService;

    /**
     * 构造方法
     *
     * @param CategoryService $categoryService
     * @param FeedService $feedService
     * @return void
     */
    public function __construct(CategoryService $categoryService, FeedService $feedService)
    {
        $this->middleware('auth');

        $this->categoryService = $categoryService;
        $this->feedService = $feedService;
    }

    /**
     * 首页
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        return view('feeds.index');
    }

    /**
     * 发现推荐订阅源
     *
     * @param Request $request
     */
    public function explorer(Request $request)
    {
        return view('feeds.explorer');
    }

    /**
     * 查找订阅源
     *
     * @param Request $request
     */
    public function search(Request $request)
    {
        return view('feeds.search');
    }

    /**
     * 订阅源管理页面
     *
     * @param Request $request
     * @return
     *
     */
    public function setting(Request $request)
    {
        return view('feeds.setting');
    }

    /**
     * 新订阅提交
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'feed_name' => 'required',
            'url' => 'required',
            'category_id' => 'required'
        ]);

        $feedName = $request->input('feed_name');
        $url = $request->input('url');
        $categoryId = $request->input('category_id');

        // 微博订阅特殊处理到特定订阅源
        $feedType = $request->input('feed_type', '');
        if ($feedType == 'weibo') {
            $weiboId = $request->input('weibo_user_id');
            // todo weibo
            $url = env('WEIBO_RSS_ADDR') . $weiboId;
            $feedName = CommonUtil::pageTitle($url);
        }

        $this->feedService->store($feedName, $url, $categoryId);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/feeds');
    }

    /**
     * 快速订阅
     *
     * @param Request $request
     * @return
     *
     */
    public function quickstore(Request $request)
    {
        $this->validate($request, [
            'feed_id' => 'required'
        ]);

        $this->feedService->quickStore($request->input("feed_id"));

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/feeds');
    }

    /**
     * 删除订阅
     *
     * @param Request $request
     * @param FeedSub $feedSub
     */
    public function destroy(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $feedSub->status = 2;
        $feedSub->update();

        $feed = $feedSub->feed;
        $feed->sub_count = $feed->sub_count - 1;
        $feed->save();

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/feeds');
    }

    /**
     * 更新订阅
     *
     * @param Request $request
     * @param FeedSub $feedSub
     */
    public function update(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        if ($request->method() == 'GET') {
            return view('feeds.update');
        }

        $this->validate($request, [
            'feed_name' => 'required',
            'category_id' => 'required'
        ]);

        $category = $this->categoryService->getByCategoryId($request->category_id);
        if (empty ($category)) {
            throw new CustomException ("分类不存在");
        }

        $feedSub->update($request->all());

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/feeds');
    }

    /**
     * 排序
     *
     * @param Request $request
     * @param FeedSub $feedSub
     */
    public function sort(Request $request, FeedSub $feedSub)
    {
        $this->validate($request, [
            'feed_sub_ids' => 'required'
        ]);

        $feedSubIdsArr = explode(',', $request->feed_sub_ids);
        $changeFeedSubId = $request->input('change_feed_sub_id', '');
        $changeFeedSubCategoryId = $request->input('change_feed_sub_category', '');

        $this->feedService->sort($feedSubIdsArr, $changeFeedSubId, $changeFeedSubCategoryId);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/feeds');
    }

    /**
     * 检测订阅源基础信息
     *
     * @param Request $request
     * @return
     *
     */
    public function checkFeedUrl(Request $request)
    {
        $this->validate($request, [
            'url' => 'required'
        ]);

        $feedUrl = $request->url;
        $this->feedService->validateFeedUrl($feedUrl);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'title' => CommonUtil::pageTitle($feedUrl)
        )));
    }

    /**
     * Legacy alias for old route /feed/checkNewFeed.
     */
    public function checkNewFeed(Request $request)
    {
        return $this->checkFeedUrl($request);
    }

    /**
     * 通过Opml导入订阅源
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function importOpml(Request $request)
    {
        if (!$request->file('opml_file')->isValid()) {
            throw new CustomException ("上送opml文件验证错误");
        }
        $path = $request->opml_file->path();
        $this->feedService->importOpml($path);

        return redirect('/feeds')->with("message", "IT WORKS!");
    }

    /**
     * 微博订阅页
     *
     * @param Request $request
     * @return
     *
     */
    public function weiborss(Request $request)
    {
        return view('feeds.weiborss');
    }

    /**
     * 微信订阅页
     *
     * @param Request $request
     * @return
     *
     */
    public function weixinrss(Request $request)
    {
        return view('feeds.weixinrss');
    }

    /**
     * opml导入订阅页
     *
     * @param Request $request
     * @return
     *
     */
    public function opml(Request $request)
    {
        return view('feeds.opml');
    }
}
