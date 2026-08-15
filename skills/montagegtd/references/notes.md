# Note CLI

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
montage note list --keyword "会议" --output table
```

创建带标题、正文和标签的笔记：

```bash
montage note create \
  --title "日复盘" \
  --content "上午被会议打断，下午完成文章阅读页优化。" \
  --tag 复盘 \
  --tag 工作
```

长正文可从文件或标准输入读取：

```bash
montage note create --title "会议纪要" --content-file ./meeting.md
printf '%s\n' "临时想法" | montage note create --content -
```

创建来源关联笔记：

```bash
montage note create \
  --content "这篇文章的关键点是先建立输入系统" \
  --source-type 2 \
  --source-id 1001
```

查看详情：

```bash
montage note show 45
```

更新笔记：

```bash
montage note update 45 --title "更新后的标题" --content "完整正文" --status 1
```

删除笔记：

```bash
montage note delete 45
```

## 语音记录

把口述灵感落库为一条带音频的笔记，只支持 MP3：

```bash
montage note record ./meeting.mp3 --title "会议口述" --tag 工作
montage note record ./idea.mp3 --content "临时想法"
```

行为：

- 上传 MP3（`audio/mpeg` 等），再用返回的 `record_name` 建笔记。
- 音频文件必须本地上存在；`--content` / `--title` 至少一个非空。
- 触发笔记积分/成就评估。

## 来源预填模板

创建 `source_type` 关联笔记时，后端会预填一段标记正文（不是空笔记）：

- `1` 专注：`[[记录专注]]` + 专注名 + 开始时间。
- `2` 文章：`[[记录文章]]` + 文章标题 + 站内/原文地址。
- `3` 任务：`[[记录待办]]` + 任务模式 + 父任务 + 任务名。
- `4` 手账：`[[记录手账]]` + 手账名 + 时间区间。

所以"从专注/任务/文章/手账转成复盘笔记"可以直接 `note create --source-type N --source-id ID`，正文缺省也自带来源信息。

## 公开与审核

- `status=2` 记公开，但**仍需 `audit_status=1` 审核通过**才会进入他人可见列表。
- 笔记详情接口 `GET /notes/{id}` 仅作者可读；更新/删除也是作者权限。
- 你"把笔记设为公开/分享"时返回值不等于"别人立刻能看到"，要向用户说明审核前提。

## 字段

创建：

- `name` / `--title`：可选标题。
- `content`：正文。创建时标题和正文至少有一个非空。
- `status`：`1` 私有，`2` 公开；CLI 创建默认 `1`。
- `tags` / `--tag`：标签名数组，可重复传入。
- `add_image`：可选图片附加信息。
- `fname`：可选语音文件名。
- `source_type` / `source_id`：可选来源关联。

来源类型：

- `1`：专注。
- `2`：文章。
- `3`：任务。
- `4`：手账。

查询：

- `keyword`：关键词。
- `add_content`：是否带正文内容。
- `tag_id`：标签。
- `source_type` / `source_id`：来源。
- `type`：平台原有分类。

## 判断规则

- 用户说“记一下、保存一条、做个记录”时用 `note create`。
- 更新接口是完整内容更新：必须同时提供 `status`，并提供最终标题/正文，不要假设 PATCH 语义。
- 用户说“提醒我、帮我做、安排”时不要创建笔记，应该转成任务。
- 用户要求“从这篇文章摘出来保存”时，如果是文章划线用 `article mark`；如果是个人理解或总结用 `note create` 并带来源。
- 创建笔记可能触发积分和成就评估。
- 音频接口只支持 MP3，`GET /notes/{id}/record` 返回文件，不是 JSON。
