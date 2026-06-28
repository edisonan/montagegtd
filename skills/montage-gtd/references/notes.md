# 笔记能力

## 使用心智

笔记用于记录知识、想法、会议纪要、复盘片段、文章摘录和来源关联内容。处理笔记时不要把它当任务：笔记重在保存信息，任务重在推进动作。

常见意图：

- 记录：把用户给出的内容保存为笔记。
- 检索：按关键词、标签、来源查找。
- 整理：更新标题/正文、删除过期笔记。
- 关联：把笔记挂到文章、课程、任务等来源上。
- 语音：上传 MP3 后把 `record_name` 作为 `fname` 创建笔记。

## 常用命令

搜索笔记：

```bash
montage_cli.py note-list --keyword "会议" --add-content 1
```

创建普通笔记：

```bash
montage_cli.py note-create --name "今天复盘：上午被会议打断，下午完成文章阅读页优化。" --status 1
```

创建来源关联笔记：

```bash
montage_cli.py note-create \
  --name "这篇文章的关键点是先建立输入系统" \
  --status 1 \
  --source-type 1 \
  --source-id 1001
```

查看详情：

```bash
montage_cli.py note-show 45
```

更新笔记：

```bash
montage_cli.py note-update 45 --name "更新后的笔记内容" --status 1
```

删除笔记：

```bash
montage_cli.py note-delete 45
```

## 字段

创建：

- `name`：必填。当前后端把它作为主要笔记内容/标题字段。
- `status`：必填。沿用平台状态。
- `add_image`：可选图片附加信息。
- `fname`：可选语音文件名。
- `source_type` / `source_id`：可选来源关联。

查询：

- `keyword`：关键词。
- `add_content`：是否带正文内容。
- `tag_id`：标签。
- `source_type` / `source_id`：来源。
- `type`：平台原有分类。

## 判断规则

- 用户说“记一下、保存一条、做个记录”时用 `note-create`。
- 用户说“提醒我、帮我做、安排”时不要创建笔记，应该转成任务。
- 用户要求“从这篇文章摘出来保存”时，如果是文章划线用 `article-mark`；如果是个人理解或总结用 `note-create` 并带来源。
- 创建笔记可能触发积分和成就评估。
- 音频接口只支持 MP3，`GET /notes/{id}/record` 返回文件，不是 JSON。
