<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_course_id');
            $table->unsignedBigInteger('course_item_id'); // 关联course_items
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('time_spent')->default(0); // 累计学习时长（秒）
            $table->tinyInteger('rating')->nullable(); // 1-5星评分
            
            // 笔记（私有）
            $table->text('notes')->nullable();
            $table->timestamp('note_updated_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_course_id')->references('id')->on('user_courses')->onDelete('cascade');
            $table->foreign('course_item_id')->references('id')->on('course_items')->onDelete('cascade');
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
        Schema::dropIfExists('user_progress');
    }
}