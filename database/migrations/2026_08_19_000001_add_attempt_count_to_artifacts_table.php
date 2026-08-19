<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttemptCountToArtifactsTable extends Migration
{
    public function up()
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempt_count')->default(0)->after('error_message');
        });
    }

    public function down()
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->dropColumn('attempt_count');
        });
    }
}