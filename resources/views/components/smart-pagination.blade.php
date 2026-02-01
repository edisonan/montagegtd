{{-- resources/views/components/smart-pagination.blade.php --}}
@props(['paginator'])

@php
    // 自动判断分页类型
    $isSimple = $paginator instanceof Illuminate\Pagination\Paginator &&
                !($paginator instanceof Illuminate\Pagination\LengthAwarePaginator);

    // 自动选择样式文件
    $paginationView = $isSimple ? 'vendor.pagination.simple-tailwind' : 'vendor.pagination.tailwind';
@endphp

@if($paginator->hasPages())
    <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            @if(!$isSimple)
                <div class="text-sm text-gray-500">
                    显示第 <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span> 到
                    <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span> 条，
                    共 <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span> 条记录
                </div>
            @else
                <div class="text-sm text-gray-500">
                    第 <span class="font-semibold text-gray-700">{{ $paginator->currentPage() }}</span> 页
                </div>
            @endif

            <div class="flex items-center gap-1">
                {!! $paginator->links($paginationView) !!}
            </div>
        </div>
    </div>
@endif