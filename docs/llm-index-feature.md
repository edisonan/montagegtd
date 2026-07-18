# `/llm/index` AI 助手功能说明

## 1. 功能定位

`/llm/index` 是 MontageGTD 的 AI 对话工作台，面向已登录用户提供基于智能体（Agent）的多轮对话能力。

页面入口：

```text
GET /llm/index
```

页面由 `LlmSessionController@index` 返回 `resources/views/llm/index.blade.php`，访问需要 Web 登录态。

模型和供应商不是在本页面直接选择，而是由智能体绑定模型后参与对话。供应商、模型和智能体管理入口分别为：

```text
/llm/llmmanagement
/llm/agentmanagement
```

## 2. 页面功能

### 2.1 新建对话

- 选择一个智能体。
- 输入首条问题后创建会话并立即发送。
- 页面会自动选择优先的通用智能体，进入后可以直接输入发送。
- 未输入问题时可以先创建空会话。
- 支持 Enter 发送，Shift+Enter 换行。
- 输入框支持最多 8000 字符，并按会话自动保存未发送草稿。
- 首轮发送后自动根据问题生成简洁会话标题。
- 支持按钮选择、拖放或粘贴文本、代码、DOCX 和 PDF 附件；单条消息最多 5 个，每个最大 10MB。
- 提供梳理今天、发展想法、提炼内容等快捷场景。

### 2.2 会话列表

左侧显示当前用户的历史会话，支持：

- 搜索会话标题。
- 按智能体筛选。
- 全部、固定、最近活跃三种快捷筛选。
- 新建会话。
- 固定/取消固定。
- 清理未固定会话。
- 点击会话加载历史消息。

### 2.3 多轮聊天

- 对话请求通过当前会话和智能体发送。
- AI 回复使用 SSE 流式响应实时渲染。
- 回复支持安全过滤后的 Markdown、代码块和代码一键复制。
- 支持服务端停止当前生成；停止后的部分回答仍会写入历史。
- 支持重新生成上一轮回复，并创建一个独立分支，原回答不会丢失。
- 支持编辑任意一条用户消息并创建无损分支；分支会复制此前上下文和附件，原会话及后续内容完整保留。
- 会话标题下方提供原对话/分支导航，可在多个版本之间切换。
- 每条 AI 回复支持点赞、点踩或取消反馈。
- 支持引用上一条 AI 回复到输入框。
- 用户上翻历史时不会被流式输出强制拉回底部，并提供“回到最新消息”按钮。
- 支持失败消息原位重试、Esc 停止生成和 `Cmd/Ctrl + K` 新建会话。
- PC 端使用固定会话侧栏，手机端使用抽屉式会话列表。

### 2.4 会话操作

- 双击标题或点击重命名修改会话标题。
- 固定/取消固定会话。
- 清空当前会话消息。
- 删除当前会话。
- 将当前会话导出为 Markdown 文件。

### 2.5 AI 回复沉淀

每条 AI 回复支持进一步沉淀到 MontageGTD：

- 保存为私密笔记，标题格式为 `[AI] 会话标题 - 回复首行`。
- 生成思维导图，标题格式为 `[AI导图] 会话标题 - 回复首行`。
- 思维导图会记录 `source_type=llm` 和当前会话 ID，并打开思维导图编辑页。

## 3. 对话数据流

```text
用户打开 /llm/index
        ↓
加载会话列表 + 智能体列表
        ↓
选择智能体并创建会话
        ↓
POST /api/v2/llm/sessions
        ↓
POST /api/v2/llm/chat
        ↓
服务端根据 Agent 找到绑定模型和 Provider
        ↓
向 OpenAI 兼容 Provider 发起模型请求
        ↓
以 SSE 流返回 AI 内容
        ↓
保存会话消息、更新会话统计
```

实际模型由以下关系决定：

```text
LlmSession → LlmAgent → LlmAgentVersion → LlmModel → LlmProvider
```

如果 Provider 没有可读取的 API Key、Base URL 不正确、模型未启用或 Agent 没有可用版本，聊天请求会失败。

## 4. `/llm/index` 使用的接口

以下接口均使用 `/api/v2` 前缀，并通过 `window.taskApiFetch` 发起请求。页面会自动附带当前登录态和 CSRF Token。

### 4.1 会话接口

| 方法 | 接口 | 用途 | 主要参数 |
| --- | --- | --- | --- |
| GET | `/api/v2/llm/sessions` | 获取当前用户会话列表 | 无 |
| POST | `/api/v2/llm/sessions` | 创建会话 | `agent_id`, `title` |
| GET | `/api/v2/llm/sessions/{id}` | 获取会话详情和历史消息 | 路径参数 `id` |
| PUT | `/api/v2/llm/sessions/{id}/title` | 修改会话标题 | `title` |
| POST | `/api/v2/llm/sessions/{id}/toggle-pin` | 固定/取消固定 | 无 |
| POST | `/api/v2/llm/sessions/{id}/clear` | 清空会话消息 | 无 |
| POST | `/api/v2/llm/sessions/{id}/regenerate` | 保留原会话并创建重试分支 | 无 |
| POST | `/api/v2/llm/sessions/{id}/messages/{conversationId}/branch` | 从指定用户消息创建无损编辑分支 | `query` |
| PUT | `/api/v2/llm/sessions/{id}/messages/{conversationId}/feedback` | 评价或取消评价 AI 回复 | `feedback`: `-1`, `0`, `1` |
| DELETE | `/api/v2/llm/sessions/{id}` | 删除会话 | 无 |

会话接口主要返回：

```json
{
  "success": true,
  "data": {}
}
```

### 4.2 智能体接口

| 方法 | 接口 | 用途 |
| --- | --- | --- |
| GET | `/api/v2/llm/agents` | 加载可用智能体并填充选择框 |
| GET | `/api/v2/llm/agents/{id}` | 获取智能体详情 |

智能体列表用于确定当前聊天使用的系统提示词、模型、温度、上下文长度和其他能力配置。

### 4.3 聊天接口

| 方法 | 接口 | 用途 | 主要参数 |
| --- | --- | --- | --- |
| POST | `/api/v2/llm/chat` | 发起一次流式 AI 对话 | `query`, `session_id`, `agent_id`, `generation_id`, `attachment_ids` |
| POST | `/api/v2/llm/chat/stop` | 停止指定的生成任务 | `generation_id` |

请求示例：

```json
{
  "query": "帮我整理今天的工作计划",
  "session_id": 123,
  "agent_id": 5,
  "generation_id": "client-generated-unique-id",
  "attachment_ids": [81, 82]
}
```

响应类型为：

```text
text/event-stream
```

前端兼容供应商的 OpenAI SSE 增量，并额外识别服务端事件：

```text
type=stopped  当前生成已停止，部分回答已保存
type=done     返回 conversation_id、stopped、usage 和 session_title
[DONE]        流结束标识
```

每轮完成后会持久化回答、模型、生成标识、输入/输出/总 token 和回答时间。会话详情中的 user/assistant 消息会返回同一个 `conversation_id`，供编辑分支使用。

服务端按从新到旧的顺序构造上下文，最多读取 24 轮，并控制在约 48000 字符预算内，避免长会话无限膨胀。

### 4.4 附件接口

| 方法 | 接口 | 用途 | 主要参数 |
| --- | --- | --- | --- |
| POST | `/api/v2/llm/attachments` | 上传并提取附件文本 | multipart 字段 `file` |
| DELETE | `/api/v2/llm/attachments/{id}` | 删除尚未发送的附件 | 路径参数 `id` |

附件上传成功后返回 `id`、文件名、扩展名、大小和文本预览。发送聊天时通过 `attachment_ids` 绑定到当前消息；历史上下文会重新注入附件内容。PDF 使用 OpenRouter 的 `file` 内容类型和免费的 `cloudflare-ai` 解析引擎，因此 PDF 对话当前仅支持 OpenRouter 供应商。清空或删除会话时，附件记录和磁盘文件会同步删除。分支中的附件使用独立副本，避免删除一个分支影响另一个分支。

### 4.5 回复沉淀接口

| 方法 | 接口 | 用途 | 主要参数 |
| --- | --- | --- | --- |
| POST | `/api/v2/notes` | 将 AI 回复保存为笔记 | `name`, `content`, `tags`, `status` |
| POST | `/api/v2/minds` | 将 AI 回复创建为思维导图根节点 | `name`, `content`, `source_type`, `source_id` |

思维导图创建请求示例：

```json
{
  "name": "[AI导图] 工作计划 - 今日重点",
  "content": "AI 回复正文",
  "source_type": "llm",
  "source_id": 123
}
```

## 5. 认证和权限

### Web 页面

`GET /llm/index` 使用 Laravel `auth` 中间件，未登录用户会被重定向到登录页。

### API 请求

当前 API 路由使用混合 Token 策略：

- 读取接口通常使用 `hybrid.token:read`。
- 写入接口通常使用 `hybrid.token:write`。
- 页面内请求还会携带 Web Session 和 CSRF Token。
- 会话、消息、笔记和思维导图均按当前用户权限处理。

## 6. 依赖的配置对象

### Provider

供应商至少需要：

- `base_url`
- `api_type`
- 加密保存的 `api_key`
- `is_active=1`

OpenAI 兼容供应商的 `base_url` 应填写到 API 基础路径，例如：

```text
https://openrouter.ai/api/v1
```

代码会自动拼接 `/chat/completions`，因此不要把完整的 `/chat/completions` 再填入 Base URL。

### Model

模型至少需要：

- 绑定 `provider_id`
- `name`
- `model_type=chat`
- `is_active=1`

### Agent

Agent 需要绑定可用模型，并通过当前版本提供：

- 系统提示词
- 温度和 Top P
- 最大输出长度
- 上下文长度
- 工具配置

## 7. 常见故障排查

### 智能体选择框为空

检查：

1. `/api/v2/llm/agents` 是否返回成功。
2. 当前用户是否拥有可用 Agent。
3. Agent 是否绑定有效模型。

### 模型请求失败

按以下顺序检查：

1. Provider 的 `base_url` 是否正确。
2. Provider 的 `api_key` 是否存在并可解密。
3. Provider 和 Model 是否启用。
4. Model 名称是否符合供应商要求。
5. 线上服务器是否能访问 Provider 地址。
6. Laravel 日志中的 HTTP 状态码和响应内容。

### 仅本地可用、线上失败

重点比较：

- 线上数据库的 Provider 配置。
- 线上 `.env` 的数据库连接。
- 线上服务器的 DNS、网络和 TLS。
- Base URL 是否被错误配置成 IP、错误路径或旧网关地址。
- 线上代码是否已部署到包含最新 LLM Key 读取逻辑的版本。

## 8. 相关代码

- 页面：`resources/views/llm/index.blade.php`
- 页面运行时：`public/js/llm-chat-workbench.js`
- Web Controller：`app/Http/Controllers/LlmSessionController.php`
- LLM Controller：`app/Http/Controllers/LlmController.php`
- Agent Controller：`app/Http/Controllers/LlmAgentController.php`
- Session Service：`app/Services/LlmSessionService.php`
- API 路由：`routes/api.php`
- Web 路由：`routes/web.php`
- 生产 HTTP 冒烟测试：`scripts/smoke_llm_chat_http.php`
- PC/手机渲染夹具：`scripts/serve_llm_ui_fixture.js`
