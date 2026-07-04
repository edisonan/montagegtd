<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToLlmTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 为llm_providers表添加user_id字段
        Schema::table('llm_providers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->comment('关联用户（如果项目需要）');
            $table->index('user_id');
        });

        // 为llm_models表添加user_id字段
        Schema::table('llm_models', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->comment('关联用户（如果项目需要）');
            $table->index('user_id');
        });

        // 为llm_provider_credentials表添加user_id字段
        Schema::table('llm_provider_credentials', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->comment('关联用户（如果项目需要）');
            $table->index('user_id');
        });

        // 为llm_usage_logs表添加user_id字段（如果不存在）
        Schema::table('llm_usage_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('llm_usage_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->comment('关联用户（如果项目需要）');
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('llm_providers', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('llm_models', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('llm_provider_credentials', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('llm_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}