<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id'); // 关联courses表
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title', 255);
            $table->enum('item_type', ['module', 'chapter', 'video', 'assignment', 'quiz', 'reading'])->default('chapter');
            $table->integer('duration')->nullable(); // 建议时长（分钟）
            $table->text('external_url')->nullable();
            $table->text('description')->nullable();
            $table->float('order_index')->default(0);
            
            // 统计信息（缓存）
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('avg_study_time')->default(0); // 平均学习时间（秒）
            $table->integer('completion_count')->default(0); // 完成人数
            
            $table->timestamps();
            
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('course_items')->onDelete('cascade');
            
            $table->index(['course_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_items');
    }
}