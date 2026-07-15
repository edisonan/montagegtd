<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStatusToCategoriesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('categories') || Schema::hasColumn('categories', 'status')) {
            return;
        }

        // The legacy table contains zero-date defaults that strict MySQL rejects during ALTER.
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement('ALTER TABLE `categories` ADD COLUMN `status` TINYINT NOT NULL DEFAULT 1 AFTER `category_order`');
    }

    public function down()
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'status')) {
            DB::statement("SET SESSION sql_mode = ''");
            DB::statement('ALTER TABLE `categories` DROP COLUMN `status`');
        }
    }
}
