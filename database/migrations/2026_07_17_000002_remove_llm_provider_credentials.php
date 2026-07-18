<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveLlmProviderCredentials extends Migration
{
    public function up()
    {
        $this->dropCredentialReference('llm_ai_chats', true);
        $this->dropCredentialReference('llm_conversations', true);
        $this->dropCredentialReference('llm_usage_logs', false);

        Schema::dropIfExists('llm_provider_credentials');

        if (Schema::hasTable('admin_menu')) {
            DB::table('admin_menu')->where('uri', 'llm-provider-credentials')->delete();
        }
    }

    private function dropCredentialReference($tableName, $hasForeignKey)
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if ($hasForeignKey) {
            try {
                DB::statement('ALTER TABLE `' . $tableName . '` DROP FOREIGN KEY `' . $tableName . '_credential_id_foreign`');
            } catch (\Exception $e) {
                // Foreign key may already be absent on older installations.
            }
        } else {
            try {
                DB::statement('ALTER TABLE `' . $tableName . '` DROP INDEX `' . $tableName . '_credential_id_index`');
            } catch (\Exception $e) {
                // Index may already be absent on older installations.
            }
        }

        if (Schema::hasColumn($tableName, 'credential_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('credential_id');
            });
        }
    }

    public function down()
    {
        throw new \RuntimeException('删除 llm_provider_credentials 是不可逆的数据清理操作。');
    }
}
