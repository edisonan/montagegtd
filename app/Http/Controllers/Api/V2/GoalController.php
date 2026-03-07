<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Goal;
use App\Services\GoalService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoalController extends Controller
{
    protected $goalService;
    protected $pointGrantService;

    public function __construct(GoalService $goalService, PointGrantService $pointGrantService)
    {
        $this->goalService = $goalService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $status = (int)$request->input('status', 1);
        if (!in_array($status, array(1, 2), true)) {
            throw new CustomException('状态不合法');
        }

        $goals = $this->goalService->getList($status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'goals' => $goals,
        )));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $this->goalService->store($request->input('name'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function show(Request $request, Goal $goal)
    {
        $this->authorize('destroy', $goal);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'goal' => $goal,
        )));
    }

    public function destroy(Request $request, Goal $goal)
    {
        $this->authorize('destroy', $goal);

        $oldStatus = (int)$goal->status;
        $status = $request->input('type') === 'finish' ? 2 : 3;
        $goal->update(array('status' => $status));
        if ($oldStatus !== 2 && (int)$status === 2) {
            try {
                $this->pointGrantService->grantByEvent(
                    (int)$goal->user_id,
                    'goal_completed',
                    'goal',
                    (int)$goal->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on goal completion failed', array(
                    'goal_id' => $goal->id,
                    'user_id' => $goal->user_id,
                    'error' => $e->getMessage(),
                ));
            }
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function update(Request $request, Goal $goal)
    {
        $this->authorize('destroy', $goal);

        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $goal->update(array(
            'name' => $request->input('name'),
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'goal' => $goal->fresh(),
        )));
    }
}
