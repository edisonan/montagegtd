<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionIdToLlmConversations extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('llm_conversations') || Schema::hasColumn('llm_conversations', 'session_id')) {
            return;
        }

        Schema::table('llm_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->after('user_id');
            $table->index('session_id', 'idx_llm_conversations_session');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('llm_conversations') || !Schema::hasColumn('llm_conversations', 'session_id')) {
            return;
        }

        Schema::table('llm_conversations', function (Blueprint $table) {
            $table->dropIndex('idx_llm_conversations_session');
            $table->dropColumn('session_id');
        });
    }
}
