<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminnte\Support\Facades\DB;

class AddLlmMenuItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llm:add-menu-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加LLM管理菜单项到后台';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $parentId = $this->createLlmParentMenu();
        
        $this->createLlmProviderMenu($parentId);
        $this->createLlmModelMenu($parentId);
        $this->createLlmCredentialMenu($parentId);
        $this->createLlmUsageLogMenu($parentId);
        $this->createLlmAgentMenu($parentId);
        
        $this->info('LLM菜单项添加成功！');
        
        return 0;
    }

    private function createLlmParentMenu()
    {
        // 检查是否已存在LLM父菜单
        $existing = DB::table('admin_menu')->where('title', 'LLM管理')->first();
        if ($existing) {
            $this->info('LLM父菜单已存在，跳过创建');
            return $existing->id;
        }

        // 找到最大排序号
        $maxOrder = DB::table('admin_menu')->max('order');
        
        $parentId = DB::table('admin_menu')->insertGetId([
            'parent_id' => 0,
            'order' => $maxOrder + 1,
            'title' => 'LLM管理',
            'icon' => 'fa-robot',
            'uri' => '',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->info('LLM父菜单创建成功');
        
        return $parentId;
    }

    private function createLlmProviderMenu($parentId)
    {
        // 检查是否已存在LLM供应商菜单
        $existing = DB::table('admin_menu')->where('title', 'LLM供应商')->first();
        if ($existing) {
            $this->info('LLM供应商菜单已存在，跳过创建');
            return;
        }

        $maxOrder = DB::table('admin_menu')->max('order');
        
        DB::table('admin_menu')->insert([
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
            'title' => 'LLM供应商',
            'icon' => 'fa-server',
            'uri' => 'llm-providers',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->info('LLM供应商菜单创建成功');
    }

    private function createLlmModelMenu($parentId)
    {
        // 检查是否已存在LLM模型菜单
        $existing = DB::table('admin_menu')->where('title', 'LLM模型')->first();
        if ($existing) {
            $this->info('LLM模型菜单已存在，跳过创建');
            return;
        }

        $maxOrder = DB::table('admin_menu')->max('order');
        
        DB::table('admin_menu')->insert([
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
            'title' => 'LLM模型',
            'icon' => 'fa-cogs',
            'uri' => 'llm-models',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->info('LLM模型菜单创建成功');
    }

    private function createLlmCredentialMenu($parentId)
    {
        // 检查是否已存在LLM凭据菜单
        $existing = DB::table('admin_menu')->where('title', 'LLM凭据')->first();
        if ($existing) {
            $this->info('LLM凭据菜单已存在，跳过创建');
            return;
        }

        $maxOrder = DB::table('admin_menu')->max('order');
        
        DB::table('admin_menu')->insert([
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
            'title' => 'LLM凭据',
            'icon' => 'fa-key',
            'uri' => 'llm-provider-credentials',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->info('LLM凭据菜单创建成功');
    }

    private function createLlmUsageLogMenu($parentId)
    {
        // 检查是否已存在LLM使用记录菜单
        $existing = DB::table('admin_menu')->where('title', 'LLM使用记录')->first();
        if ($existing) {
            $this->info('LLM使用记录菜单已存在，跳过创建');
            return;
        }

        $maxOrder = DB::table('admin_menu')->max('order');
        
        DB::table('admin_menu')->insert([
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
            'title' => 'LLM使用记录',
            'icon' => 'fa-history',
            'uri' => 'llm-usage-logs',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->info('LLM使用记录菜单创建成功');
    }

    private function createLlmAgentMenu($parentId)
    {
        // 检查是否已存在LLM智能体菜单
        $existing = DB::table('admin_menu')->where('title', 'LLM智能体')->first();
        if ($existing) {
            $this->info('LLM智能体菜单已存在，跳过创建');
            return;
        }

        $maxOrder = DB::table('admin_menu')->max('order');
        
        DB::table('admin_menu')->insert([
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
            'title' => 'LLM智能体',
            'icon' => 'fa-brain',
            'uri' => 'llm-agents',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->info('LLM智能体菜单创建成功');
    }
}