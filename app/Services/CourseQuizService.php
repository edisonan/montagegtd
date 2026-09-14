<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Course;
use App\Models\CourseItem;
use App\Models\CourseQuiz;
use App\Models\CourseQuizAttempt;
use App\Models\CourseQuizOption;
use App\Models\CourseQuizQuestion;
use App\Models\CourseReviewItem;
use App\Models\UserProgress;
use App\Repositories\CourseEnrollmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseQuizService
{
    protected $courseEnrollmentRepository;
    protected $courseService;
    protected $llmStructuredTaskService;

    public function __construct(
        CourseEnrollmentRepository $courseEnrollmentRepository,
        CourseService $courseService,
        LlmStructuredTaskService $llmStructuredTaskService
    ) {
        $this->courseEnrollmentRepository = $courseEnrollmentRepository;
        $this->courseService = $courseService;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    public function getQuizForItem($itemId)
    {
        return CourseQuiz::with(array('questions.options'))->where('course_item_id', $itemId)->first();
    }

    public function saveQuiz($itemId, array $data)
    {
        $item = CourseItem::find($itemId);
        if (!$item) {
            throw new CustomException('课程章节不存在');
        }
        if (!isset($data['questions']) || !is_array($data['questions']) || empty($data['questions'])) {
            throw new CustomException('测试至少需要一道题目');
        }

        return DB::transaction(function () use ($item, $data) {
            $quiz = CourseQuiz::firstOrNew(array('course_item_id' => $item->id));
            $quiz->passing_score = isset($data['passing_score']) ? (float)$data['passing_score'] : 70;
            $quiz->attempts_allowed = isset($data['attempts_allowed']) ? $data['attempts_allowed'] : null;
            $quiz->status = isset($data['status']) ? $data['status'] : 'published';
            $quiz->save();

            // 测验已成功保存（AI 生成成功或手动编辑保存），清掉上次生成失败的标记
            if (!empty($item->quiz_generation_error)) {
                $item->quiz_generation_error = null;
                $item->save();
            }

            CourseQuizQuestion::where('quiz_id', $quiz->id)->delete();
            foreach (array_values($data['questions']) as $index => $questionData) {
                if (empty($questionData['question'])) {
                    throw new CustomException('题目内容不能为空');
                }
                $question = CourseQuizQuestion::create(array(
                    'quiz_id' => $quiz->id,
                    'question_type' => isset($questionData['question_type']) ? $questionData['question_type'] : 'single',
                    'question' => $questionData['question'],
                    'explanation' => isset($questionData['explanation']) ? $questionData['explanation'] : null,
                    'points' => isset($questionData['points']) ? $questionData['points'] : 1,
                    'order_index' => $index,
                    'source_type' => isset($questionData['source_type']) ? $questionData['source_type'] : 'manual',
                ));
                foreach (array_values((array)(isset($questionData['options']) ? $questionData['options'] : array())) as $optionIndex => $optionData) {
                    if (is_string($optionData)) {
                        $optionData = array('option_key' => chr(65 + $optionIndex), 'content' => $optionData);
                    }
                    if (empty($optionData['content'])) {
                        continue;
                    }
                    CourseQuizOption::create(array(
                        'question_id' => $question->id,
                        'option_key' => isset($optionData['option_key']) ? $optionData['option_key'] : chr(65 + $optionIndex),
                        'content' => $optionData['content'],
                        'is_correct' => !empty($optionData['is_correct']),
                        'order_index' => $optionIndex,
                    ));
                }
            }
            return $quiz->fresh(array('questions.options'));
        });
    }

    /**
     * 课程内各章节是否有测试：返回 [item_id => bool]
     */
    public function statusForCourse($courseId)
    {
        $itemIds = CourseItem::where('course_id', $courseId)->pluck('id');
        $hasQuizIds = CourseQuiz::whereIn('course_item_id', $itemIds)->pluck('course_item_id')->all();
        $map = array();
        foreach ($itemIds as $itemId) {
            $map[(int)$itemId] = false;
        }
        foreach ($hasQuizIds as $itemId) {
            $map[(int)$itemId] = true;
        }
        return $map;
    }

    /**
     * 以章节为主线批量生成测试：为选中章节逐章调用 LLM 生成题目并落库。
     * 单项失败不中断整体，逐项返回结果。
     *
     * @param Course $course
     * @param array $itemIds
     * @param array $options [question_count, passing_score]
     * @return array ['results' => [...], 'success_count' => int, 'fail_count' => int]
     */
    public function generateForItems(Course $course, array $itemIds, array $options = array())
    {
        $questionCount = max(3, min(20, (int)($options['question_count'] ?? 5)));
        $passingScore = max(1, min(100, (float)($options['passing_score'] ?? 70)));

        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $items = CourseItem::where('course_id', $course->id)
            ->whereIn('id', $itemIds)
            ->get()
            ->keyBy('id');

        $results = array();
        $successCount = 0;
        $failCount = 0;
        foreach ($itemIds as $itemId) {
            $item = $items->get($itemId);
            if (!$item) {
                $failCount++;
                $results[] = array('item_id' => $itemId, 'title' => '', 'success' => false, 'error' => '章节不存在');
                continue;
            }
            try {
                $questions = $this->generateQuestionsForItem($item, $questionCount);
                if (empty($questions)) {
                    throw new CustomException('AI 未返回有效题目');
                }
                $quiz = $this->saveQuiz($item->id, array(
                    'passing_score' => $passingScore,
                    'attempts_allowed' => null,
                    'status' => 'published',
                    'questions' => $questions,
                ));
                $successCount++;
                $results[] = array(
                    'item_id' => (int)$item->id,
                    'title' => $item->title,
                    'success' => true,
                    'quiz_id' => (int)$quiz->id,
                    'question_count' => count($questions),
                    'error' => null,
                );
            } catch (\Throwable $e) {
                $failCount++;
                $errorMessage = Str::limit($e->getMessage(), 200, '');
                // 失败理由落库，供课程管理页刷新后仍可查看
                $item->quiz_generation_error = $errorMessage;
                $item->save();
                $results[] = array(
                    'item_id' => (int)$item->id,
                    'title' => $item->title,
                    'success' => false,
                    'error' => $errorMessage,
                );
            }
        }

        return array(
            'results' => $results,
            'success_count' => $successCount,
            'fail_count' => $failCount,
        );
    }

    /**
     * 调用 LLM 为一个章节生成测试题（严格 JSON 校验后转为题组结构）
     */
    protected function generateQuestionsForItem(CourseItem $item, $questionCount)
    {
        $title = trim((string)$item->title);
        $description = trim((string)$item->description);
        $content = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$item->content)));
        $content = mb_substr($content, 0, 4000);
        $text = trim(implode("\n", array_filter(array(
            $title !== '' ? '标题：' . $title : '',
            $description !== '' ? '简介：' . $description : '',
            $content !== '' ? '正文：' . $content : '',
        ))));
        if ($text === '') {
            throw new CustomException('章节内容为空，无法生成测试');
        }

        $prompt = '你是课程测验设计师。请根据下面的课程章节内容，为学员生成 ' . $questionCount . ' 道选择题（单选或双选）测试学习效果。'
            . '题目必须基于章节内容，难度适中，并给出答案解析。'
            . "章节内容：\n" . $text . "\n"
            . "返回严格 JSON，不要输出 Markdown 代码围栏：{\"questions\":[{\"question\":\"题干\",\"question_type\":\"single\",\"options\":[{\"content\":\"选项A\",\"is_correct\":true},{\"content\":\"选项B\",\"is_correct\":false}],\"explanation\":\"答案解析\"}]}\n"
            . '要求：每题 3~6 个选项；question_type 为 single 时恰好一个 is_correct 为 true，为 multiple 时恰好两个 is_correct 为 true；共 ' . $questionCount . ' 道题。';

        $result = $this->llmStructuredTaskService->runTask('course_quiz_generation', array(
            array('role' => 'system', 'content' => '你是课程测验设计师，只输出合法 JSON。'),
            array('role' => 'user', 'content' => $prompt),
        ), array('response_format' => array('type' => 'json_object'), 'timeout' => 120));

        if (empty($result['success'])) {
            throw new CustomException($result['error'] ?: '测试生成失败');
        }
        $payload = $this->decodeJsonContent($result['content']);
        $questions = isset($payload['questions']) && is_array($payload['questions']) ? $payload['questions'] : array();
        if (empty($questions)) {
            throw new CustomException('AI 未返回有效题目');
        }

        $normalized = array();
        foreach (array_values($questions) as $index => $question) {
            if (empty($question['question'])) continue;
            $options = array();
            $correctCount = 0;
            foreach (array_values((array)($question['options'] ?? array())) as $optionIndex => $option) {
                if (is_string($option)) {
                    $option = array('content' => $option, 'is_correct' => $optionIndex === 0);
                }
                if (empty($option['content'])) continue;
                $isCorrect = !empty($option['is_correct']);
                if ($isCorrect) $correctCount++;
                $options[] = array('content' => $option['content'], 'is_correct' => $isCorrect);
            }
            if (count($options) < 3 || $correctCount < 1) continue;
            $questionType = ($question['question_type'] ?? 'single') === 'multiple' ? 'multiple' : 'single';
            // 多选题目必须有两个正确答案，否则降级为单选并修正为第一个正确项
            if ($questionType === 'multiple' && $correctCount !== 2) {
                $questionType = 'single';
                $fixed = 0;
                foreach ($options as $k => $option) {
                    $options[$k]['is_correct'] = $fixed < 1 && $option['is_correct'];
                    if ($options[$k]['is_correct']) $fixed++;
                }
            }
            $normalized[] = array(
                'question' => $question['question'],
                'question_type' => $questionType,
                'explanation' => $question['explanation'] ?? null,
                'points' => 1,
                'source_type' => 'ai',
                'options' => $options,
            );
        }
        return $normalized;
    }

    protected function decodeJsonContent($content)
    {
        $content = trim((string)$content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content);
        $data = json_decode($content, true);
        return is_array($data) ? $data : array();
    }

    public function submit($itemId, $userId, array $answers)
    {
        $quiz = $this->getQuizForItem($itemId);
        if (!$quiz) {
            throw new CustomException('该章节还没有测试');
        }
        $item = CourseItem::find($itemId);
        $enrollment = $this->courseEnrollmentRepository->getCourseEnrollmentByUserIdAndCourseId($userId, $item->course_id);
        if (!$enrollment) {
            throw new CustomException('请先加入课程');
        }

        // 作答次数上限（attempts_allowed 为空表示不限）
        if ($quiz->attempts_allowed !== null) {
            $taken = CourseQuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $userId)
                ->count();
            if ($taken >= (int)$quiz->attempts_allowed) {
                throw new CustomException('该章节测试已达作答次数上限（' . (int)$quiz->attempts_allowed . ' 次）');
            }
        }

        $correctCount = 0;
        $results = array();
        foreach ($quiz->questions as $question) {
            $submitted = array_key_exists($question->id, $answers) ? $answers[$question->id] : array();
            $submitted = is_array($submitted) ? $submitted : array($submitted);
            $submitted = array_values(array_unique(array_map('strval', $submitted)));
            $correct = $question->options->filter(function ($option) {
                return (bool)$option->is_correct;
            })->pluck('option_key')->map(function ($key) { return (string)$key; })->values()->all();
            sort($submitted);
            sort($correct);
            $isCorrect = $submitted === $correct && !empty($correct);
            if ($isCorrect) {
                $correctCount++;
            }
            $results[] = array(
                'question_id' => (int)$question->id,
                'submitted' => $submitted,
                'correct' => $isCorrect,
                'correct_options' => $correct,
                'explanation' => $question->explanation,
            );
        }

        $totalCount = count($quiz->questions);
        $score = $totalCount ? round($correctCount * 100 / $totalCount, 2) : 0;
        $passed = $score >= (float)$quiz->passing_score;
        $attempt = CourseQuizAttempt::create(array(
            'quiz_id' => $quiz->id,
            'user_id' => $userId,
            'user_course_id' => $enrollment->id,
            'score' => $score,
            'correct_count' => $correctCount,
            'total_count' => $totalCount,
            'passed' => $passed,
            'answers' => $results,
            'completed_at' => now(),
        ));

        $this->updateProgress($userId, $enrollment->id, $itemId, $score, $passed);

        // 测验通过视为完成该课时，并聚合学习进度
        if ($passed) {
            try {
                $this->courseService->recomputeEnrollmentProgress($enrollment->id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('recompute enrollment progress after quiz failed', array(
                    'user_course_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ));
            }
        }

        return array('attempt' => $attempt, 'score' => $score, 'passed' => $passed, 'results' => $results);
    }

    protected function updateProgress($userId, $userCourseId, $itemId, $score, $passed)
    {
        $progress = UserProgress::firstOrNew(array(
            'user_id' => $userId,
            'user_course_id' => $userCourseId,
            'course_item_id' => $itemId,
        ));
        $quizId = CourseQuiz::where('course_item_id', $itemId)->value('id');
        $previousPassed = CourseQuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)->where('passed', true)->count();
        $progress->status = $passed ? 'completed' : 'in_progress';
        $progress->mastery_status = $passed && $previousPassed >= 2 ? 'mastered' : ($passed ? 'completed' : 'reviewing');
        $progress->mastery_score = $score;
        $progress->last_accessed_at = now();
        $progress->completed_at = $passed ? now() : $progress->completed_at;
        $progress->review_due_at = now()->addDays($passed ? ($previousPassed >= 2 ? 7 : 1) : 0);
        $progress->save();

        $review = CourseReviewItem::firstOrNew(array(
            'user_id' => $userId,
            'user_course_id' => $userCourseId,
            'course_item_id' => $itemId,
        ));
        $review->status = $progress->mastery_status === 'mastered' ? 'mastered' : 'due';
        $review->review_count = (int)$review->review_count + 1;
        $review->interval_days = $passed ? ($review->interval_days * 2) : 1;
        $review->last_score = $score;
        $review->last_reviewed_at = now();
        $review->next_review_at = $progress->review_due_at;
        $review->save();
    }

    public function dueReviews($userId)
    {
        return CourseReviewItem::with('courseItem')
            ->where('user_id', $userId)
            ->where('status', 'due')
            ->where(function ($query) {
                $query->whereNull('next_review_at')->orWhere('next_review_at', '<=', now());
            })
            ->orderBy('next_review_at')
            ->get();
    }
}
