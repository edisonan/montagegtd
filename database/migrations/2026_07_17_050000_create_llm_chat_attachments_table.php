<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('llm_chat_attachments')) {
            return;
        }

        Schema::create('llm_chat_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 64)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('original_name', 255);
            $table->string('mime_type', 120)->nullable();
            $table->string('extension', 20);
            $table->unsignedInteger('size');
            $table->string('storage_path', 500);
            $table->longText('extracted_text');
            $table->string('status', 30)->default('ready');
            $table->string('error_message', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'idx_llm_attachment_user');
            $table->index(['session_id', 'conversation_id'], 'idx_llm_attachment_message');
        });
    }

    public function down()
    {
        Schema::dropIfExists('llm_chat_attachments');
    }
};
