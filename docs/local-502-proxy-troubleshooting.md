# 本地环境 502 排查与修复记录

## 背景

本地访问：

```bash
http://task.congcong.us/
http://task.congcong.us/login
```

浏览器显示 `502 Bad Gateway`，但命令行直接 `curl` 访问同一地址时返回正常。

本次问题最终确认不是 Laravel、nginx 或 php-fpm 服务异常，而是浏览器请求经过了本机代理 `127.0.0.1:7897`，代理访问本地域名失败并返回 502。

## 现象

浏览器导出的请求中出现了：

```http
Proxy-Connection: keep-alive
```

这通常表示请求经过了 HTTP 代理。

同时，直接访问本地服务正常：

```bash
curl -I --max-time 10 --noproxy '*' http://task.congcong.us/login
```

返回：

```http
HTTP/1.1 200 OK
Server: nginx/1.12.2
X-Powered-By: PHP/7.3.29-to-be-removed-in-future-macOS
```

但强制通过代理访问会复现浏览器里的错误：

```bash
curl -i --max-time 10 --proxy http://127.0.0.1:7897 http://task.congcong.us/login
```

返回：

```http
HTTP/1.1 502 Bad Gateway
Proxy-Connection: keep-alive
Content-Length: 0
```

## 排查步骤

### 1. 确认应用入口是否可访问

```bash
curl -i --max-time 10 http://task.congcong.us/
curl -i --max-time 10 http://task.congcong.us/login
```

本次排查中，首页和登录页都能返回 `200 OK`。

如果 `/login` 带有 `remember_web_*` cookie，Laravel 会自动登录并返回：

```http
HTTP/1.1 302 Found
Location: http://task.congcong.us/index
```

继续访问 `/index` 也返回 `200 OK`，说明应用本身正常。

### 2. 检查 nginx 和 php-fpm 是否在监听

```bash
lsof -nP -iTCP:80 -sTCP:LISTEN
lsof -nP -iTCP:9000 -sTCP:LISTEN
```

正常状态应类似：

```text
nginx    ... TCP *:80 (LISTEN)
php-fpm  ... TCP 127.0.0.1:9000 (LISTEN)
```

### 3. 校验 nginx 配置

```bash
nginx -t
```

正常结果：

```text
nginx: the configuration file /usr/local/etc/nginx/nginx.conf syntax is ok
nginx: configuration file /usr/local/etc/nginx/nginx.conf test is successful
```

### 4. 查看 nginx 错误日志

```bash
tail -n 120 /usr/local/var/log/nginx/error.log
```

历史上如果 php-fpm 没启动，nginx 会出现类似错误：

```text
connect() failed (61: Connection refused) while connecting to upstream
upstream: "fastcgi://127.0.0.1:9000"
```

如果是接口执行超时，会出现：

```text
upstream timed out while reading response header from upstream
```

本次浏览器 502 发生时，nginx error log 没有新增对应错误，说明 502 不是本机 nginx 产生的。

### 5. 查看 nginx access log 是否命中本机服务

```bash
tail -n 30 /usr/local/var/log/nginx/access.log
```

本次直连请求能看到：

```text
127.0.0.1 ... "GET /login HTTP/1.1" 302 ...
127.0.0.1 ... "GET /index HTTP/1.1" 200 ...
```

如果浏览器刷新时 access log 没有新增记录，说明浏览器请求没有打到本机 nginx。

### 6. 检查域名解析

```bash
dscacheutil -q host -a name task.congcong.us
```

正常应指向本机：

```text
name: task.congcong.us
ip_address: 127.0.0.1
```

对应 `/etc/hosts` 中应有：

```text
127.0.0.1 task.congcong.us
```

### 7. 检查系统代理

```bash
scutil --proxy
```

本次发现系统代理开启：

```text
HTTPEnable : 1
HTTPProxy : 127.0.0.1
HTTPPort : 7897
HTTPSEnable : 1
HTTPSProxy : 127.0.0.1
HTTPSPort : 7897
SOCKSEnable : 1
SOCKSProxy : 127.0.0.1
SOCKSPort : 7897
```

绕过列表里只有：

```text
127.0.0.1
localhost
*.local
<local>
```

没有 `task.congcong.us`，所以 Chrome 可能把这个本地域名交给代理处理。代理未使用 `/etc/hosts` 的本地解析结果，最终返回 502。

## 根因

`task.congcong.us` 虽然在 `/etc/hosts` 中指向 `127.0.0.1`，但浏览器启用了系统代理。因为该域名不在代理绕过列表里，浏览器把请求发给了 `127.0.0.1:7897` 代理。

代理访问该域名时没有按本机 hosts 直连本地 nginx，而是按代理自身逻辑解析和转发，导致返回：

```http
HTTP/1.1 502 Bad Gateway
```

## 修复步骤

### 1. 查看当前网络服务

```bash
networksetup -listallnetworkservices
```

本次活动网络是 `Wi-Fi`。

可用下面命令确认活动网卡：

```bash
route -n get default
networksetup -getinfo Wi-Fi
```

### 2. 查看当前代理绕过列表

```bash
networksetup -getproxybypassdomains Wi-Fi
```

### 3. 追加本地域名到代理绕过列表

保留原有规则，并追加 `task.congcong.us` 和 `*.congcong.us`：

```bash
networksetup -setproxybypassdomains Wi-Fi \
  127.0.0.1 \
  192.168.0.0/16 \
  10.0.0.0/8 \
  172.16.0.0/12 \
  localhost \
  '*.local' \
  '*.crashlytics.com' \
  '<local>' \
  task.congcong.us \
  '*.congcong.us'
```

### 4. 确认规则已写入

```bash
networksetup -getproxybypassdomains Wi-Fi
```

应包含：

```text
task.congcong.us
*.congcong.us
```

### 5. 验证直连正常

```bash
curl -I --max-time 10 --noproxy '*' http://task.congcong.us/login
```

应返回：

```http
HTTP/1.1 200 OK
```

### 6. 验证代理会返回 502

```bash
curl -i --max-time 10 --proxy http://127.0.0.1:7897 http://task.congcong.us/login
```

若返回：

```http
HTTP/1.1 502 Bad Gateway
```

说明浏览器之前的 502 确实来自代理链路，而不是本机 nginx/php-fpm。

## 复发处理

如果以后浏览器再次访问本地环境出现 502：

1. 先用 `curl --noproxy '*'` 验证直连是否正常。
2. 查看 nginx access log，确认浏览器请求是否命中本机 nginx。
3. 如果 access log 没有新增浏览器请求，优先检查系统代理或浏览器代理插件。
4. 在代理工具中添加直连规则：

```text
task.congcong.us DIRECT
*.congcong.us DIRECT
```

5. 或者在 macOS 代理绕过列表中重新加入：

```text
task.congcong.us
*.congcong.us
```

6. 修改代理规则后，如果 Chrome 仍然显示旧的 502，重启浏览器或关闭再打开代理工具。

## 快速判断表

| 现象 | 可能原因 | 处理 |
| --- | --- | --- |
| `curl --noproxy '*'` 返回 200，浏览器 502 | 浏览器走代理 | 添加代理绕过或代理 DIRECT 规则 |
| nginx error log 出现 `connect() failed ... fastcgi://127.0.0.1:9000` | php-fpm 未启动或端口不对 | 启动 php-fpm，确认监听 9000 |
| nginx error log 出现 `upstream timed out` | PHP 请求执行超时 | 查 Laravel 日志和慢接口 |
| access log 没有浏览器请求 | 请求没到本机 nginx | 检查代理、DNS、hosts、浏览器缓存 |
| `/login` 返回 302 到 `/index` | remember cookie 自动登录 | 这是正常行为，继续检查 `/index` |

