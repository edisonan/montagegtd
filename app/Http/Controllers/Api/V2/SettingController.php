<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Setting;
use App\Services\KindleService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
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

    public function update(Request $request, Setting $setting)
    {
        $this->authorize('destroy', $setting);
        $this->normalizeLegacySettingKeys($request);

        $this->validate($request, array(
            'day_pomo_goal' => 'integer|min:1',
            'week_pomo_goal' => 'integer|min:1',
            'month_pomo_goal' => 'integer|min:1',
            'pomo_time' => 'integer|min:10|max:60',
            'pomo_rest_time' => 'integer|min:1|max:10',
            'is_start_kindle' => 'integer|min:0|max:1',
            'with_image_push' => 'integer|min:0|max:1',
        ));

        if ((int)$request->input('is_start_kindle') === 1) {
            $this->validate($request, array(
                'kindle_email' => 'email',
            ));
        }

        $setting->update($request->all());

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'setting' => $setting->fresh(),
        )));
    }

    public function updateCurrent(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);
        return $this->update($request, $setting);
    }

    public function testKindle(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);
        $this->authorize('destroy', $setting);

        $email = trim((string)$request->input('email', ''));
        if ($email !== '') {
            $this->validate($request, array(
                'email' => 'email',
            ));
            $setting->kindle_email = $email;
            $setting->save();
        }

        $this->kindleService->test();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'msg' => '测试文章已发送',
        )));
    }

    public function testIfttt(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);
        $this->authorize('destroy', $setting);

        $key = trim((string)$request->input('key', ''));
        if ($key === '') {
            $key = trim((string)$setting->ifttt_notify);
        }
        if ($key === '') {
            return $this->jsonResponse($request, array(
                'code' => 1001,
                'msg' => 'IFTTT key 不能为空',
                'result' => array(),
            ));
        }

        $ok = CommonUtil::iftttNotify('测试通知', 'Montage 设置测试通知', config('app.url'), $key);
        if (!$ok) {
            return $this->jsonResponse($request, array(
                'code' => 1002,
                'msg' => 'IFTTT 通知发送失败',
                'result' => array(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'msg' => '测试通知已发送',
        )));
    }

    public function export(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);
        $this->authorize('destroy', $setting);

        $data = $setting->only(array(
            'day_pomo_goal',
            'week_pomo_goal',
            'month_pomo_goal',
            'pomo_time',
            'pomo_rest_time',
            'kindle_email',
            'is_start_kindle',
            'with_image_push',
            'ifttt_notify',
            'cal_token',
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'data' => $data,
        )));
    }

    protected function normalizeLegacySettingKeys(Request $request): void
    {
        $payload = array();
        if ($request->has('week_focus_plan') && !$request->has('week_pomo_goal')) {
            $payload['week_pomo_goal'] = $request->input('week_focus_plan');
        }
        if ($request->has('month_focus_plan') && !$request->has('month_pomo_goal')) {
            $payload['month_pomo_goal'] = $request->input('month_focus_plan');
        }
        if (!empty($payload)) {
            $request->merge($payload);
        }
    }
}
