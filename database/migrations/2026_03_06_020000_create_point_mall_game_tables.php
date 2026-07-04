<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointMallGameTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('point_tree_instances')) {
            Schema::create('point_tree_instances', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name', 64)->default('我的树');
                $table->string('species', 32)->default('oak');
                $table->integer('growth_value')->default(0);
                $table->string('stage', 16)->default('sapling')->index();
                $table->integer('health')->default(100);
                $table->dateTime('last_watered_at')->nullable();
                $table->text('decoration_payload')->nullable();
                $table->string('status', 16)->default('alive')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_tree_water_logs')) {
            Schema::create('point_tree_water_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tree_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->integer('water_value')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_lottery_pools')) {
            Schema::create('point_lottery_pools', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 64)->unique();
                $table->string('name', 128);
                $table->string('scene', 32)->default('default')->index();
                $table->integer('cost_ap')->default(10);
                $table->tinyInteger('status')->default(1)->index();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_lottery_pool_items')) {
            Schema::create('point_lottery_pool_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('pool_id')->index();
                $table->string('reward_type', 32)->default('entitlement');
                $table->string('reward_name', 128);
                $table->text('reward_payload')->nullable();
                $table->integer('weight')->default(1);
                $table->integer('stock')->default(-1);
                $table->tinyInteger('status')->default(1)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_lottery_draw_logs')) {
            Schema::create('point_lottery_draw_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('pool_id')->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->integer('cost_ap')->default(0);
                $table->text('result_payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_bus_lines')) {
            Schema::create('point_bus_lines', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 64)->unique();
                $table->string('name', 128);
                $table->string('color', 16)->default('#16a34a');
                $table->integer('price_ap')->default(60);
                $table->text('path_payload')->nullable();
                $table->tinyInteger('status')->default(1)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_user_bus_lines')) {
            Schema::create('point_user_bus_lines', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('line_id')->index();
                $table->string('status', 16)->default('active')->index();
                $table->dateTime('bought_at')->nullable();
                $table->timestamps();
                $table->unique(array('user_id', 'line_id'), 'uniq_point_user_bus_line');
            });
        }

        if (!Schema::hasTable('point_bus_run_logs')) {
            Schema::create('point_bus_run_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('user_line_id')->index();
                $table->string('run_status', 16)->default('running')->index();
                $table->integer('progress')->default(0);
                $table->text('meta_payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('point_bus_run_logs');
        Schema::dropIfExists('point_user_bus_lines');
        Schema::dropIfExists('point_bus_lines');
        Schema::dropIfExists('point_lottery_draw_logs');
        Schema::dropIfExists('point_lottery_pool_items');
        Schema::dropIfExists('point_lottery_pools');
        Schema::dropIfExists('point_tree_water_logs');
        Schema::dropIfExists('point_tree_instances');
    }
}

