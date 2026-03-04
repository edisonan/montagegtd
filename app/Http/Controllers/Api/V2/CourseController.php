<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
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
        );

        $course = $this->courseService->createCourse($data);

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

    public function join(Request $request, $id)
    {
        $userId = $this->getAuthUserId($request);
        $customTitle = $request->input('title');
        $userCourse = $this->courseService->joinCourse($userId, $id, $customTitle);

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
