<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\KindleService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class KindleController extends Controller
{
    protected $settingService;
    protected $kindleService;

    public function __construct(SettingService $settingService, KindleService $kindleService)
    {
        $this->settingService = $settingService;
        $this->kindleService = $kindleService;
    }

    public function index(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'setting' => $setting,
        )));
    }

    public function test(Request $request)
    {
        $this->kindleService->test();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'msg' => 'success',
        )));
    }
}
