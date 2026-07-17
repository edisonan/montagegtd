<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\CourseContentService;
use Illuminate\Http\Request;

class CourseContentController extends Controller
{
    protected $contentService;

    public function __construct(CourseContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    public function generate(Request $request, $courseId)
    {
        $items = $this->contentService->generate($courseId, (int)$this->getAuthUserId($request), $request->all());
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('items' => $items, 'content_status' => 'draft')));
    }

    public function fetch(Request $request, $courseId)
    {
        $items = $this->contentService->fetch($courseId, (int)$this->getAuthUserId($request), $request->all());
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('items' => $items, 'content_status' => 'draft')));
    }
}
