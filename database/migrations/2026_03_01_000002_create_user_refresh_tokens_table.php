<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRefreshTokensTable extends Migration
{
    public function up()
    {
        Schema::create('user_refresh_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('access_token_id')->nullable()->index();
            $table->string('token_hash', 64)->unique();
            $table->string('device_id', 128)->nullable()->index();
            $table->string('client_type', 32)->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('access_token_id')->references('id')->on('user_access_tokens')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_refresh_tokens');
    }
}
