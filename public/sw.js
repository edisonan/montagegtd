/* ============================================================
   蒙太奇 (MontageGTD) - PWA Service Worker
   ------------------------------------------------------------
   策略：
   - STATIC 静态资源（css/js/img/fonts/vendor_local/pwa）：cache-first
   - PAGES  页面导航（HTML）：network-first，仅缓存 & 回退「合法的 HTML 200 页面」，
            离线时绝不会回退到重定向/错误/错误会话页面，避免白屏。
   - API    GET 数据（/api/v2/...）：stale-while-revalidate（离线回退缓存供阅读）
   - 写操作（POST/PUT/DELETE）：不缓存，交由客户端离线队列 + Background Sync
   - 离线兜底：offline.html
   ============================================================ */
'use strict';

var VERSION = 'pwa-v1.2.0';
var STATIC_CACHE = 'montage-static-' + VERSION;
var PAGES_CACHE = 'montage-pages-' + VERSION;
var API_CACHE = 'montage-api-' + VERSION;

/* 预缓存的静态资源（应用外壳）。注意：不预缓存 '/' —— 避免把可能是
   重定向/特定会话/未登录的根页面缓存住并在离线时错误回退导致白屏。 */
var PRECACHE_URLS = [
  '/offline.html',
  '/css/app.css',
  '/js/app.js',
  '/js/jquery-3.6.0.min.js',
  '/js/sweetalert2.all.min.js',
  '/js/hybrid-api-client.js',
  '/vendor_local/tailwind-play.js',
  '/vendor_local/fa/all.min.css',
  '/vendor_local/webfonts/fa-brands-400.woff2',
  '/vendor_local/webfonts/fa-solid-900.woff2',
  '/vendor_local/webfonts/fa-regular-400.woff2',
  '/vendor_local/webfonts/fa-v4compatibility.woff2',
  '/vendor_local/echarts/echarts.min.js',
  '/vendor_local/fonts/inter.css',
  '/manifest.json',
  '/favicon.ico'
];

/* 安装：逐条预缓存，任一失败不阻断整体安装 */
self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(function (cache) {
        return Promise.all(PRECACHE_URLS.map(function (url) {
          return fetch(url, { credentials: 'same-origin' })
            .then(function (resp) {
              if (resp && (resp.status === 200 || resp.status === 0)) {
                return cache.put(url, resp);
              }
            })
            .catch(function () {});
        }));
      })
      .then(function () {
        if (self.registration && self.registration.navigationPreload) {
          self.registration.navigationPreload.enable().catch(function () {});
        }
        return self.skipWaiting();
      })
  );
});

/* 激活：清理旧版本缓存 */
self.addEventListener('activate', function (event) {
  var expected = [STATIC_CACHE, PAGES_CACHE, API_CACHE];
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys
          .filter(function (key) {
            return key.indexOf('montage-') === 0 && expected.indexOf(key) === -1;
          })
          .map(function (key) { return caches.delete(key); })
      );
    }).then(function () {
      return self.clients.claim();
    })
  );
});

function isNavigation(req) {
  return req.mode === 'navigate' ||
    (req.method === 'GET' && req.headers.get('accept') && req.headers.get('accept').indexOf('text/html') !== -1);
}
function isApiGet(req) {
  return req.method === 'GET' && /^\/api\/v2\//.test(new URL(req.url).pathname);
}
function isLocalStatic(url) {
  var p = url.pathname;
  return /^\/(css|js|img|fonts|vendor_local|pwa|uploads|woff|themes|open-icon)\//.test(p) ||
    /\.(css|js|png|jpe?g|gif|svg|webp|woff2?|ttf|eot|ico|json)$/i.test(p);
}
function isSameOrigin(url) {
  return url.origin === self.location.origin;
}
/* 仅当响应是真实 HTML 文档（200 且 Content-Type 为 text/html）才视为“可信页面缓存” */
function isHtmlDocument(resp) {
  if (!resp || resp.status !== 200) { return false; }
  var ct = resp.headers.get('content-type') || '';
  return ct.indexOf('text/html') !== -1;
}

self.addEventListener('fetch', function (event) {
  var req = event.request;
  var url = new URL(req.url);

  if (req.method !== 'GET') { return; }            /* 写操作交给客户端离线队列 */
  if (!isSameOrigin(url)) { return; }

  /* ---------- 页面导航：network-first，仅回退可信 HTML 缓存 ---------- */
  if (isNavigation(req)) {
    event.respondWith(
      fetch(req)
        .then(function (resp) {
          if (resp && resp.status === 200) {
            /* 只缓存真实 HTML 文档 */
            var ct = resp.headers.get('content-type') || '';
            if (ct.indexOf('text/html') !== -1) {
              var clone = resp.clone();
              caches.open(PAGES_CACHE).then(function (cache) {
                cache.put(req, clone);
              });
            }
          }
          return resp;
        })
        .catch(function () {
          /* 离线：优先回退可信页面缓存，否则显示离线页 */
          return caches.match(req, { ignoreSearch: true }).then(function (cached) {
            if (cached && isHtmlDocument(cached)) { return cached; }
            return caches.match('/offline.html');
          });
        })
    );
    return;
  }

  /* ---------- API GET：stale-while-revalidate ---------- */
  if (isApiGet(req)) {
    event.respondWith(
      fetch(req)
        .then(function (resp) {
          if (resp && resp.status === 200) {
            var clone = resp.clone();
            caches.open(API_CACHE).then(function (cache) { cache.put(req, clone); });
          }
          return resp;
        })
        .catch(function () {
          return caches.match(req);
        })
    );
    return;
  }

  /* ---------- 本地静态资源：cache-first ---------- */
  if (isLocalStatic(url)) {
    event.respondWith(
      caches.match(req).then(function (cached) {
        if (cached) { return cached; }
        return fetch(req).then(function (resp) {
          if (resp && resp.status === 200) {
            var clone = resp.clone();
            caches.open(STATIC_CACHE).then(function (cache) { cache.put(req, clone); });
          }
          return resp;
        });
      })
    );
    return;
  }

  /* 其余：放行 */
});

/* 后台同步：通知客户端重放离线写队列 */
self.addEventListener('sync', function (event) {
  if (event.tag === 'montage-pwa-sync') {
    event.waitUntil(
      self.clients.matchAll().then(function (clients) {
        clients.forEach(function (client) {
          client.postMessage({ type: 'PWA_FLUSH_SYNC' });
        });
      })
    );
  }
});

self.addEventListener('message', function (event) {
  var data = event.data || {};
  if (data.type === 'PWA_SKIP_WAITING') {
    self.skipWaiting();
  }
});
