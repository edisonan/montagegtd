<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Task;
use App\Services\LlmStructuredTaskService;
use App\Services\PointGrantService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $taskService;
    protected $pointGrantService;
    protected $llmStructuredTaskService;

    public function __construct(
        TaskService $taskService,
        PointGrantService $pointGrantService,
        LlmStructuredTaskService $llmStructuredTaskService
    ) {
        $this->taskService = $taskService;
        $this->pointGrantService = $pointGrantService;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
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
            "user_id" => Auth::id (),
            "search" => trim((string)$request->input('search', ''))
        );

        $tasks = $this->taskService->getTaskListWithPagination($filters, $pageSize);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tasks' => $tasks->items(),
            'pagination' => array(
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => method_exists($tasks, 'total') ? $tasks->total() : null,
                'last_page' => method_exists($tasks, 'lastPage') ? $tasks->lastPage() : null,
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
            'deleted' => (int)$counts['3'],
            'folded' => (int)$counts['4'],
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

    public function subtasks(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);

        $subtasks = $task->childTasks()
            ->with('parentTask:id,name')
            ->orderBy('status', 'asc')
            ->orderBy('is_top', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tasks' => $subtasks,
            'total' => $subtasks->count(),
        )));
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
        if (!empty($remindtime) && strtotime($remindtime) < time()) {
            throw new CustomException('错误的提醒时间！');
        }
        if (!empty($deadline) && strtotime($deadline) < time()) {
            throw new CustomException('错误的截止时间！');
        }

        $task = $this->taskService->store($name, $mode, $priority, $remindtime, $deadline, $parentTaskId, $planId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($task));
    }

    /**
     * AI 智能解析：把一段自然语言解析成结构化待办事项列表。
     * 优先走 LLM 结构化解析；LLM 不可用或解析失败时，按常见分隔符兜底拆分。
     */
    public function aiParse(Request $request)
    {
        $this->validate($request, array(
            'text' => 'required|string|max:4000',
            'mode' => 'nullable|in:1,2',
        ));

        $text = trim((string)$request->input('text'));
        $defaultMode = (int)$request->input('mode', 1);
        if (!in_array($defaultMode, array(1, 2), true)) {
            $defaultMode = 1;
        }

        $now = date('Y-m-d H:i:s');
        $systemPrompt = '你是待办事项解析助手。用户会输入一段自然语言，其中可能包含一个或多个待办事项，以及时间、优先级、置顶等信息。'
            . '请把内容解析成 JSON 数组，每个元素是且仅是一个待办事项对象，字段固定为：'
            . '{"name":"待办名称(必填，简洁明确)",'
            . '"priority":1-4的整数(1=不重要不紧急,2=不重要紧急,3=重要不紧急,4=重要紧急，无法判断时给1),'
            . '"remindtime":"提醒时间，格式Y-m-d H:i:s，没有则为null",'
            . '"deadline":"截止时间，格式Y-m-d H:i:s，没有则为null",'
            . '"mode":1或2(1=工作,2=生活，无法判断时给1),'
            . '"is_top":0或1(是否置顶，默认0)}'
            . ' 当前时间是 ' . $now . '。请把"今天下午3点""明天""下周一""周五下班前"等相对时间换算成具体的绝对时间（格式Y-m-d H:i:s），不要使用相对表达。'
            . ' "紧急""尽快""今天"等表述可适度推断为合理时间，但不要编造原文没有的待办事项。'
            . ' 只能输出 JSON 数组本身（不要 markdown 代码块，不要输出任何其他文字）。';

        $messages = array(
            array('role' => 'system', 'content' => $systemPrompt),
            array('role' => 'user', 'content' => $text),
        );

        $llmResult = $this->llmStructuredTaskService->runTask('task_ai_parse', $messages, array(
            'timeout' => 90,
            'max_tokens' => 2048,
            'response_format' => array('type' => 'json_object'),
        ));

        $items = array();
        $llmUsed = false;
        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $parsed = $this->parseItemsJson($llmResult['content']);
            if (is_array($parsed)) {
                $items = $this->normalizeItems($parsed, $defaultMode);
                $llmUsed = count($items) > 0;
            }
        }

        // 兜底：LLM 未配置/失败或结果无法解析时，按分隔符拆分成简单待办
        if (count($items) === 0) {
            $items = $this->fallbackSplit($text, $defaultMode);
        }

        $items = array_slice($items, 0, 20);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'items' => $items,
            'llm' => $llmUsed,
            'now' => $now,
        )));
    }

    /**
     * 从 LLM 输出中提取 JSON 数组（兼容数组本身、{items:[...]}、{tasks:[...]}、代码块包裹等）。
     */
    protected function parseItemsJson($content)
    {
        $content = trim((string)$content);
        $content = preg_replace('/^```(?:json)?\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if (isset($decoded['items']) && is_array($decoded['items'])) {
                return $decoded['items'];
            }
            if (isset($decoded['tasks']) && is_array($decoded['tasks'])) {
                return $decoded['tasks'];
            }
            return $decoded;
        }

        $jsonStart = strpos($content, '[');
        $jsonEnd = strrpos($content, ']');
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $sub = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($sub, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * 规范化 LLM 解析出的待办条目：过滤空名称、校验枚举、格式化时间。
     */
    protected function normalizeItems(array $items, $defaultMode)
    {
        $result = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string)($item['name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 255) {
                continue;
            }

            $priority = (int)($item['priority'] ?? 1);
            if (!in_array($priority, array(1, 2, 3, 4), true)) {
                $priority = 1;
            }

            $mode = (int)($item['mode'] ?? $defaultMode);
            if (!in_array($mode, array(1, 2), true)) {
                $mode = $defaultMode;
            }

            $result[] = array(
                'name' => $name,
                'priority' => $priority,
                'remindtime' => $this->normalizeDatetime($item['remindtime'] ?? null),
                'deadline' => $this->normalizeDatetime($item['deadline'] ?? null),
                'mode' => $mode,
                'is_top' => (int)($item['is_top'] ?? 0) === 1 ? 1 : 0,
            );
        }

        return $result;
    }

    /**
     * 规范化 LLM 输出的时间字符串为 Y-m-d H:i:s；无效或空值返回 null。
     */
    protected function normalizeDatetime($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === 'null' || $value === 'NULL') {
            return null;
        }

        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * LLM 不可用时的兜底解析：按换行、顿号、分号等枚举性分隔符拆成简单待办项。
     * 注意不按中文逗号/英文逗号拆分，避免把「开会，重要紧急」这类描述性片段拆成多个任务。
     */
    protected function fallbackSplit($text, $mode)
    {
        $rawLines = preg_split('/[\r\n]+/', $text);
        $items = array();

        foreach ($rawLines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/[、；;]+/u', $line);
            foreach ($parts as $part) {
                $name = trim($part);
                // 去掉常见序号前缀：1. 1、 1) - • 等
                $name = preg_replace('/^\s*(?:\d+[\.、）)]|[-\*•·])\s*/u', '', $name);
                $name = trim($name);
                if ($name === '') {
                    continue;
                }

                $items[] = array(
                    'name' => mb_substr($name, 0, 255),
                    'priority' => 1,
                    'remindtime' => null,
                    'deadline' => null,
                    'mode' => $mode,
                    'is_top' => 0,
                );
            }
        }

        return $items;
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);
        $this->validate($request, array(
            'is_doing' => 'nullable|in:0,1',
            'status' => 'nullable|in:1,2,3,4',
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

        if ((int)$request->input('status', 0) === 2 && (int)$task->status !== 2) {
            $this->taskService->assertTaskCanComplete($task);
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
