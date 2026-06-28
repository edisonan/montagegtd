# 文章 LLM 分类与个性化汇合页 PRD

## 1. 背景

当前项目已经具备以下基础：

- 已有文章能力：`articles`、`feeds`、`article_subs`
- 已有分类基础：`categories`、`tags`
- 已有 LLM 基础：`llm_providers`、`llm_models`、`llm_usage_logs`、`llm_conversations`
- 已有定时调度入口：`app/Console/Kernel.php`

现阶段文章系统更偏“内容抓取 + 用户订阅阅读”，缺少两类智能化能力：

1. 对新增文章进行离线批量语义理解，产出可复用的结构化内容标签。
2. 对一小批白名单用户，按照其关注主题，周期性生成一页“最近一段时间值得关注内容”的汇合页。

本需求目标是在不重构现有文章主链路的前提下，以增量方式接入 LLM 能力。

## 2. 目标

### 2.1 功能目标

新增两项能力：

1. 定时批量使用 LLM 对文章进行分类、标签、摘要等结构化分析。
2. 对白名单用户开放关注主题配置，并定时生成个性化汇合页。

### 2.2 业务目标

- 提升文章内容的可检索性、可聚合性和可复用性。
- 为后续推荐、专题页、搜索增强、消息推送提供统一语义层。
- 先在白名单人群中验证“AI 个性化内容汇总”的价值。

### 2.3 非目标

以下内容不在本期范围内：

- 实时逐篇同步分析
- 对全量用户开放个性化汇合页
- 复杂向量检索或 RAG 平台建设
- 自动对外发送邮件、公众号等多渠道分发
- 完整 CMS 编辑器式人工编排专题

## 3. 用户与场景

### 3.1 用户类型

- 普通用户：仅消费文章，不参与本期新增配置能力
- 白名单用户：可配置兴趣主题并查看定时生成的汇合页
- 运营/管理员：可查看任务状态、重跑任务、修正结果

### 3.2 典型场景

#### 场景 A：文章分类

1. 系统抓取或写入新文章
2. 文章进入待分析队列
3. 定时任务批量调用 LLM
4. 系统写回分类、标签、摘要等结构化结果
5. 后续列表、搜索、聚合逻辑复用这些结果

#### 场景 B：个性化汇合页

1. 白名单用户配置关注主题，例如 “AI Agent”、“大模型产品”、“出海工具”
2. 定时任务按时间范围召回候选文章
3. LLM 对候选文章做筛选、归类、总结
4. 系统生成一篇汇合页
5. 用户在站内查看最近一期汇总

## 4. 功能一：文章定时批量 LLM 分类

## 4.1 功能说明

系统定时扫描最近新增且未完成分析的文章，调用 LLM 输出结构化结果并落库。

## 4.2 建议输出字段

建议一次分析产出以下字段，避免后续重复请求 LLM：

- 主分类 `primary_category`
- 次分类 `secondary_category`
- 标签列表 `tags`
- 摘要 `summary`
- 关键词列表 `keywords`
- 内容类型 `content_type`
- 适用人群 `audience`
- 质量分 `quality_score`
- 风险标记 `risk_flags`

建议本期最少落地字段：

- 主分类
- 标签列表
- 摘要
- 关键词列表

## 4.3 分类规则建议

### 4.3.1 分类层级

建议先采用两层结构：

- 一级：稳定大类，例如 `AI`、`前端`、`后端`、`产品`、`商业`
- 二级：可逐步扩展，例如 `AI Agent`、`Prompt Engineering`、`Laravel`、`SaaS 出海`

### 4.3.2 结果形式

建议“单主分类 + 多标签”，而不是多主分类：

- 主分类用于聚合、统计、过滤
- 标签用于补充语义细节

### 4.3.3 人工优先级

若后续支持人工修正，优先级建议如下：

1. 人工修正结果
2. LLM 最新结果
3. 原始 Feed 分类或推断分类

## 4.4 处理流程

### 4.4.1 状态流转

- `pending`：待处理
- `processing`：处理中
- `success`：成功
- `failed`：失败
- `skipped`：跳过

### 4.4.2 流程步骤

1. 新文章创建后，写入 AI 分析任务表或标记待分析
2. 定时任务扫描 `pending` 任务
3. 批量读取文章标题、正文、来源、发布时间
4. 对长文做截断和清洗
5. 调用 LLM 获取结构化 JSON
6. 校验 JSON 结构
7. 写入文章画像表
8. 更新任务状态
9. 失败时记录原因并重试

## 4.5 触发策略

建议支持三种触发方式：

- 定时批量：主路径，建议每 10 分钟一次
- 手动重跑：用于运营纠错或模型升级补跑
- 历史补刷：用于给老文章补齐 AI 画像

## 4.6 成本控制

本功能成本风险高于开发风险，必须从第一期就纳入设计：

- 仅分析最近新增文章
- 长文截断，优先保留标题、导语、前文
- 先使用低成本模型
- 固定 JSON 输出格式，减少多轮交互
- 同一篇文章避免重复分析
- prompt 版本与模型版本必须落库

## 4.7 失败处理

- 单次失败：自动重试
- 多次失败：转 `failed`
- JSON 非法：记为失败并记录原始响应摘要
- 内容为空或过短：转 `skipped`

## 5. 功能二：白名单用户个性化汇合页

## 5.1 功能说明

对白名单用户开放兴趣配置。系统按固定周期召回最近文章，并基于规则筛选 + LLM 总结生成一页主题汇总内容。

## 5.2 开放范围

本期仅对白名单用户开放：

- 用户表增加白名单标记字段
- 或新增白名单表单独管理

建议优先使用独立白名单表，方便后续灰度和扩展。

## 5.3 用户可配置项

建议本期支持：

- 关注主题列表
- 包含关键词
- 排除关键词
- 时间范围：最近 1 天、3 天、7 天
- 生成频率：每天、每周
- 单次最多纳入文章数

可延期到二期的配置：

- 输出风格：简报、趋势综述、深度周报
- 仅看某些分类
- 最低质量分
- 是否只纳入未读文章

## 5.4 汇合页生成逻辑

### 5.4.1 候选召回

不建议直接把最近文章全量送给 LLM，应先走规则召回：

- 时间范围过滤
- 主分类过滤
- 标签匹配
- 标题关键词匹配
- 摘要关键词匹配
- 质量分过滤

### 5.4.2 LLM 精炼

候选召回之后，再由 LLM 完成：

- 判断相关性
- 合并相近主题
- 生成每个主题的总结
- 输出整体趋势导语
- 为每篇文章生成一句话推荐理由

### 5.4.3 汇合页结构

建议输出以下结构：

- 页面标题
- 覆盖时间范围
- 总体导语
- 主题分组列表
- 每个主题下的总结
- 每个主题关联的文章清单
- 每篇文章的一句话摘要
- 结尾总结

## 5.5 展示形态

本期建议仅做站内页面：

- 用户在“汇合页列表”中查看历史生成记录
- 点击进入某一期详情页

不建议首期同时做：

- 邮件发送
- 微信推送
- App Push

## 5.6 生成频率建议

建议首期支持：

- 每天一次
- 每周一次

其中默认推荐：

- 日报：最近 1 天
- 周报：最近 7 天

## 6. 统一系统能力设计

## 6.1 Prompt 版本管理

必须记录：

- `prompt_type`
- `prompt_version`
- `model_id` 或 `model_name`

建议将“文章分类”和“汇合页生成”分别作为两类 prompt。

## 6.2 LLM 调用日志

项目已有 `llm_usage_logs` 基础，本需求建议补充业务关联字段：

- 业务类型：`article_classification` / `digest_generation`
- 业务对象 ID：文章 ID / 汇合页任务 ID
- 调用结果状态

## 6.3 人工干预能力

建议预留但本期可不完整实现：

- 手动重跑文章分析
- 手动重跑某用户汇合页
- 手动覆盖文章分类
- 手动屏蔽文章参与汇合页

## 6.4 审核与兜底

建议：

- 统一要求 LLM 输出 JSON
- 服务端做严格字段校验
- 当生成失败时，页面层不暴露半结构化脏数据
- 汇合页可标记“AI 生成内容，仅供参考”

## 7. 数据设计建议

以下为建议新增表。命名以不影响现有主表为原则。

## 7.1 文章 AI 画像表

表名建议：`article_ai_profiles`

字段建议：

- `id`
- `article_id`
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
- `created_at`
- `updated_at`

说明：

- 一篇文章保留一条当前生效画像即可
- 如需历史，可再加版本表

## 7.2 文章 AI 任务表

表名建议：`article_ai_tasks`

字段建议：

- `id`
- `article_id`
- `status`
- `retry_count`
- `scheduled_at`
- `started_at`
- `finished_at`
- `error_message`
- `model_name`
- `prompt_version`
- `created_at`
- `updated_at`

说明：

- 用于调度与可观测性
- 与画像表分离，避免把任务状态混进业务结果表

## 7.3 汇合页白名单表

表名建议：`digest_whitelist_users`

字段建议：

- `id`
- `user_id`
- `enabled`
- `expires_at`
- `remark`
- `created_at`
- `updated_at`

## 7.4 用户兴趣配置表

表名建议：`user_digest_profiles`

字段建议：

- `id`
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
- `created_at`
- `updated_at`

说明：

- 本期可以约束“一用户只保留一个启用中的 profile”

## 7.5 汇合页任务表

表名建议：`digest_tasks`

字段建议：

- `id`
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
- `created_at`
- `updated_at`

## 7.6 汇合页内容表

表名建议：`digest_pages`

字段建议：

- `id`
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
- `created_at`
- `updated_at`

## 8. 接口设计建议

本期建议统一走 `/api/v2` 风格。

## 8.1 管理与运营接口

### 8.1.1 文章 AI 结果查看

- `GET /api/v2/admin/article-ai-profiles`
- `GET /api/v2/admin/article-ai-profiles/{articleId}`

### 8.1.2 文章 AI 重跑

- `POST /api/v2/admin/article-ai-tasks/rebuild`

参数建议：

- `article_ids`
- `force`

### 8.1.3 白名单管理

- `GET /api/v2/admin/digest-whitelist-users`
- `POST /api/v2/admin/digest-whitelist-users`
- `DELETE /api/v2/admin/digest-whitelist-users/{id}`

## 8.2 用户接口

### 8.2.1 获取兴趣配置

- `GET /api/v2/digest/profile`

### 8.2.2 保存兴趣配置

- `POST /api/v2/digest/profile`

### 8.2.3 获取汇合页列表

- `GET /api/v2/digest/pages`

### 8.2.4 获取汇合页详情

- `GET /api/v2/digest/pages/{id}`

### 8.2.5 手动触发一次生成

- `POST /api/v2/digest/pages/generate`

说明：

- 本接口仅对白名单用户开放
- 首期建议增加频控，避免滥用

## 9. 调度设计建议

## 9.1 文章分类任务

建议新增 Artisan 命令：

- `articles:classify-pending`

建议调度：

- 每 10 分钟执行一次

单次逻辑：

1. 读取待处理任务
2. 分批锁定任务
3. 调用 LLM
4. 写入画像
5. 更新状态

## 9.2 汇合页任务

建议新增 Artisan 命令：

- `digest:generate-scheduled`

建议调度：

- 每小时执行一次或每天固定时段执行

单次逻辑：

1. 找出到期 profile
2. 建立待生成任务
3. 召回候选文章
4. 调用 LLM 生成内容
5. 保存汇合页

## 10. 风险与约束

## 10.1 结果稳定性

风险：

- 分类漂移
- 标签不稳定
- 汇合页内容偶尔空泛

措施：

- 固定分类字典
- 固定输出 JSON schema
- 记录 prompt 版本
- 支持人工重跑与人工覆盖

## 10.2 成本风险

风险：

- 文章量上来后成本失控

措施：

- 先只分析新增文章
- 长文截断
- 规则召回后再送 LLM
- 汇合页限制候选文章上限

## 10.3 数据污染

风险：

- 低质量文章进入汇合页
- LLM 输出异常结构

措施：

- 增加质量分与跳过条件
- 严格 JSON 校验
- 多次失败直接转人工或忽略

## 11. MVP 范围

建议第一期只交付最小闭环：

### 11.1 第一阶段

- 新增文章离线定时分类
- 输出主分类、标签、摘要、关键词
- 白名单用户可保存一份兴趣配置
- 每天生成一次最近 7 天汇合页
- 站内查看汇合页列表和详情

### 11.2 第二阶段

- 支持周报
- 支持排除关键词
- 支持人工重跑
- 支持人工覆盖分类
- 支持质量分过滤
- 支持模型切换与版本控制

## 12. 验收标准

### 12.1 功能验收

- 新文章进入系统后，能在定时任务中被识别并完成分类
- 分类结果可查询，且包含主分类、标签、摘要
- 白名单用户能保存兴趣配置
- 系统能按周期生成对应汇合页
- 汇合页可查看标题、导语、主题分组和来源文章

### 12.2 稳定性验收

- 任务失败有状态可查
- 重复执行不会生成脏重复数据
- LLM 返回非法结构时不会污染正式结果

### 12.3 成本验收

- 单篇文章平均 token 使用量可统计
- 单次汇合页生成成本可统计
- 可按模型和业务类型查看调用消耗

## 13. 建议的实施顺序

建议按以下顺序推进：

1. 先建数据表和基础模型
2. 再做文章分类任务闭环
3. 然后做白名单和兴趣配置
4. 最后做汇合页生成与查询接口

原因：

- 文章分类是汇合页召回质量的基础
- 如果没有稳定的文章画像，汇合页只能依赖粗糙关键词匹配，效果会明显偏弱

## 14. 与现有项目的适配建议

结合当前仓库结构，建议：

- 服务层新增独立模块，不要把逻辑继续堆进 `ArticleService`
- 定时任务放入 `app/Console/Commands`
- LLM 调用尽量复用已有 provider/model/usage log 基础
- 汇合页对文章的召回应优先复用现有 `Article`、`Feed`、`Category` 能力

建议新增服务：

- `ArticleAiService`
- `ArticleAiClassificationService`
- `DigestProfileService`
- `DigestGenerationService`

建议新增命令：

- `ArticlesClassifyPending`
- `DigestGenerateScheduled`

建议新增模型：

- `ArticleAiProfile`
- `ArticleAiTask`
- `DigestWhitelistUser`
- `UserDigestProfile`
- `DigestTask`
- `DigestPage`
