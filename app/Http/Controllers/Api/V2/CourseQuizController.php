<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\CourseItem;
use App\Services\CourseQuizService;
use Illuminate\Http\Request;

class CourseQuizController extends Controller
{
    protected $quizService;

    public function __construct(CourseQuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    public function show(Request $request, $itemId)
    {
        $item = $this->getAccessibleItem($request, $itemId, false);
        $quiz = $this->quizService->getQuizForItem($item->id);
        if (!$quiz) {
            throw new CustomException('该章节还没有测试');
        }
        $quiz->questions->each(function ($question) {
            $question->options->each(function ($option) {
                unset($option->is_correct);
            });
        });
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('quiz' => $quiz)));
    }

    public function store(Request $request, $itemId)
    {
        $item = $this->getAccessibleItem($request, $itemId, true);
        $quiz = $this->quizService->saveQuiz($item->id, $request->all());
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('quiz' => $quiz)));
    }

    public function submit(Request $request, $itemId)
    {
        $item = $this->getAccessibleItem($request, $itemId, false);
        $answers = $request->input('answers', array());
        if (!is_array($answers)) {
            throw new CustomException('测试答案格式错误');
        }
        $result = $this->quizService->submit($item->id, (int)$this->getAuthUserId($request), $answers);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function attempts(Request $request, $itemId)
    {
        $item = $this->getAccessibleItem($request, $itemId, false);
        $quiz = $this->quizService->getQuizForItem($item->id);
        if (!$quiz) {
            throw new CustomException('该章节还没有测试');
        }
        $attempts = $quiz->attempts()->where('user_id', $this->getAuthUserId($request))->latest()->get();
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('attempts' => $attempts)));
    }

    protected function getAccessibleItem(Request $request, $itemId, $ownerOnly)
    {
        $item = CourseItem::find($itemId);
        if (!$item) {
            throw new CustomException('课程章节不存在');
        }
        $course = $item->course;
        $userId = (int)$this->getAuthUserId($request);
        if ($ownerOnly) {
            if (!$course || (int)$course->created_by !== $userId) {
                throw new CustomException('您没有权限管理此测试');
            }
        } elseif (!$course || (int)$course->created_by !== $userId) {
            $enrollment = app('App\\Services\\CourseService')->getUserCourseByUserIdAndCourseId($userId, $course->id);
            if (!$enrollment) {
                throw new CustomException('请先加入课程');
            }
        }
        return $item;
    }
}
