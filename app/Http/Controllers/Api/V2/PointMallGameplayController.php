<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\PointMallGameplayService;
use Illuminate\Http\Request;

class PointMallGameplayController extends Controller
{
    protected $service;

    public function __construct(PointMallGameplayService $service)
    {
        $this->service = $service;
    }

    public function treeOverview(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $data = $this->service->getTreeOverview($userId);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($data));
    }

    public function treePlant(Request $request)
    {
        $this->validate($request, array(
            'entitlement_id' => 'required|integer|min:1',
            'name' => 'nullable|string|max:64',
        ));
        $userId = (int)$this->getAuthUserId($request);
        try {
            $tree = $this->service->plantTree(
                $userId,
                (int)$request->input('entitlement_id'),
                (string)$request->input('name', '我的树')
            );
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('tree' => $tree)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function treeWater(Request $request, $treeId)
    {
        $userId = (int)$this->getAuthUserId($request);
        try {
            $tree = $this->service->waterTree($userId, (int)$treeId);
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('tree' => $tree)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function treeDecorate(Request $request, $treeId)
    {
        $this->validate($request, array(
            'decoration' => 'required|array',
        ));
        $userId = (int)$this->getAuthUserId($request);
        try {
            $tree = $this->service->decorateTree($userId, (int)$treeId, (array)$request->input('decoration'));
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('tree' => $tree)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function lotteryOverview(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $data = $this->service->getLotteryOverview($userId);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($data));
    }

    public function lotteryDraw(Request $request)
    {
        $this->validate($request, array(
            'pool_id' => 'required|integer|min:1',
            'times' => 'nullable|integer|min:1|max:10',
        ));
        $userId = (int)$this->getAuthUserId($request);
        try {
            $times = (int)$request->input('times', 1);
            $result = $times > 1
                ? $this->service->drawLotteryMany($userId, (int)$request->input('pool_id'), $times)
                : $this->service->drawLottery($userId, (int)$request->input('pool_id'));
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('draw_result' => $result, 'times' => $times)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function busOverview(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $data = $this->service->getBusOverview($userId);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($data));
    }

    public function busBuyLine(Request $request)
    {
        $this->validate($request, array(
            'line_id' => 'required|integer|min:1',
        ));
        $userId = (int)$this->getAuthUserId($request);
        try {
            $item = $this->service->buyBusLine($userId, (int)$request->input('line_id'));
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('owned_line' => $item)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function busStartRun(Request $request)
    {
        $this->validate($request, array(
            'user_line_id' => 'required|integer|min:1',
        ));
        $userId = (int)$this->getAuthUserId($request);
        try {
            $run = $this->service->startBusRun($userId, (int)$request->input('user_line_id'));
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('run' => $run)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function busTickRun(Request $request, $runId)
    {
        $userId = (int)$this->getAuthUserId($request);
        try {
            $run = $this->service->tickBusRun($userId, (int)$runId);
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array('run' => $run)));
        } catch (\Throwable $e) {
            return $this->jsonResponse($request, ResponseDataUtil::genCommonFail($e->getMessage()));
        }
    }

    public function treeLeaderboard(Request $request)
    {
        $limit = (int)$request->input('limit', 20);
        $rows = $this->service->getTreeLeaderboard($limit);
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'leaderboard' => $rows,
        )));
    }
}
