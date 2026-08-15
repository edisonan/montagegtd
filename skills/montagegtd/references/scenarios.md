# 端到端使用场景

本文串联多个能力域，给出**可以直接照着跑**的真实工作流。遇到复合型请求时，先看下面哪个场景匹配，再按流程执行；找不到匹配时退回各领域 reference 单独处理。

判断入口：

- 复盘/回顾一段时间的任务 → 场景 1
- 最近阅读内容分析/梳理 → 场景 2
- 看订阅/整理阅读偏好 → 场景 3
- 把阅读/想法沉淀成第二个大脑 → 场景 4
- 学习打卡闭环 → 场景 5
- 最近几小时文章热点/关注点/建议阅读 → 场景 6
- 近期收藏/稍后读的个性化盘点 → 场景 7

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

## 场景 6：最近 N 小时文章热点分析

**触发**：用户说"最近几小时有啥热点""看看这一小时/半天该关注什么""推荐我读几篇"。

**目标**：对最近一段时间（默认 **6 小时**，可换成 3h/1d/3d）的文章做聚合分析，产出**热点归类 + 关注点 + 建议阅读**。**纯输出给用户，不落库**。

**流程**：

```bash
# 1. 圈定最近 6 小时 + 初筛（simple 即可：subject 标题已够判定热点；6h 没几条就放宽到 1d/3d）
$CLI article list --status all --mode simple --time-range 6h --page-count 50

# 2.（可选）只对挑中要深读的少数文章才取正文 / 抓原文，不要对全量做 full
$CLI article list --status all --mode full --time-range 6h --page-count 50
$CLI article show <article_id>
```

**分析（agent 归纳；判定顺序：标题为主 → 摘要受控截断为辅）**：

> **核心规则：热点/关注点判定尽量基于标题（`subject`）。** 摘要不要把完整原文丢给模型——很多订阅源摘要很长，且内容质量参差，直接灌给模型既浪费 token 又会让判断失真。需要参考摘要时**先截断再给**（每条 ≤100 字符，用来辅助确认主题，不追求完整语义）。

- **热点**：**优先按领域分桶**（先按 `feed.feed_name` 分大类——如技术/生活/财经——再按 `subject` 标题/关键词聚类）。不同领域的 feed（如技术博客 vs 社区生活帖）混在同一个"热点"里会很怪，应分开呈现。每类给"几篇、来自哪几个源"的聚合热度。主题判定以标题为主，除非标题存疑才看截断后的摘要。
- **关注点**：每篇一句话要点（**由标题提取**；标题不清才参考截断摘要）+ 一句"是否值得读"的判断。
- **建议阅读**：排一个优先序，标注为什么（时效新、话题贴合、字数适合通读）。
- **若正文是摘要**：按既定策略，需要时用 `article.url` 抓原文补全再做热点评。
- **`ai_profile` 可能为空**：新近抓取的文章常还未打 AI 画像（`ai_profile` 为 null，也无 primary_category/tags）。此时**不要依赖没用的画像字段**，直接用 `subject` 判定主题；想建立稳定偏好后才考虑 `digest-save-profile`。

**要点**：默认 `6h`；命太少就放大窗口（6h 常只有几条），命太多就加 `--feed-id`/`--view-mode`/`--read-duration long` 收缩。**不要拿完整 `content`/长摘要去喂模型做归纳，除非有深读需求**——热点/关注点判定用标题 + 截断摘要足够。输出多用**分层 Markdown**（按领域分组的热点→关注点→建议阅读），别平铺列表。

---

## 场景 7：近期收藏/稍后读盘点

**触发**：用户说"看看我收藏的/稍后读的""帮我个性化整理下加星的文章""最近标记的文章给我排个序"。

**目标**：对近一段时间（默认 **7 天**，可按 `1d/3d/7d`）收藏或稍后读的文章做**个性化分组展示**。

**流程**：

```bash
# 收藏（star）最近 7 天
$CLI article list --status star --mode simple --time-range 7d --page-count 100
# 或稍后读（read_later）
$CLI article list --status read_later --mode simple --time-range 7d --page-count 100

# 对命中的取正文做深度分组
$CLI article list --status star --mode full --time-range 7d --page-count 100

# 收藏是稀疏的：7d 常常 0 命中 → 放宽到全部收藏再盘点
$CLI article list --status star --mode simple --page-count 100
$CLI article list --status read_later --mode simple --page-count 100
```

**分析（agent 归纳）**：

- **按主题/分类分组**：把收藏按你关注的主题分桶，每组一句话概括"这批是什么"。
- **标注价值**：每篇标"为什么当时值得收藏"（话题、观点、待办用法），以及现在的阅读优先级（时间敏感度高的先读）。
- **建议阅读顺序**：按主题 + 时效给一个顺序；对已读完的可提示清理。

**要点**：

- 默认 `--status star --time-range 7d`；要"整理稍后读"就换 `--status read_later`。
- **收藏/稍后读是稀疏异步的**：不是每周都有，`7d` 常命中 0。7d 为空时**去掉 `--time-range` 放宽到全部收藏**再盘，别误报"没有收藏"。
- **status 计数 ≠ 有效文章数**：`article_subs` 可能含孤儿记录（对应 `article` 已删除的历史残留），`article list` 只返回 join 得上的有效文章。不能拿数据库里 status 的总数告诉用户"你有 N 篇可盘"，要以 `article list` 实际返回为准。
- 个性化分组依赖 `preferred_categories`/`ai_profile` 的标签做聚合，`ai_profile` 为空时内容不足就按 feed 分组。

---

## 兜底

- 单域请求直接看对应的 `references/{tasks,notes,articles,study,platform}.md`。
- 领域命令未覆盖的字段/动作，用 `$CLI request METHOD /path --data '...'` 兜底。
