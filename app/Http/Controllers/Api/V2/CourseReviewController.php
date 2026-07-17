<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\CourseQuizService;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    protected $quizService;

    public function __construct(CourseQuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    public function index(Request $request)
    {
        $reviews = $this->quizService->dueReviews((int)$this->getAuthUserId($request));
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('reviews' => $reviews)));
    }
}
