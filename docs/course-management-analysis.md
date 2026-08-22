# 课程管理模块深度分析报告

> 分析对象：课程中心（`/course/management`）、我的课程（`/courses`）、创建课程（`/courses/create`）、课程详情（`/courses/{id}`）、章节管理（`/courses/{id}/items`）及相关后端逻辑。
> 结论摘要：功能骨架完整（创建/加入/浏览/详情/测验/标记完成/积分/AI 生成都有），但存在 **7 个 P0 级功能缺口或失效**（章节弹窗打不开、无编辑入口、无内容阅读视图、无测验编辑、无审核流、进度不落库、统计恒为 0），易用性也有多处缺陷（移动端搜索失效、搜索与筛选不叠加、无分页、旧 Bootstrap 页面混用等）。

---

## 一、页面地图与入口

导航入口（`resources/views/layouts/app.blade.php` 第 109-116 行）：

| 菜单 | URL | 页面 |
| --- | --- | --- |
| 学习计划 | `/study` | 学习计划（任务式学习，与课程模块独立） |
| 学习打卡 | `/study/checkins` | 学习打卡 |
| 我的课程 | `/courses` | 我的课程（已加入的学习进度） |
| 课程中心 | `/course/management` | 课程中心（浏览公开课 + 管理我创建的） |

页面清单：

| 页面 | URL | 控制器 | 视图 | 数据来源 |
| --- | --- | --- | --- | --- |
| 课程中心 | `/course/management` | `CourseController@management`（web） | `courses/management.blade.php` | JS 调 v2 `GET /api/v2/courses/management` |
| 我的课程 | `/courses` | `CourseController@index`（web） | `courses/index.blade.php` | JS 调 v2 `GET /api/v2/courses` |
| 创建课程 | `/courses/create` | `CourseController@create`（web） | `courses/create.blade.php` | 表单提交 v2 `POST /api/v2/courses` |
| 课程详情 | `/courses/{id}` | `CourseController@show`（web） | `courses/show.blade.php` | JS 调 v2 `GET /api/v2/courses/{id}` |
| 章节管理 | `/courses/{id}/items` | `CourseItemController@index`（web） | `course-items/index.blade.php` + `components/course-item-modal.blade.php` | JS 调 v2 多个接口 |

v2 API 路由（`routes/api.php`）：`GET /courses`、`GET /courses/management`、`GET /courses/{id}`、`GET /courses/{id}/items`、`GET /course-items/structure/{id}`、`GET /course-items/{id}`、`GET /course-items/{id}/quiz`、`GET /course-items/{id}/quiz/attempts`、`POST /courses`、`PUT|POST /courses/{id}`、`DELETE /courses/{id}`、`POST /courses/{id}/join`、`POST /courses/{id}/publish`、`POST /courses/{id}/automation`、`POST /courses/{id}/generate|fetch`、`POST/PUT/DELETE /course-items`、`POST /course-items/{id}/complete`、`POST/PUT /course-items/{id}/quiz`、`POST /course-items/{id}/quiz/attempts`、课程讨论 4 个接口。

---

## 二、数据模型（字段与状态约定）

| 表/模型 | 关键字段 | 状态说明 |
| --- | --- | --- |
| `courses` | title, platform, instructor, public_url, cover_image_url, description, difficulty(beginner/intermediate/advanced), estimated_hours, tags, public_status, content_status, source_type/key, automation_config, created_by, user_id | `public_status`: 1=私有, 2=公开待审核(默认), 3=已审核公开；`content_status`: draft/published/archived |
| `course_items` | course_id, parent_id, title, item_type(module/chapter/video/assignment/quiz/reading), duration, external_url, description, **content(正文)**, order_index(float), source_type, content_hash, content_status | 支持无限层级（parent_id），但前端只渲染 2 层 |
| `course_enrollments` | user_id, course_id, title(可自定义), status(planned/active/completed/paused/dropped), goal, show_*, progress_percent, last_activity_at, order_index, start/target_end/completed_date | 用户加入课程的记录（“我的课程”数据源） |
| `user_progress` | user_id, user_course_id, course_item_id, status, mastery_status, mastery_score, completed_at, last_accessed_at, review_due_at, time_spent, rating, notes | 课时维度的学习进度（标记完成/测验时写入） |
| `course_quizzes` / `course_quiz_questions` / `course_quiz_options` / `course_quiz_attempts` | passing_score, attempts_allowed, question_type(single/multiple), 选项(option_key/is_correct), 答题记录(score/passed/answers) | 测验与答题记录 |
| `course_review_items` | user_id, user_course_id, course_item_id, status(due/mastered), review_count, interval_days, last_score, next_review_at | 基于熟练度的间隔复习队列 |
| `course_generation_runs` | course_id, mode(ai/fetch), status, items_count, error | AI 生成/抓取的执行记录 |
| `user_courses`（旁路） | 与 course_enrollments 类似 | 存在但无写入方，疑似遗留 |

**核心业务约定**：
- 课程权限：只有 `created_by`（创建者）可管理（编辑/删除/加章节/审核）；学习者必须先加入（join），join 前置条件是 `public_status == 3` 且未加入过。
- 删除课程前置校验：存在任何 enrollments 时禁止删除（抛异常）。
- 加入课程/创建课程/完成课时都会发积分（`PointGrantService`）。
- 测验评分：单/多选按“集合完全一致”判对；及格线 `passing_score`；及格且历史及格 ≥2 次 → mastered；同时写入 `course_review_items` 形成复习队列。
- AI 生成（`CourseContentService`）：`generate` 走 LLM 结构化任务生成章节 JSON；`fetch` 抓取 URL 正文（30k 截断）入库。

---

## 三、逐页逻辑详解

### 1. 课程中心 `/course/management`（`courses/management.blade.php`）

- 布局：标题栏（搜索框 + 创建按钮）→ 统计（公开课程数/我创建数）→ 筛选（难度/平台/排序）→ 双 Tab（我创建的课程 / 公开课程）→ 底部 3 张快捷创建卡（视频/文档/外部课程）。
- 数据流：`loadCourseManagementData()` 调 v2 `GET /courses/management`，一次性拿 `public_courses`、`user_created_courses`、`user_course_ids`，JS 渲染卡片。
- 交互：搜索（防抖 300ms，仅标题+描述，**客户端过滤**）；筛选（难度/平台，客户端 DOM 显隐）；排序（最新/最受欢迎/时长升降序，**客户端 DOM 重排**）；加入课程走 `POST /courses/{id}/join`；删除走 `DELETE /courses/{id}`（confirm 后反向）。
- 卡片动作：公开课卡 → 查看详情/加入；我创建的卡 → 查看详情 / **管理章节（齿轮）** / 删除。

### 2. 我的课程 `/courses`（`courses/index.blade.php`）

- 顶部 4 个统计卡：总课程数、已完成、学习中、平均进度。
- 列表：横向卡片（封面/标题/描述/状态徽章/进度条/章节数/时长/最后学习），排序（最近学习/进度升序降序/名称），状态筛选（全部/学习中/已完成/计划中/暂停）。
- 数据流：`GET /api/v2/courses` → `serializeUserCourses()` 序列化 enrollments（冗余课程字段，`chapters_count` 来自 `withCount('courseItems')`）。
- 动作：点击卡片 → 课程详情；状态徽章颜色映射 planned/active/completed/paused/dropped。

### 3. 创建课程 `/courses/create`（`courses/create.blade.php`）

- 完整表单：标题*、讲师、平台（固定下拉）、难度（三选卡片）、预计时长、课程链接、封面 URL（实时预览）、标签（逗号分隔 + 快捷标签）、描述（500 字）、公开状态（私有/公开待审核 单选卡片）。
- 校验：后端 validate（title required 等）+ 前端必填 toast；字符计数（标题 100/标签 100/描述 500）；图片 URL 允许的扩展名白名单。
- 提交：`POST /api/v2/courses`，成功 toast 后 300ms 跳 `/courses`。支持 `?type=video|document|external` 快捷入口（仅写 `source_type`）。
- 后端 `store`（v2）：创建后发 `course_created` 积分。

### 4. 课程详情 `/courses/{id}`（`courses/show.blade.php`）

- 头部：标题/状态徽章（私有/待审核/已审核）/动作区（创建者：管理章节+返回；未加入：加入课程；已加入：已加入+继续学习）。
- 信息卡：讲师/平台/难度/时长/标签 + 描述卡（可折叠）；封面图。
- 课程结构卡：章节树（**只展开 2 层**：chapter → lesson），课时图标按类型（video/quiz/assignment/reading），已加入用户每个课时有“小测试”“标记完成”按钮；答题在弹窗内完成（`GET /course-items/{id}/quiz` + `POST .../quiz/attempts`），提交后展示每题对错与解析。
- 标记完成：`POST /course-items/{id}/complete` → 写 UserProgress(completed, 100 分) + 发积分。
- 权限：`public_status==3` 公开可见；否则仅创建者可见（V2 `show`）。

### 5. 章节管理 `/courses/{id}/items`（`course-items/index.blade.php` + `components/course-item-modal.blade.php`）

- 页面：Bootstrap 风格旧页（`list-group`/`btn-sm`）+ 右侧“添加章节”按钮；树形 list 渲染（可多层递归展示，但节点操作只有编辑/删除）。
- 模态框（组件）：新设计系统风格，字段：父级章节（树形下拉）、章节类型（module/chapter/video/assignment/quiz/reading 带图标与说明）、标题、时长、排序、外部链接、描述（500 字计数）。
- 提交：`POST /course-items` 或 `PUT /course-items/{id}`（章节内容 `content` 字段虽在 API 支持但**表单里没有**）。
- **严重问题**：页面内联脚本与组件脚本定义了**两套同名函数**（`openCourseItemModal`/`submitCourseItemForm`/`loadCourseStructure`），后加载的 index 脚本覆盖组件版本，其内部调用 `$('#courseItemModal').modal('show')`——但全局并未加载 Bootstrap JS（见问题 #1），导致弹窗无法打开，“添加/编辑章节”实际不可用。

---

## 四、问题清单（按严重度）

### P0 — 功能缺口 / 失效

1. **章节管理弹窗打不开，章节管理功能实际不可用**
   - `course-items/index.blade.php` 内联脚本调 `$('#courseItemModal').modal('show'|'hide')`；布局（`layouts/app.blade.php`）**未加载 Bootstrap JS**，编译后的 `public/js/app.js` 中也无 `fn.modal`（0 匹配）。`jQuery(...).modal` 未定义 → 直接 TypeError。
   - 同时 `components/course-item-modal.blade.php` 自带的打开/关闭逻辑基于自定义 CSS（`.modal.show` visibility 方案），两套实现冲突，同名函数重复定义（index 的覆盖组件的）。
   - 影响：新增/编辑/删除章节的入口全部失效（删除是直接调 API 不受影响，但添加/编辑点不开弹窗）。

2. **没有课程编辑入口（update API 存在但无 UI）**
   - v2 `PUT /courses/{id}` 完整支持，但管理页“我创建的课程”卡片只有“查看详情/管理章节/删除”，无编辑按钮，无编辑页面/弹窗。课程信息错了只能删了重建。

3. **没有章节正文（content）展示视图，学习者无法阅读课程内容**
   - `course_items.content` 字段 API 支持读写（AI 生成/抓取会写入），但展示端：
     - 章节管理表单**没有 content 输入框**；
     - 课程详情 `show.blade.php` 的 `renderCourseStructure` 只渲染 标题/时长/类型/章节描述，**不渲染 content，也不渲染 external_url 跳转**。
   - 影响：学习者点开章节什么都读不到；AI 生成的内容也无处查看，功能链断裂。

4. **没有测验编辑 UI（quiz 创建/编辑 API 存在但无页面）**
   - `POST/PUT /course-items/{id}/quiz`（`CourseQuizService::saveQuiz` 支持题目/选项/正确项/解析/及格分）后端完备，但没有任何页面提供测验编辑器；只有 show 页的“答题”弹窗。课程创建者无法给自己的章节配测验。

5. **审核流是死代码，公开待审核课程永远无法通过审核**
   - `CourseService::approveCourse/unapproveCourse`（public_status 2↔3）**没有任何路由/控制器调用**；也没有管理员审核页面。新创建的“公开待审核”课程永远停在 2——除非手动改库。公开课程库只能靠种子数据或直接入库。

6. **学习进度不落库，我的课程进度条恒为 0**
   - `course_enrollments.progress_percent`、`last_activity_at` 在全部代码中**没有任何写入方**（grep 仅命中 `AchievementService` 自己的统计对象）。标记完成/测验只写 `user_progress`，未回滚聚合到 enrollment。
   - 影响：我的课程页“学习进度 0%”“最后学习：未开始”永远不变；`status`（planned/active/completed/…）也**没有更新 API 和 UI**（筛选器里的“暂停/放弃”无从触发）。

7. **课程中心统计与排序失效（章节数/学习人数/最受欢迎恒为 0）**
   - 管理页 API（v2 `management`）返回的 `public_courses`/`user_created_courses` 是裸 `Course` 集合，**没有 `withCount('courseItems')` 也没有聚合 enrollment 数**（`CourseRepository::getPublicCourses/getUserCreatedCourses`）。
   - 前端卡片读 `chapters_count`/`enrollment_count` → 永远是 `Number(undefined||0)=0`：“X 章”“学习人数: 0”失真，“最受欢迎”排序退化为无效。

### P1 — 易用性 / 交互缺陷

8. **移动端搜索失效**：`initSearch()` 只对 `#courseSearch`（桌面输入框，移动端 `hidden`）绑监听，移动端可见的 `#mobileCourseSearch` 从未绑定 → 手机上搜索框无效。
9. **搜索与筛选不叠加**：搜索按标题/描述 `display` 隐藏卡片，再切换难度/平台筛选时 `filterCourses` 只按筛选条件重算 display，被搜索隐藏的卡片会“复活”，两个条件互相覆盖。
10. **无分页/无限滚动**：公开课程全量一次性加载+客户端过滤，课程量上来后首屏和交互都会劣化。
11. **删除失败反馈粗糙**：删除课程时服务端对“有关联学习记录”抛异常，前端只显示 `msg`，用户不知道原因；也没有会连带影响的提示（如有多少人加入）。
12. **返回/跳转链路混乱**：详情页“返回课程中心”与我的课程“浏览课程”语义重叠；管理章节页“返回课程”指向 `/courses` 而非课程详情；创建页面包屑指向 `/courses`。
13. **结构树只展示 2 层**：`show.blade.php` 只渲染 `children` 一层，更深嵌套不可见（模型支持无限层）。
14. **旧 Bootstrap 页面风格割裂**：章节管理页用 `list-group`/`alert()`/`confirm()`，与全站 Tailwind + SweetAlert2 体系不一致。
15. **加入课程的自定义标题能力未暴露**：join API 支持 `title` 自定义（`CourseService::joinCourse` 有 `$customTitle`），UI 直接传入 `{}`，能力闲置。

### P2 — 技术债 / 健壮性

16. **N+1 查询**：`CourseService::getCourseStructure` 逐节点递归 `getCourseItems`，章节多时 DB 查询次数 = 节点数。
17. **web 与 v2 控制器重复**：`CourseController`（web）与 `Api\V2\CourseController`、`CourseItemController`（web）与 v2 版大量重复校验/权限/数据构造逻辑，维护成本高。
18. **路由潜在冲突**：web.php 里 `Route::resource('courses')` 的 `GET /courses/{course}` 会把 `/courses/management`（复数路径）误匹配进 `show('management')`（仅返回空壳视图）。当前前端走 `/api/v2` 不受影响，但直接访问 `/courses/management` 会得到空白详情页。
19. **XSS/注入面**：卡片渲染 `cover_image_url` 直接进 `img src`、删除按钮 `onclick` 内嵌标题（虽有 `escapeHtml`，但 onclick 字符串里引号处理仍粗糙）；`course-items` 页 `badge` 直接 `escapeHtml` OK，但全部字符串拼接易漏点。
20. **字段形态不一致**：`estimated_hours` 显示“X小时”，章节 `duration` 显示“X分钟”；`chapters_count`（管理页用）与 `course_items_count`（我的课程用）命名不统一；`user_courses` 表与 `course_enrollments` 并存疑似遗留。

---

## 五、优化建议

### A. 功能完整性（把“管理闭环”补齐）——按优先级

| 优先级 | 建议 | 落地要点 |
| --- | --- | --- |
| P0 | **修复章节管理弹窗** | 删除 `course-items/index.blade.php` 中与组件重复的 `openCourseItemModal/submitCourseItemForm/loadCourseStructure`，统一走组件自带的 show/hidden 方案（自定义 `.modal.show` CSS 已全局就绪）；或整体重写为新设计系统页面。 |
| P0 | **补课程编辑** | 复用创建表单做成“编辑课程”页/弹窗，`PUT /courses/{id}`；在“我创建的课程”卡片加“编辑”按钮；保存后刷新列表。 |
| P0 | **补章节正文展示 + 编辑** | ① 章节管理表单加 `content` 正文编辑框（textarea/Markdown 简单渲染）；② 详情页课时节点增加“阅读/查看内容”（渲染 `content`）与“打开外部链接”（`external_url`）按钮；③ 让 `GET /course-items/{id}` 承载内容供详情页弹窗展示。 |
| P0 | **补测验编辑器** | 章节管理里对 quiz 类型课时提供“配置测验”入口：题目列表（单选/多选）、选项+正确答案、解析、及格分，调 `PUT /course-items/{id}/quiz`。 |
| P0 | **打通审核流** | 给 `approveCourse/unapproveCourse` 挂上路由（管理员/创建者可再次提交审核）；课程中心公开 Tab 或管理后台加“待审核列表”；审核通过后通知创建者（站内通知）。 |
| P0 | **进度闭环** | 标记完成/测验提交成功后：汇总该课程已完成课时数 ÷ 总课时数 → 更新 `course_enrollments.progress_percent`，并刷新 `last_activity_at`；提供 `PUT /course-enrollments/{id}` 更新 status（planned→active→completed/paused/dropped）与目标日期，UI 在“我的课程”卡片加“更新状态/暂停/完成/放弃”菜单。 |
| P0 | **修复统计与排序数据源** | `CourseRepository::getPublicCourses/getUserCreatedCourses` 增加 `withCount('courseItems')` 与 enrollments 聚合（子查询 count），把 `chapters_count`/`enrollment_count` 返回给前端，让“最受欢迎”排序真正生效。 |

### B. 易用性优化

9. **卡片升级**：我创建的课程卡显示公开状态 + 学习人数 + 章节数 + 编辑/删除/管理章节；公开课程卡保持“加入/查看详情”，已加入的显示“继续学习”直达详情；统一卡片组件（当前 3 处重复渲染函数）。
10. **搜索服务端化**：关键词 + 难度 + 平台 + 排序交给 v2 API（Laravel where/sort），前端分页或“加载更多”；至少修复移动端搜索绑定（两个输入框都绑）与筛选/搜索叠加逻辑（单一 `applyFilter+Search` 函数）。
11. **Toast/Swal 统一**：全站用 SweetAlert2 确认框 + 页内 Toast；删除课程前提示影响面（若有关联学习者，明确告知不可删除或改为“归档”）。建议提供“软删除/归档”替代硬删除限制。
12. **导航一致性**：详情页返回按钮按来源（管理页/我的课程）动态生成；统一“课程中心”为课程模块总入口。
13. **结构树增强**：支持多层展开、显示每章课时数与已学数（基于 user_progress 聚合）、课时状态（已完成/需复习）。
14. **空状态**：公开课程/我的课程/章节树空状态统一插画+引导按钮（已有雏形，统一风格即可）。

### C. 性能与技术债

15. **消除 N+1**：`getCourseStructure` 一次 `where course_id` 拉全量（`course_items`）后在内存按 `parent_id` 建树；列表页统一 `withCount`。
16. **控制器收敛**：web 控制器只渲染视图，业务统一走 v2 API（现状大半已经是）；删除 web 侧重复的 store/update/destroy 实现或改为薄转发。
17. **防路由冲突**：把 web 的 `/courses/management` 复数路径在 resource 之前显式注册，或在 resource 中排除；与 v2 语义统一。
18. **前端组件化**：抽出 `course-card.js`、`renderStructure.js`、`api.js`（统一用 `TaskApiBridge`）、`ui.js`（Toast/Confirm），三个页面复用，消除内联大段重复脚本。
19. **补测试与迁移**：为 join/complete/quiz 流补 PHPUnit；若引入归档/进度聚合字段，用新迁移实现。

### D. 建议的最终信息架构

```
课程中心 /course/management（唯一入口，登录后默认 Tab=我的）
├─ Tab 我的课程（我创建的：编辑/管理章节/删除 + 我加入的：进度/继续学习/状态管理）
├─ Tab 公开课程（服务端搜索/难度/平台/排序/分页，加入、收藏）
├─ 创建课程 → 创建后引导三步：填信息 → 配章节/测验 → 提交审核
└─ 课程详情（阅读章节正文 / 做测验 / 讨论 / 标记完成 / 进度聚合）
```

**分阶段落地建议**：Phase 1 修 P0（弹窗修复→章节正文展示→课程编辑→进度闭环→统计聚合）；Phase 2 补功能（测验编辑器→审核流→状态管理→服务端搜索分页）；Phase 3 体验与技术债（组件化、N+1、路由收敛、导航统一）。

---

## 六、附：关键代码路径速查

- 页面渲染：`resources/views/courses/{management,index,create,show}.blade.php`、`resources/views/course-items/index.blade.php`、`resources/views/components/course-item-modal.blade.php`
- 控制器：`app/Http/Controllers/CourseController.php`、`CourseItemController.php`、`app/Http/Controllers/Api/V2/{CourseController,CourseItemController,CourseQuizController,CourseContentController,CourseReviewController,DiscussionController}.php`
- 业务：`app/Services/{CourseService,CourseContentService,CourseQuizService}.php`
- 数据：`app/Repositories/{CourseRepository,CourseItemRepository,CourseEnrollmentRepository}.php`
- 模型：`app/Models/{Course,CourseItem,CourseEnrollment,UserProgress,CourseQuiz,CourseQuizQuestion,CourseQuizOption,CourseQuizAttempt,CourseReviewItem,CourseGenerationRun}.php`
- 路由：`routes/web.php`(261-296)、`routes/api.php`(136-330)
- 前端桥：`public/js/hybrid-api-client.js`（`TaskApiBridge.requestWithFallback` → `/api/v2`）