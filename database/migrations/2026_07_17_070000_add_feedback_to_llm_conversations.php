<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('llm_conversations') || Schema::hasColumn('llm_conversations', 'feedback')) {
            return;
        }
        $modeRow = DB::select('SELECT @@SESSION.sql_mode AS sql_mode');
        $originalMode = isset($modeRow[0]) ? $modeRow[0]->sql_mode : '';
        $migrationMode = implode(',', array_filter(explode(',', $originalMode), function ($mode) {
            return !in_array($mode, ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE'], true);
        }));
        DB::statement('SET SESSION sql_mode = ' . DB::getPdo()->quote($migrationMode));
        try {
            Schema::table('llm_conversations', function (Blueprint $table) {
                $table->smallInteger('feedback')->nullable()->after('answer');
            });
        } finally {
            DB::statement('SET SESSION sql_mode = ' . DB::getPdo()->quote($originalMode));
        }
    }

    public function down()
    {
        if (Schema::hasColumn('llm_conversations', 'feedback')) {
            Schema::table('llm_conversations', function (Blueprint $table) {
                $table->dropColumn('feedback');
            });
        }
    }
};
