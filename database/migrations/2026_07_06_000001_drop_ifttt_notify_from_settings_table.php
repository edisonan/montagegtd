<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropIftttNotifyFromSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('settings', 'ifttt_notify')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('ifttt_notify');
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
        if (!Schema::hasColumn('settings', 'ifttt_notify')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('ifttt_notify', 120)->nullable()->after('cal_token')->comment('ifttt通知token');
            });
        }
    }
}
