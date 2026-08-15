# 端到端使用场景

本文串联多个能力域，给出**可以直接照着跑**的真实工作流。遇到复合型请求时，先看下面哪个场景匹配，再按流程执行；找不到匹配时退回各领域 reference 单独处理。

判断入口：

- 复盘/回顾一段时间的任务 → 场景 1
- 最近阅读内容分析/梳理 → 场景 2
- 看订阅/整理阅读偏好 → 场景 3
- 把阅读/想法沉淀成第二个大脑 → 场景 4
- 学习打卡闭环 → 场景 5

---

## 场景 1：周/日复盘

**触发**：用户说"帮我做周复盘""回顾这周干了啥""生成日报"。

**流程**：

```bash
# 1. 近况：当前未完成任务清单
$CLI task list --status 1 --page-count 30

# 2. 已办概览
$CLI task counts

# 3. 学习打卡（如适用）
$CLI study-overview --date 2026-08-15

# 4. 写日报（可选，写则触发积分）
$CLI daily-summary-create --summary-date 2026-08-15 \
  --work-content "本周完成..." --life-content "..."

# 5. 复盘落笔记（可选）
$CLI note create --title "2026-08-15 复盘" --content "本周..."
```

**要点**：先从 `task list` / `task counts` 拿占用/完成情况，日报可选但写会涨积分；复盘建议落成笔记而非只口头输出。

---

## 场景 2：最近阅读内容分析

**触发**：用户说"最近有啥文章值得看""总结下这周的行业趋势""分析最近新增的文章"。

**流程（先初筛，后深读）**：

```bash
# 1. 圈定时间段 + 初筛（simple：只要标题/时间/字数，省流）
$CLI article list --status all --mode simple --time-range 7d --page-count 30
# 或按日期范围
$CLI article list --status all --mode simple --start-date 2026-08-01 --end-date 2026-08-14

# 2. 对挑中的文章取正文做分析（full：带 content/plain_text）
$CLI article list --status all --mode full --feed-id 12 --page-count 10
# 或单篇读
$CLI article show 1001
# 真正要读全文 → 打开文章对象里的 url 原文
```

**要点**：

- 初筛必须 `--mode simple`，避免拉大量冗余正文。
- **默认把 `content` 当全文分析**；只有当它明显是摘要（`plain_text` 很短，或带截断标志，或用户要完整全文）时，才用文章对象的 `url` 抓原文（优先 web_fetch，抓不到退回摘要）。可先看 `ai_profile` 的摘要/标签做价值判断。
- 想按主题聚合，用 `--view-mode` / `--primary-category` / `--min-quality-score` 做 AI 筛选。

---

## 场景 3：订阅整理 / 阅读偏好

**触发**：用户说"整理下我的订阅""找找新 RSS""配置我的阅读偏好""生成阅读汇合"。

**流程**：

```bash
# 1. 看当前订阅 + 分类
$CLI feed-list

# 2. 发现新源
$CLI feed-explore
$CLI feed-search --name "前端"
$CLI feed-check-url "https://example.com/feed.xml"   # 校验后再订阅

# 3. 治理订阅（启停/改名/清空/排序）
$CLI feed-toggle-status 12 --enable 0   # 停用某个噪音源
$CLI feed-clear-articles 12             # 清空某源历史文章
$CLI feed-refresh-all --timeout 180     # 全量刷新（重操作，务必给长超时）

# 4. 阅读偏好 + 汇合页
$CLI digest-get-profile
$CLI digest-save-profile --data '{"topics":["LLM"],"frequency":"weekly","time_window_days":7,"enabled":true}'
$CLI digest-pages
$CLI digest-generate   # 手动立即生成
```

**要点**：`digest pages` / `digest generate` 有白名单前提；治理订阅大多是破坏性操作，先 `feed-list` 确认目标 id 再动手。

---

## 场景 4：阅读 → 第二大脑（积累）

**触发**：用户说"把这篇存起来""划线保存""根据文章做笔记"。

**流程**：

```bash
# 1. 划线摘录（只看不存库）
$CLI article mark 1001 --content "关键观点"

# 2. 转成来源关联笔记（source_type=2 文章，正文带 [[记录文章]] 预填）
$CLI article clip 88 --note --content "我的理解和总结" --tag 阅读

# 3. 生成 AI 阅读页（可选）
$CLI article ai-render 1001 --article-sub-id 88 --template-style magazine

# 4. 口述灵感 → 语音笔记
$CLI note-record ./idea.mp3 --content "读到一篇讲 agent 的文章"
```

**要点**：`article clip --note` 是划线→笔记的捷径；`note-record` 只支持 MP3；笔记创建会触发积分/成就。

---

## 场景 5：学习打卡闭环

**触发**：用户说"学习打卡""今天学点啥""更新我的学习计划"。

**流程**：

```bash
# 1. 看学习总览和计划
$CLI study-overview [--date 2026-08-15]
$CLI study-plans

# 2. 生成学习任务（按日期范围）
$CLI study-generate --date-from 2026-08-15 --date-to 2026-08-20
$CLI study-plan-generate 12 --date-from 2026-08-15 --date-to 2026-08-20

# 3. 给学习任务打卡（task 需 mode=3）
$CLI study-checkin 88 --date 2026-08-15 --content "完成第三章"

# 4. 复盘笔记（可选）
$CLI note create --title "学习复盘" --content "..."
```

**要点**：`study-checkin` 只支持文字，且目标是学习任务（`mode=3`）；多媒体打卡走 `request`。生成任务常用批量，先确认计划再执行。

---

## 兜底

- 单域请求直接看对应的 `references/{tasks,notes,articles,study,platform}.md`。
- 领域命令未覆盖的字段/动作，用 `$CLI request METHOD /path --data '...'` 兜底。
