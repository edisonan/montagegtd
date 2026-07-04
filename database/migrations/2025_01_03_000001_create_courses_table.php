<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('创建者用户ID');
            $table->string('title', 255)->comment('课程标题');
            $table->string('platform', 100)->nullable()->comment('平台：Coursera, Udemy, B站, YouTube等');
            $table->string('instructor', 100)->nullable()->comment('讲师');
            $table->text('public_url')->nullable()->comment('公开链接');
            $table->text('description')->nullable()->comment('描述');
            $table->text('cover_image_url')->nullable()->comment('封面图片URL');
            $table->boolean('is_public')->default(true)->comment('是否公开可见');
            $table->unsignedBigInteger('created_by')->comment('创建者用户ID');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner')->comment('难度级别');
            $table->integer('estimated_hours')->nullable()->comment('预计学习时长（小时）');
            $table->json('tags')->nullable()->comment('标签数组');
            
            $table->timestamps();
            
            $table->index('is_public');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}