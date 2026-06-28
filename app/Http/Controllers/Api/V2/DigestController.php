<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Repositories\DigestPageRepository;
use App\Services\DigestGenerationService;
use App\Services\DigestProfileService;
use Illuminate\Http\Request;

class DigestController extends Controller
{
    protected $digestProfileService;
    protected $digestPageRepository;
    protected $digestGenerationService;

    public function __construct(
        DigestProfileService $digestProfileService,
        DigestPageRepository $digestPageRepository,
        DigestGenerationService $digestGenerationService
    ) {
        $this->digestProfileService = $digestProfileService;
        $this->digestPageRepository = $digestPageRepository;
        $this->digestGenerationService = $digestGenerationService;
    }

    public function profile(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $profile = $this->digestProfileService->getProfileByUserId($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'profile' => $profile,
        )));
    }

    public function saveProfile(Request $request)
    {
        $this->validate($request, array(
            'topics' => 'array',
            'include_keywords' => 'array',
            'exclude_keywords' => 'array',
            'preferred_categories' => 'array',
            'time_window_days' => 'integer|in:1,3,7',
            'frequency' => 'in:daily,weekly',
            'max_articles' => 'integer|min:5|max:50',
            'output_style' => 'nullable|string|max:32',
            'enabled' => 'boolean',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $profile = $this->digestProfileService->saveProfileByUserId($userId, $request->all());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'profile' => $profile,
        )));
    }

    public function pages(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $this->digestProfileService->assertWhitelist($userId);
        $perPage = max(1, (int)$request->input('page_count', 20));
        $pages = $this->digestPageRepository->paginateByUserId($userId, $perPage);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'pages' => $pages->items(),
            'pagination' => array(
                'current_page' => $pages->currentPage(),
                'per_page' => $pages->perPage(),
                'next_page_url' => $pages->nextPageUrl(),
                'prev_page_url' => $pages->previousPageUrl(),
                'has_more_pages' => $pages->hasMorePages(),
            ),
        )));
    }

    public function showPage(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        $this->digestProfileService->assertWhitelist($userId);
        $page = $this->digestPageRepository->findById($id);

        if (!$page || (int)$page->user_id !== $userId) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '汇合页不存在', array()));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'page' => $page,
        )));
    }

    public function generate(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $this->digestProfileService->assertWhitelist($userId);
        $task = $this->digestGenerationService->createManualTaskForUser($userId);

        if (!$task) {
            return $this->jsonResponse($request, ResponseDataUtil::genFail(1001, '未找到可用的汇合页配置', array()));
        }

        $result = $this->digestGenerationService->processTask($task->id);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'task_id' => $task->id,
            'result' => $result,
        )));
    }
}
