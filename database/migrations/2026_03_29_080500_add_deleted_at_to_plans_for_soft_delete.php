<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToPlansForSoftDelete extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->index();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
}
