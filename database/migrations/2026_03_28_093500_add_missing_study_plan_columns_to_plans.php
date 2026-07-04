<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingStudyPlanColumnsToPlans extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'plan_type')) {
                $table->string('plan_type', 32)->default('general');
            }
            if (!Schema::hasColumn('plans', 'content')) {
                $table->text('content')->nullable();
            }
            if (!Schema::hasColumn('plans', 'start_time')) {
                $table->dateTime('start_time')->nullable();
            }
            if (!Schema::hasColumn('plans', 'repeat_type')) {
                $table->string('repeat_type', 32)->default('none');
            }
            if (!Schema::hasColumn('plans', 'repeat_days')) {
                $table->string('repeat_days', 64)->nullable();
            }
            if (!Schema::hasColumn('plans', 'repeat_meta')) {
                $table->text('repeat_meta')->nullable();
            }
            if (!Schema::hasColumn('plans', 'sp_points')) {
                $table->integer('sp_points')->default(0);
            }
            if (!Schema::hasColumn('plans', 'last_generated_date')) {
                $table->date('last_generated_date')->nullable();
            }
        });
    }

    public function down()
    {
        // Keep compatibility columns; do not drop them on rollback.
    }
}
