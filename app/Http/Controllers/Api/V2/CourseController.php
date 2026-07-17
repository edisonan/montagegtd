<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
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
            'user_courses' => $userCourses,
        )));
    }

    public function management(Request $request)
    {
        $userId = $this->getAuthUserId($request);

        $userCreatedCourses = $userId ? $this->courseService->getUserCreatedCourses($userId) : collect();
        $publicCourses = $this->courseService->getPublicCourses(false, false);

        $userCourseIds = array();
        if ($userId) {
            $userCourses = $this->courseService->getUserCourses($userId);
            $userCourseIds = $userCourses->pluck('course_id')->toArray();
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'user_created_courses' => $userCreatedCourses,
            'public_courses' => $publicCourses,
            'user_course_ids' => $userCourseIds,
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
        if ($userId) {
            $userCourse = $this->courseService->getUserCourseByUserIdAndCourseId($userId, $id);
            $isJoined = $userCourse !== null;
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $course,
            'structure' => $courseStructure,
            'is_joined' => $isJoined,
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
            'user_courses' => $userCourses,
        )));
    }
}
