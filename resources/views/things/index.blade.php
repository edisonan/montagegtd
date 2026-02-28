@extends('layouts.app')
@section('content')
    <style>
        /* 优化文本截断 */
        .rowone {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }

        /* 事情列表优化 */
        .thing-table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
        }

        .thing-table {
            width: 100%;
            border-collapse: collapse;
        }

        .thing-table thead th {
            background: #f8fafc;
            padding: 16px 20px;
            font-weight: 600;
            color: #334155;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .thing-table tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #e2e8f0;
        }

        .thing-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .thing-table tbody tr:last-child {
            border-bottom: none;
        }

        .thing-table td {
            padding: 16px 20px;
            vertical-align: middle;
        }

        /* 时间显示优化 */
        .time-display {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .date-badge {
            background: rgba(59, 130, 246, 0.1);
            color: #4a90e2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            min-width: 60px;
            text-align: center;
            flex-shrink: 0;
        }

        .time-range {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* 事情内容区域 */
        .thing-content {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .thing-icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        .thing-name {
            font-weight: 500;
            color: #1e293b;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.95rem;
        }

        /* 操作按钮优化 */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-end;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            color: #64748b;
            transition: all 0.2s ease;
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
        }

        .action-link:hover {
            background: #f1f5f9;
            color: #4a90e2;
        }

        .action-link.delete:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* 空状态优化 */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
        }

        .empty-state img {
            margin-bottom: 24px;
            opacity: 0.9;
            max-width: 160px;
            height: auto;
        }

        .empty-state-text {
            color: #64748b;
            margin-bottom: 16px;
            font-size: 1rem;
        }

        .empty-state-link {
            color: #4a90e2;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .empty-state-link:hover {
            color: #8a6cff;
            text-decoration: underline;
        }

        /* 页面头部优化 */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 0 8px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: #4a90e2;
        }

        /* 分页优化 */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        /* 卡片样式 */
        .thing-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .thing-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .thing-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .thing-card-body {
            padding: 24px;
        }

        /* 按钮组 */
        .btn-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* 成功消息样式 */
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #047857;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid rgba(16, 185, 129, 0.2);
            margin-bottom: 24px;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }

            .btn-group {
                width: 100%;
            }

            .btn-group .btn {
                flex: 1;
                justify-content: center;
            }

            .thing-table-container {
                border-radius: 8px;
            }

            .thing-table thead {
                display: none;
            }

            .thing-table tbody tr {
                display: flex;
                flex-direction: column;
                padding: 16px;
                border-bottom: 1px solid #e2e8f0;
            }

            .thing-table td {
                padding: 8px 0;
                border: none;
            }

            .time-display {
                margin-bottom: 12px;
                justify-content: space-between;
            }

            .action-buttons {
                margin-top: 16px;
                justify-content: center;
            }

            .thing-content {
                margin-top: 8px;
            }

            .thing-card-body {
                padding: 16px;
            }
        }

        /* 动画效果 */
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面头部 -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-check-circle"></i>
                事情列表
            </h1>
            <div class="btn-group">
                <button type="button" class="btn btn-primary" data-toggle="modal"  onclick="showThingCreateModal()">
                    <i class="fas fa-plus mr-2"></i>
                    新增事情
                </button>
                <a href="{{ url('/index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回
                </a>
            </div>
        </div>

        <!-- 成功消息提示 -->
        @if(session('success'))
            <div class="alert-success fade-in">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- 主内容区域 -->
        <div class="thing-card fade-in">
            <div class="thing-card-header">
                <h2 class="thing-card-title">已完成的事情</h2>
                <span class="badge badge-primary">
                {{ count($things) }} 项
            </span>
            </div>

            <div class="thing-card-body">
                @if (count($things) > 0)
                    <!-- 桌面端表格 -->
                    <div class="hidden md:block">
                        <div class="thing-table-container">
                            <table class="thing-table">
                                <thead>
                                <tr>
                                    <th style="width: 200px;">时间信息</th>
                                    <th>完成的事情</th>
                                    <th style="width: 150px; text-align: right;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $lastDate = ''; @endphp
                                @foreach ($things as $thing)
                                    @php
                                        $currentDate = date('m-d', strtotime($thing->start_time));
                                        $showDate = $currentDate != $lastDate;
                                        $lastDate = $currentDate;
                                    @endphp
                                    <tr id="{{ $thing->id }}">
                                        <td>
                                            <div class="time-display">
                                                @if ($showDate)
                                                    <span class="date-badge">{{ $currentDate }}</span>
                                                @else
                                                    <span class="date-badge" style="background: transparent; color: transparent;">{{ $currentDate }}</span>
                                                @endif
                                                <span class="time-range">
                                                    {{ date('H:i', strtotime($thing->start_time)) }} - {{ date('H:i', strtotime($thing->end_time)) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="thing-content">
                                                <img src="/img/icon/thing{{ $thing->type }}.png"
                                                     alt="事情类型图标"
                                                     class="thing-icon">
                                                <span class="thing-name" title="{{ $thing->name }}">
                                                    {{ $thing->name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="/notes?source_type=4&source_id={{ $thing->id }}"
                                                   class="action-link"
                                                   title="记录更多当时的想法">
                                                    <i class="fas fa-sticky-note" style="font-size: 1rem;"></i>
                                                </a>
                                                <a href="{{ url('thing/'.$thing->id) }}"
                                                   class="action-link"
                                                   title="编辑事情">
                                                    <i class="fas fa-edit" style="font-size: 1rem;"></i>
                                                </a>
                                                <button type="button"
                                                        class="action-link delete delete_thing"
                                                        thing_type="delete"
                                                        thing_value="{{ $thing->id }}"
                                                        thing_token="{{ csrf_token() }}"
                                                        title="删除事情">
                                                    <i class="fas fa-trash-alt" style="font-size: 1rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 移动端卡片视图 -->
                    <div class="md:hidden space-y-3">
                        @php $lastDate = ''; @endphp
                        @foreach ($things as $thing)
                            @php
                                $currentDate = date('m-d', strtotime($thing->start_time));
                                $showDate = $currentDate != $lastDate;
                                $lastDate = $currentDate;
                            @endphp
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-150" id="mobile-{{ $thing->id }}">
                                <div class="p-4">
                                    @if ($showDate)
                                        <div class="mb-3">
                                            <span class="date-badge">{{ $currentDate }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 pr-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <img src="/img/icon/thing{{ $thing->type }}.png"
                                                     alt="事情类型图标"
                                                     class="w-5 h-5 flex-shrink-0">
                                                <span class="font-medium text-gray-800 text-sm truncate" title="{{ $thing->name }}">
                                                {{ $thing->name }}
                                            </span>
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ date('H:i', strtotime($thing->start_time)) }} - {{ date('H:i', strtotime($thing->end_time)) }}
                                            </div>
                                        </div>
                                        <div class="flex gap-1">
                                            <a href="/notes?source_type=4&source_id={{ $thing->id }}"
                                               class="action-link"
                                               title="记录想法">
                                                <i class="fas fa-sticky-note"></i>
                                            </a>
                                            <a href="{{ url('thing/'.$thing->id) }}"
                                               class="action-link"
                                               title="编辑">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 pt-3 mt-3 border-t border-gray-100">
                                        <button type="button"
                                                class="flex-1 py-2 px-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors duration-150 delete_thing"
                                                thing_type="delete"
                                                thing_value="{{ $thing->id }}"
                                                thing_token="{{ csrf_token() }}">
                                            <i class="fas fa-trash-alt mr-1"></i>
                                            删除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 分页 -->
                    <div class="pagination-wrapper">
                        {!! $things->links() !!}
                    </div>
                @else
                    <!-- 空状态 -->
                    <div class="empty-state">
                        <img src="/img/new/love.png" alt="暂无事情" width="120">
                        <p class="empty-state-text mb-4">
                            暂时还没有记录完成的事情哦～
                        </p>
                        <p class="text-gray-500 mb-6 text-sm">
                            快去开始做点番茄或者考虑一下待办事项吧！
                        </p>
                        <a href="{{ url('/index') }}" class="btn btn-primary">
                            <i class="fas fa-play mr-2"></i>
                            开始工作
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.thing-create-modal')


    <script src="{{ '/js/My97DatePicker/WdatePicker.js' }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            // 删除功能
            $(".delete_thing").click(function (e) {
                e.preventDefault();
                e.stopPropagation();

                var thing_value = $(this).attr("thing_value");
                var thing_token = $(this).attr("thing_token");
                var thing_type = $(this).attr("thing_type");

                if (thing_type == 'delete' && !confirm("确认要删除此事情吗？")) {
                    return false;
                }

                $.ajax({
                    url: "{{ url('thing') }}" + "/" + thing_value,
                    type: 'DELETE',
                    data: {type: thing_type, _token: thing_token},
                    success: function (result_arr) {
                        if (result_arr.code != 9999) {
                            alert(result_arr.msg);
                        } else {
                            // 桌面端移除对应行
                            $('#' + thing_value).fadeOut(300, function() {
                                $(this).remove();
                            });
                            // 移动端移除对应卡片
                            $('#mobile-' + thing_value).fadeOut(300, function() {
                                $(this).remove();
                            });

                            // 如果列表为空，刷新页面
                            if ($('.thing-table tbody tr').length === 0 && $('.md\\:hidden .bg-white').length === 0) {
                                setTimeout(function() {
                                    location.reload();
                                }, 500);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('删除失败，请重试');
                    }
                });
            });

            // 表格行悬停效果
            $('.thing-table tbody tr').hover(
                function() {
                    $(this).css('background-color', '#f8fafc');
                },
                function() {
                    $(this).css('background-color', '');
                }
            );

            // 移动端卡片点击效果
            $('.md\\:hidden .bg-white').click(function(e) {
                // 如果不是点击链接或按钮，添加点击效果
                if (!$(e.target).closest('a, button').length) {
                    $(this).toggleClass('shadow-md');
                    setTimeout(() => {
                        $(this).toggleClass('shadow-md');
                    }, 300);
                }
            });

            // 初始化日期选择器（如果存在）
            if (typeof WdatePicker !== 'undefined') {
                // 检查模态框中的时间输入框
                setTimeout(() => {
                    $('#thingCreateModal input[type="text"]').each(function() {
                        if ($(this).attr('onclick') && $(this).attr('onclick').includes('WdatePicker')) {
                            $(this).click(function() {
                                eval($(this).attr('onclick'));
                            });
                        }
                    });
                }, 500);
            }
        });

        // 简单的通知函数
        function showNotification(message, type) {
            var bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            var $notification = $(
                '<div class="fixed top-4 right-4 z-50 max-w-sm w-full">' +
                '<div class="' + bgColor + ' text-white p-4 rounded-lg shadow-lg flex items-center justify-between">' +
                '<div class="flex items-center">' +
                '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-3"></i>' +
                '<span>' + message + '</span>' +
                '</div>' +
                '<button class="text-white hover:text-gray-200 ml-4" onclick="$(this).closest(\'.fixed\').remove()">' +
                '<i class="fas fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>'
            );

            $('body').append($notification);

            // 5秒后自动消失
            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>

@endsection