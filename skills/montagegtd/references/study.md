# Study CLI（学习计划与打卡）

## 使用心智

学习能力面向"学习计划 + 阶段任务 + 打卡"的闭环。处理前先判断意图属于哪一类：

- 总览：一次看今天/某天的学习概览（overview）。
- 打卡记录：查看某段时间的打卡流水（checkins）。
- 学习计划：列出我的学习计划、看某个计划详情、按计划生成达标任务。
- 打卡：给某个**学习任务**（`mode=3`）写当天文字打卡。
- 生成：按日期范围自动生成学习任务（全局或按计划）。

关键约束：

- **打卡目标必须是学习任务**，即任务 `mode=3`；对普通任务调用 `study checkin` 会 403。
- `study checkin` 目前仅支持**文字**打卡（`--content` 必填）。多媒体（audio/image/video）后端是 multipart 文件上传，CLI 不直接传二进制，需要时用其它前端/手工处理。
- 学习计划在 `plans` 表中 `plan_type='study'`，与普通 plan 区分开。

## 常用命令

```bash
# 今天/指定日学习总览
montage study overview
montage study overview --date 2026-07-06

# 打卡流水（时间范围 + 分页）
montage study checkins --date-from 2026-07-01 --date-to 2026-07-06 --page-size 30

# 学习计划
montage study plans
montage study plan 12

# 给学习任务 88 打卡（--content 必填）
montage study checkin 88 --date 2026-07-06 --content "完成第三章，掌握排序算法"

# 生成任务
montage study generate --date-from 2026-07-06 --date-to 2026-07-20
montage study plan-generate 12 --date-from 2026-07-06 --date-to 2026-07-20
```

## 字段说明

### overview `GET /study/overview`

- `date`：`YYYY-MM-DD`，可选。

### checkins `GET /study/checkins`

- `date_from` / `date_to`：`YYYY-MM-DD`，可选。
- `page`：页码，默认 1。
- `page_size`：默认 20，上限 100。

### checkin `POST /study/tasks/{task}/checkin`

- `date`：`YYYY-MM-DD`，默认当天。
- `content`：打卡文字，必填，≤5000 字符。
- 目标任务需 `mode=3`。
- `study checkin` 只发文字；不传 audio/image/video。

### plan-generate `POST /study/plans/{plan}/generate`

- 生成目标 plan（study 类型）在日期范围内的达标任务。
- 返回 `{plan_id, generated, from, to}`。

## 判断规则

- 用户说"我今天学了啥/学习概览" → `study overview`。
- 用户说"学习打卡" → 先确认对应学习任务 id（`mode=3`），再 `study checkin <task_id> --content ...`。
- 用户说"看我的学习计划" → `study plans` / `study plan <id>`。
- 生成任务通常是批量动作，若候选不明确应先列计划再确认。

## 兜底调用

学习的多态能力（如创建/更新计划、设置状态）用通用请求：

```bash
montage request POST /study/plans --data '{"name":"...","start_time":"...","repeat_type":"daily"}'
montage request POST /study/plans/12/status --data '{"status":0}'
montage request DELETE /study/plans/12
```
