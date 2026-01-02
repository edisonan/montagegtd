@extends('layouts.app')

<style>
    .rowone {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
    }
    
    .task-date {
        display: inline-block;
        background-color: #e9ecef;
        color: #495057;
        padding: 0.1rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.8em;
        margin-right: 0.3rem;
        font-weight: 500;
    }
    
    .task-time {
        color: #6c757d;
        font-size: 0.9em;
        margin-right: 0.5rem;
    }
    
    .task-date-same {
        display: inline-block;
        width: 30px; /* 为日期留出空间，保持对齐 */
        margin-right: 0.3rem;
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
                                        $shouldShowDate = ($currentDate != $lastDate);
                                        if ($shouldShowDate) {
                                            $lastDate = $currentDate;
                                        }
                                        ?>
                                        @if($shouldShowDate)
                                        <span class="task-date" title="{{ date('Y-m-d', strtotime($task->updated_at)) }}">
{{ $currentDate }}
</span>
                                        @endif
                                        <small class="task-time">{{ date('H:i', strtotime($task->updated_at)) }} </small>

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
                                    <a href="/notes?source_type=3&source_id={{$task->id}}">
                                        <i class="fa fa-sticky-note-o" style="font-size: 1.5rem;"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="callOpenTaskUpdateModal('{{ addslashes(json_encode($task->toArray())) }}')">
                                        <i class="fa fa-edit" style="font-size: 1.5rem;"></i>
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
        $(document).ready(function() {
            // 确保在 DOM 加载后可以使用 jQuery
            console.log('Task index 页面已加载');
        });
        
        // 用于安全地调用 openTaskUpdateModal 函数
        function callOpenTaskUpdateModal(taskDataStr) {
            try {
                var taskData = JSON.parse(taskDataStr);
                openTaskUpdateModal(taskData);
            } catch(e) {
                console.error('解析任务数据时出错:', e);
                console.log('原始数据:', taskDataStr);
            }
        }
    </script>
@endsection