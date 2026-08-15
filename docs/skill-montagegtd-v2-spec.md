# montagegtd Skill V2 设计规格

- 状态：设计稿（待评审后实施）
- 关联 Skill：`skills/montagegtd`（CLI：`skills/montagegtd/scripts/montage_cli.py`）
- 关联文档：`docs/openapi-v2.yaml`、`docs/note-logic.md`、`docs/article-llm-classification-and-digest-implementation.md`
- 目标：补齐当前 CLI 未覆盖的高价值平台能力，并强化 Article / Note 的"玩法"。

---

## 1. 背景与目标

当前 CLI 已覆盖：task、note、article、feed（部分）、focus、plan、daily-summary、PAT + 通用 `request` 兜底。
经核对 `routes/api.php` 全量接口，以下能力**有接口但无专属命令**，依赖 `request` 兜底，模型容易猜不中字段：

| 能力域 | 说明 | 优先级 |
| --- | --- | --- |
| Digest 汇合页 | 用户兴趣 profile + 自动生成阅读汇合 | P0（阅读核心玩法） |
| Study 学习 | overview / checkins / plans / checkin / generate | P0 |
| Note 语音记录 | `POST /notes/upload`（MP3） | P0 |
| Article 划线→存笔记 | 组合命令（摘录流） | P0 |
| Feed 订阅搜索/探索 | `feed explore`、`feed search`、`feed check-url` | P1 |
| Journal 手账 | CRUD + 列表筛选 | P1 |
| Mind 思维导图 | list / show / jsmind / outline / create | P1 |
| Achievement 成就 | list + `claim` | P1 |
| Points / 积分商城游戏化 | tree / pet / pond / lottery / bus / purchase | P1 |
| Statistics 统计 | 统览 | P2 |
| Course / Quiz 课程测验 | 课程、item、quiz + attempts 提交 | P2（已实现） |
| WeChat / Kindle / Settings | 低频管理面 | P2（课程/微信已实现；kindle/settings 保持 request） |

设计原则：

1. **不重复实现**——能复用 `request METHOD /path --data` 的不强行造命令；但对于"玩法明确、字段易猜错"的域，必须给一等命令 + 参考文档。
2. **读用 read、写用 write、管理用 admin**，沿用现有 hybrid token 约定。
3. 新命令全部挂进 `montage_cli.py` 的 parser，并同步更新对应 `references/*.md`。
4. P0/P1 为已交付核心；P2 的 course/quiz 与 wechat 也已实现，kindle/settings 保持 `request` 兜底。

---

## 2. 命令总览（新增命令一览）

统一沿用现有 `$CLI` 入口与 `--output table|json|raw` 约定。新增：

```
# Digest 汇合页
$CLI digest get-profile
$CLI digest save-profile --data '{"topics":[...],"frequency":"daily"}'
$CLI digest pages [--page-count N]
$CLI digest show-page <id>
$CLI digest generate

# Study 学习
$CLI study overview [--date YYYY-MM-DD]
$CLI study checkins [--date-from ... --date-to ... --page N --page-size N]
$CLI study plans
$CLI study plan <plan_id>          # show-plan 详情
$CLI study checkin <task_id> [--date --content --note]   # 文字打卡（多态媒体见 §6）
$CLI study generate [--date-from --date-to]
$CLI study plan-generate <plan_id> [--date-from --date-to]

# Note 语音
$CLI note record <mp3-file> [--title --content --tag TAG ...]

# Article → Note 摘录流（组合命令）
$CLI article clip <article_sub_id> [--content "划线/我的理解"] [--note]

# Feed 订阅发现
$CLI feed explore
$CLI feed search --name "关键词" | --recommend-category-id N
$CLI feed check-url <url>
$CLI feed quicklist                # 修复与现有 quickstore 的关系，见 §6 已知问题

# Journal 手账
$CLI journal list [--date --status ...]
$CLI journal show <id>
$CLI journal create --name "..." [--content --date ...]
$CLI journal update <id> [--name --content]
$CLI journal delete <id>

# Mind 思维导图
$CLI mind list
$CLI mind show <id>
$CLI mind outline <id>
$CLI mind jsmind <id>
$CLI mind create --data '{"title":"...","nodes":...}' --name "..."

# Achievement / Points
$CLI achievement list
$CLI achievement claim <achievement_code>
$CLI points                       # 积分总览
$CLI points mall-goods            # 商城商品
$CLI points mall-orders
$CLI points lottery draw --pool-id N [--times N]

# Course / Quiz 课程测验
$CLI course list|management|show|enrollments|items|structure|item-show|item-complete
$CLI quiz show <item_id> | quiz submit <item_id> --data '{"answers":[...]}' | quiz attempts <item_id>

# WeChat 联动
$CLI wechat explorer | articles [--status read_later] | articleview <article_id> | notes | add-note --content "..."
```

---

## 3. Digest 汇合页（P0）

### 3.1 后端接口核对（已确认）

- `GET /api/v2/digest/profile` → `{profile}`
- `POST /api/v2/digest/profile` 保存字段（**注意不是 `_json` 后缀**）：
  - `topics` array
  - `include_keywords` array
  - `exclude_keywords` array
  - `preferred_categories` array
  - `time_window_days` integer ∈ {1,3,7}
  - `frequency` ∈ {daily,weekly}
  - `max_articles` integer ∈ [5,50]
  - `output_style` string ≤32（可空）
  - `enabled` boolean
- `GET /api/v2/digest/pages?page_count=N` → `{pages, pagination}`（白名单校验，非白名单抛错）
- `GET /api/v2/digest/pages/{id}` → `{page}`，仅本人
- `POST /api/v2/digest/pages/generate` → `{task_id, result}`，白名单校验

### 3.2 CLI 设计

| 命令 | 方法/路径 | 行为 |
| --- | --- | --- |
| `digest get-profile` | GET /digest/profile | 打印当前 profile |
| `digest save-profile` | POST /digest/profile | `--data` 首选；缺省用 `--key value` 简单字段（topics 等数组仍走 `--data`） |
| `digest pages` | GET /digest/pages | 列表，支持 `--page-count` |
| `digest show-page` | GET /digest/pages/{id} | 详情 |
| `digest generate` | POST /digest/pages/generate | 手动立即生成 |

### 3.3 参考文档（articles.md 补一节）

- 说明 profile 字段命名，避免模型把 `topics` 写成 `topics_json`。
- 交代白名单前提：非白名单用户调用 `pages`/`generate` 会失败。
- 玩法提示：把"每周自动出一份我感兴趣主题的阅读汇合"作为推荐场景。

---

## 4. Study 学习（P0）

### 4.1 后端接口核对（已确认）

- `GET /study/overview?date=YYYY-MM-DD` → 直接用 controller 返回的数据
- `GET /study/checkins?date_from&date_to&page&page_size`
- `GET /study/plans`（study 类型 plan 列表）
- `GET /study/plans/{plan}` → 403 若非本人或非 study 类型
- `POST /study/tasks/{task}/checkin`：`date`、`content`、多媒体 file（audio/image/video）— **`mode` 必须为 3** 的学习任务
- `POST /study/generate`：`date_from`、`date_to`
- `POST /study/plans/{plan}/generate`：`date_from`、`date_to`，生成达标任务

### 4.2 CLI 设计

| 命令 | 方法/路径 | 行为 |
| --- | --- | --- |
| `study overview` | GET /study/overview | `--date` 可选 |
| `study checkins` | GET /study/checkins | 时间范围 + 分页 |
| `study plans` | GET /study/plans | 学习计划列表 |
| `study plan <id>` | GET /study/plans/{id} | 详情 |
| `study checkin <task_id>` | POST /study/tasks/{id}/checkin | 文字打卡：`--date` `--content`；**不做多媒体**（理由见 §6） |
| `study generate` | POST /study/generate | 按日期范围生成任务 |
| `study plan-generate <plan_id>` | POST /study/plans/{id}/generate | 按计划生成 |

### 4.3 参考文档（platform.md 或新建 study.md）

- 明确 `study checkin` 只支持文字；`request` 可用 `--data` 传文件路径（后端是 multipart/file，实际 CLI 不提交二进制，见 §6 已知问题）。
- 交代 `mode=3` 前置：checkin 目标是学习任务，拿到普通任务会 403。

---

## 5. Note / Article 玩法强化（P0）

### 5.1 Note 语音记录 `note record`

后端：`POST /notes/upload`（写入 `fname` 参数，返回 `record_name`）→ `POST /notes`（带 `fname` 建笔记，content 可空）。

CLI：

```
$CLI note record <mp3-file> [--title NAME] [--content TEXT] [--tag TAG ...]
```

行为：
1. 读本地 mp3 文件是否存在。
2. `POST /notes/upload`，file 字段 `file`，参数 `fname`（去 `.mp3` 后缀）。
3. 用返回的 `record_name` 作 `fname`，`POST /notes` 创建笔记。
4. 反馈：笔记 id、record_name、（触发积分/成就提示）。

参考文档（notes.md）补：语音玩法、上传仅支持 MP3 类型（`audio/mpeg` 等）、`fname` 语义。

### 5.2 Article 划线→笔记 `article clip`（组合命令）

后端：`POST /articles/mark`（`article_id` + `content`，划线摘录）+ `POST /notes`（带 `source_type=2`、`source_id=article_id`）。

CLI：

```
$CLI article clip <article_sub_id> [--content "划线/理解"] [--note]
```

行为：
1. **`--note` 提供时**：把文章（或其 content）存储为一条来源关联笔记（`source_type=2`），正文预填 `[[记录文章]]` 模板（对应 note-logic 的 `NOTE_SOURCE_TYPE_ARTICLE=2`）。
2. 未带 `--note` 时退化为 `article mark`（划线摘录），等价旧行为。
3. **需先解析 `article_id`**：入参是 `article_sub_id`，内含 `article_id`（沿用 articles.md 的 id 区分规则）。

参考文档（articles.md）补：摘录流的两种终点（划线 vs 成笔记），来源类型 2 的预填模板。

---

## 6. 其它新增命令（P1）

### 6.1 Feed 订阅发现

| 命令 | 方法/路径 | 说明 |
| --- | --- | --- |
| `feed explore` | GET /feeds/explorer | 推荐源 |
| `feed search` | GET /feeds/search | `--name` 或 `--recommend-category-id` ≥1 必填其一 |
| `feed check-url` | GET /feeds/check-feed-url?url= | 返回页面 title |
| `feed quicklist` | GET /feeds | 当前订阅列表（与现有 `feed-list` 同名归并） |

### 6.2 Journal 手账

`POST/PUT/DELETE /journals`，列表 `GET /journals` 有筛选。先做 list/show/create/update/delete，字段以 `--data` 为准，参考文档给出字段速查。

### 6.3 Mind 思维导图

`GET /minds`、`GET /minds/{id}`、`GET /minds/{id}/jsmind`、`GET /minds/{id}/outline`、`POST /minds`。先做浏览 + 创建，字段复杂时用 `--data`。

### 6.4 Achievement / Points

- `GET /achievements`、`POST /achievements/claim`（`achievement_code` / `achievement_id` 按后端约定，实施时核对）。
- `GET /points` 总览；`GET /point-mall/goods`、`GET /point-mall/orders`。
- 游戏化（tree/pet/pond/lottery/bus）：先只做**只读 overview + 抽奖**（`POST /point-mall/lottery/draw`，`pool_id` 必填、`times` 1-10）。树/宠物饲养类动作低频，保留 `request` 兜底，不造命令。

### 6.5 命令别名与归并原则

- 命令一律小写 kebab（`<subdomain> <verb>`），动词用 `list|show|create|update|delete|...`。
- 与现有名（如 `feed-list`）冲突的旧扁平命令保留兼容，新分组命令优先。
- 破坏性/批量操作仍遵循"先列出候选项，拿到明确 ID 再执行"纪律。

## 6.6 Course / Quiz 与 WeChat（P2，已实现）

### Course / Quiz

| 命令 | 方法/路径 | 说明 |
| --- | --- | --- |
| `course-list` | GET /courses | `--status` 可选 |
| `course-management` | GET /courses/management | 我管理的课程 |
| `course-show` | GET /courses/{id} | 详情 |
| `course-enrollments` | GET /course-enrollments | `--status` 可选 |
| `course-items` | GET /courses/{id}/items | 课程小节 |
| `course-structure` | GET /course-items/structure/{id} | 章节结构 |
| `course-item-show` | GET /course-items/{id} | 小节详情 |
| `course-item-complete` | POST /course-items/{id}/complete | 完成小节（写） |
| `quiz-show` | GET /course-items/{id}/quiz | 测验内容 |
| `quiz-submit` | POST /course-items/{id}/quiz/attempts | 提交答案，`--data '{"answers":[...]}'` |
| `quiz-attempts` | GET /course-items/{id}/quiz/attempts | 作答记录 |

### WeChat 联动

| 命令 | 方法/路径 | 说明 |
| --- | --- | --- |
| `wechat-explorer` | GET /wechat/explorer | 推荐订阅 |
| `wechat-articles` | GET /wechat/articles | 阅读队列，`--status read_later`, `--page`, `--page-date`, `--feed-id` |
| `wechat-articleview` | GET /wechat/articleview?article_id= | 文章正文（返回 subject/content） |
| `wechat-notes` | GET /wechat/notes | 最近 10 条笔记 |
| `wechat-add-note` | POST /wechat/addNote | 建笔记，`--content`/`--title`、`--status`、`--tag` |

实施说明：

- course/quiz 以**浏览 + 完成为主**，创建/编辑课程等复杂写走 `request` 兜底（字段多且含自动化配置）。
- `wechat-articles` 的 `page` 是 0 基偏移（`limit page*10,10`），status 默认 `read_later`。
- `wechat-add-note` 是对正文/标题任一非空即有效，`status` 默认 1，与 `note create` 语义一致。

---

## 7. 已知问题与修正（实施时必须处理）

1. **`feed quickstore` 参数不符（CLI 现有 bug）**：
   - 现状：`cmd_feed_quickstore` 发送 `url` / `category_id`。
   - 后端：`POST /feeds/quickstore` 校验 **`feed_id`**（required 且仅 `feed_id` this branch）——现状调用必然 422。
   - 修正：命令改为接收 `--feed-id`，`--url` 走 `POST /feeds`（store）或 `POST /feeds/quickstore` 前先 `check-url`/`store`。
   - 本次 spec 落地时一并修复，并更新 platform.md。

2. **`note update` 硬性要求 `status`**：
   - 现状：`cmd_note_update` 在 `status is None` 时报错。
   - 后端：PUT 是完整内容更新（note-logic 4.4 确认需 status）。
   - 处置：保留强制，笔记参考文档明确"更新必须带 status"，避免误用。

3. **Study / Note 多媒体文件**：
   - `study checkin`、`note record`、`notes/upload` 后端都是 **multipart/file**。
   - CLI 现状走 JSON；二进制上传不在本期 CLI 范围内（`note record` 走 upload 接口只是把本地上传的 mp3 交给后端，前端 CLI 用 urllib 传 multipart，实施时需在 CLI 内做 multipart 编码）。
   - `image` 笔记：`add_image` 存的是 URL/路径，不是二进制，CLI 可保留 `--data` 传 URL。

4. **白名单/权限**：
   - digest `pages`/`generate`、部分点商城动作受白名单或 scope 约束——参考文档须写明，模型调用前先查 `me`/`get-profile` 判断。

5. **OpenAPI 同步**：
   - 新增命令不影响 `routes/api.php`（仅新增 CLI 命令），如涉及新增后端（暂无），需同步 `openapi-v2.yaml`。

---

## 8. 参考文档更新计划

| 文件 | 新增/修订 |
| --- | --- |
| `references/articles.md` | 补 digest 汇合页、AI 画像预览、article clip 摘录流 |
| `references/notes.md` | 补语音记录玩法、图片/来源预填模板、公开审核说明、update 必须带 status |
| `references/platform.md` | 补 study 概览、digest profile/pages、journal、mind、achievement/points 字段速查；修正 feed quickstore 参数 |
| `references/study.md`（新建） | 学习打卡与计划生成专项（可选，若内容多则新建） |

## 9. 实施顺序（当前进度）

1. **P0 CLI 命令**：digest、study、note record、article clip — ✅ 已实现（含 multipart 处理与 feed quickstore bug 修复）。
2. **P1 CLI 命令**：feed explore/search/check-url、journal、mind、achievement/points — ✅ 已实现。
3. **P2 CLI 命令**：course/quiz、wechat — ✅ 已实现。
4. **参考文档**：articles.md / notes.md / platform.md 已更新，新建 study.md — ✅ 已完成。
5. **验证**：
   - ✅ 每个新增子命令 `--help` 解析通过。
   - ✅ 对每个 POST/PUT 逐一核对 `routes/api.php` 字段与控制器 validate 一致。
   - ✅ `python3 -m py_compile` 通过。
   - ✅ **live 冒烟已执行**：在本机拉起本地 Laravel（127.0.0.1:8000，连本地 MySQL）、铸造临时 PAT 后，对新增命令族（digest / study / journal / mind / achievement / points / course / wechat / note-record / article-clip / feed-quickstore 等）逐一冒烟，均返回 `code 9999`；`feed quickstore --feed-id` 修复确认生效（不再 422）。临时数据已清理。
   - 生产端点默认地址为 `https://task.congcong.us/api/v2`。
