{{-- resources/views/vendor/pagination/simple-tailwind.blade.php --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center gap-2">
        {{-- 上一页按钮 --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                <i class="fas fa-chevron-left mr-2"></i>
                上一页
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               rel="prev"
               class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <i class="fas fa-chevron-left mr-2"></i>
                上一页
            </a>
        @endif

        {{-- 当前页码显示 --}}
        <div class="text-sm text-gray-600 px-3 py-1.5">
            第 <span class="font-semibold text-gray-800">{{ $paginator->currentPage() }}</span> 页
        </div>

        {{-- 下一页按钮 --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               rel="next"
               class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 hover:text-gray-900 transition-colors">
                下一页
                <i class="fas fa-chevron-right ml-2"></i>
            </a>
        @else
            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                下一页
                <i class="fas fa-chevron-right ml-2"></i>
            </span>
        @endif
    </nav>
@endif