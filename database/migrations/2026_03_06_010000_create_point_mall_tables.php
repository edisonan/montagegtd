<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointMallTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('point_mall_goods')) {
            Schema::create('point_mall_goods', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 64)->unique();
                $table->string('name', 128);
                $table->string('scene', 32)->default('general')->index();
                $table->string('delivery_type', 32)->default('manual');
                $table->string('image_url', 255)->nullable();
                $table->text('description')->nullable();
                $table->integer('point_cost')->default(0);
                $table->integer('stock')->default(-1)->comment('-1 means unlimited');
                $table->tinyInteger('status')->default(1)->index();
                $table->integer('sort')->default(0)->index();
                $table->text('payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_mall_orders')) {
            Schema::create('point_mall_orders', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('order_no', 40)->unique();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('goods_id')->index();
                $table->text('goods_snapshot')->nullable();
                $table->integer('quantity')->default(1);
                $table->integer('point_cost_each')->default(0);
                $table->integer('point_cost_total')->default(0);
                $table->string('status', 32)->default('paid')->index();
                $table->string('delivery_status', 32)->default('pending')->index();
                $table->string('delivery_type', 32)->default('manual');
                $table->string('delivery_message', 255)->nullable();
                $table->text('delivery_payload')->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->dateTime('fulfilled_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_mall_delivery_logs')) {
            Schema::create('point_mall_delivery_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('order_id')->index();
                $table->string('handler', 64);
                $table->string('status', 32)->default('pending');
                $table->string('message', 255)->nullable();
                $table->text('request_payload')->nullable();
                $table->text('response_payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_mall_entitlements')) {
            Schema::create('point_mall_entitlements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('entitlement_type', 32)->index();
                $table->integer('quantity')->default(1);
                $table->string('status', 32)->default('active')->index();
                $table->text('meta_payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('point_mall_entitlements');
        Schema::dropIfExists('point_mall_delivery_logs');
        Schema::dropIfExists('point_mall_orders');
        Schema::dropIfExists('point_mall_goods');
    }
}

