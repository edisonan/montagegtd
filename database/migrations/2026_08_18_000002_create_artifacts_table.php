<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtifactsTable extends Migration
{
    public function up()
    {
        Schema::create('artifacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name', 255)->nullable();
            $table->string('file_type', 32)->default('text')->index();
            $table->string('artifact_type', 32)->index();
            $table->string('related_type', 32)->index();
            $table->unsignedBigInteger('related_id')->index();
            $table->longText('content')->nullable();
            $table->string('status', 32)->default('success')->index();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamp('generated_at')->nullable()->index();
            $table->string('error_message', 255)->nullable();
            $table->timestamps();

            $table->unique(array('user_id', 'related_type', 'related_id', 'artifact_type'), 'artifacts_user_rel_type_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('artifacts');
    }
}