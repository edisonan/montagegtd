<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseItemController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(Request $request, $courseId)
    {
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$userId !== (int)$course->created_by) {
            $userCourse = $this->courseService->getUserCourseByUserIdAndCourseId($userId, $courseId);
            if (!$userCourse) {
                throw new CustomException('您没有权限访问此课程内容');
            }
        }

        $courseStructure = $this->courseService->getCourseStructure($courseId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course' => $course,
            'structure' => $courseStructure,
        )));
    }

    public function show(Request $request, $id)
    {
        $courseItem = $this->courseService->getCourseItemById($id);
        if (!$courseItem) {
            throw new CustomException('课程项目不存在');
        }

        $course = $this->courseService->getCourseById($courseItem->course_id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$userId !== (int)$course->created_by) {
            $userCourse = $this->courseService->getUserCourseByUserIdAndCourseId($userId, $courseItem->course_id);
            if (!$userCourse) {
                throw new CustomException('您没有权限访问此课程内容');
            }
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course_item' => $courseItem,
        )));
    }

    public function getStructure(Request $request, $courseId)
    {
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$userId !== (int)$course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }

        $courseStructure = $this->courseService->getCourseStructure($courseId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'result' => $courseStructure,
        )));
    }

    public function store(Request $request, $courseId)
    {
        $request->merge(array('course_id' => $courseId));
        return $this->storeFromModal($request);
    }

    public function storeFromModal(Request $request)
    {
        $courseId = $request->input('course_id');
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$userId !== (int)$course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }

        $this->validate($request, array(
            'title' => 'required|max:255',
            'item_type' => 'required|in:module,chapter,video,assignment,quiz,reading',
            'description' => 'nullable',
            'duration' => 'nullable|integer|min:0',
            'external_url' => 'nullable|url',
            'parent_id' => 'nullable|integer',
        ));

        $data = array(
            'course_id' => $courseId,
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title'),
            'item_type' => $request->input('item_type'),
            'duration' => $request->input('duration'),
            'external_url' => $request->input('external_url'),
            'description' => $request->input('description'),
            'order_index' => $request->input('order_index', 0),
        );

        $courseItem = $this->courseService->createCourseItem($data);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course_item' => $courseItem,
            'msg' => '课程项目创建成功',
        )));
    }

    public function update(Request $request, $courseId, $id)
    {
        $request->merge(array('course_id' => $courseId));
        return $this->updateFromModal($request, $id);
    }

    public function updateFromModal(Request $request, $id)
    {
        $courseItem = $this->courseService->getCourseItemById($id);
        if (!$courseItem) {
            throw new CustomException('课程项目不存在');
        }

        $course = $this->courseService->getCourseById($courseItem->course_id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$userId !== (int)$course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }

        $this->validate($request, array(
            'title' => 'required|max:255',
            'item_type' => 'required|in:module,chapter,video,assignment,quiz,reading',
            'description' => 'nullable',
            'duration' => 'nullable|integer|min:0',
            'external_url' => 'nullable|url',
            'parent_id' => 'nullable|integer',
        ));

        $data = array(
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title'),
            'item_type' => $request->input('item_type'),
            'duration' => $request->input('duration'),
            'external_url' => $request->input('external_url'),
            'description' => $request->input('description'),
            'order_index' => $request->input('order_index', 0),
        );

        $updatedCourseItem = $this->courseService->updateCourseItem($id, $data);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'course_item' => $updatedCourseItem,
            'msg' => '课程项目更新成功',
        )));
    }

    public function destroy(Request $request, $id)
    {
        $courseItem = $this->courseService->getCourseItemById($id);
        if (!$courseItem) {
            throw new CustomException('课程项目不存在');
        }

        $course = $this->courseService->getCourseById($courseItem->course_id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }

        $userId = $this->getAuthUserId($request);
        if ((int)$userId !== (int)$course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }

        $this->courseService->deleteCourseItem($id);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'msg' => '课程项目删除成功',
        )));
    }
}
