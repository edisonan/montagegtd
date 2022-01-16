@extends('layouts.app')

<style>
.rowone {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
}
</style>

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
		    success: function(result_arr) {
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
			@include('common.success')
            <!-- Finish Pomos -->
                <div class="card">
                    <div class="card-header">
                        	番茄汇总
                        <div style="float:right">
                    		<a href="{{'/index'}}">[返回]</a>
                    	</div>
                    </div>

                    <div class="card-body">
		            @if (count($pomos) > 0)
                        <table class="table table-hover pomo-table">
                            <thead>
                                <th>完成的工作番茄</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
				<?php $lastDate = '';?>
                                @foreach ($pomos as $pomo)
                                    <tr id="{{$pomo->id}}">
                                        <td class="table-text" width="80%">
                                        <div class="rowone">
 <?php
$currentDate = date('m-d', strtotime($pomo->start_time));
if($currentDate != $lastDate)
{
$lastDate = $currentDate;
$style="";
}
else 
{
$style = "color:rgb(0,0,0,0);";
}
?>
<span style="{{ $style}}">
{{ $currentDate }}
</span>
<small>{{ date('H:i', strtotime($pomo->start_time)) }} - {{ date('H:i', strtotime($pomo->end_time)) }} </small>
<a href="/notes?add_content=%23记录番茄%23{{ urlencode($pomo->name) }}&pomo_id={{$pomo->id}}">[记录]</a>{{ $pomo->name }} 
</div>
</td>
                                        <!-- Task Delete Button -->
                                        <td width="20%" align="right">
                                        	
                                        	<a href="javascript:void(0)" class="delete_pomo" task_type="delete" task_value="{{ $pomo->id }}" pomo_token="{{ csrf_token() }}"  style="cursor:pointer;">
                                        		<img alt=""     style="width: 15px;" src="/img/icon/delete.png">
                                        	</a> 
                                        	<!-- 
                                            <form action="{{url('pomo/' . $pomo->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-pomo-{{ $pomo->id }}" class="btn btn-link">
                                                    <img alt=""     style="width: 15px;" src="/img/icon/delete.png">
                                                </button>
                                            </form>
                                             -->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                         {!! $pomos->links() !!}
                    @else
                    <div>
						<img src="/img/new/love.png" width="200px">
                    	暂时还没有完成哦，快去<a href="{{url('/index')}}">开始第一个番茄</a>吧！
                    	</div>
		            @endif
                    </div>
                </div>
    </div>
@endsection
