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

    /**
     * 课程各章节测试状态：GET /api/v2/courses/{courseId}/quiz-status
     */
    public function status(Request $request, $courseId)
    {
        $course = $this->getOwnedCourse($request, $courseId);
        // 章节最近一次「AI 生成测试」的失败理由（成功生成或手动保存后会被清空）
        $quizErrors = CourseItem::where('course_id', $course->id)
            ->whereNotNull('quiz_generation_error')
            ->where('quiz_generation_error', '<>', '')
            ->pluck('quiz_generation_error', 'id')
            ->all();
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'quiz_status' => $this->quizService->statusForCourse($course->id),
            'quiz_errors' => $quizErrors,
        )));
    }

    /**
     * 以章节为主线批量生成测试：POST /api/v2/courses/{courseId}/quizzes/generate
     * body: { item_ids: [..], question_count?: 5, passing_score?: 70 }
     */
    public function generate(Request $request, $courseId)
    {
        $course = $this->getOwnedCourse($request, $courseId);
        $itemIds = $request->input('item_ids');
        if (!is_array($itemIds) || empty($itemIds)) {
            throw new CustomException('请至少选择一个章节');
        }
        if (count($itemIds) > 100) {
            throw new CustomException('单次生成章节数不能超过 100 个');
        }
        $result = $this->quizService->generateForItems($course, $itemIds, array(
            'question_count' => $request->input('question_count', 5),
            'passing_score' => $request->input('passing_score', 70),
        ));
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    protected function getOwnedCourse(Request $request, $courseId)
    {
        $course = \App\Models\Course::where('id', $courseId)->first();
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        if ((int)$course->created_by !== (int)$this->getAuthUserId($request)) {
            throw new CustomException('您没有权限管理此课程');
        }
        return $course;
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
