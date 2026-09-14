<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Repositories\BriefingV2PageRepository;
use App\Services\BriefingV2GenerationService;
use App\Services\BriefingV2Service;
use Illuminate\Http\Request;

/**
 * 文章简报 v2 API 控制器（对比版）
 *
 * @author edison.an
 */
class BriefingV2Controller extends Controller
{
    protected $briefingService;
    protected $pageRepository;
    protected $generationService;

    public function __construct(
        BriefingV2Service $briefingService,
        BriefingV2PageRepository $pageRepository,
        BriefingV2GenerationService $generationService
    ) {
        $this->briefingService = $briefingService;
        $this->pageRepository = $pageRepository;
        $this->generationService = $generationService;
    }

    /**
     * 配置列表（只读复用 v1 配置，附 v2 生成历史）
     */
    public function configs(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $configs = $this->briefingService->getConfigsByUserId($userId);

        $data = array();
        foreach ($configs as $config) {
            $data[] = $this->briefingService->serializeConfig($config);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'configs' => $data,
        )));
    }

    /**
     * 手动立即生成 v2
     */
    public function generate(Request $request, $configId)
    {
        $userId = (int)$this->getAuthUserId($request);
        $result = $this->generationService->generateForConfig((int)$configId, $userId);

        if (empty($result['status']) || $result['status'] !== 'success') {
            throw new CustomException('生成失败：' . ($result['message'] ?? '未知错误'));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'page_id' => (int)$result['page_id'],
            'fallback' => (int)$result['fallback'],
            'candidates' => (int)$result['candidates'],
        )));
    }

    /**
     * v2 简报结果列表
     */
    public function pages(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $configId = $request->input('config_id', '');
        $perPage = max(1, (int)$request->input('page_count', 20));

        $pages = $this->pageRepository->paginateByUserId($userId, $perPage, $configId);

        $data = array();
        foreach ($pages->items() as $page) {
            $data[] = $this->briefingService->serializePageMeta($page);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'pages' => $data,
            'pagination' => array(
                'current_page' => $pages->currentPage(),
                'per_page' => $pages->perPage(),
                'next_page_url' => $pages->nextPageUrl(),
                'prev_page_url' => $pages->previousPageUrl(),
                'has_more_pages' => $pages->hasMorePages(),
            ),
        )));
    }

    /**
     * v2 简报详情
     */
    public function showPage(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $page = $this->pageRepository->findById((int)$id);

        if (!$page || (int)$page->user_id !== $userId) {
            throw new CustomException('简报不存在或无权访问');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'page' => $this->briefingService->serializePage($page),
        )));
    }

    /**
     * 删除一条 v2 简报结果
     */
    public function destroyPage(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $deleted = $this->pageRepository->destroy($id, $userId);
        if (!$deleted) {
            throw new CustomException('简报不存在或无权操作');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}