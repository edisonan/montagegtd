<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMarkToArticleSubsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('article_subs') || Schema::hasColumn('article_subs', 'mark')) {
            return;
        }

        // The legacy table contains zero-date defaults that strict MySQL rejects during ALTER.
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement('ALTER TABLE `article_subs` ADD COLUMN `mark` TINYINT NOT NULL DEFAULT 0 AFTER `status`');
    }

    public function down()
    {
        if (Schema::hasTable('article_subs') && Schema::hasColumn('article_subs', 'mark')) {
            DB::statement("SET SESSION sql_mode = ''");
            DB::statement('ALTER TABLE `article_subs` DROP COLUMN `mark`');
        }
    }
}
