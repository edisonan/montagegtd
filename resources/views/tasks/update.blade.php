@extends('layouts.app')

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
                    <form action="{{ url('task/'.$task->id) }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办名称</label>
								
                            <div class="col-md-8">
	                               <input type="text" name="name" id="name" class="form-control" value="{{ $task->name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办等级</label>
								
                            <div class="col-md-8">
	                            <label class="radio-inline">
								  <input type="radio" name="priority" id="priority1" value="0" {{ empty($task->priority) ?'checked':'' }}><span>不重要不紧急事项</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="priority2" value="1" {{ $task->priority == 2 ?'checked':'' }}><span>不重要紧急事项</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="priority2" value="1" {{ $task->priority == 3 ?'checked':'' }}><span>重要不紧急事项</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="priority2" value="1" {{ $task->priority == 4 ?'checked':'' }}><span>重要紧急事项</span>
								</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办提醒时间</label>
								
                            <div class="col-md-8">
	                               <input type="text" name="remindtime" id="remindtime" class="form-control" value="{{ $task->remindtime }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办截止时间</label>
								
                            <div class="col-md-8">
	                               <input type="text" name="deadline" id="deadline" class="form-control" value="{{ $task->deadline }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办状态</label>
								
                            <div class="col-md-8">
								<label class="radio-inline">
								  <input type="radio" name="status" id="status1" value="1" {{ $task->status == 1 ?'checked':'' }}><span>进行中</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="status" id="status2" value="2" {{ $task->status == 1 ?'checked':'' }}><span>已完成</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="status" id="status3" value="3" {{ $task->status == 1 ?'checked':'' }}><span>已折叠</span>
								</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办置顶</label>
								
                            <div class="col-md-8">
	                               <input type="text" name="is_top" id="is_top" class="form-control" value="{{ $task->is_top }}">
	                               <label class="radio-inline">
								  <input type="radio" name="is_top" id="is_top1" value="0" {{ empty($task->is_top) ?'checked':'' }}><span>不置顶</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="is_top" id="is_top2" value="1" {{ $task->is_top == 1 ?'checked':'' }}><span>置顶</span>
								</label>
                            </div>
                        </div>
                        
                        <!-- Add Task Button -->
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>提交！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                </div>
            </div>

        </div>
    </div>
@endsection
