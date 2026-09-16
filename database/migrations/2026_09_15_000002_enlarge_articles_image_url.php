<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * articles.image_url 目前是 varchar(255)，部分订阅源（如 V2EX 的图片
 * CDN 链接）首图 URL 远超 255 字符。严格模式下插入会报
 * SQLSTATE[22001] "Data too long for column 'image_url'"，并使整条订阅检查
 * 失败、文章无法落库。
 *
 * 将列扩展为 TEXT（与 content 一样按 utf8mb4_unicode_ci 存储），避免长 URL
 * 被截断或整条记录写入失败。
 */
class EnlargeArticlesImageUrl extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('articles') || !Schema::hasColumn('articles', 'image_url')) {
            return;
        }

        DB::statement("ALTER TABLE `articles` MODIFY `image_url` TEXT NULL COLLATE utf8mb4_unicode_ci");
    }

    public function down()
    {
        if (!Schema::hasTable('articles') || !Schema::hasColumn('articles', 'image_url')) {
            return;
        }

        DB::statement("ALTER TABLE `articles` MODIFY `image_url` VARCHAR(255) NULL COLLATE utf8mb4_unicode_ci");
    }
}
