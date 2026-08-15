---
name: montagegtd
description: Use the Montage GTD CLI with PAT/UAT authentication to manage personal tasks and notes, including task capture, listing, prioritization, scheduling, start/stop, completion, review and archiving; note creation, search, tags, source binding, voice recording, updates and deletion. Also supports articles and feeds (reading queue, digest 汇合页, AI rendering, subscriptions), focus, study plans and checkins, journal 手账, mind maps, plans, daily summaries, achievements, points, courses and quizzes, tokens and generic API requests. Use when the user mentions Montage, GTD, tasks, todos, notes, articles, reading queue, digest, feeds, RSS, focus, study, plans, journal, mind map, achievements, points, courses, CLI automation, PAT, reading queues, daily reviews or daily summaries.
whenToUse: Trigger for phrases such as 新建/完成一个待办或任务, 列出我的待办, 找笔记/记一条笔记/把语音存成笔记, 标记文章已读或收藏, 生成文章 AI 阅读页, 配置我的阅读偏好/生成一份阅读汇合, 搜索或发现新订阅源, 开始/完成专注, 学习打卡/看我的学习计划, 记手账/写复盘, 画思维导图, 查看成就/积分或抽奖, 预览或学习一门课程, 写日报, 建一个 PAT, 用 CLI 操作 Montage. Query-only intents use a read-scope token; write/state changes use read+write; provider/model/credential management additionally needs admin.
---

# Montage GTD

## 这个 Skill 负责什么

把用户的自然语言请求转成 Montage GTD 平台动作。重点不是背接口，而是判断用户要处理哪类 GTD 能力、需要读还是写、应该直接调用哪个 CLI 命令、结果该如何反馈。

典型请求：

- “帮我新建一个明天 18 点截止的待办”
- “把 123 这个任务标成完成，并写复盘”
- “搜一下包含会议的笔记”
- “给这篇文章加一条摘录”
- “把这一批文章标记已读”
- “生成文章 AI 阅读页”
- “开始一次专注”
- “创建一个给 Codex 用的 PAT”

## 执行原则

1. 先识别业务域：待办、笔记、文章、订阅、专注、计划、学习、日报、LLM/摘要、令牌。
2. 再判断权限：查询用 `read`，新增/修改/状态流转用 `write`，模型/供应商/凭据管理用 `admin`。
3. 优先使用 `scripts/montage_cli.py` 的领域命令。
4. 领域命令不覆盖时，再使用 `request METHOD /path --data ...`。
5. 不向用户展示完整 token。必要时只说明 token 类型或脱敏前后缀。
6. 最终反馈要说业务结果，不要只贴 API 响应：例如“已创建任务，id=...”，“已标记 12 篇文章为已读”。

## 配置

```bash
export MONTAGE_GTD_BASE_URL="https://task.congcong.us/api/v2"
export MONTAGE_GTD_TOKEN="pat_or_uat_token"
```

兼容旧环境变量：

- `TASK_GITEE_BASE_URL`
- `TASK_GITEE_TOKEN`
- `TASK_GITEE_OPENAPI`

推荐用 PAT 做自动化：

- 只看数据：`read`
- 自动写入、状态流转、创建笔记/任务/文章状态：`read,write`
- 管理 LLM provider/model/credential：`read,write,admin`

CLI 入口：

```bash
CLI=skills/montagegtd/scripts/montage
```

## 首选命令

```bash
# 当前用户
$CLI me

# 待办
$CLI task list --status 1
$CLI task create --name "整理 inbox" --priority 2
$CLI task doing 123
$CLI task complete 123 --rating 5 --review-note "按计划完成"

# 笔记
$CLI note list --keyword "会议"
$CLI note create --title "日复盘" --content "今天完成了..."
$CLI note show 45

# 文章
$CLI article list --status unread --page-count 10            # 默认 simple：只要标题
$CLI article list --status unread --mode full --page-count 5 # full：带正文做内容分析
$CLI article status 88 read
$CLI article mark 1001 --content "关键观点"
```

## 扩展能力速查

```bash
# 阅读汇合 digest
$CLI digest-get-profile
$CLI digest-save-profile --data '{"topics":["LLM"],"frequency":"weekly","time_window_days":7}'
$CLI digest-pages
$CLI digest-generate

# 学习 study
$CLI study-overview [--date YYYY-MM-DD]
$CLI study-plans
$CLI study-checkin <task_id> --content "打卡内容"

# 语音笔记 / 文章摘录
$CLI note-record ./idea.mp3 --content "口述想法"
$CLI article-clip <article_sub_id> --note --content "我的总结"

# 订阅发现 / 手账 / 思维导图
$CLI feed-search --name "前端"
$CLI feed-refresh-all            # 全量刷新订阅
$CLI feed-toggle-status 12 --enable 0   # 启停订阅
$CLI journal-create --name "今日复盘"
$CLI mind-list

# 成就 / 积分 / 课程
$CLI achievement-list
$CLI points
$CLI course-list
```

使用 `--output table` 做人工查看；默认 JSON 适合 Codex 和脚本解析。

## 读取参考

- 处理待办时读 `references/tasks.md`。
- 处理笔记时读 `references/notes.md`。
- 处理文章或订阅时读 `references/articles.md`。
- 处理学习打卡/计划时读 `references/study.md`。
- 处理鉴权、PAT、专注、计划、日报、Digest、手账、思维导图、成就、积分或通用请求时读 `references/platform.md`。
- 遇到复合/端到端请求（复盘、阅读分析、订阅整理、知识沉淀、学习打卡）时优先读 `references/scenarios.md`。
- 遇到"最近几小时文章热点/关注点/建议阅读""收藏/稍后读盘点"这类**聚合分析**请求时，读 `references/scenarios.md` 的场景 6/7。

## 执行约束

- 优先使用 `task ...` 和 `note ...` 分组命令；旧扁平命令只用于兼容。
- 查询可直接执行。写操作要确保目标、字段和 ID 明确。
- 批量或破坏性操作先列出候选项；用户已给明确 ID 时直接执行。
- 不显示完整 token。
- 非 2xx 或响应 `code != 9999` 视为失败。
- 领域命令未覆盖时使用 `request METHOD /path --data ...`。
- 最终反馈业务结果和对象 ID，不要只粘贴原始响应。
