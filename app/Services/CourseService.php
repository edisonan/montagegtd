<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Repositories\CourseRepository;
use App\Repositories\CourseEnrollmentRepository;
use App\Repositories\CourseItemRepository;
use App\Models\CourseItem;
use App\Models\UserProgress;

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
            throw new CustomException('课程标题和创建者为必填项');
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
            throw new CustomException('课程不存在');
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
            throw new CustomException('课程不存在');
        }

        // 检查是否有用户课程关联
        $courseEnrollments = $this->courseEnrollmentRepository->getCourseEnrollmentsByCourseId($id);
        if ($courseEnrollments && count($courseEnrollments) > 0) {
            throw new CustomException('无法删除有关联用户学习记录的课程');
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
            throw new CustomException('课程不存在');
        }

        // 课程创建者可以加入自己的课程（无论是否已公开）；其他用户仅可加入已审核通过的公开课程
        $isOwner = $course->created_by && (int)$course->created_by === (int)$userId;
        if ($course->public_status != 3 && !$isOwner) {
            throw new CustomException('无法加入未审核通过的课程');
        }

        // 检查用户是否已经加入了课程
        $existingCourseEnrollment = $this->courseEnrollmentRepository->getCourseEnrollmentByUserIdAndCourseId($userId, $courseId);
        if ($existingCourseEnrollment) {
            throw new CustomException('您已经加入了该课程');
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
     * 一次拉取全量节点并在内存中建树，避免逐节点查库（N+1）
     */
    public function getCourseStructure($courseId)
    {
        $items = CourseItem::where('course_id', $courseId)
            ->orderBy('order_index', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $byParent = $items->groupBy(function ($item) {
            return (int)$item->parent_id;
        });

        $build = function ($parentId) use (&$build, $byParent) {
            $children = collect();
            foreach ($byParent->get($parentId, collect()) as $item) {
                $item->children = $build((int)$item->id);
                $children->push($item);
            }
            return $children;
        };

        // parent_id 为 NULL 的节点归入 key 0（(int)null === 0）
        return $build(0);
    }

    /**
     * 给课程结构树附加当前学习者的逐节完成状态
     * 叶子节点增加 is_completed=true/false，供沉浸学习页展示"已学/未学"并计算下一节。
     */
    public function attachUserProgressToStructure($structure, $userCourseId = null)
    {
        if (!$userCourseId || !$structure || !is_iterable($structure)) {
            return $structure;
        }

        $completedIds = UserProgress::where('user_course_id', $userCourseId)
            ->where('status', 'completed')
            ->pluck('course_item_id')
            ->map(function ($v) {
                return (int)$v;
            })
            ->all();

        $decorate = function ($items) use (&$decorate, $completedIds) {
            foreach ($items as $item) {
                $item->is_completed = in_array((int)$item->id, $completedIds, true);
                if (!empty($item->children)) {
                    $decorate($item->children);
                }
            }
            return $items;
        };

        return $decorate($structure);
    }

    /**
     * 根据ID获取用户课程学习记录
     */
    public function getCourseEnrollmentById($id)
    {
        return $this->courseEnrollmentRepository->getCourseEnrollmentById($id);
    }

    /**
     * 更新用户课程学习记录
     */
    public function updateCourseEnrollment($id, array $data)
    {
        $enrollment = $this->courseEnrollmentRepository->getCourseEnrollmentById($id);
        if (!$enrollment) {
            throw new CustomException('学习记录不存在');
        }
        return $this->courseEnrollmentRepository->updateCourseEnrollment($id, $data);
    }

    /**
     * 聚合某条学习记录的进度并回写（标记完成/测验提交后调用）
     * 规则：已完成课时数 / 课程总课时数 = progress_percent；更新 last_activity_at；
     * 首次完成置为 active，全部完成置为 completed。
     */
    public function recomputeEnrollmentProgress($userCourseId)
    {
        $enrollment = $this->courseEnrollmentRepository->getCourseEnrollmentById($userCourseId);
        if (!$enrollment) {
            return null;
        }

        // 分母 = 可完成的"课时"（非容器节点）。容器（module/chapter 类型或带子章节的节点）不参与计数，
        // 与前端"标记完成"仅对课时开放的规则一致，否则多层课程永远到不了 100%。
        $parentIds = CourseItem::where('course_id', $enrollment->course_id)
            ->whereNotNull('parent_id')
            ->distinct()
            ->pluck('parent_id');
        $total = CourseItem::where('course_id', $enrollment->course_id)
            ->whereNotIn('id', $parentIds)
            ->whereNotIn('item_type', array('module', 'chapter'))
            ->count();
        $completed = UserProgress::where('user_id', $enrollment->user_id)
            ->where('user_course_id', $enrollment->id)
            ->where('status', 'completed')
            ->count();

        $percent = $total > 0 ? (int)round($completed * 100 / $total) : 0;

        $enrollment->progress_percent = $percent;
        $enrollment->last_activity_at = now();

        if ($enrollment->status === 'planned' && $completed > 0) {
            $enrollment->status = 'active';
        }
        if ($total > 0 && $completed >= $total) {
            $enrollment->status = 'completed';
            $enrollment->completed_date = now();
        }

        $enrollment->save();
        return $enrollment;
    }

    /**
     * 创建课程项目
     */
    public function createCourseItem(array $data)
    {
        // 验证必需字段
        if (empty($data['course_id']) || empty($data['title'])) {
            throw new CustomException('课程ID和标题为必填项');
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
            throw new CustomException('课程项目不存在');
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
            throw new CustomException('课程项目不存在');
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
            throw new CustomException('课程不存在');
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
            throw new CustomException('课程不存在');
        }
        
        $course->public_status = 2;
        $course->save();
        
        return $course;
    }
}
