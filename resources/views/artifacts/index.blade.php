@extends('layouts.app')

@section('title', 'AI制品库 - ' . $article->subject)
@section('description', '文章 AI 制品库：可视化阅读、思维导图等 AI 二次产出')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-sky-50">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500 mb-2">AI Artifact Library</div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ $article->subject }}</h1>
                        <div class="mt-2 text-sm text-slate-600">
                            @if(!empty($article->feed))
                                来源：{{ $article->feed->feed_name }}
                            @endif
                            @if(!empty($article->published))
                                <span class="mx-2">•</span>{{ $article->published }}
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ url('/article/view/'.$article->id) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:text-sky-600 hover:border-sky-400 transition">原文详情</a>
                        <a href="{{ url('/article/'.$article->id.'/ai-render') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:text-sky-600 hover:border-sky-400 transition">AI可视化</a>
                    </div>
                </div>
            </div>

            @if(request()->get('generated'))
                <div class="px-6 py-3 bg-emerald-50 border-b border-emerald-200 text-emerald-700 text-sm">制品已生成</div>
            @endif
            @if(request()->get('deleted'))
                <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 text-slate-600 text-sm">制品已删除</div>
            @endif

            <div class="px-6 py-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                @php
                    $definitions = [
                        'visual_reading' => ['可视化阅读', 'html', '适合快速理解全文的可视化 HTML 页面', 'bg-sky-50 border-sky-200', 'text-sky-700', 'fa-book-open'],
                        'mind_map' => ['思维导图', 'json', '文章结构的思维导图节点树，可保存为思维导图', 'bg-indigo-50 border-indigo-200', 'text-indigo-700', 'fa-diagram-project'],
                    ];
                    $artifactByType = $artifacts->keyBy('artifact_type');
                @endphp

                @foreach($definitions as $type => $meta)
                    @php
                        $artifact = isset($artifactByType[$type]) ? $artifactByType[$type] : null;
                    @endphp
                    <div class="rounded-2xl border {{ $meta[3] }} p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $meta[5] }} {{ $meta[4] }}"></i>
                                    <span class="font-semibold {{ $meta[4] }}">{{ $meta[0] }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $meta[2] }}</p>
                            </div>
                            @if($artifact)
                                <span class="px-2 py-1 rounded-full text-xs {{ $artifact->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $artifact->status === 'success' ? '已生成' : '生成失败' }}
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-500">未生成</span>
                            @endif
                        </div>

                        @if($artifact && $artifact->status === 'success')
                            <div class="mt-4 text-sm text-slate-600 space-y-1">
                                <div>模型：{{ $artifact->model_name ?: '-' }}</div>
                                <div>生成时间：{{ $artifact->generated_at ? $artifact->generated_at->format('Y-m-d H:i') : '-' }}</div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ url('/artifacts/'.$artifact->id) }}" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:border-sky-400 hover:text-sky-600 transition">
                                    <i class="fas fa-eye mr-1"></i>查看
                                </a>
                                @if($type === 'mind_map')
                                    <form method="POST" action="{{ url('/artifacts/'.$artifact->id.'/to-mind') }}" onsubmit="return confirm('保存为思维导图并打开编辑页？');">
                                        {{ csrf_field() }}
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition">
                                            <i class="fas fa-diagram-project mr-1"></i>保存为思维导图
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ url('/artifacts/'.$artifact->id.'/delete') }}" onsubmit="return confirm('确定删除该制品？');">
                                    {{ csrf_field() }}
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-sm text-slate-500 hover:border-red-400 hover:text-red-600 transition">
                                        <i class="fas fa-trash mr-1"></i>删除
                                    </button>
                                </form>
                            </div>
                        @elseif($artifact && $artifact->status !== 'success')
                            <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                                {{ $artifact->error_message ?: '生成失败' }}
                            </div>
                        @endif

                        <div class="mt-4 pt-4 border-t border-slate-200/60">
                            <form method="POST" action="{{ url('/article/'.$article->id.'/artifacts/generate') }}" class="flex flex-wrap items-center gap-2">
                                {{ csrf_field() }}
                                <input type="hidden" name="artifact_type" value="{{ $type }}">
                                @if($artifact && $artifact->status === 'success')
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-white border border-amber-300 text-sm text-amber-700 hover:bg-amber-50 transition">
                                        <i class="fas fa-rotate mr-1"></i>重新生成
                                    </button>
                                @else
                                    <button type="submit" class="px-3 py-1.5 rounded-lg {{ $meta[4] }} bg-white border border-slate-300 text-sm hover:border-sky-400 transition">
                                        <i class="fas fa-wand-magic-sparkles mr-1"></i>生成{{ $meta[0] }}
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // 无需额外脚本
    </script>
@endsection