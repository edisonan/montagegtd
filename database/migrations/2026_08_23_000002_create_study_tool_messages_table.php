<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudyToolMessagesTable extends Migration
{
    /**
     * 学习工具 - 房间消息表
     * WebRTC 信令（offer/answer/ice）与白板降级中继消息，以 id 作为轮询游标。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_tool_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('room_id')->default(0)->index();
            $table->unsignedInteger('sender_user_id')->default(0)->comment('发送者用户ID');
            $table->string('peer_id', 64)->default('')->comment('前端会话标识（同账号多开区分）');
            $table->string('type', 32)->default('')->comment('offer/answer/ice/join/leave/wb/wb_clear/wb_undo/content_url/ping/file');
            $table->mediumText('payload')->nullable()->comment('JSON 消息体');
            $table->dateTime('created_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_tool_messages');
    }
}