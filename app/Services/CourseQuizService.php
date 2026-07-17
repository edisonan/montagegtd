<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\CourseItem;
use App\Models\CourseQuiz;
use App\Models\CourseQuizAttempt;
use App\Models\CourseQuizOption;
use App\Models\CourseQuizQuestion;
use App\Models\CourseReviewItem;
use App\Models\UserProgress;
use App\Repositories\CourseEnrollmentRepository;
use Illuminate\Support\Facades\DB;

class CourseQuizService
{
    protected $courseEnrollmentRepository;

    public function __construct(CourseEnrollmentRepository $courseEnrollmentRepository)
    {
        $this->courseEnrollmentRepository = $courseEnrollmentRepository;
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
