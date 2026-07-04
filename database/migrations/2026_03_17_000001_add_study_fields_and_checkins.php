<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudyFieldsAndCheckins extends Migration
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
                $table->timestamps();
                $table->unique(array('user_id', 'task_id', 'checkin_date'), 'uniq_study_checkin');
            });
        }

        if (Schema::hasTable('point_account')) {
            Schema::table('point_account', function (Blueprint $table) {
                if (!Schema::hasColumn('point_account', 'sp_balance')) {
                    $table->integer('sp_balance')->default(0)->after('ap_frozen');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('point_account')) {
            Schema::table('point_account', function (Blueprint $table) {
                if (Schema::hasColumn('point_account', 'sp_balance')) {
                    $table->dropColumn('sp_balance');
                }
            });
        }

        Schema::dropIfExists('study_checkins');

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'study_source_task_id')) {
                    $table->dropColumn('study_source_task_id');
                }
                if (Schema::hasColumn('tasks', 'study_sp_points')) {
                    $table->dropColumn('study_sp_points');
                }
                if (Schema::hasColumn('tasks', 'study_repeat_meta')) {
                    $table->dropColumn('study_repeat_meta');
                }
                if (Schema::hasColumn('tasks', 'study_repeat_days')) {
                    $table->dropColumn('study_repeat_days');
                }
                if (Schema::hasColumn('tasks', 'study_repeat_type')) {
                    $table->dropColumn('study_repeat_type');
                }
                if (Schema::hasColumn('tasks', 'study_scheduled_date')) {
                    $table->dropColumn('study_scheduled_date');
                }
                if (Schema::hasColumn('tasks', 'content')) {
                    $table->dropColumn('content');
                }
            });
        }
    }
}
