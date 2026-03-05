@extends('layouts.app')

@section('title', '探索发现 - 蒙太奇')
@section('description', '发现和订阅优质RSS源，分类浏览推荐订阅，轻松管理您的阅读内容。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">订阅推荐</h2>
                        <p class="text-sm text-gray-500 mt-1">快速开始您的订阅之旅</p>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <div class="flex-1 md:flex-none"><input type="text" id="feedSearchInput" placeholder="搜索订阅源..." class="input w-full"></div>
                        <button type="button" id="feedSearchBtn" class="btn btn-primary"><i class="fas fa-search mr-2"></i>搜索</button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="/feeds" class="group"><div class="card hover:card-elevated transition-all duration-200 h-full"><div class="p-5 text-center"><div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-rss text-white text-lg"></i></div><h3 class="font-semibold text-gray-800">直接订阅</h3></div></div></a>
                    <a href="/feeds/weiborss" class="group"><div class="card hover:card-elevated transition-all duration-200 h-full"><div class="p-5 text-center"><div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fab fa-weibo text-white text-lg"></i></div><h3 class="font-semibold text-gray-800">订阅微博</h3></div></div></a>
                    <a href="/feeds/weixinrss" class="group"><div class="card hover:card-elevated transition-all duration-200 h-full"><div class="p-5 text-center"><div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fab fa-weixin text-white text-lg"></i></div><h3 class="font-semibold text-gray-800">订阅公众号</h3></div></div></a>
                    <a href="/feeds/opml" class="group"><div class="card hover:card-elevated transition-all duration-200 h-full"><div class="p-5 text-center"><div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-file-import text-white text-lg"></i></div><h3 class="font-semibold text-gray-800">OPML导入</h3></div></div></a>
                </div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">分类订阅</h2>
            </div>
            <div class="p-6">
                <div id="recommendCategoryList" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3"></div>
            </div>
        </div>

        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">推荐订阅源</h2>
                    <div class="text-sm text-gray-500" id="recommendFeedTotal">共 0 个订阅源</div>
                </div>
            </div>
            <div class="p-6">
                <div id="recommendFeedLoading" class="text-center py-8 text-gray-500">加载中...</div>
                <div id="recommendFeedList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden"></div>
                <div id="recommendFeedEmpty" class="hidden text-center py-12 text-gray-500">暂无推荐订阅源</div>
                <div id="recommendFeedPagination" class="mt-8 border-t border-gray-200 pt-6 hidden"></div>
            </div>
        </div>
    </div>

    <style>
        .line-clamp-2 { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    </style>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
            });
        }

        function showNotification(type, message) {
            var node = document.createElement('div');
            var bg = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            node.className = 'fixed top-4 right-4 z-50';
            node.innerHTML = '<div class="card ' + bg + ' text-white shadow-xl max-w-sm"><div class="p-4">' + escapeHtml(message) + '</div></div>';
            document.body.appendChild(node);
            setTimeout(function(){ if(node.parentNode) node.parentNode.removeChild(node); }, 3000);
        }

        function renderRecommendCategory(map) {
            var wrap = document.getElementById('recommendCategoryList');
            var html = '';
            Object.keys(map || {}).forEach(function(id) {
                html += '<a href="/feeds/search?recommend_category_id=' + id + '" class="group"><div class="card hover:bg-gray-50 transition-colors h-full"><div class="p-4 text-center"><div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fas fa-folder text-gray-500"></i></div><span class="text-sm font-medium text-gray-700 truncate block">' + escapeHtml(map[id]) + '</span></div></div></a>';
            });
            wrap.innerHTML = html;
        }

        function renderFeeds(feeds) {
            var wrap = document.getElementById('recommendFeedList');
            var html = '';
            feeds.forEach(function(feed) {
                var id = Number(feed.id || 0);
                var name = escapeHtml(feed.feed_name || '未命名订阅');
                var desc = escapeHtml(feed.feed_desc || '');
                var favicon = feed.favicon ? '<img src="' + escapeHtml(feed.favicon) + '" alt="" class="w-8 h-8 rounded-full flex-shrink-0 border border-gray-200">' : '<div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-rss text-white text-sm"></i></div>';
                html += ''
                    + '<div class="card hover:card-elevated transition-all duration-200 h-full flex flex-col"><div class="p-5 flex-grow">'
                    + '<div class="flex items-start gap-3 mb-3">' + favicon + '<div class="flex-grow min-w-0"><h3 class="font-semibold text-gray-800 truncate"><a href="/article/list?feed_id=' + id + '" class="hover:underline">' + name + '</a></h3></div></div>'
                    + (desc ? '<p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">' + desc + '</p>' : '')
                    + '</div><div class="px-5 pb-5 pt-0 flex items-center justify-between">'
                    + '<a href="/article/list?feed_id=' + id + '" class="text-sm text-gray-500 hover:text-gray-700"><i class="fas fa-newspaper mr-1"></i>浏览文章</a>'
                    + '<button type="button" data-feed-id="' + id + '" class="feed-quick-subscribe btn btn-sm btn-outline py-1 px-3"><i class="fas fa-plus mr-1"></i>订阅</button>'
                    + '</div></div>';
            });
            wrap.innerHTML = html;
        }

        function renderPagination(p) {
            var wrap = document.getElementById('recommendFeedPagination');
            if (!p || Number(p.last_page || 1) <= 1) {
                wrap.classList.add('hidden');
                wrap.innerHTML = '';
                return;
            }
            wrap.classList.remove('hidden');
            wrap.innerHTML = ''
                + '<div class="flex items-center justify-between">'
                + '<div class="text-sm text-gray-500">显示第 ' + Number(p.current_page || 1) + ' 页，共 ' + Number(p.last_page || 1) + ' 页</div>'
                + '<div class="flex gap-1">'
                + '<button id="explorerPrev" class="btn btn-secondary btn-sm" ' + (!p.prev_page_url ? 'disabled' : '') + '><i class="fas fa-chevron-left mr-1"></i>上一页</button>'
                + '<button id="explorerNext" class="btn btn-secondary btn-sm" ' + (!p.next_page_url ? 'disabled' : '') + '>下一页<i class="fas fa-chevron-right ml-1"></i></button>'
                + '</div></div>';

            document.getElementById('explorerPrev').onclick = function() {
                if (p.prev_page_url) loadExplorer(Math.max(1, Number(p.current_page || 1) - 1));
            };
            document.getElementById('explorerNext').onclick = function() {
                if (p.next_page_url) loadExplorer(Number(p.current_page || 1) + 1);
            };
        }

        function loadExplorer(page) {
            if (!apiRequest) {
                document.getElementById('recommendFeedLoading').textContent = 'API客户端未初始化';
                return;
            }
            document.getElementById('recommendFeedLoading').classList.remove('hidden');
            apiRequest('GET', '/feeds/explorer', { page: page || 1 }).then(function(resp) {
                document.getElementById('recommendFeedLoading').classList.add('hidden');
                if (!resp || resp.code !== 9999 || !resp.result) {
                    showNotification('error', (resp && resp.msg) || '加载失败');
                    return;
                }

                var result = resp.result;
                var feeds = Array.isArray(result.feeds) ? result.feeds : [];
                document.getElementById('recommendFeedTotal').textContent = '共 ' + Number((result.pagination && result.pagination.total) || feeds.length) + ' 个订阅源';
                renderRecommendCategory(result.recommend_categorys || {});

                if (!feeds.length) {
                    document.getElementById('recommendFeedEmpty').classList.remove('hidden');
                    document.getElementById('recommendFeedList').classList.add('hidden');
                } else {
                    renderFeeds(feeds);
                    document.getElementById('recommendFeedList').classList.remove('hidden');
                    document.getElementById('recommendFeedEmpty').classList.add('hidden');
                }
                renderPagination(result.pagination || null);
            }).catch(function() {
                document.getElementById('recommendFeedLoading').classList.add('hidden');
                showNotification('error', '加载失败，请稍后重试');
            });
        }

        function goFeedSearch() {
            var keyword = (document.getElementById('feedSearchInput').value || '').trim();
            if (!keyword) {
                showNotification('error', '请输入搜索关键词');
                return;
            }
            window.location.href = '/feeds/search?name=' + encodeURIComponent(keyword);
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.feed-quick-subscribe');
            if (!btn || !apiRequest) return;
            var feedId = btn.getAttribute('data-feed-id');
            if (!feedId) return;
            apiRequest('POST', '/feeds/quickstore', { feed_id: feedId }).then(function(resp) {
                if (resp && resp.code === 9999) {
                    btn.classList.remove('btn-outline');
                    btn.classList.add('btn-success');
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i>已订阅';
                    btn.disabled = true;
                    showNotification('success', '订阅成功');
                } else {
                    showNotification('error', (resp && resp.msg) || '订阅失败');
                }
            }).catch(function() {
                showNotification('error', '订阅失败，请稍后重试');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('feedSearchBtn').addEventListener('click', goFeedSearch);
            document.getElementById('feedSearchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    goFeedSearch();
                }
            });

            var qs = new URLSearchParams(window.location.search);
            if (qs.get('name')) {
                document.getElementById('feedSearchInput').value = qs.get('name');
            }
            loadExplorer(1);
        });
    </script>
@endsection
