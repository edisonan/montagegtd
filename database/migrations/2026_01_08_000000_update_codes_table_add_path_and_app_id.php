<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCodesTableAddPathAndAppId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 检查字段是否已存在，避免重复添加
        if (!Schema::hasColumn('codes', 'path')) {
            Schema::table('codes', function (Blueprint $table) {
                $table->string('path', 500)->after('type')->nullable()->comment('代码文件路径');
            });
        }
        
        if (!Schema::hasColumn('codes', 'app_id')) {
            Schema::table('codes', function (Blueprint $table) {
                $table->unsignedBigInteger('app_id')->after('path')->nullable()->comment('所属应用ID');
                $table->index('app_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->dropIndex(['app_id']); // 删除索引
            $table->dropColumn(['app_id', 'path']);
        });
    }
}