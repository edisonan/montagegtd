---
name: montage-gtd
description: Use the Montage GTD CLI with PAT/UAT authentication to manage personal tasks and notes, including task capture, listing, prioritization, scheduling, start/stop, completion, review and archiving; note creation, search, tags, source binding, updates and deletion. Also supports articles, feeds, focus, plans, daily summaries, tokens and generic API requests. Use when the user mentions Montage, GTD, tasks, todos, notes, CLI automation, PAT, reading queues, focus, plans or daily reviews.
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
export MONTAGE_GTD_BASE_URL="http://testtask.congcong.us/api/v2"
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
CLI=skills/montage-gtd/scripts/montage
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
$CLI article list --status unread --page-count 10
$CLI article status 88 read
$CLI article mark 1001 --content "关键观点"
```

使用 `--output table` 做人工查看；默认 JSON 适合 Codex 和脚本解析。

## 读取参考

- 处理待办时读 `references/tasks.md`。
- 处理笔记时读 `references/notes.md`。
- 处理文章或订阅时读 `references/articles.md`。
- 处理鉴权、PAT、专注、计划、日报、学习、LLM 或通用请求时读 `references/platform.md`。

## 执行约束

- 优先使用 `task ...` 和 `note ...` 分组命令；旧扁平命令只用于兼容。
- 查询可直接执行。写操作要确保目标、字段和 ID 明确。
- 批量或破坏性操作先列出候选项；用户已给明确 ID 时直接执行。
- 不显示完整 token。
- 非 2xx 或响应 `code != 9999` 视为失败。
- 领域命令未覆盖时使用 `request METHOD /path --data ...`。
- 最终反馈业务结果和对象 ID，不要只粘贴原始响应。
