@extends('layouts.app')

@section('title', '待办事项列表 - 蒙太奇')
@section('description', '查看和管理所有待办事项，跟踪任务进度和状态')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('common.success')

        <!-- 页面标题和操作栏 -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">待办事项列表</h1>
                <p class="text-gray-600">管理和跟踪您的所有任务，使用四象限法则优先处理重要事项</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{'/index'}}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回首页
                </a>
                <a href="{{'/taskpriority'}}" class="btn btn-primary">
                    <i class="fas fa-th-large mr-2"></i>
                    四象限视图
                </a>
                <button onclick="openTaskUpdateModal()" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    新建任务
                </button>
            </div>
        </div>

        <!-- 状态筛选和统计 -->
        <div class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- 状态筛选 -->
                <div class="flex items-center space-x-1 bg-gray-100 rounded-lg p-1">
                    <a href="{{'/tasks'}}?status=1&need_page=1"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                          {{ request('status') == 1 || !request('status') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                        <i class="fas fa-spinner mr-2"></i>进行中
                        <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $stats['active'] ?? 0 }}</span>
                    </a>
                    <a href="{{'/tasks'}}?status=2&need_page=1"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                          {{ request('status') == 2 ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                        <i class="fas fa-check-circle mr-2"></i>已完成
                        <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $stats['completed'] ?? 0 }}</span>
                    </a>
                    <a href="{{'/tasks'}}?status=3&need_page=1"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                          {{ request('status') == 3 ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                        <i class="fas fa-folder mr-2"></i>已折叠
                        <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $stats['folded'] ?? 0 }}</span>
                    </a>
                    <a href="{{'/tasks'}}?status=all&need_page=1"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                          {{ request('status') == 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                        <i class="fas fa-list mr-2"></i>全部
                        <span class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $stats['total'] ?? 0 }}</span>
                    </a>
                </div>

                <!-- 快速操作 -->
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text"
                               placeholder="搜索任务..."
                               class="input pl-10 pr-4 py-2 w-64"
                               id="taskSearch">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button class="btn btn-sm btn-outline" id="exportTasks">
                        <i class="fas fa-download mr-2"></i>
                        导出
                    </button>
                </div>
            </div>
        </div>

        <!-- 待办列表 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-xl font-semibold text-gray-900">任务列表</h2>
                        <span class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        共 {{ count($tasks) }} 条记录
                    </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                                title="列表视图">
                            <i class="fas fa-list"></i>
                        </button>
                        <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                                title="分组视图">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if (count($tasks) > 0)
                    <!-- 桌面端表格视图 -->
                    <div class="hidden md:block">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="w-24">状态</th>
                                <th class="w-40">更新日期</th>
                                <th>任务内容</th>
                                <th class="w-40">优先级</th>
                                <th class="w-40">模式</th>
                                <th class="w-32">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php $lastDate = ''; ?>
                            @foreach ($tasks as $task)
                                    <?php
                                    $currentDate = date('m-d', strtotime($task->updated_at));
                                    $shouldShowDate = ($currentDate != $lastDate);
                                    $lastDate = $currentDate;
                                    ?>
                                <tr class="hover:bg-gray-50 transition-colors
                                    {{ $task->priority == 4 ? 'bg-red-50 hover:bg-red-100' :
                                       ($task->priority == 3 ? 'bg-orange-50 hover:bg-orange-100' :
                                       ($task->priority == 2 ? 'bg-blue-50 hover:bg-blue-100' : '')) }}">
                                    <td>
                                        <div class="flex items-center">
                                            @if($task->status == 1)
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-spinner mr-1"></i>进行中
                                                </span>
                                            @elseif($task->status == 2)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle mr-1"></i>已完成
                                                </span>
                                            @elseif($task->status == 3)
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-folder mr-1"></i>已折叠
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            @if($shouldShowDate)
                                                <span class="text-sm font-medium text-gray-900">{{ $currentDate }}</span>
                                            @endif
                                            <span class="text-xs text-gray-500">{{ date('H:i', strtotime($task->updated_at)) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-start">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 mb-1">
                                                    @if(isset($task->parentTask->name))
                                                        <span class="text-gray-500 text-sm">{{ $task->parentTask->name}} →</span>
                                                    @endif
                                                    {{ $task->name }}
                                                </div>
                                                @if($task->remindtime || $task->deadline)
                                                    <div class="flex items-center space-x-3 text-xs text-gray-500">
                                                        @if($task->remindtime)
                                                            <span>
                                                        <i class="far fa-bell mr-1"></i>
                                                        提醒: {{ date('m-d H:i', strtotime($task->remindtime)) }}
                                                    </span>
                                                        @endif
                                                        @if($task->deadline)
                                                            <span>
                                                        <i class="far fa-clock mr-1"></i>
                                                        截止: {{ date('m-d H:i', strtotime($task->deadline)) }}
                                                    </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            @if($task->is_top == 1)
                                                <div class="ml-2">
                                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                                                    <i class="fas fa-thumbtack"></i> 置顶
                                                </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->priority == 4)
                                            <span class="badge bg-red-100 text-red-700">
                                                <i class="fas fa-exclamation-circle mr-1"></i>重要紧急
                                            </span>
                                        @elseif($task->priority == 3)
                                            <span class="badge bg-orange-100 text-orange-700">
                                                <i class="fas fa-star mr-1"></i>重要不紧急
                                            </span>
                                        @elseif($task->priority == 2)
                                            <span class="badge bg-blue-100 text-blue-700">
                                                <i class="fas fa-bolt mr-1"></i>紧急不重要
                                            </span>
                                        @else
                                            <span class="badge bg-gray-100 text-gray-700">
                                                <i class="fas fa-ellipsis-h mr-1"></i>不重要不紧急
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->mode == 1)
                                            <span class="badge bg-blue-100 text-blue-700">
                                            <i class="fas fa-briefcase mr-1"></i>工作
                                        </span>
                                        @else
                                            <span class="badge bg-green-100 text-green-700">
                                            <i class="fas fa-home mr-1"></i>生活
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end space-x-3">
                                            <a href="/notes?source_type=3&source_id={{$task->id}}"
                                               class="text-gray-400 hover:text-blue-600 transition-colors"
                                               title="添加笔记">
                                                <i class="fas fa-sticky-note"></i>
                                            </a>
                                            <button onclick="callOpenTaskUpdateModal('{{ addslashes(json_encode($task->toArray())) }}')"
                                                    class="text-gray-400 hover:text-green-600 transition-colors"
                                                    title="编辑任务">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($task->status == 1)
                                                <button class="complete-task text-gray-400 hover:text-green-600 transition-colors"
                                                        data-task-id="{{ $task->id }}"
                                                        title="标记完成">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 移动端卡片视图 -->
                    <div class="md:hidden space-y-4">
                            <?php $lastDate = ''; ?>
                        @foreach ($tasks as $task)
                                <?php
                                $currentDate = date('m-d', strtotime($task->updated_at));
                                $shouldShowDate = ($currentDate != $lastDate);
                                $lastDate = $currentDate;
                                ?>
                            <div class="card hover:shadow-md transition-shadow
                            {{ $task->priority == 4 ? 'border-l-4 border-red-500' :
                               ($task->priority == 3 ? 'border-l-4 border-orange-500' :
                               ($task->priority == 2 ? 'border-l-4 border-blue-500' : 'border-l-4 border-gray-300')) }}">
                                <div class="p-4">
                                    <!-- 日期分隔 -->
                                    @if($shouldShowDate)
                                        <div class="mb-3 pb-2 border-b border-gray-100">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-calendar-day text-blue-500"></i>
                                                <span class="font-medium text-gray-900">{{ $currentDate }}</span>
                                                <span class="text-sm text-gray-500">{{ date('H:i', strtotime($task->updated_at)) }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- 任务内容 -->
                                    <div class="mb-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <div class="flex items-center flex-wrap gap-2 mb-2">
                                                    @if($task->status == 1)
                                                        <span class="badge badge-primary text-xs">进行中</span>
                                                    @elseif($task->status == 2)
                                                        <span class="badge badge-success text-xs">已完成</span>
                                                    @elseif($task->status == 3)
                                                        <span class="badge badge-secondary text-xs">已折叠</span>
                                                    @endif

                                                    @if($task->priority == 4)
                                                        <span class="badge bg-red-100 text-red-700 text-xs">重要紧急</span>
                                                    @elseif($task->priority == 3)
                                                        <span class="badge bg-orange-100 text-orange-700 text-xs">重要不紧急</span>
                                                    @elseif($task->priority == 2)
                                                        <span class="badge bg-blue-100 text-blue-700 text-xs">紧急不重要</span>
                                                    @endif

                                                    @if($task->mode == 1)
                                                        <span class="badge bg-blue-100 text-blue-700 text-xs">工作</span>
                                                    @else
                                                        <span class="badge bg-green-100 text-green-700 text-xs">生活</span>
                                                    @endif
                                                </div>

                                                <div class="font-medium text-gray-900">
                                                    @if(isset($task->parentTask->name))
                                                        <div class="text-sm text-gray-500 mb-1">
                                                            <i class="fas fa-level-up-alt rotate-90 mr-1"></i>
                                                            {{ $task->parentTask->name}}
                                                        </div>
                                                    @endif
                                                    {{ $task->name }}
                                                </div>

                                                @if($task->remindtime || $task->deadline)
                                                    <div class="mt-2 space-y-1">
                                                        @if($task->remindtime)
                                                            <div class="text-xs text-gray-500">
                                                                <i class="far fa-bell mr-1"></i>
                                                                提醒: {{ date('m-d H:i', strtotime($task->remindtime)) }}
                                                            </div>
                                                        @endif
                                                        @if($task->deadline)
                                                            <div class="text-xs text-gray-500">
                                                                <i class="far fa-clock mr-1"></i>
                                                                截止: {{ date('m-d H:i', strtotime($task->deadline)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            @if($task->is_top == 1)
                                                <div class="ml-2">
                                                    <i class="fas fa-thumbtack text-yellow-500" title="置顶任务"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- 操作栏 -->
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        <div class="flex items-center space-x-4">
                                            <a href="/notes?source_type=3&source_id={{$task->id}}"
                                               class="text-sm text-gray-600 hover:text-blue-600">
                                                <i class="fas fa-sticky-note mr-1"></i>笔记
                                            </a>
                                            <button onclick="callOpenTaskUpdateModal('{{ addslashes(json_encode($task->toArray())) }}')"
                                                    class="text-sm text-gray-600 hover:text-green-600">
                                                <i class="fas fa-edit mr-1"></i>编辑
                                            </button>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            @if($task->status == 1)
                                                <button class="complete-task text-sm text-green-600 hover:text-green-800"
                                                        data-task-id="{{ $task->id }}">
                                                    <i class="fas fa-check mr-1"></i>完成
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 分页 -->
                    @if($tasks->hasPages())
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex flex-col md:flex-row items-center justify-between">
                                <div class="text-sm text-gray-500 mb-4 md:mb-0">
                                    显示 {{ $tasks->firstItem() }} - {{ $tasks->lastItem() }} 条，共 {{ $tasks->total() }} 条记录
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($tasks->onFirstPage())
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                    @else
                                        <a href="{{ $tasks->previousPageUrl() }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach(range(1, $tasks->lastPage()) as $page)
                                        @if($page == $tasks->currentPage())
                                            <button class="btn btn-sm btn-primary">{{ $page }}</button>
                                        @else
                                            <a href="{{ $tasks->url($page) }}" class="btn btn-sm btn-outline">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if($tasks->hasMorePages())
                                        <a href="{{ $tasks->nextPageUrl() }}" class="btn btn-sm btn-secondary">
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
                                <i class="fas fa-tasks text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">当前没有待办任务</h3>
                            <p class="text-gray-600 mb-6">创建您的第一个任务，开始高效工作吧！</p>
                        </div>
                        <button onclick="openTaskUpdateModal()" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            创建第一个任务
                        </button>
                        <a href="{{url('/index')}}" class="btn btn-outline ml-4">
                            <i class="fas fa-home mr-2"></i>
                            返回首页
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- 使用建议 -->
        @if(count($tasks) > 0)
            <div class="mt-8 card">
                <div class="p-6">
                    <div class="flex items-start space-x-3">
                        <div class="rounded-full bg-blue-100 p-2 mt-1">
                            <i class="fas fa-lightbulb text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">高效任务管理建议</h3>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>使用四象限法则</strong>：将任务按重要性和紧急性分类，优先处理重要紧急事项</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>设置合理提醒</strong>：为重要任务设置提醒时间，避免错过截止日期</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>每日回顾</strong>：每天结束时回顾任务列表，规划第二天的工作重点</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span><strong>及时标记完成</strong>：完成任务后及时标记，保持列表整洁并获得成就感</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('components.task-update-modal')

    <script>
        $(document).ready(function() {
            console.log('待办列表页面已加载');

            // 任务完成功能
            $(document).on('click', '.complete-task', function() {
                const taskId = $(this).data('task-id');
                if (confirm('确认将此任务标记为已完成吗？')) {
                    $.ajax({
                        url: '/task/' + taskId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 2,
                            _method: 'PUT'
                        },
                        success: function(response) {
                            if (response.code == 9999) {
                                location.reload();
                            } else {
                                alert('操作失败: ' + (response.msg || '未知错误'));
                            }
                        },
                        error: function() {
                            alert('请求失败，请稍后重试');
                        }
                    });
                }
            });

            // 搜索功能
            $('#taskSearch').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = $(this).val();
                    if (searchTerm) {
                        window.location.href = '{{ url("/tasks") }}?search=' + encodeURIComponent(searchTerm);
                    }
                }
            });

            // 导出功能
            $('#exportTasks').on('click', function() {
                const status = '{{ request("status") }}' || '1';
                window.location.href = '{{ url("/tasks/export") }}?status=' + status;
            });
        });

        // 安全地调用 openTaskUpdateModal 函数
        function callOpenTaskUpdateModal(taskDataStr) {
            try {
                var taskData = JSON.parse(taskDataStr);
                openTaskUpdateModal(taskData);
            } catch(e) {
                console.error('解析任务数据时出错:', e);
                console.log('原始数据:', taskDataStr);
                alert('数据解析失败，请重试');
            }
        }

        // 打开新建任务模态框
        function openTaskUpdateModal(taskData = null) {
            if (taskData) {
                // 编辑现有任务
                $('#taskUpdateModal').modal('show');
                // 这里需要填充模态框表单数据的代码
            } else {
                // 新建任务
                $('#taskUpdateModal').modal('show');
                // 这里需要清空模态框表单数据的代码
            }
        }
    </script>
@endsection