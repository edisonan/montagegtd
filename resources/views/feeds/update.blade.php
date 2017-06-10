@extends('layouts.app')

@section('content')
<script type="text/javascript">
$(document).ready(function () {

	$("#check_url").click(function(){
		url = $("#url").val();
		$.get("{{ url('feed/checkFeedUrl') }}",{url:url},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert('该url未检测到内容，请确认！');
			} else {
				alert('检测成功');
			}
			$("#feed_name").val(result_arr.result.title);
		});
	});
});
</script>
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	修改订阅
                    	<div style="float:right">
                    		[<a href="{{ url('categorys') }}" target="_blank">分类设置</a>]
                    	</div>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('feed/'.$feed->id) }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="task-name" class="col-sm-3 control-label">订阅地址</label>
								
                            <div class="col-sm-8">
	                                <input type="text" name="url" id="url" class="form-control" value="{{ $feed->url }}">
	                                <a href="javascript:void(0)" id="check_url">check url!</a>
                            </div>
                        </div>
                        
                        <div class="form-group" id="task_form_div1" >
                            <label for="task-name" class="col-sm-3 control-label">订阅名称</label>
                            
                            <div class="col-sm-8">
                            	<input type="text" name="feed_name" id="feed_name" class="form-control" value="{{ $feed->feed_name }}">
                            </div>
							
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div4" >
                            <label for="task-name" class="col-sm-3 control-label">所属分类</label>

                            <div class="col-sm-6">
                            	@if(count($categorys) == 0)
                            	所有订阅必须有分类，您当前尚未建立分类，请前往建立后再新增订阅！[<a href="{{ url('categorys') }}" target="_blank">分类设置</a>]
                            	@else
	                            <select class="form-control" name="category_id">
		                              @foreach ($categorys as $category)
									  	<option value="{{ $category->id }}" @if($feed->category_id == $category->id) checked @endif>{{ $category->name }}</option>
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
                    
                    
                </div>
            </div>

        </div>
    </div>
@endsection
