---
name: montage-gtd
description: Montage GTD 平台能力使用 skill。用于通过 PAT/UAT 鉴权和 CLI 处理个人 GTD 工作流：待办任务的收集、拆分、排序、开始、完成、复盘；笔记的记录、检索、更新、来源绑定；文章的未读队列、已读/稍后读/收藏、划线摘录、AI 阅读页生成；订阅源、专注、计划、学习、日报、LLM 会话、摘要页和个人访问令牌管理。用户提到“待办、任务、笔记、文章、阅读、订阅、专注、计划、复盘、日报、摘要、token、PAT、CLI、Montage”时优先使用。
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

## 运行配置

```bash
export MONTAGE_GTD_BASE_URL="https://pretask.congcong.us/api/v2"
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

## 常用能力入口

```bash
# 看当前 token 对应用户
skills/montage-gtd/scripts/montage_cli.py me

# 待办
skills/montage-gtd/scripts/montage_cli.py task-list --status 1
skills/montage-gtd/scripts/montage_cli.py task-create --name "整理 inbox" --mode 1 --priority 2
skills/montage-gtd/scripts/montage_cli.py task-doing 123
skills/montage-gtd/scripts/montage_cli.py task-complete 123 --rating 5 --review-note "按计划完成"

# 笔记
skills/montage-gtd/scripts/montage_cli.py note-list --keyword "会议"
skills/montage-gtd/scripts/montage_cli.py note-create --name "今天复盘：..." --status 1
skills/montage-gtd/scripts/montage_cli.py note-show 45

# 文章
skills/montage-gtd/scripts/montage_cli.py article-list --status unread --page-count 10
skills/montage-gtd/scripts/montage_cli.py article-status 88 --status read
skills/montage-gtd/scripts/montage_cli.py article-mark --article-id 1001 --content "关键观点"
skills/montage-gtd/scripts/montage_cli.py article-ai-render 1001 --article-sub-id 88

# 其他平台能力
skills/montage-gtd/scripts/montage_cli.py focus-start
skills/montage-gtd/scripts/montage_cli.py plan-create --name "本周输出计划"
skills/montage-gtd/scripts/montage_cli.py daily-summary-create --summary-date 2026-06-29 --work-content "..."
```

## 需要读取哪个参考

- 用户要处理待办、任务状态、优先级、截止时间、复盘：读 `references/tasks.md`。
- 用户要处理笔记、搜索、来源绑定、语音记录：读 `references/notes.md`。
- 用户要处理文章阅读队列、状态、摘录、AI 阅读页、订阅源：读 `references/articles.md`。
- 用户要处理鉴权、PAT、专注、计划、日报、学习、LLM、摘要页、通用请求：读 `references/platform.md`。

## 能力封装策略

新增 CLI 命令时遵循这几个规则：

- 命令名按业务动作命名，例如 `task-complete`、`article-mark`、`daily-summary-create`。
- 高频稳定字段做成参数；不稳定复杂字段保留 `--data` JSON。
- 业务命令必须比裸 API 更不容易误用，例如文章状态命令显式使用 `article_sub_id`。
- 保留 `request` 作为兜底，不把所有 OpenAPI 端点都写进主文档。
