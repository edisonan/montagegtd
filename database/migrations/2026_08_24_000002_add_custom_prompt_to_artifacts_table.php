<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomPromptToArtifactsTable extends Migration
{
    public function up()
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->text('custom_prompt')->nullable()->after('error_message');
        });
    }

    public function down()
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->dropColumn('custom_prompt');
        });
    }
}