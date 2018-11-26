<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ArandiLopez\Feed\Factories\FeedFactory;
use App\Http\Utils\CommonUtil;
use App\Models\Feed;
use App\Repositories\FeedRepository;
use App\Models\FeedSub;
use App\Repositories\FeedSubRepository;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use App\Services\FeedService;

/**
 * 订阅源控制器
 */
class FeedController extends Controller
{

    /**
     *
     * @var CategoryService
     */
    protected $categoryService;

    /**
     *
     * @var FeedService
     */
    protected $feedService;

    /**
     * Create a new controller instance.
     *
     * @param CategoryRepository $categorys
     * @param FeedSubRepository $feedSubs
     * @param FeedRepository $feeds
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
        $feedSubs = $this->feedService->getFeedSubListByStatus($request->user(), 1, $needPage = true);
        $categorys = $this->categoryService->getList($request->user(), false, true);
        
        $title = $url = '';
        
        if ($request->has('url')) {
            $url = $request->url;
            $title = CommonUtil::page_title($request->url);
        }
        
        return view('feeds.index', [
            'feedSubs' => $feedSubs,
            'categorys' => $categorys,
            'url' => $url,
            'title' => $title
        ]);
    }

    /**
     * 发现推荐订阅源
     *
     * @param Request $request
     */
    public function explorer(Request $request)
    {
        $feeds = $this->feedService->getRecommendFeed(1, $needPage = true);
        $categorys = $this->categoryService->getList($request->user(), false, true);
        
        return view('feeds.explorer', [
            'feeds' => $feeds,
            'categorys' => $categorys,
            'recommend_categorys' => Feed::$recommend_categorys
        ]);
    }

    /**
     * 查找订阅源
     *
     * @param Request $request
     */
    public function search(Request $request)
    {
        if ($request->has('recommend_category_id')) {
            $feeds = $this->feedService->findByRecommendCategoryId($request->recommend_category_id);
        } else if ($request->has('name')) {
            $feeds = $this->feedService->findByName($request->name, $needPage = true);
        } else {
            echo 'error params';
            exit();
        }
        
        return view('feeds.search', [
            'feeds' => $feeds
        ]);
    }

    /**
     * 订阅源管理页面
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function setting(Request $request)
    {
        $navInfos = $this->feedService->getNavInfo($request->user());
        
        return view('feeds.setting', [
            'nav_infos' => $navInfos
        ]);
    }

    /**
     * 订阅
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
        
        if ($request->has('feed_type')) {
            if ($request->feed_type == 'weibo') {
                $request->url = 'https://api.prprpr.me/weibo/rss/' . $request->weibo_user_id;
                $request->feed_name = CommonUtil::page_title($request->url);
            }
        }
        
        $this->feedService->store($request->user(), $request->all());
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/feeds')->with('message', 'IT WORKS!');
        }
    }

    /**
     * 快速订阅
     *
     * @param Request $request
     * @return
     */
    public function quickstore(Request $request)
    {
        $this->validate($request, [
            'feed_id' => 'required'
        ]);
        
        $this->feedService->quickStore($request, $request->all());
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE, '', '关注成功');
            return response($resp);
        } else {
            return redirect('/feeds')->with('message', 'IT WORKS!');
        }
    }

    /**
     * 删除
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
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/feeds')->with('message', 'IT WORKS!');
        }
    }

    /**
     * 更新
     *
     * @param Request $request
     * @param FeedSub $feedSub
     */
    public function update(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);
        
        if ($request->method() == 'GET') {
            $categorys = $this->categoryService->getList($request->user(), false);
            return view('feeds.update', array(
                'feedSub' => $feedSub,
                'categorys' => $categorys
            ));
        }
        
        $this->validate($request, [
            'feed_name' => 'required',
            'category_id' => 'required'
        ]);
        
        $category = $this->categoryService->getByCategoryId($request->user(), $request->category_id);
        if (empty($category)) {
            echo 'error:' . $request->category_id;
            exit();
        }
        
        $feedSub->update($request->all());
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/feeds')->with('message', 'IT WORKS!');
        }
    }

    /**
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
        
        $this->feedService->sort($request->user(), $feedSubIdsArr, $request->input('change_feed_sub_id',''), $request->input('change_feed_sub_category',''));
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/feeds')->with('message', 'IT WORKS!');
        }
    }
    
    public function checkFeedUrl(Request $request)
    {
        $this->validate($request, [
            'url' => 'required'
        ]);
        
        $result_code = 1001;
        
        $feedFactory = new FeedFactory(['cache.enabled' => false]);
        $feeder = $feedFactory->make($request->url);
        $simplePieInstance = $feeder->getRawFeederObject();
        
        if (!empty($simplePieInstance)) {
            $result_code = self::OK_CODE;
        }
        
        $title = \App\Http\Utils\CommonUtil::page_title($request->url);
        
        $resp = $this->responseJson($result_code, array('title'=>$title));
        return response($resp);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importOpml(Request $request)
    {
        if ($request->file('opml_file')->isValid()) {
            $path = $request->opml_file->path();
            $this->feedService->importOpml($request->user(), $path);
            return redirect('/feeds')->with('message', 'IT WORKS!');
        } else {
            echo 'error param!';
            exit();
        }
        exit();
    }

    public function weiborss(Request $request)
    {
        return view('feeds.weiborss', [
            'categorys' => $this->categoryService->getList($request->user(), false, true)
        ]);
    }

    public function weixinrss(Request $request)
    {
        return view('feeds.weixinrss', [
            'categorys' => $this->categoryService->getList($request->user(), false, true)
        ]);
    }

    public function opml(Request $request)
    {
        return view('feeds.opml', [
            'categorys' => $this->categoryService->getList($request->user(), false, true)
        ]);
    }
}
