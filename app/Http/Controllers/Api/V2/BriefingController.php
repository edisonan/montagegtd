<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Repositories\BriefingPageRepository;
use App\Services\BriefingGenerationService;
use App\Services\BriefingService;
use Illuminate\Http\Request;

class BriefingController extends Controller
{
    protected $briefingService;
    protected $pageRepository;
    protected $generationService;

    public function __construct(
        BriefingService $briefingService,
        BriefingPageRepository $pageRepository,
        BriefingGenerationService $generationService
    ) {
        $this->briefingService = $briefingService;
        $this->pageRepository = $pageRepository;
        $this->generationService = $generationService;
    }

    /**
     * 配置列表
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
     * 保存（新建/编辑）配置
     */
    public function saveConfig(Request $request)
    {
        $this->validate($request, array(
            'name' => 'nullable|string|max:120',
            'pull_hours' => 'required|integer|min:1|max:24',
            'schedule_time' => 'required|string',
            'scope' => 'required|in:all,feeds,exclude_feeds,by_category',
            'feed_ids' => 'array',
            'category_ids' => 'array',
            'supplement' => 'nullable|string|max:2000',
            'enabled' => 'boolean',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $config = $this->briefingService->saveConfigByUserId($userId, $request->all());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'config' => $this->briefingService->serializeConfig($config),
        )));
    }

    /**
     * 删除配置
     */
    public function destroyConfig(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $deleted = $this->briefingService->destroyConfig((int)$id, $userId);
        if (!$deleted) {
            throw new CustomException('配置不存在或无权操作');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    /**
     * 手动立即生成
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
        )));
    }

    /**
     * 简报结果列表
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
     * 简报详情
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
     * 删除一条简报结果
     */
    public function destroyPage(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $deleted = $this->briefingService->destroyPage((int)$id, $userId);
        if (!$deleted) {
            throw new CustomException('简报不存在或无权操作');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
