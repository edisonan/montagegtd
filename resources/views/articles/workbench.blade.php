@extends('layouts.app')

@section('title', '文章总览 - 蒙太奇')
@section('description', '按时间、订阅、状态、阅读时长和关键词管理文章')

@section('content')
<style>
    .article-workbench { max-width: 1600px; margin: 18px auto; padding: 0 18px 48px; }
    .workbench-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 8px 28px rgba(15,23,42,.06); }
    .workbench-head { padding: 20px 22px; border-bottom: 1px solid #e5e7eb; }
    .workbench-title { margin: 0; color: #0f172a; font-size: 22px; font-weight: 750; }
    .workbench-subtitle { margin-top: 5px; color: #64748b; font-size: 13px; }
    .workbench-filters { display: grid; grid-template-columns: repeat(6, minmax(130px, 1fr)); gap: 12px; padding: 16px 22px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
    .filter-field { min-width: 0; }
    .filter-stack { display: flex; flex-direction: column; gap: 6px; }
    .filter-field-wide { grid-column: span 2; }
    .filter-label { display: block; margin-bottom: 5px; color: #64748b; font-size: 12px; font-weight: 650; }
    .filter-control { width: 100%; min-height: 36px; padding: 7px 9px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #334155; font-size: 13px; }
    .filter-actions { display: flex; align-items: end; gap: 8px; }
    .workbench-btn { min-height: 36px; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #475569; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .workbench-btn.primary { border-color: #4f46e5; background: #4f46e5; color: #fff; }
    .workbench-btn:hover { filter: brightness(.97); }
    .custom-dates { display: none; grid-column: span 2; gap: 8px; }
    .custom-dates.active { display: flex; }
    .workbench-summary { display: flex; justify-content: space-between; align-items: center; padding: 12px 22px; color: #64748b; font-size: 13px; }
    .workbench-results { display: grid; grid-template-columns: minmax(320px, 430px) minmax(0, 1fr); height: calc(100vh - 285px); min-height: 560px; }
    .workbench-list-pane { display: flex; min-width: 0; min-height: 0; flex-direction: column; border-right: 1px solid #e5e7eb; }
    .workbench-reader-pane { min-width: 0; overflow-y: auto; background: #fff; }
    .workbench-reader-empty { padding: 150px 30px; color: #94a3b8; text-align: center; line-height: 1.8; }
    .workbench-footer { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 18px 16px; border-top: 1px solid #e5e7eb; background: #fff; }
    .workbench-footer-hint { color: #64748b; font-size: 12px; }
    .workbench-table-wrap { min-height: 0; overflow-x: hidden; overflow-y: auto; flex: 1 1 auto; }
    .workbench-table { width: 100%; min-width: 0; border-collapse: collapse; }
    .workbench-table th { padding: 11px 14px; background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-align: left; white-space: nowrap; }
    .workbench-table td { padding: 13px 14px; border-top: 1px solid #f1f5f9; color: #475569; font-size: 13px; vertical-align: middle; }
    .workbench-table tr:hover td { background: #fafbff; }
    .article-subject { max-width: 390px; color: #0f172a; font-weight: 650; line-height: 1.45; }
    .article-subject.read { color: #94a3b8; font-weight: 500; }
    .article-subject small { display: block; margin-top: 4px; color: #94a3b8; font-size: 11px; font-weight: 400; }
    .article-title-link { cursor: pointer; }
    .article-title-link:hover { color: #4338ca; }
    .workbench-row-selected td { background: #eef2ff; }
    .status-badge { display: inline-block; padding: 3px 7px; border-radius: 999px; background: #eef2ff; color: #4338ca; font-size: 11px; white-space: nowrap; }
    .status-badge.read { background: #f1f5f9; color: #64748b; }
    .status-badge.star { background: #fef3c7; color: #92400e; }
    .status-badge.read_later { background: #dcfce7; color: #166534; }
    .row-actions { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 7px; }
    .row-action { padding: 5px 7px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #64748b; cursor: pointer; font-size: 11px; white-space: nowrap; }
    .row-action:hover, .row-action.active { border-color: #6366f1; background: #eef2ff; color: #4338ca; }
    .empty-state, .loading-state { padding: 60px 20px; color: #94a3b8; text-align: center; }
    .pagination { display: flex; justify-content: center; gap: 8px; padding: 18px; }
    .pagination button { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #475569; cursor: pointer; }
    .pagination button:disabled { cursor: not-allowed; opacity: .45; }
    .workbench-modal { position: fixed; z-index: 100; inset: 0; display: none; align-items: center; justify-content: center; padding: 20px; background: rgba(15,23,42,.45); }
    .workbench-modal.active { display: flex; }
    .workbench-modal-panel { display: flex; flex-direction: column; width: min(1050px, 100%); max-height: min(850px, 92vh); overflow: hidden; border-radius: 14px; background: #fff; box-shadow: 0 24px 80px rgba(15,23,42,.25); }
    .modal-head { display: flex; justify-content: space-between; gap: 18px; padding: 18px 22px; border-bottom: 1px solid #e5e7eb; }
    .modal-title { margin: 0; color: #0f172a; font-size: 19px; font-weight: 750; }
    .modal-meta { margin-top: 6px; color: #64748b; font-size: 12px; }
    .modal-close { border: 0; background: transparent; color: #64748b; cursor: pointer; font-size: 18px; }
    .modal-actions { display: flex; gap: 6px; align-items: center; margin-top: 8px; }
    .modal-body { overflow-y: auto; padding: 24px clamp(20px, 5vw, 70px) 42px; color: #334155; line-height: 1.85; }
    .modal-body img { max-width: 100%; height: auto; border-radius: 8px; }
    .modal-body pre { overflow-x: auto; padding: 14px; border-radius: 8px; background: #0f172a; color: #e2e8f0; }
    .modal-body a { color: #4f46e5; }
    .ai-prompt { width: 100%; min-height: 110px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; resize: vertical; color: #334155; font-size: 13px; line-height: 1.6; }
    .ai-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .ai-hint { color: #64748b; font-size: 12px; }
    .ai-content h2, .ai-content h3, .ai-content h4 { color: #0f172a; }
    .workbench-reader-head { padding: 24px clamp(20px, 4vw, 56px) 18px; border-bottom: 1px solid #f1f5f9; }
    .workbench-reader-title { margin: 0; color: #0f172a; font-size: clamp(22px, 3vw, 32px); line-height: 1.3; font-weight: 750; }
    .workbench-reader-meta { margin-top: 10px; color: #64748b; font-size: 12px; line-height: 1.7; }
    .workbench-reader-actions { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 14px; }
    .workbench-reader-content { padding: 26px clamp(20px, 4vw, 56px) 55px; color: #334155; font-size: 16px; line-height: 1.85; overflow-wrap: anywhere; }
    .workbench-reader-content img { max-width: 100%; height: auto; border-radius: 8px; }
    .workbench-reader-content pre { overflow-x: auto; padding: 14px; border-radius: 8px; background: #0f172a; color: #e2e8f0; }
    .workbench-reader-content a { color: #4f46e5; }
    @media (max-width: 1100px) { .workbench-filters { grid-template-columns: repeat(3, 1fr); } .filter-field-wide, .custom-dates { grid-column: span 1; } }
    @media (max-width: 800px) { .workbench-results { grid-template-columns: 1fr; height: auto; } .workbench-list-pane { max-height: 600px; border-right: 0; border-bottom: 1px solid #e5e7eb; } .workbench-reader-pane { min-height: 600px; } }
    @media (max-width: 640px) { .workbench-filters { grid-template-columns: 1fr; } .filter-field-wide, .custom-dates { grid-column: span 1; } .custom-dates { flex-direction: column; } .article-workbench { padding: 0 8px 32px; } }
</style>

<main class="article-workbench">
    <section class="workbench-card">
        <header class="workbench-head">
            <h1 class="workbench-title"><i class="fas fa-sliders-h mr-2 text-indigo-500"></i>文章总览</h1>
            <div class="workbench-subtitle">把文章筛出来、读完它，并用 AI 生成更容易理解的辅助阅读页。</div>
        </header>
        <form id="workbenchFilters" class="workbench-filters">
            <div class="filter-field">
                <label class="filter-label">时间范围</label>
                <select class="filter-control" name="time_range">
                    <option value="all">全部时间</option><option value="3h">最近 3 小时</option><option value="6h" selected>最近 6 小时</option><option value="1d">最近 1 天</option><option value="7d">最近 7 天</option><option value="custom">自定义日期</option>
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">订阅列表</label>
                <div class="filter-stack">
                    <input class="filter-control" type="text" id="feedSearch" placeholder="输入订阅名筛选">
                    <select class="filter-control" name="feed_id" id="feedFilter"><option value="0">全部订阅</option></select>
                </div>
            </div>
            <div class="filter-field">
                <label class="filter-label">文章状态</label>
                <select class="filter-control" name="status">
                    <option value="unread">未读</option><option value="read">已读</option><option value="read_later">稍后读</option><option value="star">收藏</option><option value="all">全部状态</option>
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">阅读时长</label>
                <select class="filter-control" name="read_minutes">
                    <option value="all">不限</option><option value="short">5 分钟以内</option><option value="medium">6-15 分钟</option><option value="long">16 分钟以上</option>
                </select>
            </div>
            <div class="filter-field filter-field-wide">
                <label class="filter-label">关键词</label>
                <input class="filter-control" name="keyword" placeholder="标题、订阅关键词">
            </div>
            <div class="custom-dates" id="customDates">
                <input class="filter-control" type="date" name="start_date" aria-label="开始日期">
                <input class="filter-control" type="date" name="end_date" aria-label="结束日期">
            </div>
            <div class="filter-actions">
                <button class="workbench-btn primary" type="submit"><i class="fas fa-search mr-1"></i>筛选</button>
                <button class="workbench-btn" type="button" id="resetFilters">重置</button>
            </div>
        </form>
        <div class="workbench-results">
            <div class="workbench-list-pane">
                <div class="workbench-summary"><span id="workbenchSummary">正在加载…</span><button type="button" class="workbench-btn primary" id="aiPageBtn"><i class="fas fa-wand-magic-sparkles mr-1"></i>AI 整理本页</button></div>
                <div class="workbench-table-wrap">
                    <table class="workbench-table">
                        <thead><tr><th>文章标题 · 订阅 · 分类 · 阅读时间</th></tr></thead>
                        <tbody id="workbenchRows"><tr><td class="loading-state">正在加载文章…</td></tr></tbody>
                    </table>
                </div>
                <div class="pagination" id="workbenchPagination"></div>
                <div class="workbench-footer">
                    <div class="workbench-footer-hint" id="pageReadHint">本页未读 0 篇</div>
                    <button type="button" class="workbench-btn primary" id="markPageReadBtn"><i class="fas fa-check mr-1"></i>本页未读标为已读</button>
                </div>
            </div>
            <section class="workbench-reader-pane" id="workbenchReader">
                <div class="workbench-reader-empty"><i class="far fa-newspaper text-4xl mb-4"></i><br>点击左侧标题，在这里阅读全文</div>
            </section>
        </div>
    </section>
</main>

@include('artifacts._dialog')

<div class="workbench-modal" id="aiModal">
    <div class="workbench-modal-panel">
        <header class="modal-head"><div><h2 class="modal-title" id="aiModalTitle">AI 辅助阅读</h2><div class="modal-meta">默认按标题、订阅和分类梳理文章，每个主题分成 3-5 个小节</div></div><button type="button" class="modal-close" data-close-modal="aiModal"><i class="fas fa-times"></i></button></header>
        <div class="modal-body">
            <textarea class="ai-prompt" id="aiPrompt" placeholder="可选：补充你的阅读要求，例如“重点关注可执行建议，避免展开背景介绍”。"></textarea>
            <div class="ai-toolbar"><span class="ai-hint">留空即使用默认辅助阅读结构；修改提示词后可再次生成</span><button type="button" class="workbench-btn primary" id="generateAiBtn"><i class="fas fa-wand-magic-sparkles mr-1"></i>重新生成</button></div>
            <div class="ai-content" id="aiContent"><div class="empty-state">点击生成辅助阅读页面</div></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function ($) {
    'use strict';
    var state = { page: 1, articleSubId: null, articleSubIds: [], currentArticles: [], pagination: null };
    var feedOptions = [];
    var statusLabels = { unread: '未读', read: '已读', read_later: '稍后读', star: '收藏' };

    function escapeHtml(value) { return $('<div>').text(value == null ? '' : String(value)).html(); }
    function unwrap(response) {
        if (!response || Number(response.code) !== 9999) throw new Error(response && response.msg ? response.msg : '请求失败');
        return response.result || {};
    }
    function request(url, options) {
        options = options || {};
        options.url = url; options.dataType = 'json'; options.headers = { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
        return $.ajax(options).then(unwrap);
    }
    function formParams() {
        var data = $('#workbenchFilters').serializeArray(), params = {};
        data.forEach(function (item) { params[item.name] = item.value; });
        var duration = params.read_minutes || 'all';
        delete params.read_minutes;
        if (duration === 'short') { params.max_read_minutes = 5; }
        if (duration === 'medium') { params.min_read_minutes = 6; params.max_read_minutes = 15; }
        if (duration === 'long') { params.min_read_minutes = 16; }
        params.page = state.page;
        params.page_count = 100;
        return params;
    }
    function filterFeeds() {
        var keyword = $.trim($('#feedSearch').val() || '').toLowerCase();
        var selected = String($('#feedFilter').val() || '0');
        var html = '<option value="0">全部订阅</option>';
        var seen = {};
        var selectedOption = null;
        feedOptions.forEach(function (feed) {
            if (!feed || !feed.id) {
                return;
            }
            var optionId = String(feed.id);
            if (optionId === selected) {
                selectedOption = feed;
            }
            if (keyword && String(feed.name || '').toLowerCase().indexOf(keyword) === -1) {
                return;
            }
            if (seen[optionId]) {
                return;
            }
            seen[optionId] = true;
            html += '<option value="' + Number(feed.id) + '">' + escapeHtml(feed.name || '') + '</option>';
        });
        if (selectedOption && !seen[String(selectedOption.id)]) {
            html += '<option value="' + Number(selectedOption.id) + '">' + escapeHtml(selectedOption.name || '') + '</option>';
        }
        $('#feedFilter').html(html).val(selected && (seen[selected] || (selectedOption && String(selectedOption.id) === selected)) ? selected : '0');
    }
    function renderFeeds(feeds) {
        feedOptions = [];
        (feeds || []).forEach(function (feed) {
            if (!feed || !feed.id) return;
            var feedId = String(feed.id);
            if (feedOptions.some(function (exists) { return String(exists.id || '') === feedId; })) return;
            feedOptions.push({
                id: feedId,
                name: (feed.feed_name || feed.name || '') + (feed.category_name ? ' · ' + feed.category_name : '')
            });
        });
        filterFeeds();
    }
    function rowHtml(item) {
        var status = item.status || 'unread';
        var subjectClass = status === 'read' ? ' read' : '';
        return '<tr data-sub-id="' + Number(item.id) + '" data-status="' + escapeHtml(status) + '">'
            + '<td><div class="article-subject' + subjectClass + ' article-title-link" role="button" tabindex="0">' + escapeHtml(item.subject || '无标题文章') + '<small>' + escapeHtml(item.feed_name || '-') + ' · ' + escapeHtml(item.category_name || '未分类') + ' · 约 ' + Number(item.estimated_read_minutes || 1) + ' 分钟</small></div></td></tr>';
    }
    function loadData() {
        var params = formParams();
        $('#workbenchRows').html('<tr><td class="loading-state">正在加载文章…</td></tr>');
        request('/articles/workbench/data?' + $.param(params)).done(function (result) {
            renderFeeds(result.feeds);
            var articles = result.articles || [], html = '';
            state.currentArticles = articles;
            state.articleSubIds = articles.map(function (item) { return Number(item.id); });
            state.pagination = result.pagination || null;
            articles.forEach(function (item) { html += rowHtml(item); });
            $('#workbenchRows').html(html || '<tr><td class="empty-state">没有符合条件的文章</td></tr>');
            var pagination = result.pagination || {};
            $('#workbenchSummary').text('共 ' + Number(pagination.total || 0) + ' 篇 · 第 ' + Number(pagination.current_page || 1) + ' / ' + Number(pagination.last_page || 1) + ' 页');
            updatePageReadHint();
            var page = Number(pagination.current_page || 1), last = Number(pagination.last_page || 1);
            $('#workbenchPagination').html('<button type="button" class="page-prev" ' + (page <= 1 ? 'disabled' : '') + '>上一页</button><span class="px-2 py-1 text-sm text-gray-500">' + page + ' / ' + last + '</span><button type="button" class="page-next" ' + (page >= last ? 'disabled' : '') + '>下一页</button>');
        }).fail(function () { $('#workbenchRows').html('<tr><td class="empty-state">加载失败，请稍后重试</td></tr>'); });
    }
    function openArticle(id) {
        state.articleSubId = Number(id);
        $('#workbenchRows tr').removeClass('workbench-row-selected');
        $('#workbenchRows tr[data-sub-id="' + Number(id) + '"]').addClass('workbench-row-selected');
        $('#workbenchReader').html('<div class="workbench-reader-empty">正在加载正文…</div>');
        request('/articles/workbench/articles/' + Number(id)).done(function (result) {
            var article = result.article || {};
            var html = '<header class="workbench-reader-head"><h2 class="workbench-reader-title">' + escapeHtml(article.subject || '无标题文章') + '</h2>';
            html += '<div class="workbench-reader-meta">' + escapeHtml(article.feed_name || '') + (article.category_name ? ' · ' + escapeHtml(article.category_name) : '') + ' · ' + Number(article.word_count || 0) + ' 字 · 约 ' + Number(article.estimated_read_minutes || 1) + ' 分钟';
            if (article.published) html += ' · ' + escapeHtml(article.published);
            html += '</div><div class="workbench-reader-actions">';
            if (article.url) html += '<a href="' + escapeHtml(article.url) + '" target="_blank" rel="noopener noreferrer" class="row-action"><i class="fas fa-external-link-alt mr-1"></i>查看原文</a>';
            if (article.id) html += '<button type="button" class="row-action js-artifact-action" data-related-type="article" data-related-id="' + Number(article.id) + '" data-artifact-type="visual_reading"><i class="fas fa-book-open mr-1"></i>可视化阅读</button>';
            if (article.id) html += '<button type="button" class="row-action js-artifact-action" data-related-type="article" data-related-id="' + Number(article.id) + '" data-artifact-type="mind_map"><i class="fas fa-diagram-project mr-1"></i>思维导图</button>';
            html += '<button type="button" class="row-action reader-status-action' + (result.status === 'read_later' ? ' active' : '') + '" data-reader-status="read_later"><i class="far fa-clock mr-1"></i>稍后阅读</button>';
            html += '<button type="button" class="row-action reader-status-action' + (result.status === 'star' ? ' active' : '') + '" data-reader-status="star"><i class="far fa-star mr-1"></i>收藏</button>';
            html += '</div></header><article class="workbench-reader-content">' + (article.content || '<p>暂无正文</p>') + '</article>';
            $('#workbenchReader').html(html);
            var $row = $('#workbenchRows tr[data-sub-id="' + Number(id) + '"]');
            $row.find('.article-subject').addClass('read'); $row.find('.status-badge').attr('class', 'status-badge read').text('已读');
        }).fail(function () { $('#workbenchReader').html('<div class="workbench-reader-empty">正文加载失败</div>'); });
    }
    function openAi() {
        $('#aiModal').addClass('active'); $('#aiPrompt').val(''); $('#aiContent').html('<div class="loading-state">正在生成本页默认辅助阅读页面…</div>');
        $('#aiModalTitle').text('AI 辅助阅读');
        setTimeout(generateAi, 0);
    }
    function updateRowStatus(id, status) {
        var currentFilter = $('#workbenchFilters select[name="status"]').val() || 'unread';
        var $row = $('#workbenchRows tr[data-sub-id="' + Number(id) + '"]');
        if (!$row.length) {
            return;
        }
        $row.find('.article-subject').toggleClass('read', status === 'read');
        $row.attr('data-status', status);
        for (var i = 0; i < state.currentArticles.length; i++) {
            if (Number(state.currentArticles[i].id) === Number(id)) {
                state.currentArticles[i].status = status;
                break;
            }
        }
        updatePageReadHint();
        if (currentFilter !== 'all' && currentFilter !== status) {
            $row.remove();
            if (state.pagination && typeof state.pagination.total !== 'undefined' && state.pagination.total > 0) {
                state.pagination.total -= 1;
                $('#workbenchSummary').text('共 ' + Number(state.pagination.total || 0) + ' 篇 · 第 ' + Number(state.pagination.current_page || 1) + ' / ' + Number(state.pagination.last_page || 1) + ' 页');
            }
            if ($('#workbenchRows tr').length === 0) {
                $('#workbenchRows').html('<tr><td class="empty-state">没有符合条件的文章</td></tr>');
            }
        }
    }
    function getUnreadCurrentPageIds() {
        var ids = [];
        (state.currentArticles || []).forEach(function (item) {
            if ((item.status || 'unread') === 'unread') {
                ids.push(Number(item.id));
            }
        });
        return ids;
    }
    function updatePageReadHint() {
        var unreadCount = getUnreadCurrentPageIds().length;
        $('#pageReadHint').text('本页未读 ' + unreadCount + ' 篇');
        $('#markPageReadBtn').prop('disabled', unreadCount === 0);
    }
    function updateStatus(id, status) {
        request('/articles/workbench/articles/' + Number(id) + '/status', { method: 'POST', data: { status: status } }).done(function (result) {
            $('#workbenchReader [data-reader-status]').removeClass('active');
            $('#workbenchReader [data-reader-status="' + status + '"]').addClass('active');
            updateRowStatus(id, status);
        });
    }
    function markPageRead() {
        var ids = getUnreadCurrentPageIds();
        if (!ids.length) {
            return;
        }
        var $button = $('#markPageReadBtn');
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>处理中…');
        request('/articles/workbench/page-read', { method: 'POST', data: { ids: ids } })
            .done(function () { loadData(); })
            .always(function () { $button.prop('disabled', false).html('<i class="fas fa-check mr-1"></i>本页未读标为已读'); });
    }
    $('#workbenchFilters').on('submit', function (event) { event.preventDefault(); state.page = 1; loadData(); });
    $('#workbenchFilters select[name="time_range"]').on('change', function () { $('#customDates').toggleClass('active', $(this).val() === 'custom'); });
    $('#feedSearch').on('input', function () { filterFeeds(); });
    $('#resetFilters').on('click', function () { $('#workbenchFilters')[0].reset(); $('#feedSearch').val(''); $('#customDates').removeClass('active'); state.page = 1; loadData(); });
    $('#workbenchRows').on('click', '.view-article', function () { openArticle($(this).closest('tr').data('sub-id')); });
    $('#workbenchRows').on('click', '.article-title-link', function () { openArticle($(this).closest('tr').data('sub-id')); });
    $('#workbenchRows').on('click', 'tr', function (event) {
        if ($(event.target).closest('.row-action').length || $(event.target).closest('button').length || $(event.target).closest('.article-title-link').length) return;
        openArticle($(this).data('sub-id'));
    });
    $('#workbenchReader').on('click', '[data-reader-status]', function () {
        if (state.articleSubId) updateStatus(state.articleSubId, $(this).data('reader-status'));
    });
    // AI 制品弹窗（事件委托，阅读区是动态生成的）
    $('#workbenchReader').on('click', '.js-artifact-action', function () {
        var btn = this;
        if (window.openArtifactDialog) {
            window.openArtifactDialog({
                relatedType: $(btn).data('related-type'),
                relatedId: $(btn).data('related-id'),
                artifactType: $(btn).data('artifact-type')
            });
        }
    });
    $('#aiPageBtn').on('click', openAi);
    $('#markPageReadBtn').on('click', markPageRead);
    $('#workbenchPagination').on('click', '.page-prev', function () { if (!this.disabled) { state.page--; loadData(); } });
    $('#workbenchPagination').on('click', '.page-next', function () { if (!this.disabled) { state.page++; loadData(); } });
    $('[data-close-modal]').on('click', function () { $('#' + $(this).data('close-modal')).removeClass('active'); });
    $('.workbench-modal').on('click', function (event) { if (event.target === this) $(this).removeClass('active'); });
    function generateAi() {
        if (!state.articleSubIds.length) {
            $('#aiContent').html('<div class="empty-state">当前页没有文章</div>');
            return;
        }
        var $button = $('#generateAiBtn'); $button.prop('disabled', true).text('生成中…'); $('#aiContent').html('<div class="loading-state">AI 正在整理文章，请稍候…</div>');
        var data = { 'article_sub_ids[]': state.articleSubIds, custom_prompt: $('#aiPrompt').val() || '' };
        request('/articles/workbench/ai-digest', { method: 'POST', contentType: 'application/x-www-form-urlencoded', data: data })
            .done(function (result) {
                var render = result.ai_render || {};
                $('#aiContent').html(render.html_content || '<div class="empty-state">' + escapeHtml(render.error_message || 'AI 暂无结果') + '</div>');
            }).fail(function () { $('#aiContent').html('<div class="empty-state">AI 生成失败，请稍后重试</div>'); })
            .always(function () { $button.prop('disabled', false).html('<i class="fas fa-wand-magic-sparkles mr-1"></i>重新生成'); });
    }
    $('#generateAiBtn').on('click', generateAi);
    loadData();
})(jQuery);
</script>
@endsection
