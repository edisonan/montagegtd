<div align="center">

[English](./README.md) | 简体中文

# 🎬 MontageGTD

**不止于 GTD —— 番茄专注 · 任务管理 · 笔记 · RSS 阅读 · 思维导图 · AI 助手 于一体的知识管理系统**

[![PHP](https://img.shields.io/badge/PHP-%3E%3D%207.0-8892BF?logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-5.5-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-%3E%3D%205.5-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![License](https://img.shields.io/badge/license-MIT-brightgreen)](LICENSE.txt)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen)](https://gitee.com/accacc/task/pull_requests)

[🌐 在线体验](https://task.congcong.us) · [📦 Gitee 仓库](https://gitee.com/accacc/task) · [🐙 GitHub 仓库](https://github.com/edisonan/montagegtd) · [📖 Wiki 文档](https://gitee.com/accacc/task/wikis/Home?sort_id=42169)

</div>

---

> 取名 **“蒙太奇”**（Montage），这是个稍显拗口的电影术语，指通过对众多镜头进行不同方式的剪辑，从而展现出各异的立意。即便在疫情期间，不少人断言电影将会走向消亡，但实际上电影或许是最具生命力、最难消亡的产业之一。人类在现实中往往显得渺小脆弱，而电影为我们提供了一个能够主宰想象空间的契机。
>
> 这个平台同样如此：它期望记录下我们这些小人物在平凡日常与非凡时刻的点点滴滴，如同经历一场 **“浮生一日”**。在这里，你既是导演，掌控着情节走向；也是编剧，书写着故事内容；更是演员，演绎着自己的人生。衷心希望大家所记录的每一件事、每一段成长历程，最终能够如同精心剪辑的蒙太奇镜头一般，共同拼凑出一部专属于我们生活的宏大电影。

![预览图](public/img/index.jpg)

---

## ✨ 功能特性

| 模块 | 核心能力 | 状态 |
| --- | --- | --- |
| 🍅 番茄工作法 + 任务 | 引导式上手、番茄钟时长自定义（最小 10 分钟）、完成番茄后双击待办记录描述、未开番茄的有意义事项记录、待办提醒与 deadline 醒目提醒、**四象限**（重要/紧急）任务管理、按目标分组管理任务、每日早晚番茄提醒 | ✅ |
| 📰 阅读 / RSS | 订阅源管理、拖动排序、稍后阅读、加星 / 收藏、分享到社交网络、文章语音播放、探索模式与沉浸阅读、AI 摘要与智能分类 | ✅ |
| 🧠 思维导图 | 快速新建、快捷键插入 / 编辑节点、支持描述、一键导出为图片 | ✅ |
| 📝 笔记（想法） | 标签管理、公开 / 私密发布、Chrome 语音记录、网页 / 图片一键分享到笔记、自动引导书写每日小目标与总结 | ✅ |
| 📚 Kindle 订阅推送 | 订阅内容推送到 Kindle 设备、支持带图推送与测试推送 | ✅ |
| 📊 数据统计 | 按月对阅读、番茄、笔记等生成饼图与柱状图 | ✅ |
| 📅 日总结 | 每日提醒；书写日总结时自动罗列当天的任务、导图、笔记、阅读记录辅助回顾 | ✅ |
| 🎓 学习打卡 | 学习打卡、课程管理、章节管理、学习进度聚合 | ✅ |
| ⭐ 积分激励 | 贯穿阅读 / 笔记 / 学习等场景的积分体系 | ✅ |
| 🤖 AI 助手（LLM） | 基于智能体的多轮对话工作台、文本 / 代码 / DOCX / PDF 附件、梳理今天 / 发展想法 / 提炼内容等快捷场景、文章 AI 摘要与分类、供应商 / 模型 / 凭据管理（支持用户级与全局资源） | ✅ |
| 🚧 即将支持 | 微博 / 微信公众号订阅、头条博文与个性化推荐、每日读订阅、喜欢文章一键生成 HTML、自定义推送指定 RSS 内容、更细化的番茄统计 | 🚧 |

## 🧱 技术栈

- **后端框架**: [Laravel 5.5](https://laravel.com) · **语言**: PHP >= 7.0
- **数据库**: MySQL >= 5.5（可选用 Redis / Predis 缓存队列）
- **前端**: Blade + Vue 2 + jQuery + Bootstrap Sass，Laravel Mix 构建
- **后台管理**: [encore/laravel-admin](https://github.com/z-song/laravel-admin)
- **部署**: 内置 `Dockerfile` 与 `docker-compose.yml`（Nginx + PHP-FPM + MySQL）

## 🚀 快速开始

### 在线体验

访问 [https://task.congcong.us](https://task.congcong.us) 即可体验完整功能。

### Docker 部署（推荐）

```bash
docker-compose up -d
# 访问 http://localhost:8080
```

### 手动部署

```bash
# 1. 克隆仓库
git clone https://gitee.com/accacc/task.git
cd task

# 2. 安装 PHP 依赖
composer install

# 3. 配置环境
cp .env.example .env
php artisan key:generate
# 编辑 .env，配置 MySQL 等连接信息

# 4. 初始化数据库
php artisan migrate --seed

# 5. 构建前端资源
npm install
npm run dev

# 6. 启动服务
php artisan serve
```

> 💡 AI 助手相关功能需要额外初始化 LLM 数据表，详见 [LLM_FEATURES_INSTALL.md](./LLM_FEATURES_INSTALL.md)。

## 🔌 浏览器快捷集成

**快速订阅 RSS** — Chrome 安装 [RSS Subscription Extension](https://chrome.google.com/webstore/detail/rss-subscription-extensio/nlbjncdgjeocebhnmkbbbdekmmmcbfjd)，在扩展选项中新增订阅项后点击「立即订阅」即可：

```
录入说明: 订阅到 Montage GTD
录入网址: http://task.congcong.us/feeds?url=%s
```

**快速分享到笔记** — Chrome 安装 [右键搜](https://chrome.google.com/webstore/detail/context-menus/phlfmkfpmphogkomddckmggcfpmfchpn)，自定义菜单配置：

```
页面菜单: https://task.congcong.us/notes?add_content=%s
划词菜单: https://task.congcong.us/notes?add_content=%s
图片菜单: https://task.congcong.us/notes?type=image&add_content=%s
链接菜单: https://task.congcong.us/notes?add_content=%s
```

<details>
<summary>或者在设置界面直接加载如下完整配置（点击展开）</summary>

```json
{"mcGroup":"[]","linBack":"[]","txtSelect":"[\"montage\"]","lb1":"\"2\"","rt2":"\"2\"","shorten":"\"googl\"","txtBack":"[]","zh_TW":"false","menBack":"[]","txtIncognito":"[]","analytics":"true","menSelect":"[\"montage\"]","picIncognito":"[]","picCustom":"[[\"montage\",\"https://task.congcong.us/notes?type=image&add_content=%s\"]]","rb2":"\"2\"","linCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","picBack":"[]","lcGroup":"[]","pcGroup":"[]","qr_size":"250","txtCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","rt1":"\"1\"","isFlag":"true","ru":"false","lt2":"\"1\"","tcGroup":"[]","linSelect":"[\"montage\"]","back":"false","names":"{}","menCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","lt1":"\"1\"","newPage":"true","zh_CN":"false","phrase":"\"montagegtd\"","locale":"\"zh_CN\"","lb2":"\"1\"","picSelect":"[\"montage\"]","rb1":"\"2\"","isEdit":"true","linIncognito":"[]","en":"false","menIncognito":"[]"}
```

</details>

## 🔑 API

项目提供基于 **Personal Access Token（PAT）** 的 API 路由（前缀 `/api/v1`），认证方式：

```bash
Authorization: Bearer {personal_access_token}
```

作用域（scope）分层约定：

| Scope | 说明 | 示例 |
| --- | --- | --- |
| `read` | 查询类接口 | `GET /api/v1/auth/me`、会话 / 模型查询 |
| `write` | 写接口 | 会话创建、聊天、智能体增删改 |
| `admin` | 高权限配置接口 | provider / model / credential 管理 |

```bash
curl -H "Authorization: Bearer <TOKEN>" \
  https://your-domain/api/v1/auth/me
```

> 更现代的 `/api/v2` 接口基于 hybrid token 中间件（`hybrid.token:read` / `hybrid.token:write`），OpenAPI 文档见 `docs/openapi-v2.md` 与 `docs/openapi-v2.yaml`。

## 📚 文档

- [Wiki（部署与技术文档）](https://gitee.com/accacc/task/wikis/Home?sort_id=42169)
- [LLM 功能安装说明](./LLM_FEATURES_INSTALL.md)
- [`docs/`](./docs) — 环境说明、OpenAPI v2、PAT、混合鉴权客户端等
- [`docs/environments.md`](./docs/environments.md) — 本地与生产环境映射及部署笔记

## 🤝 贡献

欢迎提交 Issue 与 Pull Request，共建这部属于每个人的「生活蒙太奇」。

## 📄 License

[MIT](./LICENSE.txt)

## 🙏 鸣谢

[![JetBrains](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://jb.gg/OpenSourceSupport)

感谢 [JetBrains](https://jb.gg/OpenSourceSupport) 对本开源项目的支持。