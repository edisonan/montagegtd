<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\PomoService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    protected $pomoService;

    public function __construct(PomoService $pomoService)
    {
        $this->pomoService = $pomoService;
    }

    /**
     * Dashboard summary data for index page.
     */
    public function show(Request $request)
    {
        $currentPomoInfo = $this->pomoService->getRecentFormatPomo();
        $tipInfo = $this->pomoService->getTipInfo($currentPomoInfo['current_pomo_status']);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array_merge(
            $currentPomoInfo,
            $tipInfo
        )));
    }
}

