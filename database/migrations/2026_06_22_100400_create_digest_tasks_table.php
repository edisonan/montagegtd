<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigestTasksTable extends Migration
{
    public function up()
    {
        Schema::create('digest_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('profile_id')->index();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->string('error_message', 255)->nullable();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamps();

            $table->index(array('status', 'scheduled_at'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('digest_tasks');
    }
}
