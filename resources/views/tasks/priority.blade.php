@extends('layouts.app')

@section('title', '待办四象限 - 蒙太奇')
@section('description', '使用艾森豪威尔矩阵管理待办事项，高效区分任务的重要性和紧急性')

@section('content')
    <style>
        .quadrant-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        @media (max-width: 768px) {
            .quadrant-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .task-item {
            background: white;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            cursor: grab;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--gray-200);
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }

        .task-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: var(--gray-300);
        }

        .task-item.dragging {
            opacity: 0.5;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .task-item.completed {
            opacity: 0.6;
            background: var(--gray-50);
            text-decoration: line-through;
            color: var(--gray-500);
        }

        .task-content {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 12px;
        }

        .task-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .task-item:hover .task-actions {
            opacity: 1;
        }

        .task-action-btn {
            padding: 4px;
            border-radius: 4px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--gray-400);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }

        .task-action-btn:hover {
            background: var(--gray-100);
        }

        .task-action-btn.complete:hover {
            color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
        }

        .task-action-btn.edit:hover {
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .task-action-btn.delete:hover {
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.1);
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--gray-400);
            background: var(--gray-50);
            border-radius: 8px;
            border: 1px dashed var(--gray-300);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .drag-hint {
            text-align: center;
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 16px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .drag-hint i {
            margin-right: 8px;
            color: var(--primary-color);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和操作栏 -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">待办四象限</h1>
                <p class="text-gray-600">使用艾森豪威尔矩阵管理任务，区分重要性和紧急性</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <a href="{{'/taskpriority?mode=1'}}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                          {{ (request('mode') == 1 || !request('mode')) ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                        <i class="fas fa-briefcase mr-2"></i>工作模式
                    </a>
                    <a href="{{'/taskpriority?mode=2'}}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                          {{ request('mode') == 2 ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                        <i class="fas fa-home mr-2"></i>生活模式
                    </a>
                </div>
                <a href="{{'/index'}}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回首页
                </a>
                <a href="{{'/tasks'}}" class="btn btn-secondary">
                    <i class="fas fa-list mr-2"></i>
                    列表视图
                </a>
            </div>
        </div>

        <!-- 四象限矩阵说明 -->
        <div class="card mb-8">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center mx-auto">
                                <i class="fas fa-fire text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">重要紧急</h3>
                        <p class="text-sm text-gray-600">立即处理，减少危机</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mx-auto">
                                <i class="fas fa-star text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">重要不紧急</h3>
                        <p class="text-sm text-gray-600">制定计划，重点投入</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto">
                                <i class="fas fa-bolt text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">紧急不重要</h3>
                        <p class="text-sm text-gray-600">尽量委托，快速处理</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg flex items-center justify-center mx-auto">
                                <i class="fas fa-ellipsis-h text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">不重要不紧急</h3>
                        <p class="text-sm text-gray-600">最后处理，或直接删除</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 四象限网格 -->
        <div class="quadrant-grid">
            @php
                $quadrants = [
                    4 => [
                        'title' => '重要紧急事项',
                        'icon' => 'fa-fire',
                        'color' => 'red',
                        'description' => '立即处理，优先解决'
                    ],
                    3 => [
                        'title' => '重要不紧急事项',
                        'icon' => 'fa-star',
                        'color' => 'orange',
                        'description' => '制定计划，安排时间'
                    ],
                    2 => [
                        'title' => '不重要紧急事项',
                        'icon' => 'fa-bolt',
                        'color' => 'blue',
                        'description' => '快速处理，考虑委托'
                    ],
                    1 => [
                        'title' => '不重要不紧急事项',
                        'icon' => 'fa-ellipsis-h',
                        'color' => 'gray',
                        'description' => '最后处理，优化取舍'
                    ]
                ];
            @endphp

            @foreach($quadrants as $priority => $info)
                <div class="card border-l-4 @if($info['color'] == 'red') border-red-500 @elseif($info['color'] == 'orange') border-orange-500 @elseif($info['color'] == 'blue') border-blue-500 @else border-gray-400 @endif">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-3 mb-1">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    @if($info['color'] == 'red') bg-red-100 text-red-600
                                    @elseif($info['color'] == 'orange') bg-orange-100 text-orange-600
                                    @elseif($info['color'] == 'blue') bg-blue-100 text-blue-600
                                    @else bg-gray-100 text-gray-600 @endif">
                                        <i class="fas {{ $info['icon'] }}"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $info['title'] }}</h3>
                                </div>
                                <p class="text-sm text-gray-500 ml-13">{{ $info['description'] }}</p>
                            </div>
                            <div class="text-sm font-medium px-3 py-1 rounded-full
                            @if($info['color'] == 'red') bg-red-100 text-red-700
                            @elseif($info['color'] == 'orange') bg-orange-100 text-orange-700
                            @elseif($info['color'] == 'blue') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700 @endif">
                                {{ count($tasks[$priority] ?? []) }} 项
                            </div>
                        </div>

                        <div class="task-list" data-quadrant="{{ $priority }}">
                            @if(empty($tasks[$priority]))
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p class="text-sm">暂无任务</p>
                                    <p class="text-xs mt-1">将任务拖拽到此区域</p>
                                </div>
                            @else
                                @foreach ($tasks[$priority] as $task)
                                    <div class="task-item" draggable="true"
                                         data-task-id="{{ $task->id }}"
                                         data-task-name="{{ $task->name }}"
                                         @if($task->status == 2) data-completed="true" @endif>

                                        <div class="task-content">
                                            @if($task->status == 2)
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            @endif
                                            @if(!empty($task->parent_task_id))
                                                <i class="fas fa-level-up-alt rotate-90 text-gray-400 mr-2"></i>
                                            @endif
                                            <span class="@if($task->status == 2) line-through text-gray-500 @else text-gray-800 @endif">
                                            {{ $task->name }}
                                        </span>
                                        </div>

                                        <div class="task-actions">
                                            <button class="task-action-btn complete"
                                                    title="标记完成"
                                                    onclick="toggleComplete({{ $task->id }}, this)">
                                                @if($task->status == 2)
                                                    <i class="fas fa-undo"></i>
                                                @else
                                                    <i class="fas fa-check"></i>
                                                @endif
                                            </button>

                                            <button class="task-action-btn edit"
                                                    title="编辑任务"
                                                    onclick="editTask({{ $task->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="task-action-btn delete"
                                                    title="删除任务"
                                                    onclick="deleteTask({{ $task->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 拖拽提示 -->
        <div class="drag-hint">
            <i class="fas fa-hand-pointer"></i>
            <span>拖拽任务可在不同象限间移动，自动更新任务优先级</span>
        </div>

        <!-- 使用指南 -->
        <div class="card mt-8">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="rounded-full bg-blue-100 p-2 mt-1">
                        <i class="fas fa-graduation-cap text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">四象限法则使用指南</h3>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>重要紧急（第1象限）</strong>：立即处理，每天尽量控制在2-3个以内，减少危机事件</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>重要不紧急（第2象限）</strong>：计划处理，投入最多时间，这是高效能的关键</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>紧急不重要（第3象限）</strong>：快速处理或委托他人，避免占用过多时间</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>不重要不紧急（第4象限）</strong>：最后处理，适当舍弃，避免时间浪费</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.task-update-modal')

    <script>
        $(document).ready(function() {
            console.log('四象限页面已加载');

            // 初始化拖拽功能
            initDragAndDrop();

            // 监听模式切换按钮
            $('.mode-switch a').on('click', function(e) {
                e.preventDefault();
                const mode = $(this).attr('href').split('mode=')[1];
                window.location.href = '{{ url("/taskpriority") }}?mode=' + mode;
            });
        });

        // ===================== 任务操作函数 =====================
        function toggleComplete(taskId, button) {
            $.post('/tasks/' + taskId + '/toggleComplete', {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.code === 9999) {
                    // 更新UI而不刷新页面
                    const taskItem = $(`[data-task-id="${taskId}"]`);
                    const isCompleted = taskItem.data('completed') === true;

                    taskItem.data('completed', !isCompleted);

                    if (isCompleted) {
                        taskItem.removeClass('completed');
                        taskItem.find('.task-content span').removeClass('line-through text-gray-500');
                        taskItem.find('.task-content i.fa-check-circle').remove();
                        taskItem.find('.complete i').removeClass('fa-undo').addClass('fa-check');
                    } else {
                        taskItem.addClass('completed');
                        taskItem.find('.task-content').prepend('<i class="fas fa-check-circle text-green-500 mr-2"></i>');
                        taskItem.find('.task-content span').addClass('line-through text-gray-500');
                        taskItem.find('.complete i').removeClass('fa-check').addClass('fa-undo');
                    }
                } else {
                    alert('操作失败：' + (response.msg || '请稍后重试'));
                }
            }).fail(function() {
                alert('请求失败，请检查网络连接');
            });
        }

        function editTask(taskId) {
            // 从服务器获取任务数据 - 使用现有的API
            $.get('/tasks/' + taskId, function(response) {
                if(response.code == 9999) {
                    var task = response.result;
                    openTaskUpdateModal(task);
                } else {
                    alert('获取任务数据失败：' + (response.msg || '未知错误'));
                }
            }).fail(function() {
                alert('获取任务数据失败');
            });
        }

        function deleteTask(taskId) {
            if (confirm('确认要删除此任务吗？删除后无法恢复。')) {
                $.ajax({
                    url: '/tasks/' + taskId,
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.code === 9999) {
                            // 动画删除效果
                            const taskItem = $(`[data-task-id="${taskId}"]`);
                            taskItem.fadeOut(300, function() {
                                $(this).remove();

                                // 如果没有任务了，显示空状态
                                const quadrant = taskItem.closest('.task-list').data('quadrant');
                                const taskList = $(`.task-list[data-quadrant="${quadrant}"]`);
                                if (taskList.find('.task-item').length === 0) {
                                    taskList.html(`
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p class="text-sm">暂无任务</p>
                                        <p class="text-xs mt-1">将任务拖拽到此区域</p>
                                    </div>
                                `);
                                }
                            });
                        } else {
                            alert('删除失败：' + (response.msg || '请稍后重试'));
                        }
                    },
                    error: function() {
                        alert('请求失败，请检查网络连接');
                    }
                });
            }
        }

        // ===================== 拖拽逻辑 =====================
        function initDragAndDrop() {
            let draggedItem = null;

            // 初始化所有可拖拽任务
            document.querySelectorAll('.task-item').forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragend', handleDragEnd);
            });

            // 初始化所有放置区域
            document.querySelectorAll('.task-list').forEach(list => {
                list.addEventListener('dragover', handleDragOver);
                list.addEventListener('drop', handleDrop);
            });

            function handleDragStart(e) {
                draggedItem = this;
                setTimeout(() => {
                    this.classList.add('dragging');
                }, 0);
            }

            function handleDragEnd() {
                this.classList.remove('dragging');
                draggedItem = null;
            }

            function handleDragOver(e) {
                e.preventDefault();
                const afterElement = getDragAfterElement(this, e.clientY);
                const draggable = document.querySelector('.task-item.dragging');

                if (draggable) {
                    if (afterElement == null) {
                        this.appendChild(draggable);
                    } else {
                        this.insertBefore(draggable, afterElement);
                    }
                }
            }

            function handleDrop(e) {
                e.preventDefault();

                if (draggedItem) {
                    const newQuadrant = this.dataset.quadrant;
                    const taskId = draggedItem.dataset.taskId;
                    const oldQuadrant = draggedItem.closest('.task-list').dataset.quadrant;

                    // 如果象限没变，不发送请求
                    if (newQuadrant === oldQuadrant) {
                        return;
                    }

                    // 发送更新请求
                    $.post('/tasks/' + taskId + '/updateQuadrant', {
                        _token: '{{ csrf_token() }}',
                        quadrant: newQuadrant
                    }, function(response) {
                        if (response.code !== 9999) {
                            alert('更新失败，请刷新页面重试');
                            // 恢复原来的位置
                            const originalList = document.querySelector(`.task-list[data-quadrant="${oldQuadrant}"]`);
                            originalList.appendChild(draggedItem);
                        }
                    });
                }
            }

            function getDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.task-item:not(.dragging)')];

                return draggableElements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;

                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    } else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
            }
        }
    </script>
@endsection