<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Services\ArticleService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'unread');
        $pageCount = (int)$request->input('page_count', 20);
        $categoryId = $request->input('category_id', '');
        $feedId = $request->input('feed_id', '');

        if (!in_array($status, array('unread', 'read', 'read_later', 'star'), true)) {
            throw new CustomException('status状态上送错误');
        }
        if ($pageCount <= 0) {
            $pageCount = 20;
        }

        $articleSubs = $this->articleService->getArticleSubList($status, $pageCount, $feedId, $categoryId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'articles' => $this->serializeArticleSubs($articleSubs->items()),
            'pagination' => array(
                'current_page' => $articleSubs->currentPage(),
                'per_page' => $articleSubs->perPage(),
                'next_page_url' => $articleSubs->nextPageUrl(),
                'prev_page_url' => $articleSubs->previousPageUrl(),
                'has_more_pages' => $articleSubs->hasMorePages(),
            ),
            'page_params' => array(
                'page_count' => $pageCount,
                'status' => $status,
                'category_id' => $categoryId,
                'feed_id' => $feedId,
            ),
        )));
    }

    public function list(Request $request)
    {
        $feedId = $request->input('feed_id');
        $pageCount = (int)$request->input('page_count', 20);

        if (empty($feedId)) {
            throw new CustomException('feed_id参数缺失');
        }
        if ($pageCount <= 0) {
            $pageCount = 20;
        }

        $articleInfos = $this->articleService->getArticleListByFeedId($feedId, $pageCount);
        $articles = isset($articleInfos['articles']) ? $articleInfos['articles'] : null;
        $feed = isset($articleInfos['feed']) ? $articleInfos['feed'] : null;

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'feed' => $feed,
            'articles' => $articles ? $this->serializeArticleSubs($articles->items()) : array(),
            'pagination' => $articles ? array(
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'next_page_url' => $articles->nextPageUrl(),
                'prev_page_url' => $articles->previousPageUrl(),
                'has_more_pages' => $articles->hasMorePages(),
            ) : array(),
            'page_params' => array(
                'page_count' => $pageCount,
                'feed_id' => $feedId,
            ),
        )));
    }

    public function navinfo(Request $request)
    {
        $status = $request->input('status', 'unread');
        $navInfo = $this->articleService->getNavInfo($status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'nav_infos' => $navInfo,
        )));
    }

    public function navcountinfo(Request $request)
    {
        $status = $request->input('status', 'unread');
        $feedCountInfos = $this->articleService->getFeedCountInfos($this->getAuthUserId($request), $status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($feedCountInfos));
    }

    public function status(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);

        $status = $request->input('status', '');
        if (!in_array($status, array('read', 'unread', 'read_later', 'star'), true)) {
            throw new CustomException('status状态上送错误');
        }

        $processCount = $this->articleService->setArticleSubStatus($articleSub, $status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article' => $articleSub->article,
            'count' => $processCount,
        )));
    }

    public function allstatus(Request $request)
    {
        $status = $request->input('status', 'read');
        if (!in_array($status, array('read', 'unread', 'read_later', 'star'), true)) {
            throw new CustomException('status状态上送错误');
        }

        $processCount = 0;
        if ($request->filled('ids')) {
            $ids = array_filter(explode(',', (string)$request->input('ids')));
            $processCount = $this->articleService->setArticleSubStatusByIds($ids, $status);
        } elseif ($request->filled('feed_id')) {
            $processCount = $this->articleService->setArticleSubStatusByFeedId($request->input('feed_id'), $status);
        } else {
            throw new CustomException('缺少ids或feed_id参数');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'count' => $processCount,
        )));
    }

    public function mark(Request $request)
    {
        $this->validate($request, array(
            'content' => 'required',
            'article_id' => 'required',
        ));

        $this->articleService->mark($request->input('article_id'), $request->input('content'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function show(Request $request, Article $article)
    {
        $isFeed = $this->articleService->isFeed($article->feed->id);
        if ($isFeed == false && $article->feed->audit_status == 0) {
            throw new CustomException('订阅源审核中,审核通过后可正常分享');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article' => $article,
            'is_feed' => $isFeed,
        )));
    }

    public function destroy(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        $article = $articleSub->article;
        $articleSub->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article' => $article,
        )));
    }

    public function getRecord(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        $url = $this->articleService->getActiveRecordUrl($articleSub->article);
        if (empty($url)) {
            throw new CustomException('未获取到语音url');
        }

        return response()->file($url, array(
            'Content-Type' => 'audio/mp3',
        ));
    }

    public function proxyView(Request $request)
    {
        $type = $request->input('type', '');
        $url = $request->input('url', '');

        if (empty($type) || empty($url)) {
            throw new CustomException('params error');
        }
        if ($type !== 'v2ex') {
            throw new CustomException('unsupported proxy type');
        }

        $newUrl = 'https://www.v2ex.com' . $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $newUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
            'sec-ch-ua: "Not A(Brand";v="8", "Chromium";v="132", "Google Chrome";v="132"',
            'sec-ch-ua-mobile:?0',
            'sec-ch-ua-platform: "macOS"',
        ));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new CustomException('proxy fetch failed: ' . $err);
        }
        curl_close($ch);

        return response($response, 200, array(
            'Content-Type' => 'text/html; charset=UTF-8',
        ));
    }

    private function serializeArticleSubs(array $articleSubs)
    {
        $result = array();
        foreach ($articleSubs as $articleSub) {
            if (!$articleSub) {
                continue;
            }

            $article = $articleSub->relationLoaded('article') ? $articleSub->article : null;
            if (!$article && !empty($articleSub->article_id)) {
                $article = \App\Models\Article::with('feed')->find($articleSub->article_id);
            } elseif ($article && !$article->relationLoaded('feed')) {
                $article->load('feed');
            }

            $result[] = array(
                'id' => $articleSub->id,
                'user_id' => $articleSub->user_id,
                'feed_id' => $articleSub->feed_id,
                'article_id' => $articleSub->article_id,
                'status' => $articleSub->status,
                'updated_at' => $articleSub->updated_at,
                'created_at' => $articleSub->created_at,
                'article' => $article ? array(
                    'id' => $article->id,
                    'feed_id' => $article->feed_id,
                    'subject' => $article->subject,
                    'url' => $article->url,
                    'image_url' => $article->image_url,
                    'content' => $article->content,
                    'formatted_content' => CommonUtil::formatContentHtml($article->content),
                    'published' => $article->published,
                    'feed' => $article->feed ? array(
                        'id' => $article->feed->id,
                        'feed_name' => $article->feed->feed_name,
                        'url' => $article->feed->url,
                        'category_id' => $article->feed->category_id,
                    ) : null,
                ) : null,
            );
        }

        return $result;
    }
}
