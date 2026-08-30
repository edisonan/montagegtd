<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtifactVersionsTable extends Migration
{
    public function up()
    {
        Schema::create('artifact_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('artifact_id')->index();
            $table->unsignedInteger('version');
            $table->longText('content')->nullable();
            $table->string('status', 32)->default('success');
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 32)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('error_message', 255)->nullable();
            $table->text('custom_prompt')->nullable();
            $table->timestamps();

            $table->unique(array('artifact_id', 'version'), 'artifact_versions_artifact_version_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('artifact_versions');
    }
}