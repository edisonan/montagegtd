<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Plan;
use App\Services\PlanService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    protected $planService;
    protected $pointGrantService;

    public function __construct(PlanService $planService, PointGrantService $pointGrantService)
    {
        $this->planService = $planService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $status = (int)$request->input('status', 1);
        if (!in_array($status, array(1, 2), true)) {
            throw new CustomException('状态不合法');
        }

        $plans = $this->planService->getList($status);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'plans' => $plans,
        )));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $this->planService->store($request->input('name'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function show(Request $request, Plan $plan)
    {
        $this->authorize('destroy', $plan);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'plan' => $plan,
        )));
    }

    public function destroy(Request $request, Plan $plan)
    {
        $this->authorize('destroy', $plan);

        $oldStatus = (int)$plan->status;
        $status = $request->input('type') === 'finish' ? 2 : 3;
        $plan->update(array('status' => $status));
        if ($oldStatus !== 2 && (int)$status === 2) {
            try {
                $this->pointGrantService->grantByEvent(
                    (int)$plan->user_id,
                    'plan_completed',
                    'plan',
                    (int)$plan->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on plan completion failed', array(
                    'plan_id' => $plan->id,
                    'user_id' => $plan->user_id,
                    'error' => $e->getMessage(),
                ));
            }
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function update(Request $request, Plan $plan)
    {
        $this->authorize('destroy', $plan);

        $this->validate($request, array(
            'name' => 'required|max:255',
        ));

        $plan->update(array(
            'name' => $request->input('name'),
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'plan' => $plan->fresh(),
        )));
    }
}
