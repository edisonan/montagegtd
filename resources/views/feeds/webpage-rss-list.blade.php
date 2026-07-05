@extends('layouts.app')

@section('title', '网页转RSS管理 - 蒙太奇')
@section('description', '管理网页转RSS配置、复制RSS地址并添加到订阅')

@section('content')
    <div class="max-w-7xl mx-auto" id="webpageRssListApp">
        <style>
            .rss-list-panel { background: #fff; border: 1px solid #dbe2ea; border-radius: 12px; }
            .rss-source-card { border: 1px solid #dbe2ea; border-radius: 10px; background: #fff; padding: 14px; }
            .rss-source-card:hover { border-color: #93c5fd; background: #f8fafc; }
            .rss-action { border: 1px solid #dbe2ea; background: #fff; color: #475569; padding: 7px 10px; border-radius: 8px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; }
            .rss-action.primary { background: #eff6ff; color: #1d4ed8; border-color: #93c5fd; }
            .rss-action.danger { color: #b91c1c; border-color: #fecaca; }
        </style>

        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">网页转RSS管理</h1>
                        <p class="text-gray-600 mt-1">查看已保存配置，复制RSS地址，或加入订阅列表。</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('feeds') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-2"></i>返回订阅
                </a>
                <a href="{{ url('feeds/webpage-rss/create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-2"></i>新建网页转RSS
                </a>
            </div>
        </div>

        <div id="webpageRssListTips" class="hidden mb-5 rounded-lg border p-4 text-sm"></div>

        <div class="rss-list-panel">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">已保存配置</div>
                    <div class="text-xs text-gray-500 mt-1">删除只停用网页转RSS配置和对应订阅，不删除已抓取文章。</div>
                </div>
                <button type="button" id="reloadSourcesBtn" class="rss-action">
                    <i class="fas fa-rotate"></i>刷新
                </button>
            </div>
            <div class="p-4">
                <div id="sourceList" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    <div class="text-sm text-gray-500">正在读取配置...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var tips = document.getElementById('webpageRssListTips');
            var sources = [];

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function(ch) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[ch];
                });
            }

            function showTips(type, message) {
                tips.className = 'mb-5 rounded-lg border p-4 text-sm ' + (type === 'error'
                    ? 'border-red-200 bg-red-50 text-red-700'
                    : 'border-green-200 bg-green-50 text-green-700');
                tips.textContent = message;
                tips.classList.remove('hidden');
            }

            function formatDate(value) {
                if (!value) {
                    return '-';
                }
                return String(value).replace('T', ' ').slice(0, 16);
            }

            function request(url, data, method) {
                return fetch(url, {
                    method: method || 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data || {})
                }).then(function(resp) {
                    return resp.json();
                }).then(function(json) {
                    if (!json || Number(json.code) !== 9999) {
                        throw new Error(json && json.msg ? json.msg : '请求失败');
                    }
                    return json.result || {};
                });
            }

            function renderSources() {
                var list = document.getElementById('sourceList');
                if (!sources.length) {
                    list.innerHTML = '<div class="md:col-span-2 xl:col-span-3 rounded-lg border border-gray-200 p-6 text-center text-sm text-gray-500">还没有保存过网页转RSS配置。</div>';
                    return;
                }
                list.innerHTML = sources.map(function(source) {
                    var id = Number(source.id || 0);
                    var rssUrl = escapeHtml(source.rss_url || '');
                    var subscribed = Number(source.is_subscribed || 0) === 1;
                    return '<div class="rss-source-card">'
                        + '<div class="flex items-start justify-between gap-3">'
                        + '<div class="min-w-0">'
                        + '<div class="font-semibold text-gray-900 truncate">' + escapeHtml(source.name || '未命名配置') + '</div>'
                        + '<div class="text-xs text-gray-500 mt-1 truncate">' + escapeHtml(source.list_url || '') + '</div>'
                        + '</div>'
                        + '<span class="text-xs px-2 py-1 rounded-full ' + (subscribed ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600') + ' whitespace-nowrap">' + (subscribed ? '已订阅' : '未订阅') + '</span>'
                        + '</div>'
                        + '<div class="grid grid-cols-2 gap-2 mt-3 text-xs text-gray-500">'
                        + '<div>频率：' + Number(source.refresh_interval || 0) + '分钟</div>'
                        + '<div>检查：' + escapeHtml(formatDate(source.last_checked_at)) + '</div>'
                        + '</div>'
                        + '<div class="mt-3 flex flex-wrap gap-2">'
                        + '<a class="rss-action primary" href="' + escapeHtml(source.edit_url || ('/feeds/webpage-rss/' + id + '/edit')) + '"><i class="fas fa-pen"></i>编辑</a>'
                        + '<button type="button" class="rss-action" data-source-copy="' + id + '" data-rss-url="' + rssUrl + '"><i class="fas fa-copy"></i>复制RSS</button>'
                        + '<button type="button" class="rss-action" data-source-subscribe="' + id + '"><i class="fas fa-plus"></i>' + (subscribed ? '重新加入' : '加入订阅') + '</button>'
                        + '<button type="button" class="rss-action" data-source-refresh="' + id + '"><i class="fas fa-rotate"></i>刷新</button>'
                        + '<button type="button" class="rss-action danger" data-source-delete="' + id + '"><i class="fas fa-trash"></i>删除</button>'
                        + '</div>'
                        + '</div>';
                }).join('');
            }

            function loadSources() {
                fetch('/feeds/webpage-rss/sources', {
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json'}
                }).then(function(resp) {
                    return resp.json();
                }).then(function(json) {
                    if (!json || Number(json.code) !== 9999) {
                        throw new Error(json && json.msg ? json.msg : '配置读取失败');
                    }
                    sources = Array.isArray(json.result) ? json.result : [];
                    renderSources();
                }).catch(function(error) {
                    document.getElementById('sourceList').innerHTML = '<div class="md:col-span-2 xl:col-span-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">' + escapeHtml(error.message || '配置读取失败') + '</div>';
                });
            }

            document.getElementById('reloadSourcesBtn').addEventListener('click', loadSources);
            document.getElementById('sourceList').addEventListener('click', function(event) {
                var target = event.target.closest('[data-source-copy], [data-source-subscribe], [data-source-refresh], [data-source-delete]');
                if (!target) {
                    return;
                }
                var copyId = target.getAttribute('data-source-copy');
                var subscribeId = target.getAttribute('data-source-subscribe');
                var refreshId = target.getAttribute('data-source-refresh');
                var deleteId = target.getAttribute('data-source-delete');
                if (copyId) {
                    var rssUrl = target.getAttribute('data-rss-url') || '';
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(rssUrl);
                    } else {
                        var input = document.createElement('input');
                        input.value = rssUrl;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                    }
                    showTips('success', 'RSS地址已复制。');
                } else if (subscribeId) {
                    request('/feeds/webpage-rss/sources/' + Number(subscribeId) + '/subscribe', {}).then(function() {
                        showTips('success', '已加入订阅。');
                        loadSources();
                    }).catch(function(error) {
                        showTips('error', error.message);
                    });
                } else if (refreshId) {
                    request('/feeds/webpage-rss/' + Number(refreshId) + '/refresh', {limit: 20}).then(function() {
                        showTips('success', '刷新完成。');
                        loadSources();
                    }).catch(function(error) {
                        showTips('error', error.message);
                    });
                } else if (deleteId) {
                    if (!window.confirm('确定删除这个网页转RSS配置？')) {
                        return;
                    }
                    request('/feeds/webpage-rss/sources/' + Number(deleteId), {}, 'DELETE').then(function() {
                        showTips('success', '配置已删除。');
                        loadSources();
                    }).catch(function(error) {
                        showTips('error', error.message);
                    });
                }
            });

            loadSources();
        })();
    </script>
@endsection
