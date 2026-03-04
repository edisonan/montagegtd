@extends('layouts.app')

@section('title', '探索发现 - 蒙太奇')
@section('description', '发现和订阅优质RSS源，分类浏览推荐订阅，轻松管理您的阅读内容。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 快速订阅卡片 -->
        <div class="card mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">订阅推荐</h2>
                        <p class="text-sm text-gray-500 mt-1">快速开始您的订阅之旅</p>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <div class="flex-1 md:flex-none">
                            <input type="text" id="feedSearchInput" placeholder="搜索订阅源..."
                                   class="input w-full" value="{{ request('name') ?? '' }}">
                        </div>
                        <button type="button" id="feedSearchBtn" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>
                            搜索
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $quickLinks = [
                            ['url' => '/feeds', 'label' => '直接订阅', 'icon' => 'fas fa-rss', 'color' => 'from-blue-500 to-blue-600'],
                            ['url' => '/feeds/weiborss', 'label' => '订阅微博', 'icon' => 'fab fa-weibo', 'color' => 'from-red-500 to-red-600'],
                            ['url' => '/feeds/weixinrss', 'label' => '订阅公众号', 'icon' => 'fab fa-weixin', 'color' => 'from-green-500 to-green-600'],
                            ['url' => '/feeds/opml', 'label' => 'OPML导入', 'icon' => 'fas fa-file-import', 'color' => 'from-purple-500 to-purple-600'],
                        ];
                    @endphp

                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}" class="group">
                            <div class="card hover:card-elevated transition-all duration-200 h-full">
                                <div class="p-5 text-center">
                                    <div class="w-12 h-12 bg-gradient-to-br {{ $link['color'] }} rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                        <i class="{{ $link['icon'] }} text-white text-lg"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 group-hover:text-gray-900 transition-colors">
                                        {{ $link['label'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-2">快速添加订阅源</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 分类订阅 -->
        <div class="card mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">分类订阅</h2>
                <p class="text-sm text-gray-500 mt-1">按类别发现优质内容</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach ($recommend_categorys as $id => $name)
                        <a href="/feeds/search?recommend_category_id={{ $id }}"
                           class="group">
                            <div class="card hover:bg-gray-50 transition-colors h-full">
                                <div class="p-4 text-center">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-folder text-gray-500"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors truncate block">
                                {{ $name }}
                            </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 推荐订阅源 -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">推荐订阅源</h2>
                        <p class="text-sm text-gray-500 mt-1">精选优质内容源</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        共 {{ $feeds->total() }} 个订阅源
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- 错误提示 -->
                @include('common.errors')

                @if (count($feeds) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($feeds as $feed)
                            <div class="card hover:card-elevated transition-all duration-200 h-full flex flex-col">
                                <div class="p-5 flex-grow">
                                    <!-- 标题区域 -->
                                    <div class="flex items-start gap-3 mb-3">
                                        @if($feed->favicon)
                                            <img src="{{ $feed->favicon }}" alt="{{ $feed->feed_name }}"
                                                 class="w-8 h-8 rounded-full flex-shrink-0 border border-gray-200">
                                        @else
                                            <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-rss text-white text-sm"></i>
                                            </div>
                                        @endif

                                        <div class="flex-grow min-w-0">
                                            <h3 class="font-semibold text-gray-800 truncate hover:text-gray-900 transition-colors">
                                                <a href="{{ url('article/list') }}?feed_id={{$feed->id}}" class="hover:underline">
                                                    {{ $feed->feed_name }}
                                                </a>
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- 描述内容 -->
                                    @if($feed->feed_desc)
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                                {{ $feed->feed_desc }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- 操作区域 -->
                                <div class="px-5 pb-5 pt-0">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ url('article/list') }}?feed_id={{$feed->id}}"
                                           class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                            <i class="fas fa-newspaper mr-1"></i>浏览文章
                                        </a>
                                        <button type="button"
                                                data-feed-id="{{ $feed->id }}"
                                                class="feed-quick-subscribe btn btn-sm btn-outline py-1 px-3">
                                            <i class="fas fa-plus mr-1"></i>
                                            订阅
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 分页 -->
                    @if($feeds->hasPages())
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    显示 {{ $feeds->firstItem() }} 到 {{ $feeds->lastItem() }} 条，共 {{ $feeds->total() }} 条
                                </div>
                                <div class="flex gap-1">
                                    @if($feeds->onFirstPage())
                                        <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg">
                            <i class="fas fa-chevron-left mr-1"></i>上一页
                        </span>
                                    @else
                                        <a href="{{ $feeds->previousPageUrl() }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-chevron-left mr-1"></i>上一页
                                        </a>
                                    @endif

                                    @if($feeds->hasMorePages())
                                        <a href="{{ $feeds->nextPageUrl() }}" class="btn btn-secondary btn-sm">
                                            下一页<i class="fas fa-chevron-right ml-1"></i>
                                        </a>
                                    @else
                                        <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg">
                            下一页<i class="fas fa-chevron-right ml-1"></i>
                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                @else
                    <!-- 空状态 -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-rss text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">暂无推荐订阅源</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            当前没有推荐的订阅源，您可以尝试搜索其他内容或稍后再来查看。
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* 文本截断样式 */
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }

        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
    </style>

    <script>
        $(document).ready(function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };

            // 快速订阅功能
            $(document).on('click', '.feed-quick-subscribe', function() {
                const button = $(this);
                const feedId = button.data('feed-id');

                // 防止重复点击
                if (button.hasClass('loading')) return;

                // 显示加载状态
                button.addClass('loading').prop('disabled', true);
                const originalText = button.html();
                button.html('<i class="fas fa-spinner fa-spin mr-1"></i>处理中...');

                apiRequest('POST', '/feeds/quickstore', { feed_id: feedId }).then(function(response) {
                        if (response.code === 9999) {
                            // 成功 - 更新按钮状态
                            button.removeClass('btn-outline').addClass('btn-success');
                            button.html('<i class="fas fa-check mr-1"></i>已订阅');

                            // 显示成功消息
                            showNotification('success', response.msg || '订阅成功！');
                        } else {
                            // 失败
                            showNotification('error', response.msg || '订阅失败，请重试');
                            button.html(originalText).removeClass('loading').prop('disabled', false);
                        }
                }).catch(function() {
                    showNotification('error', '网络错误，请稍后重试');
                    button.html(originalText).removeClass('loading').prop('disabled', false);
                });
            });

            // 通知函数
            function showNotification(type, message) {
                // 创建通知元素
                const notification = $('<div class="fixed top-4 right-4 z-50 fade-in"></div>');
                const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

                notification.html(`
                <div class="card ${bgColor} text-white shadow-xl max-w-sm">
                    <div class="p-4 flex items-center gap-3">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} text-lg"></i>
                        <div class="flex-1">${message}</div>
                        <button class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `);

                // 添加到页面
                $('body').append(notification);

                // 点击关闭
                notification.find('button').on('click', function() {
                    notification.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            function goFeedSearch() {
                const input = $('#feedSearchInput');
                const keyword = (input.val() || '').trim();
                if (!keyword) {
                    input.focus();
                    showNotification('warning', '请输入搜索关键词');
                    return;
                }
                window.location.href = '/feeds/search?name=' + encodeURIComponent(keyword);
            }

            $('#feedSearchBtn').on('click', function() {
                goFeedSearch();
            });

            $('#feedSearchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    goFeedSearch();
                }
            });
        });
    </script>
@endsection
