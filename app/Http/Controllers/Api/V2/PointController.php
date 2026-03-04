<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\PointAccountService;
use App\Services\PointRecordService;
use Illuminate\Http\Request;

class PointController extends Controller
{
    protected $pointAccountService;
    protected $pointRecordService;

    public function __construct(
        PointAccountService $pointAccountService,
        PointRecordService $pointRecordService
    ) {
        $this->pointAccountService = $pointAccountService;
        $this->pointRecordService = $pointRecordService;
    }

    public function index(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $account = $this->pointAccountService->getOrCreateAccount($userId);
        $records = $this->pointRecordService->getUserPointRecords($userId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'account' => $account,
            'records' => $records,
        )));
    }
}

