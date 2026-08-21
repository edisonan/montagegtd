<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBriefingPagesTable extends Migration
{
    public function up()
    {
        Schema::create('briefing_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('config_id')->index();
            $table->string('title', 255)->nullable();
            // 本次包含主题数量
            $table->unsignedInteger('topic_count')->default(0);
            // 时间窗口（如 2025-08-20 08:00 ~ 10:00）
            $table->string('time_window', 64)->nullable();
            // 模型名
            $table->string('model_name', 100)->nullable();
            $table->timestamp('cover_time_start')->nullable();
            $table->timestamp('cover_time_end')->nullable();
            // 本次主要包含热点内容（JSON 数组）
            $table->json('hot_topics_json')->nullable();
            // 今日趋势（≤5 条）
            $table->json('trends_json')->nullable();
            // 待观察信号
            $table->json('signals_json')->nullable();
            // 标签聚合
            $table->json('tag_aggregation_json')->nullable();
            // 关联的全部文章快照（article ids）
            $table->json('article_ids_json')->nullable();
            $table->string('status', 32)->default('success')->index();
            $table->string('error_message', 255)->nullable();
            $table->timestamp('generated_at')->nullable()->index();
            $table->timestamps();

            $table->index(array('user_id', 'config_id', 'generated_at'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('briefing_pages');
    }
}
