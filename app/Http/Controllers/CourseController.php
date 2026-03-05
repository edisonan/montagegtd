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
     * 课程管理
     */
    public function management(Request $request)
    {
        return view('courses.management');
    }

    /**
     * 显示单个课程详情
     */
    public function show(Request $request, $id)
    {
        return view('courses.show');
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
            'public_status' => 'nullable|integer|in:1,2'
        ]);

        $data = [
            'title' => $request->input('title'),
            'platform' => $request->input('platform'),
            'instructor' => $request->input('instructor'),
            'public_url' => $request->input('public_url'),
            'description' => $request->input('description'),
            'cover_image_url' => $request->input('cover_image_url'),
            'public_status' => $request->input('public_status', 2), // 默认为待审核状态
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
            'public_status' => 'nullable|integer|in:1,2,3'
        ]);

        $data = [
            'title' => $request->input('title'),
            'platform' => $request->input('platform'),
            'instructor' => $request->input('instructor'),
            'public_url' => $request->input('public_url'),
            'description' => $request->input('description'),
            'cover_image_url' => $request->input('cover_image_url'),
            'public_status' => $request->input('public_status', 2), // 默认为待审核状态
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
     * 我的课程
     */
    public function index(Request $request)
    {
        return view('courses.index');
    }
}
