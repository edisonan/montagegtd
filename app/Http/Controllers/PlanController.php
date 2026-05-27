<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Services\PlanService;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

/**
 * 目标控制器
 *
 * @author edison.an
 *
 */
class PlanController extends Controller
{

    /**
     * PlanService 实例
     *
     * @var PlanService
     */
    protected $planService;

    /**
     * 构造方法
     *
     * @param PlanService $planService
     * @return void
     */
    public function __construct(PlanService $planService)
    {
        $this->middleware('auth');

        $this->planService = $planService;
    }

    /**
     * 首页
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        return view('plans.index');
    }

    /**
     * 新建目标
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255'
        ]);

        $this->planService->store($request->name);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/plans');
    }

    /**
     * 删除目标
     *
     * @param Request $request
     * @param Plan $plan
     */
    public function destroy(Request $request, Plan $plan)
    {
        $this->authorize('destroy', $plan);

        $params = array();

        if ($request->type == 'finish') {
            $params ['status'] = 2;
        } else {
            $params ['status'] = 3;
        }
        $flag = $plan->update($params);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/plans');
    }

    /**
     * 更新目标
     *
     * @param Request $request
     * @param Plan $plan
     * @return
     *
     */
    public function update(Request $request, Plan $plan)
    {
        $this->authorize('destroy', $plan);

        if ($request->method() == 'GET') {
            return view('plans.update');
        }

        $this->validate($request, [
            'name' => 'required|max:255'
        ]);
        $flag = $plan->update(array(
            'name' => $request->name
        ));

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/plans');
    }
}
