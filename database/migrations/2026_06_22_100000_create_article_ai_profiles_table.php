<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleAiProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('article_ai_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('article_id')->unique();
            $table->string('status', 32)->default('success')->index();
            $table->string('primary_category', 64)->nullable()->index();
            $table->string('secondary_category', 64)->nullable();
            $table->json('tags_json')->nullable();
            $table->json('keywords_json')->nullable();
            $table->text('summary')->nullable();
            $table->string('content_type', 32)->nullable();
            $table->string('audience', 32)->nullable();
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->json('risk_flags_json')->nullable();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamp('analyzed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_ai_profiles');
    }
}
