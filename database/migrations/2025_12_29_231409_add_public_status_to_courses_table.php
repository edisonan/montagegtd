<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPublicStatusToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('public_status')->default(2)->comment('公开状态：1-私有，2-公开待审核，3-公开并审核通过')->after('is_public');
        });
        
        // 更新现有课程的public_status
        DB::table('courses')->where('is_public', true)->update(['public_status' => 3]);
        DB::table('courses')->where('is_public', false)->update(['public_status' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('public_status');
        });
    }
}