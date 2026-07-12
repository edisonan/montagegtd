@extends('layouts.app')

@section('title', '探索阅读 - 蒙太奇')
@section('description', '按分类、订阅源和文章标题逐级探索阅读')

@section('content')
<style>
    .article-explorer {
        height: calc(100vh - 96px);
        min-height: 620px;
        margin: 16px auto;
        max-width: 1600px;
        padding: 0 16px;
    }
    .explorer-shell {
        position: relative;
        display: grid;
        grid-template-columns: minmax(220px, 280px) minmax(280px, 360px) minmax(0, 1fr);
        height: 100%;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
    }
    .explorer-shell.directory-collapsed {
        grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
    }
    .explorer-shell.directory-collapsed .directory-panel {
        display: none;
    }
    .directory-reopen {
        display: none;
        position: absolute;
        z-index: 5;
        top: 12px;
        left: 12px;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: rgba(255,255,255,.96);
        color: #64748b;
        cursor: pointer;
        font-size: 12px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
    }
    .directory-reopen:hover { border-color: #6366f1; color: #4338ca; }
    .explorer-shell.directory-collapsed .directory-reopen { display: inline-flex; }
    .explorer-panel {
        min-width: 0;
        overflow: hidden;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
    }
    .explorer-panel:last-child { border-right: 0; }
    .explorer-panel-header {
        flex: 0 0 auto;
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }
    .explorer-panel-title {
        margin: 0;
        color: #0f172a;
        font-size: 15px;
        font-weight: 700;
    }
    .explorer-panel-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .directory-toggle {
        flex: 0 0 auto;
        padding: 4px 7px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        font-size: 12px;
    }
    .directory-toggle:hover {
        border-color: #6366f1;
        color: #4338ca;
    }
    .explorer-panel-hint {
        margin-top: 4px;
        color: #94a3b8;
        font-size: 12px;
    }
    .explorer-status-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }
    .explorer-status-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 30px;
        padding: 0;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        font-size: 12px;
    }
    .explorer-status-btn .filter-label { display: none; }
    .explorer-status-btn:hover,
    .explorer-status-btn.active {
        border-color: #6366f1;
        background: #eef2ff;
        color: #4338ca;
    }
    .explorer-scroll {
        min-height: 0;
        overflow-y: auto;
    }
    .category-name {
        padding: 14px 16px 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
    }
    .feed-item, .article-item {
        width: 100%;
        border: 0;
        background: transparent;
        color: #334155;
        cursor: pointer;
        text-align: left;
        transition: background .15s, color .15s;
    }
    .feed-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px 10px 22px;
        font-size: 14px;
    }
    .feed-item:hover, .feed-item.active,
    .article-item:hover, .article-item.active {
        background: #eef2ff;
        color: #4338ca;
    }
    .feed-item-name {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .feed-count {
        min-width: 24px;
        padding: 1px 7px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #64748b;
        font-size: 11px;
        text-align: center;
    }
    .article-item {
        display: block;
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .article-item.read {
        color: #94a3b8;
    }
    .article-item.read .article-item-title {
        font-weight: 500;
    }
    .article-item-title {
        display: -webkit-box;
        overflow: hidden;
        color: inherit;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .article-item-meta {
        display: flex;
        gap: 8px;
        margin-top: 7px;
        color: #94a3b8;
        font-size: 11px;
    }
    .status-dot {
        width: 7px;
        height: 7px;
        margin-top: 4px;
        border-radius: 50%;
        background: #6366f1;
    }
    .status-dot.read { background: #cbd5e1; }
    .reader-wrap {
        min-height: 0;
        overflow-y: auto;
        padding: 0 clamp(22px, 5vw, 72px) 60px;
    }
    .reader-head {
        position: sticky;
        z-index: 2;
        top: 0;
        padding: 16px 0 12px;
        background: rgba(255,255,255,.96);
        border-bottom: 1px solid #f1f5f9;
        backdrop-filter: blur(8px);
    }
    .reader-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(19px, 2vw, 24px);
        font-weight: 750;
        line-height: 1.35;
    }
    .reader-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        margin-top: 12px;
        color: #64748b;
        font-size: 13px;
    }
    .reader-meta a { color: #4f46e5; text-decoration: none; }
    .reader-actions {
        display: inline-flex;
        gap: 6px;
        margin-left: auto;
    }
    .reader-action-btn {
        padding: 5px 9px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        font-size: 12px;
    }
    .reader-action-btn:hover,
    .reader-action-btn.active {
        border-color: #6366f1;
        background: #eef2ff;
        color: #4338ca;
    }
    .reader-content {
        padding-top: 28px;
        color: #334155;
        font-size: 16px;
        line-height: 1.85;
        overflow-wrap: anywhere;
    }
    .reader-content img { max-width: 100%; height: auto; border-radius: 8px; }
    .reader-content pre { overflow-x: auto; padding: 16px; border-radius: 8px; background: #0f172a; color: #e2e8f0; }
    .reader-content a { color: #4f46e5; }
    .explorer-state {
        padding: 48px 22px;
        color: #94a3b8;
        font-size: 14px;
        line-height: 1.7;
        text-align: center;
    }
    .load-more {
        display: block;
        margin: 14px auto;
        padding: 8px 18px;
        border: 1px solid #c7d2fe;
        border-radius: 8px;
        background: #fff;
        color: #4f46e5;
        cursor: pointer;
    }
    @media (max-width: 900px) {
        .article-explorer { height: auto; min-height: 0; margin: 8px auto; }
        .explorer-shell { grid-template-columns: 1fr; overflow: visible; }
        .explorer-shell.directory-collapsed { grid-template-columns: 1fr; }
        .explorer-panel { height: 360px; border-right: 0; border-bottom: 1px solid #e5e7eb; }
        .explorer-panel:last-child { height: auto; min-height: 520px; }
        .reader-wrap { overflow: visible; }
    }
</style>

<main class="article-explorer">
    <div class="explorer-shell">
        <button type="button" class="directory-reopen" id="directoryReopen" title="展开订阅目录">
            <i class="fas fa-chevron-right"></i><span>展开 Feed</span>
        </button>
        <section class="explorer-panel directory-panel" aria-label="分类和订阅源">
            <header class="explorer-panel-header">
                <div class="explorer-panel-header-row">
                    <h1 class="explorer-panel-title"><i class="fas fa-layer-group mr-2"></i>订阅目录</h1>
                    <button type="button" class="directory-toggle" id="directoryToggle" aria-expanded="true" title="折叠订阅目录">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
                <div class="explorer-panel-hint">选择一个 Feed</div>
                <div class="explorer-status-filter" role="group" aria-label="文章状态过滤">
                    <button type="button" class="explorer-status-btn" data-status="unread" title="未读" aria-label="未读"><i class="far fa-envelope"></i><span class="filter-label">未读</span></button>
                    <button type="button" class="explorer-status-btn" data-status="read" title="已读" aria-label="已读"><i class="far fa-envelope-open"></i><span class="filter-label">已读</span></button>
                    <button type="button" class="explorer-status-btn" data-status="star" title="收藏" aria-label="收藏"><i class="far fa-star"></i><span class="filter-label">收藏</span></button>
                    <button type="button" class="explorer-status-btn" data-status="read_later" title="稍后读" aria-label="稍后读"><i class="far fa-clock"></i><span class="filter-label">稍后读</span></button>
                </div>
            </header>
            <div id="feedTree" class="explorer-scroll">
                <div class="explorer-state">正在加载订阅目录…</div>
            </div>
        </section>

        <section class="explorer-panel" aria-label="文章标题">
            <header class="explorer-panel-header">
                <h2 id="articleListTitle" class="explorer-panel-title">文章标题</h2>
                <div id="articleListHint" class="explorer-panel-hint">点击左侧 Feed 后加载</div>
            </header>
            <div id="articleList" class="explorer-scroll">
                <div class="explorer-state">从订阅目录中选择一个 Feed</div>
            </div>
        </section>

        <section class="explorer-panel" aria-label="文章内容">
            <div id="reader" class="reader-wrap">
                <div class="explorer-state" style="padding-top: 150px">
                    <i class="far fa-newspaper text-4xl mb-4"></i><br>
                    选择一篇文章后，正文将在这里按需加载
                </div>
            </div>
        </section>
    </div>
</main>
@endsection

@section('scripts')
<script>
    (function ($) {
        'use strict';

        var state = {
            feedId: null,
            feedName: '',
            articleSubId: null,
            page: 1,
            hasMore: false,
            loadingArticles: false,
            articleRequestId: 0,
            loadedArticleCount: 0,
            status: new URLSearchParams(window.location.search).get('status') || 'unread',
            directoryCollapsed: localStorage.getItem('articleExplorerDirectoryCollapsed') === '1'
        };

        var statusLabels = {
            unread: '未读',
            read: '已读',
            star: '收藏',
            read_later: '稍后读'
        };
        if (!statusLabels[state.status]) {
            state.status = 'unread';
        }

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function unwrap(response) {
            if (!response || Number(response.code) !== 9999) {
                throw new Error(response && response.msg ? response.msg : '请求失败');
            }
            return response.result || {};
        }

        function toArray(value) {
            if (!value) {
                return [];
            }
            if ($.isArray(value)) {
                return value;
            }
            if (typeof value === 'object') {
                return $.map(value, function (item) {
                    return item;
                });
            }
            return [];
        }

        function request(url) {
            return $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(unwrap);
        }

        function showError($target, message) {
            $target.html('<div class="explorer-state">' + escapeHtml(message || '加载失败，请稍后重试') + '</div>');
        }

        function syncDirectoryState() {
            $('.explorer-shell').toggleClass('directory-collapsed', state.directoryCollapsed);
            $('#directoryToggle')
                .attr('aria-expanded', state.directoryCollapsed ? 'false' : 'true')
                .attr('title', state.directoryCollapsed ? '展开订阅目录' : '折叠订阅目录')
                .html(state.directoryCollapsed
                    ? '<i class="fas fa-chevron-right"></i>'
                    : '<i class="fas fa-chevron-left"></i>');
        }

        function loadFeeds() {
            request('/articles/explorer/data/feeds?status=' + encodeURIComponent(state.status)).done(function (result) {
                var groups = toArray(result.nav_infos || result);
                if (!groups.length) {
                    showError($('#feedTree'), '还没有订阅 Feed');
                    return;
                }

                var html = '';
                groups.forEach(function (group) {
                    var category = group.category_info || {};
                    html += '<div class="category-group">';
                    html += '<div class="category-name">' + escapeHtml(category.category_name || '未分类') + '</div>';
                    toArray(group.list || group.feeds).forEach(function (feed) {
                        var feedId = feed.feed_id || feed.id;
                        var feedName = feed.feed_name || feed.name || '';
                        html += '<button type="button" class="feed-item" data-feed-id="' + Number(feedId) + '" data-feed-name="' + escapeHtml(feedName) + '">';
                        html += '<i class="fas fa-rss text-xs text-orange-400"></i>';
                        html += '<span class="feed-item-name">' + escapeHtml(feedName) + '</span>';
                        html += '<span class="feed-count" title="未读文章">' + Number(feed.feed_count || 0) + '</span>';
                        html += '</button>';
                    });
                    html += '</div>';
                });
                $('#feedTree').html(html);
            }).fail(function () {
                showError($('#feedTree'), '订阅目录加载失败');
            });
        }

        function loadArticles(feedId, feedName, page, append) {
            if (state.loadingArticles && String(state.feedId) === String(feedId)) {
                return;
            }
            var requestId = ++state.articleRequestId;
            state.loadingArticles = true;
            state.feedId = feedId;
            state.feedName = feedName;
            state.page = page;
            if (!append) {
                state.loadedArticleCount = 0;
            }
            $('#articleListTitle').text(feedName);
            $('#articleListHint').text('正在加载文章标题…');
            if (!append) {
                $('#articleList').html('<div class="explorer-state">正在加载文章标题…</div>');
                $('#reader').html('<div class="explorer-state" style="padding-top:150px">选择文章标题后加载正文</div>');
            }

            request('/articles/explorer/data/feeds/' + encodeURIComponent(feedId) + '/articles?page=' + page + '&page_count=40&status=' + encodeURIComponent(state.status))
                .done(function (result) {
                    if (requestId !== state.articleRequestId || String(state.feedId) !== String(feedId)) {
                        return;
                    }
                    var articles = result.articles || [];
                    var html = '';
                    articles.forEach(function (item) {
                        var published = item.published ? String(item.published).slice(0, 10) : '';
                        html += '<button type="button" class="article-item ' + (item.status === 'read' ? 'read' : '') + '" data-article-sub-id="' + Number(item.id) + '">';
                        html += '<span class="article-item-title">' + escapeHtml(item.subject || '无标题文章') + '</span>';
                        html += '<span class="article-item-meta"><span class="status-dot ' + (item.status === 'read' ? 'read' : '') + '"></span>';
                        html += '<span>' + escapeHtml(published) + '</span><span>' + escapeHtml(statusLabels[item.status] || item.status) + '</span></span>';
                        html += '</button>';
                    });

                    state.hasMore = !!(result.pagination && result.pagination.has_more_pages);
                    state.loadedArticleCount += articles.length;
                    if (state.hasMore) {
                        html += '<button type="button" class="load-more">加载更多</button>';
                    }
                    if (!append) {
                        $('#articleList').html(html || '<div class="explorer-state">这个 Feed 暂无文章</div>');
                    } else {
                        $('#articleList .load-more').remove();
                        $('#articleList').append(html);
                    }
                    $('#articleListHint').text('已加载 ' + state.loadedArticleCount + ' 篇 · 正文尚未加载');
                })
                .fail(function () {
                    if (requestId !== state.articleRequestId) {
                        return;
                    }
                    if (!append) {
                        showError($('#articleList'), '文章标题加载失败');
                    }
                    $('#articleListHint').text('加载失败');
                })
                .always(function () {
                    if (requestId === state.articleRequestId) {
                        state.loadingArticles = false;
                    }
                });
        }

        function loadArticle(articleSubId) {
            state.articleSubId = articleSubId;
            $('#reader').html('<div class="explorer-state" style="padding-top:150px">正在加载正文…</div>');
            request('/articles/explorer/data/articles/' + encodeURIComponent(articleSubId))
                .done(function (result) {
                    if (String(state.articleSubId) !== String(articleSubId)) {
                        return;
                    }
                    var article = result.article || {};
                    var html = '<header class="reader-head">';
                    html += '<h1 class="reader-title">' + escapeHtml(article.subject || '无标题文章') + '</h1>';
                    html += '<div class="reader-meta"><span><i class="fas fa-rss mr-1"></i>' + escapeHtml(article.feed_name || state.feedName) + '</span>';
                    if (article.published) {
                        html += '<span><i class="far fa-clock mr-1"></i>' + escapeHtml(String(article.published)) + '</span>';
                    }
                    html += '<span><i class="fas fa-align-left mr-1"></i>' + escapeHtml(result.word_count || 0) + ' 字 · 约 ' + escapeHtml(result.estimated_read_minutes || 1) + ' 分钟</span>';
                    if (article.url) {
                        html += '<a href="' + escapeHtml(article.url) + '" target="_blank" rel="noopener noreferrer">查看原文 <i class="fas fa-external-link-alt ml-1"></i></a>';
                    }
                    html += '<span class="reader-actions">';
                    html += '<button type="button" class="reader-action-btn" data-article-status="read"><i class="fas fa-check mr-1"></i>已读</button>';
                    html += '<button type="button" class="reader-action-btn" data-article-status="star"><i class="far fa-star mr-1"></i>收藏</button>';
                    html += '<button type="button" class="reader-action-btn" data-article-status="read_later"><i class="far fa-clock mr-1"></i>稍后阅读</button>';
                    html += '</span>';
                    html += '</div></header>';
                    html += '<article class="reader-content">' + (article.content || '<p>暂无正文内容</p>') + '</article>';
                    $('#reader').html(html).scrollTop(0);
                    syncArticleActions(result.status);
                    if (result.status === 'unread') {
                        updateArticleStatus('read', true);
                    }
                })
                .fail(function () {
                    showError($('#reader'), '正文加载失败');
                });
        }

        function syncArticleActions(status) {
            $('#reader [data-article-status]').each(function () {
                var actionStatus = $(this).data('article-status');
                $(this).toggleClass('active', actionStatus === status);
            });
        }

        function updateArticleStatus(status, silent) {
            if (!state.articleSubId) {
                return;
            }
            request('/articles/status/' + encodeURIComponent(state.articleSubId) + '?status=' + encodeURIComponent(status))
                .done(function () {
                    syncArticleActions(status);
                    var $item = $('#articleList .article-item[data-article-sub-id="' + Number(state.articleSubId) + '"]');
                    $item.find('.article-item-meta span:last-child').text(statusLabels[status] || status);
                    $item.toggleClass('read', status === 'read');
                    $item.find('.status-dot').toggleClass('read', status === 'read');
                    if (!silent && status !== state.status) {
                        $item.remove();
                    }
                    if (!silent) {
                        $('#articleListHint').text('文章状态已更新');
                    }
                })
                .fail(function () {
                    if (!silent) {
                        $('#articleListHint').text('状态更新失败，请稍后重试');
                    }
                });
        }

        $('#feedTree').on('click', '.feed-item', function () {
            $('.feed-item').removeClass('active');
            $(this).addClass('active');
            state.articleSubId = null;
            loadArticles($(this).data('feed-id'), $(this).data('feed-name'), 1, false);
        });

        $('#articleList').on('click', '.article-item', function () {
            $('.article-item').removeClass('active');
            $(this).addClass('active');
            loadArticle($(this).data('article-sub-id'));
        });

        $('#articleList').on('click', '.load-more', function () {
            if (state.feedId && state.hasMore) {
                loadArticles(state.feedId, state.feedName, state.page + 1, true);
            }
        });

        $('#reader').on('click', '[data-article-status]', function () {
            updateArticleStatus($(this).data('article-status'), false);
        });

        $('#directoryToggle').on('click', function () {
            state.directoryCollapsed = !state.directoryCollapsed;
            localStorage.setItem('articleExplorerDirectoryCollapsed', state.directoryCollapsed ? '1' : '0');
            syncDirectoryState();
        });

        $('#directoryReopen').on('click', function () {
            state.directoryCollapsed = false;
            localStorage.setItem('articleExplorerDirectoryCollapsed', '0');
            syncDirectoryState();
        });

        $('.explorer-status-filter').on('click', '.explorer-status-btn', function () {
            var status = $(this).data('status');
            if (status === state.status) {
                return;
            }
            state.status = status;
            state.feedId = null;
            state.articleSubId = null;
            $('.explorer-status-btn').removeClass('active');
            $(this).addClass('active');
            history.replaceState(null, '', window.location.pathname + '?status=' + encodeURIComponent(status));
            $('#feedTree').html('<div class="explorer-state">正在加载订阅目录…</div>');
            $('#articleListTitle').text('文章标题');
            $('#articleListHint').text('点击左侧 Feed 后加载');
            $('#articleList').html('<div class="explorer-state">从订阅目录中选择一个 Feed</div>');
            $('#reader').html('<div class="explorer-state" style="padding-top:150px">选择一篇文章后，正文将在这里按需加载</div>');
            loadFeeds();
        });

        $('.explorer-status-btn[data-status="' + state.status + '"]').addClass('active');
        syncDirectoryState();

        loadFeeds();
    })(jQuery);
</script>
@endsection
