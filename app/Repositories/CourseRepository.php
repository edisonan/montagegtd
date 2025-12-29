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
            $query->where('is_public', true);
        }
        
        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('is_public', true);
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
        return Course::create($data);
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
}