# API V2 OpenAPI 文档说明

- OpenAPI 文件：`docs/openapi-v2.yaml`
- 生成脚本：`scripts/generate_openapi_v2.php`
- 生成命令：

```bash
php scripts/generate_openapi_v2.php
```

## 设计目标（AI 友好）

这份文档专门做了以下增强，便于 AI Agent / API Client 自动调用：

- 稳定 `operationId`（基于 `Controller@action + method + path`）
- 统一响应结构（`code/msg/result`）
- 每个接口标注 `x-token-capability`：`public/read/write/admin/token/web-session`
- 每个接口带 `x-ai-hints`（控制器与方法）
- 所有写接口自动带通用 `requestBody`（`FlexibleObject`）

## 鉴权

默认使用：

```http
Authorization: Bearer <PAT|UAT>
```

OpenAPI 中安全方案：

- `BearerAuth`：PAT/UAT
- `SessionCookie`：仅少量 web-session 引导接口

## 快速验证示例

```bash
curl -s 'http://testtask.congcong.us/api/v2/feeds/navinfo' \
  -H 'Authorization: Bearer <YOUR_PAT>'
```

## 注意事项

- 这是基于路由自动生成的文档，字段级 schema 暂为通用结构。
- 若需要更强 AI 调用准确率，建议在控制器逐步补充精确请求/响应 schema，并合并到生成器（白名单增强）。
