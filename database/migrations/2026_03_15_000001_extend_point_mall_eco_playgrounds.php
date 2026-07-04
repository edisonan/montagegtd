<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendPointMallEcoPlaygrounds extends Migration
{
    public function up()
    {
        if (Schema::hasTable('point_tree_water_logs')) {
            Schema::table('point_tree_water_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('point_tree_water_logs', 'point_cost')) {
                    $table->integer('point_cost')->default(0)->after('water_value');
                }
                if (!Schema::hasColumn('point_tree_water_logs', 'water_tier')) {
                    $table->string('water_tier', 16)->default('basic')->after('point_cost');
                }
            });
        }

        if (!Schema::hasTable('point_pet_instances')) {
            Schema::create('point_pet_instances', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name', 64)->default('我的宠物');
                $table->string('species', 32)->default('cat');
                $table->integer('growth_value')->default(0);
                $table->integer('level')->default(1)->index();
                $table->integer('health')->default(100);
                $table->dateTime('last_fed_at')->nullable();
                $table->string('status', 16)->default('active')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_pet_feed_logs')) {
            Schema::create('point_pet_feed_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('pet_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->integer('feed_value')->default(0);
                $table->integer('point_cost')->default(0);
                $table->string('feed_tier', 16)->default('basic');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_fish_instances')) {
            Schema::create('point_fish_instances', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name', 64)->default('我的鱼');
                $table->string('species', 32)->default('goldfish');
                $table->integer('growth_value')->default(0);
                $table->integer('level')->default(1)->index();
                $table->integer('health')->default(100);
                $table->dateTime('last_fed_at')->nullable();
                $table->string('status', 16)->default('active')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_fish_feed_logs')) {
            Schema::create('point_fish_feed_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('fish_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->integer('feed_value')->default(0);
                $table->integer('point_cost')->default(0);
                $table->string('feed_tier', 16)->default('basic');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('point_fish_feed_logs');
        Schema::dropIfExists('point_fish_instances');
        Schema::dropIfExists('point_pet_feed_logs');
        Schema::dropIfExists('point_pet_instances');

        if (Schema::hasTable('point_tree_water_logs')) {
            Schema::table('point_tree_water_logs', function (Blueprint $table) {
                if (Schema::hasColumn('point_tree_water_logs', 'water_tier')) {
                    $table->dropColumn('water_tier');
                }
                if (Schema::hasColumn('point_tree_water_logs', 'point_cost')) {
                    $table->dropColumn('point_cost');
                }
            });
        }
    }
}
