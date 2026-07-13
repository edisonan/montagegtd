<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Models\FeedSub;
use App\Services\ArticleAiRenderService;
use App\Services\ArticleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    protected $articleAiRenderService;

    /**
     * 构造方法
     *
     * @param ArticleService $articleService
     * @return void
     */
    public function __construct(ArticleService $articleService, ArticleAiRenderService $articleAiRenderService)
    {
        $this->middleware('auth', [
            'except' => [
                'welcome',
                'view'
            ]
        ]);

        $this->articleService = $articleService;
        $this->articleAiRenderService = $articleAiRenderService;
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
        return view('articles.welcome');
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
        return view('articles.index');
    }

    /**
     * 三栏探索阅读页。
     */
    public function explorer(Request $request)
    {
        return view('articles.explorer');
    }

    public function workbench(Request $request)
    {
        return view('articles.workbench');
    }

    public function workbenchData(Request $request)
    {
        $status = (string)$request->input('status', 'unread');
        if (!in_array($status, array('all', 'unread', 'read', 'read_later', 'star'), true)) {
            $status = 'unread';
        }

        $query = ArticleSub::join('articles', 'article_subs.article_id', '=', 'articles.id')
            ->join('feeds', 'article_subs.feed_id', '=', 'feeds.id')
            ->leftJoin('categories', 'feeds.category_id', '=', 'categories.id')
            ->where('article_subs.user_id', Auth::id())
            ->select(array(
                'article_subs.id',
                'article_subs.status',
                'article_subs.feed_id',
                'article_subs.article_id',
                'articles.subject',
                'articles.url',
                'articles.published',
                'articles.word_count',
                'articles.estimated_read_minutes',
                'feeds.feed_name',
                'categories.name as category_name',
            ));

        if ($status !== 'all') {
            $query->where('article_subs.status', $status);
        }

        $feedId = (int)$request->input('feed_id', 0);
        if ($feedId > 0) {
            $query->where('article_subs.feed_id', $feedId);
        }
        $categoryId = (int)$request->input('category_id', 0);
        if ($categoryId > 0) {
            $query->where('feeds.category_id', $categoryId);
        }

        $keyword = trim((string)$request->input('keyword', ''));
        if ($keyword !== '') {
            $query->where(function ($subQuery) use ($keyword) {
                $like = '%' . $keyword . '%';
                $subQuery->where('articles.subject', 'like', $like)
                    ->orWhere('feeds.feed_name', 'like', $like)
                    ->orWhere('categories.name', 'like', $like);
            });
        }

        $minMinutes = max(0, (int)$request->input('min_read_minutes', 0));
        $maxMinutes = max(0, (int)$request->input('max_read_minutes', 0));
        if ($minMinutes > 0) {
            $query->where('articles.estimated_read_minutes', '>=', $minMinutes);
        }
        if ($maxMinutes > 0) {
            $query->where('articles.estimated_read_minutes', '<=', $maxMinutes);
        }

        $timeRange = (string)$request->input('time_range', 'all');
        $now = Carbon::now();
        $timeStart = null;
        if ($timeRange === '3h') {
            $timeStart = $now->copy()->subHours(3);
        } elseif ($timeRange === '6h') {
            $timeStart = $now->copy()->subHours(6);
        } elseif ($timeRange === '1d') {
            $timeStart = $now->copy()->subDay();
        } elseif ($timeRange === '7d') {
            $timeStart = $now->copy()->subDays(7);
        } elseif ($timeRange === 'custom') {
            $customStart = trim((string)$request->input('start_date', ''));
            $customEnd = trim((string)$request->input('end_date', ''));
            if ($customStart !== '') {
                $query->where('articles.published', '>=', $customStart . ' 00:00:00');
            }
            if ($customEnd !== '') {
                $query->where('articles.published', '<=', $customEnd . ' 23:59:59');
            }
        }
        if ($timeStart) {
            $query->where('articles.published', '>=', $timeStart->toDateTimeString());
        }

        $pageCount = max(10, min(100, (int)$request->input('page_count', 100)));
        $articles = $query->orderBy('articles.published', 'desc')
            ->orderBy('article_subs.id', 'desc')
            ->paginate($pageCount);

        $feeds = DB::table('feed_subs')
            ->join('feeds', 'feed_subs.feed_id', '=', 'feeds.id')
            ->leftJoin('categories', 'feeds.category_id', '=', 'categories.id')
            ->where('feed_subs.user_id', Auth::id())
            ->select('feeds.id', 'feeds.feed_name', 'categories.name as category_name')
            ->orderBy('categories.name')
            ->orderBy('feeds.feed_name')
            ->get();
        $categories = DB::table('feed_subs')
            ->join('feeds', 'feed_subs.feed_id', '=', 'feeds.id')
            ->join('categories', 'feeds.category_id', '=', 'categories.id')
            ->where('feed_subs.user_id', Auth::id())
            ->select('categories.id', 'categories.name')
            ->distinct()
            ->orderBy('categories.name')
            ->get();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'articles' => $articles->items(),
            'feeds' => $feeds,
            'categories' => $categories,
            'pagination' => array(
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
                'has_more_pages' => $articles->hasMorePages(),
            ),
        )));
    }

    public function workbenchAiDigest(Request $request)
    {
        $ids = array_values(array_filter(array_map('intval', (array)$request->input('article_sub_ids', array()))));
        if (empty($ids)) {
            throw new CustomException('当前页没有可整理的文章');
        }
        $ids = array_slice($ids, 0, 100);
        $articles = ArticleSub::with('article.feed.category')
            ->where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->get()
            ->pluck('article')
            ->filter()
            ->values()
            ->all();
        if (empty($articles)) {
            throw new CustomException('未找到当前页文章');
        }

        $result = $this->articleAiRenderService->generateWorkbenchDigest(
            $articles,
            substr(trim((string)$request->input('custom_prompt', '')), 0, 3000)
        );
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'count' => count($articles),
            'ai_render' => $result,
        )));
    }

    public function workbenchArticle(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        $articleSub->load('article.feed.category');
        if (!$articleSub->article) {
            abort(404);
        }
        if ($articleSub->status === 'unread') {
            $this->articleService->setArticleSubStatus($articleSub, 'read');
        }
        $article = $articleSub->fresh('article.feed.category')->article;

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_sub_id' => $articleSub->id,
            'status' => $articleSub->fresh()->status,
            'article' => array(
                'id' => $article->id,
                'subject' => $article->subject,
                'url' => $article->url,
                'published' => $article->published,
                'content' => CommonUtil::formatContentHtml($article->content),
                'feed_name' => $article->feed ? $article->feed->feed_name : '',
                'category_name' => $article->feed && $article->feed->category ? $article->feed->category->name : '',
                'word_count' => (int)$article->word_count,
                'estimated_read_minutes' => max(1, (int)$article->estimated_read_minutes),
            ),
        )));
    }

    public function workbenchStatus(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        $status = (string)$request->input('status', '');
        if (!in_array($status, array('read', 'unread', 'read_later', 'star'), true)) {
            throw new CustomException('status状态上送错误');
        }
        $this->articleService->setArticleSubStatus($articleSub, $status);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_sub_id' => $articleSub->id,
            'status' => $status,
        )));
    }

    public function workbenchPageRead(Request $request)
    {
        $ids = array_values(array_filter(array_map('intval', (array)$request->input('ids', array()))));
        if (empty($ids)) {
            throw new CustomException('当前页没有可标记为已读的文章');
        }

        $articleSubs = ArticleSub::where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->where('status', 'unread')
            ->get();
        if ($articleSubs->isEmpty()) {
            throw new CustomException('当前页没有未读文章');
        }

        $processCount = $this->articleService->setArticleSubStatusByIds($articleSubs->pluck('id')->all(), 'read');
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'count' => $processCount,
            'article_sub_ids' => $articleSubs->pluck('id')->all(),
            'status' => 'read',
        )));
    }

    public function workbenchAiRender(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        $articleSub->load('article.feed.category');
        if (!$articleSub->article) {
            abort(404);
        }

        $customPrompt = trim((string)$request->input('custom_prompt', ''));
        if (strlen($customPrompt) > 3000) {
            $customPrompt = substr($customPrompt, 0, 3000);
        }
        $article = $articleSub->article;
        $render = $this->articleAiRenderService->ensureRender($article, array(
            'force' => $customPrompt !== '',
            'custom_prompt' => $customPrompt,
            'template_style' => 'magazine',
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_id' => $article->id,
            'ai_render' => array(
                'status' => $render->status,
                'summary' => $render->summary,
                'outline' => (array)$render->outline_json,
                'html_content' => $render->html_content,
                'error_message' => $render->error_message,
            ),
        )));
    }

    /**
     * 探索版分类及订阅源目录。
     */
    public function explorerFeeds(Request $request)
    {
        $status = $this->resolveExplorerStatus($request);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'status' => $status,
            'nav_infos' => array_values($this->articleService->getNavInfo($status)),
        )));
    }

    /**
     * 探索版文章标题列表。此接口不读取或返回正文。
     */
    public function explorerArticleList(Request $request, $feedId)
    {
        $status = $this->resolveExplorerStatus($request);
        $feedSub = FeedSub::where('user_id', Auth::id())
            ->where('feed_id', $feedId)
            ->first();
        if (!$feedSub) {
            abort(404);
        }

        $pageCount = max(10, min(100, (int)$request->input('page_count', 40)));
        $articleSubs = ArticleSub::join('articles', 'article_subs.article_id', '=', 'articles.id')
            ->where('article_subs.user_id', Auth::id())
            ->where('article_subs.feed_id', $feedId)
            ->where('article_subs.status', $status)
            ->select(array(
                'article_subs.id',
                'article_subs.article_id',
                'article_subs.status',
                'articles.subject',
                'articles.published',
            ))
            ->orderBy('article_subs.updated_at', 'desc')
            ->paginate($pageCount);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'feed' => array(
                'id' => (int)$feedId,
                'name' => $feedSub->feed_name,
            ),
            'status' => $status,
            'articles' => $articleSubs->items(),
            'pagination' => array(
                'current_page' => $articleSubs->currentPage(),
                'last_page' => $articleSubs->lastPage(),
                'has_more_pages' => $articleSubs->hasMorePages(),
            ),
        )));
    }

    protected function resolveExplorerStatus(Request $request)
    {
        $status = (string)$request->input('status', 'unread');
        if (!in_array($status, array('unread', 'read', 'star', 'read_later'), true)) {
            $status = 'unread';
        }
        return $status;
    }

    /**
     * 探索版按需加载单篇正文。
     */
    public function explorerArticle(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);
        $articleSub->load('article.feed');
        if (!$articleSub->article) {
            abort(404);
        }

        $article = $articleSub->article;
        $wordCount = (int)$article->word_count;
        $estimatedReadMinutes = (int)$article->estimated_read_minutes;

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'article_sub_id' => $articleSub->id,
            'status' => $articleSub->status,
            'word_count' => $wordCount,
            'estimated_read_minutes' => max(1, $estimatedReadMinutes),
            'article' => array(
                'id' => $article->id,
                'subject' => $article->subject,
                'url' => $article->url,
                'published' => $article->published,
                'content' => CommonUtil::formatContentHtml($article->content),
                'feed_name' => $article->feed ? $article->feed->feed_name : '',
            ),
        )));
    }

    /**
     * Legacy route compatibility. Article creation is not supported in web flow.
     */
    public function store(Request $request)
    {
        return $this->jsonResponse($request, ResponseDataUtil::genCommonFail('legacy article store endpoint is deprecated, please use /api/v2/articles*'));
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
        return view('articles.list');
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
        return view('articles.view', compact('article'));
    }

    public function aiRender(Request $request, Article $article)
    {
        $articleSub = $this->resolveOwnedArticleSub($article);
        if (!$articleSub) {
            abort(403, '未找到当前文章的阅读记录');
        }

        $article->load('feed');
        $render = $this->articleAiRenderService->getRenderByArticleId($article->id);
        if (empty($render) || empty($render->html_content)) {
            $render = $this->articleAiRenderService->ensureRender($article, array(
                'force' => (int)$request->input('force', 0) === 1,
                'template_style' => (string)$request->input('template_style', 'magazine'),
            ));
        }

        return view('articles.ai_render', compact('article', 'articleSub', 'render'));
    }

    public function generateAiRenderWeb(Request $request, Article $article)
    {
        $articleSub = $this->resolveOwnedArticleSub($article);
        if (!$articleSub) {
            abort(403, '未找到当前文章的阅读记录');
        }

        $article->load('feed');
        $this->articleAiRenderService->ensureRender($article, array(
            'force' => true,
            'template_style' => (string)$request->input('template_style', 'magazine'),
        ));

        return redirect('/article/' . $article->id . '/ai-render?refresh=1');
    }

    public function stream(Request $request)
    {
        return view('articles.stream');
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

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'article' => $articleSub->article,
            'count' => $processCount
        ]), '/articles');
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

    /**
     * 代理访问异常网站
     * @param Request $request
     */
    public function proxyView(Request $request) {
        $type = $request->get('type', '');
        $url = $request->get('url', '');
        if(empty($type) || empty($url)) {
            echo 'params error';
            exit;
        }
        if($type == 'v2ex') {
            $newurl = 'https://www.v2ex.com' . $url;
//            echo file_get_contents('https://www.v2ex.com' . $url);

            // 初始化 cURL 会话
            $ch = curl_init();

            // 设置请求的 URL
            curl_setopt($ch, CURLOPT_URL, $newurl);

            // 设置请求头
            $headers = array(
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
                'sec-ch-ua: "Not A(Brand";v="8", "Chromium";v="132", "Google Chrome";v="132"',
                'sec-ch-ua-mobile:?0',
                'sec-ch-ua-platform: "macOS"'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // 设置为不输出响应头
            curl_setopt($ch, CURLOPT_HEADER, false);

            // 只获取页面内容，不直接输出
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // 执行 cURL 请求
            $response = curl_exec($ch);

            // 检查请求是否成功
            if ($response === false) {
                echo 'cURL Error: ' . curl_error($ch).'<a href="'.$newurl.'">'.$newurl.'</a>';
            } else {
                // 输出响应内容
                echo $response;
            }

            // 关闭 cURL 会话
            curl_close($ch);

            exit;
        }
    }

    protected function resolveOwnedArticleSub(Article $article)
    {
        if (!Auth::check()) {
            return null;
        }

        return ArticleSub::where('article_id', $article->id)
            ->where('user_id', Auth::id())
            ->first();
    }
}
