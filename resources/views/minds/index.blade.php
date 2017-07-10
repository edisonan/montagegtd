@extends('layouts.app')

@section('content')

<script type="text/javascript">
$(document).ready(function () {
	$("#check_url").unbind("click").click(function(){
// 		$("#processTips").text("处理中");
		
// 		url = $("#url").val();
// 		$.get("{{ url('mind/checkFeedUrl') }}",{url:url},function(result){
// 			result_arr = JSON.parse(result);
// 			if(result_arr.code != 9999){
// 				alert('该url未检测到内容，请确认！');
// 			}
// 			$("#mind_name").val(result_arr.result.title);
// 			$("#processTips").text("处理完成");
// 		});
	});

	$(".delete_mind").click(function(){
		mind_value = $(this).attr("mind_value");
		mind_token = $(this).attr("mind_token");
		mind_type = $(this).attr("mind_type");

		if (mind_type == 'delete' && !confirm("确认要删除此目标咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('mind') }}"+"/"+mind_value,
		    type: 'DELETE',
		    data: {type:mind_type,_token:mind_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+mind_value).remove();
				}
		    }
		});
	});
});
</script>
    <div class="container">
    
        <div class="col-sm-offset-0 col-sm-12">
        	@include('common.success')
            <div class="panel panel-default">
                <div class="panel-heading">
                    	思维导图
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New mind Form -->
                    <form action="{{ url('mind') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- mind Name -->
                        <div class="form-group" id="mind_form_div1" >
                            <div class="col-sm-9" style="display: -webkit-inline-box;width: 75%;">
                            	<input type="text" name="name" id="name" class="form-control" value="">
                            </div>
                            <div class="col-sm-3" style="display: -webkit-inline-box;width: 25%;">
                            	<button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>新想法！
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    	导图列表
                </div>

                <div class="panel-body">
                
                 		@if (count($minds) > 0)
                                @foreach ($minds as $mind)
                                	<div calss="col-sm-12" id="{{$mind->id}}">
                                		<div class="col-sm-3  text-center">
                                			<div>
                                				<img alt="" src="/img/cover.jpg" width="150px">
                                			</div>
                                			<div style="margin-top: -30px;">
                                				<a href="{{url('mind/' . $mind->id)}}" title="">
	                                				<span style="display: block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
	                                					{{ $mind->name }}
	                                				</span>
                                				</a>
                                			</div>
                                			<div class="text-left col-sm-6"  style="display: -webkit-inline-box;">
		                                		<a href="{{ url('mind/'.$mind->id)}}" style="color:blue">
		                                			<img alt="" style="width: 15px;" src="/img/icon/edit.png">
		                                		</a>
                                			</div>
                                			<div class="text-right col-sm-6">
		                                		<a href="javascript:void(0)" class="delete_mind" mind_type="delete" mind_value="{{ $mind->id }}" mind_token="{{ csrf_token() }}"  style="cursor:pointer;" class="text-right">
		                                        		<img alt=""     style="width: 15px;" src="/img/icon/delete.png">
		                                        </a> 
	                                        </div>
                                		</div>
                                	</div>
                                @endforeach
                        
                         		{!! $minds->links() !!}
                    	@endif

                </div>
            </div>

        </div>
    </div>
@endsection
