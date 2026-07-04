<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoPathToStudyCheckins extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('study_checkins')) {
            return;
        }

        Schema::table('study_checkins', function (Blueprint $table) {
            if (!Schema::hasColumn('study_checkins', 'video_path')) {
                $table->string('video_path', 500)->nullable()->after('image_path');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('study_checkins')) {
            return;
        }

        Schema::table('study_checkins', function (Blueprint $table) {
            if (Schema::hasColumn('study_checkins', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });
    }
}
