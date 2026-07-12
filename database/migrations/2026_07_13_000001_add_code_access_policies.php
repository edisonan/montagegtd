<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeAccessPolicies extends Migration
{
    public function up()
    {
        if (Schema::hasTable('applications') && !Schema::hasColumn('applications', 'auth_mode')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->string('auth_mode', 32)->default('public')->index();
            });
        }

        if (Schema::hasTable('codes') && !Schema::hasColumn('codes', 'auth_mode')) {
            Schema::table('codes', function (Blueprint $table) {
                $table->string('auth_mode', 32)->nullable()->index();
            });
        }

        if (!Schema::hasTable('application_allowed_users')) {
            Schema::create('application_allowed_users', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('application_id')->index();
                $table->unsignedInteger('user_id')->index();
                $table->timestamps();
                $table->unique(array('application_id', 'user_id'));
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('application_allowed_users');

        if (Schema::hasTable('codes') && Schema::hasColumn('codes', 'auth_mode')) {
            Schema::table('codes', function (Blueprint $table) {
                $table->dropColumn('auth_mode');
            });
        }

        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'auth_mode')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('auth_mode');
            });
        }
    }
}
