<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\FocusService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    protected $focusService;

    public function __construct(FocusService $focusService)
    {
        $this->focusService = $focusService;
    }

    /**
     * Dashboard summary data for index page.
     */
    public function show(Request $request)
    {
        $currentFocusInfo = $this->focusService->getRecentFormatFocus();
        $tipInfo = $this->focusService->getTipInfo($currentFocusInfo['current_focus_status']);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array_merge(
            $currentFocusInfo,
            $tipInfo
        )));
    }
}

