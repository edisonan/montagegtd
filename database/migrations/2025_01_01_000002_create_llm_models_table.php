<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLlmModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('llm_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('供应商ID');
            $table->string('name', 100)->comment('模型名称，如gpt-4-turbo');
            $table->string('display_name', 100)->nullable()->comment('显示名称');
            $table->string('model_type', 50)->default('chat')->comment('模型类型：chat/completion/embedding/image');
            $table->integer('context_length')->nullable()->comment('上下文长度');
            $table->integer('max_tokens')->nullable()->comment('最大输出tokens');
            $table->decimal('input_price_per_1k', 10, 6)->nullable()->comment('输入价格/1K tokens');
            $table->decimal('output_price_per_1k', 10, 6)->nullable()->comment('输出价格/1K tokens');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->json('capabilities')->nullable()->comment('能力配置：vision, json_mode等');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['provider_id', 'name']);
            $table->index('model_type');
            $table->index('is_active');
            $table->foreign('provider_id')->references('id')->on('llm_providers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('llm_models');
    }
}