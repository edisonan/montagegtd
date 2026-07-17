<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseAutomation extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('courses', 'automation_config')) Schema::table('courses', function (Blueprint $table) {
            $table->text('automation_config')->nullable()->after('content_status');
        });

        if (!Schema::hasTable('course_generation_runs')) Schema::create('course_generation_runs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('course_id');
            $table->string('mode', 20);
            $table->string('status', 20)->default('running');
            $table->text('source_url')->nullable();
            $table->integer('items_count')->default(0);
            $table->text('error')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['course_id', 'status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_generation_runs');
        if (Schema::hasColumn('courses', 'automation_config')) Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('automation_config');
        });
    }
}
