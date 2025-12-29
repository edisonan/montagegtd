<?php

namespace App\Repositories;

use App\Models\CourseItem;

class CourseItemRepository
{
    /**
     * 获取课程的所有项目
     */
    public function getCourseItems($courseId, $parentId = null)
    {
        $query = CourseItem::where('course_id', $courseId);
        
        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }
        
        return $query->orderBy('order_index', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();
    }

    /**
     * 根据ID获取课程项目
     */
    public function getCourseItemById($id)
    {
        return CourseItem::where('id', $id)->first();
    }

    /**
     * 创建课程项目
     */
    public function createCourseItem(array $data)
    {
        return CourseItem::create($data);
    }

    /**
     * 更新课程项目
     */
    public function updateCourseItem($id, array $data)
    {
        $item = CourseItem::where('id', $id)->first();
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    /**
     * 删除课程项目
     */
    public function deleteCourseItem($id)
    {
        $item = CourseItem::where('id', $id)->first();
        if ($item) {
            return $item->delete();
        }
        return false;
    }
}