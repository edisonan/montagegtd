<?php

namespace App\Http\Controllers;

use App\Repositories\BriefingV2PageRepository;
use App\Services\BriefingV2GenerationService;
use Illuminate\Http\Request;

/**
 * 文章简报 v2 Web 控制器（页面渲染，对比版）
 *
 * @author edison.an
 */
class BriefingV2Controller extends Controller
{
    protected $pageRepository;
    protected $generationService;

    public function __construct(
        BriefingV2PageRepository $pageRepository,
        BriefingV2GenerationService $generationService
    ) {
        $this->middleware('auth');
        $this->pageRepository = $pageRepository;
        $this->generationService = $generationService;
    }

    /**
     * v2 首页（配置列表 + v2 简报历史）
     */
    public function index(Request $request)
    {
        return $this->renderNoCacheView('briefings.v2_index');
    }

    /**
     * v2 简报详情页
     */
    public function show(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $page = $this->pageRepository->findById((int)$id);
        if (!$page || (int)$page->user_id !== $userId) {
            abort(404, '编号不存在');
        }

        return $this->renderNoCacheView('briefings.v2_show', array(
            'pageId' => (int)$page->id,
        ));
    }

    /**
     * 手动立即生成 v2（Web 表单/请求）
     */
    public function generate(Request $request, $configId)
    {
        $userId = (int)$this->getAuthUserId($request);
        $result = $this->generationService->generateForConfig((int)$configId, $userId);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->jsonResponse($request, \App\Http\Utils\ResponseDataUtil::genSimpleSucc(array(
                'status' => $result['status'] ?? 'failed',
                'page_id' => isset($result['page_id']) ? (int)$result['page_id'] : null,
                'fallback' => isset($result['fallback']) ? (int)$result['fallback'] : null,
                'message' => $result['message'] ?? null,
            )));
        }

        if (!empty($result['status']) && $result['status'] === 'success') {
            return redirect('/briefings-v2/' . (int)$result['page_id']);
        }

        return redirect('/briefings-v2')->with('message', '生成失败：' . ($result['message'] ?? '未知错误'));
    }

    /**
     * 渲染视图并禁止缓存
     */
    protected function renderNoCacheView($view, array $data = array())
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}