<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementCatalogSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $items = array(
            // 自动成就（achievement）
            array(
                'code' => 'achievement_first_task_done',
                'name' => '初次完成任务',
                'description' => '完成第一个待办任务',
                'category' => 'achievement',
                'point_value' => 10,
                'icon' => 'check-circle',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_task_done_10',
                'name' => '任务达人·青铜',
                'description' => '累计完成 10 个任务',
                'category' => 'achievement',
                'point_value' => 20,
                'icon' => 'tasks',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_task_done_100',
                'name' => '任务达人·白银',
                'description' => '累计完成 100 个任务',
                'category' => 'achievement',
                'point_value' => 80,
                'icon' => 'list-check',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_pomo_done_10',
                'name' => '专注新手',
                'description' => '累计完成 10 个番茄钟',
                'category' => 'achievement',
                'point_value' => 20,
                'icon' => 'clock',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_pomo_done_100',
                'name' => '专注达人',
                'description' => '累计完成 100 个番茄钟',
                'category' => 'achievement',
                'point_value' => 100,
                'icon' => 'fire',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_daily_summary_7',
                'name' => '复盘一周',
                'description' => '累计完成 7 次日报复盘',
                'category' => 'achievement',
                'point_value' => 40,
                'icon' => 'book-open',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_daily_summary_30',
                'name' => '复盘习惯养成',
                'description' => '累计完成 30 次日报复盘',
                'category' => 'achievement',
                'point_value' => 120,
                'icon' => 'calendar-check',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'achievement_note_50',
                'name' => '记录者',
                'description' => '累计创建 50 条笔记',
                'category' => 'achievement',
                'point_value' => 60,
                'icon' => 'pen-to-square',
                'enabled' => 1,
                'visible' => 1,
            ),

            // 手动勋章（badge）
            array(
                'code' => 'badge_early_bird',
                'name' => '晨型执行者',
                'description' => '连续一段时间保持早起高效习惯，可手动领取',
                'category' => 'badge',
                'point_value' => 30,
                'icon' => 'sun',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'badge_deep_work',
                'name' => '深度工作勋章',
                'description' => '专注完成多个高质量番茄后可手动领取',
                'category' => 'badge',
                'point_value' => 50,
                'icon' => 'brain',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'badge_consistency',
                'name' => '持续精进勋章',
                'description' => '连续多日保持任务与复盘节奏后可手动领取',
                'category' => 'badge',
                'point_value' => 60,
                'icon' => 'bolt',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'badge_knowledge_collector',
                'name' => '知识收藏家',
                'description' => '沉淀阅读、笔记与课程学习成果后可手动领取',
                'category' => 'badge',
                'point_value' => 40,
                'icon' => 'book',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'badge_focus_master',
                'name' => '专注大师',
                'description' => '累计大量专注时长后可手动领取',
                'category' => 'badge',
                'point_value' => 120,
                'icon' => 'medal',
                'enabled' => 1,
                'visible' => 1,
            ),
            array(
                'code' => 'badge_productivity_architect',
                'name' => '效率架构师',
                'description' => '任务、番茄、笔记、复盘体系完善后可手动领取',
                'category' => 'badge',
                'point_value' => 150,
                'icon' => 'crown',
                'enabled' => 1,
                'visible' => 1,
            ),
        );

        foreach ($items as $item) {
            $row = DB::table('achievement')->where('code', $item['code'])->first();

            if ($row) {
                DB::table('achievement')->where('id', $row->id)->update(array(
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'point_value' => $item['point_value'],
                    'icon' => $item['icon'],
                    'enabled' => $item['enabled'],
                    'visible' => $item['visible'],
                    'updated_at' => $now,
                ));
            } else {
                DB::table('achievement')->insert(array(
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'point_value' => $item['point_value'],
                    'icon' => $item['icon'],
                    'enabled' => $item['enabled'],
                    'visible' => $item['visible'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ));
            }
        }
    }
}

