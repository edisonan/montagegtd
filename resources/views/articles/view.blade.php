@extends('layouts.app')

@section('title', '文章阅读 - 蒙太奇')

@section('description', '文章阅读详情页')

<style>
    /* 文章阅读页面专用样式 */
    .article-reader-page {
        max-width: 800px;
        margin: 0 auto;
    }

    /* 文章容器 */
    .article-container {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .article-container:hover {
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1);
    }

    /* 文章头部 */
    .article-header {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        padding: 24px 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .article-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        opacity: 0.3;
    }

    .article-meta-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .source-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .source-link {
        color: white;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: opacity 0.3s ease;
    }

    .source-link:hover {
        opacity: 0.8;
    }

    .article-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .header-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .header-action-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    .article-title {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.3;
        margin: 0 0 12px 0;
        position: relative;
        z-index: 1;
    }

    .publish-info {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 0.9rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    /* 文章内容区域 */
    .article-content-wrapper {
        padding: 32px;
    }

    .article-content {
        color: #334155;
        line-height: 1.8;
        font-size: 1.05rem;
        word-wrap: break-word;
    }

    .article-content p {
        margin-bottom: 1.5em;
    }

    .article-content h2,
    .article-content h3,
    .article-content h4 {
        color: #1e293b;
        margin: 2em 0 1em 0;
        font-weight: 600;
        line-height: 1.3;
    }

    .article-content h2 {
        font-size: 1.5rem;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 8px;
    }

    .article-content h3 {
        font-size: 1.25rem;
    }

    .article-content h4 {
        font-size: 1.125rem;
    }

    .article-content blockquote {
        border-left: 4px solid #4a90e2;
        margin: 1.5em 0;
        padding: 12px 20px;
        background: #f8fafc;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #475569;
    }

    .article-content code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.9em;
        color: #dc2626;
    }

    .article-content pre {
        background: #1e293b;
        color: #e2e8f0;
        padding: 16px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1.5em 0;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.9em;
        line-height: 1.5;
    }

    .article-content ul,
    .article-content ol {
        padding-left: 1.5em;
        margin: 1em 0;
    }

    .article-content li {
        margin-bottom: 0.5em;
    }

    /* 图片优化 */
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5em 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .article-content img:hover {
        transform: scale(1.02);
    }

    .article-content img.lazy {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .article-content img.lazy.loaded {
        opacity: 1;
    }

    /* 标注功能 */
    .marker-tool {
        position: fixed;
        display: none;
        z-index: 1000;
        pointer-events: none;
        transition: transform 0.2s ease;
    }

    .marker-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        cursor: pointer;
        pointer-events: auto;
        transition: all 0.3s ease;
    }

    .marker-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
    }

    .marker-tool.active {
        display: block;
        animation: popIn 0.3s ease;
    }

    @keyframes popIn {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* 文本选择样式 */
    .article-content ::selection {
        background: rgba(59, 130, 246, 0.3);
        color: inherit;
    }

    /* 文章底部 */
    .article-footer {
        padding: 24px 32px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        border-radius: 0 0 16px 16px;
    }

    .footer-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .original-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .original-link:hover {
        background: #f1f5f9;
        border-color: #4a90e2;
        color: #4a90e2;
        transform: translateY(-2px);
    }

    .continue-reading {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #4a90e2;
        color: white;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .continue-reading:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .share-section {
        flex: 1;
        text-align: center;
    }

    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .share-btn:hover {
        background: #f1f5f9;
        border-color: #4a90e2;
        color: #4a90e2;
        transform: translateY(-2px);
    }

    /* 分享菜单 */
    .share-menu {
        position: absolute;
        bottom: 120%;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        padding: 16px;
        z-index: 100;
        display: none;
        min-width: 220px;
    }

    .share-menu.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .share-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .share-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        color: #475569;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .share-option:hover {
        background: #f1f5f9;
        color: #4a90e2;
    }

    .share-option i {
        width: 20px;
        text-align: center;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .article-reader-page {
            padding: 0 16px;
        }

        .article-header {
            padding: 20px 24px;
        }

        .article-title {
            font-size: 1.5rem;
        }

        .article-meta-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .article-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .article-content-wrapper {
            padding: 24px;
        }

        .article-footer {
            padding: 20px 24px;
        }

        .footer-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .original-link,
        .continue-reading,
        .share-btn {
            width: 100%;
            justify-content: center;
        }

        .article-content img {
            border-radius: 6px;
        }

        .share-menu {
            right: -50%;
            min-width: 200px;
        }
    }

    @media (max-width: 480px) {
        .article-header {
            padding: 16px 20px;
        }

        .article-title {
            font-size: 1.25rem;
        }

        .article-content-wrapper {
            padding: 20px;
        }

        .article-content {
            font-size: 1rem;
        }

        .publish-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }

    /* 动画效果 */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="article-reader-page">
            <!-- 文章容器 -->
            <div class="article-container animate-fadeIn">
                <!-- 文章头部 -->
                <div class="article-header">
                    <div class="article-meta-bar">
                        <div class="source-info">
                            <a href="#" id="article_feed_link" class="source-link">
                                <i class="fas fa-rss"></i>
                                <span id="article_feed_name">来源</span>
                            </a>
                        </div>

                        <div class="article-actions">
                            <div class="relative share-container">
                                <a href="javascript:void(0);" class="header-action-btn share-trigger">
                                    <i class="fas fa-share-alt"></i>
                                    分享
                                </a>

                                <div class="share-menu">
                                    <div class="share-options">
                                        <a href="javascript:void(0);"
                                           class="share-option icon-heart"
                                           data-title=""
                                           data-url=""
                                           data-id="">
                                            <i class="fas fa-heart"></i>
                                            <span>记录想法</span>
                                        </a>
                                        <!-- 其他分享选项 -->
                                    </div>
                                </div>
                            </div>

                            <a href="javascript:void(0);"
                               id="article_quick_sub"
                               feed_id=""
                               class="header-action-btn feed_quick_sub hidden">
                                <i class="fas fa-plus"></i>
                                添加订阅
                            </a>

                            <a href="{{ url('/article/'.$article->id.'/ai-render') }}" class="header-action-btn">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                AI可视化
                            </a>

                            <a href="{{ url('/articles') }}" class="header-action-btn">
                                <i class="fas fa-arrow-right"></i>
                                继续阅读
                            </a>
                        </div>
                    </div>

                    <h1 class="article-title" id="article_subject">文章标题</h1>

                    <div class="publish-info">
                    <span>
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span id="article_published">-</span>
                    </span>
                        <span>
                        <i class="fas fa-external-link-alt mr-1"></i>
                        <a href="#" id="article_origin_top" target="_blank" class="text-white hover:underline">
                            原文链接
                        </a>
                    </span>
                        <span>
                        <i class="fas fa-globe mr-1"></i>
                        <a href="#" id="article_feed_host_link" target="_blank" class="text-white hover:underline">
                            <span id="article_feed_host">-</span>
                        </a>
                    </span>
                    </div>
                </div>

                <!-- 文章内容 -->
                <div class="article-content-wrapper">
                    <div class="article-content" id="articleContent">
                        <div class="text-gray-500">加载中...</div>
                    </div>
                </div>

                <!-- 文章底部 -->
                <div class="article-footer">
                    <div class="footer-actions">
                        <a href="#" id="article_origin_bottom" target="_blank" class="original-link">
                            <i class="fas fa-external-link-alt"></i>
                            查看原文
                        </a>

                        <div class="share-section">
                            <div class="relative inline-block">
                                <a href="javascript:void(0);" class="share-btn share-trigger">
                                    <i class="fas fa-share-alt"></i>
                                    分享文章
                                </a>
                            </div>
                        </div>

                        <a href="javascript:void(0);" id="article_mindmap_btn" class="share-btn">
                            <i class="fas fa-brain"></i>
                            AI生成导图
                        </a>

                        <a href="{{ url('/article/'.$article->id.'/ai-render') }}" class="share-btn">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            AI可视化
                        </a>

                        <a href="{{ url('/articles') }}" class="continue-reading">
                            <i class="fas fa-book-reader"></i>
                            继续阅读其他文章
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 标注工具 -->
    <div class="marker-tool" id="markerTool">
        <div class="marker-icon" id="markerBtn" title="标记选中文本">
            <i class="fas fa-highlighter"></i>
        </div>
    </div>

    <!-- 外部脚本 -->
    <link rel="stylesheet" href="/css/share.min.css">
    <script src="/js/lazyload.min.js"></script>
    <script src="/js/social-share.js"></script>
    <script src="/js/qrcode.js"></script>
    <div id="articleMindmapModal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" id="articleMindmapBackdrop"></div>
        <div class="absolute inset-0 p-3 sm:p-6 overflow-y-auto">
            <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">AI生成思维导图</h3>
                        <p class="text-xs text-gray-500 mt-1" id="articleMindmapTitle">-</p>
                    </div>
                    <button type="button" class="header-action-btn" id="articleMindmapCloseBtn" title="关闭">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <div class="text-sm text-gray-600" id="articleMindmapStatus">准备生成导图...</div>
                </div>
                <div class="p-5">
                    <div id="articleMindmapLoading" class="text-center py-10 hidden">
                        <i class="fas fa-spinner fa-spin text-gray-500 text-2xl"></i>
                        <p class="text-sm text-gray-500 mt-2">AI正在分析文章并生成结构...</p>
                    </div>
                    <div id="articleMindmapPreviewWrap" class="hidden">
                        <div class="article-mind-root" id="articleMindmapPreview"></div>
                    </div>
                    <div id="articleMindmapError" class="hidden text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="text-xs text-gray-500">保存后可在思维导图页继续编辑</div>
                    <div class="flex items-center gap-2">
                        <a href="javascript:void(0);" id="articleMindmapEditLink" class="share-btn hidden" target="_blank">
                            <i class="fas fa-pen mr-1"></i>打开编辑
                        </a>
                        <a href="javascript:void(0);" class="share-btn" id="articleMindmapRegenerateBtn">
                            <i class="fas fa-sync-alt mr-1"></i>重新生成
                        </a>
                        <a href="javascript:void(0);" class="continue-reading" id="articleMindmapSaveBtn">
                            <i class="fas fa-save mr-1"></i>一键保存
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .article-mind-root {
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f8fafc;
            max-height: 60vh;
            overflow: auto;
        }
        .article-mind-node {
            margin: 8px 0;
            padding-left: 14px;
            border-left: 2px dashed #cbd5e1;
        }
        .article-mind-topic {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.4;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .article-mind-node.level-0 > .article-mind-topic {
            background: #eff6ff;
            border-color: #93c5fd;
            font-weight: 600;
            font-size: 14px;
        }
        .article-mind-children {
            margin-top: 6px;
            margin-left: 10px;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };
            var currentArticleId = '';
            var currentArticle = null;
            var articleMindState = {
                title: '',
                referText: '',
                tree: null,
                isGenerating: false,
                isSaving: false
            };

            function getArticleIdFromPath() {
                var parts = window.location.pathname.split('/').filter(Boolean);
                return parts.length ? parts[parts.length - 1] : '';
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text == null ? '' : String(text);
                return div.innerHTML;
            }

            function getHostFromUrl(url) {
                try {
                    return new URL(url).host || '-';
                } catch (e) {
                    return '-';
                }
            }

            function populateArticle(article, isFeed) {
                currentArticle = article;
                currentArticleId = article && article.id ? String(article.id) : '';

                var subject = (article && article.subject) ? article.subject : '文章标题';
                document.title = subject + ' - 蒙太奇';
                $('#article_subject').text(subject);
                $('#article_published').text(article && article.published ? article.published : '-');

                var articleUrl = article && article.url ? article.url : '#';
                $('#article_origin_top').attr('href', articleUrl);
                $('#article_origin_bottom').attr('href', articleUrl);

                var feed = article && article.feed ? article.feed : {};
                var feedId = feed.id || '';
                var feedName = feed.feed_name || '来源';
                var feedUrl = feed.url || '#';
                var feedHost = getHostFromUrl(feedUrl);

                $('#article_feed_name').text(feedName);
                $('#article_feed_link').attr('href', '/article/list?feed_id=' + encodeURIComponent(feedId));
                $('#article_feed_host').text(feedHost);
                $('#article_feed_host_link').attr('href', feedUrl);

                var articleViewUrl = window.location.origin + '/article/view/' + currentArticleId;
                $('.icon-heart')
                    .attr('data-title', subject + ' From:' + articleViewUrl)
                    .attr('data-url', articleViewUrl)
                    .attr('data-id', currentArticleId);

                var contentHtml = article && article.formatted_content
                    ? article.formatted_content
                    : (article && article.content ? article.content : '<div class="text-gray-500">暂无内容</div>');
                $('#articleContent').html(contentHtml);

                if (isFeed) {
                    $('#article_quick_sub').addClass('hidden').attr('feed_id', '');
                } else {
                    $('#article_quick_sub').removeClass('hidden').attr('feed_id', feedId);
                }
            }

            function loadArticle() {
                var articleId = getArticleIdFromPath();
                if (!articleId) {
                    showNotification('未找到文章ID', 'error');
                    return;
                }
                apiRequest('GET', '/articles/' + articleId, {}).then(function(resp) {
                    if (!(resp && resp.code === 9999 && resp.result && resp.result.article)) {
                        showNotification((resp && resp.msg) || '加载文章失败', 'error');
                        return;
                    }
                    populateArticle(resp.result.article, !!resp.result.is_feed);
                }).catch(function() {
                    showNotification('加载文章失败，请稍后重试', 'error');
                });
            }

            function setMindmapStatus(message) {
                $('#articleMindmapStatus').text(message || '');
            }

            function showMindmapModal() {
                $('#articleMindmapModal').removeClass('hidden');
            }

            function hideMindmapModal() {
                $('#articleMindmapModal').addClass('hidden');
            }

            function sanitizeTopic(text, fallbackText) {
                var topic = String(text || '').replace(/\s+/g, ' ').trim();
                if (!topic) topic = String(fallbackText || '导图节点');
                if (topic.length > 36) topic = topic.slice(0, 36);
                return topic || '导图节点';
            }

            function sanitizeRemark(text) {
                var remark = String(text || '').replace(/\s+/g, ' ').trim();
                if (!remark) return '';
                if (remark.length > 180) remark = remark.slice(0, 180);
                return remark;
            }

            function normalizeMindTree(input, depth) {
                var currentDepth = Number(depth || 0);
                if (currentDepth > 3) return null;
                if (typeof input === 'string') return { topic: sanitizeTopic(input), content: '', children: [] };
                if (!input || typeof input !== 'object') return null;
                var topic = sanitizeTopic(input.topic || input.title || input.name || input.text, '导图节点');
                var content = sanitizeRemark(input.content || input.remark || input.note || input.summary || '');
                var rawChildren = input.children || input.nodes || input.items || [];
                var children = [];
                if (Array.isArray(rawChildren)) {
                    for (var i = 0; i < rawChildren.length; i++) {
                        var childNode = normalizeMindTree(rawChildren[i], currentDepth + 1);
                        if (childNode) children.push(childNode);
                        if (children.length >= 8) break;
                    }
                }
                return { topic: topic, content: content, children: children };
            }

            function extractJsonObject(text) {
                var raw = String(text || '').trim();
                if (!raw) return null;
                raw = raw.replace(/```json/ig, '').replace(/```/g, '').trim();
                var start = raw.indexOf('{');
                var end = raw.lastIndexOf('}');
                if (start === -1 || end === -1 || end <= start) return null;
                try { return JSON.parse(raw.slice(start, end + 1)); } catch (e) { return null; }
            }

            function buildFallbackMindTree(title, referText) {
                var cleanTitle = sanitizeTopic(title, '文章导图');
                var text = String(referText || '').replace(/\s+/g, ' ').trim();
                var parts = text.split(/[。！？；\n]/).map(function(item) { return item.trim(); }).filter(Boolean);
                var children = [];
                for (var i = 0; i < Math.min(5, parts.length); i++) {
                    children.push({ topic: sanitizeTopic(parts[i], '要点' + (i + 1)), content: sanitizeRemark(parts[i]), children: [] });
                }
                if (children.length === 0) {
                    children.push({ topic: '核心观点', content: '', children: [] });
                    children.push({ topic: '关键细节', content: '', children: [] });
                    children.push({ topic: '可行动事项', content: '', children: [] });
                }
                return { topic: cleanTitle, content: '', children: children };
            }

            function renderMindTree(node, depth) {
                var level = Number(depth || 0);
                var html = '<div class="article-mind-node level-' + level + '"><div class="article-mind-topic">' + escapeHtml(node.topic || '') + '</div>';
                if (node.content) {
                    html += '<div class="text-xs text-gray-600 mt-1 leading-5">' + escapeHtml(node.content) + '</div>';
                }
                if (Array.isArray(node.children) && node.children.length > 0) {
                    html += '<div class="article-mind-children">';
                    for (var i = 0; i < node.children.length; i++) {
                        html += renderMindTree(node.children[i], level + 1);
                    }
                    html += '</div>';
                }
                html += '</div>';
                return html;
            }

            function updateMindmapPreview(tree) {
                if (!tree) {
                    $('#articleMindmapPreviewWrap').addClass('hidden');
                    $('#articleMindmapPreview').empty();
                    return;
                }
                $('#articleMindmapPreview').html(renderMindTree(tree, 0));
                $('#articleMindmapPreviewWrap').removeClass('hidden');
            }

            async function ensureAiSession() {
                if (typeof window.createNewSession === 'function') {
                    return await window.createNewSession('builtin_common');
                }
                const response = await window.taskApiFetch('/api/v2/llm/sessions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ agent_id: 'builtin_common', title: '文章导图生成' })
                });
                const result = await response.json();
                if (!response.ok || !result || result.success !== true || !result.data || !result.data.id) {
                    throw new Error((result && result.message) ? result.message : '创建AI会话失败');
                }
                return result.data.id;
            }

            async function requestMindTreeFromAi(title, referText) {
                var query = [
                    '请根据我提供的文章标题和内容，生成思维导图。',
                    '只返回JSON，不要解释，不要markdown。',
                    '格式: {"topic":"根节点","content":"节点备注","children":[{"topic":"一级节点","content":"备注","children":[{"topic":"二级节点","content":"备注","children":[]}]}]}',
                    '要求: 中文；层级不超过3层；一级节点3-6个；topic简洁；关键节点要给content备注（20-80字，可为空字符串）。'
                ].join('\n');
                var sessionId = await ensureAiSession();
                var response = await window.taskApiFetch('/api/v2/llm/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: sessionId,
                        refer_text: ('文章标题：' + title + '\n\n文章内容：\n' + String(referText || '')).slice(0, 6000),
                        query: query
                    })
                });
                if (!response.ok) throw new Error('AI请求失败，状态码: ' + response.status);
                if (!String(response.headers.get('content-type') || '').includes('text/event-stream')) {
                    throw new Error('AI返回格式异常');
                }
                var reader = response.body.getReader();
                var decoder = new TextDecoder();
                var buffer = '';
                var finalText = '';
                var reasoningText = '';
                var doneSignal = false;
                while (!doneSignal) {
                    var chunk = await reader.read();
                    if (chunk.done) break;
                    buffer += decoder.decode(chunk.value, { stream: true });
                    var lines = buffer.split('\n');
                    buffer = lines.pop() || '';
                    for (var i = 0; i < lines.length; i++) {
                        var line = String(lines[i] || '').trim();
                        if (!line || line.indexOf('data:') !== 0) continue;
                        var payload = line.slice(5).trim();
                        if (payload === '[DONE]') { doneSignal = true; break; }
                        if (!payload || payload.charAt(0) !== '{') continue;
                        try {
                            var data = JSON.parse(payload);
                            var delta = data && data.choices && data.choices[0] && data.choices[0].delta ? data.choices[0].delta : {};
                            var piece = '';
                            if (typeof delta.content === 'string' && delta.content) piece = delta.content;
                            else if (typeof data.content === 'string' && data.content) piece = data.content;
                            else if (data.message && typeof data.message.content === 'string' && data.message.content) piece = data.message.content;
                            if (piece) finalText += piece;
                            if (typeof delta.reasoning === 'string' && delta.reasoning) reasoningText += delta.reasoning;
                        } catch (e) {}
                    }
                }
                console.info('[ArticleMindmap] AI raw content output:', finalText);
                if (reasoningText) {
                    console.info('[ArticleMindmap] AI reasoning output:', reasoningText);
                }
                var parsed = extractJsonObject(finalText);
                if (!parsed) {
                    var rawPreview = String(finalText || '').replace(/\s+/g, ' ').trim().slice(0, 800);
                    throw new Error('AI输出无法解析为导图JSON。原始输出预览：' + (rawPreview || '[empty]'));
                }
                var normalized = normalizeMindTree(parsed, 0);
                if (!normalized || !Array.isArray(normalized.children) || normalized.children.length === 0) {
                    throw new Error('AI导图结构为空。请检查AI输出结构是否包含children节点。');
                }
                if (!normalized.topic || normalized.topic === '导图节点') {
                    normalized.topic = sanitizeTopic(title, '文章导图');
                }
                return normalized;
            }

            async function createMindNode(name, parentMindId, content, sourceType, sourceId) {
                var payload = {
                    name: sanitizeTopic(name, '导图节点'),
                    content: sanitizeRemark(content || '')
                };
                if (parentMindId) payload.parent_mind_id = parentMindId;
                if (sourceType) payload.source_type = sourceType;
                if (sourceId) payload.source_id = Number(sourceId);
                var result = await apiRequest('POST', '/minds', payload);
                if (!result || result.code !== 9999 || !result.result || !result.result.id) {
                    throw new Error((result && result.msg) ? result.msg : '保存导图节点失败');
                }
                return Number(result.result.id);
            }

            async function saveMindTreeToServer(tree) {
                var sourceType = 'article';
                var sourceId = Number(currentArticleId || 0);
                var rootId = await createMindNode(tree.topic || '文章导图', 0, tree.content || '', sourceType, sourceId);
                async function createChildren(parentId, children) {
                    if (!Array.isArray(children) || children.length === 0) return;
                    for (var i = 0; i < children.length; i++) {
                        var child = children[i];
                        var childId = await createMindNode(child.topic || ('节点' + (i + 1)), parentId, child.content || '', sourceType, sourceId);
                        await createChildren(childId, child.children || []);
                    }
                }
                await createChildren(rootId, tree.children || []);
                return rootId;
            }

            async function generateArticleMindmap() {
                if (articleMindState.isGenerating) return;
                articleMindState.isGenerating = true;
                $('#articleMindmapEditLink').addClass('hidden').attr('href', 'javascript:void(0);');
                $('#articleMindmapError').addClass('hidden').text('');
                $('#articleMindmapLoading').removeClass('hidden');
                updateMindmapPreview(null);
                setMindmapStatus('AI生成中，请稍候...');
                try {
                    var tree = await requestMindTreeFromAi(articleMindState.title, articleMindState.referText);
                    articleMindState.tree = tree;
                    updateMindmapPreview(tree);
                    setMindmapStatus('导图生成完成，可保存后继续编辑。');
                } catch (error) {
                    var fallbackTree = buildFallbackMindTree(articleMindState.title, articleMindState.referText);
                    articleMindState.tree = fallbackTree;
                    updateMindmapPreview(fallbackTree);
                    $('#articleMindmapError').removeClass('hidden').text('AI生成失败，已使用降级结构。失败原因：' + (error && error.message ? error.message : '未知错误'));
                    setMindmapStatus('已展示降级导图，可直接保存后再补充。');
                } finally {
                    $('#articleMindmapLoading').addClass('hidden');
                    articleMindState.isGenerating = false;
                }
            }

            // 图片懒加载
            if (typeof $.fn.lazyload === 'function') {
                $("img.lazy").lazyload({
                    effect: "fadeIn",
                    threshold: 200
                });
            }

            loadArticle();

            // 快速订阅
            $(".feed_quick_sub").click(function() {
                var feed_id = $(this).attr('feed_id');
                var button = $(this);
                var originalText = button.html();

                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin"></i> 处理中...');

                apiRequest('POST', '/feeds/quickstore', {"feed_id": feed_id}).then(function(result_arr) {
                    if (result_arr.code != 9999) {
                        button.prop('disabled', false);
                        button.html(originalText);
                        showNotification(result_arr.msg, 'error');
                    } else {
                        button.html('<i class="fas fa-check"></i> 已订阅');
                        showNotification(result_arr.msg, 'success');

                        // 3秒后恢复按钮
                        setTimeout(function() {
                            button.prop('disabled', false);
                            button.html(originalText);
                        }, 3000);
                    }
                }).catch(function() {
                    button.prop('disabled', false);
                    button.html(originalText);
                    showNotification('订阅失败，请重试', 'error');
                });
            });

            // 记录想法
            $(".icon-heart").click(function() {
                var title = $(this).data('title');
                var url = $(this).data('url');
                var id = $(this).data('id');
                window.open('/notes?source_type=3&source_id=' + id + '&add_content=' + encodeURIComponent(url));
            });

            // 标注功能
            var markerTool = document.getElementById("markerTool");
            var isSelecting = false;
            var selectionTimeout;

            // 文本选择处理
            document.addEventListener('mouseup', function(event) {
                var selection = window.getSelection();
                var selectedText = selection.toString().trim();

                // 清除之前的定时器
                clearTimeout(selectionTimeout);

                if (selectedText.length >= 10) {
                    isSelecting = true;

                    // 获取选择位置
                    var range = selection.getRangeAt(0);
                    var rect = range.getBoundingClientRect();

                    // 显示标注工具
                    selectionTimeout = setTimeout(function() {
                        if (isSelecting) {
                            markerTool.style.left = (rect.left + rect.width / 2 - 18) + 'px';
                            markerTool.style.top = (rect.top - 50) + 'px';
                            markerTool.classList.add('active');
                        }
                    }, 100);
                } else {
                    hideMarkerTool();
                }
            });

            // 点击其他地方隐藏标注工具
            document.addEventListener('mousedown', function(event) {
                if (!event.target.closest('#markerTool') && !isTextSelectionEvent(event)) {
                    hideMarkerTool();
                }
            });

            // 点击标注按钮
            document.getElementById('markerBtn').addEventListener('click', function() {
                var selection = window.getSelection();
                var selectedText = selection.toString().trim();

                if (selectedText.length >= 10) {
                    apiRequest('POST', '/articles/mark', {
                        "article_id": currentArticleId,
                        "content": selectedText
                    }).then(function(result_arr) {
                        if (result_arr.code != 9999) {
                            showNotification(result_arr.msg, 'error');
                        } else {
                            showNotification(result_arr.msg, 'success');
                            hideMarkerTool();

                            // 清除选择
                            window.getSelection().removeAllRanges();
                        }
                    }).catch(function() {
                        showNotification('标注失败，请重试', 'error');
                    });
                } else {
                    showNotification('请选择至少10个字符', 'error');
                }
            });

            // 判断是否是文本选择事件
            function isTextSelectionEvent(event) {
                var selection = window.getSelection();
                return selection.toString().length > 0;
            }

            // 隐藏标注工具
            function hideMarkerTool() {
                markerTool.classList.remove('active');
                isSelecting = false;
            }

            // 分享功能
            $(".share-trigger").click(function(e) {
                e.stopPropagation();
                var $shareMenu = $(this).siblings('.share-menu');

                // 关闭其他分享菜单
                $('.share-menu').not($shareMenu).removeClass('active');

                // 切换当前分享菜单
                $shareMenu.toggleClass('active');
            });

            // 点击外部关闭分享菜单
            $(document).click(function(e) {
                if (!$(e.target).closest('.share-container').length &&
                    !$(e.target).closest('.share-menu').length) {
                    $('.share-menu').removeClass('active');
                }
            });

            // 页面滚动时隐藏分享菜单
            $(window).scroll(function() {
                $('.share-menu').removeClass('active');
                hideMarkerTool();
            });

            $('#article_mindmap_btn').on('click', function() {
                var title = (currentArticle && currentArticle.subject) ? String(currentArticle.subject) : $('#article_subject').text().trim();
                var referText = $('#articleContent').text().replace(/\s+/g, ' ').trim();
                if (!referText) {
                    showNotification('未找到可用于生成导图的文章内容', 'error');
                    return;
                }
                articleMindState.title = title || '文章导图';
                articleMindState.referText = referText.slice(0, 12000);
                articleMindState.tree = null;
                $('#articleMindmapTitle').text(articleMindState.title);
                showMindmapModal();
                generateArticleMindmap();
            });

            $('#articleMindmapBackdrop, #articleMindmapCloseBtn').on('click', function() {
                hideMindmapModal();
            });

            $('#articleMindmapRegenerateBtn').on('click', function() {
                if (!articleMindState.title || !articleMindState.referText) {
                    showNotification('缺少文章内容，无法生成导图', 'error');
                    return;
                }
                generateArticleMindmap();
            });

            $('#articleMindmapSaveBtn').on('click', async function() {
                if (articleMindState.isSaving) return;
                if (!articleMindState.tree) {
                    showNotification('请先生成导图', 'error');
                    return;
                }
                articleMindState.isSaving = true;
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>保存中');
                setMindmapStatus('正在保存导图节点...');
                try {
                    var rootMindId = await saveMindTreeToServer(articleMindState.tree);
                    $('#articleMindmapEditLink').removeClass('hidden').attr('href', '/mind/' + rootMindId);
                    setMindmapStatus('保存成功，已可继续编辑。');
                    showNotification('导图已保存，可继续编辑', 'success');
                } catch (error) {
                    setMindmapStatus('保存失败，请重试。');
                    showNotification((error && error.message) ? error.message : '保存导图失败', 'error');
                } finally {
                    articleMindState.isSaving = false;
                    $btn.html(originalHtml);
                }
            });

            // ESC键关闭分享菜单
            $(document).keydown(function(e) {
                if (e.key === 'Escape') {
                    $('.share-menu').removeClass('active');
                    hideMarkerTool();
                }
            });

            // 通知函数
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

            // 文章内容图片点击放大
            $('#articleContent').on('click', 'img', function() {
                var $img = $(this);
                var src = $img.attr('src');

                // 创建放大视图
                var overlay = $(
                    '<div class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">' +
                    '<div class="relative max-w-90vw max-h-90vh p-4">' +
                    '<button class="absolute top-4 right-4 text-white text-2xl bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">' +
                    '<i class="fas fa-times"></i>' +
                    '</button>' +
                    '<img src="' + src + '" class="max-w-full max-h-full rounded-lg">' +
                    '</div>' +
                    '</div>'
                );

                $('body').append(overlay);

                // 关闭放大视图
                overlay.find('button').click(function() {
                    overlay.remove();
                });

                overlay.click(function(e) {
                    if (e.target === this) {
                        overlay.remove();
                    }
                });

                // ESC键关闭
                $(document).on('keydown.imageViewer', function(e) {
                    if (e.key === 'Escape') {
                        overlay.remove();
                        $(document).off('keydown.imageViewer');
                    }
                });
            });

            // 添加平滑滚动
            $('a[href^="#"]').click(function(e) {
                var target = $(this.getAttribute('href'));
                if(target.length) {
                    e.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                }
            });
        });
    </script>
@endsection
