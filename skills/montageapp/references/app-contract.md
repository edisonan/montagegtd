# 应用工作台契约

## 文件类型

| type | 含义 | 推荐扩展名 |
| --- | --- | --- |
| 1 | PHP | `.php` |
| 2 | HTML | `.html` |
| 3 | JavaScript | `.js` |
| 4 | CSS | `.css` |
| 5 | JSON | `.json` |

`index.html` 必须使用 type 2；入口判断支持 `index.html` 和历史兼容的 `index.php`，新应用统一使用 HTML 入口。

## 最小可运行模板

```html
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>应用名称</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main id="app" aria-live="polite"></main>
  <script src="app.js"></script>
</body>
</html>
```

```css
:root { color-scheme: light; font-family: system-ui, sans-serif; }
* { box-sizing: border-box; }
body { margin: 0; background: #f6f8fb; color: #1f2937; }
main { width: min(1120px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0; }
@media (max-width: 640px) { main { width: min(100% - 24px, 520px); padding: 20px 0; } }
```

```javascript
(function () {
  var root = document.getElementById('app');
  if (!root) return;
  root.innerHTML = '<h1>应用名称</h1><p>准备就绪。</p>';
})();
```

## 虚拟数据表约定

- 表需要名称、唯一 slug 和说明；slug 使用小写字母、数字和连字符。
- 字段需要名称、唯一 slug、类型；类型只能是 string、text、integer、decimal、boolean、date、datetime、json 之一。
- 字段可选属性：`length`（长度）、`nullable`（0/1）、`default_enabled`（0/1）、`default_value`、`indexed`（0/1）、`sort_order`（0 开始）、`description`、`status`（0/1）。按实际查询和展示需要设置，不要无理由建立大量索引。
- 前端展示层使用字段 slug，不依赖数据库物理列名；工作台会将物理列统一处理为 `f_` 前缀。
- 记录列表分页参数为 `page` 与 `per_page`（默认每页 100）。新增/修改记录时，请求体直接以字段 slug 作为 key 传值（服务端会剔除 `_token`、`_method`）。
- 记录列表必须有加载中、无数据、保存成功和失败状态；编辑记录时保留当前字段值。
- 记录通过 `records` 接口读写（见下），不要把会变化的业务数据硬编码进 HTML。

## 接口与预览

后台管理接口使用浏览器登录态和 CSRF（后台基址为 `/admin`）：

```text
# 应用元信息
GET  /admin/applications
GET  /admin/applications/{id}
POST /admin/applications
PUT  /admin/applications/{id}/meta
GET  /admin/applications/{id}/workspace-data

# 文件 / 代码
GET  /admin/applications/{id}/codes/{codeId}
POST /admin/applications/{id}/codes
PUT  /admin/applications/{id}/codes/{codeId}
POST /admin/applications/{id}/codes/{codeId}/ai-generate   # body: mode=generate|optimize, prompt
GET  /admin/applications/{id}/codes/{codeId}/history
POST /admin/applications/{id}/codes/{codeId}/history/{historyId}/rollback

# 虚拟数据表
GET  /admin/applications/{id}/virtual-tables
POST /admin/applications/{id}/virtual-tables
POST /admin/applications/{id}/virtual-tables/{tableId}/fields
GET  /admin/applications/{id}/virtual-tables/{tableId}/records?page=1&per_page=100
POST /admin/applications/{id}/virtual-tables/{tableId}/records
PUT  /admin/applications/{id}/virtual-tables/{tableId}/records/{recordId}
DELETE /admin/applications/{id}/virtual-tables/{tableId}/records/{recordId}
```

- `ai-generate` 用 `mode=generate|optimize`（生成或优化），可带 `prompt`；接口返回 `data.content`，需在 next 消息或工作流里据此更新文件内容后走 `PUT .../codes/{codeId}` 落库。
- 文件类型 type 与扩展名对照：`1=php`、`2=html`、`3=js`、`4=css`、`5=json`。仅 type 2/3/4/5 可预览，type 1（php）不可预览。
- 入口判断只认 `index.html` 与历史兼容的 `index.php`，新应用统一用 HTML 入口。
- 前端对新记录用 `POST .../records`、改 `PUT .../records/{id}`、删 `DELETE .../records/{id}`，请求体 key 为字段 slug。

### 访问控制（auth_mode）

应用和单个文件都有 `auth_mode`，文件级缺省时回落到应用级，再缺省为 `public`，由 `code.access` 中间件按此执行，四种取值：

| auth_mode | 含义 |
| --- | --- |
| `public` | 公开访问，无需登录 |
| `login` | 需登录用户 |
| `whitelist` | 仅应用 `allowed_users` 白名单内用户可访问 |
| `pat` | 需 PAT（`code:execute`）鉴权 |

创建应用时可指定 `auth_mode`；需要限定用户时，在后台配置应用的允许用户名单。

### 预览 URL

统一使用线上域名 `https://task.congcong.us`：公开预览入口为 `/app/{slug}/index.html`，即 `https://task.congcong.us/app/{slug}/index.html`。详见 `docs/environments.md` 的环境映射；不要写成 `testtask` 或 `pretask`。预览前先检查 slug 和入口文件是否启用。若页面引用应用内 CSS/JS，引用路径必须和文件 `path` 相同，例如文件 `styles.css` 就写 `href="styles.css"`。

具体字段以服务器路由和页面返回为准；自动化代理不得把 admin 登录凭证写入 APP 文件或提交到仓库。

## 创建结果示例

```text
应用：习惯打卡（id=5，slug=habit-checkin）
文件：index.html、styles.css、app.js
数据表：habits(name, target, enabled)、habit_logs(habit_slug, logged_on)
预览：https://task.congcong.us/app/habit-checkin/index.html
验证：桌面/手机布局通过；入口、CSS、JS 均加载；空数据状态已处理
```
