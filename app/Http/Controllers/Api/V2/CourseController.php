<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\CourseEnrollment;
use App\Models\CourseItem;
use App\Services\CourseService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    protected $courseService;
    protected $pointGrantService;

    public function __construct(CourseService $courseService, PointGrantService $pointGrantService)
    {
        $this->courseService = $courseService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        $userCourses = $this->courseService->getUserCourses($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'user_courses' => $this->serializeUserCourses($userCourses),
        )));
    }

    public function management(Request $request)
    {
        $userId = $this->getAuthUserId($request);

        $userCreatedCourses = $userId ? $this->courseService->getUserCreatedCourses($userId) : collect();
        $publicCourses = $this->courseService->getPublicCourses(false, false);

        $userCourseIds = array();
        $userCourses = collect();
        if ($userId) {
            $userCourses = $this->courseService->getUserCourses($userId);
            $userCourseIds = $userCourses->pluck('course_id')->toArray();
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'user_created_courses' => $this->serializeCourseList($userCreatedCourses),
            'public_courses' => $this->serializeCourseList($publicCourses),
            'user_course_ids' => $userCourseIds,
            'user_courses' => $this->serializeUserCourses($userCourses),
        )));
    }

    public function show(Request $request, $id)
    {
        $course = $this->courseService->getCourseById($id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ($course->public_status != 3 && (!$userId || (int)$course->created_by !== (int)$userId)) {
            throw new CustomException('课程不存在或未审核通过');
        }

        $courseStructure = $this->courseService->getCourseStructure($id);

        $isJoined = false;
        $userCourse = null;
        if ($userId) {
            $userCourse = $this->courseService->getUserCourseByUserIdAndCourseId($userId, $id);
            $isJoined = $userCourse !== null;
        }

        $course = $this->withCourseCounts($course);
        $course->is_owner = $userId && (int)$course->created_by === (int)$userId;

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $course,
            'structure' => $courseStructure,
            'is_joined' => $isJoined,
            'user_course' => $userCourse ? array(
                'id' => (int)$userCourse->id,
                'status' => (string)$userCourse->status,
                'progress_percent' => (int)round((float)$userCourse->progress_percent),
            ) : null,
        )));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'title' => 'required|max:255',
            'description' => 'nullable',
            'platform' => 'nullable|max:100',
            'instructor' => 'nullable|max:100',
            'public_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'estimated_hours' => 'nullable|integer|min:0',
            'public_status' => 'nullable|integer|in:1,2',
            'content_status' => 'nullable|in:draft,published,archived',
            'source_type' => 'nullable|string|max:40',
            'source_key' => 'nullable|string|max:191',
            'content_hash' => 'nullable|string|max:64',
        ));

        $data = array(
            'title' => $request->input('title'),
            'platform' => $request->input('platform'),
            'instructor' => $request->input('instructor'),
            'public_url' => $request->input('public_url'),
            'description' => $request->input('description'),
            'cover_image_url' => $request->input('cover_image_url'),
            'public_status' => $request->input('public_status', 2),
            'created_by' => $this->getAuthUserId($request),
            'difficulty' => $request->input('difficulty', 'beginner'),
            'estimated_hours' => $request->input('estimated_hours'),
            'tags' => $request->input('tags', array()),
            'source_type' => $request->input('source_type', 'manual'),
            'source_key' => $request->input('source_key'),
            'content_hash' => $request->input('content_hash'),
            'generated_at' => $request->input('generated_at'),
            'content_status' => $request->input('content_status', 'published'),
        );

        $course = $this->courseService->createCourse($data);
        try {
            $this->pointGrantService->grantByEvent(
                (int)$course->created_by,
                'course_created',
                'course',
                (int)$course->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on course create failed', array(
                'course_id' => $course->id,
                'user_id' => $course->created_by,
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $course,
        )));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, array(
            'title' => 'required|max:255',
            'description' => 'nullable',
            'platform' => 'nullable|max:100',
            'instructor' => 'nullable|max:100',
            'public_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'estimated_hours' => 'nullable|integer|min:0',
            'public_status' => 'nullable|integer|in:1,2,3',
            'content_status' => 'nullable|in:draft,published,archived',
            'source_type' => 'nullable|string|max:40',
            'source_key' => 'nullable|string|max:191',
            'content_hash' => 'nullable|string|max:64',
        ));

        $course = $this->courseService->getCourseById($id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$course->created_by !== (int)$userId) {
            throw new CustomException('您没有权限管理此课程');
        }

        $data = array(
            'title' => $request->input('title'),
            'platform' => $request->input('platform'),
            'instructor' => $request->input('instructor'),
            'public_url' => $request->input('public_url'),
            'description' => $request->input('description'),
            'cover_image_url' => $request->input('cover_image_url'),
            'public_status' => $request->input('public_status', 2),
            'difficulty' => $request->input('difficulty', 'beginner'),
            'estimated_hours' => $request->input('estimated_hours'),
            'tags' => $request->input('tags', array()),
            'source_type' => $request->input('source_type', $course->source_type ?: 'manual'),
            'source_key' => $request->input('source_key', $course->source_key),
            'content_hash' => $request->input('content_hash', $course->content_hash),
            'generated_at' => $request->input('generated_at', $course->generated_at),
            'content_status' => $request->input('content_status', $course->content_status ?: 'published'),
        );

        $updatedCourse = $this->courseService->updateCourse($id, $data);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $updatedCourse,
        )));
    }

    public function destroy(Request $request, $id)
    {
        $course = $this->courseService->getCourseById($id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$course->created_by !== (int)$userId) {
            throw new CustomException('您没有权限管理此课程');
        }

        $this->courseService->deleteCourse($id);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function publish(Request $request, $id)
    {
        $course = $this->courseService->getCourseById($id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        if ((int)$course->created_by !== (int)$this->getAuthUserId($request)) {
            throw new CustomException('您没有权限发布此课程');
        }
        $course->content_status = 'published';
        $course->save();
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('course' => $course->fresh())));
    }

    /**
     * 提交公开审核（public_status: 1/2 -> 2）
     */
    public function requestPublic(Request $request, $id)
    {
        $course = $this->ownedCourseOrFail($id, $request);
        $course->public_status = 2;
        $course->save();
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $this->withCourseCounts($course->fresh()),
            'msg' => '已提交公开审核，审核通过后对所有用户可见',
        )));
    }

    /**
     * 审核通过（public_status: 2 -> 3）
     */
    public function approve(Request $request, $id)
    {
        $course = $this->ownedCourseOrFail($id, $request);
        $course = $this->courseService->approveCourse($id);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $this->withCourseCounts($course),
            'msg' => '课程已审核通过并公开',
        )));
    }

    /**
     * 撤回公开（public_status: 3 -> 2）
     */
    public function unapprove(Request $request, $id)
    {
        $course = $this->ownedCourseOrFail($id, $request);
        $course = $this->courseService->unapproveCourse($id);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $this->withCourseCounts($course),
            'msg' => '课程已撤回公开，转为待审核状态',
        )));
    }

    protected function ownedCourseOrFail($id, Request $request)
    {
        $course = $this->courseService->getCourseById($id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        $userId = $this->getAuthUserId($request);
        if (!$userId || (int)$course->created_by !== (int)$userId) {
            throw new CustomException('您没有权限管理此课程');
        }
        return $course;
    }

    /**
     * 统一补齐前端展示用计数字段
     */
    protected function withCourseCounts($course)
    {
        if (!$course) {
            return $course;
        }
        if (!isset($course->course_items_count)) {
            $course->course_items_count = CourseItem::where('course_id', $course->id)->count();
        }
        if (!isset($course->course_enrollments_count)) {
            $course->course_enrollments_count = CourseEnrollment::where('course_id', $course->id)->count();
        }
        $course->chapters_count = (int)$course->course_items_count;
        $course->enrollment_count = (int)$course->course_enrollments_count;
        return $course;
    }

    /**
     * 列表序列化（补章节数与学习人数）
     */
    protected function serializeCourseList($courses)
    {
        if ($courses instanceof \Illuminate\Support\Collection) {
            foreach ($courses as $course) {
                $this->withCourseCounts($course);
            }
        }
        return $courses;
    }

    public function automation(Request $request, $id)
    {
        $course = $this->courseService->getCourseById($id);
        if (!$course || (int)$course->created_by !== (int)$this->getAuthUserId($request)) {
            throw new CustomException('课程不存在或无权限');
        }
        $this->validate($request, array(
            'enabled' => 'required|boolean',
            'mode' => 'required|in:ai,fetch',
            'frequency_minutes' => 'nullable|integer|min:60|max:10080',
            'topic' => 'nullable|string|max:255',
            'source_url' => 'nullable|url',
        ));
        $config = array(
            'enabled' => (bool)$request->input('enabled'),
            'mode' => $request->input('mode'),
            'frequency_minutes' => (int)$request->input('frequency_minutes', 1440),
            'topic' => $request->input('topic'),
            'source_url' => $request->input('source_url'),
            'next_run_at' => now()->toDateTimeString(),
        );
        $course->automation_config = $config;
        $course->save();
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('automation' => $config)));
    }

    public function join(Request $request, $id)
    {
        $userId = $this->getAuthUserId($request);
        $customTitle = $request->input('title');
        $userCourse = $this->courseService->joinCourse($userId, $id, $customTitle);
        try {
            $this->pointGrantService->grantByEvent(
                (int)$userId,
                'course_joined',
                'user_course',
                (int)$userCourse->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on course join failed', array(
                'course_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'user_course' => $userCourse,
        )));
    }

    public function enrollments(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        $status = $request->input('status');
        $userCourses = $this->courseService->getUserCourses($userId, $status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'user_courses' => $this->serializeUserCourses($userCourses),
        )));
    }

    /**
     * 更新本人学习记录状态（暂停/完成/放弃/恢复等）
     */
    public function updateEnrollment(Request $request, $id)
    {
        $userId = (int)$this->getAuthUserId($request);
        if (!$userId) {
            throw new CustomException('用户未认证');
        }

        $enrollment = $this->courseService->getCourseEnrollmentById($id);
        if (!$enrollment || (int)$enrollment->user_id !== $userId) {
            throw new CustomException('您没有权限操作此学习记录');
        }

        $this->validate($request, array(
            'status' => 'nullable|in:planned,active,completed,paused,dropped',
            'goal' => 'nullable|string|max:255',
            'target_end_date' => 'nullable|date',
            'show_progress' => 'nullable|boolean',
            'show_notes' => 'nullable|boolean',
            'show_study_time' => 'nullable|boolean',
        ));

        $data = array();
        foreach (array('status', 'goal', 'target_end_date', 'show_progress', 'show_notes', 'show_study_time') as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        if (isset($data['status']) && $data['status'] === 'completed') {
            $data['completed_date'] = now();
        }

        $updated = $this->courseService->updateCourseEnrollment($id, $data);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'user_course' => $updated->fresh(),
            'msg' => '学习状态已更新',
        )));
    }

    /**
     * 序列化用户课程（报名记录）为前端可直接使用的结构，附带课程冗余字段
     */
    protected function serializeUserCourses($enrollments)
    {
        $items = array();
        if ($enrollments instanceof \Illuminate\Support\Collection && !$enrollments->isEmpty()) {
            foreach ($enrollments as $e) {
                $course = $e->course;
                $items[] = array(
                    'id' => (int)$e->id,
                    'course_id' => (int)$e->course_id,
                    'title' => (string)($e->title ?: ($course ? $course->title : '')),
                    'status' => (string)($e->status ?: 'planned'),
                    'progress_percent' => (int)round((float)$e->progress_percent),
                    'last_studied_at' => !empty($e->last_activity_at) ? (string)$e->last_activity_at : '',
                    'order_index' => (int)$e->order_index,
                    'course' => $course ? array(
                        'id' => (int)$course->id,
                        'title' => (string)$course->title,
                        'description' => (string)($course->description ?: ''),
                        'cover_image_url' => (string)($course->cover_image_url ?: ''),
                        'instructor' => (string)($course->instructor ?: ''),
                        'estimated_hours' => (int)($course->estimated_hours ?: 0),
                        'chapters_count' => (int)($course->course_items_count ?: 0),
                    ) : null,
                );
            }
        }

        return $items;
    }
}
