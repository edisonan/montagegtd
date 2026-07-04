<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id'); // 关联courses表
            $table->string('title', 255); // 可自定义标题
            $table->enum('status', ['planned', 'active', 'paused', 'completed', 'dropped'])->default('planned');
            $table->text('goal')->nullable();
            
            // 可见性设置
            $table->boolean('show_progress')->default(true);
            $table->boolean('show_notes')->default(false);
            $table->boolean('show_study_time')->default(false);
            
            // 进度相关
            $table->decimal('progress_percent', 5, 2)->default(0.00); // 缓存进度，避免每次计算
            $table->timestamp('last_activity_at')->nullable(); // 最近学习时间
            
            $table->integer('order_index')->default(0);
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('completed_date')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->unique(['user_id', 'course_id']);
            
            $table->index(['user_id', 'status']);
            $table->index(['course_id', 'progress_percent']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_courses');
    }
}