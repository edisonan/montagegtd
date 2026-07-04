<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleReviewFieldsToTasksAndPomosTable extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'planned_start_time')) {
                $table->dateTime('planned_start_time')->nullable()->comment('预计开始时间')->after('name');
            }
            if (!Schema::hasColumn('tasks', 'planned_end_time')) {
                $table->dateTime('planned_end_time')->nullable()->comment('预计结束时间')->after('planned_start_time');
            }
            if (!Schema::hasColumn('tasks', 'rating')) {
                $table->tinyInteger('rating')->nullable()->comment('任务评分 1-5')->after('status');
            }
            if (!Schema::hasColumn('tasks', 'review_note')) {
                $table->text('review_note')->nullable()->comment('任务备注')->after('rating');
            }
        });

        Schema::table('focus', function (Blueprint $table) {
            if (!Schema::hasColumn('focus', 'rating')) {
                $table->tinyInteger('rating')->nullable()->comment('番茄评分 1-5')->after('name');
            }
            if (!Schema::hasColumn('focus', 'review_note')) {
                $table->text('review_note')->nullable()->comment('番茄备注')->after('rating');
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'review_note')) {
                $table->dropColumn('review_note');
            }
            if (Schema::hasColumn('tasks', 'rating')) {
                $table->dropColumn('rating');
            }
            if (Schema::hasColumn('tasks', 'planned_end_time')) {
                $table->dropColumn('planned_end_time');
            }
            if (Schema::hasColumn('tasks', 'planned_start_time')) {
                $table->dropColumn('planned_start_time');
            }
        });

        Schema::table('focus', function (Blueprint $table) {
            if (Schema::hasColumn('focus', 'review_note')) {
                $table->dropColumn('review_note');
            }
            if (Schema::hasColumn('focus', 'rating')) {
                $table->dropColumn('rating');
            }
        });
    }
}
