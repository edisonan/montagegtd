<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('llm_sessions')) {
            return;
        }

        $modeRow = DB::select('SELECT @@SESSION.sql_mode AS sql_mode');
        $originalMode = isset($modeRow[0]) ? $modeRow[0]->sql_mode : '';
        $migrationMode = implode(',', array_filter(explode(',', $originalMode), function ($mode) {
            return !in_array($mode, ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE'], true);
        }));
        DB::statement('SET SESSION sql_mode = ' . DB::getPdo()->quote($migrationMode));

        try {
            if (!Schema::hasColumn('llm_sessions', 'parent_session_id')) {
                Schema::table('llm_sessions', function (Blueprint $table) {
                    $table->unsignedBigInteger('parent_session_id')->nullable()->after('agent_id');
                    $table->index('parent_session_id', 'idx_llm_session_parent');
                });
            }
            if (!Schema::hasColumn('llm_sessions', 'branched_from_conversation_id')) {
                Schema::table('llm_sessions', function (Blueprint $table) {
                    $table->unsignedBigInteger('branched_from_conversation_id')->nullable()->after('parent_session_id');
                });
            }
            if (!Schema::hasColumn('llm_sessions', 'branch_order')) {
                Schema::table('llm_sessions', function (Blueprint $table) {
                    $table->unsignedInteger('branch_order')->default(0)->after('branched_from_conversation_id');
                });
            }
        } finally {
            DB::statement('SET SESSION sql_mode = ' . DB::getPdo()->quote($originalMode));
        }
    }

    public function down()
    {
        if (!Schema::hasTable('llm_sessions')) {
            return;
        }
        if (Schema::hasColumn('llm_sessions', 'parent_session_id')) {
            Schema::table('llm_sessions', function (Blueprint $table) {
                $table->dropIndex('idx_llm_session_parent');
                $table->dropColumn(['parent_session_id', 'branched_from_conversation_id', 'branch_order']);
            });
        }
    }
};
