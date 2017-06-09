@extends('layouts.app')

@section('content')
    <div class="container">
            <!-- Current Goals -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        	Goals
                    </div>

                    <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('goal') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="goal-name" class="col-sm-3 control-label">Name:</label>

                            <div class="col-sm-8">
	                                <input type="text" name="name" id="goal-name" class="form-control" value="{{ old('goal') }}">
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                    @if (count($goals) > 0)
                    <table class="table table-striped goal-table">
                            <thead>
                                <th>goals</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($goals as $goal)
                                    <tr>
                                        <td class="table-text"  width="80%">
                                        	<div class="preprepre">
                                        	{{ $goal->name }}
                                        	</pre>
                                        </td>

                                        <!-- Task Delete Button -->
                                        <td  width="10%"  align='right'>
                                            <form action="{{url('goal/' . $goal->id)}}" method="POST" class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
												<input type="hidden" name="type" value="finish"/> 
                                                <button type="submit" id="delete-goal-{{ $goal->id }}" class="btn btn-success">
                                                    <i class="glyphicon glyphicon-ok"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td  width="10%"  align='right'>
                                            <form action="{{url('goal/' . $goal->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-goal-{{ $goal->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>
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
