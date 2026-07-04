<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDigestProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('user_digest_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('enabled')->default(true)->index();
            $table->json('topics_json')->nullable();
            $table->json('include_keywords_json')->nullable();
            $table->json('exclude_keywords_json')->nullable();
            $table->json('preferred_categories_json')->nullable();
            $table->unsignedTinyInteger('time_window_days')->default(7);
            $table->string('frequency', 16)->default('daily')->index();
            $table->unsignedSmallInteger('max_articles')->default(20);
            $table->string('output_style', 32)->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->index(array('user_id', 'enabled'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_digest_profiles');
    }
}
