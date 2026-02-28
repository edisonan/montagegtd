@extends('layouts.app')

@section('title', '番茄历史 - 蒙太奇')
@section('description', '查看已完成的番茄工作记录，跟踪您的工作效率')

@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            // 删除番茄
            $(".delete_pomo").click(function (e) {
                e.preventDefault();
                pomo_value = $(this).attr("pomo_value");
                pomo_token = $(this).attr("pomo_token");

                if (!confirm("确认要删除此番茄咩？")) {
                    return false;
                }

                $.ajax({
                    url: "{{ url('pomo') }}" + "/" + pomo_value,
                    type: 'DELETE',
                    data: {type: 'delete', _token: pomo_token},
                    success: function (result_arr) {
                        if (result_arr.code != 9999) {
                            alert('处理失败，请稍后再试');
                        } else {
                            $('#' + pomo_value).fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    },
                    error: function() {
                        alert('请求失败，请稍后重试');
                    }
                });
            });

            // 更新番茄描述
            $(".update_pomo").click(function (e) {
                e.preventDefault();
                $pomo_name = $(this).attr('pomo_name');
                $pomo_id = $(this).attr('pomo_id');

                var new_name = prompt("请输入番茄描述：", $pomo_name);
                if (new_name != null && new_name != "" && new_name != $pomo_name) {
                    $.ajax({
                        url: "{{ url('pomoupdate') }}" + "/" + $pomo_id,
                        type: 'POST',
                        data: {_token: "{{ csrf_token() }}", name: new_name},
                        success: function (result_arr) {
                            if (result_arr.code != 9999) {
                                alert('处理失败，请稍后再试');
                            } else {
                                $('#name' + $pomo_id).html(new_name);
                            }
                        },
                        error: function() {
                            alert('请求失败，请稍后重试');
                        }
                    });
                }
            });
        });
    </script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('common.success')

        <!-- 页面标题和操作栏 -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">番茄工作历史</h1>
                <p class="text-gray-600">查看和回顾您已完成的所有番茄工作记录</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{'/index'}}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回首页
                </a>
                <a href="{{'/statistics'}}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar mr-2"></i>
                    数据统计
                </a>
            </div>
        </div>

        <!-- 番茄统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-clock text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">总计番茄数</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pomos->total() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4">
                            <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">今日完成</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $today_pomos ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-fire text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">本周效率</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $week_average ?? 0 }}/天</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 番茄列表 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">番茄记录</h2>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-info-circle"></i>
                        <span>共 {{ $pomos->total() }} 条记录</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if (count($pomos) > 0)
                    <!-- 桌面端表格 -->
                    <div class="hidden md:block">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="w-24">日期</th>
                                <th class="w-40">时间段</th>
                                <th>番茄描述</th>
                                <th class="w-32">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php $lastDate = ''; ?>
                            @foreach ($pomos as $pomo)
                                    <?php
                                    $currentDate = date('m-d', strtotime($pomo->start_time));
                                    $showDate = $currentDate != $lastDate;
                                    $lastDate = $currentDate;
                                    ?>
                                <tr id="{{$pomo->id}}" class="hover:bg-gray-50 transition-colors">
                                    <td>
                                        <div class="flex items-center">
                                            @if($showDate)
                                                <span class="font-medium text-gray-900">{{ $currentDate }}</span>
                                            @else
                                                <span class="text-gray-300">— —</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-600">
                                            {{ date('H:i', strtotime($pomo->start_time)) }} - {{ date('H:i', strtotime($pomo->end_time)) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center group">
                                            <span id="name{{ $pomo->id }}" class="text-gray-800 font-medium">
                                                {{ $pomo->name }}
                                            </span>
                                            <div class="ml-3 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="/notes?source_type=1&source_id={{$pomo->id}}"
                                                   class="text-gray-400 hover:text-blue-600"
                                                   title="记录更多当时的想法">
                                                    <i class="fas fa-sticky-note text-sm"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end space-x-3">
                                            <button class="update_pomo text-gray-400 hover:text-green-600 transition-colors"
                                                    pomo_name="{{ $pomo->name }}"
                                                    pomo_id="{{ $pomo->id }}"
                                                    title="编辑描述">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="delete_pomo text-gray-400 hover:text-red-600 transition-colors"
                                                    pomo_value="{{ $pomo->id }}"
                                                    pomo_token="{{ csrf_token() }}"
                                                    title="删除番茄">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 移动端卡片列表 -->
                    <div class="md:hidden space-y-4">
                            <?php $lastDate = ''; ?>
                        @foreach ($pomos as $pomo)
                                <?php
                                $currentDate = date('m-d', strtotime($pomo->start_time));
                                $showDate = $currentDate != $lastDate;
                                $lastDate = $currentDate;
                                ?>
                            <div id="{{$pomo->id}}" class="card hover:shadow-md transition-shadow">
                                <div class="p-4">
                                    @if($showDate)
                                        <div class="mb-3 pb-2 border-b border-gray-100">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-calendar-day text-blue-500"></i>
                                                <span class="font-medium text-gray-900">{{ $currentDate }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex-1">
                                            <div class="mb-2">
                                            <span class="text-sm text-gray-500">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ date('H:i', strtotime($pomo->start_time)) }} - {{ date('H:i', strtotime($pomo->end_time)) }}
                                            </span>
                                            </div>
                                            <div class="text-gray-800 font-medium">
                                                <span id="name{{ $pomo->id }}">{{ $pomo->name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        <div class="flex space-x-3">
                                            <a href="/notes?source_type=1&source_id={{$pomo->id}}"
                                               class="text-sm text-gray-600 hover:text-blue-600">
                                                <i class="fas fa-sticky-note mr-1"></i>记录想法
                                            </a>
                                            <button class="update_pomo text-sm text-gray-600 hover:text-green-600"
                                                    pomo_name="{{ $pomo->name }}"
                                                    pomo_id="{{ $pomo->id }}">
                                                <i class="fas fa-edit mr-1"></i>编辑
                                            </button>
                                        </div>
                                        <button class="delete_pomo text-sm text-red-600 hover:text-red-800"
                                                pomo_value="{{ $pomo->id }}"
                                                pomo_token="{{ csrf_token() }}">
                                            <i class="fas fa-trash mr-1"></i>删除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 分页 -->
                    @if($pomos->hasPages())
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex flex-col md:flex-row items-center justify-between">
                                <div class="text-sm text-gray-500 mb-4 md:mb-0">
                                    显示 {{ $pomos->firstItem() }} - {{ $pomos->lastItem() }} 条，共 {{ $pomos->total() }} 条记录
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($pomos->onFirstPage())
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                    @else
                                        <a href="{{ $pomos->previousPageUrl() }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach(range(1, $pomos->lastPage()) as $page)
                                        @if($page == $pomos->currentPage())
                                            <button class="btn btn-sm btn-primary">{{ $page }}</button>
                                        @else
                                            <a href="{{ $pomos->url($page) }}" class="btn btn-sm btn-outline">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if($pomos->hasMorePages())
                                        <a href="{{ $pomos->nextPageUrl() }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                @else
                    <!-- 空状态 -->
                    <div class="text-center py-16">
                        <div class="mb-6">
                            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-100 mb-4">
                                <i class="fas fa-clock text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">还没有完成的番茄哦</h3>
                            <p class="text-gray-600 mb-6">快去开始您的第一个番茄工作吧！</p>
                        </div>
                        <a href="{{url('/index')}}" class="btn btn-primary">
                            <i class="fas fa-play-circle mr-2"></i>
                            开始第一个番茄
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- 使用说明 -->
        @if(count($pomos) > 0)
            <div class="mt-8 card">
                <div class="p-6">
                    <div class="flex items-start space-x-3">
                        <div class="rounded-full bg-blue-100 p-2 mt-1">
                            <i class="fas fa-lightbulb text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">如何高效回顾番茄历史</h3>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>每日回顾</strong>：每天结束时查看当天的番茄记录，评估工作效率</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>点击记录图标</strong>：为重要的番茄添加详细的工作笔记和心得</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>批量删除</strong>：定期清理无效的番茄记录，保持列表整洁</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>数据统计</strong>：点击上方的"数据统计"按钮查看您的长期番茄趋势和效率分析</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection