<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBriefingV2PagesTable extends Migration
{
    /**
     * 文章简报 v2（对比版）结果表
     *
     * 与 v1 的 briefing_pages 结构对齐，差异：
     * - candidate_count：本次喂给 LLM 的候选文章数（用于对比透明度）
     * - fallback：是否走了兜底路径（0=真实 LLM 结构化输出，1=兜底）
     * - error_message：真实失败原因（v1 恒为 null）
     * 生产库为 MariaDB 5.5，无原生 json 类型，JSON 列统一用 text（与 v1 一致）。
     */
    public function up()
    {
        Schema::create('briefing_v2_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('config_id')->index();
            $table->string('title', 255)->nullable();
            // 本期展示的主题数量（真实热点数，非候选数）
            $table->unsignedInteger('topic_count')->default(0);
            // 本次喂给 LLM 的候选文章数
            $table->unsignedInteger('candidate_count')->default(0);
            $table->string('time_window', 64)->nullable();
            $table->string('model_name', 100)->nullable();
            $table->timestamp('cover_time_start')->nullable();
            $table->timestamp('cover_time_end')->nullable();
            $table->text('hot_topics_json')->nullable();
            $table->text('trends_json')->nullable();
            $table->text('signals_json')->nullable();
            $table->text('tag_aggregation_json')->nullable();
            $table->text('article_ids_json')->nullable();
            $table->string('status', 32)->default('success')->index();
            // 1=兜底路径（LLM 失败后降级）
            $table->unsignedTinyInteger('fallback')->default(0)->index();
            $table->string('error_message', 255)->nullable();
            $table->timestamp('generated_at')->nullable()->index();
            $table->timestamps();

            $table->index(array('user_id', 'config_id', 'generated_at'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('briefing_v2_pages');
    }
}