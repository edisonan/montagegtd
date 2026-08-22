<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 幂等补齐 user_progress 表。
 * 历史迁移 2025_01_03_000004 虽然被记录为已执行，但实际表未建成
 * （模型 UserProgress 默认查 user_progresses 复数表名也属既有缺陷，
 *  已通过模型指定 protected $table = 'user_progress' 修复）。
 * 此处合并 2025_01_03_000004 与 2026_07_17_010000 的列定义，保证字段齐全。
 */
class EnsureUserProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_progress')) {
            return;
        }

        Schema::create('user_progress', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_course_id')->comment('关联 course_enrollments.id');
            $table->unsignedInteger('course_item_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->string('mastery_status', 20)->nullable(); // not_started/completed/mastered/reviewing
            $table->decimal('mastery_score', 5, 2)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('review_due_at')->nullable();
            $table->integer('time_spent')->default(0);
            $table->tinyInteger('rating')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('note_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'user_course_id', 'course_item_id']);
            $table->index(['user_id', 'user_course_id', 'status']);
            $table->index(['course_item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 幂等补表迁移，不执行删除
    }
}