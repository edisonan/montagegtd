<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLlmUsageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('llm_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('credential_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('关联用户（如果项目需要）');
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->decimal('cost', 10, 6)->nullable()->comment('本次调用成本');
            $table->decimal('request_time', 8, 3)->nullable()->comment('请求耗时（秒）');
            $table->string('status', 20)->default('success')->comment('success/failed/rate_limited');
            $table->text('error_message')->nullable();
            $table->json('request_data')->nullable()->comment('请求数据（可脱敏存储）');
            $table->json('response_data')->nullable()->comment('响应数据（可脱敏存储）');
            $table->timestamp('created_at')->nullable();
            
            $table->index('provider_id');
            $table->index('model_id');
            $table->index('credential_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->foreign('provider_id')->references('id')->on('llm_providers');
            $table->foreign('model_id')->references('id')->on('llm_models');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('llm_usage_logs');
    }
}