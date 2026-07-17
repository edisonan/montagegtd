<?php

namespace App\Services;

use App\Repositories\CourseRepository;
use App\Repositories\CourseEnrollmentRepository;
use App\Repositories\CourseItemRepository;

class CourseService
{
    protected $courseRepository;
    protected $courseEnrollmentRepository;
    protected $courseItemRepository;

    public function __construct(
        CourseRepository $courseRepository,
        CourseEnrollmentRepository $courseEnrollmentRepository,
        CourseItemRepository $courseItemRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->courseEnrollmentRepository = $courseEnrollmentRepository;
        $this->courseItemRepository = $courseItemRepository;
    }

    /**
     * 获取所有课程列表
     */
    public function getAllCourses($userId = null, $isPublicOnly = true)
    {
        return $this->courseRepository->getAllCourses(false, $userId, $isPublicOnly);
    }

    /**
     * 根据ID获取课程
     */
    public function getCourseById($id)
    {
        return $this->courseRepository->getCourseById($id);
    }

    /**
     * 创建课程
     */
    public function createCourse(array $data)
    {
        // 验证必需字段
        if (empty($data['title']) || empty($data['created_by'])) {
            throw new \Exception('课程标题和创建者为必填项');
        }

        // 如果没有提供user_id，使用created_by
        if (!isset($data['user_id']) && isset($data['created_by'])) {
            $data['user_id'] = $data['created_by'];
        }

        // 设置默认的public_status为2（公开待审核）
        if (!isset($data['public_status'])) {
            $data['public_status'] = 2;
        }

        if (!isset($data['content_status'])) {
            $data['content_status'] = 'published';
        }

        if (!empty($data['source_key'])) {
            $existing = $this->courseRepository->getByCreatorAndSourceKey($data['created_by'], $data['source_key']);
            if ($existing) {
                return $existing;
            }
        }

        return $this->courseRepository->createCourse($data);
    }

    /**
     * 更新课程
     */
    public function updateCourse($id, array $data)
    {
        // 检查课程是否存在
        $course = $this->courseRepository->getCourseById($id);
        if (!$course) {
            throw new \Exception('课程不存在');
        }

        return $this->courseRepository->updateCourse($id, $data);
    }

    /**
     * 删除课程
     */
    public function deleteCourse($id)
    {
        // 检查课程是否存在
        $course = $this->courseRepository->getCourseById($id);
        if (!$course) {
            throw new \Exception('课程不存在');
        }

        // 检查是否有用户课程关联
        $courseEnrollments = $this->courseEnrollmentRepository->getCourseEnrollmentsByCourseId($id);
        if ($courseEnrollments && count($courseEnrollments) > 0) {
            throw new \Exception('无法删除有关联用户学习记录的课程');
        }

        return $this->courseRepository->deleteCourse($id);
    }

    /**
     * 用户加入课程
     */
    public function joinCourse($userId, $courseId, $customTitle = null)
    {
        // 检查课程是否存在
        $course = $this->courseRepository->getCourseById($courseId);
        if (!$course) {
            throw new \Exception('课程不存在');
        }

        // 检查课程是否已审核通过（public_status = 3）
        if ($course->public_status != 3) {
            throw new \Exception('无法加入未审核通过的课程');
        }

        // 检查用户是否已经加入了课程
        $existingCourseEnrollment = $this->courseEnrollmentRepository->getCourseEnrollmentByUserIdAndCourseId($userId, $courseId);
        if ($existingCourseEnrollment) {
            throw new \Exception('您已经加入了该课程');
        }

        // 创建用户课程记录
        $courseEnrollmentData = [
            'user_id' => $userId,
            'course_id' => $courseId,
            'title' => $customTitle ?: $course->title,
            'status' => 'planned'
        ];

        return $this->courseEnrollmentRepository->createCourseEnrollment($courseEnrollmentData);
    }

    /**
     * 获取用户的所有课程
     */
    public function getUserCourses($userId, $status = null)
    {
        return $this->courseEnrollmentRepository->getCourseEnrollments($userId, $status);
    }

    /**
     * 获取课程的项目列表
     */
    public function getCourseItems($courseId, $parentId = null)
    {
        return $this->courseItemRepository->getCourseItems($courseId, $parentId);
    }

    /**
     * 获取课程的完整层级结构
     */
    public function getCourseStructure($courseId)
    {
        $rootItems = $this->courseItemRepository->getCourseItems($courseId, null);

        foreach ($rootItems as $item) {
            $this->loadChildren($item, $courseId);
        }

        return $rootItems;
    }

    protected function loadChildren($item, $courseId)
    {
        $item->children = $this->courseItemRepository->getCourseItems($courseId, $item->id);
        foreach ($item->children as $child) {
            $this->loadChildren($child, $courseId);
        }
    }

    /**
     * 创建课程项目
     */
    public function createCourseItem(array $data)
    {
        // 验证必需字段
        if (empty($data['course_id']) || empty($data['title'])) {
            throw new \Exception('课程ID和标题为必填项');
        }

        if (!isset($data['content_status'])) {
            $data['content_status'] = 'published';
        }

        if (!empty($data['source_key'])) {
            $existing = $this->courseItemRepository->getByCourseAndSourceKey($data['course_id'], $data['source_key']);
            if ($existing) {
                return $existing;
            }
        }

        return $this->courseItemRepository->createCourseItem($data);
    }

    /**
     * 更新课程项目
     */
    public function updateCourseItem($id, array $data)
    {
        // 检查课程项目是否存在
        $item = $this->courseItemRepository->getCourseItemById($id);
        if (!$item) {
            throw new \Exception('课程项目不存在');
        }

        return $this->courseItemRepository->updateCourseItem($id, $data);
    }

    /**
     * 删除课程项目
     */
    public function deleteCourseItem($id)
    {
        // 检查课程项目是否存在
        $item = $this->courseItemRepository->getCourseItemById($id);
        if (!$item) {
            throw new \Exception('课程项目不存在');
        }

        return $this->courseItemRepository->deleteCourseItem($id);
    }

    /**
     * 根据ID获取课程项目
     */
    public function getCourseItemById($id)
    {
        return $this->courseItemRepository->getCourseItemById($id);
    }

    /**
     * 获取用户特定课程的记录
     */
    public function getUserCourseByUserIdAndCourseId($userId, $courseId)
    {
        return $this->courseEnrollmentRepository->getCourseEnrollmentByUserIdAndCourseId($userId, $courseId);
    }
    
    /**
     * 获取用户创建的课程
     */
    public function getUserCreatedCourses($userId, $withTrashed = false)
    {
        return $this->courseRepository->getUserCreatedCourses($userId, $withTrashed);
    }
    
    /**
     * 获取公开课程（包括待审核的）
     */
    public function getPublicCourses($withTrashed = false, $includePending = false)
    {
        return $this->courseRepository->getPublicCourses($withTrashed, $includePending);
    }
    
    /**
     * 审核课程（将public_status从2改为3）
     */
    public function approveCourse($id)
    {
        $course = $this->courseRepository->getCourseById($id);
        if (!$course) {
            throw new \Exception('课程不存在');
        }
        
        $course->public_status = 3;
        $course->save();
        
        return $course;
    }
    
    /**
     * 将课程设为待审核状态（将public_status从3改为2）
     */
    public function unapproveCourse($id)
    {
        $course = $this->courseRepository->getCourseById($id);
        if (!$course) {
            throw new \Exception('课程不存在');
        }
        
        $course->public_status = 2;
        $course->save();
        
        return $course;
    }
}
