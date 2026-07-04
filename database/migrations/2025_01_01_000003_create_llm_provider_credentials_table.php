<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLlmProviderCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('llm_provider_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->string('name', 100)->comment('凭据名称，用于区分不同账号');
            $table->text('api_key')->comment('加密存储的API Key');
            $table->json('config')->nullable()->comment('额外配置，如organization_id等');
            $table->boolean('is_default')->default(false)->comment('是否为默认凭据');
            $table->integer('usage_count')->default(0)->comment('使用次数');
            $table->timestamp('last_used_at')->nullable();
            $table->integer('quota_limit')->nullable()->comment('配额限制');
            $table->integer('quota_used')->default(0)->comment('已使用配额');
            $table->timestamp('quota_reset_at')->nullable()->comment('配额重置时间');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('provider_id');
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
        Schema::dropIfExists('llm_provider_credentials');
    }
}