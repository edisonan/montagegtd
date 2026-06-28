# 平台通用能力

## 鉴权

所有受保护操作使用：

```http
Authorization: Bearer <token>
```

token 类型：

- `pat_...`：Personal Access Token，适合 CLI、脚本和 Codex 自动化。
- `uat_...`：用户登录 access token，适合前端会话。

PAT scope：

- `read`：查询、列表、详情。
- `write`：创建、更新、删除、状态流转、动作接口。
- `admin`：LLM provider/model/credential 等管理能力。

创建 PAT：

```bash
montage_cli.py pat-create --name codex-cli --scopes read,write
```

撤销 PAT：

```bash
montage_cli.py pat-revoke 12
```

## 专注

查看当前专注：

```bash
montage_cli.py focus-status
```

开始专注：

```bash
montage_cli.py focus-start
```

完成专注，通常需要等后端判断已到结束时间：

```bash
montage_cli.py focus-complete 123 --name "深度工作"
```

写复盘：

```bash
montage_cli.py request PUT /focuss/123 --data '{"rating":5,"review_note":"状态很好"}'
```

## 计划

计划用于承载一组任务或阶段目标。

```bash
montage_cli.py plan-list --status 1
montage_cli.py plan-create --name "本周输出计划"
montage_cli.py plan-finish 12
```

状态：

- `1`：进行中。
- `2`：完成。
- 删除接口中 `type=finish` 表示完成计划，否则后端会把状态置为其他归档状态。

## 日报

创建日报：

```bash
montage_cli.py daily-summary-create \
  --summary-date 2026-06-29 \
  --work-content "完成文章阅读页 skill 重写" \
  --life-content "散步 30 分钟"
```

按日期查看：

```bash
montage_cli.py daily-summary-date --summary-date 2026-06-29
```

日报创建可能触发积分。

## 学习

学习计划和打卡目前用通用请求：

```bash
montage_cli.py request GET /study/overview
montage_cli.py request GET /study/plans
montage_cli.py request POST /study/plans --data '{...}'
montage_cli.py request POST /study/tasks/42/checkin --data '{"note":"完成"}'
```

## LLM 和摘要

LLM 会话：

```bash
montage_cli.py request GET /llm/sessions
montage_cli.py request POST /llm/chat --data '{...}'
```

摘要页：

```bash
montage_cli.py request GET /digest/profile
montage_cli.py request POST /digest/profile --data '{...}'
montage_cli.py request POST /digest/pages/generate --data '{...}'
```

## 通用请求兜底

当领域命令没有覆盖时使用：

```bash
montage_cli.py request GET /auth/me
montage_cli.py request POST /some/path --data '{"key":"value"}'
```

响应一般是：

```json
{"code":9999,"msg":"ok","result":{}}
```

非 2xx 或 `code != 9999` 都应视为失败。

## 常见错误

- 401：token 缺失、格式错误、过期、撤销或服务环境不匹配。
- 403：scope 不足，通常是拿 read token 调 write/admin 能力。
- 422：字段格式不符合控制器校验，例如时间格式、缺少必填字段。
- 502：网关或远端服务不可用，优先切换本地环境或稍后重试。
