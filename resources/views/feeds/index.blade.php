@extends('layouts.app')

@section('content')
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的订阅
                    	<div style="float:right">
                    		[<a href="{{ url('categorys') }}" target="_blank">新建</a>]
                    	</div>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('feed') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="task-name" class="col-sm-3 control-label">订阅地址</label>
								
                            <div class="col-sm-8">
	                                <input type="text" name="url" id="url" class="form-control" value="{{ old('task') }}">
                            </div>
                        </div>
                        
                        <div class="form-group" id="task_form_div1" >
                            <label for="task-name" class="col-sm-3 control-label">订阅名称</label>
                            
                            <div class="col-sm-8">
                            	<input type="text" name="feed_name" id="feed_name" class="form-control" value="{{ old('task') }}">
                            </div>
							
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div4" >
                            <label for="task-name" class="col-sm-3 control-label">所属分类</label>

                            <div class="col-sm-6">
	                            <select class="form-control" name="category_id">
		                              @foreach ($categorys as $category)
									  	<option value="{{ $category->id }}">{{ $category->name }}</option>
									  @endforeach
								</select>
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
                    
                    
                    @if (count($feeds) > 0)
                    <table class="table table-striped task-table">
                            <thead>
                                <th>订阅列表</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($feeds as $feed)
                                    <tr >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre">
                                        	
                                        	@if(!empty($feed->category->name))
                                        	<a href="#{{$task->goal->id}}">[{{ $feed->category->name }}]</a>
                                        	@endif
                                        	
                                        	{{ $feed->feed_name }}|
                                        	{{ $feed->url }}
                                        	</pre>
                                        </td>

                                        <!-- Task Delete Button -->
                                        <td  width="1"  align='right'>
                                            <form action="{{url('feed/' . $feed->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-task-{{ $feed->id }}" class="btn btn-link" title="删除!!!">
                                                	<!-- 
                                                    <i class="fa fa-btn fa-trash"></i>
                                                	 -->
                                                	 <span style="color:red">×</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
