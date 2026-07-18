<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveLlmApiKeysToProviders extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('llm_providers', 'api_key')) {
            Schema::table('llm_providers', function (Blueprint $table) {
                $table->text('api_key')->nullable()->after('api_type')->comment('加密存储的供应商API Key');
            });
        }

        // 保留旧凭据表和历史记录；仅把每个供应商的默认/首个有效 Key 回填为主 Key。
        DB::table('llm_providers')->orderBy('id')->get()->each(function ($provider) {
            if (!empty($provider->api_key)) {
                return;
            }

            $credential = DB::table('llm_provider_credentials')
                ->where('provider_id', $provider->id)
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->whereNotNull('api_key')
                ->where('api_key', '<>', '')
                ->orderBy('is_default', 'desc')
                ->orderBy('id')
                ->first();

            if ($credential) {
                DB::table('llm_providers')
                    ->where('id', $provider->id)
                    ->update(array('api_key' => $credential->api_key));
            }
        });

        // 新的 Provider Key 模式不再要求每次调用必须有 credential_id；历史日志仍保留原关联。
        if (Schema::hasColumn('llm_usage_logs', 'credential_id')) {
            DB::statement('ALTER TABLE `llm_usage_logs` MODIFY `credential_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('llm_usage_logs', 'credential_id')) {
            DB::statement('ALTER TABLE `llm_usage_logs` MODIFY `credential_id` BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasColumn('llm_providers', 'api_key')) {
            Schema::table('llm_providers', function (Blueprint $table) {
                $table->dropColumn('api_key');
            });
        }
    }
}
