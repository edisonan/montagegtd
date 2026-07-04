@extends('layouts.app')

@section('title', '待办事项列表 - 蒙太奇')
@section('description', '查看和管理所有待办事项，跟踪任务进度和状态')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('common.success')

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">待办事项列表</h1>
                <p class="text-gray-600">管理和跟踪您的所有任务，使用四象限法则优先处理重要事项</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{'/index'}}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>返回首页
                </a>
                <a href="{{'/taskpriority'}}" class="btn btn-primary">
                    <i class="fas fa-th-large mr-2"></i>四象限视图
                </a>
            </div>
        </div>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-1 bg-gray-100 rounded-lg p-1" id="taskStatusFilters">
                <button type="button" class="px-4 py-2 rounded-md text-sm font-medium transition-colors" data-status="1">
                    <i class="fas fa-spinner mr-2"></i>进行中
                    <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs" id="count-active">0</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-md text-sm font-medium transition-colors" data-status="2">
                    <i class="fas fa-check-circle mr-2"></i>已完成
                    <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs" id="count-completed">0</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-md text-sm font-medium transition-colors" data-status="3">
                    <i class="fas fa-folder mr-2"></i>已折叠
                    <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs" id="count-folded">0</span>
                </button>
                <button type="button" class="px-4 py-2 rounded-md text-sm font-medium transition-colors" data-status="all">
                    <i class="fas fa-list mr-2"></i>全部
                    <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs" id="count-total">0</span>
                </button>
            </div>

            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" placeholder="搜索任务..." class="input pl-10 pr-4 py-2 w-64" id="taskSearch">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">任务列表</h2>
                <span class="text-sm text-gray-500" id="taskRecordCount">共 0 条记录</span>
            </div>

            <div class="p-6">
                <div id="taskEmptyState" class="text-center py-16 hidden">
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-tasks text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">当前没有待办任务</h3>
                        <p class="text-gray-600 mb-6">创建您的第一个任务，开始高效工作吧！</p>
                    </div>
                    <a href="{{url('/index')}}" class="btn btn-outline ml-4">
                        <i class="fas fa-home mr-2"></i>返回首页
                    </a>
                </div>

                <div id="taskContentArea">
                    <div class="hidden md:block overflow-x-auto">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="w-24">状态</th>
                                <th class="w-40">更新日期</th>
                                <th>任务内容</th>
                                <th class="w-40">优先级</th>
                                <th class="w-32">操作</th>
                            </tr>
                            </thead>
                            <tbody id="taskTableBody"></tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-4" id="taskMobileList"></div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between" id="taskPaginationWrap">
                        <div class="text-sm text-gray-500" id="taskPaginationText"></div>
                        <div class="flex items-center space-x-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="taskPrevPage">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="text-sm text-gray-600" id="taskPageIndicator"></span>
                            <button type="button" class="btn btn-sm btn-secondary" id="taskNextPage">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.task-update-modal')

    <script>
        function getApiRequest() {
            if (window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function') {
                return window.TaskApiBridge.requestWithFallback;
            }
            return null;
        }

        function withApiReady(fn) {
            var bootstrap = window.__taskTokenBootstrapPromise;
            if (bootstrap && typeof bootstrap.then === 'function') {
                return bootstrap.finally(fn);
            }
            fn();
            return Promise.resolve();
        }

        var taskPageState = {
            status: '1',
            page: 1,
            perPage: 20,
            total: 0,
            lastPage: 1,
            tasks: [],
            statusCounts: {
                active: 0,
                completed: 0,
                folded: 0,
                total: 0
            },
            hasStatsLoaded: false
        };

        function getQueryParam(name) {
            var params = new URLSearchParams(window.location.search || '');
            return params.get(name);
        }

        function updateUrl() {
            var params = new URLSearchParams(window.location.search || '');
            params.set('status', taskPageState.status);
            params.set('page', String(taskPageState.page));
            window.history.replaceState({}, '', window.location.pathname + '?' + params.toString());
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function formatDateTime(value) {
            if (!value) {
                return {date: '-', time: '-'};
            }
            var d = new Date(value.replace(' ', 'T'));
            if (isNaN(d.getTime())) {
                return {date: value, time: ''};
            }
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var dd = String(d.getDate()).padStart(2, '0');
            var hh = String(d.getHours()).padStart(2, '0');
            var mi = String(d.getMinutes()).padStart(2, '0');
            return {date: mm + '-' + dd, time: hh + ':' + mi};
        }

        function statusBadge(status) {
            if (Number(status) === 2) {
                return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>已完成</span>';
            }
            if (Number(status) === 3) {
                return '<span class="badge badge-secondary"><i class="fas fa-folder mr-1"></i>已折叠</span>';
            }
            return '<span class="badge badge-primary"><i class="fas fa-spinner mr-1"></i>进行中</span>';
        }

        function priorityBadge(priority) {
            if (Number(priority) === 4) {
                return '<span class="badge bg-red-100 text-red-700"><i class="fas fa-exclamation-circle mr-1"></i>重要紧急</span>';
            }
            if (Number(priority) === 3) {
                return '<span class="badge bg-orange-100 text-orange-700"><i class="fas fa-star mr-1"></i>重要不紧急</span>';
            }
            if (Number(priority) === 2) {
                return '<span class="badge bg-blue-100 text-blue-700"><i class="fas fa-bolt mr-1"></i>紧急不重要</span>';
            }
            return '<span class="badge bg-gray-100 text-gray-700"><i class="fas fa-ellipsis-h mr-1"></i>不重要不紧急</span>';
        }

        function renderTasks() {
            var tasks = taskPageState.tasks || [];
            var tbody = $('#taskTableBody');
            var mobile = $('#taskMobileList');
            tbody.empty();
            mobile.empty();

            tasks.forEach(function(task) {
                var updated = formatDateTime(task.updated_at);
                var parentName = (task.parentTask && task.parentTask.name) ? task.parentTask.name : '';
                var taskNameHtml = parentName
                    ? '<span class="text-gray-500 text-sm">' + escapeHtml(parentName) + ' →</span> ' + escapeHtml(task.name)
                    : escapeHtml(task.name);

                var desktopRow = '' +
                    '<tr>' +
                    '<td>' + statusBadge(task.status) + '</td>' +
                    '<td><div class="flex flex-col"><span class="text-sm font-medium text-gray-900">' + updated.date + '</span><span class="text-xs text-gray-500">' + updated.time + '</span></div></td>' +
                    '<td><div class="font-medium text-gray-900">' + taskNameHtml + '</div></td>' +
                    '<td>' + priorityBadge(task.priority) + '</td>' +
                    '<td>' +
                    '<div class="flex items-center justify-end space-x-3">' +
                    '<a href="/notes?source_type=3&source_id=' + task.id + '" class="text-gray-400 hover:text-blue-600" title="添加笔记"><i class="fas fa-sticky-note"></i></a>' +
                    '<button onclick="editTask(' + task.id + ')" class="text-gray-400 hover:text-green-600" title="编辑任务"><i class="fas fa-edit"></i></button>' +
                    (Number(task.status) === 1 ? '<button onclick="completeTask(' + task.id + ')" class="text-gray-400 hover:text-green-600" title="标记完成"><i class="fas fa-check"></i></button>' : '') +
                    '</div>' +
                    '</td>' +
                    '</tr>';

                var mobileCard = '' +
                    '<div class="card border-l-4 border-gray-300"><div class="p-4">' +
                    '<div class="flex items-center justify-between mb-2">' + statusBadge(task.status) + '<span class="text-xs text-gray-500">' + updated.date + ' ' + updated.time + '</span></div>' +
                    '<div class="font-medium text-gray-900 mb-3">' + taskNameHtml + '</div>' +
                    '<div class="flex items-center justify-between">' +
                    '<div>' + priorityBadge(task.priority) + '</div>' +
                    '<div class="flex items-center space-x-3">' +
                    '<button onclick="editTask(' + task.id + ')" class="text-sm text-gray-600 hover:text-green-600"><i class="fas fa-edit mr-1"></i>编辑</button>' +
                    (Number(task.status) === 1 ? '<button onclick="completeTask(' + task.id + ')" class="text-sm text-green-600 hover:text-green-800"><i class="fas fa-check mr-1"></i>完成</button>' : '') +
                    '</div></div></div></div>';

                tbody.append(desktopRow);
                mobile.append(mobileCard);
            });

            $('#taskRecordCount').text('共 ' + taskPageState.total + ' 条记录');
            $('#taskEmptyState').toggle(tasks.length === 0);
            $('#taskContentArea').toggle(tasks.length > 0);

            var start = taskPageState.total === 0 ? 0 : ((taskPageState.page - 1) * taskPageState.perPage + 1);
            var end = Math.min(taskPageState.total, taskPageState.page * taskPageState.perPage);
            $('#taskPaginationText').text('显示 ' + start + ' - ' + end + ' 条，共 ' + taskPageState.total + ' 条记录');
            $('#taskPageIndicator').text('第 ' + taskPageState.page + ' / ' + taskPageState.lastPage + ' 页');
            $('#taskPrevPage').prop('disabled', taskPageState.page <= 1);
            $('#taskNextPage').prop('disabled', taskPageState.page >= taskPageState.lastPage);
        }

        function setFilterActive() {
            $('#taskStatusFilters button').each(function() {
                var active = $(this).data('status') == taskPageState.status;
                $(this).toggleClass('bg-white text-gray-900 shadow-sm', active);
                $(this).toggleClass('text-gray-600 hover:text-gray-900 hover:bg-gray-200', !active);
            });
        }

        function loadTaskStats() {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                return Promise.resolve();
            }

            return apiRequest('GET', '/tasks/tab-counts', {}).then(function(resp) {
                if (!resp || resp.code !== 9999 || !resp.result) {
                    return;
                }
                taskPageState.statusCounts = {
                    active: Number(resp.result.active || 0),
                    completed: Number(resp.result.completed || 0),
                    folded: Number(resp.result.folded || 0),
                    total: Number(resp.result.total || 0)
                };
                taskPageState.hasStatsLoaded = true;
                $('#count-active').text(taskPageState.statusCounts.active);
                $('#count-completed').text(taskPageState.statusCounts.completed);
                $('#count-folded').text(taskPageState.statusCounts.folded);
                $('#count-total').text(taskPageState.statusCounts.total);
            }).catch(function() {
                // ignore stats failure
            });
        }

        function loadTasks() {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }

            var url = '/tasks?status=' + encodeURIComponent(taskPageState.status)
                + '&page_count=' + taskPageState.perPage
                + '&page=' + taskPageState.page;

            apiRequest('GET', url, {}).then(function(response) {
                if (response.code !== 9999) {
                    alert(response.msg || '加载任务失败');
                    return;
                }
                var result = response.result || {};
                var pagination = result.pagination || {};
                taskPageState.tasks = result.tasks || [];

                var currentStatus = String(taskPageState.status || '1');
                var totalByStatus = 0;
                if (taskPageState.hasStatsLoaded) {
                    if (currentStatus === '2') {
                        totalByStatus = taskPageState.statusCounts.completed;
                    } else if (currentStatus === '3') {
                        totalByStatus = taskPageState.statusCounts.folded;
                    } else if (currentStatus === 'all') {
                        totalByStatus = taskPageState.statusCounts.total;
                    } else {
                        totalByStatus = taskPageState.statusCounts.active;
                    }
                } else {
                    totalByStatus = Number(taskPageState.tasks.length || 0);
                }

                taskPageState.total = totalByStatus;
                taskPageState.page = Math.max(1, Number(pagination.current_page || taskPageState.page));
                taskPageState.lastPage = Math.max(1, Math.ceil((taskPageState.total || 0) / taskPageState.perPage));
                if (taskPageState.lastPage < taskPageState.page) {
                    taskPageState.lastPage = taskPageState.page;
                }

                if (pagination.has_more_pages) {
                    taskPageState.lastPage = Math.max(taskPageState.lastPage, taskPageState.page + 1);
                }

                setFilterActive();
                renderTasks();
                updateUrl();
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        function completeTask(taskId) {
            if (!confirm('确认将此任务标记为已完成吗？')) {
                return;
            }
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('PUT', '/tasks/' + taskId, {status: 2}).then(function(response) {
                if (response.code === 9999) {
                    loadTasks();
                } else {
                    alert('操作失败: ' + (response.msg || '未知错误'));
                }
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        function editTask(taskId) {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('GET', '/tasks/' + taskId, {}).then(function(response) {
                if (response.code === 9999) {
                    openTaskUpdateModal(response.result);
                } else {
                    alert('获取任务数据失败：' + (response.msg || '未知错误'));
                }
            }).catch(function() {
                alert('获取任务数据失败');
            });
        }

        $(document).ready(function() {
            taskPageState.status = getQueryParam('status') || '1';
            taskPageState.page = Math.max(1, Number(getQueryParam('page') || 1));

            $('#taskStatusFilters').on('click', 'button', function() {
                taskPageState.status = String($(this).data('status'));
                taskPageState.page = 1;
                loadTasks();
            });

            $('#taskPrevPage').on('click', function() {
                if (taskPageState.page > 1) {
                    taskPageState.page -= 1;
                    loadTasks();
                }
            });

            $('#taskNextPage').on('click', function() {
                if (taskPageState.page < taskPageState.lastPage) {
                    taskPageState.page += 1;
                    loadTasks();
                }
            });

            $('#taskSearch').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    var searchTerm = $(this).val();
                    if (searchTerm) {
                        window.location.href = '{{ url("/tasks") }}?search=' + encodeURIComponent(searchTerm);
                    }
                }
            });

            withApiReady(function() {
                loadTaskStats().finally(function() {
                    loadTasks();
                });
            });
        });
    </script>
@endsection
