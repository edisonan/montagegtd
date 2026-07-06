<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MergePomoSettingsIntoPomoConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('settings', 'pomo_config')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('pomo_config')->nullable()->after('user_id')->comment('JSON格式番茄目标及计时配置');
            });
        }

        $legacyColumns = array(
            'day_pomo_goal',
            'week_pomo_goal',
            'month_pomo_goal',
            'pomo_time',
            'pomo_rest_time',
        );
        $hasLegacyColumns = true;
        foreach ($legacyColumns as $column) {
            if (!Schema::hasColumn('settings', $column)) {
                $hasLegacyColumns = false;
                break;
            }
        }

        if ($hasLegacyColumns) {
            DB::table('settings')->orderBy('id')->chunk(100, function ($settings) {
                foreach ($settings as $setting) {
                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(array(
                            'pomo_config' => json_encode(array(
                                'day_goal' => !empty($setting->day_pomo_goal) ? (int)$setting->day_pomo_goal : 8,
                                'week_goal' => !empty($setting->week_pomo_goal) ? (int)$setting->week_pomo_goal : 40,
                                'month_goal' => !empty($setting->month_pomo_goal) ? (int)$setting->month_pomo_goal : 160,
                                'focus_minutes' => !empty($setting->pomo_time) ? (int)$setting->pomo_time : 25,
                                'rest_minutes' => !empty($setting->pomo_rest_time) ? (int)$setting->pomo_rest_time : 5,
                            )),
                        ));
                }
            });

            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn(array(
                    'day_pomo_goal',
                    'week_pomo_goal',
                    'month_pomo_goal',
                    'pomo_time',
                    'pomo_rest_time',
                ));
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('settings', 'day_pomo_goal')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->tinyInteger('day_pomo_goal')->nullable()->default(8)->after('user_id')->comment('日番茄数');
                $table->integer('week_pomo_goal')->nullable()->default(40)->after('day_pomo_goal')->comment('周番茄数');
                $table->integer('month_pomo_goal')->nullable()->default(160)->after('week_pomo_goal')->comment('月番茄数');
                $table->integer('pomo_time')->default(25)->after('month_pomo_goal')->comment('番茄时间');
                $table->integer('pomo_rest_time')->default(5)->after('pomo_time')->comment('番茄休息时间');
            });
        }

        if (Schema::hasColumn('settings', 'pomo_config')) {
            DB::table('settings')->orderBy('id')->chunk(100, function ($settings) {
                foreach ($settings as $setting) {
                    $config = json_decode($setting->pomo_config, true);
                    $config = is_array($config) ? $config : array();
                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(array(
                            'day_pomo_goal' => isset($config['day_goal']) ? (int)$config['day_goal'] : 8,
                            'week_pomo_goal' => isset($config['week_goal']) ? (int)$config['week_goal'] : 40,
                            'month_pomo_goal' => isset($config['month_goal']) ? (int)$config['month_goal'] : 160,
                            'pomo_time' => isset($config['focus_minutes']) ? (int)$config['focus_minutes'] : 25,
                            'pomo_rest_time' => isset($config['rest_minutes']) ? (int)$config['rest_minutes'] : 5,
                        ));
                }
            });

            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('pomo_config');
            });
        }
    }
}
