@extends('layouts.app')

@section('content')
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的分类
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('category') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="task-name" class="col-sm-3 control-label">分类名称</label>
								
                            <div class="col-sm-8">
	                               <input type="text" name="name" id="name" class="form-control" value="{{ old('task') }}">
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
                    
                    
                    @if (count($categorys) > 0)
                    <table class="table table-striped task-table">
                            <thead>
                                <th>分类列表</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($categorys as $category)
                                    <tr >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre">
                                        		{{ $category->name }}
                                        	</pre>
                                        </td>

                                        <!-- Task Delete Button -->
                                        <td  width="1"  align='right'>
                                            <form action="{{url('category/' . $category->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-task-{{ $category->id }}" class="btn btn-link" title="删除!!!">
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
