<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokenUsageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('token_id')->comment('令牌ID');
            $table->string('api_endpoint', 255)->comment('API端点');
            $table->string('ip_address', 45)->nullable()->comment('IP地址');
            $table->text('user_agent')->nullable()->comment('用户代理');
            $table->timestamps();

            // 索引
            $table->index('token_id');
            
            // 外键约束
            $table->foreign('token_id')->references('id')->on('personal_access_tokens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('token_usage_logs');
    }
}