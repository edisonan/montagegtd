@extends('layouts.app')
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

@section('content')
    <div class="container">

        <div class="col-md-offset-2 col-md-8">
            <div class="card">
                <div class="card-header">
                    修改待办
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                @include('common.errors')

                <!-- New Task Form -->
                    <form action="{{ url('/api/v2/tasks/'.$task->id) }}" method="POST" class="form-horizontal" id="taskUpdateForm">
                    {{ csrf_field() }}

                    <!-- Task Name -->
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办名称</label>

                            <div class="col-md-8">
                                <input type="text" name="name" id="name" class="form-control" value="{{ $task->name }}">
                            </div>
                        </div>
                        
                        <!-- Parent Task -->
                        <div class="form-group row">
                            <label for="parent-task" class="col-md-3 control-label">父级任务</label>

                            <div class="col-md-8">
                                <select name="parent_task_id" id="parent_task_id" class="form-control">
                                    <option value="">-- 无父级任务 --</option>
                                    @foreach($task->user->tasks()->where(function($query) use($task) {
                                        $query->whereNull('parent_task_id')
                                              ->orWhere('parent_task_id', 0);
                                     })->where('id', '!=', $task->id)->get() as $parentTask)
                                        <option value="{{ $parentTask->id }}" {{ $task->parent_task_id == $parentTask->id ? 'selected' : '' }}>
                                            {{ $parentTask->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办等级</label>

                            <div class="col-md-8">
                                <label class="radio-inline">
                                    <input type="radio" name="priority" id="priority1"
                                           value="1" {{ empty($task->priority) || $task->priority == 1 ?'checked':'' }}><span>不重要不紧急事项</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="priority" id="priority2"
                                           value="2" {{ $task->priority == 2 ?'checked':'' }}><span>不重要紧急事项</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="priority" id="priority3"
                                           value="3" {{ $task->priority == 3 ?'checked':'' }}><span>重要不紧急事项</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="priority" id="priority4"
                                           value="4" {{ $task->priority == 4 ?'checked':'' }}><span>重要紧急事项</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办提醒时间</label>

                            <div class="col-md-8">
                                <input type="text" name="remindtime" id="remindtime" class="form-control"
                                       value="{{ $task->remindtime }}"
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办截止时间</label>

                            <div class="col-md-8">
                                <input type="text" name="deadline" id="deadline" class="form-control"
                                       value="{{ $task->deadline }}"
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办状态</label>

                            <div class="col-md-8">
                                <label class="radio-inline">
                                    <input type="radio" name="status" id="status1"
                                           value="1" {{ $task->status == 1 ?'checked':'' }}><span>进行中</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="status" id="status2"
                                           value="2" {{ $task->status == 2 ?'checked':'' }}><span>已完成</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="status" id="status3"
                                           value="3" {{ $task->status == 3 ?'checked':'' }}><span>已折叠</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办置顶</label>

                            <div class="col-md-8">
                                <label class="radio-inline">
                                    <input type="radio" name="is_top" id="is_top1"
                                           value="0" {{ empty($task->is_top) ?'checked':'' }}><span>不置顶</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="is_top" id="is_top2"
                                           value="1" {{ $task->is_top == 1 ?'checked':'' }}><span>置顶</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">模式</label>

                            <div class="col-md-8">
                                <label class="radio-inline">
                                    <input type="radio" name="mode" id="mode1"
                                           value="1" {{ (empty($task->mode) || $task->mode == 1) ?'checked':'' }}><span>工作</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="mode" id="mode2"
                                           value="2" {{ $task->mode == 2 ?'checked':'' }}><span>生活</span>
                                </label>
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i>提交！
                                </button>
                            </div>
                        </div>
                    </form>


                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;

            var form = document.getElementById('taskUpdateForm');
            if (!form) {
                return;
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!apiRequest) {
                    alert('API客户端未初始化');
                    return;
                }

                var submitBtn = form.querySelector('button[type=\"submit\"]');
                var original = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class=\"fa fa-spinner fa-spin\"></i> 提交中...';
                }

                apiRequest('PUT', '/tasks/{{ $task->id }}', {
                    name: (document.getElementById('name') || {}).value || '',
                    parent_task_id: (document.getElementById('parent_task_id') || {}).value || '',
                    priority: (form.querySelector('input[name=\"priority\"]:checked') || {}).value || '',
                    remindtime: (document.getElementById('remindtime') || {}).value || '',
                    deadline: (document.getElementById('deadline') || {}).value || '',
                    status: (form.querySelector('input[name=\"status\"]:checked') || {}).value || '',
                    is_top: (form.querySelector('input[name=\"is_top\"]:checked') || {}).value || '',
                    mode: (form.querySelector('input[name=\"mode\"]:checked') || {}).value || ''
                }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        window.location.href = '{{ url('/tasks') }}';
                        return;
                    }
                    alert((resp && resp.msg) ? resp.msg : '更新失败');
                }).catch(function() {
                    alert('网络错误，请稍后重试');
                }).finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = original;
                    }
                });
            });
        });
    </script>
@endsection
