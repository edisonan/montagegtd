<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    protected $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
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

        $status = $request->input('type') === 'finish' ? 2 : 3;
        $goal->update(array('status' => $status));

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
