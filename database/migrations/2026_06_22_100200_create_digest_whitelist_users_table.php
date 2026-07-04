<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigestWhitelistUsersTable extends Migration
{
    public function up()
    {
        Schema::create('digest_whitelist_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamp('expires_at')->nullable();
            $table->string('remark', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('digest_whitelist_users');
    }
}
