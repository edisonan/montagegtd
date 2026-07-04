# Note 模块逻辑梳理

本文档整理当前 Note/笔记模块的主要业务逻辑、入口、数据表和联动关系。代码依据为 `routes/web.php`、`routes/api.php`、`app/Http/Controllers/NoteController.php`、`app/Http/Controllers/Api/V2/NoteController.php`、`app/Services/NoteService.php`、`app/Repositories/NoteRepository.php`、`app/Models/Note.php`、`app/Models/NoteTagMap.php` 和当前本地数据库结构。

## 1. 模块定位

Note 用于记录想法、知识片段、文章摘录、任务/专注/手账关联想法、语音记录和图片记录。当前实现里 `notes.name` 是可选标题，`notes.content` 是正文。

核心能力：

- 创建私密/公开笔记。
- Markdown 输入和渲染。
- 按关键词、标签、来源筛选笔记。
- 从专注、文章、任务、手账等来源预填记录内容。
- 上传 MP3 语音并在笔记中关联播放。
- 附加图片 URL。
- 公开笔记进入他人可见列表，但需要审核通过。
- 创建笔记后触发积分和成就评估。

## 2. 入口和路由

### 2.1 Web 页面入口

`routes/web.php`：

| 方法 | 路径 | 控制器 | 用途 |
| --- | --- | --- | --- |
| GET | `/notes` | `NoteController@index` | 笔记列表/创建页面 |
| GET | `/notes/add_content/{add_content}` | `NoteController@index` | 旧的预填内容入口，当前页面主要靠查询参数处理 |
| GET | `/notes/{note}/edit` | `NoteController@update` | 编辑页 |
| POST | `/note` | `NoteController@store` | 旧 Web 表单创建 |
| DELETE | `/note/{note}` | `NoteController@destroy` | 旧 Web 删除 |
| POST | `/notes/upload` | `NoteController@upload` | 旧 Web 音频上传 |
| GET | `/note/getRecord/{note}` | `NoteController@getRecord` | 旧 Web 音频读取 |
| GET | `/note/welcome` | `NoteController@welcome` | 笔记介绍页 |

Web 控制器保留了旧表单兼容能力。现代前端页面 `resources/views/notes/index.blade.php` 和 `resources/views/notes/update.blade.php` 实际主要通过 `TaskApiBridge` 调用 `/api/v2/notes*`。

### 2.2 V2 API 入口

`routes/api.php` 下 `/api/v2`：

| 方法 | 路径 | 中间件 | 控制器 | 用途 |
| --- | --- | --- | --- | --- |
| GET | `/notes` | `hybrid.token:read` | `Api\V2\NoteController@index` | 列表、筛选、预填信息 |
| GET | `/notes/{note}` | `hybrid.token:read` | `show` | 笔记详情，仅作者可读 |
| GET | `/notes/{note}/record` | `hybrid.token:read` | `getRecord` | 音频文件读取，作者或公开笔记可读 |
| POST | `/notes` | `hybrid.token:write` | `store` | 创建笔记 |
| POST | `/notes/upload` | `hybrid.token:write` | `upload` | 上传 MP3 到临时目录 |
| PUT | `/notes/{note}` | `hybrid.token:write` | `update` | 更新正文和状态 |
| DELETE | `/notes/{note}` | `hybrid.token:write` | `destroy` | 删除笔记 |
| POST | `/notes/{note}/like` | `hybrid.token:write` | `like` | 点赞兼容接口，目前不落库 |

API 响应沿用统一结构：

```json
{
  "code": 9999,
  "msg": "ok",
  "result": {}
}
```

### 2.3 微信接口入口

`Api\V2\WechatController` 也有笔记相关接口：

| 方法 | 路径 | 用途 |
| --- | --- | --- |
| GET | `/api/v2/wechat/notes` | 查询当前用户最近 10 条笔记 |
| POST | `/api/v2/wechat/notes` | 创建笔记 |
| POST | `/api/v2/wechat/addNote` | 创建笔记，兼容路径 |

微信创建时要求 `content` 或旧字段 `name` 至少有一个非空，默认 `status=1`。代码里对布尔 status 做了兼容：`false` 转成 `2`，`true` 转成 `1`。

## 3. 核心代码分层

| 层 | 文件 | 责任 |
| --- | --- | --- |
| Web 控制器 | `app/Http/Controllers/NoteController.php` | 页面入口、旧表单兼容、旧音频接口 |
| V2 控制器 | `app/Http/Controllers/Api/V2/NoteController.php` | V2 JSON API、上传校验、积分/成就触发 |
| Service | `app/Services/NoteService.php` | 内容预格式化、创建/更新、标签解析、图片/音频处理 |
| Repository | `app/Repositories/NoteRepository.php` | 列表查询、统计查询、日报汇总查询 |
| Model | `app/Models/Note.php` | `notes` 模型和关系 |
| Model | `app/Models/NoteTagMap.php` | `note_tag_maps` 模型和关系 |
| Policy | `app/Policies/NotePolicy.php` | 作者权限判断 |
| Admin | `app/Admin/Controllers/NoteController.php` | 后台只读列表和按用户筛选 |

## 4. 主要业务流程

### 4.1 列表和筛选

请求入口：`GET /api/v2/notes`

参数：

| 参数 | 说明 |
| --- | --- |
| `type` | 预填内容类型，目前图片预填使用 `image` |
| `add_content` | 预填内容，可为普通文本、URL 或图片地址 |
| `source_type` | 来源类型 |
| `source_id` | 来源 ID |
| `tag_id` | 标签筛选 |
| `keyword` | 正文关键词筛选 |
| `page` | Laravel 分页参数 |

流程：

1. `Api\V2\NoteController@index` 读取查询参数。
2. 调用 `NoteService::getIndexInfo()`。
3. `getFormatContent()` 根据来源或预填内容生成默认正文。
4. `NoteRepository::getUserList()` 查询笔记列表。
5. 返回 `add_content`、`add_image`、`notes`、`source_type`、`source_id`，每条笔记带 `noteTagMaps.tag` 关系供前端展示标签。

列表可见性规则：

- 当前用户自己的笔记全部可见。
- 其他用户笔记只有 `status=2` 且 `audit_status=1` 才进入列表。
- 查询结果按 `id desc` 排序，每页 20 条。

### 4.2 来源预填逻辑

`NoteService` 定义了数字来源类型：

| 常量 | 值 | 含义 | 预填内容 |
| --- | --- | --- | --- |
| `NOTE_SOURCE_TYPE_POMO` | `1` | 专注/Pomodoro | `[[记录专注]]`、专注名称、开始时间、固定 20 分钟 |
| `NOTE_SOURCE_TYPE_ARTICLE` | `2` | 文章 | `[[记录文章]]`、文章标题、站内地址、原文地址 |
| `NOTE_SOURCE_TYPE_TASK` | `3` | 任务 | `[[记录待办]]`、任务模式、父任务、任务名、开始时间、固定 20 分钟 |
| `NOTE_SOURCE_TYPE_THING` | `4` | 手账/事项 | `[[记录手账]]`、手账名称、时间区间、持续分钟数 |

权限校验：

- 专注、任务、手账来源要求记录归当前用户所有。
- 文章来源只校验文章存在，没有按用户校验。

非来源预填逻辑：

- `type=image`：校验图片 MIME，正文预填为 `[[分享图片]]`，同时返回 `add_image`。
- `add_content` 是 URL：抓取页面 title，尝试短链，正文预填为 `[[分享链接]] {url} {title}`。
- `add_content` 是普通文本且不包含 `#`：正文预填为 `[[分享]]{content}`。

### 4.3 创建笔记

请求入口：`POST /api/v2/notes`

必填参数：

- `status`
- `content` 或旧兼容字段 `name` 至少一个非空；新客户端应使用 `content` 作为正文。

可选参数：

- `name`：可选标题。
- `tags`：独立标签输入，支持逗号、空格、换行、中文逗号/顿号分隔。
- `fname`
- `add_image`
- `source_type`
- `source_id`

流程：

1. 控制器校验 `status`，并确认标题、正文、语音或图片至少有一个有效内容。
2. 调用 `NoteService::store()`。
3. 如果有 `fname`，调用 `storeRecord()`，将 `recorders/temp/{userId}{fname}.mp3` 移到 `recorders/{userId}{fname}.mp3`，并保存相对路径。
4. 如果有 `add_image`，调用 `validateImage()` 校验图片 MIME 后保存 URL/路径。
5. `name` 作为标题保存；`content` 作为正文保存，并做 HTML 转义、保留 `<code></code>`、执行 `nl2br()`。
6. 保存 `notes` 记录。
7. 从独立 `tags` 参数解析标签。
8. 通过 `TagService::getByTagName($tagName, true)` 查找或创建标签。
9. 写入 `note_tag_maps`；不再从正文中解析 `#标签#`。
10. V2 控制器调用 `PointGrantService::grantByEvent()` 触发 `note_created` 积分。
11. V2 控制器调用 `AchievementAutoUnlockService::evaluateForUser()` 评估成就。

注意：Web 旧控制器创建笔记时不会触发 V2 控制器里的积分/成就逻辑。

### 4.4 更新笔记

请求入口：`PUT /api/v2/notes/{note}`

必填参数：

- `status`
- `content` 或旧兼容字段 `name` 至少一个非空；新客户端应使用 `content` 作为正文。

可选参数：

- `name`：可选标题。
- `tags`：独立标签输入。

流程：

1. 通过 `NotePolicy::destroy()` 校验作者权限。
2. `NoteService::update()` 重新格式化标题和正文。
3. 如果标题或正文有变化，将 `audit_status` 重置为 `0`。
4. 更新 `name/content/status`。
5. 如果请求包含 `tags`，先清理旧 `note_tag_maps`，再按独立标签输入重建关系。

### 4.5 删除笔记

请求入口：`DELETE /api/v2/notes/{note}`

流程：

1. 通过 `NotePolicy::destroy()` 校验作者权限。
2. 直接 `$note->delete()`。

当前 `Note` 模型没有 `SoftDeletes`，删除是硬删除。删除时没有显式清理 `note_tag_maps`、音频文件或图片文件。

### 4.6 音频上传和读取

上传入口：`POST /api/v2/notes/upload`

流程：

1. 请求必须包含文件字段 `file`。
2. Laravel 上传文件必须有效。
3. MIME 仅允许 `audio/mp3`、`audio/mpeg`、`audio/mpeg3`。
4. `fname` 来自请求参数。
5. 文件保存为 `{storage_path}/recorders/temp/{userId}{fname}.mp3`。
6. 返回 `record_name`。

创建笔记时：

- 前端把去掉 `.mp3` 后缀的值放进 `fname`。
- `NoteService::storeRecord()` 再按 `{userId}{fname}.mp3` 找临时文件并移动到 `{storage_path}/recorders/`。
- `notes.record_path` 保存为 `recorders/{userId}{fname}.mp3`。

读取入口：`GET /api/v2/notes/{note}/record`

读取规则：

- 作者可读。
- `status=2` 的公开笔记可读。
- 文件不存在时报 `语音文件不存在`。
- 返回文件响应，`Content-Type: audio/mpeg`。

### 4.7 点赞

请求入口：`POST /api/v2/notes/{note}/like`

当前只是兼容前端操作，返回：

```json
{
  "note_id": 1,
  "liked": true
}
```

没有点赞表，也没有计数落库。

## 5. 前端页面逻辑

### 5.1 列表页

文件：`resources/views/notes/index.blade.php`

主要能力：

- EasyMDE Markdown 编辑器。
- 快捷插入标签、代码块、标题等内容。
- 录音上传。
- 图片预览。
- 私密/公开创建按钮。
- URL 参数解析：`add_content`、`type`、`source_type`、`source_id`、`tag_id`、`keyword`。
- 调用 `GET /api/v2/notes` 加载列表。
- 调用 `POST /api/v2/notes` 创建笔记。
- 调用 `DELETE /api/v2/notes/{id}` 删除。
- 调用 `POST /api/v2/notes/{id}/like` 点赞兼容。
- 调用 `GET /api/v2/notes/{id}/record` 播放音频。

卡片渲染：

- 作者头像/名称。
- 公开/私密 badge。
- 相对时间。
- AI、复制、编辑/删除/点赞操作。
- Markdown 正文渲染。
- 音频播放器和图片预览。
- 长正文按 8 行折叠。

### 5.2 编辑页

文件：`resources/views/notes/update.blade.php`

主要能力：

- 从 URL 或服务端传入的 `note_id` 确定笔记 ID。
- 调用 `GET /api/v2/notes/{id}` 加载详情。
- 将后端保存的 `<br>` 转回换行，并解 HTML 实体后放入 EasyMDE。
- 调用 `PUT /api/v2/notes/{id}` 更新正文和状态。
- 显示已有音频，支持播放。
- 支持再次录音上传，但当前更新接口只更新 `name/content/status/tags`，不会把新的 `fname` 写回笔记。

### 5.3 其他页面入口

常见来源入口：

- 首页专注卡片：`/notes?source_type=1&source_id={focus_id}`。
- 首页任务卡片：`/notes?source_type=3&source_id={task_id}`。
- 文章列表：`/notes?source_type=2&source_id={article_id}`。
- 手账列表：`/notes?source_type=4&source_id={journal_id}`。
- LLM 页面可创建笔记，并传 `source_type: 'llm'`、`source_id: session_id`。

## 6. 权限和可见性

作者权限：

- `NotePolicy::destroy(User $user, Note $note)` 返回 `$user->id === $note->user_id`。
- 当前 show/update/destroy 共用这个 policy 方法名。

公开权限：

- 列表查询中，非本人公开笔记需要 `status=2` 且 `audit_status=1`。
- 音频读取中，公开笔记只判断 `status=2`，没有判断 `audit_status=1`。
- `GET /api/v2/notes/{note}` 详情接口只允许作者访问，不支持直接查看他人公开笔记详情。

状态含义：

| 字段 | 值 | 含义 |
| --- | --- | --- |
| `notes.status` | `1` | 私密 |
| `notes.status` | `2` | 公开 |
| `notes.audit_status` | `0` | 未审核/审核中 |
| `notes.audit_status` | `1` | 审核通过，可作为他人可见公开笔记进入列表 |

## 7. 标签逻辑

标签格式：

- 标签来自独立 `tags` 字段，不再从正文内容解析。
- 支持逗号、空格、换行、中文逗号/顿号、分号分隔。
- 输入中的 `#` 会被忽略，`#读书笔记#` 和 `读书笔记` 等价。

创建：

- 每个标签通过 `TagService::getByTagName($tagName, true)` 获取或创建。
- 写入 `note_tag_maps`。

更新：

- 请求包含 `tags` 时会先删除该笔记已有标签关系，再按输入重建。
- 请求不包含 `tags` 时保留已有标签关系。
- 标签解析会按标签名去重。

列表展示：

- 前端根据 `noteTagMaps.tag` 渲染标签，并链接到 `/notes?tag_id={tag_id}`。

## 8. 数据表

以下字段来自当前本地数据库 `SHOW COLUMNS`，比 `database/db.sql` 更新。`database/db.sql` 中 `notes` 表缺少 `source_type/source_id`，当前运行库已经存在这两个字段。

### 8.1 `notes`

笔记主表。

| 字段 | 类型 | Null | 默认 | Key | 说明 |
| --- | --- | --- | --- | --- | --- |
| `id` | `int(10) unsigned` | NO | NULL | PRI | 主键 |
| `user_id` | `int(11)` | NO | NULL | MUL | 创建用户 |
| `status` | `tinyint(4)` | NO | `1` |  | 1 私密，2 公开 |
| `name` | `text` | YES | NULL |  | 笔记标题，可为空 |
| `content` | `text` | YES | NULL |  | 笔记正文 |
| `record_path` | `varchar(300)` | YES | NULL |  | 音频相对路径 |
| `image_path` | `varchar(300)` | YES | NULL |  | 图片 URL/路径 |
| `task_id` | `int(11)` | YES | NULL |  | 旧任务来源字段，当前代码主要用 `source_type/source_id` |
| `pomo_id` | `int(11)` | YES | NULL |  | 旧专注来源字段 |
| `article_id` | `int(11)` | YES | NULL |  | 旧文章来源字段 |
| `source_type` | `tinyint(4)` | YES | NULL | MUL | 当前统一来源类型 |
| `source_id` | `int(11)` | YES | NULL |  | 当前统一来源 ID |
| `audit_status` | `tinyint(4)` | NO | `0` |  | 公开审核状态 |
| `created_at` | `timestamp` | YES | NULL |  | 创建时间 |
| `updated_at` | `timestamp` | YES | NULL |  | 更新时间 |

模型：`App\Models\Note`

关系：

- `user()`：`belongsTo(User::class)`。
- `noteTagMaps()`：`hasMany(NoteTagMap::class)`。

### 8.2 `note_tag_maps`

笔记和标签关系表。

| 字段 | 类型 | Null | 默认 | Key | 说明 |
| --- | --- | --- | --- | --- | --- |
| `id` | `int(10) unsigned` | NO | NULL | PRI | 主键 |
| `tag_id` | `int(11)` | NO | NULL |  | 标签 ID |
| `note_id` | `int(11)` | NO | NULL |  | 笔记 ID |
| `status` | `tinyint(4)` | NO | `1` |  | 1 启用，2 删除/禁用 |
| `created_at` | `timestamp` | YES | NULL |  | 创建时间 |
| `updated_at` | `timestamp` | YES | NULL |  | 更新时间 |

模型：`App\Models\NoteTagMap`

关系：

- `tag()`：`belongsTo(Tag::class)`。
- `note()`：`belongsTo(Note::class)`。

当前查询没有过滤 `note_tag_maps.status`。

### 8.3 `tags`

通用标签表，Note 通过 `note_tag_maps` 使用。

| 字段 | 类型 | Null | 默认 | Key | 说明 |
| --- | --- | --- | --- | --- | --- |
| `id` | `int(10) unsigned` | NO | NULL | PRI | 主键 |
| `name` | `varchar(255)` | NO | NULL |  | 标签名 |
| `status` | `tinyint(4)` | NO | `1` |  | 1 启用，2 禁用 |
| `created_at` | `timestamp` | YES | NULL |  | 创建时间 |
| `updated_at` | `timestamp` | YES | NULL |  | 更新时间 |

### 8.4 积分相关表

创建笔记通过事件 `note_created` 触发积分。

#### `point_rule`

积分规则表。`PointRuleSeeder` 中 note 规则为：

- `event_type=note_created`
- `name=创建笔记`
- `point_type=AP`
- `point_value=3`
- `daily_max_grants=10`

关键字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `event_type` | `varchar(64)` | 事件类型 |
| `point_type` | `varchar(16)` | 积分类型，如 AP/GP |
| `point_value` | `int(11)` | 单次奖励分值 |
| `daily_max_grants` | `int(11)` | 每日最多发放次数，0 表示不限 |
| `enabled` | `tinyint(4)` | 是否启用 |

#### `point_event_log`

积分事件幂等和发放日志表。

Note 创建时事件 key 形如：

```text
note_created:note:{note_id}:rule:{rule_id}
```

关键字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `user_id` | `int(11)` | 用户 |
| `rule_id` | `int(11)` | 规则 ID |
| `event_type` | `varchar(64)` | `note_created` |
| `event_key` | `varchar(191)` | 幂等键 |
| `source_type` | `varchar(64)` | V2 传入 `note` |
| `source_id` | `int(11)` | 笔记 ID |
| `point_type` | `varchar(16)` | AP/GP |
| `granted_points` | `int(11)` | 发放分值 |
| `occurred_on` | `date` | 发放日期 |

#### `point_record`

积分流水表。Note 创建时 `source_type` 保存为 `event:note_created`，`source_id` 保存笔记 ID。

### 8.5 成就相关表

创建笔记后会评估成就。

#### `achievement`

成就定义表。Note 相关自动成就：

- `achievement_note_50`：累计创建 50 条笔记。

其他手动领取 badge 也会统计笔记数：

- `badge_knowledge_collector`：创建 100 条笔记。
- `badge_productivity_architect`：组合条件中包含创建 100 条笔记。

#### `user_achievement`

用户成就记录表，保存 `user_id`、`achievement_code`、`achieved_at`。

## 9. 统计和日报联动

### 9.1 统计

`StatisticsService` 会把 `note` 作为基础统计类型之一。

`NoteRepository::getStatisticCounts($startTime, $endTime)`：

- 按 `updated_at` 落在时间段内统计。
- 按 `user_id` 分组。
- 写入统计后用于统计页的 `note_bar_statistics`。

### 9.2 日报提示

`DailySummaryService::getTipInfos()` 调用 `NoteRepository::getListForSummary()`：

- 查询当前用户指定日期内 `updated_at` 落入区间的笔记。
- 返回到日报提示信息的 `note` 分组。

## 10. 后台管理

后台路由：`/admin/notes`

`app/Admin/Controllers/NoteController.php`：

- 按 `id desc` 展示。
- 展示 ID、创建者、内容、创建时间。
- 禁用创建、行选择和默认操作。
- 支持按 `user_id` 过滤。

`app/Admin/Controllers/UserController.php` 会按用户统计不同 `status` 的 Note 数，并链接到 `/admin/notes?user_id={user_id}`。

## 11. 当前注意点

1. `notes.content` 保存前会执行 `htmlspecialchars()` 和 `nl2br()`，编辑页会再转回换行。其他调用方直接读取时要注意这是 HTML 化后的内容。
2. 新客户端应传 `content` 作为正文；旧客户端只传 `name` 时，后端会兼容为正文，标题置空。
3. `note_tag_maps.status` 字段当前未参与查询过滤。
4. 删除笔记不会清理标签关系、音频文件或图片文件。
5. Web 旧创建接口不会触发 V2 的积分和成就逻辑。
6. `GET /api/v2/notes/{note}` 只允许作者读取，公开笔记不能通过详情接口直接读取。
7. 音频读取对公开笔记只判断 `status=2`，没有判断 `audit_status=1`。
8. 当前代码的来源类型主要是数字枚举，但存在前端传值不一致的风险：例如 LLM 页面传 `source_type: 'llm'`，部分文章详情入口曾出现传 `source_type=3` 的路径；`notes.source_type` 当前数据库类型是 `tinyint(4)`，这类入口需要单独核对。
9. `database/db.sql` 中的 `notes` 表结构落后于当前本地数据库，缺少 `source_type/source_id` 字段；排查表结构时应优先以迁移和实际库为准。
