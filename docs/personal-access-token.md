# Personal Access Token 功能文档

## 1. 设计目标

- 支持用户为第三方调用创建长期可控的访问凭证（PAT）。
- 支持统一鉴权、统一用户上下文注入、统一 scope 权限校验。
- 支持细粒度授权与安全失效（过期、撤销、删除）。
- 保持对历史 token 数据的兼容。

## 2. 关键结论（针对你的问题）

不是把 token 强制分成“read token / write token”两种类型。

系统设计是：**一个 token，用户在创建时自行勾选 scopes**，例如：

- 仅 `read`
- `read + write`
- `read + write + admin`
- `*`（全权限）

路由按 `hybrid.token:scope` 校验，只要该 token 包含对应 scope 即可访问。

## 3. 数据模型

表：`personal_access_tokens`

核心字段：

- `id`：主键
- `user_id`：归属用户
- `name`：token 名称
- `token`：非认证用途占位值（不存储可直接认证的明文）
- `token_hash`：`sha256` 哈希，用于认证匹配
- `scopes`：JSON 数组
- `revoked_at`：撤销时间
- `expires_at`：过期时间
- `last_used_at`：最后使用时间
- `created_at`/`updated_at`

表：`token_usage_logs`

- `token_id`
- `api_endpoint`
- `ip_address`
- `user_agent`
- `created_at`/`updated_at`

## 4. 生命周期

### 4.1 创建

1. 用户在页面填写 `name`、`scopes`、`expires_at`。
2. 系统生成明文 token（仅创建响应返回一次）。
3. 系统持久化 `token_hash`，不以明文作为认证主依据；明文 token 统一使用 `pat_` 前缀。

### 4.2 使用

请求头：

```text
Authorization: Bearer {token}
```

中间件统一完成：

1. 解析 Bearer token
2. 校验 token（哈希匹配 + 过期/撤销判断）
3. 校验 scope（路由声明的 required scopes）
4. 注入请求上下文（当前用户、当前 PAT）
5. 记录使用日志（失败不阻断主流程）

### 4.3 失效

- 撤销：默认逻辑（保留记录，立即不可用）
- 删除：`force_delete=1` 时可物理删除
- 过期：`expires_at` 到期后自动不可用

## 5. 权限模型（Scope）

默认 scope 集：

- `read`
- `write`
- `delete`
- `admin`

可扩展建议：

- `llm:read`、`llm:write`
- `course:read`、`course:write`
- `provider:admin`

> 建议长期从“通用 scope”逐步演进到“领域 scope”。

## 6. API 路由分层（当前）

前缀：`/api/v1`

### 6.1 read

中间件：`hybrid.token:read`

示例：

- `GET /api/v1/auth/verify`
- `GET /api/v1/auth/me`
- `GET /api/v1/llm/sessions`
- `GET /api/v1/llm/agents`
- `GET /api/v1/llm/models`

### 6.2 write

中间件：`hybrid.token:write`

示例：

- `POST /api/v1/llm/chat`
- `POST /api/v1/llm/sessions`
- `PUT /api/v1/llm/sessions/{id}/title`
- `DELETE /api/v1/llm/sessions/{id}`
- `POST/PUT/DELETE /api/v1/llm/agents...`

### 6.3 admin

中间件：`hybrid.token:admin`

示例：

- `POST/PUT/DELETE /api/v1/llm/providers...`
- `POST/PUT/DELETE /api/v1/llm/models...`
- `POST/PUT/DELETE /api/v1/llm/credentials...`

## 7. 用户体验建议（创建 token）

创建页面建议使用多选框让用户勾选 scopes，而不是“read token / write token”单选：

- [ ] read
- [ ] write
- [ ] admin
- [ ] *（危险操作，二次确认）

推荐预设：

- 只读集成：默认勾选 `read`
- 自动化写入：`read + write`
- 运维管理：`read + write + admin`

## 8. 安全策略

- 明文 token 仅创建时展示一次。
- 模型序列化默认隐藏 `token` / `token_hash`。
- 日志记录 token 使用行为，便于审计。
- 鉴权失败统一返回 `401`，权限不足返回 `403`。

## 9. 兼容性

- 对历史明文 token 数据做向后兼容：首次命中可自动迁移到哈希校验路径。
- PAT 相关改动尽量兼容 PHP 7.0 语法边界。

## 10. 验收用例

### 10.1 功能正确性

1. `read` token 调 read 路由 -> 200
2. `read` token 调 write 路由 -> 403
3. `write` token 调 write 路由 -> 200
4. 撤销 token 后调用任意 PAT 路由 -> 401
5. 过期 token 调用 -> 401

### 10.2 安全性

1. 响应中不泄漏 `token_hash`
2. token 列表不返回可直接认证的明文 token
3. 无 `Authorization` 头 -> 401
4. 非 Bearer 格式 -> 401

## 11. 后续优化清单

- 增加“最后使用来源”聚合视图（按 endpoint/IP）。
- 增加“按 scope 统计调用量”监控面板。
- 增加“token 轮换”接口（新旧 token 平滑切换窗口）。
- 补充 Feature Test（鉴权、scope、撤销、兼容迁移）。
