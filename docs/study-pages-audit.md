# 学习菜单页面问题审计

> 审计时间：2026-08；范围：/study、/study/checkins、/studyfocus/{id}、/courses、/course/management、/courses/create
> 相关代码：StudyController / CourseController / Api\V2\StudyController / Api\V2\CourseController / StudyService / CourseService / CourseEnrollmentRepository

## 菜单结构

- 学习计划 `/study`
- 我的学习 `/courses`
- 课程管理 `/course/management`（web 路由为单数 course）
- 学习打卡 `/study/checkins`（从学习页进入）
- 学习专注 `/studyfocus/{task}`（从学习页任务卡片进入）
- 创建课程 `/courses/create`（管理页/我的学习页进入）

## 一、学习计划页 /study（study/index.blade.php + StudyService）

1. 概览卡片为硬编码假数据：宠物吉祥物、"Lv.6"、68% 进度条、"点击可前往领取"无链接。
2. "学习总时长" = 已打卡任务的预计时长之和（StudyService::getOverview 中 learnedMinutesTotal += estimatedMinutes），非真实专注时长，口径误导。
3. 全页使用 alert() 原生弹窗（创建成功/生成任务/打卡失败等）。
4. inferTaskType 仅识别 语文/英语，其余一律"其他"。
5. 计划管理弹窗顶栏"生成未来任务"误触即批量生成 14 天任务，无确认。
6. 删除计划为软删除（status=0 + delete()），确认文案与按钮语义矛盾。

## 二、学习专注页 /studyfocus/{id}（study/focus.blade.php）

1. 计时完全不落库：结束仅 alert("本轮专注完成")，刷新丢失；专注时长不写任何统计。
2. setInterval 计时漂移，锁屏/后台不准。
3. 固定 25 分钟不可配置；无休息提醒、无系统通知。
4. 无任务联动：专注结束不提示打卡、不更新任务状态。
5. focus 路由用 authorize('destroy', $task) 做查看鉴权，语义奇怪（次要）。

## 三、学习打卡页 /study/checkins

1. 打卡支持录音/图片/视频，但列表与详情卡片只有"有/无"图标，无播放/查看媒体入口。
2. 详情靠 hover 浮层（checkinPopover），移动端无 hover 无法查看。
3. 无默认日期范围、无搜索、无任务维度筛选。

## 四、我的学习页 /courses（courses/index.blade.php）

1. "学习统计"（时间分布/学习习惯/专注度/完成率）为写死的假数据。
2. "为您推荐"3 门课程全部硬编码。
3. 死链："探索更多课程"→ /courses/explore（无路由，404）；顶部"浏览课程"→ /courses（自引用）。
4. API 返回 user_courses 为原始 CourseEnrollment 模型（CourseEnrollmentRepository::getCourseEnrollments 未 with('course')），前端读取的 item.course / last_studied_at / study_minutes / chapters_count / duration 等字段不存在，页面静默降级为占位文案。
5. 课程卡片"设置/进度详情/切换状态"均弹"开发中"；course_settings_modal / progress_modal 为死代码。
6. searchCourses() 引用本页不存在的 #courseSearch，死代码。

## 五、课程中心/管理页 /course/management（courses/management.blade.php）

1. 卡片"统计/管理/进度详情"按钮均弹"开发中"。
2. "已加入课程"Tab：仅用公开课程列表过滤 userCourseIds，私有/非公开已加入课程不显示；renderJoinedCourseCard 读取 course.user_progress（不存在），进度恒 0%。
3. 我创建的课程卡片无编辑/删除入口（后端 API 存在，前端未接）。
4. 创建的课程 public_status=2（待审核）无可视状态展示。
5. 文案混乱：菜单"课程管理" vs 页头"课程中心"。

## 六、创建课程页 /courses/create

1. 面包屑"我的课程"→ /mycourse（无路由，404）。
2. 管理页三个创建入口（?type=video/document/external）在 create 页未读取 type 参数，效果相同。
3. 封面仅 URL 输入，无上传。

## 七、交叉问题

- 模块内代码风格分裂：study 系列原生 fetch + 手写弹窗；courses 系列 jQuery + TaskApiBridge。
- 提示方式混用 alert / toast / showNotification。
- 大量"功能开发中"死按钮/死 modal，建议移除。
- 每页 600~1100 行内联 JS/CSS，无组件化、无公共 utils。

## 优先级建议

- P0 数据真实性/可用性：/courses 假统计与假推荐移除或接口化；死链（/courses/explore、/mycourse、自引用）修正；API 序列化补 course 关联。
- P1 功能完整性：focus 计时落库与打卡联动；checkins 媒体回放；死按钮移除。
- P2 体验一致性：alert→toast 统一；移动端打卡详情点击查看；文案/面包屑修正。

## 已完成修复（2026-08-22 课程优先轮）

- **结构规划**：学习菜单重命名为 学习计划/学习打卡/我的课程/课程中心；课程中心移除与"我的课程"重复的"已加入"Tab；课程详情页对创建者改走"管理章节"，删除走 v2 DELETE 接口。
- **API**：`getUserCourses` eager load `course.courseItems`（withCount），`/api/v2/courses`、`/course-enrollments`、`/courses/management` 返回序列化后的 `user_courses`（真实 progress_percent/last_studied_at/course 冗余字段）。
- **/courses（我的课程）**：删除硬编码"学习统计"与"为您推荐"；修复"浏览课程"自引用与"/courses/explore"死链（统一指向 /course/management）；移除 设置/进度详情/切换状态 死按钮与两个死 modal；JS 改用真实字段（cover_image_url/chapters_count/estimated_hours/last_studied_at）。
- **/course/management（课程中心）**：移除"已加入"Tab 及其统计；我创建的课程卡片加 私有/待审核/已公开 徽章 + 管理章节 + 删除按钮；平台筛选选项与真实平台值对齐；清理"功能开发中"死按钮。
- **/courses/create**：面包屑修掉 /mycourse 死链；`?type=video/document/external` 生效（顶部标识 + source_type 随提交）。
- **/courses/show**：创建者"编辑课程"死链移除；"返回课程中心"统一指向 /course/management。
- **专注落库（新）**：新增 `study_focus_sessions` 表（migration 已建）+ model + `POST /api/v2/study/tasks/{task}/focus-sessions`；专注页改为时间戳计时（无漂移）、可选单轮时长（15/25/45/60）、结束后自动上报并可直接打卡；页面离开 keepalive 补记；/study 概览"学习总时长"改为真实专注分钟。
- **打卡媒体回放（新）**：新增受权媒体路由 `/study/media/{path}`（仅本人可访问）；打卡列表卡片内嵌 图片/音频播放器/视频 预览；行点击固定查看（适配移动端无 hover）。
- **体验统一**：学习计划/专注/打卡页 alert/confirm 全部换成 SweetAlert toast/确认框；"生成未来任务"与删除计划加确认；/study 概览卡移除假宠物数据。