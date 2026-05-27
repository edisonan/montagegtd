<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Plan;
use App\Models\Task;
use App\Services\StudyService;
use Illuminate\Http\Request;

class StudyController extends Controller
{
    protected $studyService;

    public function __construct(StudyService $studyService)
    {
        $this->studyService = $studyService;
    }

    public function overview(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $date = (string)$request->input('date', '');
        $data = $this->studyService->getOverview($userId, $date);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($data));
    }

    public function checkins(Request $request)
    {
        $this->validate($request, array(
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:100',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $result = $this->studyService->listCheckins(
            $userId,
            (string)$request->input('date_from', ''),
            (string)$request->input('date_to', ''),
            (int)$request->input('page', 1),
            (int)$request->input('page_size', 20)
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function createPlan(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|string|max:255',
            'content' => 'nullable|string|max:3000',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'repeat_type' => 'required|in:none,daily,weekly,ebbinghaus',
            'repeat_days' => 'nullable|array',
            'repeat_days.*' => 'in:1,2,3,4,5,6,7',
            'sp_points' => 'nullable|integer|min:0|max:10000',
            'content_mode' => 'nullable|in:fixed,by_repeat',
            'estimated_time_mode' => 'nullable|in:fixed,by_repeat',
            'estimated_minutes' => 'nullable|integer|min:0|max:1440',
            'content_by_slot' => 'nullable|array',
            'content_by_slot.*' => 'nullable|string|max:3000',
            'estimated_by_slot' => 'nullable|array',
            'estimated_by_slot.*' => 'nullable|integer|min:0|max:1440',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $result = $this->studyService->createPlan($userId, $request->all());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function plans(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $result = $this->studyService->listPlans($userId);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'plans' => $result,
        )));
    }

    public function showPlan(Request $request, Plan $plan)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$plan->user_id !== $userId || (string)$plan->plan_type !== 'study') {
            abort(403);
        }
        $result = $this->studyService->getPlanDetail($plan);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function checkin(Request $request, Task $task)
    {
        $this->validate($request, array(
            'date' => 'nullable|date_format:Y-m-d',
            'content' => 'nullable|string|max:5000',
            'audio' => 'nullable|file|mimes:mp3,mpeg,wav,m4a,webm,ogg',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,video/x-m4v|max:51200',
        ));

        $userId = (int)$this->getAuthUserId($request);
        if ((int)$task->user_id !== $userId || (int)$task->mode !== 3) {
            abort(403);
        }

        $result = $this->studyService->checkin(
            $userId,
            (int)$task->id,
            (string)$request->input('date', date('Y-m-d')),
            (string)$request->input('content', ''),
            $request->file('audio'),
            $request->file('image'),
            $request->file('video')
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function focusTask(Request $request, Task $task)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$task->user_id !== $userId || (int)$task->mode !== 3) {
            abort(403);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'task' => $task,
        )));
    }

    public function generate(Request $request)
    {
        $this->validate($request, array(
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $result = $this->studyService->generateTasksForUser(
            $userId,
            (string)$request->input('date_from', ''),
            (string)$request->input('date_to', '')
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    public function generateByPlan(Request $request, Plan $plan)
    {
        $this->validate($request, array(
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
        ));

        $userId = (int)$this->getAuthUserId($request);
        if ((int)$plan->user_id !== $userId || (string)$plan->plan_type !== 'study') {
            abort(403);
        }

        $dateFrom = (string)$request->input('date_from', date('Y-m-d'));
        $dateTo = (string)$request->input('date_to', date('Y-m-d', strtotime('+14 day')));
        $tasks = $this->studyService->generatePlanTasks($plan, $dateFrom, $dateTo);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'plan_id' => (int)$plan->id,
            'generated' => count($tasks),
            'from' => $dateFrom,
            'to' => $dateTo,
        )));
    }

    public function updatePlanStatus(Request $request, Plan $plan)
    {
        $this->validate($request, array(
            'status' => 'required|in:0,1',
        ));

        $userId = (int)$this->getAuthUserId($request);
        if ((int)$plan->user_id !== $userId || (string)$plan->plan_type !== 'study') {
            abort(403);
        }

        $updated = $this->studyService->setPlanStatus($plan, (int)$request->input('status'));
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'plan_id' => (int)$updated->id,
            'status' => (int)$updated->status,
        )));
    }

    public function destroyPlan(Request $request, Plan $plan)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$plan->user_id !== $userId || (string)$plan->plan_type !== 'study') {
            abort(403);
        }

        $result = $this->studyService->deletePlan($plan);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }
}
