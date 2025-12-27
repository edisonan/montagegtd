@extends('layouts.app')

<style>
    .rowone {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
    }
</style>

@section('content')
    <div class="container">
    @include('common.success')
    <!-- Current Tasks -->
        <div class="card">
            <div class="card-header">
                待办列表
                <div style="float:right">
                    <a href="{{'/tasks'}}?status=1&need_page=1">[进行中]</a>
                    <a href="{{'/tasks'}}?status=2&need_page=1">[已完成]</a>
                    <a href="{{'/tasks'}}?status=3&need_page=1">[已折叠]</a>
                    <a href="{{'/index'}}">[返回]</a>
                </div>
            </div>

            <div class="card-body">

                @if (count($tasks) > 0)
                    <table class="table table-hover task-table">
                        <thead>
                        <th>待办事项</th>
                        <th>操作</th>
                        </thead>
                        <tbody>
                        <?php $lastDate = '';?>
                        @foreach ($tasks as $task)
                            <tr
                                    @if($task->priority == 4)
                                    class="danger" title="重要紧急事项"
                                    @elseif($task->priority == 3)
                                    class="warning" title="重要不紧急事项"
                                    @elseif($task->priority == 2)
                                    class="info" title="不重要紧急事项"
                                    @else
                                    title="不重要不紧急事项"
                                    @endif
                            >
                                <td class="table-text" width="80%">
                                    <div class="rowone">
                                        <?php
                                        $currentDate = date('m-d', strtotime($task->updated_at));
                                        if ($currentDate != $lastDate) {
                                            $lastDate = $currentDate;
                                            $style = "";
                                        } else {
                                            $style = "color:rgb(0,0,0,0);";
                                        }
                                        ?>
                                        <span style="{{ $style}}">
{{ $currentDate }}
</span>
                                        <small>{{ date('H:i', strtotime($task->updated_at)) }} </small>

                                        @if($task->status == 1)
                                            [进行中]
                                        @elseif($task->status == 2)
                                            [已完成]
                                        @elseif($task->status == 3)
                                            [已折叠]
                                        @endif

                                        @if($task->mode == 1)
                                            #work#
                                        @else
                                            #life#
                                        @endif
                                        @if(isset($task->parentTask->name))
                                            {{ $task->parentTask->name}}--
                                        @endif
                                        {{ $task->name }}
                                    </div>
                                </td>

                                <td width="20%" align="right">
                                    <a href="/notes?add_content=%23记录待办%23{{ urlencode($task->name)}}&task_id={{$task->id}}">
                                        <i class="bi-textarea-t" style="font-size: 1.5rem;"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="loadTaskDataAndOpenModal({{ json_encode($task->toArray()) }})">
                                        <i class="bi-pencil-square" style="font-size: 1.5rem;"></i>
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $tasks->links() !!}
                @else
                    <div>
                        <img src="/img/new/love.png" width="200px">
                        暂时还没有完成哦，快去<a href="{{url('/index')}}" style="color:green">开始第一个任务</a>吧！
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>
    
    @include('components.task-update-modal')
    
    <script>
        // 加载任务数据并打开模态框的函数
        function loadTaskDataAndOpenModal(taskData) {
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
    </script>
@endsection