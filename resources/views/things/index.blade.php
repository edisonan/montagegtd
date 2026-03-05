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
        .empty-state.hidden {
            display: none !important;
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

        .section-body {
            padding: 0 8px;
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

            .section-body {
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
                <span class="badge badge-primary" id="thing_count_badge">0 项</span>
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
        <div id="thing_success_alert" class="alert-success fade-in hidden">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="thing_success_text"></span>
        </div>

        <!-- 主内容区域 -->
        <div class="fade-in">
            <div class="section-body">
                <!-- 桌面端表格 -->
                <div class="hidden md:block" id="things_desktop_wrap">
                    <div class="thing-table-container">
                        <table class="thing-table">
                            <thead>
                            <tr>
                                <th style="width: 200px;">时间信息</th>
                                <th>完成的事情</th>
                                <th style="width: 150px; text-align: right;">操作</th>
                            </tr>
                            </thead>
                            <tbody id="things_desktop_body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- 移动端卡片视图 -->
                <div class="md:hidden space-y-3" id="things_mobile_list"></div>

                <!-- 分页 -->
                <div class="pagination-wrapper" id="things_pagination">
                    <div class="flex items-center gap-3">
                        <button type="button" class="btn btn-outline btn-sm" id="things_prev_page">
                            <i class="fas fa-chevron-left mr-1"></i>上一页
                        </button>
                        <span class="text-sm text-gray-600" id="things_page_text">第 1 页</span>
                        <button type="button" class="btn btn-outline btn-sm" id="things_next_page">
                            下一页<i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>

                <!-- 空状态 -->
                <div class="empty-state hidden" id="things_empty_state">
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
            </div>
        </div>
    </div>

    @include('components.thing-create-modal')


    <script src="{{ '/js/My97DatePicker/WdatePicker.js' }}"></script>
    <script type="text/javascript">
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        var thingPageState = {
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: 0,
            things: []
        };

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function formatDateParts(dateTime) {
            if (!dateTime) {
                return {md: '-', hm: '-'};
            }
            var d = new Date(String(dateTime).replace(' ', 'T'));
            if (isNaN(d.getTime())) {
                return {md: '-', hm: '-'};
            }
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var dd = String(d.getDate()).padStart(2, '0');
            var hh = String(d.getHours()).padStart(2, '0');
            var mi = String(d.getMinutes()).padStart(2, '0');
            return {md: mm + '-' + dd, hm: hh + ':' + mi};
        }

        function renderThingsList() {
            var things = thingPageState.things || [];
            $('#thing_count_badge').text((thingPageState.total || 0) + ' 项');

            if (!things.length) {
                $('#things_desktop_wrap').addClass('hidden');
                $('#things_mobile_list').addClass('hidden');
                $('#things_pagination').addClass('hidden');
                $('#things_empty_state').removeClass('hidden');
                return;
            }

            $('#things_desktop_wrap').removeClass('hidden');
            $('#things_mobile_list').removeClass('hidden');
            $('#things_pagination').removeClass('hidden');
            $('#things_empty_state').addClass('hidden');

            var desktopBody = $('#things_desktop_body');
            var mobileList = $('#things_mobile_list');
            desktopBody.empty();
            mobileList.empty();

            var lastDate = '';
            things.forEach(function(thing) {
                var start = formatDateParts(thing.start_time);
                var end = formatDateParts(thing.end_time);
                var showDate = start.md !== lastDate;
                lastDate = start.md;

                desktopBody.append(
                    '<tr id="thing-row-' + thing.id + '">' +
                    '<td><div class="time-display">' +
                    '<span class="date-badge"' + (showDate ? '' : ' style="background: transparent; color: transparent;"') + '>' + start.md + '</span>' +
                    '<span class="time-range">' + start.hm + ' - ' + end.hm + '</span>' +
                    '</div></td>' +
                    '<td><div class="thing-content">' +
                    '<img src="/img/icon/thing' + (thing.type || 1) + '.png" alt="事情类型图标" class="thing-icon">' +
                    '<span class="thing-name" title="' + escapeHtml(thing.name || '') + '">' + escapeHtml(thing.name || '') + '</span>' +
                    '</div></td>' +
                    '<td><div class="action-buttons">' +
                    '<a href="/notes?source_type=4&source_id=' + thing.id + '" class="action-link" title="记录更多当时的想法"><i class="fas fa-sticky-note" style="font-size: 1rem;"></i></a>' +
                    '<a href="/thing/' + thing.id + '" class="action-link" title="编辑事情"><i class="fas fa-edit" style="font-size: 1rem;"></i></a>' +
                    '<button type="button" class="action-link delete delete_thing" thing_value="' + thing.id + '" title="删除事情"><i class="fas fa-trash-alt" style="font-size: 1rem;"></i></button>' +
                    '</div></td>' +
                    '</tr>'
                );

                mobileList.append(
                    '<div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-150" id="mobile-thing-' + thing.id + '">' +
                    '<div class="p-4">' +
                    (showDate ? '<div class="mb-3"><span class="date-badge">' + start.md + '</span></div>' : '') +
                    '<div class="flex justify-between items-start">' +
                    '<div class="flex-1 pr-4">' +
                    '<div class="flex items-center gap-2 mb-2">' +
                    '<img src="/img/icon/thing' + (thing.type || 1) + '.png" alt="事情类型图标" class="w-5 h-5 flex-shrink-0">' +
                    '<span class="font-medium text-gray-800 text-sm truncate" title="' + escapeHtml(thing.name || '') + '">' + escapeHtml(thing.name || '') + '</span>' +
                    '</div>' +
                    '<div class="text-xs text-gray-600"><i class="far fa-clock mr-1"></i>' + start.hm + ' - ' + end.hm + '</div>' +
                    '</div>' +
                    '<div class="flex gap-1">' +
                    '<a href="/notes?source_type=4&source_id=' + thing.id + '" class="action-link" title="记录想法"><i class="fas fa-sticky-note"></i></a>' +
                    '<a href="/thing/' + thing.id + '" class="action-link" title="编辑"><i class="fas fa-edit"></i></a>' +
                    '</div></div>' +
                    '<div class="flex gap-2 pt-3 mt-3 border-t border-gray-100">' +
                    '<button type="button" class="flex-1 py-2 px-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors duration-150 delete_thing" thing_value="' + thing.id + '">' +
                    '<i class="fas fa-trash-alt mr-1"></i>删除</button>' +
                    '</div></div></div>'
                );
            });

            $('#things_page_text').text('第 ' + thingPageState.currentPage + ' / ' + thingPageState.lastPage + ' 页');
            $('#things_prev_page').prop('disabled', thingPageState.currentPage <= 1);
            $('#things_next_page').prop('disabled', thingPageState.currentPage >= thingPageState.lastPage);
        }

        function loadThings(page) {
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            var targetPage = page || thingPageState.currentPage || 1;
            apiRequest('GET', '/things?page=' + targetPage + '&page_size=' + thingPageState.perPage, {}).then(function(resultArr) {
                if (!resultArr || resultArr.code != 9999) {
                    alert((resultArr && resultArr.msg) ? resultArr.msg : '加载失败');
                    return;
                }
                var pagination = (resultArr.result && resultArr.result.pagination) ? resultArr.result.pagination : {};
                thingPageState.things = (resultArr.result && resultArr.result.things) ? resultArr.result.things : [];
                thingPageState.currentPage = Number(pagination.current_page || targetPage || 1);
                thingPageState.lastPage = Number(pagination.last_page || 1);
                thingPageState.total = Number(pagination.total || thingPageState.things.length || 0);
                renderThingsList();
            }).catch(function() {
                alert('加载失败，请重试');
            });
        }

        $(document).ready(function () {
            // 删除功能
            $(document).on('click', '.delete_thing', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var thingValue = $(this).attr('thing_value');
                if (!confirm("确认要删除此事情吗？")) {
                    return false;
                }

                if (!apiRequest) {
                    alert('API客户端未初始化');
                    return false;
                }

                apiRequest('DELETE', '/things/' + thingValue, {}).then(function(resultArr) {
                    if (resultArr.code != 9999) {
                        alert(resultArr.msg || '删除失败');
                    } else {
                        loadThings(thingPageState.currentPage);
                    }
                }).catch(function() {
                    alert('删除失败，请重试');
                });
            });

            $('#things_prev_page').on('click', function() {
                if (thingPageState.currentPage > 1) {
                    loadThings(thingPageState.currentPage - 1);
                }
            });

            $('#things_next_page').on('click', function() {
                if (thingPageState.currentPage < thingPageState.lastPage) {
                    loadThings(thingPageState.currentPage + 1);
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

            loadThings(1);
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
