<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuizGenerationErrorToCourseItems extends Migration
{
    public function up()
    {
        // 记录最近一次「AI 生成测试」的失败理由，成功生成或手动保存测验时清空
        if (!Schema::hasColumn('course_items', 'quiz_generation_error')) {
            Schema::table('course_items', function (Blueprint $table) {
                $table->text('quiz_generation_error')->nullable()->after('content_status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('course_items', 'quiz_generation_error')) {
            Schema::table('course_items', function (Blueprint $table) {
                $table->dropColumn('quiz_generation_error');
            });
        }
    }
}