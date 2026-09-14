<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointBusCollectionTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('point_bus_catalog')) {
            Schema::create('point_bus_catalog', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 64)->unique();
                $table->string('name', 128);
                $table->string('type', 24)->default('bus')->index();
                $table->string('rarity', 8)->default('N')->index();
                $table->string('color', 16)->default('#16a34a');
                $table->string('district', 64)->default('北京');
                $table->integer('price_ap')->default(60);
                $table->integer('reward_ap')->default(20);
                $table->decimal('distance_km', 6, 1)->default(0);
                $table->string('first_bus', 16)->nullable();
                $table->string('last_bus', 16)->nullable();
                $table->integer('station_count')->default(0);
                $table->longText('stations')->nullable();
                $table->text('description')->nullable();
                $table->tinyInteger('is_free')->default(0);
                $table->integer('sort_order')->default(0)->index();
                $table->tinyInteger('status')->default(1)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_bus_user_collections')) {
            Schema::create('point_bus_user_collections', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('catalog_id')->index();
                $table->string('status', 16)->default('active')->index();
                $table->integer('progress')->default(0);
                $table->integer('score')->default(0);
                $table->dateTime('unlocked_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('last_checkin_at')->nullable();
                $table->timestamps();
                $table->unique(array('user_id', 'catalog_id'), 'uniq_point_bus_user_collection');
            });
        }

        if (!Schema::hasTable('point_bus_checkin_logs')) {
            Schema::create('point_bus_checkin_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('catalog_id')->index();
                $table->integer('station_index')->default(0);
                $table->string('station_name', 128)->nullable();
                $table->integer('ap_cost')->default(0);
                $table->integer('reward_ap')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_bus_achievements')) {
            Schema::create('point_bus_achievements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 64)->unique();
                $table->string('name', 128);
                $table->text('description')->nullable();
                $table->string('condition_type', 32)->default('collect_count');
                $table->text('condition_payload')->nullable();
                $table->integer('reward_ap')->default(20);
                $table->string('badge_icon', 32)->default('fa-medal');
                $table->integer('sort_order')->default(0)->index();
                $table->tinyInteger('status')->default(1)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_bus_user_achievements')) {
            Schema::create('point_bus_user_achievements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('achievement_code', 64)->index();
                $table->tinyInteger('claimed')->default(0)->index();
                $table->dateTime('unlocked_at')->nullable();
                $table->dateTime('claimed_at')->nullable();
                $table->timestamps();
                $table->unique(array('user_id', 'achievement_code'), 'uniq_point_bus_user_achievement');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('point_bus_user_achievements');
        Schema::dropIfExists('point_bus_achievements');
        Schema::dropIfExists('point_bus_checkin_logs');
        Schema::dropIfExists('point_bus_user_collections');
        Schema::dropIfExists('point_bus_catalog');
    }
}
