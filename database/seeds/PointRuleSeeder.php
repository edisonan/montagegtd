<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointRuleSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $rules = array(
            array(
                'event_type' => 'task_completed',
                'name' => '完成任务',
                'point_type' => 'AP',
                'point_value' => 3,
                'daily_max_grants' => 30,
                'enabled' => 1,
                'description' => '完成待办任务奖励（AP）',
            ),
            array(
                'event_type' => 'pomo_completed',
                'name' => '完成番茄',
                'point_type' => 'AP',
                'point_value' => 8,
                'daily_max_grants' => 16,
                'enabled' => 1,
                'description' => '完成专注番茄奖励（AP）',
            ),
            array(
                'event_type' => 'daily_summary_created',
                'name' => '完成日报',
                'point_type' => 'AP',
                'point_value' => 20,
                'daily_max_grants' => 1,
                'enabled' => 1,
                'description' => '每日复盘奖励（AP）',
            ),
            array(
                'event_type' => 'note_created',
                'name' => '创建笔记',
                'point_type' => 'AP',
                'point_value' => 3,
                'daily_max_grants' => 10,
                'enabled' => 1,
                'description' => '创建有效笔记奖励（AP）',
            ),
            array(
                'event_type' => 'mind_created',
                'name' => '创建导图',
                'point_type' => 'AP',
                'point_value' => 5,
                'daily_max_grants' => 5,
                'enabled' => 1,
                'description' => '创建思维导图奖励（AP）',
            ),
            array(
                'event_type' => 'article_finished',
                'name' => '阅读文章',
                'point_type' => 'AP',
                'point_value' => 2,
                'daily_max_grants' => 20,
                'enabled' => 1,
                'description' => '将文章标记为已读奖励（AP）',
            ),
            array(
                'event_type' => 'article_mark_created',
                'name' => '文章摘录',
                'point_type' => 'AP',
                'point_value' => 2,
                'daily_max_grants' => 20,
                'enabled' => 1,
                'description' => '保存文章摘录奖励（AP）',
            ),
            array(
                'event_type' => 'goal_completed',
                'name' => '完成目标',
                'point_type' => 'GP',
                'point_value' => 20,
                'daily_max_grants' => 3,
                'enabled' => 1,
                'description' => '完成目标里程碑奖励（GP）',
            ),
            array(
                'event_type' => 'course_created',
                'name' => '创建课程',
                'point_type' => 'GP',
                'point_value' => 10,
                'daily_max_grants' => 2,
                'enabled' => 1,
                'description' => '创建课程里程碑奖励（GP）',
            ),
            array(
                'event_type' => 'course_joined',
                'name' => '加入课程',
                'point_type' => 'AP',
                'point_value' => 6,
                'daily_max_grants' => 5,
                'enabled' => 1,
                'description' => '加入学习课程奖励（AP）',
            ),
            array(
                'event_type' => 'thing_created',
                'name' => '创建事项',
                'point_type' => 'AP',
                'point_value' => 2,
                'daily_max_grants' => 20,
                'enabled' => 1,
                'description' => '创建事项奖励（AP）',
            ),
            array(
                'event_type' => 'llm_session_completed',
                'name' => 'AI有效对话',
                'point_type' => 'AP',
                'point_value' => 2,
                'daily_max_grants' => 10,
                'enabled' => 1,
                'description' => '完成一次有效AI对话奖励（AP）',
            ),
            array(
                'event_type' => 'course_item_completed',
                'name' => '完成课时',
                'point_type' => 'AP',
                'point_value' => 4,
                'daily_max_grants' => 20,
                'enabled' => 1,
                'description' => '完成课程课时奖励（AP）',
            ),
        );

        foreach ($rules as $rule) {
            $exists = DB::table('point_rule')
                ->where('event_type', $rule['event_type'])
                ->where('name', $rule['name'])
                ->first();

            if ($exists) {
                DB::table('point_rule')
                    ->where('id', $exists->id)
                    ->update(array(
                        'point_type' => $rule['point_type'],
                        'point_value' => $rule['point_value'],
                        'daily_max_grants' => $rule['daily_max_grants'],
                        'enabled' => $rule['enabled'],
                        'description' => $rule['description'],
                        'updated_at' => $now,
                    ));
            } else {
                DB::table('point_rule')->insert(array(
                    'event_type' => $rule['event_type'],
                    'name' => $rule['name'],
                    'point_type' => $rule['point_type'],
                    'point_value' => $rule['point_value'],
                    'daily_max_grants' => $rule['daily_max_grants'],
                    'enabled' => $rule['enabled'],
                    'description' => $rule['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ));
            }
        }
    }
}
