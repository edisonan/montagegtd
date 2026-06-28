# 文章 LLM 分类与个性化汇合页实施方案

## 1. 实施目标

本方案基于当前仓库现状，给出一套可增量落地的开发拆解，目标是：

1. 不破坏现有文章订阅与阅读链路
2. 尽量复用现有 `Repository + Service + Controller + Command` 结构
3. 先做 MVP，可快速上线，再逐步增强

## 2. 当前代码现状与接入判断

结合仓库当前代码：

- 文章主模型：`app/Models/Article.php`
- 文章服务：`app/Services/ArticleService.php`
- 文章 API：`app/Http/Controllers/Api/V2/ArticleController.php`
- 文章仓储：`app/Repositories/ArticleRepository.php`
- 定时调度：`app/Console/Kernel.php`
- LLM 基础：`app/Services/Llm*`、`app/Repositories/Llm*`、`app/Models/Llm*`

结论：

- 不建议把新能力直接继续堆进 `ArticleService`
- 应新增独立的 AI 文章模块和汇合页模块
- 调度与任务状态需要独立表，否则后续无法观察和补偿

## 3. 建议的模块拆分

## 3.1 新增模型

建议新增 6 个模型：

- `app/Models/ArticleAiProfile.php`
- `app/Models/ArticleAiTask.php`
- `app/Models/DigestWhitelistUser.php`
- `app/Models/UserDigestProfile.php`
- `app/Models/DigestTask.php`
- `app/Models/DigestPage.php`

### 3.1.1 模型职责

`ArticleAiProfile`

- 保存文章结构化 AI 结果
- 一篇文章一条当前生效记录

`ArticleAiTask`

- 保存文章分析任务状态
- 支撑重试、补偿、失败排查

`DigestWhitelistUser`

- 控制哪些用户有资格使用汇合页能力

`UserDigestProfile`

- 保存白名单用户的兴趣配置

`DigestTask`

- 保存某次汇合页生成任务

`DigestPage`

- 保存最终生成的汇合页正文与元信息

## 3.2 新增仓储层

建议新增以下 Repository：

- `app/Repositories/ArticleAiProfileRepository.php`
- `app/Repositories/ArticleAiTaskRepository.php`
- `app/Repositories/DigestWhitelistUserRepository.php`
- `app/Repositories/UserDigestProfileRepository.php`
- `app/Repositories/DigestTaskRepository.php`
- `app/Repositories/DigestPageRepository.php`

### 3.2.1 Repository 风格

保持与现有项目一致：

- 负责查询、创建、更新、分页
- 不承载 LLM 业务判断
- 尽量返回 Eloquent 模型或分页对象

## 3.3 新增服务层

建议新增以下 Service：

- `app/Services/ArticleAiService.php`
- `app/Services/ArticleAiClassificationService.php`
- `app/Services/DigestWhitelistService.php`
- `app/Services/DigestProfileService.php`
- `app/Services/DigestGenerationService.php`
- `app/Services/LlmTaskLogService.php` 可选

### 3.3.1 服务职责

`ArticleAiService`

- 负责文章 AI 主流程编排
- 创建任务
- 拉取待处理任务
- 更新任务状态
- 触发重跑

`ArticleAiClassificationService`

- 负责具体分类 prompt 组装
- 文本清洗与截断
- 调用 LLM
- 解析 JSON
- 回写画像

`DigestWhitelistService`

- 白名单增删查

`DigestProfileService`

- 用户兴趣配置的读取与保存
- 白名单校验

`DigestGenerationService`

- 候选文章召回
- LLM 归纳生成
- 汇合页落库

## 3.4 新增控制器

建议新增以下控制器：

- `app/Http/Controllers/Api/V2/DigestController.php`
- `app/Http/Controllers/Api/V2/Admin/ArticleAiController.php`
- `app/Http/Controllers/Api/V2/Admin/DigestAdminController.php`

说明：

- 用户侧接口和管理侧接口分开
- 即使暂时没有完整后台，也应先把路由和权限边界区分开

## 3.5 新增命令

建议新增以下 Artisan 命令：

- `app/Console/Commands/ArticlesClassifyPending.php`
- `app/Console/Commands/DigestGenerateScheduled.php`

可选补充：

- `app/Console/Commands/ArticlesBackfillAi.php`
- `app/Console/Commands/DigestRegenerate.php`

## 4. 数据库实施方案

## 4.1 迁移文件建议

建议新增 6 个 migration：

- `create_article_ai_profiles_table`
- `create_article_ai_tasks_table`
- `create_digest_whitelist_users_table`
- `create_user_digest_profiles_table`
- `create_digest_tasks_table`
- `create_digest_pages_table`

## 4.2 字段建议

### 4.2.1 `article_ai_profiles`

关键字段：

- `article_id` 唯一索引
- `status`
- `primary_category`
- `secondary_category`
- `tags_json`
- `keywords_json`
- `summary`
- `content_type`
- `audience`
- `quality_score`
- `risk_flags_json`
- `model_name`
- `prompt_version`
- `analyzed_at`

索引建议：

- `article_id unique`
- `primary_category`
- `status`
- `analyzed_at`

### 4.2.2 `article_ai_tasks`

关键字段：

- `article_id`
- `status`
- `retry_count`
- `scheduled_at`
- `started_at`
- `finished_at`
- `error_message`
- `model_name`
- `prompt_version`

索引建议：

- `(status, scheduled_at)`
- `article_id`

说明：

- 允许一篇文章有多条历史任务
- 当前生效结果只看 `article_ai_profiles`

### 4.2.3 `digest_whitelist_users`

关键字段：

- `user_id`
- `enabled`
- `expires_at`
- `remark`

索引建议：

- `user_id unique`
- `enabled`

### 4.2.4 `user_digest_profiles`

关键字段：

- `user_id`
- `enabled`
- `topics_json`
- `include_keywords_json`
- `exclude_keywords_json`
- `preferred_categories_json`
- `time_window_days`
- `frequency`
- `max_articles`
- `output_style`
- `last_generated_at`

索引建议：

- `user_id`
- `enabled`
- `frequency`

### 4.2.5 `digest_tasks`

关键字段：

- `user_id`
- `profile_id`
- `status`
- `scheduled_at`
- `started_at`
- `finished_at`
- `retry_count`
- `error_message`
- `model_name`
- `prompt_version`

索引建议：

- `(status, scheduled_at)`
- `user_id`
- `profile_id`

### 4.2.6 `digest_pages`

关键字段：

- `user_id`
- `profile_id`
- `task_id`
- `title`
- `cover_time_start`
- `cover_time_end`
- `intro`
- `content_markdown`
- `source_article_ids_json`
- `status`
- `generated_at`
- `model_name`
- `prompt_version`

索引建议：

- `user_id`
- `profile_id`
- `generated_at`

## 4.3 是否修改已有表

建议本期尽量不改已有 `articles` 表。

原因：

- 当前 `Article` 模型较轻
- AI 结果字段较多
- 单独建表更利于隔离和回滚

如果一定要补基础字段，最多建议在 `users` 表增加一个轻量字段：

- `is_digest_whitelist`

但更推荐独立白名单表，不改 `users`。

## 5. LLM 调用接入方案

## 5.1 不建议直接复用聊天接口

当前项目里的 `LlmController`、`LlmConversationService` 更偏“对话”场景，不适合直接承担离线批处理主流程。

建议新建一个内部服务层封装：

- 输入：结构化业务请求
- 输出：结构化 JSON 结果
- 附带：usage log 落库

## 5.2 建议的内部抽象

建议新增一个内部服务，例如：

- `app/Services/LlmStructuredTaskService.php`

职责：

- 接收 `task_type`
- 读取默认模型或指定模型
- 组装 prompt
- 调用 provider
- 解析 JSON
- 记录 `llm_usage_logs`

### 5.2.1 task_type 建议

- `article_classification`
- `digest_generation`

### 5.2.2 usage log 建议补充

如果现有 `llm_usage_logs` 字段不够，建议补充：

- `biz_type`
- `biz_id`
- `status`
- `request_time`
- `raw_response_excerpt`

如果暂时不想改表，也至少在 `response_data` 里保留业务标识。

## 5.3 Prompt 组织方式

建议统一放在配置或独立类中，不要写死在 Controller：

- `config/llm_tasks.php`
- 或 `app/Services/Prompts/*`

建议分为两套：

- 文章分类 Prompt
- 汇合页生成 Prompt

并带版本号：

- `article_classification:v1`
- `digest_generation:v1`

## 6. 文章分类实施细化

## 6.1 创建任务时机

建议在文章写入成功后补建 AI 任务。

当前项目里文章主要通过 Feed 同步进入系统，接入点优先考虑：

- `app/Services/FeedService.php`

接入原则：

- 文章首次创建时生成 `pending` 任务
- 已存在文章不重复建任务

如果暂时不想侵入 Feed 流程，也可以先用补偿任务兜底：

- 定时扫描最近 N 天没有画像的文章，自动补建任务

## 6.2 分类任务执行流程

`ArticlesClassifyPending` 命令建议：

1. 查出 `pending` 且到期的任务
2. 每次取固定数量，例如 20 条
3. 更新为 `processing`
4. 调用 `ArticleAiClassificationService`
5. 成功则写画像并置 `success`
6. 失败则递增 `retry_count`
7. 超过阈值置 `failed`

## 6.3 文本清洗建议

送模前建议清洗：

- 去 HTML 标签
- 去多余空白
- 截断正文长度
- 保留标题、来源、发布时间

建议优先送这些字段：

- `subject`
- `content`
- `feed.name` 或来源标识
- `published`

## 6.4 分类结果写回策略

建议：

- `article_ai_profiles` 使用 `updateOrCreate`
- 每次成功分类覆盖当前画像
- 同时保留任务历史

## 7. 汇合页实施细化

## 7.1 用户配置接口

用户侧先做 4 个接口即可：

- `GET /api/v2/digest/profile`
- `POST /api/v2/digest/profile`
- `GET /api/v2/digest/pages`
- `GET /api/v2/digest/pages/{id}`

可选补一个：

- `POST /api/v2/digest/pages/generate`

### 7.1.1 控制器建议

`DigestController` 中建议包含方法：

- `profile`
- `saveProfile`
- `pages`
- `showPage`
- `generate`

### 7.1.2 校验规则

至少校验：

- 当前用户是否在白名单
- `time_window_days` 仅允许 1、3、7
- `frequency` 仅允许 `daily`、`weekly`
- `max_articles` 限制范围，例如 5 到 50

## 7.2 候选文章召回实现

首期不建议做向量检索，先基于 SQL + 现有画像表实现。

建议新增一个查询方法到新仓储中，例如：

- `ArticleAiProfileRepository::getRecentCandidatesForDigest(...)`

召回条件：

- 发布时间在时间窗口内
- 有 AI 画像
- 主分类匹配或标签命中
- 标题/摘要命中包含关键词
- 不命中排除词

排序建议：

- 发布时间倒序
- 质量分降序

## 7.3 汇合页生成流程

`DigestGenerateScheduled` 命令建议：

1. 查启用中的白名单用户 profile
2. 判断是否到了生成时间
3. 创建 `digest_tasks`
4. 召回候选文章
5. 若候选为空，可生成空摘要页或直接跳过
6. 调用 LLM 输出 Markdown
7. 落库到 `digest_pages`
8. 更新 `last_generated_at`
9. 更新任务状态

## 7.4 内容落库格式

首期建议只存：

- `title`
- `intro`
- `content_markdown`
- `source_article_ids_json`

原因：

- Markdown 最容易直接展示
- 后续也能转 HTML
- 不会过早把前端渲染绑定死

## 8. API 路由落地建议

## 8.1 用户读接口

在 `routes/api.php` 的 `hybrid.token:read` 分组下新增：

- `GET /v2/digest/profile`
- `GET /v2/digest/pages`
- `GET /v2/digest/pages/{id}`

## 8.2 用户写接口

在 `hybrid.token:write` 分组下新增：

- `POST /v2/digest/profile`
- `POST /v2/digest/pages/generate`

## 8.3 管理接口

如果当前项目没有成型 admin middleware，本期可先只做内部命令，不急着暴露管理 API。

如果要做 API，建议预留：

- `GET /v2/admin/article-ai/tasks`
- `POST /v2/admin/article-ai/tasks/rebuild`
- `GET /v2/admin/digest/whitelist-users`
- `POST /v2/admin/digest/whitelist-users`

## 9. 调度接入

在 `app/Console/Kernel.php` 中新增：

- `$schedule->command('articles:classify-pending')->everyTenMinutes();`
- `$schedule->command('digest:generate-scheduled')->hourly();`

说明：

- 文章分类建议高频小批次
- 汇合页建议按小时检查是否到达生成窗口

## 10. 分阶段开发顺序

## 10.1 第一阶段：数据层与基础骨架

交付内容：

- 6 个 migration
- 6 个 model
- 6 个 repository
- 文章分类和汇合页 service 骨架

验收点：

- 迁移可正常执行
- 模型关联关系可跑通

## 10.2 第二阶段：文章分类闭环

交付内容：

- 文章 AI 任务创建
- `articles:classify-pending`
- 文章 AI 画像落库

验收点：

- 新文章能被分类
- 失败可重试

## 10.3 第三阶段：白名单与兴趣配置

交付内容：

- 白名单表与服务
- `DigestController`
- profile 读写接口

验收点：

- 非白名单用户无法调用
- 白名单用户可保存 profile

## 10.4 第四阶段：汇合页生成闭环

交付内容：

- 候选召回
- `digest:generate-scheduled`
- 汇合页列表与详情接口

验收点：

- 到期 profile 能生成一篇 digest
- 页面能读到结构化结果

## 10.5 第五阶段：运营与补偿

交付内容：

- 手动重跑命令
- 简易管理接口或脚本
- 用量与成本监控

## 11. 最小上线版本建议

如果你想尽快可用，建议把首发范围压到下面这些：

- 文章 AI 画像只做 `主分类 + 标签 + 摘要 + 关键词`
- 用户兴趣配置只支持一个 profile
- 汇合页只支持最近 7 天
- 汇合页只支持每日生成
- 仅站内查看，不做推送

这样可以明显降低开发和 prompt 调优复杂度。

## 12. 需要你先确认的实施决策

真正开工前，建议你拍板下面 6 件事：

1. 分类字典是否由你先给固定版本
2. 白名单是独立表，还是临时放在 `users` 表
3. 汇合页首期是否只做最近 7 天日报
4. 汇合页是否允许用户手动立即生成
5. LLM 默认模型使用哪个已有 provider/model
6. 是否需要补历史文章分类

## 13. 我建议的最终技术路线

如果按“最快上线 + 最少返工”排序，我建议直接这样做：

1. 新建独立表，不改 `articles`
2. 先完成文章 AI 分类闭环
3. 汇合页首期只做白名单 + 单 profile + 7 天窗口
4. LLM 调用封装成结构化任务服务，不走聊天控制器
5. 管理后台先不做完整 UI，优先做命令和只读查询接口

这条路线最稳，也最符合你当前项目结构。
