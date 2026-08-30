<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudyToolRoomsTable extends Migration
{
    /**
     * 学习工具 - 房间表（WebRTC 远程辅导的房间与信令中继）
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_tool_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 16)->unique()->comment('房间号（邀请凭证）');
            $table->string('kind', 32)->default('tutoring')->comment('工具类型，预留扩展');
            $table->unsignedInteger('owner_user_id')->default(0)->index()->comment('房主用户ID');
            $table->tinyInteger('state')->default(0)->comment('0=waiting 1=active 2=closed');
            $table->dateTime('expires_at')->nullable()->comment('过期时间（创建后默认+2h）');
            $table->text('meta')->nullable()->comment('保留字段（如约定内容）');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_tool_rooms');
    }
}