# Task CLI

## 使用心智

待办任务用于承载“需要被推进或完成的动作”。处理任务时先判断用户意图属于哪一类：

- 收集：新建一个任务，尽量保留原话，必要时补充截止时间、提醒时间、优先级。
- 整理：列出任务、筛选未完成/已完成/折叠、查看优先任务、找父任务。
- 推进：设为正在做、设置计划开始/结束、改优先级、改截止时间。
- 完成：把状态改成完成，同时可写评分和复盘。
- 归档/删除：折叠、删除或按后端 `type` 参数执行删除语义。

## 命令

列出未完成任务：

```bash
montage task list --status 1 --page-count 20
```

查看详情、计数、优先级和可选父任务：

```bash
montage task show 123
montage task counts
montage task priority --status 1 --mode 1
montage task parents --exclude-task-id 123
```

创建任务；`mode` 默认 `1`：

```bash
montage task create \
  --name "提交发票" \
  --mode 1 \
  --priority 3 \
  --remindtime "2026-06-30 09:00:00" \
  --deadline "2026-06-30 18:00:00"
```

设为正在做：

```bash
montage task doing 123
montage task stop 123
```

完成任务并复盘：

```bash
montage task complete 123 --rating 5 --review-note "按计划完成，后续可模板化"
```

恢复、归档、删除：

```bash
montage task reopen 123
montage task archive 123
montage task delete 123
```

更新任务字段：

```bash
montage task update 123 --priority 3 --deadline "2026-06-30 18:00:00"
montage task update 123 --data '{"parent_task_id":45,"is_top":1}'
```

## 字段和状态

创建任务：

- `name`：必填，任务名称。
- `mode`：必填，沿用平台现有任务模式。
- `priority`：`1..4`，默认 `1`。
- `remindtime`：提醒时间，格式 `YYYY-mm-dd HH:MM:SS`。
- `deadline`：截止时间，格式 `YYYY-mm-dd HH:MM:SS`。
- `parent_task_id`：父任务。
- `plan_id`：所属计划。
- `mode`：`1` 工作，`2` 生活，`3` 学习。

更新任务：

- `status`：`1` 未完成，`2` 已完成，`3` 折叠。
- `is_doing`：`0/1`，只有未完成任务可设为正在做。
- `planned_start_time` / `planned_end_time`：计划执行时间。
- `rating`：`1..5`。
- `review_note`：复盘文字。
- `name` / `content` / `priority` / `mode` / `parent_task_id` / `plan_id` / `is_top` 也可更新。

## 判断规则

- 用户说“完成、搞定、已做完”时优先用 `task complete`，不要只改标题。
- 用户说“现在做、正在做、开始处理”时用 `task doing`。
- 用户只给自然语言日期时，先转换成 `YYYY-mm-dd HH:MM:SS`；无法确定时问一句。
- 完成任务会触发积分逻辑，重复完成前要确认当前状态。
- `task complete` 使用更新接口，触发任务完成积分；`task delete --type finish` 使用旧完成语义并会写手账，不要混用。
- 如果用户要求批量处理，先列出候选任务并确认 ID，除非用户已经给出明确 ID 列表。

## 兜底调用

```bash
montage request GET /tasks/tab-counts
montage request GET /tasks/priority --query status=1 --query mode=1
```
