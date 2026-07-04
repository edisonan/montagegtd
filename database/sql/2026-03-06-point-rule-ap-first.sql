-- Point rule rebalance: AP-first, GP as milestone points
-- Generated at: 2026-03-06
-- Target table: point_rule

START TRANSACTION;

UPDATE point_rule
SET point_type='AP', point_value=3, daily_max_grants=30, enabled=1,
    description='完成待办任务奖励（AP）', updated_at=NOW()
WHERE event_type='task_completed';

UPDATE point_rule
SET point_type='AP', point_value=8, daily_max_grants=16, enabled=1,
    description='完成专注番茄奖励（AP）', updated_at=NOW()
WHERE event_type='pomo_completed';

UPDATE point_rule
SET point_type='AP', point_value=20, daily_max_grants=1, enabled=1,
    description='每日复盘奖励（AP）', updated_at=NOW()
WHERE event_type='daily_summary_created';

UPDATE point_rule
SET point_type='AP', point_value=3, daily_max_grants=10, enabled=1,
    description='创建有效笔记奖励（AP）', updated_at=NOW()
WHERE event_type='note_created';

UPDATE point_rule
SET point_type='AP', point_value=5, daily_max_grants=5, enabled=1,
    description='创建思维导图奖励（AP）', updated_at=NOW()
WHERE event_type='mind_created';

UPDATE point_rule
SET point_type='AP', point_value=2, daily_max_grants=20, enabled=1,
    description='将文章标记为已读奖励（AP）', updated_at=NOW()
WHERE event_type='article_finished';

UPDATE point_rule
SET point_type='AP', point_value=2, daily_max_grants=20, enabled=1,
    description='保存文章摘录奖励（AP）', updated_at=NOW()
WHERE event_type='article_mark_created';

UPDATE point_rule
SET point_type='GP', point_value=20, daily_max_grants=3, enabled=1,
    description='完成目标里程碑奖励（GP）', updated_at=NOW()
WHERE event_type='goal_completed';

UPDATE point_rule
SET point_type='GP', point_value=10, daily_max_grants=2, enabled=1,
    description='创建课程里程碑奖励（GP）', updated_at=NOW()
WHERE event_type='course_created';

UPDATE point_rule
SET point_type='AP', point_value=6, daily_max_grants=5, enabled=1,
    description='加入学习课程奖励（AP）', updated_at=NOW()
WHERE event_type='course_joined';

UPDATE point_rule
SET point_type='AP', point_value=2, daily_max_grants=20, enabled=1,
    description='创建事项奖励（AP）', updated_at=NOW()
WHERE event_type='thing_created';

UPDATE point_rule
SET point_type='AP', point_value=2, daily_max_grants=10, enabled=1,
    description='完成一次有效AI对话奖励（AP）', updated_at=NOW()
WHERE event_type='llm_session_completed';

UPDATE point_rule
SET point_type='AP', point_value=4, daily_max_grants=20, enabled=1,
    description='完成课程课时奖励（AP）', updated_at=NOW()
WHERE event_type='course_item_completed';

COMMIT;

