<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepairStudySchema extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'content')) {
                    $table->text('content')->nullable()->after('name');
                }
                if (!Schema::hasColumn('tasks', 'study_scheduled_date')) {
                    $table->date('study_scheduled_date')->nullable()->index();
                }
                if (!Schema::hasColumn('tasks', 'study_repeat_type')) {
                    $table->string('study_repeat_type', 32)->nullable()->default('none');
                }
                if (!Schema::hasColumn('tasks', 'study_repeat_days')) {
                    $table->string('study_repeat_days', 64)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'study_repeat_meta')) {
                    $table->text('study_repeat_meta')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'study_sp_points')) {
                    $table->integer('study_sp_points')->default(0);
                }
                if (!Schema::hasColumn('tasks', 'study_source_task_id')) {
                    $table->integer('study_source_task_id')->nullable()->index();
                }
            });
        }

        if (!Schema::hasTable('study_checkins')) {
            Schema::create('study_checkins', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('user_id')->index();
                $table->integer('task_id')->index();
                $table->date('checkin_date')->index();
                $table->text('content')->nullable();
                $table->string('audio_path', 255)->nullable();
                $table->string('image_path', 255)->nullable();
                $table->string('video_path', 255)->nullable();
                $table->timestamps();
                $table->unique(array('user_id', 'task_id', 'checkin_date'), 'uniq_study_checkin');
            });
        } elseif (!Schema::hasColumn('study_checkins', 'video_path')) {
            Schema::table('study_checkins', function (Blueprint $table) {
                $table->string('video_path', 255)->nullable();
            });
        }

        if (Schema::hasTable('point_account') && !Schema::hasColumn('point_account', 'sp_balance')) {
            Schema::table('point_account', function (Blueprint $table) {
                $table->integer('sp_balance')->default(0)->after('ap_frozen');
            });
        }
    }

    public function down()
    {
        // Keep this repair migration non-destructive. The original study migrations
        // remain the source of truth for rollback in environments where they ran.
    }
}
