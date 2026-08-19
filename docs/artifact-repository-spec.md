# 制品库（Artifact Repository）功能规格 Spec

> 本文档定义「制品库」的功能与实现规格：一个通用的、多态的 AI 产出存储层，用于保存「由 AI 基于某个实体数据二次产出」的内容（制品），并支持按制品类型扩展。
>
> 状态：待评审 → 实施（本文档完成后直接进入实现）。

## 1. 背景与定位

MontageGTD 已有多个「AI 二次产出」能力，但各自孤立：

| 现有能力 | 存储 | 关联方式 |
| --- | --- | --- |
| 文章 AI 可视化阅读 | `article_ai_renders`（html_content） | 仅关联文章 |
| 文章 AI 分类/摘要 | `article_ai_profiles` / 写回分类标签 | 仅关联文章 |
| 个性化汇合页 Digest | `digest_pages`（content_markdown） | 按用户+主题 |
| 思维导图 | `minds`（节点树，source_type/source_id） | 可关联任意来源 |
| LLM 回复沉淀 | `notes` / `minds`（source_type=llm） | 笔记/导图 |

**制品库的目标**：把这些「AI 产出物」统一到一个通用、可扩展的存储层，每个制品与任意业务实体（文章、笔记、计划……）多态关联，并且按「制品类型」区分产出形态（可视化阅读、思维导图、简报 HTML……），后续新增产出类型时只加新类型、不新建表。

## 2. 数据模型

### 2.1 表 `artifacts`

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigIncrements | 主键 |
| `user_id` | unsignedBigInteger indexed | 所属用户 |
| `name` | string(255) | 制品名称（如「文章可视化阅读」） |
| `file_type` | string(32) indexed | 制品内容格式：`html` / `json` / `markdown` / `text` |
| `artifact_type` | string(32) indexed | 制品类型：`visual_reading`（可视化阅读）、`mind_map`（思维导图）、`briefing_latest`（最新简报）、`briefing_followed`（关注简报）、`note_mind_map`（笔记导图）等 |
| `related_type` | string(32) indexed | 关联实体类型：`article` / `note` / `mind` / `plan` … |
| `related_id` | unsignedBigInteger indexed | 关联实体 id |
| `content` | longText | 制品内容（HTML 片段 / JSON / Markdown / 文本） |
| `status` | string(32) default `success` | 生成状态：`success` / `failed` / `pending` |
| `model_name` | string(100) nullable | 生成模型名 |
| `prompt_version` | string(32) nullable | 提示词版本 |
| `generated_at` | timestamp nullable | 生成时间 |
| `error_message` | string(255) nullable | 失败原因 |
| `created_at` / `updated_at` | timestamps | |

**唯一约束**：`unique(user_id, related_type, related_id, artifact_type)` —— 同一用户对同一实体的同类型制品只有一条，重新生成 = 覆盖更新（幂等）。

**扩展性**：`related_type` + `artifact_type` 组合即「一个产出物」，未来新增场景（文章→简报 HTML、笔记→思维导图/可视化阅读）不建新表，只新增 `artifact_type` 常量与对应的生成器（见 §5）。

### 2.2 与现有 `article_ai_renders` 的关系

`article_ai_renders` 保留不动（历史数据、文章阅读页依赖）。制品库作为**新的统一入口**承载新场景；文章「可视化阅读」制品生成时会复用 `ArticleAiRenderService` 的产出并**复制**到 `artifacts.content`，两者并存。后续新场景一律走制品库。

## 3. 制品类型规划

| artifact_type | 关联实体 | file_type | 内容 | 阶段 |
| --- | --- | --- | --- | --- |
| `visual_reading` | `article` | `html` | 可视化阅读 HTML 片段（复用现有 ArticleAiRender 提示词/清理管线） | **本阶段实现** |
| `mind_map` | `article` | `json` | 思维导图 jsMind 树数据（node_tree JSON） | **本阶段实现** |
| `briefing_latest` | `article`（feed 聚合） | `html` | 「最新简报」HTML 页面 | 预留 |
| `briefing_followed` | `article`（关注聚合） | `html` | 「关注简报」HTML 页面 | 预留 |
| `note_mind_map` / `visual_reading` | `note` | `json` / `html` | 笔记生成思维导图 / 可视化阅读 | 预留 |

## 4. 接口设计（API v2）

前缀 `/api/v2`，鉴权沿用 `hybrid.token:read` / `hybrid.token:write`；响应结构沿用 `{code:9999, msg:'ok', result:{}}`。

| 方法 | 路径 | 鉴权 | 说明 | 主要参数 |
| --- | --- | --- | --- | --- |
| GET | `/artifacts` | read | 双模式：① 管理页全局搜索（未同时提供 related_type+related_id 时）：按实体聚合返回 `entities`，每个实体含已生成类型、原文标题/链接、该实体下制品列表；② 实体维度：返回某实体全部制品 | 管理模式：`keyword`、`related_type`、`related_id`、`artifact_type`（=已生成类型筛选）、`status`、`page`、`per_page`；实体维度：`related_type` + `related_id`、`artifact_type`、`status` |
| GET | `/artifacts/{id}` | read | 查询单个制品详情（含 content） | 路径 id |
| POST | `/artifacts/generate` | write | 生成（或复用已有）制品；`force=1` 强制重新生成 | `related_type`、`related_id`、`artifact_type`、`force`、`custom_prompt`（可选） |
| POST | `/artifacts/{id}/to-mind` | write | 把已生成的 mind_map 制品落库为 `minds` 节点树（source_type=article, source_id=related_id），返回导图 id | 路径 id |
| DELETE | `/artifacts/{id}` | write | 删除制品 | 路径 id |

### 4.1 制品序列化（list/show 输出）

```json
{
  "id": 1,
  "name": "文章可视化阅读",
  "file_type": "html",
  "artifact_type": "visual_reading",
  "related_type": "article",
  "related_id": 123,
  "status": "success",
  "model_name": "gpt-4o-mini",
  "prompt_version": "article_visual_reading:v1",
  "generated_at": "2026-08-18 12:00:00",
  "error_message": null,
  "content": "<main>…</main>"
}
```

### 4.2 generate 语义

- 已存在 `success` 制品且 `force != 1` → 直接返回现有制品（不重复调用 LLM）。
- 不存在 / 已失败 / `force=1` → 调用对应生成器，覆盖写入同一条记录（upsert）。
- `custom_prompt` 仅对支持自定义的生成器生效（本阶段：`visual_reading`、`mind_map` 均支持）。
- 返回值带 `generated: true|false` 标记本次是否真实调用生成。

## 5. 服务层设计

`app/Services/ArtifactService.php`，核心方法：

### 5.1 生成器注册表

```php
protected $generators = [
    'article:visual_reading' => 'generateArticleVisualReading',
    'article:mind_map'       => 'generateArticleMindMap',
];
```

key = `relatedType:artifactType`，未来新增类型时注册新方法即可，`ensure()` 统一按 key 分派。

### 5.2 `ensure(userId, relatedType, relatedId, artifactType, options)`

1. 查已有制品（同 user + related + type）。
2. 若存在且 `status=success` 且未 `force` → 返回（`generated=false`）。
3. 否则查生成器 → 组装 prompt → `LlmStructuredTaskService::runTask('artifact_<type>', …)` → 解析 → upsert 写入 `artifacts`。
4. LLM 失败 → 写 `status=failed + error_message`，不抛异常（返回带 error 的制品，由上层决定提示）。

### 5.3 `generateArticleVisualReading(article)`（复用现有管线）

- 直接调用 `ArticleAiRenderService::ensureRender()`（已包含校验、清理、fallback、需要时自动生成）。
- 把 `html_content` 复制为制品 `content`，`file_type=html`；`model_name/prompt_version/generated_at` 原样透传。
- 说明：这是「复用而非重写」，保证与文章阅读页产出一致。

### 5.4 `generateArticleMindMap(article)`（新生成器）

- Prompt：把文章正文（标题+来源+分类+正文前 5000 字）交给 LLM，要求返回 node_tree JSON：
  ```json
  {
    "format": "node_tree",
    "data": {"id": "root", "topic": "标题", "children": [{"id": "1", "topic": "要点1", "children": []}]}
  }
  ```
- 要求：3~6 个主分支、每分支 2~4 个子节点；主题概括全文；保留关键数字与专有名词；不得编造原文没有的事实。
- 解析 JSON（复用并强化 ArticleAiRenderService 的解析容错），规范化节点（id 唯一、topic 非空、深度 ≤ 4）。
- 制品 `content = json_encode(node_tree json)`，`file_type = json`，`artifact_type = mind_map`。
- 提供 `toMindTree(artifact)`：把 node_tree 递归写入 `minds` 表（根节点 + 子节点，`source_type=article`、`source_id=related_id`），返回根节点 id。

## 6. Web 页面设计

### 6.1 制品库管理页 `GET /artifacts`

- 入口：顶部「助手」菜单 → 「制品库管理」子项。
- **按实体聚合的卡片列表**：每张卡片 = 一个来源实体（文章/笔记/导图），展示关联类型与 id、原文标题、已生成制品徽标（可视化阅读 ✓ / 思维导图 ✓ / 未生成）、最近生成时间。
- 可搜索：关键词（类型 / id）、关联类型、关联 id、已生成类型、分页。
- 卡片操作栏：**可视化阅读**、**思维导图** 两个按钮（弹窗），以及「原文」跳转（回到产出前的原始位置）。
- 卡片底部列出该实体下已生成的制品，成功可点击查看、失败显示原因。
- 全部基于 `/api/v2/artifacts` 管理模式接口 + 页面内嵌弹窗组件（`artifacts/_dialog.blade.php`）。

### 6.2 制品弹窗组件 `artifacts/_dialog.blade.php`

- 提供全局 `window.openArtifactDialog({relatedType, relatedId, artifactType})`。
- 打开后先查询该实体该类型制品：
  - **没有** → 中间提示「当前还没有 XX 制品」+「生成 XX」按钮，点击调用 generate 接口（异步等待，圈 loading）。
  - **有** → 展示制品列表：每条可点击「查看」进入制品查看页；顶部提供「重新生成」；生成失败展示原因并可重试。
- 复用于：制品库管理页卡片操作栏、文章信息流（`stream_v2` 右侧「可视化阅读 / 思维导图」按钮）、文章工作台阅读区按钮。

### 6.3 文章制品页 `GET /article/{article}/artifacts`

- 保留：展示该文章全部制品（按类型分组卡片），提供「生成 / 重新生成 / 保存为思维导图」。
- 入口：文章详情页头部「AI制品库」链接。

### 6.4 制品查看页 `GET /artifacts/{id}`

- 按 `file_type` 渲染：
  - `html` → 直接嵌入（内容生成时已做 XSS 清理）。
  - `json`(mind_map) → 用 jsMind 渲染（复用 `public/js/jsmind.js` + `css/jsmind.css`，节点树只读）。
  - `markdown` → marked.js 渲染。
  - `text` → `<pre>`。
- 顶部显示元信息：名称、类型、状态、模型、生成时间。

## 7. 权限与归属

- 所有查询/生成/删除严格按 `user_id` 隔离；非本人制品返回 404/403。
- Web 页依赖已认证会话（与其他模块一致）。
- `related_type/related_id` 为多态引用，不建外键（与 `minds.source_*` 一致）。

## 8. 非目标（本期不做）

- 制品版本历史 / 多个同类型制品并存（唯一约束保证单版本，重新生成覆盖）。
- 简报类生成器（`briefing_latest` / `briefing_followed`）本期只预留类型，不实现生成。
- 笔记制品（`note_mind_map` 等）本期预留，新增生成器即可接入。
- 向量检索 / RAG。
- 制品公开分享（如需要可复用 notes 分享体系）。

## 9. 文件清单（实现路径）

- 迁移：`database/migrations/2026_08_18_000002_create_artifacts_table.php`
- 模型：`app/Models/Artifact.php`
- Repository：`app/Repositories/ArtifactRepository.php`
- Service：`app/Services/ArtifactService.php`
- API 控制器：`app/Http/Controllers/Api/V2/ArtifactController.php`
- Web 控制器：`app/Http/Controllers/ArtifactController.php`
- 视图：`resources/views/artifacts/index.blade.php`（文章制品页）、`resources/views/artifacts/view.blade.php`（制品查看页）
- 路由：`routes/api.php`（/api/v2/artifacts*）、`routes/web.php`（/article/{article}/artifacts、/artifacts/{id}）
- 文档：本文档 + OpenAPI 补充（如需要）

## 10. 验收标准

1. `php artisan migrate` 成功，`artifacts` 表结构与 §2.1 一致。
2. 对一篇文章 `POST /api/v2/artifacts/generate`（visual_reading / mind_map）各生成成功制品；再次调用命中复用（`generated=false`）；`force=1` 重新生成。
3. `GET /api/v2/artifacts?related_type=article&related_id=x` 返回该文章两个制品。
4. 思维导图制品 `POST /artifacts/{id}/to-mind` 后可在 `/mind/{id}` 正常查看节点树。
5. 非本人制品查询/删除不可达。
6. Web 文章制品页可完成「生成 → 查看 → 保存为导图」全流程。