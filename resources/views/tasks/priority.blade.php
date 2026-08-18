@extends('layouts.app')

@section('title', '待办四象限 - 蒙太奇')
@section('description', '使用艾森豪威尔矩阵管理待办事项，高效区分任务的重要性和紧急性')

@section('content')
    <style>
        .quadrant-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px; margin-bottom: 32px; align-items: stretch; }
        @media (max-width: 768px) { .quadrant-grid { grid-template-columns: 1fr; gap: 20px; } }
        .quadrant-card { min-width: 0; height: 100%; }
        .quadrant-card-body { min-height: 280px; display: flex; flex-direction: column; }
        .task-list { flex: 1; min-height: 180px; }
        .priority-task-item { background: white; border-radius: 8px; padding: 12px 16px; margin-bottom: 8px; cursor: grab; display: flex; justify-content: space-between; align-items: center; gap: 12px; border: 1px solid var(--gray-200); box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: all 0.2s ease; min-width: 0; }
        .priority-task-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: var(--gray-300); }
        .priority-task-item.dragging { opacity: 0.5; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); }
        .priority-task-item.completed { opacity: 0.6; background: var(--gray-50); text-decoration: line-through; color: var(--gray-500); }
        .priority-task-content { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .priority-task-actions { display: flex; align-items: center; gap: 6px; opacity: 0; transition: opacity 0.2s ease; flex-shrink: 0; }
        .priority-task-item:hover .priority-task-actions { opacity: 1; }
        @media (max-width: 768px) { .priority-task-actions { opacity: 1; } }
        .task-action-btn { padding: 4px; border-radius: 4px; background: transparent; border: none; cursor: pointer; color: var(--gray-400); transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; }
        .task-action-btn:hover { background: var(--gray-100); }
        .task-action-btn.complete:hover { color: var(--success-color); background: rgba(16, 185, 129, 0.1); }
        .task-action-btn.edit:hover { color: var(--primary-color); background: rgba(59, 130, 246, 0.1); }
        .task-action-btn.delete:hover { color: var(--danger-color); background: rgba(239, 68, 68, 0.1); }
        .empty-state { padding: 40px 20px; text-align: center; color: var(--gray-400); background: var(--gray-50); border-radius: 8px; border: 1px dashed var(--gray-300); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
        .drag-hint { text-align: center; color: var(--gray-500); font-size: 14px; margin-top: 16px; padding: 12px; background: var(--gray-50); border-radius: 8px; border: 1px solid var(--gray-200); }
        .drag-hint i { margin-right: 8px; color: var(--primary-color); }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">待办四象限</h1>
                <p class="text-gray-600">使用艾森豪威尔矩阵管理任务，区分重要性和紧急性</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center bg-gray-100 rounded-lg p-1" id="modeSwitchWrap">
                    <button type="button" class="px-4 py-2 rounded-md text-sm font-medium transition-colors" data-mode="1"><i class="fas fa-briefcase mr-2"></i>工作模式</button>
                    <button type="button" class="px-4 py-2 rounded-md text-sm font-medium transition-colors" data-mode="2"><i class="fas fa-home mr-2"></i>生活模式</button>
                </div>
                <a href="{{'/index'}}" class="btn btn-outline"><i class="fas fa-arrow-left mr-2"></i>返回首页</a>
                <a href="{{'/tasks'}}" class="btn btn-secondary"><i class="fas fa-list mr-2"></i>列表视图</a>
            </div>
        </div>

        <div class="card mb-8">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center"><div class="mb-3"><div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center mx-auto"><i class="fas fa-fire text-white text-xl"></i></div></div><h3 class="font-semibold text-gray-900 mb-2">重要紧急</h3><p class="text-sm text-gray-600">立即处理，减少危机</p></div>
                    <div class="text-center"><div class="mb-3"><div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mx-auto"><i class="fas fa-star text-white text-xl"></i></div></div><h3 class="font-semibold text-gray-900 mb-2">重要不紧急</h3><p class="text-sm text-gray-600">制定计划，重点投入</p></div>
                    <div class="text-center"><div class="mb-3"><div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto"><i class="fas fa-bolt text-white text-xl"></i></div></div><h3 class="font-semibold text-gray-900 mb-2">紧急不重要</h3><p class="text-sm text-gray-600">尽量委托，快速处理</p></div>
                    <div class="text-center"><div class="mb-3"><div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg flex items-center justify-center mx-auto"><i class="fas fa-ellipsis-h text-white text-xl"></i></div></div><h3 class="font-semibold text-gray-900 mb-2">不重要不紧急</h3><p class="text-sm text-gray-600">最后处理，或直接删除</p></div>
                </div>
            </div>
        </div>

        <div class="quadrant-grid">
            <div class="card quadrant-card border-l-4 border-red-500"><div class="p-6 quadrant-card-body"><div class="flex items-center justify-between mb-4 gap-3"><h3 class="text-lg font-semibold text-gray-900">重要紧急事项</h3><div class="text-sm font-medium px-3 py-1 rounded-full bg-red-100 text-red-700 flex-shrink-0" id="count-q4">0 项</div></div><div class="task-list" data-quadrant="4" id="list-q4"></div></div></div>
            <div class="card quadrant-card border-l-4 border-orange-500"><div class="p-6 quadrant-card-body"><div class="flex items-center justify-between mb-4 gap-3"><h3 class="text-lg font-semibold text-gray-900">重要不紧急事项</h3><div class="text-sm font-medium px-3 py-1 rounded-full bg-orange-100 text-orange-700 flex-shrink-0" id="count-q3">0 项</div></div><div class="task-list" data-quadrant="3" id="list-q3"></div></div></div>
            <div class="card quadrant-card border-l-4 border-blue-500"><div class="p-6 quadrant-card-body"><div class="flex items-center justify-between mb-4 gap-3"><h3 class="text-lg font-semibold text-gray-900">不重要紧急事项</h3><div class="text-sm font-medium px-3 py-1 rounded-full bg-blue-100 text-blue-700 flex-shrink-0" id="count-q2">0 项</div></div><div class="task-list" data-quadrant="2" id="list-q2"></div></div></div>
            <div class="card quadrant-card border-l-4 border-gray-400"><div class="p-6 quadrant-card-body"><div class="flex items-center justify-between mb-4 gap-3"><h3 class="text-lg font-semibold text-gray-900">不重要不紧急事项</h3><div class="text-sm font-medium px-3 py-1 rounded-full bg-gray-100 text-gray-700 flex-shrink-0" id="count-q1">0 项</div></div><div class="task-list" data-quadrant="1" id="list-q1"></div></div></div>
        </div>

        <div class="drag-hint"><i class="fas fa-hand-pointer"></i><span>拖拽任务可在不同象限间移动，自动更新任务优先级</span></div>
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

        var priorityPageState = {
            mode: '1',
            tasksByPriority: {1: [], 2: [], 3: [], 4: []}
        };

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function getQueryParam(name) {
            var params = new URLSearchParams(window.location.search || '');
            return params.get(name);
        }

        function updateModeUrl() {
            var params = new URLSearchParams(window.location.search || '');
            params.set('mode', priorityPageState.mode);
            window.history.replaceState({}, '', window.location.pathname + '?' + params.toString());
        }

        function setModeActive() {
            $('#modeSwitchWrap button').each(function() {
                var active = String($(this).data('mode')) === String(priorityPageState.mode);
                $(this).toggleClass('bg-white text-gray-900 shadow-sm', active);
                $(this).toggleClass('text-gray-600 hover:text-gray-900 hover:bg-gray-200', !active);
            });
        }

        function renderEmptyState() {
            return '<div class="empty-state"><i class="fas fa-inbox"></i><p class="text-sm">暂无任务</p><p class="text-xs mt-1">将任务拖拽到此区域</p></div>';
        }

        function renderTaskItem(task) {
            var completed = Number(task.status) === 2;
            var taskName = escapeHtml(task.name || '');
            return '' +
                '<div class="priority-task-item ' + (completed ? 'completed' : '') + '" draggable="true" data-task-id="' + task.id + '">' +
                '<div class="priority-task-content">' +
                (completed ? '<i class="fas fa-check-circle text-green-500 mr-2"></i>' : '') +
                '<span class="' + (completed ? 'line-through text-gray-500' : 'text-gray-800') + '">' + taskName + '</span>' +
                '</div>' +
                '<div class="priority-task-actions">' +
                '<button class="task-action-btn complete" title="标记完成" onclick="toggleComplete(' + task.id + ', ' + (completed ? 1 : 2) + ')"><i class="fas ' + (completed ? 'fa-undo' : 'fa-check') + '"></i></button>' +
                '<button class="task-action-btn edit" title="编辑任务" onclick="editTask(' + task.id + ')"><i class="fas fa-edit"></i></button>' +
                (!completed ? '<button class="task-action-btn" title="折叠任务" onclick="foldTask(' + task.id + ')"><i class="fas fa-folder"></i></button>' : '') +
                '<button class="task-action-btn delete" title="删除任务" onclick="deleteTask(' + task.id + ')"><i class="fas fa-trash"></i></button>' +
                '</div>' +
                '</div>';
        }

        function renderPriorityLists() {
            [1, 2, 3, 4].forEach(function(priority) {
                var tasks = priorityPageState.tasksByPriority[priority] || [];
                var list = $('#list-q' + priority);
                if (!tasks.length) {
                    list.html(renderEmptyState());
                } else {
                    list.html(tasks.map(renderTaskItem).join(''));
                }
                $('#count-q' + priority).text(tasks.length + ' 项');
            });

            initDragAndDrop();
        }

        function loadPriorityTasks() {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }

            apiRequest('GET', '/tasks/priority?status=1&mode=' + encodeURIComponent(priorityPageState.mode), {}).then(function(response) {
                if (response.code !== 9999) {
                    alert(response.msg || '加载失败');
                    return;
                }
                var tasks = (response.result && response.result.tasks) ? response.result.tasks : {};
                priorityPageState.tasksByPriority = {
                    1: tasks[1] || [],
                    2: tasks[2] || [],
                    3: tasks[3] || [],
                    4: tasks[4] || []
                };
                renderPriorityLists();
                setModeActive();
                updateModeUrl();
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        function toggleComplete(taskId, targetStatus) {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('PUT', '/tasks/' + taskId, {status: targetStatus}).then(function(response) {
                if (response.code === 9999) {
                    loadPriorityTasks();
                } else {
                    alert('操作失败：' + (response.msg || '请稍后重试'));
                }
            }).catch(function() {
                alert('请求失败，请检查网络连接');
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

        function deleteTask(taskId) {
            if (!confirm('确认要删除此任务吗？删除后无法恢复。')) {
                return;
            }
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('DELETE', '/tasks/' + taskId, {}).then(function(response) {
                if (response.code === 9999) {
                    loadPriorityTasks();
                } else {
                    alert('删除失败：' + (response.msg || '请稍后重试'));
                }
            }).catch(function() {
                alert('请求失败，请检查网络连接');
            });
        }

        function foldTask(taskId) {
            if (!confirm('确认要折叠此任务吗？')) {
                return;
            }
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('DELETE', '/tasks/' + taskId, {type: 'fold'}).then(function(response) {
                if (response.code === 9999) {
                    loadPriorityTasks();
                } else {
                    alert('折叠失败：' + (response.msg || '请稍后重试'));
                }
            }).catch(function() {
                alert('请求失败，请检查网络连接');
            });
        }

        function initDragAndDrop() {
            var draggedItem = null;
            var originQuadrant = null;

            document.querySelectorAll('.priority-task-item').forEach(function(item) {
                item.addEventListener('dragstart', function(e) {
                    draggedItem = this;
                    originQuadrant = this.closest('.task-list') ? this.closest('.task-list').dataset.quadrant : null;
                    // Firefox 要求 dragstart 中必须写入 dataTransfer，否则拖拽会被取消
                    if (e.dataTransfer) {
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', this.dataset.taskId || '');
                    }
                    setTimeout(function() { item.classList.add('dragging'); }, 0);
                });
                item.addEventListener('dragend', function() {
                    item.classList.remove('dragging');
                    draggedItem = null;
                });
            });

            document.querySelectorAll('.task-list').forEach(function(list) {
                list.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    var draggable = document.querySelector('.priority-task-item.dragging');
                    if (!draggable) {
                        return;
                    }
                    var afterElement = getDragAfterElement(this, e.clientY);
                    if (afterElement == null) {
                        this.appendChild(draggable);
                    } else {
                        this.insertBefore(draggable, afterElement);
                    }
                });

                list.addEventListener('drop', function(e) {
                    e.preventDefault();
                    if (!draggedItem) {
                        return;
                    }
                    var targetQuadrant = this.dataset.quadrant;
                    var taskId = draggedItem.dataset.taskId;
                    if (!originQuadrant || originQuadrant === targetQuadrant) {
                        return;
                    }

                    var apiRequest = getApiRequest();
                    if (!apiRequest) {
                        alert('API客户端未初始化');
                        loadPriorityTasks();
                        return;
                    }

                    apiRequest('PUT', '/tasks/' + taskId, {priority: targetQuadrant}).then(function(response) {
                        if (response.code !== 9999) {
                            alert('更新失败，请刷新页面重试');
                        }
                        loadPriorityTasks();
                    }).catch(function() {
                        alert('更新失败，请刷新页面重试');
                        loadPriorityTasks();
                    });
                });
            });
        }

        function getDragAfterElement(container, y) {
            var draggableElements = [].slice.call(container.querySelectorAll('.priority-task-item:not(.dragging)'));
            return draggableElements.reduce(function(closest, child) {
                var box = child.getBoundingClientRect();
                var offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        $(document).ready(function() {
            priorityPageState.mode = getQueryParam('mode') || '1';

            $('#modeSwitchWrap').on('click', 'button', function() {
                priorityPageState.mode = String($(this).data('mode'));
                loadPriorityTasks();
            });

            withApiReady(function() {
                loadPriorityTasks();
            });
        });
    </script>
@endsection
