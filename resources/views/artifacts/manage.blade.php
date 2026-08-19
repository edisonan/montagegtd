@extends('layouts.app')

@section('title', '制品库管理 - AI制品库')
@section('description', '制品库管理：可视化阅读、思维导图等 AI 二次产出的统一管理')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-sky-50">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500 mb-2">AI Artifact Library</div>
                        <h1 class="text-2xl font-bold text-slate-900">制品库管理</h1>
                        <div class="mt-2 text-sm text-slate-600">管理所有 AI 二次产出：可视化阅读、思维导图等，共 {{ $total }} 个来源实体</div>
                    </div>
                </div>
            </div>

            <!-- 搜索与筛选 -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <form method="GET" action="{{ url('/artifacts') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs text-slate-500 mb-1">关键词</label>
                        <input type="text" name="keyword" value="{{ $filters['keyword'] }}" placeholder="关联类型 / 关联 id，如 article、99637" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">关联类型</label>
                        <select name="related_type" class="px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                            <option value="">全部</option>
                            <option value="article" {{ ($filters['related_type'] ?? '') === 'article' ? 'selected' : '' }}>文章 article</option>
                            <option value="note" {{ ($filters['related_type'] ?? '') === 'note' ? 'selected' : '' }}>笔记 note</option>
                            <option value="mind" {{ ($filters['related_type'] ?? '') === 'mind' ? 'selected' : '' }}>思维导图 mind</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">关联 id</label>
                        <input type="number" name="related_id" value="{{ $filters['related_id'] ?: '' }}" placeholder="如 99637" class="w-28 px-3 py-2 rounded-lg border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">已生成类型</label>
                        <select name="artifact_type" class="px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                            <option value="">全部</option>
                            <option value="visual_reading" {{ ($filters['artifact_type'] ?? '') === 'visual_reading' ? 'selected' : '' }}>可视化阅读</option>
                            <option value="mind_map" {{ ($filters['artifact_type'] ?? '') === 'mind_map' ? 'selected' : '' }}>思维导图</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 text-white text-sm hover:bg-sky-700 transition"><i class="fas fa-search mr-1"></i>搜索</button>
                        <a href="{{ url('/artifacts') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:text-sky-600 transition">重置</a>
                    </div>
                </form>
            </div>

            <!-- 卡片列表（按实体聚合） -->
            <div class="px-6 py-6">
                @if(count($entities) === 0)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-10 text-center text-slate-500">
                        <i class="fas fa-box-open text-3xl mb-3"></i>
                        <p>没有找到来源实体</p>
                        <p class="text-sm mt-1">先到文章页生成可视化阅读 / 思维导图，或换一个筛选条件</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($entities as $entity)
                            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $entity['related_type'] }} #{{ $entity['related_id'] }}</span>
                                        <span class="text-[10px] text-slate-400">
                                            {{ \Carbon\Carbon::parse($entity['last_generated_at'])->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="px-4 py-3 flex-1">
                                    <div class="text-sm font-semibold text-slate-800 break-all leading-6">{{ \Illuminate\Support\Str::limit($entity['related_title'], 40) }}</div>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        @foreach(['visual_reading' => '可视化阅读', 'mind_map' => '思维导图'] as $type => $label)
                                            @if(!empty($entity['success_types'][$type]))
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700"><i class="fas fa-check mr-0.5"></i>{{ $label }}</span>
                                            @elseif(!empty($entity['failed_types'][$type]))
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-600">{{ $label }}失败</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-400">{{ $label }}未生成</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                                    <button type="button" class="px-3 py-1.5 rounded-lg bg-sky-600 text-white text-xs hover:bg-sky-700 transition js-artifact-action"
                                            data-related-type="{{ $entity['related_type'] }}" data-related-id="{{ $entity['related_id'] }}" data-artifact-type="visual_reading">
                                        <i class="fas fa-book-open mr-1"></i>可视化阅读
                                    </button>
                                    <button type="button" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs hover:bg-indigo-700 transition js-artifact-action"
                                            data-related-type="{{ $entity['related_type'] }}" data-related-id="{{ $entity['related_id'] }}" data-artifact-type="mind_map">
                                        <i class="fas fa-diagram-project mr-1"></i>思维导图
                                    </button>
                                    @if(!empty($entity['related_url']))
                                        <a href="{{ url($entity['related_url']) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-600 hover:border-sky-400 hover:text-sky-600 transition">
                                            <i class="fas fa-external-link-alt mr-1"></i>原文
                                        </a>
                                    @endif
                                </div>
                                @if(count($entity['artifacts']) > 0)
                                    <div class="px-4 py-2 border-t border-slate-100 bg-slate-50/60">
                                        @foreach($entity['artifacts'] as $artifact)
                                            <div class="flex items-center justify-between gap-2 py-1 text-xs">
                                                <span class="text-slate-600">{{ $artifact->artifact_type === 'mind_map' ? '思维导图' : '可视化阅读' }}
                                                    <span class="text-slate-400">· {{ $artifact->generated_at ? $artifact->generated_at->format('m-d H:i') : '-' }}</span>
                                                </span>
                                                @if($artifact->status === 'success')
                                                    <a href="{{ url('/artifacts/'.$artifact->id) }}" class="text-sky-600 hover:underline">查看</a>
                                                @else
                                                    <span class="text-red-500" title="{{ $artifact->error_message }}">失败</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($last_page > 1)
                        <div class="mt-6 flex items-center justify-center gap-2">
                            @if($current_page > 1)
                                <a href="{{ url('/artifacts') }}?{{ http_build_query(array_merge($filters, ['page' => $current_page - 1])) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm text-slate-600 hover:border-sky-400 transition">上一页</a>
                            @endif
                            <span class="text-sm text-slate-500">第 {{ $current_page }} / {{ $last_page }} 页</span>
                            @if($current_page < $last_page)
                                <a href="{{ url('/artifacts') }}?{{ http_build_query(array_merge($filters, ['page' => $current_page + 1])) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm text-slate-600 hover:border-sky-400 transition">下一页</a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- 制品弹窗 -->
    @include('artifacts._dialog')
@endsection

@section('scripts')
    <script>
        (function () {
            // 入口按钮：打开制品弹窗
            document.querySelectorAll('.js-artifact-action').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    window.openArtifactDialog({
                        relatedType: btn.getAttribute('data-related-type'),
                        relatedId: btn.getAttribute('data-related-id'),
                        artifactType: btn.getAttribute('data-artifact-type')
                    });
                });
            });
        })();
    </script>
@endsection