# Article CLI

## 使用心智

文章能力服务于阅读队列管理：发现、筛选、阅读、标记状态、摘录、生成 AI 阅读页。要特别区分：

- `article_id`：文章正文。
- `article_sub_id`：用户订阅阅读记录，文章状态挂在这里。

用户要“标记已读/稍后读/收藏”时，通常需要 `article_sub_id`，不是 `article_id`。

## 阅读队列

列出未读：

```bash
montage article list --status unread --page-count 20
```

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

快速添加订阅：

```bash
montage feed-quickstore --url "https://example.com/feed.xml"
```

复杂订阅字段用 `--data`：

```bash
montage feed-quickstore --data '{"url":"https://example.com/feed.xml","category_id":3}'
```
