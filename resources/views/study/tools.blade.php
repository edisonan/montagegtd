@extends('layouts.app')

@section('title', '学习工具 - 蒙太奇')

@section('content')
    <style>
        .tools-shell { max-width: 1080px; margin: 0 auto; }
        .tools-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
        .tools-title { font-size: 22px; font-weight: 700; color: #101828; display: flex; align-items: center; gap: 10px; }
        .tools-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
        .tools-card { position: relative; border-radius: 20px; border: 1px solid #e4e7ec; background: #fff; padding: 20px; box-shadow: 0 6px 18px rgba(17, 24, 39, 0.05); transition: transform .15s, box-shadow .15s; display: flex; flex-direction: column; }
        .tools-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(17, 24, 39, 0.10); }
        .tools-card-icon { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; }
        .tools-card-name { margin-top: 14px; font-size: 17px; font-weight: 700; color: #111827; }
        .tools-card-desc { margin-top: 8px; font-size: 13px; line-height: 1.7; color: #475467; flex: 1; }
        .tools-card-tags { margin-top: 12px; display: flex; gap: 6px; flex-wrap: wrap; }
        .tools-card-tag { border-radius: 999px; background: #eef4ff; color: #1e3a8a; font-size: 11px; font-weight: 600; padding: 3px 10px; }
        .tools-card-enter { margin-top: 18px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; border: 0; padding: 10px 16px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; text-decoration: none; }
        .tools-empty { text-align: center; padding: 60px 20px; color: #667085; border: 1px dashed #d0d5dd; border-radius: 20px; }
    </style>

    <div class="tools-shell">
        <div class="tools-head">
            <div class="flex items-center gap-3">
                <a href="{{ url('/study') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>学习</a>
                <h1 class="tools-title"><i class="fas fa-tools text-[#1e3a8a]"></i>学习工具</h1>
            </div>
            <div class="text-sm text-gray-500">高效学习小工具集，持续上新</div>
        </div>

        @if(count($tools) > 0)
            <div class="tools-grid">
                @foreach($tools as $tool)
                    @php
                        $accent = $tool['accent'] ?? '#1e3a8a';
                    @endphp
                    <a class="tools-card" href="{{ url($tool['url']) }}" style="text-decoration:none;">
                        <div class="tools-card-icon" style="background:{{ $accent }};">
                            <i class="{{ $tool['icon'] }}"></i>
                        </div>
                        <div class="tools-card-name">{{ $tool['name'] }}</div>
                        <div class="tools-card-desc">{{ $tool['desc'] }}</div>
                        @if(!empty($tool['tags']))
                            <div class="tools-card-tags">
                                @foreach($tool['tags'] as $tag)
                                    <span class="tools-card-tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                        <span class="tools-card-enter" style="background:{{ $accent }};">
                            进入工具 <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="tools-empty">
                <i class="fas fa-toolbox text-2xl mb-3"></i>
                <div>暂无学习工具</div>
            </div>
        @endif
    </div>
@endsection