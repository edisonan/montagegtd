<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Services\GoalService;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

/**
 * 目标控制器
 *
 * @author edison.an
 *
 */
class GoalController extends Controller
{

    /**
     * GoalService 实例
     *
     * @var GoalService
     */
    protected $goalService;

    /**
     * 构造方法
     *
     * @param GoalService $goalService
     * @return void
     */
    public function __construct(GoalService $goalService)
    {
        $this->middleware('auth');

        $this->goalService = $goalService;
    }

    /**
     * 首页
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        return view('goals.index');
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

        $this->goalService->store($request->name);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/goals');
    }

    /**
     * 删除目标
     *
     * @param Request $request
     * @param Goal $goal
     */
    public function destroy(Request $request, Goal $goal)
    {
        $this->authorize('destroy', $goal);

        $params = array();

        if ($request->type == 'finish') {
            $params ['status'] = 2;
        } else {
            $params ['status'] = 3;
        }
        $flag = $goal->update($params);

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/goals');
    }

    /**
     * 更新目标
     *
     * @param Request $request
     * @param Goal $goal
     * @return
     *
     */
    public function update(Request $request, Goal $goal)
    {
        $this->authorize('destroy', $goal);

        if ($request->method() == 'GET') {
            return view('goals.update');
        }

        $this->validate($request, [
            'name' => 'required|max:255'
        ]);
        $flag = $goal->update(array(
            'name' => $request->name
        ));

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc(), '/goals');
    }
}
