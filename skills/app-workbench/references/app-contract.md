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
- 字段需要名称、唯一 slug、类型；常用类型为 string、text、integer、decimal、boolean、date、datetime、json。
- 前端展示层使用字段 slug，不依赖数据库物理列名；工作台会将物理列统一处理为 `f_` 前缀。
- 可空、索引、长度等属性要按实际查询和展示需要设置，不要无理由建立大量索引。
- 记录列表必须有加载中、无数据、保存成功和失败状态；编辑记录时保留当前字段值。

## 接口与预览

后台管理接口使用浏览器登录态和 CSRF：

```text
GET  /admin/applications
GET  /admin/applications/{id}
POST /admin/applications
GET  /admin/applications/{id}/codes/{codeId}
POST /admin/applications/{id}/codes
PUT  /admin/applications/{id}/codes/{codeId}
GET  /admin/applications/{id}/virtual-tables
```

具体字段以服务器路由和页面返回为准；自动化代理不得把 admin 登录凭证写入 APP 文件或提交到仓库。

公开预览格式：

```text
https://pretask.congcong.us/app/{slug}/index.html
```

预览前先检查 slug 和入口文件是否启用。若页面引用应用内 CSS/JS，引用路径必须和文件 `path` 相同，例如文件 `styles.css` 就写 `href="styles.css"`。

## 创建结果示例

```text
应用：习惯打卡（id=5，slug=habit-checkin）
文件：index.html、styles.css、app.js
数据表：habits(name, target, enabled)、habit_logs(habit_slug, logged_on)
预览：https://pretask.congcong.us/app/habit-checkin/index.html
验证：桌面/手机布局通过；入口、CSS、JS 均加载；空数据状态已处理
```
