<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Category;
use App\Models\LlmModel;
use App\Models\WebpageRssSource;
use App\Models\FeedSub;
use App\Services\CategoryService;
use App\Services\FeedService;
use App\Services\WebpageRssService;
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
     * WebpageRssService 实例.
     *
     * @var WebpageRssService
     */
    protected $webpageRssService;

    /**
     * 构造方法
     *
     * @param CategoryService $categoryService
     * @param FeedService $feedService
     * @return void
     */
    public function __construct(CategoryService $categoryService, FeedService $feedService, WebpageRssService $webpageRssService)
    {
        $this->middleware('auth')->except('webpageRssXml');

        $this->categoryService = $categoryService;
        $this->feedService = $feedService;
        $this->webpageRssService = $webpageRssService;
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
     * 网页转RSS配置页
     *
     * @param Request $request
     * @return
     *
     */
    public function webpageRss(Request $request)
    {
        return view('feeds.webpage-rss');
    }

    /**
     * 网页转RSS分类列表
     *
     * @param Request $request
     */
    public function webpageRssCategories(Request $request)
    {
        $categories = Category::where('user_id', \Auth::id())
            ->orderBy('category_order')
            ->orderBy('id', 'desc')
            ->get();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($categories));
    }

    /**
     * 调试网页转RSS规则
     *
     * @param Request $request
     */
    public function debugWebpageRss(Request $request)
    {
        $config = $this->webpageRssService->normalizeConfig($request->all());
        if (empty($config['list_url'])) {
            throw new CustomException('调试时请先填写列表页地址');
        }
        $result = $this->webpageRssService->debug($config, (int)$request->input('limit', 10));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    /**
     * 保存网页转RSS配置
     *
     * @param Request $request
     */
    public function saveWebpageRss(Request $request)
    {
        $config = $this->webpageRssService->normalizeConfig($request->all());
        $this->validateWebpageRssCategory($config['category_id']);
        $result = $this->webpageRssService->save(\Auth::id(), $config);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    /**
     * AI 解析网页转RSS规则
     *
     * @param Request $request
     */
    public function aiAnalyzeWebpageRss(Request $request)
    {
        $this->validate($request, [
            'model_id' => 'required|integer|exists:llm_models,id',
            'list_url' => 'required|string',
            'ai_mode' => 'nullable|in:list_summary,detail_content,balanced',
        ]);

        $user = \Auth::user();
        $model = LlmModel::with('provider')->where('id', $request->input('model_id'))->first();
        if (!$model || (!$user->is_admin && !in_array((int)$model->user_id, array(0, (int)$user->id), true))) {
            throw new CustomException('模型不存在或无权限使用');
        }

        $config = $this->webpageRssService->normalizeConfig($request->all());
        $result = $this->webpageRssService->analyzeByAi(
            $config,
            (int)$request->input('model_id'),
            (string)$request->input('ai_mode', 'balanced')
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    /**
     * 手动刷新网页转RSS配置
     *
     * @param Request $request
     * @param WebpageRssSource $source
     */
    public function refreshWebpageRss(Request $request, WebpageRssSource $source)
    {
        if ((int)$source->user_id !== (int)\Auth::id()) {
            throw new CustomException('配置不存在');
        }

        $result = $this->webpageRssService->refreshSource($source, (int)$request->input('limit', 20));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    /**
     * 网页转RSS XML输出
     *
     * @param string $token
     */
    public function webpageRssXml($token)
    {
        $source = WebpageRssSource::where('rss_token', $token)->where('status', 1)->first();
        if (empty($source)) {
            abort(404);
        }

        return response($this->webpageRssService->buildRssXml($source), 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    private function validateWebpageRssCategory($categoryId)
    {
        $category = Category::where('id', $categoryId)->where('user_id', \Auth::id())->first();
        if (empty($category)) {
            throw new CustomException('分类不存在');
        }
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
