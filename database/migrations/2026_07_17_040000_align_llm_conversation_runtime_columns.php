<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('llm_conversations')) {
            return;
        }

        if (!Schema::hasColumn('llm_conversations', 'prompt_tokens')) {
            Schema::table('llm_conversations', function (Blueprint $table) {
                $table->integer('prompt_tokens')->default(0);
            });
        }

        if (!Schema::hasColumn('llm_conversations', 'completion_tokens')) {
            Schema::table('llm_conversations', function (Blueprint $table) {
                $table->integer('completion_tokens')->default(0);
            });
        }

        if (!Schema::hasColumn('llm_conversations', 'total_tokens')) {
            Schema::table('llm_conversations', function (Blueprint $table) {
                $table->integer('total_tokens')->default(0);
            });
        }

        if (!Schema::hasColumn('llm_conversations', 'answered_at')) {
            Schema::table('llm_conversations', function (Blueprint $table) {
                $table->timestamp('answered_at')->nullable();
            });
        }

        if (Schema::hasColumn('llm_conversations', 'input_tokens')) {
            DB::table('llm_conversations')->update([
                'prompt_tokens' => DB::raw('COALESCE(input_tokens, 0)'),
            ]);
        }

        if (Schema::hasColumn('llm_conversations', 'output_tokens')) {
            DB::table('llm_conversations')->update([
                'completion_tokens' => DB::raw('COALESCE(output_tokens, 0)'),
            ]);
        }

        DB::table('llm_conversations')->update([
            'total_tokens' => DB::raw('COALESCE(prompt_tokens, 0) + COALESCE(completion_tokens, 0)'),
        ]);
    }

    public function down()
    {
        // Compatibility migration: keep runtime columns to avoid data loss.
    }
};
