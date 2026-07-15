<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Http\Utils\CommonUtil;
use App\Models\ArticleSub;
use App\Models\Category;
use App\Models\FeedSub;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    protected $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    protected function bootstrapAuthContext(Request $request)
    {
        $user = $request->user();
        if ($user) {
            Auth::setUser($user);
        }
    }

    public function index(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $url = (string)$request->input('url', '');
        $indexInfo = $this->feedService->getIndexInfo($url);
        $feedSubs = $indexInfo['feedSubs'];

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'url' => $indexInfo['url'],
            'title' => $indexInfo['title'],
            'categorys' => $indexInfo['categorys'],
            'feed_subs' => $feedSubs->items(),
            'pagination' => array(
                'total' => $feedSubs->total(),
                'current_page' => $feedSubs->currentPage(),
                'per_page' => $feedSubs->perPage(),
                'last_page' => $feedSubs->lastPage(),
                'next_page_url' => $feedSubs->nextPageUrl(),
                'prev_page_url' => $feedSubs->previousPageUrl(),
                'has_more_pages' => $feedSubs->hasMorePages(),
            ),
        )));
    }

    public function navinfo(Request $request)
    {
        $this->bootstrapAuthContext($request);
        $navInfos = $this->feedService->getNavInfo();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'nav_infos' => array_values($navInfos),
        )));
    }

    public function explorer(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $explorerInfo = $this->feedService->getExplorerInfo();
        $feeds = $explorerInfo['feeds'];

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'feeds' => $feeds->items(),
            'categorys' => $explorerInfo['categorys'],
            'recommend_categorys' => $explorerInfo['recommend_categorys'],
            'pagination' => array(
                'total' => $feeds->total(),
                'current_page' => $feeds->currentPage(),
                'per_page' => $feeds->perPage(),
                'last_page' => $feeds->lastPage(),
                'next_page_url' => $feeds->nextPageUrl(),
                'prev_page_url' => $feeds->previousPageUrl(),
                'has_more_pages' => $feeds->hasMorePages(),
            ),
        )));
    }

    public function search(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $recommendCategoryId = (string)$request->input('recommend_category_id', '');
        $name = (string)$request->input('name', '');
        if ($recommendCategoryId === '' && $name === '') {
            throw new CustomException('error params');
        }

        $feeds = $this->feedService->getSearchFeeds($recommendCategoryId, $name);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'feeds' => $feeds->items(),
            'pagination' => array(
                'total' => $feeds->total(),
                'current_page' => $feeds->currentPage(),
                'per_page' => $feeds->perPage(),
                'last_page' => $feeds->lastPage(),
                'next_page_url' => $feeds->nextPageUrl(),
                'prev_page_url' => $feeds->previousPageUrl(),
                'has_more_pages' => $feeds->hasMorePages(),
            ),
        )));
    }

    public function show(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $userId = $this->getAuthUserId($request);
        $this->bootstrapAuthContext($request);
        $feedSub->load(['feed', 'category']);

        $categories = Category::where('user_id', $userId)
            ->where('status', 1)
            ->orderBy('category_order', 'asc')
            ->get();

        $feedId = (int)$feedSub->feed_id;
        $totalCount = ArticleSub::where('user_id', $userId)->where('feed_id', $feedId)->count();
        $unreadCount = ArticleSub::where('user_id', $userId)->where('feed_id', $feedId)->where('status', 'unread')->count();
        $starredCount = DB::table('article_subs')
            ->where('user_id', $userId)
            ->where('feed_id', $feedId)
            ->where('mark', 1)
            ->count();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'feed_sub' => $feedSub,
            'categories' => $categories,
            'stats' => array(
                'total_articles' => (int)$totalCount,
                'unread_articles' => (int)$unreadCount,
                'starred_articles' => (int)$starredCount,
            ),
        )));
    }

    public function store(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $this->validate($request, array(
            'feed_name' => 'required',
            'url' => 'required',
            'category_id' => 'required',
        ));

        $feedName = $request->input('feed_name');
        $url = $request->input('url');
        $categoryId = $request->input('category_id');

        $feedType = $request->input('feed_type', '');
        if ($feedType === 'weibo') {
            $weiboId = $request->input('weibo_user_id');
            $url = env('WEIBO_RSS_ADDR') . $weiboId;
            $feedName = CommonUtil::pageTitle($url);
        }

        $this->feedService->store($feedName, $url, $categoryId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function quickstore(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $this->validate($request, array(
            'feed_id' => 'required',
        ));

        $this->feedService->quickStore($request->input('feed_id'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function checkFeedUrl(Request $request)
    {
        $this->validate($request, array(
            'url' => 'required',
        ));

        $feedUrl = $request->input('url');
        $this->feedService->validateFeedUrl($feedUrl);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'title' => CommonUtil::pageTitle($feedUrl),
        )));
    }

    public function importOpml(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $this->validate($request, array(
            'opml_file' => 'required|file',
        ));

        $file = $request->file('opml_file');
        if (!$file || !$file->isValid()) {
            throw new CustomException('上送opml文件验证错误');
        }

        $this->feedService->importOpml($file->path());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function destroy(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $feedSub->status = 2;
        $feedSub->update();

        $feed = $feedSub->feed;
        if (!$feed) {
            throw new CustomException('订阅源不存在');
        }
        $feed->sub_count = max(0, (int)$feed->sub_count - 1);
        $feed->save();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function sort(Request $request)
    {
        $this->bootstrapAuthContext($request);

        $this->validate($request, array(
            'feed_sub_ids' => 'required',
        ));

        $feedSubIdsArr = explode(',', (string)$request->input('feed_sub_ids'));
        $changeFeedSubId = $request->input('change_feed_sub_id', '');
        $changeFeedSubCategoryId = $request->input('change_feed_sub_category', '');

        $this->feedService->sort($feedSubIdsArr, $changeFeedSubId, $changeFeedSubCategoryId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function refreshAll(Request $request)
    {
        $this->bootstrapAuthContext($request);
        $result = $this->feedService->refreshUserFeeds($this->getAuthUserId($request));
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function update(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $this->validate($request, array(
            'feed_name' => 'required',
            'category_id' => 'required',
            'feed_order' => 'sometimes|integer|min:0',
        ));

        $userId = $this->getAuthUserId($request);
        $category = Category::where('id', (int)$request->input('category_id'))
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();
        if (!$category) {
            throw new CustomException('分类不存在');
        }

        $feedSub->feed_name = (string)$request->input('feed_name');
        $feedSub->category_id = (int)$request->input('category_id');
        if ($request->has('feed_order')) {
            $feedSub->feed_order = (int)$request->input('feed_order', 0);
        }
        $feedSub->save();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($feedSub->fresh()));
    }

    public function refresh(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);
        if (!$feedSub->feed) {
            throw new CustomException('订阅源不存在');
        }

        $this->feedService->checkFeed($feedSub->feed);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function toggleStatus(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $enable = (int)$request->input('enable', -1);
        if ($enable === 1) {
            $feedSub->status = 1;
        } elseif ($enable === 0) {
            $feedSub->status = 2;
        } else {
            $feedSub->status = ((int)$feedSub->status === 1) ? 2 : 1;
        }
        $feedSub->save();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'status' => (int)$feedSub->status,
        )));
    }

    public function clearArticles(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            throw new CustomException('用户未认证');
        }

        $deleted = ArticleSub::where('user_id', $userId)
            ->where('feed_id', $feedSub->feed_id)
            ->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'deleted' => (int)$deleted,
        )));
    }
}
