<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Task;
use App\Services\PointGrantService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $taskService;
    protected $pointGrantService;

    public function __construct(TaskService $taskService, PointGrantService $pointGrantService)
    {
        $this->taskService = $taskService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $status = $request->input('status', '');
        $pageSize = (int)$request->input('page_count', 20);
        if ($pageSize <= 0) {
            $pageSize = 20;
        }

        $filters = array(
            "status" => $status,
            "user_id" => Auth::id ()
        );

        $tasks = $this->taskService->getTaskListWithPagination($filters, $pageSize);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tasks' => $tasks->items(),
            'pagination' => array(
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => null,
                'last_page' => null,
                'next_page_url' => $tasks->nextPageUrl(),
                'prev_page_url' => $tasks->previousPageUrl(),
                'has_more_pages' => $tasks->hasMorePages(),
            ),
        )));
    }

    public function getAllList(Request $request)
    {
        $status = $request->input('status', 1);
        $mode = $request->input('mode', 1);
        $formatTasks = $this->taskService->getAllList($status, $mode);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($formatTasks));
    }

    public function tabCounts(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return response()->json(array(
                'code' => 9998,
                'msg' => '用户未认证',
                'result' => array(),
            ));
        }

        $counts = $this->taskService->getStatusCounts($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'active' => (int)$counts['1'],
            'completed' => (int)$counts['2'],
            'folded' => (int)$counts['3'],
            'total' => (int)$counts['all'],
        )));
    }

    public function priority(Request $request)
    {
        $status = $request->input('status', 1);
        $mode = $request->input('mode', 1);
        $tasks = $this->taskService->getPriorityList($status, $mode);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tasks' => $tasks,
        )));
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($task));
    }

    public function getParentTasks(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return response()->json(array(
                'code' => 9998,
                'msg' => '用户未认证',
                'result' => array(),
            ));
        }

        $excludeTaskId = $request->input('exclude_task_id');
        $parentTasks = $this->taskService->getUserParentTasks($userId, $excludeTaskId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($parentTasks));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
            'mode' => 'required',
            'remindtime' => 'nullable|date_format:Y-m-d H:i:s',
            'deadline' => 'nullable|date_format:Y-m-d H:i:s',
        ));

        $name = $request->input('name');
        $mode = $request->input('mode');
        $priority = (int)$request->input('priority', 1);
        $remindtime = $request->input('remindtime', null);
        $deadline = $request->input('deadline', null);
        $parentTaskId = $request->input('parent_task_id', null);
        $planId = $request->input('plan_id', null);

        if (!in_array($priority, array(1, 2, 3, 4), true)) {
            throw new CustomException('错误的优先级！');
        }
        if (!empty($remindtime) && strtotime($remindtime) > time()) {
            throw new CustomException('错误的提醒时间！');
        }
        if (!empty($deadline) && strtotime($deadline) > time()) {
            throw new CustomException('错误的截止时间！');
        }

        $task = $this->taskService->store($name, $mode, $priority, $remindtime, $deadline, $parentTaskId, $planId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($task));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);
        $this->validate($request, array(
            'is_doing' => 'nullable|in:0,1',
            'status' => 'nullable|in:1,2,3',
            'planned_start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'planned_end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'remindtime' => 'nullable|date_format:Y-m-d H:i:s',
            'deadline' => 'nullable|date_format:Y-m-d H:i:s',
            'rating' => 'nullable|integer|min:1|max:5',
            'review_note' => 'nullable|string|max:2000',
        ));

        if ($request->has('is_doing') && (int)$request->input('is_doing') === 1 && (int)$task->status !== 1) {
            throw new CustomException('仅进行中的任务可设置为正在做');
        }

        $plannedStartTime = $request->input('planned_start_time');
        $plannedEndTime = $request->input('planned_end_time');
        if (!empty($plannedStartTime) && !empty($plannedEndTime) && strtotime($plannedStartTime) > strtotime($plannedEndTime)) {
            throw new CustomException('预计开始时间不能晚于预计结束时间');
        }

        $payload = $request->all();
        if (isset($payload['status']) && (int)$payload['status'] !== 1) {
            $payload['is_doing'] = 0;
        }

        $oldStatus = (int)$task->status;
        $task->update($payload);
        $freshTask = $task->fresh();

        if ($oldStatus !== 2 && (int)$freshTask->status === 2) {
            try {
                $this->pointGrantService->grantByEvent(
                    (int)$freshTask->user_id,
                    'task_completed',
                    'task',
                    (int)$freshTask->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on task completion failed', array(
                    'task_id' => $freshTask->id,
                    'user_id' => $freshTask->user_id,
                    'error' => $e->getMessage(),
                ));
            }
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($freshTask));
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);

        $this->taskService->updateTaskByType($task, $request->input('type', ''));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
