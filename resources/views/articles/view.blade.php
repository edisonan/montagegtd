@extends('layouts.app')

@section('title', $article->subject . ' - 蒙太奇')

@section('description', mb_substr(strip_tags($article->content), 0, 100, 'UTF-8') . '...')

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
                            <a href="{{ url('article/list') }}?feed_id={{$article->feed->id}}" class="source-link">
                                <i class="fas fa-rss"></i>
                                {{$article->feed->feed_name}}
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
                                           data-title="{{ $article->subject }} From:{{ url('/article/view/'.$article->id) }}"
                                           data-url="{{ url('/article/view/'.$article->id) }}"
                                           data-id="{{ $article->id }}">
                                            <i class="fas fa-heart"></i>
                                            <span>记录想法</span>
                                        </a>
                                        <!-- 其他分享选项 -->
                                    </div>
                                </div>
                            </div>

                            @if(!$is_feed)
                                <a href="javascript:void(0);"
                                   feed_id="{{ $article->feed->id }}"
                                   class="header-action-btn feed_quick_sub">
                                    <i class="fas fa-plus"></i>
                                    添加订阅
                                </a>
                            @endif

                            <a href="{{ url('/articles') }}" class="header-action-btn">
                                <i class="fas fa-arrow-right"></i>
                                继续阅读
                            </a>
                        </div>
                    </div>

                    @if(!empty($article->subject))
                        <h1 class="article-title">{{ $article->subject }}</h1>
                    @endif

                    <div class="publish-info">
                    <span>
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ $article->published }}
                    </span>
                        <span>
                        <i class="fas fa-external-link-alt mr-1"></i>
                        <a href="{{ $article->url }}" target="_blank" class="text-white hover:underline">
                            原文链接
                        </a>
                    </span>
                        <span>
                        <i class="fas fa-globe mr-1"></i>
                        <a href="{{ App\Http\Utils\CommonUtil::hostUrl($article->feed->url) }}"
                           target="_blank"
                           class="text-white hover:underline">
                            {{ parse_url($article->feed->url, PHP_URL_HOST) }}
                        </a>
                    </span>
                    </div>
                </div>

                <!-- 文章内容 -->
                <div class="article-content-wrapper">
                    <div class="article-content" id="articleContent">
                            <?php echo App\Http\Utils\CommonUtil::formatContentHtml($article->content); ?>
                    </div>
                </div>

                <!-- 文章底部 -->
                <div class="article-footer">
                    <div class="footer-actions">
                        <a href="{{ $article->url }}" target="_blank" class="original-link">
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

    <script type="text/javascript">
        $(document).ready(function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };

            // 图片懒加载
            if (typeof $.fn.lazyload === 'function') {
                $("img.lazy").lazyload({
                    effect: "fadeIn",
                    threshold: 200
                });
            }

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
                        "article_id": {{ $article->id }},
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
