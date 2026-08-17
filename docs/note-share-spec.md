# 笔记审核 + 分享链接 功能规格（Spec v2）

## 1. 背景

当前 `notes` 表已有公开/审核字段：

| 字段 | 值 | 含义 |
| --- | --- | --- |
| `status` | `1` | 私密 |
| `status` | `2` | 公开 |
| `audit_status` | `0` | 未审核/待审核 |
| `audit_status` | `1` | 审核通过，进入他人可见列表 |

v1 已实现（第一轮）：

1. 后台笔记审核：`/admin/notes` 可把 `audit_status` 置为 `1`（审核通过）或 `0`（撤销）。
2. 公开笔记分享页：`GET /notes/share/{note}`（免登录，按笔记 ID 定位）。

v2 需求（本轮）：

1. **分享不用笔记 ID，改用随机码**——避免被遍历、泄露笔记存在性。
2. **带密码分享**——生成分享时随机生成访问密码，密码随链接一起携带（作为 URL 中的 `key` 校验码）；没有 key 访问时显示"受密码保护"输入页。
3. **未审核的公开笔记也能分享**——分享页只看 `status=2`，不要求 `audit_status=1`（审核仅约束"公共广场"列表可见性）。
4. 部署到生产环境（`task.congcong.us`）。

## 2. 数据变更

新增迁移 `2026_08_18_000001_add_share_fields_to_notes_table`：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `notes.share_token` | `varchar(64)` nullable + unique | 分享随机码（`str_random(24)`，替代笔记 ID 出现在分享 URL 中） |
| `notes.share_password` | `varchar(64)` nullable | 分享访问密码的 bcrypt 哈希 |

生成策略：`share_token` 首次生成后保持不变（链接路径稳定）；每次调用生成接口都会**重新生成随机密码**并覆盖哈希（旧链接中的 key 立即失效），符合"密码随机生成填充"的预期。

## 3. 需求一：后台笔记审核（v1 保留）

- 后台 `/admin/notes`：`status`/`audit_status` 列、徽标、筛选（`user_id`/`status`/`audit_status`）。
- 每行操作：公开笔记显示"审核通过/撤销审核"（表单 POST，带确认），私密笔记显示"无需审核"。
- 接口：`POST /admin/notes/audit/{id}`，body `value=0|1`。
- 审核通过后该公开笔记进入所有用户 `/notes` 列表；正文/标题修改时 `audit_status` 重置为 `0`（现有逻辑）。

## 4. 需求二：随机码 + 密码分享

### 4.1 生成分享链接（作者操作）

新增 API：

```
POST /api/v2/notes/{note}/share   中间件 hybrid.token:write，仅作者（NotePolicy）
```

逻辑：

1. 校验作者身份；`status !== 2` 时报"仅公开笔记可生成分享链接"。
2. 首次：`share_token = str_random(24)`。
3. 每次：`password = str_random(12)`，`share_password = bcrypt(password)`，覆盖保存。
4. 返回：

```json
{
  "code": 9999,
  "msg": "ok",
  "result": {
    "url": "{origin}/notes/share/{share_token}?key={password}",
    "token": "…",
    "password": "…"
  }
}
```

前端（列表页、管理页）点击"复制分享链接"→ 调用该接口 → 复制 `result.url` → toast"分享链接已复制（含访问密码）"。

### 4.2 分享页（免登录）

```
GET /notes/share/{token}?key={password}
```

`NoteController@share`：

- `share_token` 不存在 或 `status !== 2` → "笔记未公开或不存在"（不泄露信息）。
- 存在 `share_password` 且 `key` 缺失/校验失败 → "该笔记受密码保护" + 密码输入表单（GET 提交 `key`），错误时提示"密码不正确"。
- 校验通过 → 正常展示（标题/作者/时间/公开徽标/标签/Markdown 正文/图片）。

- 未审核（`audit_status=0`）的公开笔记同样可分享，分享页不校验 `audit_status`。

### 4.3 分享 URL 形态

- 旧：`/notes/share/{笔记ID}`
- 新：`/notes/share/{随机码}?key={随机密码}`

不再暴露笔记 ID，也无法通过递增 ID 遍历他人的分享。

## 5. 涉及文件

| 文件 | 改动 |
| --- | --- |
| `database/migrations/2026_08_18_000001_add_share_fields_to_notes_table.php` | 新增 `share_token`/`share_password` |
| `app/Http/Controllers/NoteController.php` | `share` 改为随机码定位 + 密码校验 |
| `app/Http/Controllers/Api/V2/NoteController.php` | 新增 `shareLink` |
| `routes/web.php` | `/notes/share/{token}` |
| `routes/api.php` | `POST /notes/{note}/share` |
| `resources/views/notes/share.blade.php` | 锁定/错误/正常三态 |
| `resources/views/notes/index.blade.php` | 复制分享改为调 API |
| `resources/views/notes/manage.blade.php` | 同上 |
| `app/Admin/Controllers/NoteController.php` + `app/Admin/routes.php` | v1 审核（保留） |
| `docs/note-share-spec.md` | 本规格 |

## 6. 验证清单

- `php -l` 改动 PHP 文件；`php artisan migrate` 本地应用迁移。
- `php artisan route:list` 确认 `/notes/share/{token}` 与 `POST /api/v2/notes/{note}/share`。
- 分享页冒烟：无 token → 未公开页；有随机码无 key → 密码页；带正确 key → 内容；带错误 key → 密码错误。
- 审核冒烟（v1 已验）：approve → `audit_status=1`，revoke → `0`。
- 部署后远程执行 `php artisan migrate` 并刷新存储视图缓存。

## 7. 后续可选项（本次不做）

- 匿名播放分享笔记语音（需免鉴权录音路由）。
- 分享链接有效期/次数限制、分享列表管理（重置/关闭分享）。
- 管理员审核后通知作者。