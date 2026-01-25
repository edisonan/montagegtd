<?php

namespace App\Repositories;

use App\Models\CourseEnrollment;

class CourseEnrollmentRepository
{
    /**
     * 获取用户的课程注册信息
     */
    public function getCourseEnrollments($userId, $status = null)
    {
        $query = CourseEnrollment::where('user_id', $userId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('order_index', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * 根据ID获取课程注册信息
     */
    public function getCourseEnrollmentById($id, $userId = null)
    {
        $query = CourseEnrollment::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->first();
    }

    /**
     * 获取用户特定课程的注册记录
     */
    public function getCourseEnrollmentByUserIdAndCourseId($userId, $courseId)
    {
        return CourseEnrollment::where('user_id', $userId)
                        ->where('course_id', $courseId)
                        ->first();
    }

    /**
     * 获取课程的所有注册记录
     */
    public function getCourseEnrollmentsByCourseId($courseId)
    {
        return CourseEnrollment::where('course_id', $courseId)->get();
    }

    /**
     * 创建课程注册
     */
    public function createCourseEnrollment(array $data)
    {
        return CourseEnrollment::create($data);
    }

    /**
     * 更新课程注册
     */
    public function updateCourseEnrollment($id, array $data, $userId = null)
    {
        $query = CourseEnrollment::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $courseEnrollment = $query->first();
        if ($courseEnrollment) {
            $courseEnrollment->update($data);
        }
        return $courseEnrollment;
    }

    /**
     * 删除课程注册
     */
    public function deleteCourseEnrollment($id, $userId = null)
    {
        $query = CourseEnrollment::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $courseEnrollment = $query->first();
        if ($courseEnrollment) {
            return $courseEnrollment->delete();
        }
        return false;
    }
}