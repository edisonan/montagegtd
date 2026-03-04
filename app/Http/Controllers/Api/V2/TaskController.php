<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Task;
use App\Services\PointGrantService;
use App\Services\TaskService;
use Illuminate\Http\Request;
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

        $tasks = $this->taskService->getTaskListWithPagination($status, $pageSize);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tasks' => $tasks->items(),
            'pagination' => array(
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
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
        $goalId = $request->input('goal_id', null);

        if (!in_array($priority, array(1, 2, 3, 4), true)) {
            throw new CustomException('错误的优先级！');
        }
        if (!empty($remindtime) && strtotime($remindtime) > time()) {
            throw new CustomException('错误的提醒时间！');
        }
        if (!empty($deadline) && strtotime($deadline) > time()) {
            throw new CustomException('错误的截止时间！');
        }

        $task = $this->taskService->store($name, $mode, $priority, $remindtime, $deadline, $parentTaskId, $goalId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($task));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);
        $oldStatus = (int)$task->status;
        $task->update($request->all());
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
