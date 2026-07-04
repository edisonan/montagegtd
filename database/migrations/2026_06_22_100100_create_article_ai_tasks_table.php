<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleAiTasksTable extends Migration
{
    public function up()
    {
        Schema::create('article_ai_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('article_id')->index();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('error_message', 255)->nullable();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamps();

            $table->index(array('status', 'scheduled_at'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_ai_tasks');
    }
}
