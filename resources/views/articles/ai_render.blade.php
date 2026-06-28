@extends('layouts.app')

@section('title', 'AI可视化 - 蒙太奇')
@section('description', '文章 AI 可视化阅读页')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-r from-sky-50 to-indigo-50">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500 mb-2">AI Visual Reading</div>
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
                        <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:text-sky-600 hover:border-sky-400 transition">阅读原文</a>
                        <form method="POST" action="{{ url('/article/'.$article->id.'/ai-render/generate') }}">
                            {{ csrf_field() }}
                            <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700 transition">重新生成</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 bg-slate-50 border-b border-slate-200">
                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span>生成状态</span>
                    <span class="px-2 py-1 rounded-full bg-white border border-slate-200 text-slate-700">{{ $render->status ?? 'unknown' }}</span>
                    @if(!empty($render->render_mode))
                        <span class="px-2 py-1 rounded-full bg-white border border-slate-200 text-slate-700">{{ $render->render_mode }}</span>
                    @endif
                    @if(!empty($render->template_style))
                        <span class="px-2 py-1 rounded-full bg-white border border-slate-200 text-slate-700">{{ $render->template_style }}</span>
                    @endif
                </div>

                @if(!empty($render->error_message))
                    <div class="mt-4 p-4 rounded-2xl bg-red-50 border border-red-200 shadow-sm">
                        <div class="text-sm font-semibold text-red-800 mb-2">生成失败原因</div>
                        <div class="text-red-700 leading-7">{{ $render->error_message }}</div>
                    </div>
                @endif
            </div>

            <div class="px-6 py-8">
                @if(!empty($render->html_content))
                    <div class="ai-render-content max-w-none rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        {!! $render->html_content !!}
                    </div>
                @else
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-slate-500">
                        暂时还没有可用的 AI 可视化内容。请查看上方生成状态和失败原因后重新生成。
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
