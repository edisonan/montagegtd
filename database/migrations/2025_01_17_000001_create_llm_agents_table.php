<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('llm_agents', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->notNull()->comment('创建用户ID');
            $table->string('name', 100)->notNull()->comment('Agent名称');
            $table->text('description')->nullable()->comment('Agent描述');
            $table->string('avatar', 255)->nullable()->comment('头像URL');
            $table->unsignedBigInteger('model_id')->notNull()->comment('默认模型ID');
            $table->text('system_prompt')->notNull()->comment('系统提示词');
            $table->decimal('temperature', 3, 2)->nullable()->comment('温度参数，0-2范围');
            $table->decimal('top_p', 4, 3)->nullable()->comment('top_p参数，0-1范围');
            $table->integer('max_tokens')->nullable()->comment('最大输出tokens');
            $table->integer('context_length')->notNull()->default(4000)->comment('上下文长度');
            $table->text('tools_config')->nullable()->comment('JSON格式工具配置');
            $table->boolean('is_public')->notNull()->default(0)->comment('是否公开：0-私有，1-公开');
            $table->boolean('is_active')->notNull()->default(1)->comment('是否启用：0-禁用，1-启用');
            $table->integer('usage_count')->notNull()->default(0)->comment('使用次数统计');
            $table->integer('favorite_count')->notNull()->default(0)->comment('收藏次数（公开Agent用）');
            $table->timestamp('last_used_at')->nullable()->comment('最后使用时间');
            $table->timestamp('created_at')->notNull()->default('0000-00-00 00:00:00')->comment('创建时间');
            $table->timestamp('updated_at')->notNull()->default('0000-00-00 00:00:00')->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('软删除时间');
            $table->string('builtin_slug', 100)->nullable()->comment('内置Agent标识，如：general-assistant、coding-helper 等，NULL表示用户自定义');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('model_id')->references('id')->on('llm_models')->onDelete('cascade');
            
            $table->index(['user_id', 'is_active', 'deleted_at'], 'idx_user_status'); // 用户有效agent查询
            $table->index(['is_public', 'is_active', 'favorite_count'], 'idx_public_popular'); // 热门公开agent
            $table->index(['model_id', 'is_active'], 'idx_model_active');
            $table->index('usage_count', 'idx_usage'); // 按使用量排序
            $table->index('created_at', 'idx_created_at'); // 按创建时间查询
            $table->index('last_used_at', 'idx_last_used'); // 清理长时间未用agent
            $table->index(['is_public', 'created_at'], 'idx_public_recent'); // 最新公开agent
            $table->index(['user_id', 'is_public', 'deleted_at'], 'idx_user_public');
        });
    }

    public function down()
    {
        Schema::dropIfExists('llm_agents');
    }
};