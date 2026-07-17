<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository
{
    /**
     * 获取所有课程列表
     */
    public function getAllCourses($withTrashed = false, $userId = null, $isPublicOnly = true)
    {
        $query = Course::query();
        
        if ($isPublicOnly) {
            $query->where('public_status', 3); // 只获取审核通过的公开课程
        }
        
        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('public_status', 3); // 用户可以看到自己创建的课程和审核通过的公开课程
            });
        }
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * 根据ID获取课程
     */
    public function getCourseById($id, $withTrashed = false)
    {
        $query = Course::where('id', $id);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->first();
    }

    /**
     * 创建课程
     */
    public function createCourse(array $data)
    {
        // 设置默认的public_status为2（公开待审核）
        if (!isset($data['public_status'])) {
            $data['public_status'] = 2;
        }
        
        return Course::create($data);
    }

    public function getByCreatorAndSourceKey($userId, $sourceKey)
    {
        return Course::where('created_by', $userId)->where('source_key', $sourceKey)->first();
    }

    /**
     * 更新课程
     */
    public function updateCourse($id, array $data)
    {
        $course = Course::where('id', $id)->first();
        if ($course) {
            $course->update($data);
        }
        return $course;
    }

    /**
     * 删除课程
     */
    public function deleteCourse($id, $force = false)
    {
        $course = Course::where('id', $id)->first();
        if ($course) {
            if ($force) {
                return $course->forceDelete();
            } else {
                return $course->delete();
            }
        }
        return false;
    }

    /**
     * 恢复已软删除的课程
     */
    public function restoreCourse($id)
    {
        return Course::withTrashed()->where('id', $id)->restore();
    }
    
    /**
     * 获取用户创建的课程列表
     */
    public function getUserCreatedCourses($userId, $withTrashed = false)
    {
        $query = Course::where('created_by', $userId);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
    
    /**
     * 获取所有公开课程（包括待审核的）
     */
    public function getPublicCourses($withTrashed = false, $includePending = false)
    {
        $query = Course::query();
        
        if ($includePending) {
            $query->whereIn('public_status', [2, 3]); // 包括待审核和已审核的
        } else {
            $query->where('public_status', 3); // 只获取审核通过的
        }
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
}
