@extends('layouts.app')
@section('title', '蒙太奇 - 待办四象限')
@section('description', '利用艾森豪威尔矩阵管理待办事项，高效完成工作与生活任务')

@section('content')
    <style>
        .quadrant-card {
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 200px;
        }
        .quadrant-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }
        .quadrant-header {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .task-item {
            background: #fff;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            cursor: grab;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }
        .task-item.dragging {
            opacity: 0.5;
        }
        .task-item.completed {
            color: #aaa;
            text-decoration: line-through;
            background: #f5f5f5;
        }
        .task-actions i {
            margin-left: 10px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s;
        }
        .task-actions i:hover {
            color: #429c4e;
        }
        .quadrant-1 { background: #f8f9fa; }
        .quadrant-2 { background: #fff3cd; }
        .quadrant-3 { background: #d1ecf1; }
        .quadrant-4 { background: #d4edda; }
        @media (max-width: 768px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        .mode-switch a {
            margin-left: 10px;
            font-size: 0.9rem;
            text-decoration: none;
            color: #429c4e;
            transition: color 0.2s;
        }
        .mode-switch a:hover { color: #2f7d32; }
    </style>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>待办四象限</span>
                <div class="mode-switch">
                    <a href="{{'/taskpriority?mode=1'}}">工作</a>
                    <a href="{{'/taskpriority?mode=2'}}">生活</a>
                    <a href="{{'/index'}}">返回</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $quadrants = [
                            4 => ['title'=>'重要紧急事项','class'=>'quadrant-4'],
                            3 => ['title'=>'重要不紧急事项','class'=>'quadrant-3'],
                            2 => ['title'=>'不重要紧急事项','class'=>'quadrant-2'],
                            1 => ['title'=>'不重要不紧急事项','class'=>'quadrant-1']
                        ];
                    @endphp

                    @foreach($quadrants as $q => $info)
                        <div class="col-md-6">
                            <div class="quadrant-card {{ $info['class'] }}" data-quadrant="{{ $q }}">
                                <div class="quadrant-header">{{ $info['title'] }}</div>
                                <div class="task-list" data-quadrant="{{ $q }}">
                                    @if(empty($tasks[$q]))
                                        <p>暂无待办</p>
                                    @else
                                        @foreach ($tasks[$q] as $task)
                                            <div class="task-item" draggable="true" data-task-id="{{$task->id}}">
                                        <span>
                                            @if(!empty($task->parent_task_id))
                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                            @endif
                                            {{$task->name}}
                                        </span>
                                                <div class="task-actions">
                                                    <i class="bi bi-check-circle" title="标记完成" onclick="toggleComplete({{$task->id}})"></i>
                                                    <i class="bi bi-pencil-square" title="编辑" onclick="loadTaskDataAndOpenModalForPriority({{ json_encode($task->toArray()) }})"></i>
                                                    <i class="bi bi-trash" title="删除" onclick="deleteTask({{$task->id}})"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('components.task-update-modal')
    
    <script>
        function toggleComplete(taskId) {
            $.post('/tasks/' + taskId + '/toggleComplete', {_token:'{{ csrf_token() }}'}, function(res){
                if(res.code === 9999) location.reload();
                else alert('操作失败');
            });
        }
        function loadTaskDataAndOpenModalForPriority(taskData) {
            // 填充表单数据
            $('#task_name_input').val(taskData.name);
            $('input[name="priority"][value="' + (taskData.priority || 1) + '"]').prop('checked', true);
            $('#remindtime_input').val(taskData.remindtime || '');
            $('#deadline_input').val(taskData.deadline || '');
            $('input[name="status"][value="' + (taskData.status || 1) + '"]').prop('checked', true);
            $('input[name="is_top"][value="' + (taskData.is_top || 0) + '"]').prop('checked', true);
            $('input[name="mode"][value="' + (taskData.mode || 1) + '"]').prop('checked', true);
            
            // 添加隐藏字段存储任务ID
            if ($('#taskUpdateForm input[name=id]').length === 0) {
                $('#taskUpdateForm').append('<input type="hidden" name="id" value="">');
            }
            $('#taskUpdateForm input[name=id]').val(taskData.id);
            
            // 设置表单提交URL
            $('#taskUpdateForm').attr('action', '/task/' + taskData.id);
            
            // 显示模态框
            $('#taskUpdateModal').modal('show');
        }
        function deleteTask(taskId) {
            if(confirm('确认删除该任务吗？')) {
                $.ajax({
                    url: '/tasks/' + taskId,
                    type: 'DELETE',
                    data: {_token:'{{ csrf_token() }}'},
                    success: function(res) {
                        if(res.code === 9999) location.reload();
                        else alert('删除失败');
                    }
                });
            }
        }

        // ===================== 拖拽逻辑 =====================
        let draggedItem = null;

        document.querySelectorAll('.task-item').forEach(item => {
            item.addEventListener('dragstart', e => {
                draggedItem = item;
                setTimeout(() => item.classList.add('dragging'), 0);
            });
            item.addEventListener('dragend', e => {
                item.classList.remove('dragging');
                draggedItem = null;
            });
        });

        document.querySelectorAll('.task-list').forEach(list => {
            list.addEventListener('dragover', e => {
                e.preventDefault();
                const afterElement = getDragAfterElement(list, e.clientY);
                const draggable = document.querySelector('.dragging');
                if(afterElement == null) list.appendChild(draggable);
                else list.insertBefore(draggable, afterElement);
            });

            list.addEventListener('drop', e => {
                e.preventDefault();
                if(draggedItem) {
                    const newQuadrant = list.dataset.quadrant;
                    const taskId = draggedItem.dataset.taskId;

                    $.post('/tasks/' + taskId + '/updateQuadrant', {
                        _token:'{{ csrf_token() }}',
                        quadrant:newQuadrant
                    }, function(res){
                        if(res.code !== 9999) alert('更新失败');
                    });
                }
            });
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.task-item:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height/2;
                if(offset < 0 && offset > closest.offset) return {offset: offset, element: child};
                else return closest;
            }, {offset:Number.NEGATIVE_INFINITY}).element;
        }
    </script>
@endsection