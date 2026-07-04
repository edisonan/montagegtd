<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('llm_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('model_id')->nullable()->constrained('llm_models')->onDelete('set null');
            $table->foreignId('credential_id')->nullable()->constrained('llm_provider_credentials')->onDelete('set null');
            $table->text('question');
            $table->longText('answer');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('cost', 10, 6)->default(0.000000);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('llm_conversations');
    }
};
