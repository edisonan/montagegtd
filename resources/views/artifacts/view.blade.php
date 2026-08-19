@extends('layouts.app')

@section('title', $artifact->name . ' - AI制品库')
@section('description', 'AI 制品查看')

@if($artifact->file_type === 'json' && $artifact->artifact_type === 'mind_map')
    <link type="text/css" rel="stylesheet" href="{{ url('/css/jsmind.css') }}">
    <style>
        #jsmind_container {
            width: 100%;
            min-height: 520px;
            height: calc(100vh - 320px);
            background: #fff;
            border-radius: 1rem;
        }
        #jsmind_container jmnode {
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            font-size: 14px;
            padding: 8px 14px;
            border-radius: 8px;
        }
        #jsmind_container jmnode.selected {
            background: #6366f1;
            color: #fff;
            border-color: #6366f1;
        }
        #jsmind_container jmexpander {
            color: #6366f1;
        }
    </style>
@endif

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-sky-50">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500 mb-2">AI Artifact</div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ $artifact->name }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                            <span class="px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-700">{{ $artifact->artifact_type }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-700">{{ $artifact->file_type }}</span>
                            <span class="px-2 py-0.5 rounded-full {{ $artifact->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $artifact->status }}</span>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            关联：{{ $artifact->related_type }} #{{ $artifact->related_id }}
                            @if($artifact->model_name) · 模型：{{ $artifact->model_name }} @endif
                            @if($artifact->generated_at) · 生成：{{ $artifact->generated_at->format('Y-m-d H:i') }} @endif
                        </div>
                        @if($artifact->artifact_type === 'mind_map')
                            <div class="mt-3">
                                <form method="POST" action="{{ url('/artifacts/'.$artifact->id.'/to-mind') }}" class="inline">
                                    {{ csrf_field() }}
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition">
                                        <i class="fas fa-diagram-project mr-1"></i>保存为思维导图
                                    </button>
                                </form>
                            </div>
                        @endif
                        @if($artifact->status !== 'success')
                            <div class="mt-3 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $artifact->error_message ?: '生成失败' }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($artifact->related_type === 'article')
                            <a href="{{ url('/article/'.$artifact->related_id.'/artifacts') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:text-sky-600 hover:border-sky-400 transition">返回制品库</a>
                            <a href="{{ url('/article/view/'.$artifact->related_id) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:text-sky-600 hover:border-sky-400 transition">原文详情</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="px-6 py-8">
                @if($artifact->status !== 'success' || $artifact->content === null)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-500 text-center">
                        <i class="fas fa-circle-exclamation text-2xl mb-2"></i>
                        <p>{{ $artifact->error_message ?: '制品内容为空' }}</p>
                        @if($artifact->related_type === 'article')
                            <a href="{{ url('/article/'.$artifact->related_id.'/artifacts') }}" class="mt-3 inline-block px-4 py-2 rounded-lg bg-sky-600 text-white text-sm hover:bg-sky-700 transition">返回重新生成</a>
                        @endif
                    </div>
                @elseif($artifact->file_type === 'html')
                    <div class="ai-render-content max-w-none rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        {!! $artifact->content !!}
                    </div>
                @elseif($artifact->file_type === 'json' && $artifact->artifact_type === 'mind_map')
                    @if(!empty($nodeTree))
                        <div id="jsmind_container">
                            <div class="flex items-center justify-center h-full text-slate-400" id="mindmapLoading">正在加载思维导图...</div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-500 text-center">思维导图数据格式无效</div>
                    @endif
                @elseif($artifact->file_type === 'markdown')
                    <div id="markdown_content" class="prose max-w-none rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">{{ $artifact->content }}</div>
                @else
                    <pre class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm text-slate-700 whitespace-pre-wrap break-words">{{ $artifact->content }}</pre>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($artifact->file_type === 'json' && $artifact->artifact_type === 'mind_map' && !empty($nodeTree))
        <script src="{{ url('/js/jsmind.js') }}"></script>
        <script>
            (function () {
                var nodeTree = {!! json_encode($nodeTree, JSON_UNESCAPED_UNICODE) !!};
                var options = {
                    container: 'jsmind_container',
                    editable: false,
                    theme: 'primary',
                    mode: 'full',
                    support_html: true,
                    view: {
                        hmargin: 120,
                        vmargin: 60,
                        line_width: 2,
                        line_color: '#cbd5e1'
                    },
                    layout: {
                        hspace: 80,
                        vspace: 40,
                        pspace: 25
                    }
                };
                var jm = new jsMind(options);
                var mindData = {
                    meta: {
                        name: '{{ addslashes($artifact->name) }}',
                        author: 'MontageGTD AI',
                        version: '1.0'
                    },
                    format: 'node_tree',
                    data: nodeTree
                };
                jm.show(mindData);
                var loading = document.getElementById('mindmapLoading');
                if (loading) { loading.style.display = 'none'; }
            })();
        </script>
    @elseif($artifact->file_type === 'markdown' && $artifact->status === 'success' && $artifact->content !== null)
        <script src="{{ url('/js/marked.min.js') }}"></script>
        <script>
            (function () {
                var el = document.getElementById('markdown_content');
                if (el) {
                    var raw = el.textContent;
                    if (window.marked && window.marked.parse) {
                        el.innerHTML = marked.parse(raw);
                    } else if (window.marked) {
                        el.innerHTML = marked(raw);
                    }
                }
            })();
        </script>
    @endif
@endsection