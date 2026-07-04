<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleAiRendersTable extends Migration
{
    public function up()
    {
        Schema::create('article_ai_renders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('article_id')->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->string('render_mode', 32)->default('reader_card')->index();
            $table->string('template_style', 32)->nullable();
            $table->text('summary')->nullable();
            $table->longText('outline_json')->nullable();
            $table->longText('html_content')->nullable();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamp('generated_at')->nullable()->index();
            $table->string('error_message', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_ai_renders');
    }
}
