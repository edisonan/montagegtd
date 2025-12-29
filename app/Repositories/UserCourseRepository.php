<?php

namespace App\Repositories;

use App\Models\UserCourse;

class UserCourseRepository
{
    /**
     * 获取用户的所有课程
     */
    public function getUserCourses($userId, $status = null)
    {
        $query = UserCourse::where('user_id', $userId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('order_index', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * 根据ID获取用户课程
     */
    public function getUserCourseById($id, $userId = null)
    {
        $query = UserCourse::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->first();
    }

    /**
     * 获取用户特定课程的记录
     */
    public function getUserCourseByUserIdAndCourseId($userId, $courseId)
    {
        return UserCourse::where('user_id', $userId)
                        ->where('course_id', $courseId)
                        ->first();
    }

    /**
     * 获取课程的所有用户记录
     */
    public function getUserCoursesByCourseId($courseId)
    {
        return UserCourse::where('course_id', $courseId)->get();
    }

    /**
     * 创建用户课程
     */
    public function createUserCourse(array $data)
    {
        return UserCourse::create($data);
    }

    /**
     * 更新用户课程
     */
    public function updateUserCourse($id, array $data, $userId = null)
    {
        $query = UserCourse::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $userCourse = $query->first();
        if ($userCourse) {
            $userCourse->update($data);
        }
        return $userCourse;
    }

    /**
     * 删除用户课程
     */
    public function deleteUserCourse($id, $userId = null)
    {
        $query = UserCourse::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $userCourse = $query->first();
        if ($userCourse) {
            return $userCourse->delete();
        }
        return false;
    }
}