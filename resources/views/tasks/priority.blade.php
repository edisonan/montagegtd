@extends('layouts.app')
@section('title', '蒙太奇 - 待办四象限')
@section('description', '利用艾森豪威尔矩阵管理待办事项，高效完成工作与生活任务')

@section('content')
    <style>
        /* 四象限卡片样式 */
        .quadrant-card {
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .quadrant-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }
        .quadrant-header {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .task-item {
            background: #fff;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .task-item:hover {
            background: rgba(66,156,78,0.05);
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
        .mode-switch a:hover {
            color: #2f7d32;
        }
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
                    <!-- 重要紧急事项 -->
                    <div class="col-md-6">
                        <div class="quadrant-card quadrant-4">
                            <div class="quadrant-header">重要紧急事项</div>
                            @if(empty($tasks[4]))
                                <p>暂无待办</p>
                            @else
                                @foreach ($tasks[4] as $task)
                                    <div class="task-item {{ $task->completed ? 'completed' : '' }}">
                                    <span>
                                        @if(!empty($task->parent_task_id))
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif
                                        {{$task->name}}
                                    </span>
                                        <div class="task-actions">
                                            <i class="bi bi-check-circle" title="标记完成" onclick="toggleComplete({{$task->id}})"></i>
                                            <i class="bi bi-pencil-square" title="编辑" onclick="editTask({{$task->id}})"></i>
                                            <i class="bi bi-trash" title="删除" onclick="deleteTask({{$task->id}})"></i>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- 重要不紧急事项 -->
                    <div class="col-md-6">
                        <div class="quadrant-card quadrant-3">
                            <div class="quadrant-header">重要不紧急事项</div>
                            @if(empty($tasks[3]))
                                <p>暂无待办</p>
                            @else
                                @foreach ($tasks[3] as $task)
                                    <div class="task-item {{ $task->completed ? 'completed' : '' }}">
                                    <span>
                                        @if(!empty($task->parent_task_id))
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif
                                        {{$task->name}}
                                    </span>
                                        <div class="task-actions">
                                            <i class="bi bi-check-circle" title="标记完成" onclick="toggleComplete({{$task->id}})"></i>
                                            <i class="bi bi-pencil-square" title="编辑" onclick="editTask({{$task->id}})"></i>
                                            <i class="bi bi-trash" title="删除" onclick="deleteTask({{$task->id}})"></i>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- 不重要紧急事项 -->
                    <div class="col-md-6">
                        <div class="quadrant-card quadrant-2">
                            <div class="quadrant-header">不重要紧急事项</div>
                            @if(empty($tasks[2]))
                                <p>暂无待办</p>
                            @else
                                @foreach ($tasks[2] as $task)
                                    <div class="task-item {{ $task->completed ? 'completed' : '' }}">
                                    <span>
                                        @if(!empty($task->parent_task_id))
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif
                                        {{$task->name}}
                                    </span>
                                        <div class="task-actions">
                                            <i class="bi bi-check-circle" title="标记完成" onclick="toggleComplete({{$task->id}})"></i>
                                            <i class="bi bi-pencil-square" title="编辑" onclick="editTask({{$task->id}})"></i>
                                            <i class="bi bi-trash" title="删除" onclick="deleteTask({{$task->id}})"></i>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- 不重要不紧急事项 -->
                    <div class="col-md-6">
                        <div class="quadrant-card quadrant-1">
                            <div class="quadrant-header">不重要不紧急事项</div>
                            @if(empty($tasks[1]))
                                <p>暂无待办</p>
                            @else
                                @foreach ($tasks[1] as $task)
                                    <div class="task-item {{ $task->completed ? 'completed' : '' }}">
                                    <span>
                                        @if(!empty($task->parent_task_id))
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif
                                        {{$task->name}}
                                    </span>
                                        <div class="task-actions">
                                            <i class="bi bi-check-circle" title="标记完成" onclick="toggleComplete({{$task->id}})"></i>
                                            <i class="bi bi-pencil-square" title="编辑" onclick="editTask({{$task->id}})"></i>
                                            <i class="bi bi-trash" title="删除" onclick="deleteTask({{$task->id}})"></i>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleComplete(taskId) {
            // Ajax 调用，切换完成状态
            $.post('/tasks/' + taskId + '/toggleComplete', {_token:'{{ csrf_token() }}'}, function(res){
                if(res.code === 9999) location.reload();
                else alert('操作失败');
            });
        }

        function editTask(taskId) {
            window.location.href = '/tasks/' + taskId + '/edit';
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
    </script>
@endsection
