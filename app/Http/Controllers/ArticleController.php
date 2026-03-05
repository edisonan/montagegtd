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
        return view('articles.view');
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
}
