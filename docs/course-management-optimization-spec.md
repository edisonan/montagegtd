# 课程管理模块优化 SPEC v1.0

> 目标：修复"首次进入课程中心不展示列表"现象，并补齐课程管理全功能闭环（浏览/创建/编辑/章节/内容/测验/审核/进度/易用性）。
> 依据：`docs/course-management-analysis.md`（上一轮深度分析）+ 本轮代码级定位与本地实测（见 §2 证据链）。
> 环境：Laravel 5.5 / PHP7 / Vue2 + jQuery + Tailwind；前端统一走 `TaskApiBridge.requestWithFallback` → `/api/v2`。

---

## 1. 现状结论（一句话）

课程模块骨架完整（创建/加入/浏览/详情/测验/完成/积分/AI 生成均有后端支撑），但存在 **4 类根因导致"首次进入课程中心看不到列表"**，以及 **7 个 P0 功能缺口**（章节弹窗打不开、无课程编辑、无正文阅读、无测验编辑、审核流死代码、进度不落库、统计恒 0），易用性有多处缺陷。

---

## 2. 现象根因分析（实测证据链）

### 现象
首次（登录后）进入课程中心 `/course/management`：列表区域空白或永远显示"加载课程中..."；点击 Tab（我创建的课程 / 公开课程）后才出现内容。

### 根因 1（直接原因 · 前端渲染级 Bug）— 初始两个 Tab 面板全部 `display:none`

- 模板 `resources/views/courses/management.blade.php` L141：登录用户时
  `my-courses` 面板类为 `tab-pane active hidden`（**`active` 与 `hidden` 并存**）；
  `public-courses` 面板类为 `tab-pane hidden`（L131）。
- JS `initTabSwitching()` L379-380 存在 **Blade 字符串布尔陷阱**：
  `if (!{{ auth()->guest() ? 'true' : 'false' }})` 对登录用户渲染为 `if (!'false')`，
  非空字符串 `'false'` 取反 = `false` → **永远走 else 分支**，把 `public-courses` 设为激活。
- 但 else 分支只 `classList.add('active')`，**从不 `remove('hidden')`**（L386-388）；
  模板给 `my-courses` 的 `active hidden` 也没被清理。
- 结果：加载完成后 `public-courses` 面板 = `hidden + active`、`my-courses` 面板 = `hidden + active`，
  两者均 `display:none` → **列表区域空白**；只有用户点击 Tab（点击逻辑 L404-414 会 `remove('hidden')`）才可见。
- 附带影响：搜索/筛选/排序作用于"当前激活面板"，初始空白时用户操作无反馈。

### 根因 2（数据层）— 公开课程恒为空

- v2 `CourseController@management` → `getPublicCourses(false, false)` 只返回 `public_status == 3`；
  `CourseRepository::getPublicCourses`（L119-134）无其他来源。
- 本地实测：`SELECT public_status, COUNT(*) FROM courses GROUP BY public_status` → **仅 `1` 两条，`3` 为 0**。
- 且 `approveCourse/unapproveCourse`（`CourseService` L267-294）**无任何路由/控制器调用**（死代码）→
  新建课程默认 `public_status=2`（待审核）永远无法变为 3 → 公开课程库无法自然增长。
- 带 token 实测 `GET /api/v2/courses/management` → `"public_courses": []`。

### 根因 3（认证层）— 无 token 一律 401，且前端静默吞错

- `HybridTokenMiddleware`（L29-35）**强制要求 `Authorization: Bearer`**，无头/空头直接 401。
- 已登录用户首屏：layout 的 `bootstrap-session`（`web.php` L245，`auth` 中间件）异步签发 token pair 写入 localStorage；
  **若该 fetch 失败**（网络/CSRF/502 等），`__taskBootstrapAccessToken` catch 后 `resolve(null)`，
  `TaskApiClient` 无 token → 后续页面请求无 Bearer → **401**。
- 前端 `loadCourseManagementData()` 的 `.catch(function() {})`（L306）**静默** → 页面停留在"加载课程中..."永不刷新。
- 实测：`GET /api/v2/courses/management`（无 token）→ `HTTP 401 {"code":401,"msg":"Invalid authorization header format..."}`。
- 注：页面本身有 `auth` 中间件（guest 会被重定向登录），故该问题只影响**已登录用户 bootstrap 失败**的首次会话（localStorage 尚无 token 时最易触发）。

### 根因 4（数据管道）— 卡片统计/进度字段缺失或恒 0

- `getPublicCourses/getUserCreatedCourses` 返回裸 `Course` 集合，**无 `withCount('courseItems')` 也无 enrollment 聚合**；
  前端 `renderPublicCourseCard/renderCreatedCourseCard` 读 `chapters_count`/`enrollment_count` →
  `Number(undefined||0)=0` → "X 章 / 学习人数 0 / 最受欢迎排序"全部失真（实测响应中确实无这两个字段）。
- `course_enrollments.progress_percent / last_activity_at` **全代码库无写入方** → "我的课程"进度条恒 0%、最后学习恒"未开始"（实测 `progress_percent=0, last_studied_at=""`）。

---

## 3. 优化目标（验收导向）

1. **首次进入课程中心，列表立即可见**（默认展示一个有内容的 Tab + 明确空态引导）。
2. **公开课程可见性闭环**：创建的公开课程可进入"待审核→已公开"流程；公开列表有真实内容。
3. **管理闭环**：课程的增删改查、章节增删改/排序、正文编辑、测验配置全链路可用。
4. **学习者闭环**：可阅读章节正文、可跳转外链、可做测验、可标记完成；进度/时间真实落库并回显。
5. **易用性**：服务端搜索+分页、移动端可用、组合筛选正确、提示统一（Toast/Swal）、导航一致。
6. **稳定与性能**：消除 401 静默失败、消除 N+1、收敛重复控制器、统一前端组件。

---

## 4. 功能需求（FR 清单，完整功能矩阵）

| 编号 | 功能 | 当前状态 | 目标 | 优先级 |
| --- | --- | --- | --- | --- |
| FR-1 | 首次进入列表可见 | ✗ 空白（根因1） | 登录默认展示"我创建的课程"；guest(不可达页)默认"公开课程"；面板类正确 | P0 |
| FR-2 | 401/加载失败兜底 | ✗ 静默"加载中"（根因3） | 加载态带失败提示+重试按钮；401 时提示登录/刷新 | P0 |
| FR-3 | 公开课程有内容 | ✗ 恒空（根因2） | 审核流打通；公开列表含待审核可见性规则；空态引导创建 | P0 |
| FR-4 | 课程编辑 | ✗ 无 UI | "我创建的"卡片加编辑，`PUT /courses/{id}` 复用创建表单 | P0 |
| FR-5 | 卡片数据真实 | ✗ 恒 0（根因4） | API 返回 `chapters_count`/`enrollment_count`；排序有效 | P0 |
| FR-6 | 进度闭环 | ✗ 恒 0 | 完成/测验后聚合更新 `progress_percent`、`last_activity_at`；提供 enrollment 状态更新（planned/active/completed/paused/dropped） | P0 |
| FR-7 | 章节管理弹窗 | ✗ 打不开（Bootstrap modal 未加载 + 同名函数覆盖） | 统一走组件自定义 `.modal.show` 方案；删除 index 页重复函数 | P0 |
| FR-8 | 章节正文编辑与阅读 | ✗ 无 | 管理表单加 `content` 编辑；详情页课时可查看正文/打开外链 | P0 |
| FR-9 | 测验编辑器 | ✗ 仅答题 | 章节管理中"配置测验"：题目/选项/正确答案/解析/及格分，`PUT /course-items/{id}/quiz` | P0 |
| FR-10 | 服务端搜索+分页 | ✗ 全量客户端 | management API 支持 `q/难度/平台/排序/page`；前端分页或加载更多 | P1 |
| FR-11 | 移动端搜索/组合筛选 | ✗ 移动端未绑定、搜索筛选互相覆盖 | 两输入框都绑定；单一过滤函数叠加搜索+筛选+排序 | P1 |
| FR-12 | 章节树增强 | ✗ 2 层 | 多层展开；课时显示已学/完成状态（基于 user_progress） | P1 |
| FR-13 | UX 统一 | ✗ alert/confirm/Bootstrap 混用 | 全站 SweetAlert2 + Toast；导航/面包屑一致；空态统一 | P1 |
| FR-14 | 性能与架构 | ✗ N+1/重复控制器 | `getCourseStructure` 内存建树；web 控制器薄化；路由冲突消除 | P2 |

---

## 5. 技术方案（按模块，含代码级修复要点）

### 5.1 FR-1 首次进入列表可见（修复前端渲染）

文件：`resources/views/courses/management.blade.php`

1. **模板**：登录用户初始只允许一个面板可见且不冲突。
   - `my-courses`：`class="tab-pane active"`（去掉 `hidden`，登录默认可见）。
   - `public-courses`：`class="tab-pane hidden"`（登录时隐藏）。
   - guest（页面临时不可达，但保留正确性）：`public-courses` 为 `active`，不渲染 `my-courses`。
2. **JS `initTabSwitching()`**：
   - 用 PHP 输出版本控制逻辑：`var isGuest = {{ auth()->guest() ? 'true' : 'false' }};`
     （输出布尔字面量 `true`/`false`，而非字符串 `'true'/'false'`——直接 `!isGuest` 即可，消除字符串陷阱）。
   - 激活分支统一封装 `activatePane(tabId)`：先对所有 pane `add('hidden')`/`remove('active')`，
     再对目标 pane `remove('hidden')` + `add('active')`；按钮样式同步。
   - 初始化时对默认 Tab 调用 `activatePane(initialTab)`（登录=`my-courses`，guest=`public-courses`）。
3. 回归点：点击 Tab 切换、初始渲染、搜索/筛选作用于可见面板。

### 5.2 FR-2 401/加载失败兜底（消除静默）

文件：`resources/views/courses/management.blade.php`（及 `index/show` 同款逻辑）

1. `loadCourseManagementData()` 增加状态机：`loading → success | failed(empty) | error`。
   - error 分支渲染可重试提示（按钮重新调用加载），并区分"登录失效(401，提示重新登录)"与"网络错误"。
   - 空数据渲染**有引导的空态**（FR-3），而非停留在"加载课程中..."。
2. 统一错误码判断：`resp.code !== 9999` 时也走 error 分支（当前是静默 return）。
3. 可选：`requestWithFallback` 401 后主 Layout 触发一次 `bootstrap-session` 重试（见 5.8 基础设施）。

### 5.3 FR-3 公开课程可见性闭环（数据生态）

1. 给 `approveCourse/unapproveCourse` 挂路由（管理员或创建者提交审核）：
   - `POST /api/v2/courses/{id}/approve`、`POST /api/v2/courses/{id}/unapprove`（仅创建者/管理员）。
2. 管理页"我创建的课程"卡片：待审核(2)显示"提交审核/重新提交"；已公开(3)显示"公开中"；私有(1)可"申请公开"。
3. 公开列表规则调整（可见性）：
   - 默认展示 `public_status=3`（已审核公开）；
   - 允许"公开待审核(2)"在下拉筛选"仅看公开/含待审核"中展示（复用 `getPublicCourses($withTrashed, $includePending)` 的 `$includePending` 参数，当前传了 `false`）。
4. 空态引导：公开列表空时，未登录体验文案 + 登录后引导"创建课程并从公开待审核开始"。

### 5.4 FR-5 卡片数据真实（聚合）

文件：`app/Repositories/CourseRepository.php`

```php
public function getUserCreatedCourses($userId, $withTrashed = false) {
    $query = Course::withCount('courseItems')
        ->withCount(['courseEnrollments'])  // 需要 Course 模型 add 关联方法注：
        // courseEnrollments 已存在（Course.php L56-59）
        ->where('created_by', $userId);
    ...
}
// 同样给 getPublicCourses / getAllCourses 加 withCount('courseItems') + withCount('courseEnrollments')
```

- 说明：`withCount('courseEnrollments')` 生成 `course_enrollments_count`，前端读 `enrollment_count` 需映射（后端统一输出字段别名 `enrollment_count`，见 5.9 契约）。
- 管理 API `management()` 与 `show()` 返回时统一序列化这两个计数（与 `serializeUserCourses` 的 `chapters_count` 键名对齐，前端统一用 `chapters_count`/`enrollment_count`）。

### 5.5 FR-6 进度闭环（学习引擎）

文件：`app/Services/CourseService.php`（新增 `recomputeEnrollmentProgress`）、`Api/V2/CourseItemController@complete`、`CourseQuizService@submit`

1. 新增方法：给定 `user_course_id`，统计该课程已 `completed` 的 `user_progress` 数 ÷ `course_items` 总数 → 更新 `progress_percent`；写 `last_activity_at = now()`；全部完成时 `status='completed'`、`completed_date=now()`；首次完成至少 1 课时时 `status='active'`（若为 planned）。
2. 触发点：`complete()`（标记完成）、`CourseQuizService::submit`（测验及格视为完成该课时，写 user_progress 后同样聚合）。
3. 新增 enrollment 状态/目标日期更新接口：
   - `PUT /api/v2/course-enrollments/{id}`（`status/goal/target_end_date/show_*`），仅本人。
4. UI：我的课程卡片加"更新状态"菜单（暂停/完成/放弃/恢复）与目标日期设置；详情页"继续学习/复习课程"按钮在 completed 时可用。

### 5.6 FR-7 章节管理弹窗修复

文件：`resources/views/course-items/index.blade.php`、`resources/views/components/course-item-modal.blade.php`

1. 删除 index 页内重复定义的 `openCourseItemModal/submitCourseItemForm/loadCourseStructure/cancelEdit/deleteItem 中使用 Bootstrap` 相关调用（`.modal('show'/'hide')`），统一使用组件自带的 `openCourseItemModal(courseId, itemData)`（基于 `.show`/`.hidden` class，layout 全局 CSS 已支持 `.modal.show`）。
2. 删除 index 页对 `$('#courseItemModal').modal(...)` 的一切调用；`deleteItem` 用 SweetAlert2 确认。
3. 组件内暴露 `window.refreshCourseStructure`；index 页成功回调调用它刷新树（替代 `location.reload()`）。
4. 回归点：添加/编辑/删除章节全链路。

### 5.7 FR-8 / FR-9 正文与测验（内容完整）

文件：`components/course-item-modal.blade.php`（加字段）、`course-items/index.blade.php`（加按钮）、`courses/show.blade.php`（加阅读视图）、`Api/V2/CourseQuizController`（已有保存/提交，无改动）＋新建测验编辑 UI

1. 章节表单增加 `content` 正文 textarea（纯文本/简单 Markdown 占位渲染），后端 `storeFromModal/updateFromModal` 已支持 `content` 字段（`Api/V2/CourseItemController` L118/L133/L180/L194），无需改后端。
2. 详情页课时节点：
   - 有 `content` → "阅读"按钮，弹层渲染（`white-space: pre-wrap`）。
   - 有 `external_url` → "打开链接"按钮（target=_blank）。
3. 测验编辑 UI（新）：章节管理中，`item_type=quiz` 的节点显示"配置测验"：
   - 题目列表（增删/排序）、题型（single/multiple）、选项+勾选正确答案、解析、及格分(passing_score)、作答次数限制。
   - 调 `PUT /course-items/{id}/quiz`（`CourseQuizService::saveQuiz` 已支持全部字段）。

### 5.8 基础设施

1. **401 兜底重试**（`public/js/hybrid-api-client.js` 或 layout 内联）：`bootstrap-session` 失败时允许一次重试；`requestWithFallback` 在 401 且 `__TASK_FORCE_API__` 时，尝试 `POST /api/v2/auth/bootstrap-session` 重签后再请求一次。
2. **N+1 消除**（`CourseService::getCourseStructure`）：一次 `CourseItem::where('course_id')->orderBy('order_index')->get()`，内存按 `parent_id` 建树（现为逐节点查库）。
3. **web 控制器薄化**：`CourseController/CourseItemController`（web）只 render 视图；删 web 侧 store/update/destroy 重复实现或改为转发。
4. **路由冲突**：`/courses/management` 在 `Route::resource('courses')` 之前显式注册（web.php L276 之前），避免被 `{course}` 捕获。

### 5.9 API 契约变更表

| 端点 | 变更 |
| --- | --- |
| `GET /api/v2/courses/management` | result 中 `public_courses`/`user_created_courses` 增加 `chapters_count`、`enrollment_count`；支持 `q/difficulty/platform/sort/page/page_size` 可选参数 |
| `PUT /api/v2/course-enrollments/{id}` | 新增：本人更新 status/goal/target_end_date/show_* |
| `POST /api/v2/courses/{id}/approve\|unapprove` | 新增：公开状态流转（创建者/管理员） |
| `GET /api/v2/courses/{id}` | course 增加 `chapters_count`/`enrollment_count`/`is_owner` |

#### 沉浸学习页（Study Mode）追加记录

- 新页面：`GET /courses/{id}/study`（web，auth）→ `resources/views/courses/study.blade.php`，全屏隐藏主站导航（`$hideAppShell`），专注章节学习。
- API 变更：`GET /api/v2/courses/{id}` 在已加入时，`structure` 每个节点附加 `is_completed`（`CourseService::attachUserProgressToStructure`），供学习页展示已学状态与"下一节未学"自动定位；支持 `?item=` 深链定位章节。
- 进度分母修正：`CourseService::recomputeEnrollmentProgress` 分母改为"非容器课时"（排除 module/chapter 类型及有子章节的节点），与前端仅对课时开放"标记完成"的规则对齐，修复多层课程进度无法到 100% 的问题。
- 学习页能力：章节抽屉（已学/当前高亮）、Markdown 正文、外链、小测验（及格自动记完成）、标记完成（自动前进下一节未学）、上/下一节与方向键导航、AI 制品生成（复用 `artifacts._dialog`）。
- 入口接线：`/courses` 卡片 CTA、详情页"继续学习"、课程中心已加入卡片 → 均指向 `/courses/{id}/study`（原详情页内"继续学习→/courses"怪按钮已修正）。

### 5.10 数据库变更

- 无新表；如需要可为 `course_items.order_index`、`courses.public_status` 补索引迁移（可选）。
- `course_enrollments` 现有列已够用（progress_percent/last_activity_at/status/日期字段均在）。

---

## 6. 验收标准（可测试）

1. **首次进入**：登录后直达 `/course/management`，无需点击任何 Tab 即可看到"我创建的课程"列表（或引导空态）；公开 Tab 数据正确。
2. **无 token/401 场景**：模拟 bootstrap 失败（清 localStorage 后强制）→ 页面显示可重试错误提示而非"加载中"。
3. **公开可见性**：创建者将课程提交公开 → 状态流转 1→2→3 全链路；公开列表可见。
4. **卡片数据**：章节数/学习人数与 DB 一致；"最受欢迎/时长"排序符合预期。
5. **进度**：加入课程→标记完成 1 个课时 → 我的课程进度=完成数/总数、最后学习时间刷新；全部完成 → 状态 completed。
6. **章节**：添加/编辑/删除章节（含子层级）、正文写入并可在详情页阅读；外部链接可打开。
7. **测验**：创建者配置测验 → 学习者答题 → 得分/及格/解析正确 → 进度同步。
8. **搜索分页**：关键词/难度/平台组合过滤 + 分页无重复无遗漏。
9. **移动端**：搜索框可用；筛选与搜索叠加正确。
10. **性能**：50 章节课程详情接口 SQL 查询 ≤ 3 条（树加载 1 次）。

---

## 7. 实施计划（分阶段）

**Phase 1 · 止血（约 1 天）——让首次进入有列表、章节可用**
- FR-1 前端渲染修复（模板类 + initTabSwitching + activatePane）
- FR-2 加载失败兜底（错误态 + 重试）
- FR-7 章节管理弹窗修复（去重函数 + 统一 modal）

**Phase 2 · 闭环（约 2-3 天）——功能完整**
- FR-4 课程编辑（复用创建表单 → PUT /courses/{id}）
- FR-5 withCount 聚合 + management 序列化
- FR-6 进度聚合引擎 + enrollment 状态接口 + 我的课程状态 UI
- FR-8 正文编辑/阅读（表单字段 + 详情页阅读/外链）
- FR-9 测验编辑器

**Phase 3 · 生态与体验（约 2 天）**
- FR-3 审核流（路由 + 卡片提交审核 + 公开列表含待审核筛选）
- FR-10 服务端搜索分页
- FR-11 移动端/组合筛选修复
- FR-12 章节树多层 + 已学状态
- FR-13 UX 统一（Swal/Toast/导航）
- FR-14 性能与架构（N+1、控制器收敛、路由冲突、401 兜底重试）

---

## 8. 风险与注意

1. **历史页面兼容**：修改 management 面板逻辑时保持 `courseTabs` DOM 结构，避免影响其他依赖（无）。
2. **数据迁移**：已存在课程 public_status 未变，审核流上线后建议提供一次性"状态批量更正"脚本（可选）。
3. **bootstrap-session 重试**：避免循环——重试次数上限 1，失败即走错误态。
4. **权限**：approve/unapprove 仅创建者/管理员；enrollment 更新仅本人。
5. **回归**：Phase 1 必须验证登录/未登录、桌面/移动、Tab 切换三个矩阵。

---

## 9. 相关文件速查

- 视图：`resources/views/courses/{management,index,create,show}.blade.php`、`resources/views/course-items/index.blade.php`、`resources/views/components/course-item-modal.blade.php`
- 控制器：`app/Http/Controllers/{CourseController,CourseItemController}.php`、`app/Http/Controllers/Api/V2/{CourseController,CourseItemController,CourseQuizController,CourseContentController}.php`
- 服务：`app/Services/{CourseService,CourseContentService,CourseQuizService}.php`
- 仓库：`app/Repositories/{CourseRepository,CourseItemRepository,CourseEnrollmentRepository}.php`
- 中间件：`app/Http/Middleware/HybridTokenMiddleware.php`
- 前端桥：`public/js/hybrid-api-client.js`、`resources/views/layouts/app.blade.php`（bootstrap-session 与 CSRF）
- 路由：`routes/web.php`（L245/L276-296）、`routes/api.php`（L136-330）