# 文章和订阅能力

## 使用心智

文章能力服务于阅读队列管理：发现、筛选、阅读、标记状态、摘录、生成 AI 阅读页。要特别区分：

- `article_id`：文章正文。
- `article_sub_id`：用户订阅阅读记录，文章状态挂在这里。

用户要“标记已读/稍后读/收藏”时，通常需要 `article_sub_id`，不是 `article_id`。

## 阅读队列

列出未读：

```bash
montage_cli.py article-list --status unread --page-count 20
```

状态可选：

- `unread`：未读。
- `read`：已读。
- `read_later`：稍后读。
- `star`：收藏。

按订阅源或分类筛选：

```bash
montage_cli.py article-list --status unread --feed-id 12
montage_cli.py article-list --status unread --category-id 3
```

查看文章：

```bash
montage_cli.py article-show 1001
montage_cli.py article-reader 1001 --article-sub-id 88
```

## 状态流转

单篇状态：

```bash
montage_cli.py article-status 88 --status read
montage_cli.py article-status 88 --status read_later
montage_cli.py article-status 88 --status star
```

批量状态：

```bash
montage_cli.py articles-status --ids 88,89,90 --status read
montage_cli.py articles-status --feed-id 12 --status read
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
montage_cli.py article-mark --article-id 1001 --content "这个观点可以用于任务拆解"
```

生成 AI 阅读页：

```bash
montage_cli.py article-ai-render 1001 --article-sub-id 88 --template-style magazine
```

强制重新生成：

```bash
montage_cli.py article-ai-render 1001 --article-sub-id 88 --force
```

判断规则：

- 用户说“划线、摘录、标注这句话”用 `article-mark`。
- 用户说“生成阅读页、AI 整理、沉浸阅读”用 `article-ai-render`。
- 如果用户只给文章 ID，生成阅读页时尽量先查 `article_sub_id`；查不到再请求用户提供。

## 订阅源

列出订阅：

```bash
montage_cli.py feed-list
```

刷新订阅：

```bash
montage_cli.py feed-refresh 12
```

快速添加订阅：

```bash
montage_cli.py feed-quickstore --url "https://example.com/feed.xml"
```

复杂订阅字段用 `--data`：

```bash
montage_cli.py feed-quickstore --data '{"url":"https://example.com/feed.xml","category_id":3}'
```
