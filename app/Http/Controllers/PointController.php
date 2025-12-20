<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PointAccountService;
use App\Services\PointRecordService;

/**
 * 用户积分控制器
 */
class PointController extends Controller {

    protected $pointAccountService;
    protected $pointRecordService;

    public function __construct(
        PointAccountService $pointAccountService,
        PointRecordService $pointRecordService
    ) {
        $this->middleware('auth');
        $this->pointAccountService = $pointAccountService;
        $this->pointRecordService  = $pointRecordService;
    }

    /**
     * 积分首页
     */
    public function index(Request $request) {
        $userId = \Auth::id();

        $account = $this->pointAccountService->getOrCreateAccount($userId);
        $records = $this->pointRecordService->getUserPointRecords($userId);

        return view('points.index', [
            'account' => $account,
            'records' => $records,
        ]);
    }
}
