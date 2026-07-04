<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLlmProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('llm_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('供应商名称，如OpenAI、Anthropic等');
            $table->string('slug', 50)->unique()->comment('唯一标识符，如openai、anthropic');
            $table->text('description')->nullable()->comment('供应商描述');
            $table->string('base_url', 255)->nullable()->comment('API基础URL');
            $table->string('api_type', 50)->comment('API类型：openai、anthropic、custom');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->integer('priority')->default(0)->comment('优先级，数字越大优先级越高');
            $table->json('config_schema')->nullable()->comment('配置项JSON Schema');
            $table->integer('rate_limit_per_minute')->nullable()->comment('每分钟请求限制');
            $table->integer('concurrent_limit')->default(10)->comment('并发限制');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('llm_providers');
    }
}