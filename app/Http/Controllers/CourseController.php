<?php

namespace App\Http\Controllers;

use App\Services\CourseService;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->courseService = $courseService;
    }

    /**
     * 课程列表页
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $courses = $this->courseService->getAllCourses($userId, true);
        
        // 获取用户已加入的课程ID列表
        $userCourseIds = [];
        if ($userId) {
            $userCourses = $this->courseService->getUserCourses($userId);
            $userCourseIds = $userCourses->pluck('course_id')->toArray();
        }
        
        return $this->jsonAndViewAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'courses' => $courses,
            'user_course_ids' => $userCourseIds
        ]), 'courses.index');
    }

    /**
     * 显示单个课程详情
     */
    public function show(Request $request, $id)
    {
        $course = $this->courseService->getCourseById($id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        
        // 获取课程结构
        $courseStructure = $this->courseService->getCourseStructure($id);
        
        // 检查用户是否已加入课程
        $userId = auth()->id();
        $isJoined = false;
        if ($userId) {
            $userCourse = $this->courseService->getUserCourseByUserIdAndCourseId($userId, $id);
            $isJoined = $userCourse !== null;
        }
        
        return $this->jsonAndViewAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'course' => $course,
            'structure' => $courseStructure,
            'is_joined' => $isJoined
        ]), 'courses.show');
    }

    /**
     * 创建课程
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'platform' => 'nullable|max:100',
            'instructor' => 'nullable|max:100',
            'public_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'estimated_hours' => 'nullable|integer|min:0',
            'is_public' => 'boolean'
        ]);

        $data = [
            'title' => $request->input('title'),
            'platform' => $request->input('platform'),
            'instructor' => $request->input('instructor'),
            'public_url' => $request->input('public_url'),
            'description' => $request->input('description'),
            'cover_image_url' => $request->input('cover_image_url'),
            'is_public' => $request->input('is_public', true),
            'created_by' => auth()->id(),
            'difficulty' => $request->input('difficulty', 'beginner'),
            'estimated_hours' => $request->input('estimated_hours'),
            'tags' => $request->input('tags', [])
        ];

        $course = $this->courseService->createCourse($data);
        
        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'course' => $course
        ]), '/courses');
    }

    /**
     * 更新课程
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'platform' => 'nullable|max:100',
            'instructor' => 'nullable|max:100',
            'public_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'estimated_hours' => 'nullable|integer|min:0',
            'is_public' => 'boolean'
        ]);

        $data = [
            'title' => $request->input('title'),
            'platform' => $request->input('platform'),
            'instructor' => $request->input('instructor'),
            'public_url' => $request->input('public_url'),
            'description' => $request->input('description'),
            'cover_image_url' => $request->input('cover_image_url'),
            'is_public' => $request->input('is_public', true),
            'difficulty' => $request->input('difficulty', 'beginner'),
            'estimated_hours' => $request->input('estimated_hours'),
            'tags' => $request->input('tags', [])
        ];

        $course = $this->courseService->updateCourse($id, $data);
        
        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'course' => $course
        ]), '/courses');
    }

    /**
     * 删除课程
     */
    public function destroy(Request $request, $id)
    {
        $this->courseService->deleteCourse($id);
        
        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/courses');
    }

    /**
     * 显示创建课程表单
     */
    public function create(Request $request)
    {
        return view('courses.create');
    }

    /**
     * 用户加入课程
     */
    public function joinCourse(Request $request, $id)
    {
        $userId = auth()->id();
        $customTitle = $request->input('title'); // 允许用户自定义课程标题

        $userCourse = $this->courseService->joinCourse($userId, $id, $customTitle);
        
        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'user_course' => $userCourse
        ]), "/courses/{$id}");
    }

    /**
     * 获取用户课程列表
     */
    public function getUserCourses(Request $request)
    {
        $userId = auth()->id();
        $status = $request->input('status'); // 可选参数：planned, active, completed等

        $userCourses = $this->courseService->getUserCourses($userId, $status);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'user_courses' => $userCourses
        ]));
    }

    /**
     * 课程管理首页
     */
    public function managementIndex(Request $request)
    {
        $userId = auth()->id();
        
        // 获取用户的所有课程
        $userCourses = $this->courseService->getUserCourses($userId);
        
        return $this->jsonAndViewAutoResponse($request, ResponseDataUtil::genSimpleSucc([
            'user_courses' => $userCourses
        ]), 'course-management.index');
    }
}