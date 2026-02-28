{{-- resources/views/vendor/pagination/tailwind-enhanced.blade.php --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="space-y-4">
        {{-- 顶部信息栏 --}}
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                显示第 <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span> 到
                <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span> 条，
                共 <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span> 条记录
            </div>

            @if($paginator->total() > 10)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">每页显示：</span>
                    <select onchange="window.location.href = this.value"
                            class="input input-sm text-sm">
                        @php
                            $perPageOptions = [10, 25, 50, 100];
                            $currentUrl = request()->fullUrl();
                        @endphp

                        @foreach($perPageOptions as $option)
                            @php
                                $url = preg_replace('/[?&]per_page=\d+/', '', $currentUrl);
                                $url .= (strpos($url, '?') === false ? '?' : '&') . 'per_page=' . $option;
                            @endphp
                            <option value="{{ $url }}" {{ $paginator->perPage() == $option ? 'selected' : '' }}>
                                {{ $option }} 条
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- 分页控件 --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- 移动端快速导航 --}}
            <div class="sm:hidden flex items-center justify-between">
                @if ($paginator->onFirstPage())
                    <span class="btn btn-sm btn-secondary opacity-50 cursor-not-allowed w-24">
                        <i class="fas fa-chevron-left mr-1"></i>上一页
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="btn btn-sm btn-secondary w-24">
                        <i class="fas fa-chevron-left mr-1"></i>上一页
                    </a>
                @endif

                <span class="text-sm text-gray-600 px-3">
                    {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="btn btn-sm btn-secondary w-24">
                        下一页<i class="fas fa-chevron-right ml-1"></i>
                    </a>
                @else
                    <span class="btn btn-sm btn-secondary opacity-50 cursor-not-allowed w-24">
                        下一页<i class="fas fa-chevron-right ml-1"></i>
                    </span>
                @endif
            </div>

            {{-- 桌面端完整分页 --}}
            <div class="hidden sm:flex items-center gap-1">
                {{-- 第一页 --}}
                @if ($paginator->currentPage() > 3)
                    <a href="{{ $paginator->url(1) }}"
                       class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        1
                    </a>
                    @if ($paginator->currentPage() > 4)
                        <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-400">
                            ...
                        </span>
                    @endif
                @endif

                {{-- 中间页码 --}}
                @foreach (range(max($paginator->currentPage() - 2, 1), min($paginator->currentPage() + 2, $paginator->lastPage())) as $page)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="relative z-10 inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-purple-600 border border-transparent rounded-lg shadow-sm cursor-default">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}"
                           class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- 最后一页 --}}
                @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                    @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                        <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-400">
                            ...
                        </span>
                    @endif
                    <a href="{{ $paginator->url($paginator->lastPage()) }}"
                       class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-r-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        {{ $paginator->lastPage() }}
                    </a>
                @endif
            </div>

            {{-- 跳转到指定页 --}}
            @if($paginator->lastPage() > 5)
                <div class="hidden sm:flex items-center gap-2">
                    <span class="text-sm text-gray-500">跳转到：</span>
                    <div class="relative">
                        <input type="number"
                               id="jumpToPage"
                               min="1"
                               max="{{ $paginator->lastPage() }}"
                               value="{{ $paginator->currentPage() }}"
                               class="input input-sm w-20 text-center">
                        <button onclick="jumpToPage()"
                                class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </nav>

    <script>
        function jumpToPage() {
            const pageInput = document.getElementById('jumpToPage');
            const page = parseInt(pageInput.value);
            const maxPage = {{ $paginator->lastPage() }};

            if (page >= 1 && page <= maxPage && page !== {{ $paginator->currentPage() }}) {
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            }
        }

        document.getElementById('jumpToPage')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                jumpToPage();
            }
        });
    </script>
@endif