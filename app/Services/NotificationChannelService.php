<?php

namespace App\Services;

use App\Http\Utils\CommonUtil;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationChannelService
{
    const TYPE_IFTTT = 'ifttt';
    const TYPE_BARK = 'bark';

    public function getSettingsPayload(Setting $setting)
    {
        $payload = array(
            self::TYPE_IFTTT => array(
                'type' => self::TYPE_IFTTT,
                'name' => 'IFTTT',
                'status' => 0,
                'config' => array(
                    'key' => '',
                ),
            ),
            self::TYPE_BARK => array(
                'type' => self::TYPE_BARK,
                'name' => 'Bark',
                'status' => 0,
                'config' => array(
                    'server_url' => 'https://api.day.app',
                    'key' => '',
                ),
            ),
        );

        $channels = $this->getStoredChannels($setting);
        foreach ($channels as $type => $channel) {
            if (!is_array($channel)) {
                continue;
            }

            if (!isset($payload[$type])) {
                $payload[$type] = array(
                    'type' => $type,
                    'name' => isset($channel['name']) ? $channel['name'] : $type,
                    'status' => isset($channel['status']) ? (int)$channel['status'] : 0,
                    'config' => isset($channel['config']) && is_array($channel['config'])
                        ? $channel['config']
                        : array(),
                );
                continue;
            }

            $payload[$type]['name'] = isset($channel['name']) && $channel['name'] !== ''
                ? $channel['name']
                : $payload[$type]['name'];
            $payload[$type]['status'] = isset($channel['status']) ? (int)$channel['status'] : 0;
            $config = isset($channel['config']) && is_array($channel['config'])
                ? $channel['config']
                : array();
            $payload[$type]['config'] = array_merge($payload[$type]['config'], $config);
        }

        return $payload;
    }

    public function saveFromSettingRequest(Request $request, Setting $setting)
    {
        $this->saveIftttFromRequest($request, $setting);
        $this->saveBarkFromRequest($request, $setting);
    }

    public function sendToUser(User $user, $title, $message, $url)
    {
        $results = array();
        if (!$user->setting) {
            return $results;
        }

        $channels = $this->getStoredChannels($user->setting);
        foreach ($channels as $type => $channel) {
            if (!is_array($channel) || (int)(isset($channel['status']) ? $channel['status'] : 0) !== 1) {
                continue;
            }

            $config = isset($channel['config']) && is_array($channel['config'])
                ? $channel['config']
                : array();
            $results[] = $this->sendByChannel($type, $config, $title, $message, $url, $user->id);
        }

        return $results;
    }

    public function testChannel($type, array $config)
    {
        $title = '测试通知';
        $message = 'Montage 设置测试通知';
        $url = config('app.url');

        if ($type === self::TYPE_IFTTT) {
            $key = isset($config['key']) ? trim((string)$config['key']) : '';
            return $key !== '' && CommonUtil::iftttNotify($title, $message, $url, $key);
        }

        if ($type === self::TYPE_BARK) {
            $key = isset($config['key']) ? trim((string)$config['key']) : '';
            $serverUrl = isset($config['server_url']) ? trim((string)$config['server_url']) : '';
            return $key !== '' && CommonUtil::barkNotify($title, $message, $url, $key, $serverUrl);
        }

        return false;
    }

    protected function saveIftttFromRequest(Request $request, Setting $setting)
    {
        if (!$request->has('notification_ifttt_status') && !$request->has('notification_ifttt_key')) {
            return;
        }

        $channels = $this->getStoredChannels($setting);
        $currentKey = isset($channels[self::TYPE_IFTTT]['config']['key'])
            ? $channels[self::TYPE_IFTTT]['config']['key']
            : '';
        $key = trim((string)$request->input('notification_ifttt_key', $currentKey));
        $status = (int)$request->input('notification_ifttt_status', $key !== '' ? 1 : 0);

        $this->saveChannel($setting, self::TYPE_IFTTT, $status, array(
            'key' => $key,
        ));
    }

    protected function saveBarkFromRequest(Request $request, Setting $setting)
    {
        if (!$request->has('notification_bark_status') && !$request->has('notification_bark_key')) {
            return;
        }

        $channels = $this->getStoredChannels($setting);
        $currentConfig = isset($channels[self::TYPE_BARK]['config'])
            && is_array($channels[self::TYPE_BARK]['config'])
            ? $channels[self::TYPE_BARK]['config']
            : array();
        $key = trim((string)$request->input(
            'notification_bark_key',
            isset($currentConfig['key']) ? $currentConfig['key'] : ''
        ));
        $serverUrl = trim((string)$request->input(
            'notification_bark_server_url',
            isset($currentConfig['server_url']) ? $currentConfig['server_url'] : 'https://api.day.app'
        ));
        $status = (int)$request->input('notification_bark_status', $key !== '' ? 1 : 0);

        $this->saveChannel($setting, self::TYPE_BARK, $status, array(
            'server_url' => $serverUrl ?: 'https://api.day.app',
            'key' => $key,
        ));
    }

    protected function saveChannel(Setting $setting, $type, $status, array $config)
    {
        $channels = $this->getStoredChannels($setting);
        $channels[$type] = array(
            'status' => (int)$status === 1 ? 1 : 0,
            'config' => $config,
        );
        $setting->notify_channels = $channels;
        $setting->save();
    }

    protected function getStoredChannels(Setting $setting = null)
    {
        if (!$setting || !is_array($setting->notify_channels)) {
            return array();
        }

        return $setting->notify_channels;
    }

    protected function sendByChannel($type, array $config, $title, $message, $url, $userId)
    {
        $ok = false;

        if ($type === self::TYPE_IFTTT) {
            $key = isset($config['key']) ? trim((string)$config['key']) : '';
            $ok = $key !== '' && CommonUtil::iftttNotify($title, $message, $url, $key);
        } elseif ($type === self::TYPE_BARK) {
            $key = isset($config['key']) ? trim((string)$config['key']) : '';
            $serverUrl = isset($config['server_url']) ? trim((string)$config['server_url']) : '';
            $ok = $key !== '' && CommonUtil::barkNotify($title, $message, $url, $key, $serverUrl);
        }

        if (!$ok) {
            Log::info('notification channel send failed:' . $type . '|user:' . $userId);
        }

        return array(
            'type' => $type,
            'ok' => $ok,
        );
    }
}
