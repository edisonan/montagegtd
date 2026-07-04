<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBgmTracksTable extends Migration
{
    public function up()
    {
        Schema::create('bgm_tracks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 120);
            $table->string('artist', 120)->nullable();
            $table->string('audio_url', 500);
            $table->string('source_url', 500)->nullable();
            $table->string('cover_color', 20)->nullable();
            $table->string('source_type', 32)->default('manual_pixabay')->index();
            $table->string('search_keyword', 80)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->unsignedTinyInteger('is_active')->default(1)->index();
            $table->longText('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bgm_tracks');
    }
}
