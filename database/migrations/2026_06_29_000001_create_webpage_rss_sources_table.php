<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebpageRssSourcesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('webpage_rss_sources')) {
            return;
        }

        Schema::create('webpage_rss_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('feed_id')->nullable();
            $table->unsignedInteger('category_id');
            $table->string('name', 255);
            $table->string('list_url', 1024);
            $table->string('rss_token', 64)->unique();
            $table->string('item_selector', 512);
            $table->string('title_selector', 512);
            $table->string('url_selector', 512);
            $table->string('published_selector', 512)->nullable();
            $table->string('image_selector', 512)->nullable();
            $table->string('summary_source', 32)->default('list');
            $table->string('summary_selector', 512)->nullable();
            $table->tinyInteger('detail_enabled')->default(0);
            $table->string('detail_summary_selector', 512)->nullable();
            $table->text('content_selector')->nullable();
            $table->text('exclude_selector')->nullable();
            $table->string('author_selector', 512)->nullable();
            $table->integer('max_content_length')->default(12000);
            $table->string('failure_strategy', 32)->default('fallback');
            $table->integer('refresh_interval')->default(60);
            $table->string('dedupe_key', 32)->default('url');
            $table->string('encoding', 32)->default('auto');
            $table->mediumText('last_debug_result')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(array('user_id', 'status'));
            $table->index('feed_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('webpage_rss_sources');
    }
}
