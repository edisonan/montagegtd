<?php

namespace App\Http\Controllers;

use App\Repositories\BriefingConfigRepository;
use App\Repositories\BriefingPageRepository;
use App\Services\BriefingGenerationService;
use Illuminate\Http\Request;

/**
 * 文章简报 Web 控制器（页面渲染）
 *
 * @author edison.an
 */
class BriefingController extends Controller
{
    protected $configRepository;
    protected $pageRepository;
    protected $generationService;

    public function __construct(
        BriefingConfigRepository $configRepository,
        BriefingPageRepository $pageRepository,
        BriefingGenerationService $generationService
    ) {
        $this->middleware('auth');
        $this->configRepository = $configRepository;
        $this->pageRepository = $pageRepository;
        $this->generationService = $generationService;
    }

    /**
     * 简报首页（配置列表 + 简报历史）
     */
    public function index(Request $request)
    {
        return $this->renderNoCacheView('briefings.index');
    }

    /**
     * 简报详情页
     */
    public function show(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $page = $this->pageRepository->findById((int)$id);
        if (!$page || (int)$page->user_id !== $userId) {
            abort(404, '编号不存在');
        }

        return $this->renderNoCacheView('briefings.show', array(
            'pageId' => (int)$page->id,
        ));
    }

    /**
     * 配置页（新建 / 编辑）
     */
    public function config(Request $request, $id = null)
    {
        return $this->renderNoCacheView('briefings.config', array(
            'configId' => $id !== null ? (int)$id : null,
        ));
    }

    /**
     * 渲染视图并禁止缓存（避免浏览器缓存旧版 JS 导致页面卡在加载中）
     */
    protected function renderNoCacheView($view, array $data = array())
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * 手动立即生成（Web 表单/请求）
     */
    public function generate(Request $request, $configId)
    {
        $userId = (int)$this->getAuthUserId($request);
        $result = $this->generationService->generateForConfig((int)$configId, $userId);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->jsonResponse($request, \App\Http\Utils\ResponseDataUtil::genSimpleSucc(array(
                'status' => $result['status'] ?? 'failed',
                'page_id' => isset($result['page_id']) ? (int)$result['page_id'] : null,
                'message' => $result['message'] ?? null,
            )));
        }

        if (!empty($result['status']) && $result['status'] === 'success') {
            return redirect('/briefings/' . (int)$result['page_id']);
        }

        return redirect('/briefings')->with('message', '生成失败：' . ($result['message'] ?? '未知错误'));
    }
}
