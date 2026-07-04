<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentToNotesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('notes', 'content')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->text('content')->nullable()->after('name')->comment('正文内容');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('notes', 'content')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->dropColumn('content');
            });
        }
    }
}
