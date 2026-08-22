# 课程 / 测验（Courses & Quizzes）

把用户「按某个资料/文档/网页创建一门课程」或「管理课程目录、章节、测验」的自然语言转成课程域动作。课程是**分层结构**：课程（course）→ 模块（module）→ 章节（chapter / video / reading / assignment / quiz）。

## 权限

- 查询/浏览：`read`。
- 创建课程/章节/测验、更新、删除、加入课程：`write`。
- 创建「我管理的课程」时，`created_by` 取当前 token 用户。

### public_status 与「能否学习」的关系（重要）

| public_status | 含义 | 详情页可见 | 可加入（join） |
| --- | --- | --- | --- |
| 1 | 私有（仅创建者） | 仅创建者 | 否 |
| 2 | 待审核 | 非创建者不可见 | 否 |
| 3 | 已审核（公开） | 所有人 | **是** |

- `GET /courses/{id}`（详情）、`POST /courses/{id}/join`、公开课程列表都只对 **public_status=3** 放行；`join` 对非 3 状态抛「无法加入未审核通过的课程」。
- 用户要「能学」一门课（出现小测试/标记完成按钮、进度记录），必须：课程 `public_status=3` **且** 用户已 join。
- 创建后默认 `public_status=2`（待审核）。**技能创建完课程若用户要求可直接学/公开，应明确把 `public_status` 设为 3**（或用 `course-update <id> --public-status 3`），否则用户「看不了学不了」。

## 课程字段（POST /courses、PUT /courses/{id}）

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `title` | string | **必填**，课程标题 |
| `description` | string | 课程简介 |
| `platform` | string | 所属平台 |
| `instructor` | string | 讲师/作者 |
| `public_url` | string | 公开地址 |
| `cover_image_url` | string | 封面图 URL |
| `difficulty` | enum | `beginner` / `intermediate` / `advanced`，默认 `beginner` |
| `estimated_hours` | int | 预计学习时长（小时） |
| `tags` | array | 标签数组，如 `["deepseek-harness","教程"]` |
| `public_status` | int | 1=公开、2=待审核（create 默认 2） |
| `content_status` | enum | `draft` / `published` / `archived`，默认 `published` |
| `source_type` / `source_key` / `content_hash` | string | 来源与幂等标识；同一创建者+`source_key` 重复会返回已有课程 |

## 章节字段（POST /courses/{courseId}/items、PUT /courses/{courseId}/items/{id}）

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `title` | string | **必填** |
| `item_type` | enum | **必填**：`module`（模块/分组）、`chapter`（章节）、`video`、`reading`、`assignment`、`quiz` |
| `parent_id` | int | 父级模块 id；顶层章节不传 |
| `order_index` | float | 排序，同层从 0 开始递增 |
| `content` | text | Markdown 正文 |
| `description` | string | 小节简介 |
| `duration` | int | 预计分钟数 |
| `external_url` | string(URL) | 外链（video/reading 常用） |
| `content_status` | enum | `draft` / `published` / `archived` |

> 结构约定：**先建 `module`（item_type=module，order 0,1,2…），再把可学习内容挂到 module 的 `parent_id` 下，同一父级内 order_index 从 0 递增**。这样课程页才会显示清晰的分组层级。

### ⚠️ 类型语义（前端渲染关键，别踩坑）

| item_type | 前端表现 | 何时用 |
| --- | --- | --- |
| `module` | 折叠容器，只显示标题 | 分组/模块 |
| `chapter` | **也是容器**（紧跟 module 的二级分组），不会渲染「阅读」按钮 | 需要二级分组的容器；**不要**拿它承载正文 |
| `reading` / `video` / `assignment` / `quiz` | 叶子课时，渲染「阅读/打开/小测试/标记完成」按钮 | **有 content（正文）的章节必须用这些类型**，文字内容用 `reading` |

> 前端判断 `isContainer = children 非空 || item_type in (module, chapter)`：凡是 `chapter` 一律当容器渲染，正文不会出现在学页面。**建正文章节务必用 `reading`，不要用 `chapter`**。

## 测验字段（POST /course-items/{itemId}/quiz）

```json
{
  "passing_score": 70,
  "attempts_allowed": null,
  "status": "published",
  "questions": [
    {
      "question_type": "single",
      "question": "题干",
      "explanation": "解析",
      "points": 1,
      "options": [
        { "option_key": "A", "content": "选项A", "is_correct": true },
        { "option_key": "B", "content": "选项B", "is_correct": false }
      ]
    }
  ]
}
```

- `question_type`：`single`（单选）/ `multi`（多选）等。
- 每章测验默认 3~10 题，且必须至少 1 题。
- 题库创建用 `quiz-create`（`--data` 传上面的 JSON）。

## 首选命令

```bash
$CLI course-list                          # 公开课程列表
$CLI course-management                    # 我创建的课程
$CLI course-show 4                        # 课程详情+结构
$CLI course-create --title "x" --description "..." --difficulty beginner --estimated-hours 3 --tags "a,b" --public-status 1
$CLI course-update 4 --title "x" --estimated-hours 8 --difficulty intermediate
$CLI course-enroll 4                      # 加入课程
$CLI course-item-create 4 --title "模块一" --item-type module --order-index 0
$CLI course-item-create 4 --title "1.1 引言" --item-type chapter --parent-id 9 --order-index 0 --content "正文" --content-file ./p.md
$CLI course-item-update 4 12 --order-index 1
$CLI course-item-delete 4 12
$CLI quiz-create 12 --data '{"questions":[...]}'
$CLI quiz-show 12                         # 查看测验
```

## 从文档 / 网页创建一门课程的步骤（端到端）

1. **先读全资料**：拿到来源 URL/文档后，先爬取**整棵树**（首页 + 所有子页 + 「下一步」链接指向的页面），不要只做首页。参考 `references/scenarios.md` 场景 9。
2. **设计结构**：按文档目录/分组建 `module`（0,1,2…），每个页面一节 `chapter` 挂到对应 module。
3. **补元数据**：title、description、difficulty、estimated_hours、tags、source_type（例如 `manual`/`external`）、cover_image_url（如有）。
4. **内容清洗**：正文转 Markdown 时要：
   - 去掉零宽空格（`\u200b`）和 `[](#锚点)` 残留；
   - 代码块语言标签要放到围栏上（` ```ts `），不要变成单独一行文本；
   - 保留原链接，但去掉无意义的目录锚点。
5. **加测验**：每个实质性章节配 3~5 题测验（`quiz-create`），题目来自该章内容。
6. **状态与公开**：默认 `published`；用户要求审核流程再改 `public_status`/`content_status`。
7. **收尾给入口**：课程建好后告知网页端入口（见 SKILL.md「网页端访问入口」）。

## 校验

- 非 2xx 或 `code != 9999` 视为失败。
- 批量/破坏性操作先列清单；拿到明确 id 再执行。
- 课程页面前端会读取 module 分组和 order_index，平铺无层级会导致展示混乱——建课程时务必建 module。