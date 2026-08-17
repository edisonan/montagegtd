<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShareFieldsToNotesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('notes', 'share_token')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->string('share_token', 64)->nullable()->after('audit_status')->comment('分享随机码（替代笔记ID，用于公开分享链接）')->unique();
            });
        }
        if (!Schema::hasColumn('notes', 'share_password')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->string('share_password', 64)->nullable()->after('share_token')->comment('分享访问密码（bcrypt 哈希）');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('notes', 'share_password')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->dropColumn('share_password');
            });
        }
        if (Schema::hasColumn('notes', 'share_token')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->dropUnique(['share_token']);
                $table->dropColumn('share_token');
            });
        }
    }
}