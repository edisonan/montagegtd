@extends('layouts.app')

@section('title', '探索阅读 - 蒙太奇')
@section('description', '按分类、订阅源和文章标题逐级探索阅读')

@section('content')
<style>
    .article-explorer {
        height: calc(100vh - 96px);
        min-height: 720px;
        margin: 16px auto;
        max-width: 1600px;
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .explorer-filter-card {
        flex: 0 0 auto;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .04);
    }
    .explorer-filters {
        display: grid;
        grid-template-columns: repeat(6, minmax(120px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .explorer-filter-field { min-width: 0; }
    .explorer-filter-field-wide { grid-column: span 2; }
    .explorer-filter-label {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 11px;
        font-weight: 650;
    }
    .explorer-filter-control {
        width: 100%;
        min-height: 34px;
        padding: 6px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #334155;
        font-size: 12px;
    }
    .explorer-filter-stack { display: flex; flex-direction: column; gap: 5px; }
    .explorer-custom-dates { display: none; grid-column: span 2; gap: 8px; }
    .explorer-custom-dates.active { display: flex; }
    .explorer-filter-actions { display: flex; gap: 7px; }
    .explorer-btn {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #475569;
        cursor: pointer;
        font-size: 12px;
        white-space: nowrap;
    }
    .explorer-btn.primary { border-color: #4f46e5; background: #4f46e5; color: #fff; }
    .explorer-btn:disabled { cursor: not-allowed; opacity: .55; }
    .explorer-btn:hover:not(:disabled) { filter: brightness(.97); }
    .explorer-ai-btn { min-height: 30px; padding: 5px 9px; }
    .explorer-shell {
        position: relative;
        display: grid;
        grid-template-columns: minmax(220px, 280px) minmax(280px, 360px) minmax(0, 1fr);
        min-height: 0;
        flex: 1 1 auto;
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
        justify-content: center;
        width: 34px;
        height: 34px;
        padding: 0;
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
    .explorer-shell.directory-collapsed .directory-panel + .explorer-panel .explorer-panel-header {
        padding-left: 60px;
    }
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
    .explorer-modal {
        position: fixed;
        z-index: 100;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .45);
    }
    .explorer-modal.active { display: flex; }
    .explorer-modal-panel {
        display: flex;
        flex-direction: column;
        width: min(1050px, 100%);
        max-height: min(850px, 92vh);
        overflow: hidden;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 24px 80px rgba(15, 23, 42, .25);
    }
    .explorer-modal-head {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }
    .explorer-modal-title { margin: 0; color: #0f172a; font-size: 19px; font-weight: 750; }
    .explorer-modal-meta { margin-top: 6px; color: #64748b; font-size: 12px; }
    .explorer-modal-close { border: 0; background: transparent; color: #64748b; cursor: pointer; font-size: 18px; }
    .explorer-modal-body { overflow-y: auto; padding: 24px clamp(20px, 5vw, 70px) 42px; color: #334155; line-height: 1.85; }
    .explorer-ai-prompt { width: 100%; min-height: 110px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; resize: vertical; color: #334155; font-size: 13px; line-height: 1.6; }
    .explorer-ai-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 12px 0 14px; }
    .explorer-ai-hint { color: #64748b; font-size: 12px; }
    .explorer-ai-content h2, .explorer-ai-content h3, .explorer-ai-content h4 { color: #0f172a; }
    .explorer-ai-content img { max-width: 100%; height: auto; }
    @media (max-width: 900px) {
        .article-explorer { height: auto; min-height: 0; margin: 8px auto; }
        .explorer-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .explorer-filter-field-wide, .explorer-custom-dates { grid-column: span 2; }
        .explorer-shell { grid-template-columns: 1fr; overflow: visible; }
        .explorer-shell.directory-collapsed { grid-template-columns: 1fr; }
        .explorer-panel { height: 360px; border-right: 0; border-bottom: 1px solid #e5e7eb; }
        .explorer-panel:last-child { height: auto; min-height: 520px; }
        .reader-wrap { overflow: visible; }
    }
    @media (max-width: 560px) {
        .explorer-filters { grid-template-columns: 1fr; }
        .explorer-filter-field-wide, .explorer-custom-dates { grid-column: span 1; }
        .explorer-custom-dates { flex-direction: column; }
    }
</style>

<main class="article-explorer">
    <section class="explorer-filter-card" aria-label="文章筛选">
        <form id="explorerFilters" class="explorer-filters">
            <div class="explorer-filter-field">
                <label class="explorer-filter-label" for="explorerTimeRange">时间范围</label>
                <select class="explorer-filter-control" id="explorerTimeRange" name="time_range">
                    <option value="all">全部时间</option><option value="3h">最近 3 小时</option><option value="6h">最近 6 小时</option><option value="1d">最近 1 天</option><option value="7d">最近 7 天</option><option value="custom">自定义日期</option>
                </select>
            </div>
            <div class="explorer-filter-field">
                <label class="explorer-filter-label" for="explorerFeedFilter">订阅列表</label>
                <div class="explorer-filter-stack">
                    <input class="explorer-filter-control" id="explorerFeedSearch" type="text" placeholder="输入订阅名筛选">
                    <select class="explorer-filter-control" id="explorerFeedFilter" name="feed_id"><option value="all">全部订阅</option></select>
                </div>
            </div>
            <div class="explorer-filter-field">
                <label class="explorer-filter-label" for="explorerStatusFilter">文章状态</label>
                <select class="explorer-filter-control" id="explorerStatusFilter" name="status">
                    <option value="unread">未读</option><option value="read">已读</option><option value="read_later">稍后读</option><option value="star">收藏</option><option value="all">全部状态</option>
                </select>
            </div>
            <div class="explorer-filter-field">
                <label class="explorer-filter-label" for="explorerReadMinutes">阅读时长</label>
                <select class="explorer-filter-control" id="explorerReadMinutes" name="read_minutes">
                    <option value="all">不限</option><option value="short">5 分钟以内</option><option value="medium">6-15 分钟</option><option value="long">16 分钟以上</option>
                </select>
            </div>
            <div class="explorer-filter-field explorer-filter-field-wide">
                <label class="explorer-filter-label" for="explorerKeyword">关键词</label>
                <input class="explorer-filter-control" id="explorerKeyword" name="keyword" placeholder="标题、订阅或分类关键词">
            </div>
            <div class="explorer-custom-dates" id="explorerCustomDates">
                <input class="explorer-filter-control" type="date" name="start_date" aria-label="开始日期">
                <input class="explorer-filter-control" type="date" name="end_date" aria-label="结束日期">
            </div>
            <div class="explorer-filter-actions">
                <button class="explorer-btn primary" type="submit"><i class="fas fa-search mr-1"></i>筛选</button>
                <button class="explorer-btn" type="button" id="explorerResetFilters">重置</button>
            </div>
        </form>
    </section>
    <div class="explorer-shell">
        <button type="button" class="directory-reopen" id="directoryReopen" title="展开订阅目录" aria-label="展开订阅目录">
            <i class="fas fa-folder-open"></i>
        </button>
        <section class="explorer-panel directory-panel" aria-label="分类和订阅源">
            <header class="explorer-panel-header">
                <div class="explorer-panel-header-row">
                    <h1 class="explorer-panel-title"><i class="fas fa-layer-group mr-2"></i>订阅目录</h1>
                    <button type="button" class="directory-toggle" id="directoryToggle" aria-expanded="true" title="折叠订阅目录">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
                <div class="explorer-panel-hint">默认显示全部未读内容，也可选择单个 Feed</div>
                <div class="explorer-status-filter" role="group" aria-label="文章状态过滤">
                    <button type="button" class="explorer-status-btn" data-status="all" title="全部状态" aria-label="全部状态"><i class="fas fa-layer-group"></i><span class="filter-label">全部</span></button>
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
                <div class="explorer-panel-header-row">
                    <h2 id="articleListTitle" class="explorer-panel-title">全部未读文章</h2>
                    <button type="button" class="explorer-btn primary explorer-ai-btn" id="explorerAiPageBtn"><i class="fas fa-wand-magic-sparkles mr-1"></i>AI 整理本页</button>
                </div>
                <div id="articleListHint" class="explorer-panel-hint">正在加载全部未读文章…</div>
            </header>
            <div id="articleList" class="explorer-scroll">
                <div class="explorer-state">正在加载全部未读文章…</div>
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

<div class="explorer-modal" id="explorerAiModal" role="dialog" aria-modal="true" aria-labelledby="explorerAiModalTitle">
    <div class="explorer-modal-panel">
        <header class="explorer-modal-head">
            <div>
                <h2 class="explorer-modal-title" id="explorerAiModalTitle">AI 整理本页</h2>
                <div class="explorer-modal-meta">按当前已加载文章的标题、订阅和分类生成辅助阅读页，最多整理 100 篇</div>
            </div>
            <button type="button" class="explorer-modal-close" data-close-explorer-modal aria-label="关闭"><i class="fas fa-times"></i></button>
        </header>
        <div class="explorer-modal-body">
            <textarea class="explorer-ai-prompt" id="explorerAiPrompt" placeholder="可选：补充阅读要求，例如“重点关注可执行建议，避免展开背景介绍”。"></textarea>
            <div class="explorer-ai-toolbar">
                <span class="explorer-ai-hint">留空使用默认整理结构；修改提示词后可再次生成</span>
                <button type="button" class="explorer-btn primary" id="explorerGenerateAiBtn"><i class="fas fa-wand-magic-sparkles mr-1"></i>重新生成</button>
            </div>
            <div class="explorer-ai-content" id="explorerAiContent"><div class="explorer-state">点击生成辅助阅读页面</div></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function ($) {
        'use strict';

        var state = {
            feedId: 'all',
            feedName: '全部未读文章',
            articleSubId: null,
            page: 1,
            hasMore: false,
            loadingArticles: false,
            articleRequestId: 0,
            feedRequestId: 0,
            loadedArticleCount: 0,
            status: new URLSearchParams(window.location.search).get('status') || 'unread',
            directoryCollapsed: localStorage.getItem('articleExplorerDirectoryCollapsed') === '1'
        };

        var statusLabels = {
            all: '全部状态',
            unread: '未读',
            read: '已读',
            star: '收藏',
            read_later: '稍后读'
        };
        var explorerFeedOptions = [];
        if (!statusLabels[state.status]) {
            state.status = 'unread';
        }

        function allArticlesTitle() {
            return state.status === 'all'
                ? '全部状态文章'
                : '全部' + (statusLabels[state.status] || '未读') + '文章';
        }
        state.feedName = allArticlesTitle();

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

        function request(url, options) {
            options = options || {};
            options.url = url;
            options.method = options.method || 'GET';
            options.dataType = 'json';
            options.headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            };
            return $.ajax(options).then(unwrap);
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

        function filterExplorerFeedOptions(preferredSelected) {
            var keyword = $.trim($('#explorerFeedSearch').val() || '').toLowerCase();
            var selected = String(preferredSelected || $('#explorerFeedFilter').val() || state.feedId || 'all');
            var html = '<option value="all">全部订阅</option>';
            var selectedOption = null;
            explorerFeedOptions.forEach(function (feed) {
                if (String(feed.id) === selected) {
                    selectedOption = feed;
                }
                if (keyword && String(feed.name || '').toLowerCase().indexOf(keyword) === -1) {
                    return;
                }
                html += '<option value="' + Number(feed.id) + '">' + escapeHtml(feed.name) + '</option>';
            });
            if (selectedOption && keyword && String(selectedOption.name || '').toLowerCase().indexOf(keyword) === -1) {
                html += '<option value="' + Number(selectedOption.id) + '">' + escapeHtml(selectedOption.name) + '</option>';
            }
            $('#explorerFeedFilter').html(html).val(selected);
            if ($('#explorerFeedFilter').val() === null) {
                $('#explorerFeedFilter').val('all');
            }
        }

        function renderExplorerFeedOptions(groups) {
            explorerFeedOptions = [];
            var seen = {};
            groups.forEach(function (group) {
                var category = group.category_info || {};
                toArray(group.list || group.feeds).forEach(function (feed) {
                    var feedId = String(feed.feed_id || feed.id || '');
                    if (!feedId || seen[feedId]) {
                        return;
                    }
                    seen[feedId] = true;
                    var feedName = feed.feed_name || feed.name || '';
                    explorerFeedOptions.push({
                        id: feedId,
                        name: feedName + (category.category_name ? ' · ' + category.category_name : ''),
                        plainName: feedName
                    });
                });
            });
            filterExplorerFeedOptions(state.feedId);
        }

        function explorerFilterParams() {
            var params = {};
            $('#explorerFilters').serializeArray().forEach(function (item) {
                params[item.name] = item.value;
            });
            var duration = params.read_minutes || 'all';
            delete params.read_minutes;
            delete params.feed_id;
            params.status = state.status;
            if (duration === 'short') {
                params.max_read_minutes = 5;
            } else if (duration === 'medium') {
                params.min_read_minutes = 6;
                params.max_read_minutes = 15;
            } else if (duration === 'long') {
                params.min_read_minutes = 16;
            }
            return params;
        }

        function selectedFeedName(feedId) {
            if (String(feedId) === 'all') {
                return allArticlesTitle();
            }
            for (var i = 0; i < explorerFeedOptions.length; i++) {
                if (String(explorerFeedOptions[i].id) === String(feedId)) {
                    return explorerFeedOptions[i].plainName || explorerFeedOptions[i].name;
                }
            }
            return $('#explorerFeedFilter option:selected').text() || '订阅文章';
        }

        function syncStatusControls() {
            $('#explorerStatusFilter').val(state.status);
            $('.explorer-status-btn').removeClass('active');
            $('.explorer-status-btn[data-status="' + state.status + '"]').addClass('active');
        }

        function loadFeeds() {
            var requestedStatus = state.status;
            var requestId = ++state.feedRequestId;
            request('/articles/explorer/data/feeds?status=' + encodeURIComponent(requestedStatus)).done(function (result) {
                if (requestId !== state.feedRequestId || requestedStatus !== state.status) {
                    return;
                }
                var groups = toArray(result.nav_infos || result);
                renderExplorerFeedOptions(groups);
                var html = '<button type="button" class="feed-item' + (state.feedId === 'all' ? ' active' : '') + '" data-feed-id="all" data-feed-name="' + escapeHtml(allArticlesTitle()) + '">';
                html += '<i class="fas fa-layer-group text-xs text-indigo-400"></i>';
                html += '<span class="feed-item-name">全部订阅</span>';
                html += '</button>';
                groups.forEach(function (group) {
                    var category = group.category_info || {};
                    html += '<div class="category-group">';
                    html += '<div class="category-name">' + escapeHtml(category.category_name || '未分类') + '</div>';
                    toArray(group.list || group.feeds).forEach(function (feed) {
                        var feedId = feed.feed_id || feed.id;
                        var feedName = feed.feed_name || feed.name || '';
                        html += '<button type="button" class="feed-item' + (String(state.feedId) === String(feedId) ? ' active' : '') + '" data-feed-id="' + Number(feedId) + '" data-feed-name="' + escapeHtml(feedName) + '">';
                        html += '<i class="fas fa-rss text-xs text-orange-400"></i>';
                        html += '<span class="feed-item-name">' + escapeHtml(feedName) + '</span>';
                        html += '<span class="feed-count" title="' + escapeHtml(statusLabels[state.status] || '当前状态') + '文章">' + Number(feed.feed_count || 0) + '</span>';
                        html += '</button>';
                    });
                    html += '</div>';
                });
                if (!groups.length) {
                    html += '<div class="explorer-state">还没有订阅 Feed</div>';
                }
                $('#feedTree').html(html);
            }).fail(function () {
                if (requestId === state.feedRequestId && requestedStatus === state.status) {
                    showError($('#feedTree'), '订阅目录加载失败');
                }
            });
        }

        function loadArticles(feedId, feedName, page, append) {
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

            var params = explorerFilterParams();
            params.page = page;
            params.page_count = 40;
            request('/articles/explorer/data/feeds/' + encodeURIComponent(feedId) + '/articles?' + $.param(params))
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
                        html += '<span>' + escapeHtml(published) + '</span><span class="article-item-status">' + escapeHtml(statusLabels[item.status] || item.status) + '</span>';
                        html += '<span>约 ' + Number(item.estimated_read_minutes || 1) + ' 分钟</span></span>';
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
                    } else {
                        $('#articleList .load-more').prop('disabled', false).text('加载更多');
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
                    $item.find('.article-item-status').text(statusLabels[status] || status);
                    $item.toggleClass('read', status === 'read');
                    $item.find('.status-dot').toggleClass('read', status === 'read');
                    if (!silent) {
                        $('#articleListHint').text('文章状态已更新，当前列表保持不变');
                    }
                })
                .fail(function () {
                    if (!silent) {
                        $('#articleListHint').text('状态更新失败，请稍后重试');
                    }
                });
        }

        function markLoadedUnreadAsRead() {
            var ids = [];
            if (state.status !== 'unread') {
                return $.Deferred().resolve().promise();
            }

            $('#articleList .article-item:not(.read)').each(function () {
                var id = Number($(this).data('article-sub-id'));
                if (id) {
                    ids.push(id);
                }
            });
            if (!ids.length) {
                return $.Deferred().resolve().promise();
            }

            return request('/articles/explorer/data/articles/read', {
                method: 'POST',
                data: { ids: ids }
            }).done(function (result) {
                var updatedIds = result.article_sub_ids || ids;
                updatedIds.forEach(function (id) {
                    var $item = $('#articleList .article-item[data-article-sub-id="' + Number(id) + '"]');
                    $item.addClass('read');
                    $item.find('.status-dot').addClass('read');
                    $item.find('.article-item-status').text(statusLabels.read);
                });
                $('#articleListHint').text('前面的 ' + updatedIds.length + ' 篇文章已标记为已读');
            });
        }

        function applyExplorerFilters(reloadFeeds) {
            var nextStatus = $('#explorerStatusFilter').val() || 'unread';
            var nextFeedId = $('#explorerFeedFilter').val() || 'all';
            var statusChanged = nextStatus !== state.status;
            state.status = nextStatus;
            state.feedId = nextFeedId;
            state.feedName = selectedFeedName(nextFeedId);
            state.articleSubId = null;
            state.page = 1;
            syncStatusControls();

            var urlParams = new URLSearchParams(window.location.search);
            urlParams.set('status', state.status);
            history.replaceState(null, '', window.location.pathname + '?' + urlParams.toString());

            if (reloadFeeds || statusChanged) {
                $('#feedTree').html('<div class="explorer-state">正在加载订阅目录…</div>');
                loadFeeds();
            } else {
                $('#feedTree .feed-item').removeClass('active');
                $('#feedTree .feed-item[data-feed-id="' + String(nextFeedId) + '"]').addClass('active');
            }
            loadArticles(state.feedId, state.feedName, 1, false);
        }

        function currentArticleSubIds() {
            var ids = [];
            $('#articleList .article-item').each(function () {
                var id = Number($(this).data('article-sub-id'));
                if (id && ids.indexOf(id) === -1) {
                    ids.push(id);
                }
            });
            return ids.slice(0, 100);
        }

        function generateExplorerAi() {
            var ids = currentArticleSubIds();
            if (!ids.length) {
                $('#explorerAiContent').html('<div class="explorer-state">当前页没有可整理的文章</div>');
                return;
            }
            var $button = $('#explorerGenerateAiBtn');
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>生成中…');
            $('#explorerAiContent').html('<div class="explorer-state">AI 正在整理当前已加载的 ' + ids.length + ' 篇文章，请稍候…</div>');
            request('/articles/workbench/ai-digest', {
                method: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                data: {
                    'article_sub_ids[]': ids,
                    custom_prompt: $('#explorerAiPrompt').val() || ''
                }
            }).done(function (result) {
                var render = result.ai_render || {};
                $('#explorerAiContent').html(render.html_content || '<div class="explorer-state">' + escapeHtml(render.error_message || 'AI 暂无结果') + '</div>');
            }).fail(function () {
                $('#explorerAiContent').html('<div class="explorer-state">AI 生成失败，请稍后重试</div>');
            }).always(function () {
                $button.prop('disabled', false).html('<i class="fas fa-wand-magic-sparkles mr-1"></i>重新生成');
            });
        }

        function openExplorerAi() {
            $('#explorerAiModal').addClass('active');
            $('#explorerAiPrompt').val('');
            generateExplorerAi();
        }

        $('#feedTree').on('click', '.feed-item', function () {
            $('.feed-item').removeClass('active');
            $(this).addClass('active');
            state.articleSubId = null;
            state.feedId = String($(this).data('feed-id'));
            state.feedName = $(this).data('feed-name');
            $('#explorerFeedSearch').val('');
            filterExplorerFeedOptions(state.feedId);
            $('#explorerFeedFilter').val(state.feedId);
            loadArticles(state.feedId, state.feedName, 1, false);
        });

        $('#articleList').on('click', '.article-item', function () {
            $('.article-item').removeClass('active');
            $(this).addClass('active');
            loadArticle($(this).data('article-sub-id'));
        });

        $('#articleList').on('click', '.load-more', function () {
            if (state.feedId && state.hasMore) {
                var $button = $(this);
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>处理中…');
                markLoadedUnreadAsRead()
                    .done(function () {
                        var nextPage = state.status === 'unread' ? 1 : state.page + 1;
                        loadArticles(state.feedId, state.feedName, nextPage, true);
                    })
                    .fail(function () {
                        $button.prop('disabled', false).text('加载更多');
                        $('#articleListHint').text('标记已读失败，请稍后重试');
                    });
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
            $('#explorerStatusFilter').val(status);
            $('#explorerFeedFilter').val('all');
            applyExplorerFilters(true);
        });

        $('#explorerFilters').on('submit', function (event) {
            event.preventDefault();
            applyExplorerFilters(false);
        });
        $('#explorerTimeRange').on('change', function () {
            $('#explorerCustomDates').toggleClass('active', $(this).val() === 'custom');
        });
        $('#explorerFeedSearch').on('input', function () {
            filterExplorerFeedOptions();
        });
        $('#explorerResetFilters').on('click', function () {
            $('#explorerFilters')[0].reset();
            $('#explorerFeedSearch').val('');
            $('#explorerCustomDates').removeClass('active');
            $('#explorerStatusFilter').val('unread');
            $('#explorerFeedFilter').val('all');
            applyExplorerFilters(true);
        });

        $('#explorerAiPageBtn').on('click', openExplorerAi);
        $('#explorerGenerateAiBtn').on('click', generateExplorerAi);
        $('[data-close-explorer-modal]').on('click', function () {
            $('#explorerAiModal').removeClass('active');
        });
        $('#explorerAiModal').on('click', function (event) {
            if (event.target === this) {
                $(this).removeClass('active');
            }
        });

        $('#explorerStatusFilter').val(state.status);
        syncStatusControls();
        syncDirectoryState();

        loadFeeds();
        loadArticles('all', state.feedName, 1, false);
    })(jQuery);
</script>
@endsection
