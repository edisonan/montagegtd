/* ============================================================
   蒙太奇 (MontageGTD) - PWA 客户端离线模块
   ------------------------------------------------------------
   职责：
   1. 注册 Service Worker
   2. IndexedDB 离线写队列（任务/笔记等 /api/v2 写操作）
   3. 断网时写操作入队（乐观保存），联网后自动重放
      - Background Sync（sw.js 的 sync 事件）
      - 监听 online 事件
      - 页面活跃时定时兜底重放
   4. API GET 离线阅读：交由 SW 的 stale-while-revalidate 缓存 + 本地 IndexedDB 读取缓存
   5. 安装提示、应用更新提示
   拦截点：TaskApiClient.request（TaskApiBridge 与 taskApiFetch 最终都汇入此处）
   ============================================================ */
(function (global) {
  'use strict';

  var DB_NAME = 'montage_pwa';
  var DB_VERSION = 1;
  var STORE_OPS = 'pending_ops';
  var STORE_READ = 'read_cache';
  var SYNC_TAG = 'montage-pwa-sync';
  var OFFLINE = 'offline';
  var AUTH_ROUTES = [
    '/api/v2/auth/login', '/auth/login',
    '/api/v2/auth/refresh', '/auth/refresh',
    '/api/v2/auth/register', '/auth/register'
  ];

  /* ---------- IndexedDB 基础 ---------- */
  function openDb() {
    return new Promise(function (resolve, reject) {
      var req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onupgradeneeded = function () {
        var db = req.result;
        if (!db.objectStoreNames.contains(STORE_OPS)) {
          db.createObjectStore(STORE_OPS, { keyPath: 'id', autoIncrement: true });
        }
        if (!db.objectStoreNames.contains(STORE_READ)) {
          db.createObjectStore(STORE_READ, { keyPath: 'key' });
        }
      };
      req.onsuccess = function () { resolve(req.result); };
      req.onerror = function () { reject(req.error); };
    });
  }

  function withStore(store, mode, fn) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var t = db.transaction(store, mode);
        var s = t.objectStore(store);
        var result;
        try { result = fn(s); } catch (e) { return reject(e); }
        t.oncomplete = function () { resolve(result); db.close(); };
        t.onerror = function () { reject(t.error); db.close(); };
        t.onabort = function () { reject(t.error); db.close(); };
      });
    });
  }

  /* ---------- 写队列 ---------- */
  function enqueueOp(op) {
    return withStore(STORE_OPS, 'readwrite', function (s) { return s.add(op); });
  }
  function listOps() {
    return withStore(STORE_OPS, 'readonly', function (s) {
      var req = s.getAll();
      return new Promise(function (resolve) { req.onsuccess = function () { resolve(req.result || []); }; });
    });
  }
  function removeOp(id) {
    return withStore(STORE_OPS, 'readwrite', function (s) { return s.delete(id); });
  }
  function countOps() {
    return withStore(STORE_OPS, 'readonly', function (s) {
      var req = s.count();
      return new Promise(function (resolve) { req.onsuccess = function () { resolve(req.result || 0); }; });
    });
  }

  /* ---------- 读取缓存（API GET 离线阅读的本地补充） ---------- */
  function setReadCache(key, data, ttlMs) {
    return withStore(STORE_READ, 'readwrite', function (s) {
      return s.put({ key: key, data: data, updatedAt: Date.now(), ttlMs: ttlMs || 0 });
    });
  }
  function getReadCache(key) {
    return withStore(STORE_READ, 'readonly', function (s) {
      var req = s.get(key);
      return new Promise(function (resolve) {
        req.onsuccess = function () { resolve(req.result || null); };
        req.onerror = function () { resolve(null); };
      });
    }).then(function (r) {
      if (!r) { return null; }
      if (r.ttlMs && (Date.now() - r.updatedAt > r.ttlMs)) { return null; }
      return r.data;
    });
  }

  /* ---------- 辅助 ---------- */
  function isWriteMethod(method) {
    method = (method || 'GET').toUpperCase();
    return method === 'POST' || method === 'PUT' || method === 'PATCH' || method === 'DELETE';
  }
  function isApiPath(url) {
    return url.indexOf('/api/v2/') !== -1;
  }
  function isAuthRoute(url) {
    return AUTH_ROUTES.indexOf(url) !== -1;
  }
  function getCsrf() {
    var n = global.document && global.document.head
      ? global.document.head.querySelector('meta[name="csrf-token"]') : null;
    return n ? n.content : '';
  }

  /* ---------- 离线写入数据库 ---------- */
  function enqueueWrite(method, fullUrl, body) {
    return enqueueOp({
      method: method,
      url: fullUrl,
      body: body !== undefined && body !== null ? body : null,
      createdAt: Date.now()
    }).then(function () {
      markOfflineUI();
      registerSync();
      updateBadgeCount();
      return { queuedOffline: true };
    });
  }

  /* ---------- 重放一条写操作 ----------
     通过原始的 TaskApiClient.request 重放，以复用其 401 → token 刷新 → 重试 逻辑；
     不用被包装后的 request，避免失败时重复入队。 */
  var originalClientRequest = null;

  /* 兜底重放：原生 fetch + 当前 token（无 TaskApiClient 时） */
  function replayRaw(op) {
    var token = '';
    if (global.TaskApiClient && typeof global.TaskApiClient.getAccessToken === 'function') {
      token = global.TaskApiClient.getAccessToken() || '';
    }
    var headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    var csrf = getCsrf();
    if (csrf) { headers['X-CSRF-TOKEN'] = csrf; }
    if (token) { headers['Authorization'] = 'Bearer ' + token; }

    return fetch(op.url, {
      method: op.method,
      credentials: 'same-origin',
      headers: headers,
      body: op.method === 'DELETE' ? undefined : (op.body !== null ? op.body : undefined)
    }).then(function (resp) {
      return resp.ok;
    }).catch(function () {
      return false;
    });
  }

  function replayOp(op) {
    if (originalClientRequest && global.TaskApiClient) {
      return originalClientRequest.call(global.TaskApiClient, {
        method: op.method,
        url: op.url,
        body: op.body !== null && op.body !== undefined ? op.body : {}
      }).then(function () {
        return true;
      }).catch(function () {
        return false;
      });
    }
    /* 兜底：原生 fetch + 当前 token */
    return replayRaw(op);
  }

  /* ---------- 重放整个队列 ---------- */
  var flushing = false;
  function flushQueue() {
    if (flushing) { return Promise.resolve({ flushed: 0, remaining: -1 }); }
    flushing = true;
    return listOps().then(function (ops) {
      if (!ops || ops.length === 0) { flushing = false; return { flushed: 0, remaining: 0 }; }
      var result = { flushed: 0, remaining: ops.length };
      return ops.reduce(function (chain, op) {
        return chain.then(function () {
          if (!global.navigator.onLine) { return; }
          return replayOp(op).then(function (ok) {
            if (ok) {
              return removeOp(op.id).then(function () { result.flushed += 1; });
            }
          }).catch(function () {});
        });
      }, Promise.resolve()).then(function () {
        return listOps().then(function (left) {
          result.remaining = left.length;
          updateBadgeCount();
          return result;
        });
      });
    }).then(function (r) { flushing = false; return r; })
      .catch(function (e) { flushing = false; throw e; });
  }

  /* ---------- 注册后台同步 ---------- */
  function registerSync() {
    if (!('serviceWorker' in global.navigator)) { return Promise.resolve(); }
    return global.navigator.serviceWorker.ready.then(function (reg) {
      if (reg.sync && reg.sync.register) {
        return reg.sync.register(SYNC_TAG).catch(function () {});
      }
    }).catch(function () {});
  }

  /* ---------- 拦截 TaskApiClient.request ---------- */
  function wrapTaskApiClient() {
    var client = global.TaskApiClient;
    if (!client || typeof client.request !== 'function') { return; }
    var original = client.request;
    originalClientRequest = original;

    client.request = function (config) {
      var cfg = config || {};
      var method = (cfg.method || 'GET').toUpperCase();
      var url = cfg.url || '/';
      var baseURL = '/api/v2';
      var fullUrl;
      if (/^https?:\/\//i.test(url)) { fullUrl = url; }
      else if (url.indexOf(baseURL) === 0) { fullUrl = url; }
      else { fullUrl = baseURL + (url.charAt(0) === '/' ? url : '/' + url); }

      /* 认证路由：不拦截 */
      if (isAuthRoute(url) || isAuthRoute(fullUrl)) {
        return original.apply(client, arguments);
      }

      /* 写操作 */
      if (isWriteMethod(method) && isApiPath(fullUrl)) {
        if (!global.navigator.onLine) {
          return enqueueWrite(method, fullUrl, cfg.body).then(function () {
            return { status: 207, data: { code: OFFLINE, msg: 'queued offline', result: null }, headers: {} };
          });
        }
        return original.call(client, cfg).catch(function (err) {
          /* 网络层失败（fetch 抛错 / status 0 / 5xx）→ 入队乐观保存 */
          var status = err && err.status ? err.status : 0;
          if (status === 0 || status >= 500 || (err && err.message === 'Failed to fetch')) {
            return enqueueWrite(method, fullUrl, cfg.body).then(function () {
              return { status: 207, data: { code: OFFLINE, msg: 'queued offline', result: null }, headers: {} };
            });
          }
          throw err;
        });
      }

      /* 读操作：原样执行（离线由 SW 的 stale-while-revalidate 兜底） */
      return original.apply(client, arguments);
    };
  }

  /* ---------- 拦截 taskApiFetch（备用路径） ---------- */
  function wrapTaskApiFetch() {
    var original = global.taskApiFetch;
    if (typeof original !== 'function') { return; }
    global.taskApiFetch = function (url, options) {
      var opts = options || {};
      var method = (opts.method || 'GET').toUpperCase();

      if (isWriteMethod(method) && isApiPath(url) && !isAuthRoute(url)) {
        if (!global.navigator.onLine) {
          return enqueueWrite(method, url, opts.body).then(function () {
            return { ok: false, status: 0, queuedOffline: true,
              json: function () { return Promise.resolve({ code: OFFLINE, msg: 'queued offline', result: null }); },
              text: function () { return Promise.resolve(''); } };
          });
        }
        return original(url, opts).catch(function () {
          return enqueueWrite(method, url, opts.body).then(function () {
            return { ok: false, status: 0, queuedOffline: true,
              json: function () { return Promise.resolve({ code: OFFLINE, msg: 'queued offline', result: null }); },
              text: function () { return Promise.resolve(''); } };
          });
        });
      }
      return original(url, opts);
    };
  }

  /* ---------- UI ---------- */
  function updateBadgeCount() {
    countOps().then(function (n) {
      var el = global.document.getElementById('pwaPendingBadge');
      if (!el) { return; }
      if (n > 0) {
        el.textContent = String(n);
        el.classList.remove('hidden');
      } else {
        el.classList.add('hidden');
      }
    });
  }
  function markOfflineUI() {
    var el = global.document.getElementById('pwaOfflineIndicator');
    if (el) { el.style.display = 'flex'; }
  }
  function markOnlineUI() {
    var el = global.document.getElementById('pwaOfflineIndicator');
    if (el) { el.style.display = 'none'; }
  }

  /* ---------- 安装提示 ---------- */
  function setupInstallPrompt() {
    var deferredPrompt = null;
    global.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      deferredPrompt = e;
      var btn = global.document.getElementById('pwaInstallButton');
      if (btn) { btn.style.display = 'inline-flex'; }
    });
    global.addEventListener('appinstalled', function () {
      var btn = global.document.getElementById('pwaInstallButton');
      if (btn) { btn.style.display = 'none'; }
    });
    global.document.addEventListener('click', function (e) {
      var t = e.target && e.target.closest ? e.target.closest('#pwaInstallButton') : null;
      if (!t) { return; }
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function () {
          deferredPrompt = null;
          t.style.display = 'none';
        });
      }
    });
  }

  /* ---------- 更新提示 ---------- */
  function setupUpdate() {
    if (!('serviceWorker' in global.navigator)) { return; }
    global.navigator.serviceWorker.ready.then(function (reg) {
      reg.addEventListener('updatefound', function () {
        var newWorker = reg.installing;
        if (!newWorker) { return; }
        newWorker.addEventListener('statechange', function () {
          if (newWorker.state === 'installed' && global.navigator.serviceWorker.controller) {
            try {
              if (global.Swal) {
                global.Swal.fire({
                  icon: 'info',
                  title: '有新版本可用',
                  text: '点击刷新以应用最新功能。',
                  confirmButtonText: '立即刷新',
                  showCancelButton: true,
                  cancelButtonText: '稍后'
                }).then(function (res) {
                  if (res.isConfirmed) {
                    if (reg.waiting) { reg.waiting.postMessage({ type: 'PWA_SKIP_WAITING' }); }
                    global.location.reload();
                  }
                });
              }
            } catch (e) {}
          }
        });
      });
    });
  }

  /* ---------- SW 注册与消息 ---------- */
  function registerSw() {
    if (!('serviceWorker' in global.navigator)) { return Promise.resolve(); }
    return global.navigator.serviceWorker.register('/sw.js', { scope: '/' })
      .then(function () {
        global.navigator.serviceWorker.addEventListener('message', function (event) {
          var data = event.data || {};
          if (data.type === 'PWA_FLUSH_SYNC') {
            flushQueue();
          }
        });
      })
      .catch(function () {});
  }

  function setupOnlineEvents() {
    function onOnline() {
      markOnlineUI();
      flushQueue().then(function (r) {
        if (r && r.flushed > 0 && global.Swal) {
          global.Swal.fire({
            icon: 'success',
            title: '已恢复网络',
            text: '离线操作已同步：' + r.flushed + ' 条。',
            toast: true,
            position: 'top-end',
            timer: 2500,
            showConfirmButton: false
          });
        }
      });
    }
    global.addEventListener('online', onOnline);
    /* 兜底：每 20s 尝试重放（若在线且有队列） */
    global.setInterval(function () {
      if (global.navigator.onLine) { flushQueue(); }
    }, 20000);
    global.addEventListener('offline', function () { markOfflineUI(); });
  }

  function init() {
    return registerSw().then(function () {
      wrapTaskApiClient();
      wrapTaskApiFetch();
      setupInstallPrompt();
      setupUpdate();
      setupOnlineEvents();
      updateBadgeCount();
      if (!global.navigator.onLine) { markOfflineUI(); }
      /* SW ready 后立即尝试重放残留队列 */
      if ('serviceWorker' in global.navigator) {
        global.navigator.serviceWorker.ready.then(function () {
          if (global.navigator.onLine) { flushQueue(); }
        }).catch(function () {});
      }
    });
  }

  global.__PWA_OFFLINE__ = { init: init, flush: flushQueue, count: countOps };

  /* 自动启动：等 taskApiFetch / TaskApiClient 就绪（DOM 加载后） */
  function boot() {
    var start = function () {
      if (global.TaskApiClient || (typeof global.taskApiFetch === 'function')) {
        init();
      } else {
        global.setTimeout(start, 100);
      }
    };
    if (global.document && (global.document.readyState === 'complete' || global.document.readyState === 'interactive')) {
      start();
    } else if (global.document) {
      global.document.addEventListener('DOMContentLoaded', start);
    } else {
      start();
    }
  }
  if (global.document) { boot(); }
  else { boot(); }
})(window);
