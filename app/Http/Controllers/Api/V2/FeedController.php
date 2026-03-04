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

    public function update(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $this->validate($request, array(
            'feed_name' => 'required',
            'category_id' => 'required',
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
