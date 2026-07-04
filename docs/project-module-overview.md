# 项目模块粗略梳理

本文基于当前代码结构、路由、控制器、服务层、模型和已有文档做粗略模块梳理，用于快速理解项目边界。项目主体是 Laravel 5.5 的 MontageGTD Web 应用，覆盖 GTD 任务、番茄专注、笔记、RSS 阅读、思维导图、学习、LLM、积分激励等个人知识与效率管理能力。

## 1. 基础架构与入口

### 技术栈

- 后端框架：Laravel 5.5，PHP >= 7.0。
- 数据库：MySQL。
- 后台管理：`encore/laravel-admin`。
- 队列/缓存：Laravel 标准组件，依赖中包含 `predis/predis`。
- 前端资源：Blade 视图为主，`resources/assets` + `webpack.mix.js`。
- 部署：根目录有 `Dockerfile`、`docker-compose.yml`、`docker-compose/nginx/default.conf`。

### 主要入口

- Web 路由：`routes/web.php`，提供传统 Web 页面和部分登录态接口。
- API 路由：`routes/api.php`，当前主要是 `/api/v2/*`，按 `read`、`write`、`admin` 能力拆分鉴权。
- 后台路由：`app/Admin/routes.php`，对应 `app/Admin/Controllers`。
- Console 命令：`app/Console/Commands`，包含提醒、RSS 抓取、LLM 摘要、统计等定时任务。
- 视图：`resources/views`。
- 业务服务层：`app/Services`。
- 数据访问层：`app/Repositories`。
- 数据模型：`app/Models`。

## 2. 认证、用户与令牌模块

### 职责

负责用户登录注册、Web Session、第三方登录、个人访问令牌、User Access Token、Refresh Token，以及 API 的混合鉴权上下文。

### 主要代码

- Web/Auth 控制器：`app/Http/Controllers/Auth/*`。
- API 控制器：`app/Http/Controllers/Api/V2/AuthController.php`、`PersonalAccessTokenController.php`。
- 中间件：`HybridTokenMiddleware`、`TokenAuthMiddleware`、`ResolveAuthContext`。
- 服务：`PersonalAccessTokenService`、`Auth/UserTokenService`、`AccountService`。
- 模型：`User`、`PersonalAccessToken`、`OauthInfo`。
- 支撑对象：`app/Support/AuthContext.php`。

### 相关文档

- `docs/hybrid-auth-client.md`
- `docs/personal-access-token.md`
- `docs/openapi-v2.md`

## 3. 首页、仪表盘与统计模块

### 职责

提供登录后的首页概览、专注状态、统计报表、账户连接信息等聚合视图。

### 主要代码

- 控制器：`IndexController`、`StatisticsController`、`AccountController`。
- API 控制器：`Api/V2/IndexController`、`StatisticsController`、`AccountController`。
- 服务：`StatisticsService`、`AccountService`、部分依赖 `FocusService`。
- 视图：`resources/views/index`、`statistics`、`accounts`。
- 模型：`Statistics`、`OauthInfo`，以及任务、番茄、阅读、笔记等统计来源模型。

## 4. 任务、计划与专注模块

### 职责

项目核心 GTD 能力。包括任务列表、任务优先级、父子任务、提醒、Deadline、目标/计划、番茄专注记录、今日专注、专注状态、计划兼容旧的 goal 接口等。

### 主要代码

- Web 控制器：`TaskController`、`PlanController`、`FocusController`。
- API 控制器：`Api/V2/TaskController`、`PlanController`、`FocusController`。
- 服务：`TaskService`、`PlanService`、`FocusService`。
- 模型：`Task`、`Plan`、`Focus`、`Calendar`。
- 视图：`resources/views/tasks`、`plans`、`focus`、`pomos`。
- Console：`TaskReminder`、`PomoDailyReminder`、`PomoRecordReminder`、`PomoRestedReminder`。

## 5. 学习模块

### 职责

在任务/计划基础上扩展学习场景。包括学习概览、学习计划、根据计划生成任务、学习打卡、学习专注页、音频/图片/视频打卡附件。

### 主要代码

- Web 控制器：`StudyController`。
- API 控制器：`Api/V2/StudyController`。
- 服务：`StudyService`。
- 模型：`StudyActivity`、`StudyCheckin`、`Plan`、`Task`。
- 视图：`resources/views/study`、`resources/views/learning`。
- Console：`StudyGenerateTasks`。
- 数据迁移：`add_study_fields_and_checkins`、`create_study_plans_table`、`merge_study_plans_into_plans` 等。

## 6. 笔记、日志、日总结模块

### 职责

记录想法、灵感、日志和日总结，支持标签、公开/私密、图片上传、从浏览器快速分享内容、根据当天平台记录生成日总结辅助信息。

### 主要代码

- Web 控制器：`NoteController`、`JournalController`、`DailySummaryController`。
- API 控制器：`Api/V2/NoteController`、`JournalController`、`DailySummaryController`。
- 服务：`NoteService`、`JournalService`、`DailySummaryService`、`TagService`。
- 模型：`Note`、`Journal`、`DailySummary`、`Tag`、`NoteTagMap`。
- 视图：`resources/views/notes`、`journals`、`dailysummarys`。
- Console：`DailySummaryReminder`。

## 7. RSS、阅读与文章模块

### 职责

支持 RSS 订阅、订阅分类、OPML 导入、订阅排序、文章列表、阅读状态、收藏/标记、代理阅读、稍后读，以及网页转 RSS。

### 主要代码

- Web 控制器：`FeedController`、`ArticleController`、`CategoryController`。
- API 控制器：`Api/V2/FeedController`、`ArticleController`、`CategoryController`。
- 服务：`FeedService`、`ArticleService`、`CategoryService`、`WebpageRssService`。
- 模型：`Feed`、`FeedSub`、`Article`、`ArticleSub`、`ArticleMark`、`Category`、`WebpageRssSource`。
- 视图：`resources/views/feeds`、`articles`、`categorys`。
- Console：`FeedCommon`、`ArticlesClassifyPending`。

## 8. 文章 AI、摘要 Digest 与背景音乐模块

### 职责

围绕阅读内容做 AI 分类、文章 AI 渲染、个性化摘要、摘要任务调度、摘要页面，以及背景音乐曲目支持。

### 主要代码

- API 控制器：`Api/V2/DigestController`，文章 AI 能力在 `ArticleController` 中暴露。
- 服务：`ArticleAiService`、`ArticleAiClassificationService`、`ArticleAiRenderService`、`DigestProfileService`、`DigestGenerationService`、`DigestWhitelistService`、`LlmStructuredTaskService`。
- 模型：`ArticleAiProfile`、`ArticleAiTask`、`ArticleAiRender`、`DigestTask`、`DigestPage`、`DigestWhitelistUser`、`BgmTrack`。
- Console：`DigestGenerateScheduled`、`DigestUpsertProfileCommand`、`DigestWhitelistUserCommand`、`ArticlesClassifyPending`。
- 脚本：`scripts/import_pixabay_bgm.php`。

### 相关文档

- `docs/article-llm-classification-and-digest-prd.md`
- `docs/article-llm-classification-and-digest-implementation.md`

## 9. 思维导图模块

### 职责

支持创建思维导图、父子节点、导图查看、JsMind 数据格式、Markdown/HTML 大纲、标签绑定，以及从来源内容创建导图。

### 主要代码

- Web 控制器：`MindController`。
- API 控制器：`Api/V2/MindController`。
- 服务：`MindService`、`TagService`。
- 模型：`Mind`、`MindTagMap`、`Tag`。
- 视图：`resources/views/minds`。
- 数据迁移：`add_source_fields_to_minds_table`。

## 10. 课程、公开学习与讨论模块

### 职责

支持课程创建、公开课程、课程项结构、用户加入课程、学习进度、课程讨论和回复。

### 主要代码

- Web 控制器：`CourseController`、`CourseItemController`、`DiscussionController`。
- API 控制器：`Api/V2/CourseController`、`CourseItemController`、`DiscussionController`。
- 服务：`CourseService`。
- 模型：`Course`、`CourseItem`、`CourseEnrollment`、`PublicDiscussion`、`DiscussionReply`、`StudyActivity`。
- 视图：`resources/views/courses`、`course-items`。
- 数据迁移：`create_courses_table`、`create_course_items_table`、`create_public_discussions_table`、`create_discussion_replies_table`。

## 11. LLM 与智能体模块

### 职责

提供 LLM Provider、Model、Credential 管理，聊天会话，使用量日志，智能体管理、草稿版本、发布、测试聊天，以及结构化 LLM 任务执行能力。

### 主要代码

- Web 控制器：`LlmController`、`LlmSessionController`、`LlmAgentController`。
- API 控制器：`Api/V2/LlmController`、`LlmSessionController`、`LlmAgentController`。
- 服务：`LlmProviderService`、`LlmModelService`、`LlmProviderCredentialService`、`LlmSessionService`、`LlmConversationService`、`LlmAgentService`、`LlmAgentVersionService`、`LlmUsageLogService`、`LlmStructuredTaskService`。
- 模型：`LlmProvider`、`LlmModel`、`LlmProviderCredential`、`LlmSession`、`LlmConversation`、`LlmAgent`、`LlmAgentVersion`、`LlmUsageLog`。
- 视图：`resources/views/llm`。
- Console：`AddLlmMenuItems`。
- 额外目录：`app/Agent` 是一个相对独立的 Agent 实现/实验目录，包含自己的 `composer.json`、文档、示例和测试。

## 12. 积分、成就、通知与积分商城模块

### 职责

对用户行为发放积分、记录积分流水、自动解锁成就、领取成就徽章、发送通知，并提供积分商城与小游戏化权益，包括树、宠物、鱼塘、抽奖、巴士等玩法。

### 主要代码

- Web 控制器：`PointController`、`PointMallController`、`AchievementController`。
- API 控制器：`Api/V2/PointController`、`PointMallController`、`PointMallGameplayController`、`AchievementController`、`NotificationController`。
- 服务：`PointAccountService`、`PointRecordService`、`PointGrantService`、`PointMallService`、`PointMallGameplayService`、`AchievementService`、`AchievementGrantService`、`AchievementAutoUnlockService`、`UserNotificationService`。
- 模型：`PointAccount`、`PointRecord`、`PointRule`、`PointEventLog`、`PointMallGood`、`PointMallOrder`、`PointMallEntitlement`、`PointMallDeliveryLog`、`Achievement`、`BehaviorEvent`。
- 视图：`resources/views/points`、`resources/views/achievement`。
- Seeder：`PointRuleSeeder`、`PointMallGoodsSeeder`、`PointMallGameplaySeeder`、`AchievementCatalogSeeder`。

## 13. 日历、设置与 Kindle 推送模块

### 职责

提供个人设置、Kindle 推送配置与测试、日历页面、ICS 订阅地址、任务日历导出。

### 主要代码

- Web 控制器：`SettingController`、`KindleController`、`CalendarController`。
- API 控制器：`Api/V2/SettingController`、`KindleController`、`CalendarController`。
- 服务：`SettingService`、`KindleService`、`CalendarService`。
- 模型：`Setting`、`KindleLog`、`Calendar`。
- 视图：`resources/views/settings`、`kindles`、`cals`。
- Console：`KindlePush`。
- 工具：`app/Http/Utils/ICSUtil.php`、`ICSUtil2.php`。

## 14. 第三方集成与微信相关模块

### 职责

对接第三方登录、饭否收藏/发布、Twitter 回调占位，以及微信侧文章、笔记、探索、登录等接口。

### 主要代码

- Web 控制器：`ThirdController`，登录回调在 `Auth/LoginController`。
- API 控制器：`Api/V2/ThirdController`、`WechatController`。
- 服务：`ThirdService`、`AccountService`。
- 模型：`Third`、`OauthInfo`。
- Console：`FanfouPublish`。
- 路由：`/login/third/{driver}`、`/api/v2/wechat/*`、`/api/v2/thirds/*`。

## 15. 代码托管/应用展示模块

### 职责

支持代码片段或小应用的存储、访问、路径路由、应用级代码展示或执行。

### 主要代码

- Web 控制器：`CodeController`、`ApplicationController`。
- API 控制器：`Api/V2/CodeController`、`ApplicationController`。
- Admin 控制器：`app/Admin/Controllers/CodeController.php`、`ApplicationController.php`。
- 模型：`Code`、`CodeHistory`、`Application`。
- 视图：`resources/views/codeview.blade.php`、`resources/views/admin/applications`。
- 路由：`/code/{codeInfo}`、`/app/{appSlug}/{codePath}`、`/api/v2/codes/{codeInfo}`、`/api/v2/applications/{appSlug}/{codePath}`。

## 16. 后台管理模块

### 职责

基于 laravel-admin 提供系统后台，包括用户、设置、分类、任务、笔记、文章、订阅、课程、LLM 配置、系统日志等管理入口。

### 主要代码

- 路由：`app/Admin/routes.php`。
- 控制器：`app/Admin/Controllers/*`。
- 配置：`config/admin.php`。
- Console：`AddSystemLogMenuItem`、`AddLlmMenuItems`。
- 视图：`resources/views/admin`。

## 17. 帮助、反馈与系统日志模块

### 职责

提供关于页面、反馈提交、后台系统日志查看。

### 主要代码

- Web 控制器：`HelpController`。
- API 控制器：`Api/V2/HelpController`。
- 服务：`HelpService`、`AdminLogService`。
- 模型：`Feedback`。
- 视图：`resources/views/help`、`resources/views/admin/system_logs`。

## 18. 脚本、审计与文档模块

### 职责

辅助 API v2 迁移、路由完整性审计、OpenAPI 生成、Smoke Test、通知脚本、数据初始化等。

### 主要代码

- 脚本：`scripts/audit_*.sh`、`scripts/audit_route_handler_integrity.php`、`scripts/generate_openapi_v2.php`、`scripts/v2_token_smoke.sh`、`scripts/bark_notify.sh`。
- 文档：`docs/openapi-v2.md`、`docs/openapi-v2.yaml`、`docs/v2-migration-checklist.md`。
- 数据库 SQL：`database/sql/*`、`create_llm_tables.sql`、`setup_llm_tables.php`。

## 19. 模块分层规律

大多数业务模块遵循类似结构：

- `routes/web.php` 暴露 Web 页面入口。
- `routes/api.php` 暴露 `/api/v2` 接口。
- `app/Http/Controllers` 处理 Web 请求。
- `app/Http/Controllers/Api/V2` 处理 API 请求。
- `app/Services` 承载主要业务逻辑。
- `app/Repositories` 封装数据访问。
- `app/Models` 对应数据表。
- `resources/views` 提供 Blade 页面。
- `app/Console/Commands` 承载定时任务、补偿任务或管理命令。

## 20. 当前粗略模块清单

| 模块 | 关键词 | 主要入口 |
| --- | --- | --- |
| 认证与令牌 | 登录、注册、PAT、UAT、Refresh Token、混合鉴权 | `AuthController`、`PersonalAccessTokenController`、鉴权中间件 |
| 首页与统计 | 首页、账户、报表 | `IndexController`、`StatisticsController`、`AccountController` |
| 任务/计划/专注 | GTD、任务、计划、番茄、提醒 | `TaskController`、`PlanController`、`FocusController` |
| 学习 | 学习计划、学习任务、打卡 | `StudyController`、`StudyService` |
| 笔记/日志/日总结 | 想法、日志、每日总结、标签 | `NoteController`、`JournalController`、`DailySummaryController` |
| RSS/阅读/文章 | 订阅、文章、阅读状态、网页 RSS | `FeedController`、`ArticleController`、`WebpageRssService` |
| 文章 AI/Digest | AI 分类、AI 渲染、个性摘要 | `ArticleAi*Service`、`Digest*Service` |
| 思维导图 | Mind、节点、大纲、标签 | `MindController`、`MindService` |
| 课程/讨论 | 课程、课程项、加入、讨论 | `CourseController`、`CourseItemController`、`DiscussionController` |
| LLM/智能体 | Provider、Model、Credential、Session、Agent | `LlmController`、`LlmSessionController`、`LlmAgentController` |
| 积分/成就/商城 | 积分流水、规则、成就、商城玩法 | `Point*Controller`、`AchievementController`、`NotificationController` |
| 设置/日历/Kindle | 用户设置、ICS、Kindle 推送 | `SettingController`、`CalendarController`、`KindleController` |
| 第三方/微信 | 第三方登录、饭否、微信接口 | `ThirdController`、`WechatController` |
| 代码/应用展示 | 代码访问、小应用路由 | `CodeController`、`ApplicationController` |
| 后台管理 | laravel-admin 管理后台 | `app/Admin/*` |
| 帮助/反馈/日志 | 关于、反馈、系统日志 | `HelpController`、`AdminLogService` |
| 脚本与文档 | OpenAPI、审计、迁移、Smoke Test | `scripts/*`、`docs/*` |

## 21. 后续可继续细化的方向

- 按模块补充“接口清单”：从 `routes/api.php` 自动提取到模块级文档。
- 按模块补充“数据表关系”：基于 migration 和 model 关系梳理 ERD。
- 按模块补充“页面清单”：把 `resources/views` 和 Web 路由对应起来。
- 按模块补充“定时任务清单”：整理 `Console/Commands` 与 `Kernel` 调度关系。
- 按模块补充“V2 API 迁移状态”：结合 `docs/v2-migration-checklist.md` 和审计脚本输出。
