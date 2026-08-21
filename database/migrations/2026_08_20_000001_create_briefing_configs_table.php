<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBriefingConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('briefing_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name', 120)->default('默认简报');
            $table->boolean('enabled')->default(true)->index();
            // 拉取时间范围（前 x 小时），跨度不能大于 24 小时
            $table->unsignedTinyInteger('pull_hours')->default(6);
            // 定时获取时间（HH:MM，Asia/Shanghai）
            $table->string('schedule_time', 5)->default('08:00');
            // 拉取文章范围：all=全部订阅源，feeds=指定多个订阅源
            $table->string('scope', 16)->default('all')->index();
            // 指定订阅源 ids（scope=feeds 时生效）
            $table->json('feed_ids_json')->nullable();
            // 附加补充内容（人工填写，注入 prompt）
            $table->text('supplement')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->index(array('user_id', 'enabled'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('briefing_configs');
    }
}
