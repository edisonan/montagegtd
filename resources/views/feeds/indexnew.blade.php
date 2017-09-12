@extends('layouts.app')



@section('content')
<script src="//cdnjs.cloudflare.com/ajax/libs/Sortable/1.6.1/Sortable.min.js"></script>

<script type="text/javascript">
$(document).ready(function () {

	$("#check_url").unbind("click").click(function(){
		$("#processTips").text("处理中");
		
		url = $("#url").val();
		$.get("{{ url('feed/checkFeedUrl') }}",{url:url},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert('该url未检测到内容，请确认！');
			}
			$("#feed_name").val(result_arr.result.title);
			$("#processTips").text("处理完成");
		});
	});

	$(".delete_feed").click(function(){
		feed_value = $(this).attr("feed_value");
		feed_token = $(this).attr("feed_token");
		feed_type = $(this).attr("feed_type");

		if (feed_type == 'delete' && !confirm("确认要删除此订阅咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('feed') }}"+"/"+feed_value,
		    type: 'DELETE',
		    data: {type:feed_type,_token:feed_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+feed_value).remove();
				}
		    }
		});
	});
});

@foreach ($nav_infos as $nav_info)
Sortable.create('foo{{ $nav_info['category_info']['category_id'] }}', {
	  group: 'foo{{ $nav_info['category_info']['category_id'] }}',
	  animation: 100
});
@endforeach

</script>

    <div class="container">
    
        <div class="col-sm-offset-0 col-sm-12">
        	@include('common.success')
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的订阅
                    	<div style="float:right">
                    		[<a href="{{ url('categorys') }}" target="_blank">分类设置</a>]
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
	                                <input type="text" name="url" id="url" class="form-control" value="{{ $url }}">
	                                <a href="javascript:void(0)" id="check_url">检测地址!</a><span id="processTips"></span>
                            </div>
                        </div>
                        
                        <div class="form-group" id="task_form_div1" >
                            <label for="task-name" class="col-sm-3 control-label">订阅名称</label>
                            
                            <div class="col-sm-8">
                            	<input type="text" name="feed_name" id="feed_name" class="form-control" value="{{  $title }}">
                            </div>
							
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div4" >
                            <label for="task-name" class="col-sm-3 control-label">所属分类</label>

                            <div class="col-sm-6">
                            	@if(count($categorys) == 0)
                            	所有订阅必须有分类，您当前尚未建立分类，请前往建立后再新增订阅！[<a href="{{ url('categorys') }}" target="_blank">分类设置</a>]
                            	@else
	                            <select class="form-control" name="category_id">
		                              @foreach ($nav_infos as $nav_info)
									  	<option value="{{ $nav_info['category_info']['category_id'] }}">{{ $nav_info['category_info']['category_id'] }}</option>
									  @endforeach
								</select>
								@endif
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
                    
                    
                    @if (count($nav_infos) > 0)
                    	@foreach ($nav_infos as $nav_info)
                                <ul id="foo{{ $nav_info['category_info']['category_id'] }}">
                                	@foreach($nav_info['list'] as $feed)
                                	<li>{{ $feed['feed_name'] }}</li>
                                	@endforeach
  								</ul>	
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
