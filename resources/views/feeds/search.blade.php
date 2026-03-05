@extends('layouts.app')

@section('title', '发现订阅源 - 蒙太奇')
@section('description', '探索和发现新的RSS订阅源，丰富您的阅读列表')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">发现订阅源</h1>
                <p class="text-gray-600 mt-2">按关键词或推荐分类搜索可订阅源</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('articles') }}" class="btn btn-secondary btn-sm"><i class="fas fa-newspaper mr-2"></i>返回阅读</a>
                <a href="{{ url('feeds/explorer') }}" class="btn btn-outline btn-sm"><i class="fas fa-compass mr-2"></i>发现更多</a>
            </div>
        </div>

        <div class="card mb-6">
            <div class="p-5">
                <form id="feedSearchForm" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input id="searchName" type="text" class="input md:col-span-2" placeholder="输入订阅名称关键词">
                    <input id="searchCategory" type="number" class="input" placeholder="推荐分类ID（可选）" min="1">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search mr-2"></i>搜索</button>
                </form>
            </div>
        </div>

        <div id="searchResult" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        <div id="searchEmpty" class="hidden card p-12 text-center text-gray-500">未找到订阅源</div>
        <div id="searchLoading" class="hidden card p-8 text-center text-gray-500">加载中...</div>
        <div id="searchPagination" class="mt-6"></div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
            });
        }

        function showToast(message, type) {
            var node = document.createElement('div');
            node.className = 'fixed top-4 right-4 z-50 px-5 py-3 rounded-lg text-white ' + (type === 'error' ? 'bg-red-500' : 'bg-green-500');
            node.textContent = message;
            document.body.appendChild(node);
            setTimeout(function(){ if (node.parentNode) node.parentNode.removeChild(node); }, 2500);
        }

        function renderFeeds(items) {
            var wrap = document.getElementById('searchResult');
            var html = '';
            items.forEach(function(feed) {
                var id = Number(feed.id || 0);
                var name = escapeHtml(feed.feed_name || '未命名');
                var desc = escapeHtml(feed.feed_desc || '暂无描述');
                var date = feed.updated_at ? String(feed.updated_at).slice(0, 10) : '未知';
                html += ''
                    + '<div class="card"><div class="p-5">'
                    + '<h3 class="font-semibold text-gray-900 mb-2 truncate">' + name + '</h3>'
                    + '<p class="text-sm text-gray-600 line-clamp-2 mb-4">' + desc + '</p>'
                    + '<div class="text-xs text-gray-500 mb-4">更新：' + date + '</div>'
                    + '<div class="flex items-center justify-between">'
                    + '<a href="/article/list?feed_id=' + id + '" class="btn btn-outline btn-sm"><i class="fas fa-eye mr-2"></i>查看文章</a>'
                    + '<button type="button" class="btn btn-primary btn-sm quick-sub" data-feed-id="' + id + '"><i class="fas fa-plus mr-2"></i>一键订阅</button>'
                    + '</div></div></div>';
            });
            wrap.innerHTML = html;
        }

        function renderPagination(p) {
            var wrap = document.getElementById('searchPagination');
            if (!p || Number(p.last_page || 1) <= 1) {
                wrap.innerHTML = '';
                return;
            }
            wrap.innerHTML = ''
                + '<div class="flex items-center justify-between card p-4">'
                + '<div class="text-sm text-gray-600">共 ' + Number(p.total || 0) + ' 条</div>'
                + '<div class="flex gap-2">'
                + '<button id="searchPrev" class="btn btn-secondary" ' + (!p.prev_page_url ? 'disabled' : '') + '>上一页</button>'
                + '<span class="text-sm text-gray-600 px-2 py-2">第 ' + Number(p.current_page || 1) + '/' + Number(p.last_page || 1) + ' 页</span>'
                + '<button id="searchNext" class="btn btn-secondary" ' + (!p.next_page_url ? 'disabled' : '') + '>下一页</button>'
                + '</div></div>';

            document.getElementById('searchPrev').onclick = function() {
                if (p.prev_page_url) submitSearch(Math.max(1, Number(p.current_page || 1) - 1));
            };
            document.getElementById('searchNext').onclick = function() {
                if (p.next_page_url) submitSearch(Number(p.current_page || 1) + 1);
            };
        }

        function submitSearch(page) {
            var name = (document.getElementById('searchName').value || '').trim();
            var recommendCategoryId = (document.getElementById('searchCategory').value || '').trim();
            if (!name && !recommendCategoryId) {
                showToast('请输入关键词或推荐分类ID', 'error');
                return;
            }
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            document.getElementById('searchLoading').classList.remove('hidden');
            document.getElementById('searchEmpty').classList.add('hidden');
            document.getElementById('searchResult').innerHTML = '';

            var params = { page: page || 1 };
            if (name) params.name = name;
            if (recommendCategoryId) params.recommend_category_id = recommendCategoryId;

            apiRequest('GET', '/feeds/search', params).then(function(resp) {
                document.getElementById('searchLoading').classList.add('hidden');
                if (!resp || resp.code !== 9999) {
                    showToast((resp && resp.msg) || '搜索失败', 'error');
                    return;
                }
                var result = resp.result || {};
                var feeds = Array.isArray(result.feeds) ? result.feeds : [];
                if (!feeds.length) {
                    document.getElementById('searchEmpty').classList.remove('hidden');
                    renderPagination(result.pagination || null);
                    return;
                }
                renderFeeds(feeds);
                renderPagination(result.pagination || null);
            }).catch(function() {
                document.getElementById('searchLoading').classList.add('hidden');
                showToast('搜索失败，请稍后重试', 'error');
            });
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.quick-sub');
            if (!btn || !apiRequest) return;
            var feedId = btn.getAttribute('data-feed-id');
            apiRequest('POST', '/feeds/quickstore', { feed_id: feedId }).then(function(resp) {
                if (resp && resp.code === 9999) {
                    showToast('订阅成功', 'success');
                } else {
                    showToast((resp && resp.msg) || '订阅失败', 'error');
                }
            }).catch(function() { showToast('订阅失败', 'error'); });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('feedSearchForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitSearch(1);
            });

            var qs = new URLSearchParams(window.location.search);
            if (qs.get('name')) document.getElementById('searchName').value = qs.get('name');
            if (qs.get('recommend_category_id')) document.getElementById('searchCategory').value = qs.get('recommend_category_id');
            if (qs.get('name') || qs.get('recommend_category_id')) submitSearch(1);
        });
    </script>
@endsection
