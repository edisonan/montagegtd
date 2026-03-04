@extends('layouts.app')

@section('title', '发现订阅源 - 蒙太奇')
@section('description', '探索和发现新的RSS订阅源，丰富您的阅读列表')

@section('content')
    <div class="fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- 页面标题和操作栏 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">发现订阅源</h1>
                    <p class="text-gray-600 mt-2">探索优质的RSS订阅源，一键订阅丰富您的阅读体验</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- 搜索框 -->
                    <div class="hidden sm:block">
                        <div class="relative">
                            <input type="text"
                                   id="explorerSearchDesktop"
                                   placeholder="搜索订阅源..."
                                   value="{{ request('search') }}"
                                   class="input pl-10 pr-4 w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <a href="{{ url('articles') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-newspaper mr-2"></i>
                        返回阅读
                    </a>
                    <a href="{{ url('feeds/explorer') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-compass mr-2"></i>
                        发现更多
                    </a>
                </div>
            </div>

            <!-- 移动端搜索 -->
            <div class="sm:hidden mb-6">
                <div class="relative">
                    <input type="text"
                           id="explorerSearchMobile"
                           placeholder="搜索订阅源..."
                           value="{{ request('search') }}"
                           class="input pl-10 pr-4 w-full">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                </div>
            </div>

            <!-- 成功/错误消息 -->
            @include('common.success')
            @include('common.errors')

            <!-- 订阅源网格 -->
            @if(count($feeds) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($feeds as $feed)
                        <div class="card hover:transform hover:-translate-y-1 transition-all duration-200">
                            <div class="p-5">
                                <!-- 订阅源头部 -->
                                <div class="flex items-start gap-3 mb-4">
                                    <!-- 网站图标 -->
                                    <div class="flex-shrink-0">
                                        @if($feed->favicon)
                                            <img src="{{ $feed->favicon }}"
                                                 alt="{{ $feed->feed_name }}"
                                                 class="w-10 h-10 rounded-lg bg-gray-100 object-cover"
                                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSI4IiBmaWxsPSIjRjFGNUY5Ii8+PHBhdGggZD0iTTIwIDEzQzE2LjEzIDEzIDEzIDE2LjEzIDEzIDIwQzEzIDIzLjg3IDE2LjEzIDI3IDIwIDI3QzIzLjg3IDI3IDI3IDIzLjg3IDI3IDIwQzI3IDE2LjEzIDIzLjg3IDEzIDIwIDEzWk0yMCAyNS40MkMxNy40NCAyNS40MiAxNS4zOCAyMy4zNiAxNS4zOCAyMEMxNS4zOCAxNi42NCAxNy40NCAxNC41OCAyMCAxNC41OEMyMi41NiAxNC41OCAyNC42MiAxNi42NCAyNC42MiAyMEMyNC42MiAyMy4zNiAyMi41NiAyNS40MiAyMCAyNS40MloiIGZpbGw9IiM5NEEzQjgiLz48L3N2Zz4='">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                <i class="fas fa-rss text-gray-400"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 标题和描述 -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 mb-1 truncate">
                                            <a href="{{ url('article/list') }}?feed_id={{ $feed->id }}"
                                               class="hover:text-blue-600 transition-colors">
                                                {{ $feed->feed_name }}
                                            </a>
                                        </h3>
                                        @if($feed->feed_desc)
                                            <p class="text-gray-600 text-sm line-clamp-2 leading-relaxed">
                                                {{ $feed->feed_desc }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- 统计信息 -->
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <div class="flex items-center gap-4">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-clock text-xs"></i>
                                        {{ $feed->updated_at ? $feed->updated_at->format('Y-m-d') : '未知' }}
                                    </span>
                                        @if($feed->category)
                                            <span class="badge badge-primary">
                                            {{ $feed->category }}
                                        </span>
                                        @endif
                                    </div>

                                    <span class="flex items-center gap-1">
                                    <i class="fas fa-signal text-xs"></i>
                                    <span>{{ $feed->frequency ?? '稳定' }}</span>
                                </span>
                                </div>

                                <!-- 操作按钮 -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <a href="{{ url('article/list') }}?feed_id={{ $feed->id }}"
                                       class="btn btn-outline btn-sm">
                                        <i class="fas fa-eye mr-2"></i>
                                        查看文章
                                    </a>

                                    <button type="button"
                                            data-feed-id="{{ $feed->id }}"
                                            class="feed-quick-sub btn btn-primary btn-sm hover:shadow-lg transition-shadow">
                                        <i class="fas fa-plus mr-2"></i>
                                        一键订阅
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 分页 -->
                @if($feeds->hasPages())
                    <div class="card">
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    显示 {{ $feeds->firstItem() }} - {{ $feeds->lastItem() }}
                                    共 {{ $feeds->total() }} 个订阅源
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- 上一页 --}}
                                    @if($feeds->onFirstPage())
                                        <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                    @else
                                        <a href="{{ $feeds->previousPageUrl() }}"
                                           class="nav-link px-3 py-2 hover:bg-gray-100 rounded-lg">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    @endif

                                    {{-- 页码 --}}
                                    <div class="flex items-center gap-1">
                                        @foreach(range(1, min(5, $feeds->lastPage())) as $page)
                                            @if($page == $feeds->currentPage())
                                                <span class="px-3 py-1 bg-blue-100 text-blue-600 font-semibold rounded-lg">
                                                {{ $page }}
                                            </span>
                                            @else
                                                <a href="{{ $feeds->url($page) }}"
                                                   class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                                    {{ $page }}
                                                </a>
                                            @endif
                                        @endforeach

                                        @if($feeds->lastPage() > 5)
                                            <span class="px-2 text-gray-400">...</span>
                                            <a href="{{ $feeds->url($feeds->lastPage()) }}"
                                               class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                                {{ $feeds->lastPage() }}
                                            </a>
                                        @endif
                                    </div>

                                    {{-- 下一页 --}}
                                    @if($feeds->hasMorePages())
                                        <a href="{{ $feeds->nextPageUrl() }}"
                                           class="nav-link px-3 py-2 hover:bg-gray-100 rounded-lg">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                <!-- 空状态 -->
                <div class="card">
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-compass text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">未找到订阅源</h3>
                        <p class="text-gray-600 mb-6">暂时没有发现可用的订阅源，请尝试其他搜索关键词</p>
                        <a href="{{ url('feeds/explorer') }}" class="btn btn-primary">
                            <i class="fas fa-redo mr-2"></i>
                            重新探索
                        </a>
                    </div>
                </div>
            @endif

            <!-- 分类筛选（可选） -->
            <div class="mt-8">
                <div class="card">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">热门分类</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ url('feeds/explorer') }}?category=technology"
                               class="badge badge-primary hover:bg-blue-100 transition-colors">
                                科技技术
                            </a>
                            <a href="{{ url('feeds/explorer') }}?category=news"
                               class="badge badge-primary hover:bg-blue-100 transition-colors">
                                新闻资讯
                            </a>
                            <a href="{{ url('feeds/explorer') }}?category=design"
                               class="badge badge-primary hover:bg-blue-100 transition-colors">
                                设计创意
                            </a>
                            <a href="{{ url('feeds/explorer') }}?category=productivity"
                               class="badge badge-primary hover:bg-blue-100 transition-colors">
                                效率工具
                            </a>
                            <a href="{{ url('feeds/explorer') }}?category=programming"
                               class="badge badge-primary hover:bg-blue-100 transition-colors">
                                编程开发
                            </a>
                            <a href="{{ url('feeds/explorer') }}?category=ai"
                               class="badge badge-primary hover:bg-blue-100 transition-colors">
                                AI人工智能
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };

            // 一键订阅功能
            $('.feed-quick-sub').click(function() {
                const button = $(this);
                const feedId = button.data('feed-id');
                const originalText = button.html();

                // 显示加载状态
                button.html('<i class="fas fa-spinner fa-spin mr-2"></i>订阅中...');
                button.prop('disabled', true);

                apiRequest('POST', '/feeds/quickstore', { feed_id: feedId }).then(function(response) {
                        if (response.code === 9999) {
                            // 成功订阅
                            button.removeClass('btn-primary').addClass('btn-success');
                            button.html('<i class="fas fa-check mr-2"></i>已订阅');

                            // 显示成功提示
                            showNotification('success', response.msg || '订阅成功！');
                        } else {
                            // 订阅失败
                            button.html(originalText);
                            button.prop('disabled', false);
                            showNotification('error', response.msg || '订阅失败，请重试');
                        }
                }).catch(function() {
                    button.html(originalText);
                    button.prop('disabled', false);
                    showNotification('error', '网络错误，请检查连接后重试');
                });
            });

            // 显示通知函数
            function showNotification(type, message) {
                // 创建通知元素
                const notification = $(`
            <div class="fixed top-4 right-4 z-50 fade-in">
                <div class="card shadow-lg border-l-4 ${type === 'success' ? 'border-green-500' : 'border-red-500'}">
                    <div class="p-4 flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <i class="fas ${type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500'} text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800">${message}</p>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);

                // 添加到页面
                $('body').append(notification);

                // 点击关闭
                notification.find('button').click(function() {
                    notification.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            function goExplorerSearch(keyword) {
                var q = (keyword || '').trim();
                if (!q) {
                    window.location.href = '{{ url('feeds/explorer') }}';
                    return;
                }
                window.location.href = '{{ url('feeds/explorer') }}?search=' + encodeURIComponent(q);
            }

            // 搜索框回车跳转
            $('#explorerSearchDesktop, #explorerSearchMobile').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    goExplorerSearch($(this).val());
                }
            });
        });
    </script>

    <style>
        /* 文本截断样式 */
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* 卡片悬停效果 */
        .card {
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* 分页器样式 */
        .pagination-link {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .pagination-link:hover {
            background-color: var(--gray-100);
        }

        .pagination-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
