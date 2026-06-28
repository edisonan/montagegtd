<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddSystemLogMenuItem extends Command
{
    protected $signature = 'admin:add-system-log-menu';

    protected $description = '添加系统日志查询菜单项到后台';

    public function handle()
    {
        $existing = DB::table('admin_menu')->where('uri', 'system-logs')->first();
        if ($existing) {
            $this->info('日志查询菜单已存在，跳过创建');
            return 0;
        }

        $parentId = $this->findOrCreateParent();
        $maxOrder = (int) DB::table('admin_menu')->max('order');

        DB::table('admin_menu')->insert(array(
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
            'title' => '日志查询',
            'icon' => 'fa-file-text-o',
            'uri' => 'system-logs',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        $this->info('日志查询菜单创建成功');

        return 0;
    }

    protected function findOrCreateParent()
    {
        $existing = DB::table('admin_menu')->where('title', '系统管理')->first();
        if ($existing) {
            return $existing->id;
        }

        $maxOrder = (int) DB::table('admin_menu')->max('order');

        return DB::table('admin_menu')->insertGetId(array(
            'parent_id' => 0,
            'order' => $maxOrder + 1,
            'title' => '系统管理',
            'icon' => 'fa-cogs',
            'uri' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }
}
