<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceFieldsToMindsTable extends Migration
{
    public function up()
    {
        Schema::table('minds', function (Blueprint $table) {
            if (!Schema::hasColumn('minds', 'source_type')) {
                $table->string('source_type', 50)->nullable()->after('copy_mind_id');
            }
            if (!Schema::hasColumn('minds', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
        });
    }

    public function down()
    {
        Schema::table('minds', function (Blueprint $table) {
            if (Schema::hasColumn('minds', 'source_id')) {
                $table->dropColumn('source_id');
            }
            if (Schema::hasColumn('minds', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
}

