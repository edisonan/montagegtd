# AI 二次产出路线图（方案文档）

> 目标：盘点 MontageGTD 现有各模块的数据资产，给出「哪些能与 AI 结合做二次产出、按什么顺序做、用同一套技术路径做」的完整方案。
>
> 状态：方案评审稿，尚未进入实施。

## 1. 背景与定位

MontageGTD 已经积累了丰富的个人数据资产：GTD 任务、番茄专注、笔记、日志、RSS 文章、思维导图、学习打卡、课程、积分成就、代码片段等。同时已有完整的 LLM 基础设施（Provider/Model/Credential 管理、智能体、会话工作台、结构化任务、用量统计）。

“**AI 二次产出**”的定义：**以用户已在系统内沉淀的数据为输入，由 AI 生成新的、可复用的内容产出**（周报、摘要、拆解、导图、建议、聚合页等），而不是把 AI 当成一个孤立对话框。

本项目已有一个先例：`docs/article-llm-classification-and-digest-prd.md` —— 文章 AI 分类 + 个性化汇合页，就是“文章数据 → AI 二次产出”的样板。本文档要把这套打法推广到其余模块。

## 2. 现状盘点（已有的 AI 能力）

| 能力 | 位置 | 说明 |
| --- | --- | --- |
| LLM 资源管理 | `LlmProviderService` / `LlmModelService` / `LlmProviderCredentialService` | 多供应商、多模型、API Key 加密、配额、用户级/全局资源 |
| 结构化任务执行 | `LlmStructuredTaskService::runTask(taskType, messages, options)` | 非流式 chat/completions、批量建议、节流、response_format 重试、用量/成本落库、按 `task_type` 分类 |
| 定时任务编排 | `app/Console/Kernel.php` + `app/Console/Commands/*` | task_reminder、feed_common、articles:classify-pending、digest:generate-scheduled、study:generate-tasks、course:generate-scheduled 等已挂调度 |
| Agent / 会话工作台 | `llm-index-feature.md`、`LlmSessionService`、`LlmAgent*Service`、`app/Agent` | 多轮对话、SSE 流式、附件、分支、回复可沉淀为笔记/导图（`source_type=llm`） |
| 文章 AI | `ArticleAiClassificationService`（分类/标签/摘要，task `article_*`）、`ArticleAiRenderService`（`article_workbench_digest`）、`DigestGenerationService`（个性化汇合页）、`WebpageRssService`（网页转 RSS + AI 字段） | 定时任务已挂载：`articles:classify-pending`（每 5 分钟）、`digest:generate-scheduled`（每小时） |
| 课程 AI | `CourseContentService`（`course_content_generation`）、`CourseQuizService`（出题） | `course:generate-scheduled` 每小时跑 |

**结论：技术底座已成熟，缺的是“把更多模块的数据接进来”的业务封装。**

## 3. 评估标准（哪些模块优先做）

对每个候选方向按四个维度打分：

1. **数据富集度**：模块内个人数据是否足够多、是否有内容文本（AI 是“越喂越值钱”）。
2. **产出价值**：AI 产出是否用户愿意反复看、愿意传播（分享链接是现成出口：`notes.share_token` / 密码分享）。
3. **轮子复用度**：能否直接套 `LlmStructuredTaskService` + Console Command + Kernel 调度，不新增架构。
4. **成本可控性**：定时任务的 token 消耗是否可预期、可节流、可按 task_type 统计。

## 4. 模块 × AI 二次产出机会清单

### 4.1 任务 / GTD（Tier 1）

数据资产：任务、子任务、优先级、Deadline、完成时间、四象限（已有拖拽排序）。

| 产出 | 说明 | 触发方式 |
| --- | --- | --- |
| 任务智能拆解 | 用户点“AI 拆解”，LLM 把一条模糊任务拆成 2~5 个可执行子任务 | 手动（Web 按钮 → API） |
| 周报/月报 | 汇总“本周完成任务 + 专注时长 + 优先级分布”，生成叙事性周报 | 定时（周日晚） |
| 四象限智能归类 | 结合内容 + Deadline，建议放入哪一象限 | 手动 / 创建时异步 |
| Deadline 风险提示 | 已逾期或临期但未拆解/无子步骤的任务，生成提醒措辞 | 定时 |

### 4.2 笔记 + 日志（Tier 1）

数据资产：笔记（含公开/私密、标签、分享链接）、日志（手账/任务/专注类型）、标签体系。

| 产出 | 说明 |
| --- | --- |
| 自动打标签/分组 | 批量对未打标签笔记生成建议标签 |
| 笔记摘要 + 要点 | 长笔记 → 3~5 条要点，列表页可折叠展示 |
| 周回顾 | 每周日把本周新增笔记/日志聚合成“你本周沉淀了什么”，生成一段可读回顾（可一键转公开分享） |
| 笔记关联推荐 | 按语义相似度推荐相关笔记（先关键词 + 标签兜底，再上向量） |

### 4.3 日总结升级（Tier 1）

现状：`DailySummaryService` 只做**统计聚合**（把当天日志/文章/导图/笔记列出来，无 LLM）。

升级方向：**多模块 AI 叙事日总结** —— 把当天任务完成情况、专注记录、新增笔记、阅读文章、学习打卡一并喂给 LLM，输出带“今日亮点 / 值得记录 / 明日建议”的叙事总结，存为 `DailySummary` 或笔记。

- 入口：`daily_summary_reminder`（现 18:10 提醒用户手写）→ 升级为“自动生成草稿 + 用户可编辑”。
- 注意：这是典型的跨模块聚合，数据组装放 `DailySummaryService`，LLM 放 `LlmStructuredTaskService`。

### 4.4 学习打卡（Tier 1）

数据资产：学习计划、`StudyActivity`、`StudyCheckin`（音频/图片/视频附件）、学习任务。

| 产出 | 说明 |
| --- | --- |
| 学习周报 | 本周打卡内容 → 掌握度 / 薄弱点 / 下周建议 |
| 打卡内容摘要 | 每日打卡 → 一句总结沉淀到日总结 |
| 学习计划 AI 生成 | 现有 `StudyGenerateTasks` 是规则生成，可升级为 LLM 按目标生成任务+里程碑 |

### 4.5 课程（Tier 1，部分已做）

已做：内容生成（`CourseContentService`）、出题（`CourseQuizService`）。

可补：
- **课程 AI 助教**：基于课程项内容做问答（可复用 Agent + `source_type` 体系）。
- **讨论区摘要**：课程讨论/回复较多时，AI 汇总观点与待解答问题。

### 4.6 RSS / 文章（Tier 2，基础已做）

已做：AI 分类/标签/摘要、白名单汇合页 Digest、网页转 RSS。

可延伸：
- **“本周值得读”**：按白名单用户阅读/收藏历史，AI 从本周新文章里筛选排名（Digest 升级版）。
- **文章 → 笔记/导图** 一键沉淀（完善“读后产出”闭环，参考 LLM 回复沉淀的实现）。

### 4.7 思维导图（Tier 2）

已做：AI 回复可存为导图（`source_type=llm`）。

可延伸：
- **任意文本 → 导图**：笔记/文章/对话内容 → 生成 JsMind 结构。
- **导图 → 大纲/Markdown**：反向产出，方便复制到笔记或分享。

### 4.8 LLM 会话 / 知识库（Tier 2，引擎级）

已做：会话、Agent、附件、分支、回复沉淀笔记/导图。

可延伸：
- **RAG 检索**：让 Agent 检索“我的笔记 + 任务 + 文章”后再回答（先关键词/标签检索，后向量）。
- **会话知识沉淀**：把高质量长会话自动提炼成笔记条目（防止淹没在会话列表里）。

### 4.9 积分 / 成就（Tier 3，趣味向）

- AI 生成成就徽章文案 / 解锁祝贺语（个性化）。
- 行为数据分析 → 激励建议（ROI 一般，放最后）。

### 4.10 代码 / 小应用（Tier 3）

- 代码解释、自动 README、`/app` 小应用按提示生成。
- 与现有 `AppVirtualTableService`/`AppCodeDatabase` 结合做“AI 生成小应用”。

### 4.11 日历 / Kindle / 第三方（Tier 3，做出口不做 AI）

- AI 产出 → 日历（排期建议）、Kindle（`KindlePush` 推送阅读版）、微信/饭否（分发渠道）。
- 本身不产生新 AI 逻辑，作为上面产出的“分发层”。

## 5. 分阶段路线图

### Phase 0：打样（约 1~2 个迭代）——先证明一套模式

目标：**用一套新模式跑通“数据 → 结构化任务 → 产出落库 → 页面展示”的闭环**，顺便把缺失的抽象补齐。

建议选 **4.1 任务周报** 或 **4.3 日总结升级** 作为打样（数据最现成、价值最直观）。

产出物：
- 新 Console Command（如 `ai:daily-summary`）。
- 挂到 `Kernel::schedule()`。
- 结果落库：优先复用 `daily_summaries` / `notes` 表，必要时加迁移。
- 页面/接口：在对应模块页面展示 AI 产出，加“重新生成”按钮。

### Phase 1：三个高频模块（任务、笔记/日志、学习）

按 4.1 / 4.2 / 4.4 实施，全部复用 Phase 0 的 Command + Service 模式。

### Phase 2：跨模块聚合（杀手锏）

**统一 AI 周报/月报**：任务完成 + 专注 + 笔记 + 阅读 + 学习打卡汇总成一份叙事性周报，落库为笔记并支持一键公开分享。

- 这是“AI 二次产出”最典型的形态，也是本产品区别于普通待办工具的核心卖点（用户自己的数据最全）。
- 建议在 Phase 1 的模块产出稳定后做，因为周报要复用各模块的聚合函数。

### Phase 3：延伸与分发（文章周选、RAG、导图双向、课程助教、Kindle/微信分发）

逐项按性价比做，见第 4 节。

## 6. 统一技术模式（所有产出项目都走这一条路）

```text
数据查询（Repository/Service 聚合）
        ↓
构造 messages（系统提示词 + 该 task_type 的 JSON schema 要求）
        ↓
LlmStructuredTaskService::runTask('<task_type>', $messages, [
    'timeout' => ..., 'throttle_minutes' => ..., 'response_format' => 'json_object',
])
        ↓
解析结构化 JSON（参考 ArticleAiClassificationService::parseStructuredJson）
        ↓
落库（产出表或 notes 表，带 source_type / source_id / llm meta）
        ↓
Console Command 挂 Kernel 定时；手动入口走 Web/API + 按钮
```

要点：
- **task_type 命名规范**：`<模块>_<产出>`，如 `task_weekly_report`、`daily_summary_narrative`、`study_weekly_review` —— 直接复用现有 `llm_usage_logs.request_data.task_type` 做成本统计。
- **批量建议**：批量任务用 `getRecommendedBatchSize()` / `calculateBatchSize()`（文章分类已有先例）。
- **失败兜底**：参考 `ArticleAiClassificationService` —— LLM 失败时落 `fallback-local` 的确定性兜底（比如周报兜底为纯统计列表），保证用户永远有产出。
- **成本控制**：定时任务只在活跃用户上跑（参考 `DailySummaryService::scheduleDailySummaryReminder` 按 `last_login` 过滤）；手动生成用 `throttle_minutes` 节流。
- **多模型选择**：`runTask` 支持 `model_id`，周报/聚合类用便宜模型，拆解/助教类用强模型。

## 7. 风险与注意事项

1. **数据隐私**：笔记/日志可能涉密 → 喂给 LLM 前做脱敏或加“仅使用标题+摘要”的开关；外部 Provider 调用注意合规。
2. **LLM 返回不稳**：结构化 JSON 解析必须有兜底（解析失败 → 用首次尝试的内容原样落库或重试）。
3. **成本**：全量用户周报成本 = 活跃用户数 × 每周一次；建议先白名单/活跃用户灰度（Digest 已有 `DigestWhitelistUser` 先例）。
4. **避免影响主链路**：所有 AI 产出都是增量写入，不动现有任务/笔记/文章的读写主流程（PRD 第 2.3 节非目标同样适用于本方案）。
5. **页面入口**：AI 产出要有“一眼可见”的入口和“重新生成/编辑”能力，否则用户感知不到（参考日总结编辑）；分享出口用现成 `notes.share_token` 体系。

## 8. 结论

- **立即可做（Tier 1）**：任务周报 + 智能拆解、笔记自动标签/摘要/周回顾、日总结升级为 AI 叙事、学习打卡周报。
- **延伸做（Tier 2）**：文章“本周值得读”、任意文本↔导图、会话知识 RAG。
- **最后做（Tier 3）**：积分成就文案、代码/小应用生成、日历/Kindle/微信分发。
- **杀手锏**：Phase 2 的统一 AI 周报/月报（跨模块聚合 + 公开分享分发）。

所有项目沿用第 6 节统一技术模式，增量成本低，且每个产出的 token 消耗都能通过 `llm_usage_logs` 按 `task_type` 直接审计。