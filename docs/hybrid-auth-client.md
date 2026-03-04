
# Hybrid Token Client 接入说明

## 目标

统一使用 `/api/v2/*` 接口，并通过 `Authorization: Bearer <token>` 同时支持：

- `uat_`：用户登录 access token（多端会话）
- `pat_`：personal access token（脚本/集成）

## 前端 SDK（纯 JS 直引版）

文件：`public/js/hybrid-api-client.js`

默认会：

1. 自动附带 `Authorization` 头
2. 请求返回 401 时自动调用 `/api/v2/auth/refresh`
3. refresh 成功后自动重放原请求
4. refresh 失败自动清空本地 token

布局已直接引入：`<script src="/js/hybrid-api-client.js"></script>`  
全局对象：`window.TaskApiClient`

## 典型用法

```js
// 登录并缓存 token 对
window.TaskApiClient.login({
  email: 'your@email.com',
  password: 'your-password',
  client_type: 'web',
  device_id: 'browser-1'
});

// 调用受保护接口（自动带 Bearer）
window.TaskApiClient.get('/llm/sessions').then(function (resp) {
  console.log(resp.data);
});

// 登出（服务端撤销 + 本地清理）
window.TaskApiClient.logout();
```

## 存储键

默认 localStorage：

- `task_access_token`
- `task_refresh_token`

可在创建 client 时覆写。
