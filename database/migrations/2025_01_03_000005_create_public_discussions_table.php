<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicDiscussionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_discussions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('course_item_id')->nullable(); // 可选：关联具体章节
            $table->unsignedBigInteger('user_id');
            $table->string('title', 255)->nullable();
            $table->longText('content');
            $table->enum('type', ['question', 'note', 'resource', 'tip'])->default('note');
            $table->boolean('is_resolved')->default(false);
            $table->boolean('is_pinned')->default(false);
            
            // 互动数据
            $table->integer('vote_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('reply_count')->default(0);
            
            $table->timestamps();
            
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('course_item_id')->references('id')->on('course_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['course_id', 'course_item_id']);
            $table->index(['course_id', 'vote_count', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public_discussions');
    }
}