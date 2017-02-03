@extends('layouts.app')

@section('content')
    <div class="container">
            <!-- Current Tasks -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        	待办汇总
                    </div>

                    <div class="panel-body">
                    	
			            @if (count($tasks) > 0)
                        <table class="table table-striped task-table">
                            <thead>
                                <th>Task</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr>
                                        <td class="table-text"  width="80%"><div>{{ $task->name }}</div></td>

                                        <!-- Task Delete Button -->
                                        <td  width="20%" align="right">
                                            <form action="{{url('task/' . $task->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-task-{{ $task->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                    	暂时还没有完成哦，快去<a href="{{url('/index')}}">开始第一个任务</a>吧！
			            @endif
                    </div>
                </div>
        </div>
    </div>
@endsection
