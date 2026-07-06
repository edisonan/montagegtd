<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Setting;
use App\Services\KindleService;
use App\Services\NotificationChannelService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $settingService;
    protected $kindleService;
    protected $notificationChannelService;

    public function __construct(
        SettingService $settingService,
        KindleService $kindleService,
        NotificationChannelService $notificationChannelService
    )
    {
        $this->settingService = $settingService;
        $this->kindleService = $kindleService;
        $this->notificationChannelService = $notificationChannelService;
    }

    public function index(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'setting' => $setting,
            'notification_channels' => $this->notificationChannelService->getSettingsPayload($setting),
        )));
    }

    public function update(Request $request, Setting $setting)
    {
        $this->authorize('destroy', $setting);
        $this->normalizePomoSettingKeys($request, $setting);

        $this->validate($request, array(
            'pomo_config' => 'array',
            'pomo_config.day_goal' => 'integer|min:1',
            'pomo_config.week_goal' => 'integer|min:1',
            'pomo_config.month_goal' => 'integer|min:1',
            'pomo_config.focus_minutes' => 'integer|min:10|max:60',
            'pomo_config.rest_minutes' => 'integer|min:1|max:10',
            'is_start_kindle' => 'integer|min:0|max:1',
            'with_image_push' => 'integer|min:0|max:1',
            'notification_ifttt_status' => 'integer|min:0|max:1',
            'notification_bark_status' => 'integer|min:0|max:1',
            'notification_bark_server_url' => 'nullable|url',
        ));

        if ((int)$request->input('is_start_kindle') === 1) {
            $this->validate($request, array(
                'kindle_email' => 'email',
            ));
        }

        if ($request->has('pomo_config')) {
            $request->merge(array(
                'pomo_config' => Setting::normalizePomoConfig($request->input('pomo_config')),
            ));
        }
        $setting->update($request->all());
        $this->notificationChannelService->saveFromSettingRequest($request, $setting);
        $setting = $setting->fresh();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'setting' => $setting,
            'notification_channels' => $this->notificationChannelService->getSettingsPayload($setting),
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
            $channels = $this->notificationChannelService->getSettingsPayload($setting);
            $key = isset($channels['ifttt']['config']['key'])
                ? trim((string)$channels['ifttt']['config']['key'])
                : '';
        }
        if ($key === '') {
            return $this->jsonResponse($request, array(
                'code' => 1001,
                'msg' => 'IFTTT key 不能为空',
                'result' => array(),
            ));
        }

        $ok = $this->notificationChannelService->testChannel('ifttt', array(
            'key' => $key,
        ));
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

    public function testBark(Request $request)
    {
        $setting = $this->settingService->getSettingInfo(true);
        $this->authorize('destroy', $setting);

        $key = trim((string)$request->input('key', ''));
        $serverUrl = trim((string)$request->input('server_url', 'https://api.day.app'));
        if ($key === '') {
            $channels = $this->notificationChannelService->getSettingsPayload($setting);
            $key = isset($channels['bark']['config']['key']) ? trim((string)$channels['bark']['config']['key']) : '';
            $serverUrl = isset($channels['bark']['config']['server_url']) ? trim((string)$channels['bark']['config']['server_url']) : $serverUrl;
        }
        if ($key === '') {
            return $this->jsonResponse($request, array(
                'code' => 1001,
                'msg' => 'Bark key 不能为空',
                'result' => array(),
            ));
        }

        $ok = $this->notificationChannelService->testChannel('bark', array(
            'key' => $key,
            'server_url' => $serverUrl,
        ));
        if (!$ok) {
            return $this->jsonResponse($request, array(
                'code' => 1002,
                'msg' => 'Bark 通知发送失败',
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
            'pomo_config',
            'kindle_email',
            'is_start_kindle',
            'with_image_push',
            'cal_token',
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'data' => $data,
            'notification_channels' => $this->notificationChannelService->getSettingsPayload($setting),
        )));
    }

    protected function normalizePomoSettingKeys(Request $request, Setting $setting): void
    {
        $config = $setting->getPomoConfigValues();
        $hasConfig = false;
        if ($request->has('pomo_config')) {
            $submitted = $request->input('pomo_config');
            if (!is_array($submitted)) {
                return;
            }
            $config = array_merge($config, $submitted);
            $hasConfig = true;
        }

        $legacyKeys = array(
            'day_pomo_goal' => 'day_goal',
            'week_pomo_goal' => 'week_goal',
            'month_pomo_goal' => 'month_goal',
            'pomo_time' => 'focus_minutes',
            'pomo_rest_time' => 'rest_minutes',
            'week_focus_plan' => 'week_goal',
            'month_focus_plan' => 'month_goal',
        );
        foreach ($legacyKeys as $legacyKey => $configKey) {
            if ($request->has($legacyKey)) {
                $config[$configKey] = $request->input($legacyKey);
                $hasConfig = true;
            }
        }

        if ($hasConfig) {
            $request->merge(array('pomo_config' => $config));
        }
    }
}
