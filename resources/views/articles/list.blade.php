@extends('layouts.app')

@section('title', '订阅文章 - 蒙太奇阅读')
@section('description', '查看订阅源的最新文章')

<style>
    /* 订阅源页面专用样式 */
    .feed-articles-page {
        max-width: 900px;
        margin: 0 auto;
    }

    /* 页面标题区域 */
    .feed-header {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        padding: 24px 32px;
        color: white;
        border-radius: 16px 16px 0 0;
        margin-bottom: 24px;
    }

    .feed-title-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .feed-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .feed-title i {
        font-size: 1.5rem;
        opacity: 0.9;
    }

    .subscribe-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        font-size: 0.95rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .subscribe-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
    }

    .subscribe-btn i {
        margin-right: 8px;
        font-size: 0.9rem;
    }

    .feed-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-item i {
        font-size: 0.85rem;
    }

    .feed-source-link {
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: opacity 0.3s ease;
    }

    .feed-source-link:hover {
        opacity: 0.9;
        text-decoration: underline;
    }

    /* 文章卡片 */
    .article-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .article-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .article-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .article-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .article-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .article-title a:hover {
        color: #4a90e2;
    }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 0.875rem;
    }

    .article-meta a {
        color: #4a90e2;
        text-decoration: none;
        font-weight: 500;
    }

    .article-meta a:hover {
        text-decoration: underline;
    }

    .article-content {
        padding: 24px;
        position: relative;
    }

    .content-preview {
        color: #475569;
        line-height: 1.7;
        font-size: 0.95rem;
        max-height: 360px;
        overflow: hidden;
        position: relative;
        transition: max-height 0.5s ease;
    }

    .content-preview.expanded {
        max-height: 5000px !important;
    }

    .content-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 12px 0;
        transition: transform 0.3s ease;
    }

    .content-preview img.lazy {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .content-preview img.lazy.loaded {
        opacity: 1;
    }

    .content-preview img:hover {
        transform: scale(1.02);
    }

    .content-fade {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100px;
        background: linear-gradient(to bottom, rgba(255,255,255,0), white);
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .content-preview.expanded .content-fade {
        opacity: 0;
    }



    .read-more {
        text-align: center;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid #f1f5f9;
    }

    .read-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .read-more-btn:hover {
        background: #e2e8f0;
        color: #4a90e2;
        border-color: #4a90e2;
    }

    .read-more-btn i {
        transition: transform 0.3s ease;
    }

    .read-more-btn.expanded i {
        transform: rotate(180deg);
    }

    /* 空状态 */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state img {
        max-width: 160px;
        height: auto;
        margin-bottom: 24px;
        opacity: 0.8;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .empty-state-text {
        color: #64748b;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 24px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .empty-state-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 24px;
        font-size: 0.95rem;
        font-weight: 600;
        background: white;
        color: #4a90e2;
        border: 1px solid #4a90e2;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .empty-state-btn:hover {
        background: #4a90e2;
        color: white;
        transform: translateY(-2px);
    }

    /* 分页样式 */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 40px;
        padding-top: 32px;
        border-top: 1px solid #e2e8f0;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination > li {
        display: inline-block;
    }

    .pagination > li > a,
    .pagination > li > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .pagination > li > a:hover {
        background: #f1f5f9;
        border-color: #4a90e2;
        color: #4a90e2;
        transform: translateY(-2px);
    }

    .pagination > .active > span,
    .pagination > .active > a {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .pagination > .disabled > span,
    .pagination > .disabled > a {
        color: #cbd5e1;
        background: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
        transform: none !important;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .feed-header {
            padding: 20px 24px;
        }

        .feed-title {
            font-size: 1.5rem;
        }

        .feed-title-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .feed-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .article-header {
            padding: 16px 20px;
        }

        .article-content {
            padding: 20px;
        }

        .article-title {
            font-size: 1.125rem;
        }

        .article-meta {
            gap: 12px;
        }

        .empty-state-actions {
            flex-direction: column;
        }

        .empty-state-btn {
            width: 100%;
        }
    }

    /* 动画效果 */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.6s ease-out;
    }

    /* 成功消息样式 */
    .success-message {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: fadeIn 0.5s ease;
    }

    .success-message i {
        font-size: 1.2rem;
    }

    /* 错误消息样式 */
    .error-message {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid rgba(239, 68, 68, 0.2);
        margin-bottom: 24px;
        animation: fadeIn 0.5s ease;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="feed-articles-page">
            <!-- 订阅源头部 -->
            <div class="feed-header animate-fadeIn">
                <div class="feed-title-section">
                    <h1 class="feed-title" id="feedTitle">
                        <i class="fas fa-rss"></i>
                        订阅文章
                    </h1>

                    <button type="button"
                            class="subscribe-btn feed_quick_sub"
                            id="feedQuickSubBtn"
                            data-feed-id="">
                        <i class="fas fa-plus"></i>
                        订阅此源
                    </button>
                </div>

                <div class="feed-meta" id="feedMeta">
                    <div class="meta-item">
                        <i class="fas fa-link"></i>
                        来源：
                        <a href="#"
                           id="feedSourceLink"
                           class="feed-source-link"
                           target="_blank"
                           rel="noopener noreferrer">
                            -
                        </a>
                    </div>

                    <div class="meta-item">
                        <i class="fas fa-newspaper"></i>
                        <span id="feedArticleCount">文章数量：0</span>
                    </div>

                    <div class="meta-item" id="feedUpdatedAtWrap" style="display: none;">
                        <i class="fas fa-clock"></i>
                        <span id="feedUpdatedAtText"></span>
                    </div>
                </div>
            </div>

            <div id="articleLoading" class="text-center py-12 text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>加载中...
            </div>

            <div id="articleList"></div>

            <div class="pagination-wrapper" id="articlePagination" style="display: none;"></div>

            <div class="empty-state animate-fadeIn" id="articleEmptyState" style="display: none;">
                <img src="/img/new/love.png" alt="暂无文章" class="mx-auto">
                <h3 class="empty-state-title">暂时没有文章</h3>
                <p class="empty-state-text">
                    当前订阅源暂无文章，或者文章正在同步中。您可以稍后再来查看，或者浏览其他订阅源。
                </p>
                <div class="empty-state-actions">
                    <a href="/articles" class="empty-state-btn">
                        <i class="fas fa-newspaper mr-2"></i>
                        浏览其他文章
                    </a>
                    <a href="/feeds/explorer" class="empty-state-btn">
                        <i class="fas fa-compass mr-2"></i>
                        发现新订阅
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/lazyload.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };

            function escapeHtml(text) {
                return String(text || '').replace(/[&<>"']/g, function(c) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[c];
                });
            }

            function getQueryParam(name) {
                var params = new URLSearchParams(window.location.search);
                return params.get(name) || '';
            }

            function buildPageUrl(page) {
                var params = new URLSearchParams(window.location.search);
                params.set('page', String(page));
                return window.location.pathname + '?' + params.toString();
            }

            function renderFeedHeader(feed, articleCount) {
                var title = feed && feed.feed_name ? feed.feed_name : '订阅文章';
                $('#feedTitle').html('<i class="fas fa-rss"></i>' + escapeHtml(title));
                document.title = title + ' - 蒙太奇阅读';
                $('#feedArticleCount').text('文章数量：' + Number(articleCount || 0));

                var feedUrl = feed && feed.url ? feed.url : '';
                if (feedUrl) {
                    var host = '';
                    try {
                        host = new URL(feedUrl).host;
                    } catch (e) {
                        host = feedUrl;
                    }
                    $('#feedSourceLink').attr('href', feedUrl).text(host);
                } else {
                    $('#feedSourceLink').attr('href', '#').text('-');
                }

                var feedId = feed && feed.id ? Number(feed.id) : 0;
                $('#feedQuickSubBtn').attr('data-feed-id', feedId || '');
                if (feed && feed.updated_at) {
                    $('#feedUpdatedAtWrap').show();
                    $('#feedUpdatedAtText').text('最近更新：' + String(feed.updated_at).replace('T', ' ').slice(0, 16));
                } else {
                    $('#feedUpdatedAtWrap').hide();
                }
            }

            function renderArticleCards(articleSubs) {
                var html = '';
                articleSubs.forEach(function(item) {
                    var article = item && item.article ? item.article : null;
                    if (!article) return;
                    var articleId = Number(article.id || 0);
                    var subject = escapeHtml(article.subject || '无标题');
                    var published = escapeHtml(article.published || '');
                    var articleUrl = article.url ? String(article.url) : '#';
                    var feedName = escapeHtml(article.feed && article.feed.feed_name ? article.feed.feed_name : '未知来源');
                    var feedUrl = article.feed && article.feed.url ? String(article.feed.url) : '#';
                    var contentHtml = article.formatted_content ? String(article.formatted_content) : String(article.content || '');

                    html += ''
                        + '<div class="article-card animate-fadeIn">'
                        + '<div class="article-header">'
                        + '<h2 class="article-title"><a href="/article/view/' + articleId + '" title="' + subject + '">' + subject + '</a></h2>'
                        + '<div class="article-meta">'
                        + '<span><i class="far fa-clock"></i> ' + published + '</span><span>•</span>'
                        + '<a href="' + escapeHtml(articleUrl) + '" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt"></i> 阅读原文</a><span>•</span>'
                        + '<span>来源：<a href="' + escapeHtml(feedUrl) + '" target="_blank" rel="noopener noreferrer">' + feedName + '</a></span>'
                        + '</div></div>'
                        + '<div class="article-content">'
                        + '<div class="content-preview" id="content-' + articleId + '">' + contentHtml + '<div class="content-fade"></div></div>'
                        + '<div class="read-more"><button type="button" class="read-more-btn" data-article-id="' + articleId + '"><i class="fas fa-chevron-down"></i> 阅读更多</button></div>'
                        + '</div></div>';
                });
                $('#articleList').html(html);
            }

            function renderPagination(pagination) {
                var $wrap = $('#articlePagination');
                if (!pagination || (!pagination.next_page_url && !pagination.prev_page_url)) {
                    $wrap.hide().html('');
                    return;
                }

                var currentPage = Number(pagination.current_page || 1);
                var prevBtn = pagination.prev_page_url
                    ? '<a href="' + buildPageUrl(Math.max(1, currentPage - 1)) + '" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left mr-1"></i>上一页</a>'
                    : '<span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg"><i class="fas fa-chevron-left mr-1"></i>上一页</span>';
                var nextBtn = pagination.next_page_url
                    ? '<a href="' + buildPageUrl(currentPage + 1) + '" class="btn btn-secondary btn-sm">下一页<i class="fas fa-chevron-right ml-1"></i></a>'
                    : '<span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg">下一页<i class="fas fa-chevron-right ml-1"></i></span>';

                $wrap.show().html('<div class="flex items-center justify-between w-full"><div class="text-sm text-gray-500">当前第 ' + currentPage + ' 页</div><div class="flex gap-1">' + prevBtn + nextBtn + '</div></div>');
            }

            function loadFeedArticlesByApi() {
                var feedId = getQueryParam('feed_id');
                if (!feedId) {
                    $('#articleLoading').hide();
                    $('#articleEmptyState').show();
                    return;
                }

                var page = Number(getQueryParam('page') || 1);
                var pageCount = Number(getQueryParam('page_count') || 20);

                apiRequest('GET', '/articles/list', {
                    feed_id: feedId,
                    page_count: pageCount,
                    page: page
                }).then(function(resp) {
                    if (!resp || resp.code !== 9999 || !resp.result) {
                        throw new Error((resp && resp.msg) ? resp.msg : '加载失败');
                    }

                    var result = resp.result || {};
                    var articleSubs = Array.isArray(result.articles) ? result.articles : [];
                    renderFeedHeader(result.feed || null, articleSubs.length);

                    if (!articleSubs.length) {
                        $('#articleList').empty();
                        $('#articlePagination').hide().empty();
                        $('#articleEmptyState').show();
                    } else {
                        $('#articleEmptyState').hide();
                        renderArticleCards(articleSubs);
                        renderPagination(result.pagination || null);
                    }

                    $('#articleLoading').hide();
                    initReadMoreButtons();

                    if (typeof $.fn.lazyload === 'function') {
                        $("img.lazy").lazyload({
                            effect: "fadeIn",
                            threshold: 200
                        });
                    }
                }).catch(function(error) {
                    $('#articleLoading').hide();
                    $('#articleList').empty();
                    $('#articlePagination').hide().empty();
                    $('#articleEmptyState').show();
                    showNotification(error && error.message ? error.message : '加载失败，请稍后重试', 'error');
                });
            }

            // 快速订阅功能（委托绑定，兼容动态渲染）
            $(document).on('click', '.feed_quick_sub', function() {
                var feed_id = $(this).data('feed-id');
                var button = $(this);
                var originalText = button.html();
                if (!feed_id) {
                    showNotification('订阅源信息缺失', 'error');
                    return;
                }

                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin mr-2"></i>处理中...');

                apiRequest('POST', '/feeds/quickstore', {"feed_id": feed_id}).then(function(result_arr) {
                    if (!result_arr || result_arr.code != 9999) {
                        showNotification(result_arr && result_arr.msg ? result_arr.msg : '订阅失败，请重试', 'error');
                        button.prop('disabled', false);
                        button.html(originalText);
                    } else {
                        showNotification(result_arr.msg || '订阅成功', 'success');
                        button.html('<i class="fas fa-check mr-2"></i>已订阅');
                        button.css({
                            'background': 'rgba(16, 185, 129, 0.2)',
                            'border-color': 'rgba(16, 185, 129, 0.3)'
                        });

                        setTimeout(function() {
                            button.prop('disabled', false);
                            button.html(originalText);
                            button.css({
                                'background': 'rgba(255, 255, 255, 0.2)',
                                'border-color': 'rgba(255, 255, 255, 0.3)'
                            });
                        }, 3000);
                    }
                }).catch(function() {
                    showNotification('订阅失败，请重试', 'error');
                    button.prop('disabled', false);
                    button.html(originalText);
                });
            });

            // 阅读更多功能
            function initReadMoreButtons() {
                $(".read-more-btn").off('click').on('click', function() {
                    var articleId = $(this).data('article-id');
                    var $content = $("#content-" + articleId);
                    var $button = $(this);

                    $content.toggleClass('expanded');

                    if ($content.hasClass('expanded')) {
                        $button.html('<i class="fas fa-chevron-up"></i> 收起内容');
                        $button.addClass('expanded');

                        // 触发图片懒加载
                        $content.find('img.lazy').each(function() {
                            var $img = $(this);
                            if (typeof $.fn.lazyload === 'function') {
                                $img.lazyload();
                            }
                        });
                    } else {
                        $button.html('<i class="fas fa-chevron-down"></i> 阅读更多');
                        $button.removeClass('expanded');
                    }

                    // 检测是否需要显示阅读更多按钮
                    checkContentHeight($content, $button);
                });

                // 初始化时检查内容高度
                $(".content-preview").each(function() {
                    var $content = $(this);
                    var $button = $content.siblings('.read-more').find('.read-more-btn');
                    checkContentHeight($content, $button);
                });
            }

            // 检测内容高度，决定是否显示阅读更多按钮
            function checkContentHeight($content, $button) {
                var originalHeight = $content[0].scrollHeight;
                var maxHeight = 360; // 与CSS中的max-height一致

                if (originalHeight <= maxHeight) {
                    // 内容高度小于等于最大高度，不需要阅读更多按钮
                    $button.parent().hide();
                    $content.addClass('expanded');
                    $content.find('.content-fade').remove();
                } else {
                    // 内容需要折叠
                    if (!$content.find('.content-fade').length && !$content.hasClass('expanded')) {
                        $content.append('<div class="content-fade"></div>');
                    }
                    $button.parent().show();
                }
            }

            // 卡片悬停效果
            $('.article-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-4px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 图片点击预览（如果有大图）
            $(document).on('click', '.content-preview img', function() {
                var src = $(this).attr('src');
                if (src && !src.includes('unable_img.png')) {
                    window.open(src, '_blank');
                }
            });

            // 显示通知
            function showNotification(message, type = 'success') {
                var bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                var notification = $(
                    '<div class="fixed top-4 right-4 z-50 max-w-sm w-full animate-fadeIn">' +
                    '<div class="' + bgColor + ' text-white p-4 rounded-lg shadow-lg flex items-center justify-between transform translate-x-full transition-transform duration-300">' +
                    '<div class="flex items-center">' +
                    '<i class="fas ' + icon + ' mr-3"></i>' +
                    '<span class="text-sm">' + message + '</span>' +
                    '</div>' +
                    '<button class="text-white hover:text-gray-200 ml-4" onclick="$(this).closest(\'.fixed\').remove()">' +
                    '<i class="fas fa-times"></i>' +
                    '</button>' +
                    '</div>' +
                    '</div>'
                );

                $('body').append(notification);

                // 显示通知
                setTimeout(function() {
                    notification.find('div:first').removeClass('translate-x-full');
                }, 10);

                // 3秒后自动隐藏
                setTimeout(function() {
                    notification.find('div:first').addClass('translate-x-full');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 3000);
            }

            // 初始化功能
            loadFeedArticlesByApi();
        });
    </script>
@endsection
