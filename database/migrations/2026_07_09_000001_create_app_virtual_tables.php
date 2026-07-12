<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppVirtualTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('app_virtual_tables')) {
            Schema::create('app_virtual_tables', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('app_id')->index();
                $table->string('name', 120);
                $table->string('slug', 80);
                $table->string('physical_table', 160)->nullable();
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1)->index();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(array('app_id', 'slug'));
                $table->index('physical_table');
            });
        }

        if (!Schema::hasTable('app_virtual_table_fields')) {
            Schema::create('app_virtual_table_fields', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('virtual_table_id')->index();
                $table->string('name', 120);
                $table->string('slug', 80);
                $table->string('physical_name', 100);
                $table->string('type', 30)->default('string');
                $table->unsignedInteger('length')->nullable();
                $table->tinyInteger('nullable')->default(1);
                $table->tinyInteger('default_enabled')->default(0);
                $table->string('default_value', 255)->nullable();
                $table->tinyInteger('indexed')->default(0);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->tinyInteger('status')->default(1)->index();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(array('virtual_table_id', 'slug'));
                $table->unique(array('virtual_table_id', 'physical_name'));
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('app_virtual_table_fields');
        Schema::dropIfExists('app_virtual_tables');
    }
}
