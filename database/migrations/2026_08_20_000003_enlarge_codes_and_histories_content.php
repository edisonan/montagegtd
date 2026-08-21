<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Enlarge code content columns to MEDIUMTEXT.
 *
 * codes.content / code_histories.content are currently TEXT (max ~64KB / 65535
 * bytes). Users save full HTML pages (e.g. a 75KB+ slides deck) into these
 * columns; under MySQL strict mode the insert then fails with
 * SQLSTATE[22001]/[22007] "Data too long for column 'content'" and the whole
 * admin save request returns a 5xx.
 *
 * MEDIUMTEXT holds up to 16MB, which comfortably covers large code/HTML pages.
 */
class EnlargeCodesAndHistoriesContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // codes.content is nullable; code_histories.content is NOT NULL.
        $modifiers = [
            'codes'          => 'MEDIUMTEXT NULL',
            'code_histories' => 'MEDIUMTEXT NOT NULL',
        ];
        foreach ($modifiers as $table => $modifier) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'content')) {
                continue;
            }
            DB::statement("ALTER TABLE `{$table}` MODIFY `content` {$modifier} COLLATE utf8mb4_unicode_ci");
        }

        // Drop any cached config that might pin old schema info (no-op otherwise).
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $modifiers = [
            'codes'          => 'TEXT NULL',
            'code_histories' => 'TEXT NOT NULL',
        ];
        foreach ($modifiers as $table => $modifier) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'content')) {
                continue;
            }
            DB::statement("ALTER TABLE `{$table}` MODIFY `content` {$modifier} COLLATE utf8mb4_unicode_ci");
        }
    }
}
