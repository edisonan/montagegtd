<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotifyChannelsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('settings', 'notify_channels')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('notify_channels')->nullable()->after('ifttt_notify')->comment('JSON格式通知渠道配置');
            });
        }

        DB::table('settings')
            ->whereNotNull('ifttt_notify')
            ->where('ifttt_notify', '<>', '')
            ->orderBy('id')
            ->chunk(100, function ($settings) {
                foreach ($settings as $setting) {
                    if (!empty($setting->notify_channels)) {
                        continue;
                    }

                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(array(
                            'notify_channels' => json_encode(array(
                                'ifttt' => array(
                                    'status' => 1,
                                    'config' => array(
                                        'key' => $setting->ifttt_notify,
                                    ),
                                ),
                            )),
                        ));
                }
            });

        if (Schema::hasTable('user_notification_channels')) {
            DB::table('user_notification_channels')
                ->orderBy('id')
                ->chunk(100, function ($channels) {
                    foreach ($channels as $channel) {
                        $setting = DB::table('settings')->where('user_id', $channel->user_id)->first();
                        if (!$setting) {
                            continue;
                        }

                        $storedChannels = json_decode($setting->notify_channels, true);
                        if (!is_array($storedChannels)) {
                            $storedChannels = array();
                        }

                        $storedChannel = array(
                            'status' => (int)$channel->status === 1 ? 1 : 0,
                            'config' => json_decode($channel->config, true),
                        );
                        if (!is_array($storedChannel['config'])) {
                            $storedChannel['config'] = array();
                        }
                        if (!empty($channel->name)) {
                            $storedChannel['name'] = $channel->name;
                        }

                        $storedChannels[$channel->type] = $storedChannel;
                        DB::table('settings')
                            ->where('id', $setting->id)
                            ->update(array(
                                'notify_channels' => json_encode($storedChannels),
                            ));
                    }
                });

            Schema::drop('user_notification_channels');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('settings', 'notify_channels')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('notify_channels');
            });
        }
    }
}
