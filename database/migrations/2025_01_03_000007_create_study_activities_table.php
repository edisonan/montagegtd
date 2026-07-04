<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudyActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('course_item_id')->nullable();
            $table->enum('activity_type', ['start_course', 'complete_item', 'add_note', 'rate_item', 'post_discussion', 'complete_course']);
            
            $table->json('metadata')->nullable(); // 扩展数据
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('course_item_id')->references('id')->on('course_items')->onDelete('set null');
            
            $table->index(['user_id', 'course_id', 'created_at']);
            $table->index(['course_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_activities');
    }
}