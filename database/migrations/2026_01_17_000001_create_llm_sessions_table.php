<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLlmSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('llm_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('user_id');
            $table->string('title')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->integer('message_count')->default(0);
            $table->integer('token_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'deleted_at', 'last_message_at'], 'idx_user_main');
            $table->index(['is_active', 'last_message_at'], 'idx_cleanup');
            $table->index('agent_id', 'idx_agent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('llm_sessions');
    }
}