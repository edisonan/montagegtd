<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigestPagesTable extends Migration
{
    public function up()
    {
        Schema::create('digest_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('profile_id')->index();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->string('title', 255)->nullable();
            $table->timestamp('cover_time_start')->nullable();
            $table->timestamp('cover_time_end')->nullable();
            $table->text('intro')->nullable();
            $table->longText('content_markdown')->nullable();
            $table->json('source_article_ids_json')->nullable();
            $table->string('status', 32)->default('success')->index();
            $table->timestamp('generated_at')->nullable()->index();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('digest_pages');
    }
}
