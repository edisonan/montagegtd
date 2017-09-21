@extends('layouts.app')

@section('content')
    <div class="container">
    
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	添加反馈
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('index/feedbackStore') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="task-name" class="col-sm-3 control-label">反馈来源</label>
								
                            <div class="col-sm-8">
	                               <input type="text" name="from" id="name" class="form-control" value="{{ $from }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-name" class="col-sm-3 control-label">反馈内容</label>
								
                            <div class="col-sm-8">
	                               <textarea type="text" name="content" id="category_order" class="form-control" value=""></textarea>
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
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
