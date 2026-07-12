@extends('layouts.app')

@section('title', '文章工作台 - 蒙太奇')
@section('description', '按时间、订阅源、状态、阅读时长和关键词管理文章')

@section('content')
<style>
    .article-workbench { max-width: 1600px; margin: 18px auto; padding: 0 18px 48px; }
    .workbench-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 8px 28px rgba(15,23,42,.06); }
    .workbench-head { padding: 20px 22px; border-bottom: 1px solid #e5e7eb; }
    .workbench-title { margin: 0; color: #0f172a; font-size: 22px; font-weight: 750; }
    .workbench-subtitle { margin-top: 5px; color: #64748b; font-size: 13px; }
    .workbench-filters { display: grid; grid-template-columns: repeat(6, minmax(130px, 1fr)); gap: 12px; padding: 16px 22px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
    .filter-field { min-width: 0; }
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
    .workbench-table-wrap { overflow-x: auto; }
    .workbench-table { width: 100%; min-width: 960px; border-collapse: collapse; }
    .workbench-table th { padding: 11px 14px; background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-align: left; white-space: nowrap; }
    .workbench-table td { padding: 13px 14px; border-top: 1px solid #f1f5f9; color: #475569; font-size: 13px; vertical-align: middle; }
    .workbench-table tr:hover td { background: #fafbff; }
    .article-subject { max-width: 390px; color: #0f172a; font-weight: 650; line-height: 1.45; }
    .article-subject.read { color: #94a3b8; font-weight: 500; }
    .article-subject small { display: block; margin-top: 4px; color: #94a3b8; font-size: 11px; font-weight: 400; }
    .status-badge { display: inline-block; padding: 3px 7px; border-radius: 999px; background: #eef2ff; color: #4338ca; font-size: 11px; white-space: nowrap; }
    .status-badge.read { background: #f1f5f9; color: #64748b; }
    .status-badge.star { background: #fef3c7; color: #92400e; }
    .status-badge.read_later { background: #dcfce7; color: #166534; }
    .row-actions { display: flex; flex-wrap: wrap; gap: 5px; min-width: 250px; }
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
    .modal-body { overflow-y: auto; padding: 24px clamp(20px, 5vw, 70px) 42px; color: #334155; line-height: 1.85; }
    .modal-body img { max-width: 100%; height: auto; border-radius: 8px; }
    .modal-body pre { overflow-x: auto; padding: 14px; border-radius: 8px; background: #0f172a; color: #e2e8f0; }
    .modal-body a { color: #4f46e5; }
    .ai-prompt { width: 100%; min-height: 110px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; resize: vertical; color: #334155; font-size: 13px; line-height: 1.6; }
    .ai-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .ai-hint { color: #64748b; font-size: 12px; }
    .ai-content h2, .ai-content h3, .ai-content h4 { color: #0f172a; }
    @media (max-width: 1100px) { .workbench-filters { grid-template-columns: repeat(3, 1fr); } .filter-field-wide, .custom-dates { grid-column: span 1; } }
    @media (max-width: 640px) { .workbench-filters { grid-template-columns: 1fr; } .filter-field-wide, .custom-dates { grid-column: span 1; } .custom-dates { flex-direction: column; } .article-workbench { padding: 0 8px 32px; } }
</style>

<main class="article-workbench">
    <section class="workbench-card">
        <header class="workbench-head">
            <h1 class="workbench-title"><i class="fas fa-sliders-h mr-2 text-indigo-500"></i>文章工作台</h1>
            <div class="workbench-subtitle">把文章筛出来、读完它，并用 AI 生成更容易理解的辅助阅读页。</div>
        </header>
        <form id="workbenchFilters" class="workbench-filters">
            <div class="filter-field">
                <label class="filter-label">时间范围</label>
                <select class="filter-control" name="time_range">
                    <option value="all">全部时间</option><option value="3h">最近 3 小时</option><option value="6h">最近 6 小时</option><option value="1d">最近 1 天</option><option value="7d">最近 7 天</option><option value="custom">自定义日期</option>
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">Feed</label>
                <select class="filter-control" name="feed_id" id="feedFilter"><option value="0">全部 Feed</option></select>
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
                <input class="filter-control" name="keyword" placeholder="标题、Feed 或分类关键词">
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
        <div class="workbench-summary"><span id="workbenchSummary">正在加载…</span><span>点击标题查看全文，默认标记已读</span></div>
        <div class="workbench-table-wrap">
            <table class="workbench-table">
                <thead><tr><th>标题</th><th>所属 Feed</th><th>分类</th><th>字数</th><th>阅读时长</th><th>状态</th><th>操作</th></tr></thead>
                <tbody id="workbenchRows"><tr><td colspan="7" class="loading-state">正在加载文章…</td></tr></tbody>
            </table>
        </div>
        <div class="pagination" id="workbenchPagination"></div>
    </section>
</main>

<div class="workbench-modal" id="articleModal">
    <div class="workbench-modal-panel">
        <header class="modal-head"><div><h2 class="modal-title" id="articleModalTitle">文章正文</h2><div class="modal-meta" id="articleModalMeta"></div></div><button type="button" class="modal-close" data-close-modal="articleModal"><i class="fas fa-times"></i></button></header>
        <article class="modal-body" id="articleModalBody"><div class="loading-state">正在加载正文…</div></article>
    </div>
</div>
<div class="workbench-modal" id="aiModal">
    <div class="workbench-modal-panel">
        <header class="modal-head"><div><h2 class="modal-title" id="aiModalTitle">AI 辅助阅读</h2><div class="modal-meta">默认按标题、分类和 Feed 梳理文章，每个主题分成 3-5 个小节</div></div><button type="button" class="modal-close" data-close-modal="aiModal"><i class="fas fa-times"></i></button></header>
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
    var state = { page: 1, articleSubId: null };
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
        params.page_count = 30;
        return params;
    }
    function renderFeeds(feeds) {
        var html = '<option value="0">全部 Feed</option>';
        (feeds || []).forEach(function (feed) {
            html += '<option value="' + Number(feed.id) + '">' + escapeHtml(feed.feed_name || '') + (feed.category_name ? ' · ' + escapeHtml(feed.category_name) : '') + '</option>';
        });
        var selected = $('#feedFilter').val();
        $('#feedFilter').html(html).val(selected || '0');
    }
    function rowHtml(item) {
        var status = item.status || 'unread';
        var subjectClass = status === 'read' ? ' read' : '';
        return '<tr data-sub-id="' + Number(item.id) + '">'
            + '<td><div class="article-subject' + subjectClass + '">' + escapeHtml(item.subject || '无标题文章') + '<small>' + escapeHtml(item.published || '') + '</small></div></td>'
            + '<td>' + escapeHtml(item.feed_name || '-') + '</td><td>' + escapeHtml(item.category_name || '未分类') + '</td>'
            + '<td>' + Number(item.word_count || 0) + ' 字</td><td>约 ' + Number(item.estimated_read_minutes || 1) + ' 分钟</td>'
            + '<td><span class="status-badge ' + escapeHtml(status) + '">' + escapeHtml(statusLabels[status] || status) + '</span></td>'
            + '<td><div class="row-actions">'
            + '<button type="button" class="row-action view-article">查看全文</button>'
            + '<button type="button" class="row-action ai-article"><i class="fas fa-wand-magic-sparkles"></i> AI辅助读</button>'
            + '<button type="button" class="row-action status-action ' + (status === 'read_later' ? 'active' : '') + '" data-status-action="read_later"><i class="far fa-clock"></i></button>'
            + '<button type="button" class="row-action status-action ' + (status === 'star' ? 'active' : '') + '" data-status-action="star"><i class="far fa-star"></i></button>'
            + '</div></td></tr>';
    }
    function loadData() {
        var params = formParams();
        $('#workbenchRows').html('<tr><td colspan="7" class="loading-state">正在加载文章…</td></tr>');
        request('/articles/workbench/data?' + $.param(params)).done(function (result) {
            renderFeeds(result.feeds);
            var articles = result.articles || [], html = '';
            articles.forEach(function (item) { html += rowHtml(item); });
            $('#workbenchRows').html(html || '<tr><td colspan="7" class="empty-state">没有符合条件的文章</td></tr>');
            var pagination = result.pagination || {};
            $('#workbenchSummary').text('共 ' + Number(pagination.total || 0) + ' 篇 · 第 ' + Number(pagination.current_page || 1) + ' / ' + Number(pagination.last_page || 1) + ' 页');
            var page = Number(pagination.current_page || 1), last = Number(pagination.last_page || 1);
            $('#workbenchPagination').html('<button type="button" class="page-prev" ' + (page <= 1 ? 'disabled' : '') + '>上一页</button><span class="px-2 py-1 text-sm text-gray-500">' + page + ' / ' + last + '</span><button type="button" class="page-next" ' + (page >= last ? 'disabled' : '') + '>下一页</button>');
        }).fail(function () { $('#workbenchRows').html('<tr><td colspan="7" class="empty-state">加载失败，请稍后重试</td></tr>'); });
    }
    function openArticle(id) {
        $('#articleModal').addClass('active'); $('#articleModalBody').html('<div class="loading-state">正在加载正文…</div>');
        request('/articles/workbench/articles/' + Number(id)).done(function (result) {
            var article = result.article || {};
            $('#articleModalTitle').text(article.subject || '无标题文章');
            $('#articleModalMeta').text((article.feed_name || '') + (article.category_name ? ' · ' + article.category_name : '') + ' · ' + Number(article.word_count || 0) + ' 字 · 约 ' + Number(article.estimated_read_minutes || 1) + ' 分钟');
            $('#articleModalBody').html(article.content || '<p>暂无正文</p>');
            var $row = $('#workbenchRows tr[data-sub-id="' + Number(id) + '"]');
            $row.find('.article-subject').addClass('read'); $row.find('.status-badge').attr('class', 'status-badge read').text('已读');
        }).fail(function () { $('#articleModalBody').html('<div class="empty-state">正文加载失败</div>'); });
    }
    function openAi(id) {
        state.articleSubId = id; $('#aiModal').addClass('active'); $('#aiPrompt').val(''); $('#aiContent').html('<div class="loading-state">正在生成默认辅助阅读页面…</div>');
        $('#aiModalTitle').text('AI 辅助阅读');
        setTimeout(generateAi, 0);
    }
    function updateStatus(id, status) {
        request('/articles/status/' + Number(id) + '?status=' + encodeURIComponent(status)).done(function () {
            loadData();
        });
    }
    $('#workbenchFilters').on('submit', function (event) { event.preventDefault(); state.page = 1; loadData(); });
    $('#workbenchFilters select[name="time_range"]').on('change', function () { $('#customDates').toggleClass('active', $(this).val() === 'custom'); });
    $('#resetFilters').on('click', function () { $('#workbenchFilters')[0].reset(); $('#customDates').removeClass('active'); state.page = 1; loadData(); });
    $('#workbenchRows').on('click', '.view-article', function () { openArticle($(this).closest('tr').data('sub-id')); });
    $('#workbenchRows').on('click', '.ai-article', function () { openAi($(this).closest('tr').data('sub-id')); });
    $('#workbenchRows').on('click', '.status-action', function () { updateStatus($(this).closest('tr').data('sub-id'), $(this).data('status-action')); });
    $('#workbenchPagination').on('click', '.page-prev', function () { if (!this.disabled) { state.page--; loadData(); } });
    $('#workbenchPagination').on('click', '.page-next', function () { if (!this.disabled) { state.page++; loadData(); } });
    $('[data-close-modal]').on('click', function () { $('#' + $(this).data('close-modal')).removeClass('active'); });
    $('.workbench-modal').on('click', function (event) { if (event.target === this) $(this).removeClass('active'); });
    function generateAi() {
        if (!state.articleSubId) return;
        var $button = $('#generateAiBtn'); $button.prop('disabled', true).text('生成中…'); $('#aiContent').html('<div class="loading-state">AI 正在整理文章，请稍候…</div>');
        request('/articles/workbench/articles/' + Number(state.articleSubId) + '/ai-render', { method: 'POST', contentType: 'application/x-www-form-urlencoded', data: { custom_prompt: $('#aiPrompt').val() || '' } })
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
