# Article CLI

## 使用心智

文章能力服务于阅读队列管理：发现、筛选、阅读、标记状态、摘录、生成 AI 阅读页。要特别区分：

- `article_id`：文章正文。
- `article_sub_id`：用户订阅阅读记录，文章状态挂在这里。

用户要“标记已读/稍后读/收藏”时，通常需要 `article_sub_id`，不是 `article_id`。

## 阅读队列

列出未读：

```bash
montage article list --status unread --page-count 20 --mode simple
```

### simple vs full：如何明确命中

`--mode` 只决定一件事：**是否返回文章正文字段**（`content` / `formatted_content` / `plain_text`）。除此之外的元数据（id、subject、url、image_url、published、word_count、estimated_read_minutes、feed、ai_profile、ai_render_state）**两种 mode 都返回**。默认 `simple`。

按场景选 mode：

| 场景 | 用哪个 mode | 说明 |
| --- | --- | --- |
| 初筛 / 扫列表 / 按标题挑文章 | `simple`（默认） | 只要标题和元数据，省流量、快 |
| 统计"最近时间段新增了哪些文章" | `simple` | 先用 `--time-range`/`--start-date`/`--end-date` 圈定，再按标题/字数初筛 |
| 找"稍后读/收藏"清单 | `simple` | 列表操作不需要正文 |
| 基于正文做分析 / 总结 / 归类 | `full` | 必须显式 `--mode full`，否则拿不到正文 |
| 提取正文做 AI 阅读页素材 / 存笔记 | `full` | 需要 `plain_text` 清洗后的正文 |

**决策规则（优先遵守）：**

- 用户只要"看看有什么/列出标题/最近新增"→ 默认 `simple`，**不要盲目加 `--mode full`**，避免拉大量冗余正文。
- 用户要做"总结这些文章、分析内容、找观点、判断是否值得读全文"→ **主动加 `--mode full`**，因为只有这样文章才带 `content` 和 `plain_text`。
- 数量较大（`--page-count` 高）且没有内容分析需求时，绝不用 `full`，否则 payload 会非常大。

> **默认当全文处理，判断是摘要才抓原文。** `full` 的 `content` / `show` 返回的正文**默认就当它是全文**来做分析，不要遇事就抓 URL（省一次网络抓取）。
> 只有当 `content` 明显是摘要时才去取原文：
> - 正文很短（如几十到几百字符，`plain_text` 长度可判断）；
> - 或结尾有"阅读全文/查看原文/…更多"这类截断标志；
> - 或用户明确说"读完整全文""文章太短，看原文"。
> 此时用文章对象的 `url` 字段（`article.url`）抓原文：优先用 web_fetch 抓 URL 转文本；抓不到再退回 `content` 的摘要做分析。做文本分析用 `plain_text`（去标签清洗），原始 `content` 是 HTML。

> 注意：`full` 模式返回的 `content` 是原始 HTML，`plain_text` 是去标签清洗后的文本——做文本分析优先用 `plain_text`。

文章列表支持与 `/article/index` 对齐的筛选参数：`--status`（包含 `all`）、`--category-id`、`--feed-id`、`--keyword`、`--time-range`、`--start-date`、`--end-date`、`--read-duration`、`--page-count`，以及 AI 筛选 `--view-mode`、`--primary-category`、`--min-quality-score`。

状态可选：

- `unread`：未读。
- `read`：已读。
- `read_later`：稍后读。
- `star`：收藏。

按订阅源或分类筛选：

```bash
montage article list --status unread --feed-id 12
montage article list --status unread --category-id 3
montage article by-feed 12 --page-count 20
```

查看文章：

```bash
montage article show 1001
montage article reader 1001 --article-sub-id 88
```

### 典型组合：先初筛，再深读

"总结最近 N 天新增文章" / "分析一段时间内的文章" 的正确做法是**分两步**：

```bash
# 第 1 步：圈定时间段 + 初筛（simple，只要标题/时间/字数，省流）
montage article list --status all --mode simple --time-range 7d --page-count 30
montage article list --status all --mode simple --start-date 2026-08-01 --end-date 2026-08-14 --page-count 50

# 第 2 步：对挑中的文章用 full 取正文，做内容分析
montage article list --status all --mode full --feed-id 12 --page-count 10 --read-duration long
# 或单篇深读
montage article show 1001
```

时间筛选参数：`--time-range`（all/3h/6h/1d/3d/7d/custom）、`--start-date`+`--end-date`（配合 `--time-range custom`）、`--read-duration`（all/short/medium/long，筛掉短视频类）。

判断规则：

- "最近有哪些新文章" → `--time-range 7d` 或 `--start-date/--end-date` + `--mode simple`。
- "这些文章讲什么" → 对上一步的结果用 `--mode full` 或 `article show` 取正文再分析。

## 状态流转

单篇状态：

```bash
montage article status 88 read
montage article status 88 read_later
montage article status 88 star
```

批量状态：

```bash
montage article batch-status read --ids 88,89,90
montage article batch-status read --feed-id 12
```

判断规则：

- “读完了/标记已读” -> `read`。
- “稍后再看/加入待读” -> `read_later`。
- “收藏/重要” -> `star`。
- “恢复未读” -> `unread`。
- 标记为已读可能触发积分。

## 摘录和 AI 阅读页

创建文章摘录：

```bash
montage article mark 1001 --content "这个观点可以用于任务拆解"
```

生成 AI 阅读页：

```bash
montage article ai-render 1001 --article-sub-id 88 --template-style magazine
```

强制重新生成：

```bash
montage article ai-render 1001 --article-sub-id 88 --force
```

查看已有 AI 渲染状态：

```bash
montage article ai-show 1001 --article-sub-id 88
```

查看订阅导航和数量：

```bash
montage article nav --status unread
montage article counts --status unread
```

删除个人阅读记录，参数必须是 `article_sub_id`：

```bash
montage article delete 88
```

判断规则：

- 用户说“划线、摘录、标注这句话”用 `article mark`。
- 用户说“生成阅读页、AI 整理、沉浸阅读”用 `article ai-render`。
- 如果用户只给文章 ID，生成阅读页时尽量先查 `article_sub_id`；查不到再请求用户提供。

## 订阅源

列出订阅：

```bash
montage feed-list
```

刷新订阅：

```bash
montage feed-refresh 12
```

快速添加订阅（后端契约要求 `--feed-id`）：

```bash
montage feed-quickstore --feed-id 12
```

复杂字段用 `--data`：

```bash
montage feed-quickstore --data '{"feed_id":12}'
```

> 注意：`feed-quickstore` 接收的是 `feed_id` 而不是 URL。要按 URL 添加新源，先用 `feed check-url <url>` 校验标题，再通过全量 store/其它入口完成。

## 订阅管理

查看单源详情、全量刷新、改订阅名/分类/排序、启停、清空文章、排序、导入 OPML：

```bash
# 单源详情（含分类与 total/unread/starred 统计）
montage feed-show 12

# 全量刷新所有订阅（返回该次刷新结果）
# 注意：这是重操作，订阅多时可能超过默认 30s 超时，务必加 --timeout
montage feed-refresh-all --timeout 180

# 更新订阅名/分类/顺序（后两者必填）
montage feed-update 12 --name "新的订阅名" --category-id 3

# 启停订阅：--enable 0=停用 1=启用；省略则切换
montage feed-toggle-status 12 --enable 0
montage feed-toggle-status 12           # 切换

# 清空该源的所有文章
montage feed-clear-articles 12

# 排序：feed_sub_ids 为逗号分隔的订阅 id 序列（必填）
montage feed-sort --feed-sub-ids 12,9,5

# 导入 OPML 订阅列表（上传 .opml/.xml 文件）
montage feed-import-opml ./feeds.opml
```

判断规则：

- 用户说"刷新所有订阅/把订阅都刷一遍" → `feed-refresh-all`。
- 用户说"停用/启用某个订阅、先暂停这个源" → `feed-toggle-status <id> [--enable 0|1]`。
- 用户说"清空这个源的文章" → `feed-clear-articles <id>`。
- 用户说"改这个订阅的名字/分类" → `feed-update <id> --name ... --category-id N`。
- 用户提供 OPML 文件让批量导入 → `feed-import-opml <文件>`。

## 订阅发现

```bash
# 推荐源列表
montage feed-explore

# 按名字或推荐分类搜索订阅源（两者至少一个）
montage feed-search --name "前端"
montage feed-search --recommend-category-id 3

# 校验一个订阅 URL，返回页面标题
montage feed-check-url "https://example.com/feed.xml"
```

## Digest 汇合页（按兴趣自动生成阅读汇合）

Digest 把"最近若干天、符合我兴趣主题的文章"归纳成一篇汇合页。**前提是有白名单资格**；非白名单用户调用 `digest pages` / `digest generate` 会失败。

查看 / 配置兴趣 profile：

```bash
montage digest-get-profile
montage digest-save-profile --data '{
  "topics": ["LLM", "前端工程"],
  "include_keywords": ["agent"],
  "exclude_keywords": ["广告"],
  "preferred_categories": ["tech"],
  "time_window_days": 7,
  "frequency": "weekly",
  "max_articles": 20,
  "enabled": true
}'
```

> profile 字段名没有 `_json` 后缀，是 `topics`、`include_keywords`、`exclude_keywords`、`preferred_categories`。数组用 `--data` 传；`time_window_days` ∈ {1,3,7}，`frequency` ∈ {daily,weekly}，`max_articles` ∈ [5,50]。

查看生成的汇合页 / 手动立即生成：

```bash
montage digest-pages --page-count 10
montage digest-show-page 45
montage digest-generate   # 手动立即生成一次
```

判断规则：

- 用户说"配置我的阅读偏好/每周给我一份阅读汇合" → `digest-save-profile`。
- 用户说"看我的阅读汇合/最近生成的文章" → `digest-pages` / `digest-show-page`。

## Article → Note 摘录流

`article clip` 让"读到好内容直接转成笔记"更顺：

```bash
# 只做划线摘录（等价 article mark 的语义）
montage article clip 88 --content "这个观点可以用于任务拆解"

# 存成一条来源关联笔记（source_type=2=文章）
montage article clip 88 --note --content "我的理解和总结" --tag 阅读
```

行为：

- 入参默认是 `article_sub_id`。`article clip` 会自动从中解析 `article_id`；也可显式给 `--article-id`。
- 不带 `--note`：退化为文章划线（要求 `--content`）。
- 带 `--note`：把 `--content` 保存为一条 `source_type=2`、`source_id=article_id` 的笔记，正文走 `[[记录文章]]` 预填模板，并触发积分/成就。

判断规则：

- 用户说"划线/摘录这句话" → `article clip <sub_id> --content ...`（或直接 `article mark`）。
- 用户说"把这篇转成笔记/存成我的理解" → `article clip <sub_id> --note --content ...`。
