@extends('layouts.app')

@section('content')

<script type="text/javascript">
$(document).ready(function () {

	$(".delete_pomo").click(function(){
		pomo_value = $(this).attr("pomo_value");
		pomo_token = $(this).attr("pomo_token");
		pomo_type = $(this).attr("pomo_type");

		if (pomo_type == 'delete' && !confirm("确认要删除此番茄咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('pomo') }}"+"/"+pomo_value,
		    type: 'DELETE',
		    data: {type:pomo_type,_token:pomo_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+pomo_value).remove();
				}
		    }
		});
	});
});
</script>
    <div class="container">
            <!-- Current Goals -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        	技能列表
                    </div>

                    <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('goal') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="goal-name" class="col-sm-3 control-label">技能名称:</label>

                            <div class="col-sm-8">
	                                <input type="text" name="name" id="goal-name" class="form-control" value="{{ old('goal') }}">
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>添加！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                    @if (count($goals) > 0)
                    <table class="table table-striped goal-table">
                            <thead>
                                <th>&nbsp;</th>
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
                                        <td  width="1"  align='right'>
                                            <a href="{{ url('goal/'.$goal->id)}}" style="color:blue"><img alt=""     style="width: 15px;" src="/img/icon/edit.png"></a>
                                        </td>
                                        <td  width="10%"  align='right'>
                                            <form action="{{url('goal/' . $goal->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-goal-{{ $goal->id }}" class="btn btn-link">
                                                    <img alt=""     style="width: 15px;" src="/img/icon/delete.png">
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                         {!! $goals->links() !!}
                    @endif
                </div>
                </div>
        </div>
    </div>
@endsection
