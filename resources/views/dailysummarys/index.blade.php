@extends('layouts.app')

@section('content')

<script type="text/javascript">
$(document).ready(function () {

	$(".delete_dailysummary").click(function(){
		dailysummary_value = $(this).attr("dailysummary_value");
		dailysummary_token = $(this).attr("dailysummary_token");
		dailysummary_type = $(this).attr("dailysummary_type");

		if (dailysummary_type == 'delete' && !confirm("确认要删除此日报咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('dailysummary') }}"+"/"+dailysummary_value,
		    type: 'DELETE',
		    data: {type:dailysummary_type,_token:dailysummary_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+dailysummary_value).remove();
				}
		    }
		});
	});
});
</script>
    <div class="container">
            	@include('common.success')
                <div class="card">
                    <div class="card-header">
                        	日报列表
                        	<div style="float:right">
	                    		<a href="{{'/dailycreate'}}">[新建]</a>
	                    		<a href="{{'/index'}}">[返回]</a>
	                    	</div>
                    </div>

                    <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if (count($dailysummarys) > 0)
                    <table class="table table-striped dailysummary-table">
                            <thead>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($dailysummarys as $dailysummary)
                                    <tr id="{{$dailysummary->id}}">
                                        <td class="table-text"  width="80%">
                                        	<div class="preprepre">
                                        	{{ $dailysummary->summary_date }}
                                        	</div>
                                        	<div>{{ $dailysummary->work_content }}</div>
                                        	<div>{{ $dailysummary->life_content }}</div>
                                        </td>

                                        <td  width="10%"  align='right'>
                                        	<a href="{{ url('dailysummary/'.$dailysummary->id)}}" style="color:blue"><img alt=""     style="width: 15px;" src="/img/icon/edit.png"></a>
                                        	<a href="javascript:void(0)" class="delete_dailysummary" dailysummary_type="delete" dailysummary_value="{{ $dailysummary->id }}" dailysummary_token="{{ csrf_token() }}"  style="cursor:pointer;">
                                        		<img alt=""     style="width: 15px;" src="/img/icon/delete.png">
                                        	</a> 
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                         {!! $dailysummarys->links() !!}
                    @endif
                </div>
                </div>
        </div>
    </div>
@endsection
