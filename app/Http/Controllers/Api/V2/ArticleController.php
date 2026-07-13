<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Repositories\ArticleSubRepository;
use App\Repositories\BgmTrackRepository;
use App\Services\ArticleAiRenderService;
use App\Services\ArticleService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    protected $articleService;
    protected $pointGrantService;
    protected $articleSubRepository;
    protected $articleAiRenderService;
    protected $bgmTrackRepository;

    public function __construct(
        ArticleService $articleService,
        PointGrantService $pointGrantService,
        ArticleSubRepository $articleSubRepository,
        ArticleAiRenderService $articleAiRenderService,
        BgmTrackRepository $bgmTrackRepository
    )
    {
        $this->articleService = $articleService;
        $this->pointGrantService = $pointGrantService;
        $this->articleSubRepository = $articleSubRepository;
        $this->articleAiRenderService = $articleAiRenderService;
        $this->bgmTrackRepository = $bgmTrackRepository;
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'unread');
        $pageCount = max(1, min(100, (int)$request->input('page_count', 20)));
        $categoryId = $request->input('category_id', '');
        $feedId = $request->input('feed_id', '');
        $filters = $this->resolveArticleAiFilters($request);
        $filters = array_merge($filters, $this->resolveArticleCommonFilters($request));

        if (!in_array($status, array('unread', 'read', 'read_later', 'star'), true)) {
            throw new CustomException('status状态上送错误');
        }
        if ($pageCount <= 0) {
            $pageCount = 20;
        }

        $articleSubs = $this->articleService->getArticleSubList($status, $pageCount, $feedId, $categoryId, $filters);

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
                'time_range' => $filters['time_range'],
                'read_duration' => $filters['read_duration'],
                'view_mode' => $filters['view_mode'],
                'primary_category' => $filters['primary_category'],
                'min_quality_score' => $filters['min_quality_score'],
            ),
        )));
    }

    public function list(Request $request)
    {
        $feedId = $request->input('feed_id');
        $pageCount = max(1, min(100, (int)$request->input('page_count', 20)));
        $filters = array_merge($this->resolveArticleAiFilters($request), $this->resolveArticleCommonFilters($request));

        if (empty($feedId)) {
            throw new CustomException('feed_id参数缺失');
        }
        if ($pageCount <= 0) {
            $pageCount = 20;
        }

        $articleInfos = $this->articleService->getArticleListByFeedId($feedId, $pageCount, $filters);
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
                'time_range' => $filters['time_range'],
                'read_duration' => $filters['read_duration'],
                'view_mode' => $filters['view_mode'],
                'primary_category' => $filters['primary_category'],
                'min_quality_score' => $filters['min_quality_score'],
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

        $oldStatus = (string)$articleSub->status;
        $processCount = $this->articleService->setArticleSubStatus($articleSub, $status);
        $freshArticleSub = $articleSub->fresh();
        if ($oldStatus !== 'read' && (string)$status === 'read' && $freshArticleSub) {
            try {
                $this->pointGrantService->grantByEvent(
                    (int)$freshArticleSub->user_id,
                    'article_finished',
                    'article_sub',
                    (int)$freshArticleSub->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on article read failed', array(
                    'article_sub_id' => $freshArticleSub->id,
                    'user_id' => $freshArticleSub->user_id,
                    'error' => $e->getMessage(),
                ));
            }
        }

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

        $articleMark = $this->articleService->mark($request->input('article_id'), $request->input('content'));
        try {
            $this->pointGrantService->grantByEvent(
                (int)$articleMark->user_id,
                'article_mark_created',
                'article_mark',
                (int)$articleMark->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on article mark failed', array(
                'article_id' => $request->input('article_id'),
                'user_id' => $this->getAuthUserId($request),
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function show(Request $request, Article $article)
    {
        $isFeed = $this->articleService->isFeed($article->feed->id);
        if ($isFeed == false && $article->feed->audit_status == 0) {
            throw new CustomException('订阅源审核中,审核通过后可正常分享');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article' => $this->serializeArticle($article),
            'is_feed' => $isFeed,
        )));
    }

    public function readerView(Request $request, Article $article)
    {
        $article->load('feed', 'aiRender');

        $articleSub = $this->resolveArticleSubForUser($request, $article, $request->input('article_sub_id'));
        if (!$articleSub) {
            throw new CustomException('未找到当前文章的阅读记录');
        }

        $aiRender = $this->articleAiRenderService->getRenderByArticleId($article->id);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_sub' => $this->serializeSingleArticleSub($articleSub),
            'article' => $this->serializeArticle($article, $aiRender),
            'ai_render' => $this->serializeAiRender($aiRender),
        )));
    }

    public function getAiRender(Request $request, Article $article)
    {
        $articleSub = $this->resolveArticleSubForUser($request, $article, $request->input('article_sub_id'));
        if (!$articleSub) {
            throw new CustomException('未找到当前文章的阅读记录');
        }

        $render = $this->articleAiRenderService->getRenderByArticleId($article->id);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_id' => $article->id,
            'ai_render' => $this->serializeAiRender($render),
        )));
    }

    public function generateAiRender(Request $request, Article $article)
    {
        $articleSub = $this->resolveArticleSubForUser($request, $article, $request->input('article_sub_id'));
        if (!$articleSub) {
            throw new CustomException('未找到当前文章的阅读记录');
        }

        $article->load('feed');
        $render = $this->articleAiRenderService->ensureRender($article, array(
            'force' => (int)$request->input('force', 0) === 1,
            'template_style' => $request->input('template_style', 'magazine'),
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_id' => $article->id,
            'ai_render' => $this->serializeAiRender($render),
        )));
    }

    public function hotPlaylist(Request $request)
    {
        $keyword = trim((string)$request->input('keyword', 'tiktok'));
        $tracks = $this->bgmTrackRepository->getActiveTracks(30, $keyword);
        if ($tracks->count() > 0) {
            $playlist = array();
            foreach ($tracks as $track) {
                $playlist[] = array(
                    'id' => 'bgm-track-' . $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist,
                    'audio_url' => $track->audio_url,
                    'source_url' => $track->source_url,
                    'cover_color' => $track->cover_color ?: '#102033',
                    'source_type' => $track->source_type ?: 'manual_pixabay',
                    'search_keyword' => $track->search_keyword,
                    'metadata' => (array)$track->metadata_json,
                );
            }

            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
                'playlist' => $playlist,
                'source' => 'bgm_tracks',
                'keyword' => $keyword,
            )));
        }

        $playlist = array(
            array(
                'id' => 'demo-hot-1',
                'title' => '流行热歌 Demo 1',
                'artist' => 'Montage Mix',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                'cover_color' => '#ff7a59',
                'source_type' => 'fallback_demo',
            ),
            array(
                'id' => 'demo-hot-2',
                'title' => '流行热歌 Demo 2',
                'artist' => 'Montage Mix',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
                'cover_color' => '#00b894',
                'source_type' => 'fallback_demo',
            ),
            array(
                'id' => 'demo-hot-3',
                'title' => '流行热歌 Demo 3',
                'artist' => 'Montage Mix',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
                'cover_color' => '#6c5ce7',
                'source_type' => 'fallback_demo',
            ),
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'playlist' => $playlist,
            'source' => 'fallback_demo',
            'keyword' => $keyword,
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

            $result[] = $this->serializeSingleArticleSub($articleSub, $article);
        }

        return $result;
    }

    private function serializeSingleArticleSub($articleSub, $article = null)
    {
        if (!$article && $articleSub && $articleSub->relationLoaded('article')) {
            $article = $articleSub->article;
        }

        return array(
            'id' => $articleSub->id,
            'user_id' => $articleSub->user_id,
            'feed_id' => $articleSub->feed_id,
            'article_id' => $articleSub->article_id,
            'status' => $articleSub->status,
            'updated_at' => $articleSub->updated_at,
            'created_at' => $articleSub->created_at,
            'personalized_score' => isset($articleSub->personalized_score) ? (float)$articleSub->personalized_score : null,
            'article' => $article ? $this->serializeArticle($article) : null,
        );
    }

    private function serializeArticle($article, $aiRender = null)
    {
        if ($article && !$article->relationLoaded('feed')) {
            $article->load('feed');
        }

        if ($article && !$article->relationLoaded('aiProfile')) {
            $article->load('aiProfile');
        }

        if (!$aiRender && $article && $article->relationLoaded('aiRender')) {
            $aiRender = $article->aiRender;
        }

        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$article->content)));
        $wordCount = (int)$article->word_count;
        $estimatedReadMinutes = (int)$article->estimated_read_minutes;

        return array(
            'id' => $article->id,
            'feed_id' => $article->feed_id,
            'subject' => $article->subject,
            'url' => $article->url,
            'image_url' => $article->image_url,
            'content' => $article->content,
            'formatted_content' => CommonUtil::formatContentHtml($article->content),
            'plain_text' => $plainText,
            'published' => $article->published,
            'word_count' => $wordCount,
            'estimated_read_minutes' => max(1, $estimatedReadMinutes),
            'feed' => $article->feed ? array(
                'id' => $article->feed->id,
                'feed_name' => $article->feed->feed_name,
                'url' => $article->feed->url,
                'category_id' => $article->feed->category_id,
            ) : null,
            'ai_profile' => $this->serializeAiProfile($article->aiProfile),
            'ai_render_state' => $this->serializeAiRender($aiRender),
        );
    }

    private function serializeAiProfile($profile)
    {
        if (!$profile) {
            return null;
        }

        return array(
            'status' => $profile->status,
            'primary_category' => $profile->primary_category,
            'secondary_category' => $profile->secondary_category,
            'tags' => (array)$profile->tags_json,
            'keywords' => (array)$profile->keywords_json,
            'summary' => $profile->summary,
            'content_type' => $profile->content_type,
            'audience' => $profile->audience,
            'quality_score' => $profile->quality_score,
            'risk_flags' => (array)$profile->risk_flags_json,
            'model_name' => $profile->model_name,
            'analyzed_at' => $profile->analyzed_at ? $profile->analyzed_at->toDateTimeString() : null,
        );
    }

    private function serializeAiRender($render)
    {
        if (!$render) {
            return array(
                'status' => 'pending',
                'render_mode' => 'visual_story',
                'template_style' => 'magazine',
                'summary' => null,
                'outline' => array(),
                'html_content' => null,
                'model_name' => null,
                'prompt_version' => null,
                'generated_at' => null,
                'error_message' => null,
            );
        }

        return array(
            'status' => $render->status,
            'render_mode' => $render->render_mode,
            'template_style' => $render->template_style,
            'summary' => $render->summary,
            'outline' => (array)$render->outline_json,
            'html_content' => $render->html_content,
            'model_name' => $render->model_name,
            'prompt_version' => $render->prompt_version,
            'generated_at' => $render->generated_at ? $render->generated_at->toDateTimeString() : null,
            'error_message' => $render->error_message,
        );
    }

    private function resolveArticleAiFilters(Request $request)
    {
        $viewMode = (string)$request->input('view_mode', 'all');
        $allowedViewModes = array('all', 'personalized', 'tech', 'product', 'read_later_suggest', 'low_priority');
        if (!in_array($viewMode, $allowedViewModes, true)) {
            $viewMode = 'all';
        }

        return array(
            'view_mode' => $viewMode,
            'primary_category' => trim((string)$request->input('primary_category', '')),
            'min_quality_score' => max(0, (int)$request->input('min_quality_score', 0)),
        );
    }

    private function resolveArticleCommonFilters(Request $request)
    {
        $timeRange = (string)$request->input('time_range', 'all');
        $allowedTimeRanges = array('all', '3h', '6h', '1d', '3d', '7d');
        if (!in_array($timeRange, $allowedTimeRanges, true)) {
            $timeRange = 'all';
        }

        $readDuration = (string)$request->input('read_duration', 'all');
        $allowedReadDurations = array('all', 'short', 'medium', 'long');
        if (!in_array($readDuration, $allowedReadDurations, true)) {
            $readDuration = 'all';
        }

        $filters = array(
            'time_range' => $timeRange,
            'read_duration' => $readDuration,
        );

        if ($readDuration === 'short') {
            $filters['max_read_minutes'] = 5;
        } elseif ($readDuration === 'medium') {
            $filters['min_read_minutes'] = 6;
            $filters['max_read_minutes'] = 15;
        } elseif ($readDuration === 'long') {
            $filters['min_read_minutes'] = 16;
        }

        return $filters;
    }

    private function resolveArticleSubForUser(Request $request, Article $article, $articleSubId = null)
    {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return null;
        }

        if (!empty($articleSubId)) {
            $articleSub = ArticleSub::with('article.feed')
                ->where('id', $articleSubId)
                ->where('user_id', $userId)
                ->first();

            if ($articleSub && (int)$articleSub->article_id === (int)$article->id) {
                return $articleSub;
            }
        }

        return $this->articleSubRepository->findByUserIdAndArticleId($userId, $article->id);
    }
}
