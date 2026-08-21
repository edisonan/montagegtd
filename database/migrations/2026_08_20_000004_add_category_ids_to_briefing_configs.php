<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdsToBriefingConfigs extends Migration
{
    public function up()
    {
        if (Schema::hasTable('briefing_configs') && !Schema::hasColumn('briefing_configs', 'category_ids_json')) {
            // 生产库为 MariaDB 5.5，无原生 json 类型，统一用 text（与 feed_ids_json 约定一致）
            Schema::table('briefing_configs', function (Blueprint $table) {
                $table->text('category_ids_json')->nullable()->after('feed_ids_json');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('briefing_configs') && Schema::hasColumn('briefing_configs', 'category_ids_json')) {
            Schema::table('briefing_configs', function (Blueprint $table) {
                $table->dropColumn('category_ids_json');
            });
        }
    }
}
