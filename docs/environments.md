# 项目环境与部署说明

> 本文档用于明确本项目的**本地/远程环境对应关系**以及部署时的关键注意事项。
> 核心结论：**`testtask` = 本地环境，`task` = 远程生产环境**，两者切勿混淆。

## 环境对应关系

| 环境 | 域名 | 说明 | 数据入口 |
| ---- | ---- | ---- | -------- |
| **本地开发** | `http://testtask.congcong.us/` | 本机 nginx + PHP-FPM，通过本机 `/etc/hosts` 映射到 `127.0.0.1` | `http://testtask.congcong.us/` |
| **远程生产** | `https://task.congcong.us` | 云服务器（`23.238.119.21`），nginx 配置在 `/etc/nginx/conf.d/taskcongcong.conf` | `https://task.congcong.us` |

### 本地为何是 `testtask`

`testtask.congcong.us` 并没有公网 A 记录，它能在本地访问是因为本机 `/etc/hosts` 里有：

```
127.0.0.1 testtask.congcong.us
```

所以**公网 `dig` 查不到它是正常的**——它只在本机生效，不要据此判定环境不存在。
不要用 `curl https://task.congcong.us`（远程）去验证本地改动；本地联调请使用 `testtask.congcong.us`。

## 远程服务器关键信息

- 服务器：`root@task.congcong.us`（`23.238.119.21`）
- 部署路径：`/home/q/system/task`（这是一个**指向 `task-pre` 的符号链接**，真实目录是 `/home/q/system/task-pre`）
- nginx 站点：`/etc/nginx/conf.d/taskcongcong.conf`，其 `root /home/q/system/task-pre/public`（与符号链接指向一致）
- 同机其它域名：`pretask.congcong.us`（预发布，root `/home/q/system/task-pre/public` 相同的 nginx 配置）、`congcong.us`/`www.congcong.us`
- PHP-FPM 由 `apache` 用户运行（`php-fpm: pool www`），池监听 `127.0.0.1:9000`

> 部署时注意：`/home/q/system/task` 与 `task-pre` 是同一目录（符号链接），修改任一路径效果相同。

## 常用命令

```bash
# 本地浏览器联调（推荐）
open http://testtask.congcong.us/

# API 冒烟检查（本地）
curl -i --max-time 10 --noproxy '*' http://testtask.congcong.us/login

# 远程部署（详见 scripts/deploy_task_rsync.sh）
bash scripts/deploy_task_rsync.sh
```

## 部署注意：改了 Blade 后“没生效”

Laravel 会缓存编译后的 Blade 模板到 `storage/framework/views`。如果修改了 `resources/views/**/*.blade.php` 部署后页面没变化，**先清除视图缓存**：

```bash
# 在远程服务器项目根目录执行
php artisan view:clear
```

权限方面：`storage/framework/views`（及 `storage/logs`、`storage/framework/{cache,sessions}`）由 `apache:apache` 拥有且可写，`php artisan view:clear` 后正常请求会重新编译模板。

## 本机 SSH 部署权限问题（常见坑）

`scripts/deploy_task_rsync.sh` 通过 SSH 推送，依赖 `~/.ssh/known_hosts`。

- 若报 `Host key verification failed.`，多半是 `~/.ssh/known_hosts` 的所有者变成了 `root`（当前用户无读取权限导致的）。
- 修复：在**本机自己的终端**执行

  ```bash
  sudo chown ancongcong ~/.ssh/known_hosts
  ```

- 临时绕过（仅应急，不推荐长期使用）：

  ```bash
  ssh -o UserKnownHostsFile=/tmp/kh -o StrictHostKeyChecking=accept-new root@task.congcong.us ...
  ```

## 相关文档

- 本地 502 问题排查记录：`docs/local-502-proxy-troubleshooting.md`（该文档中的本地访问地址仍写作 `task.congcong.us`，为历史遗留，实际本地应使用 `testtask.congcong.us`）
- 部署脚本：`scripts/deploy_task_rsync.sh`
