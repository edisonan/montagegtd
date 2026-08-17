# montageapp Skill 使用指南

- 关联 Skill：`skills/montageapp`（CLI：`skills/montageapp/scripts/montage` / `montage_cli.py`）
- 用途：创建和维护 Montage 应用工作台里的网页 APP（应用、HTML/CSS/JS 文件、虚拟数据表与记录、历史回滚、预览发布）
- 环境：Python 3.7+（CLI 仅用标准库，无需 pip 安装依赖）

---

## 1. 它是什么

`montageapp` 把"创建/修改/预览/发布应用工作台 APP"变成一套可自动化操作：

- 用 CLI（PAT 认证）完成应用、文件、虚拟数据表、记录的增删改查与历史回滚；
- 页面通过 `https://task.congcong.us/app/{slug}/index.html` 公开预览；
- 后台 `/admin` 工作台仅用于人工查看/微调（session 登录），自动化一律走 CLI。

不同于 gtd 技能（管理待办/笔记），本技能管理的是**应用工作台里的网页应用**。

---

## 2. 安装

### 2.1 前置条件

- Python 3.7+（`python3 --version` 确认）
- 一个已授权的 PAT（见第 3 节）
- 不需要 composer / npm / 额外 pip 包（CLI 只依赖 Python 标准库）

### 2.2 获取技能文件

技能位于项目仓库 `skills/montageapp/`：

```text
skills/montageapp/
├── SKILL.md                        # 技能定义（代理读取的主文件）
├── scripts/
│   ├── montage                     # CLI 入口（sh 包装，调用 montage_cli.py）
│   └── montage_cli.py              # CLI 主体（17 个子命令）
├── references/
│   └── app-contract.md             # 文件类型、auth_mode、虚拟表、后台接口契约
└── agents/
    └── openai.yaml                 # 代理展示配置
```

无需安装到系统：直接在项目仓库里以 `skills/montageapp/scripts/montage` 调用即可。

> 部署到线上后，同一套文件会存在于服务器 `/home/q/system/task/skills/montageapp/`。

---

## 3. 配置

### 3.1 必需：PAT（个人访问令牌）

CLI 用 `hybrid.token` 通过 PAT 认证，**只认 `Bearer` PAT**。需求是 `read` + `write`（应用的写操作）。

获取 PAT 的途径（任选其一）：

1. **已有 PAT**：在代理环境中设置环境变量。
2. **后台生成**：登录后创建 Personal Access Token，勾选 `read`、`write` scope。
3. **API 创建**：用已有账号调 `POST /api/v2/personal-access-tokens`。

> ⚠️ 安全约束：**admin scope 仅限后台管理员或应用管理者白名单账号创建**（普通用户自建 admin PAT 会被 403 拒绝）。应用管理本身还需要账号在`应用管理者白名单`内（见 3.3），二者缺一不可。

### 3.2 环境变量

```bash
# 必填：PAT
export MONTAGE_APP_TOKEN="pat_xxxxx"

# 可选：API 基址（默认 https://task.congcong.us/api/v2，一般不用设）
# export MONTAGE_APP_BASE_URL="https://task.congcong.us/api/v2"

# 兼容旧变量（优先于上面的 MONTAGE_* 被环境读取时按代码顺序）
# export TASK_GITEE_TOKEN="..."
# export TASK_GITEE_BASE_URL="..."
```

也可以不 export，直接每次传参：

```bash
skills/montageapp/scripts/montage --token pat_xxxxx app-list
```

### 3.3 应用管理者白名单（服务端配置）

应用管理接口（`/api/v2/app-manage/*`）在 PAT 之外还要求**当前用户在白名单内**，否则一律 403。白名单在**服务器 `.env`** 配置：

```ini
# 允许的邮箱（逗号分隔）
APP_MANAGE_ALLOWED_EMAILS=you@example.com

# 可选：允许的用户 ID（逗号分隔）
# APP_MANAGE_ALLOWED_USER_IDS=1,2
```

配置后需清缓存：`php artisan config:clear`（在服务器项目目录执行）。

> 没有配置白名单 = 任何人都不能操作应用管理接口（默认拒绝），这是刻意设计的安全底线。

### 3.4 HTTPS 证书

- 默认跳过证书校验（宽松模式，开箱即用），无需配置证书环境变量。
- 需要严格校验时加 `--verify`。

---

## 4. 使用

所有命令统一入口：

```bash
CLI=skills/montageapp/scripts/montage
```

### 4.1 全局参数

```bash
$CLI [--token TOKEN] [--base-url URL] [--output json|table|raw] [--verify|--insecure] <子命令> ...
```

- `--output table`：人看的格式；默认 `json` 适合脚本/代理解析
- `--output raw`：打印原始 HTTP 响应体

### 4.2 应用管理

```bash
$CLI app-list                                    # 列出所有应用
$CLI app-list --output table
$CLI app-show 5                                  # 查看应用详情 + 文件清单 + 预览 URL
$CLI app-create --name "习惯打卡" --slug habit-checkin --auth-mode public
$CLI app-update 5 --description "更新说明" --status 2
$CLI app-delete 5                                # 软删除
```

创建应用参数：

| 参数 | 说明 | 默认 |
| --- | --- | --- |
| `--name` | 应用名称（必填） | - |
| `--slug` | URL 标识，小写字母/数字/连字符，唯一（必填） | - |
| `--description` | 说明 | 无 |
| `--status` | 1/2/3/4 | 1 |
| `--auth-mode` | `public` / `login` / `whitelist` / `pat` | `public` |

### 4.3 文件管理

文件类型：`2=html`、`3=js`、`4=css`、`5=json`（**API 禁止 1=php**，PHP 仅后台人工维护）。

```bash
# 创建（内容可从本地文件读入）
$CLI code-create 5 --name index --path index.html --type 2 --content-file ./index.html
$CLI code-create 5 --name styles --path styles.css --type 4 --content-file ./styles.css
$CLI code-create 5 --name app --path app.js --type 3 --content-file ./app.js

# 更新
$CLI code-update 5 12 --content-file ./index.html
$CLI code-update 5 12 --name index --status 2

# 历史与回滚
$CLI code-history 5 12                 # 查看历史版本
$CLI code-rollback 5 12 34             # 回滚到历史版本 34

# 删除
$CLI code-delete 5 12
```

> 入口文件必须是 `index.html`（相对路径引用 `styles.css`、`app.js`，不要用外部 CDN）。

### 4.4 虚拟数据表与记录

用于持久化业务数据（不要把会变的业务数据硬编码进 HTML）：

```bash
# 建表
$CLI table-create 5 --name 打卡记录 --slug habit_logs
$CLI table-list 5                          # 列出表

# 加字段（类型：string/text/integer/decimal/boolean/date/datetime/json）
$CLI table-field 5 3 --name 习惯 --slug habit_name --type string --nullable 0 --indexed 1
$CLI table-field 5 3 --name 完成 --slug done --type boolean --default-value 0

# 记录增删改查
$CLI record-create 5 3 --data '{"habit_name":"喝水"}'        # JSON 字符串
$CLI record-create 5 3 --data @record.json                   # 从文件读
$CLI record-list 5 3 --output table
$CLI record-list 5 3 --page 1 --per-page 50
$CLI record-update 5 3 7 --data '{"habit_name":"喝水2"}'
$CLI record-delete 5 3 7
```

### 4.5 预览

创建后打开预览验证（默认线上域名）：

```text
https://task.congcong.us/app/{slug}/index.html
```

---

## 5. 推荐工作流

```bash
# 1. 创建应用
$CLI app-create --name "典型应用" --slug my-app --auth-mode public

# 2. 创建入口 + 资源
$CLI code-create <app_id> --name index --path index.html --type 2 --content-file ./index.html
$CLI code-create <app_id> --name style --path styles.css --type 4 --content-file ./styles.css
$CLI code-create <app_id> --name app --path app.js --type 3 --content-file ./app.js

# 3. 要持久化数据 → 建表建字段写记录
$CLI table-create <app_id> --name 事项 --slug items
$CLI table-field  <app_id> <table_id> --name 标题 --slug title --type string
$CLI record-create <app_id> <table_id> --data '{"title":"第一条"}'

# 4. 预览验证
open "https://task.congcong.us/app/my-app/index.html"

# 5. 反复迭代：code-update → 预览
```

---

## 6. 权限与安全（要点）

| 项 | 规则 |
| --- | --- |
| 认证 | PAT（`hybrid.token`），需 `read` + `write` |
| 应用管理 | 额外要求账号在服务端**应用管理者白名单**内（否则 403） |
| admin scope | 仅后台管理员 / 白名单账号可创建（普通用户 403） |
| PHP 文件 | API 层**禁止** type=1（防止任意代码执行），只走后台人工 |
| Token 显示 | 不向用户展示完整 token |
| 预览访问 | 由应用的 `auth_mode` 决定（public=公开可看） |

## 7. 失败处理

- **403「不在应用管理者白名单内」**：账号未授权 → 让管理员在服务器 `.env` 配置 `APP_MANAGE_ALLOWED_EMAILS` 后清缓存。
- **401「Invalid or expired token」**：PAT 失效/撤销 → 重新生成 PAT。
- **403「admin scope 仅限后台管理员」**：普通用户不能创建 admin PAT → 用白名单账号或后台管理员账号创建。
- **422「文件路径已存在」「slug 已存在」**：目标冲突 → 换 slug / path，或用 `app-update` / `code-update`。
- **TYPE/PHP 被拒**：CLI 与 API 都拒绝 type=1 → 应用不用 PHP，改用 HTML/JS/CSS/JSON。
- **SSL 报错**：默认已宽松校验；仍报错时可用 `--insecure`（仅调试）。

## 8. 附录

- 技能细节：`skills/montageapp/SKILL.md`、`skills/montageapp/references/app-contract.md`
- 后台人工维护：`/admin` 工作台（session 登录）
- 环境说明：`docs/environments.md`（`testtask` 本地 / `task` 线上）