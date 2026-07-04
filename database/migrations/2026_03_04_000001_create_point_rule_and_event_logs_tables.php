<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointRuleAndEventLogsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('point_rule')) {
            Schema::create('point_rule', function (Blueprint $table) {
                $table->increments('id');
                $table->string('event_type', 64)->index();
                $table->string('name', 128)->default('');
                $table->string('point_type', 16)->default('GP');
                $table->integer('point_value')->default(0);
                $table->integer('daily_max_grants')->default(0)->comment('0 means unlimited');
                $table->tinyInteger('enabled')->default(1)->index();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_event_log')) {
            Schema::create('point_event_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('user_id')->index();
                $table->integer('rule_id')->nullable()->index();
                $table->string('event_type', 64)->index();
                $table->string('event_key', 191);
                $table->string('source_type', 64)->nullable();
                $table->integer('source_id')->nullable();
                $table->string('point_type', 16)->default('GP');
                $table->integer('granted_points')->default(0);
                $table->integer('balance_after')->default(0);
                $table->date('occurred_on')->index();
                $table->timestamps();

                $table->unique(array('user_id', 'event_key'), 'uniq_point_event_user_key');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('point_event_log');
        Schema::dropIfExists('point_rule');
    }
}

