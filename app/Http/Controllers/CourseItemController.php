        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'course_item' => $courseItem
        ]));
    }

    /**
     * 获取课程结构（用于模态框中的下拉列表）
     */
    public function getStructure(Request $request, $courseId)
    {
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        
        // 检查用户是否有权限管理此课程
        if (auth()->id() != $course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }
        
        $courseStructure = $this->courseService->getCourseStructure($courseId);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'result' => $courseStructure
        ]));
    }

    /**
     * 从模态框创建课程项目
     */
    public function storeFromModal(Request $request)
    {
        $courseId = $request->input('course_id');
        
        // 检查用户是否有权限管理此课程
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        
        if (auth()->id() != $course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }

        $this->validate($request, [
            'title' => 'required|max:255',
            'item_type' => 'required|in:module,chapter,video,assignment,quiz,reading',
            'description' => 'nullable',
            'duration' => 'nullable|integer|min:0',
            'external_url' => 'nullable|url',
            'parent_id' => 'nullable|integer'
        ]);

        $data = [
            'course_id' => $courseId,
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title'),
            'item_type' => $request->input('item_type'),
            'duration' => $request->input('duration'),
            'external_url' => $request->input('external_url'),
            'description' => $request->input('description'),
            'order_index' => $request->input('order_index', 0)
        ];

        $courseItem = $this->courseService->createCourseItem($data);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'course_item' => $courseItem,
            'msg' => '课程项目创建成功'
        ]));
    }

    /**
     * 从模态框更新课程项目
     */
    public function updateFromModal(Request $request, $id)
    {
        $courseItem = $this->courseService->getCourseItemById($id);
        if (!$courseItem) {
            throw new CustomException('课程项目不存在');
        }
        
        // 检查用户是否有权限管理此课程
        $course = $this->courseService->getCourseById($courseItem->course_id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        
        if (auth()->id() != $course->created_by) {
            throw new CustomException('您没有权限管理此课程');
        }

        $this->validate($request, [
            'title' => 'required|max:255',
            'item_type' => 'required|in:module,chapter,video,assignment,quiz,reading',
            'description' => 'nullable',
            'duration' => 'nullable|integer|min:0',
            'external_url' => 'nullable|url',
            'parent_id' => 'nullable|integer'
        ]);

        $data = [
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title'),
            'item_type' => $request->input('item_type'),
            'duration' => $request->input('duration'),
            'external_url' => $request->input('external_url'),
            'description' => $request->input('description'),
            'order_index' => $request->input('order_index', 0)
        ];

        $updatedCourseItem = $this->courseService->updateCourseItem($id, $data);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'course_item' => $updatedCourseItem,
            'msg' => '课程项目更新成功'
        ]));
    }

    /**
     * 为模态框获取单个课程项目
     */
    public function showForModal(Request $request, $id)
    {
        $courseItem = $this->courseService->getCourseItemById($id);
        if (!$courseItem) {
            throw new CustomException('课程项目不存在');
        }
        
        // 检查用户是否有权限访问此课程
        $course = $this->courseService->getCourseById($courseItem->course_id);
        if (!$course) {
            throw new CustomException('课程不存在');
        }
        
        if (auth()->id() != $course->created_by) {
            $userCourse = $this->courseService->getUserCourseByUserIdAndCourseId(auth()->id(), $courseItem->course_id);
            if (!$userCourse) {
                throw new CustomException('您没有权限访问此课程内容');
            }
        }
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'course_item' => $courseItem
        ]));
    }
}