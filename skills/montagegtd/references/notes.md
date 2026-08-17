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
- 公开笔记支持免登录分享链接 `GET /notes/share/{token}`：随机码定位（不用笔记 ID），未审核的公开笔记也能分享；分享带随机密码，URL 需带 `?key=`，无 key/错误 key 时显示密码输入页。列表页/管理页的"复制分享链接"按钮调用 `POST /api/v2/notes/{id}/share` 生成链接并复制（每次生成都会重置随机密码，旧 key 失效）。
- 后台 `/admin/notes` 可审核公开笔记（审核通过/撤销审核，`audit_status` 0↔1）。

## 发布后展示地址（必做）

**每次创建/发布笔记后，给用户反馈可查看的地址**，这是收尾的固定动作，不要只报 id。分两种情况：

### 私有笔记（默认 `status=1`）
- **没有对外查看链接**：详情接口仅作者可读，`POST /notes/{id}/share` 会返回 `code 1001，仅公开笔记可生成分享链接`（实测确认）。
- 反馈话术：`已发布笔记 id=1058（私有，作者本人可查看；无公开链接）。需要别人能看就把笔记设为公开再生成分享链接。`

### 公开笔记（`status=2`）
- 生成免登录分享链接（管理页"复制分享链接"同款接口）：
```bash
# 1. 先设为公开（CLI 创建默认私有）
montage note update <id> --status 2
# 2. 生成分享链接（仅公开笔记可生成；每次生成重置随机密码，旧 key 失效）
montage request POST /notes/<id>/share
```
- 返回值含完整 `url`（`https://task.congcong.us/notes/share/{token}?key={key}`）以及 `token`、`password` 字段——**直接展示返回的 `url` 即可，无需手工拼接**（实测确认）。
- **必须同时说明审核前提**：公开笔记仍需后台 `audit_status=1` 审核通过才会进入他人可见列表；未审核的公开笔记只能通过上述分享链接查看（分享页带密码，无 key/错误 key 显示密码输入页）。
- 反馈话术：`已发布笔记 id=1058（公开，待审核）。分享链接：<share 返回的 url>（审核通过前仅持链接+密码者可看）`
- 每次调用分享接口都会重置随机密码，旧 key 立即失效——重复展示地址时以**最新一次生成**的为准。

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
