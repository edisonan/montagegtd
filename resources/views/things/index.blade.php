@extends('layouts.app')
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

@section('content')
<script type="text/javascript">
$(document).ready(function () {

	$(".delete_thing").click(function(){
		thing_value = $(this).attr("thing_value");
		thing_token = $(this).attr("thing_token");
		thing_type = $(this).attr("thing_type");

		if (thing_type == 'delete' && !confirm("确认要删除此事情咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('thing') }}"+"/"+thing_value,
		    type: 'DELETE',
		    data: {type:thing_type,_token:thing_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert(result_arr.msg);
				} else {
					$('#'+thing_value).remove();
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
                    	新增事情记录
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New thing Form -->
                    <form action="{{ url('thing') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- thing Name -->
                        <div class="form-group">
                            <label for="thing-name" class="col-sm-3 control-label">完成事情名称</label>
								
                            <div class="col-sm-8">
	                               <input type="text" name="name" id="name" class="form-control" value="{{ old('thing') }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="thing-name" class="col-sm-3 control-label">事情开始时间</label>
								
                            <div class="col-sm-8">
	                               <input type="text" name="start_time" id="task-remindtime" class="form-control" value="" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="thing-name" class="col-sm-3 control-label">事情持续结束</label>
								
                            <div class="col-sm-8">
	                               <input type="text" name="end_time" id="task-deadline" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})">
                            </div>
                        </div>

                        <!-- Add thing Button -->
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
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新完成事情记录
                </div>

                <div class="panel-body">
                	@if (count($things) > 0)
                    
                    	<div>
		                    @foreach ($things as $thing)
			                    <div>
				                    <img alt=""     style="width: 15px;" src="/img/icon/thing{{ $thing->type }}.png">
				                    {{ $thing->name }}  
				                    <a href="{{ url('thing/'.$thing->id)}}" style="color:blue">
				                    	<img alt=""     style="width: 15px;" src="/img/icon/edit.png">
				                    </a>
				                    <a href="javascript:void(0)" class="delete_thing" thing_type="delete" thing_value="{{ $thing->id }}" thing_token="{{ csrf_token() }}"  style="cursor:pointer;">
				                    	<img alt=""     style="width: 15px;" src="/img/icon/delete.png">
				                    </a>
				                    <div style="float:right">
					                    <?php echo \App\Http\Utils\CommonUtil::formatTime($thing->start_time, $thing->end_time);?>
				                    </div>
			                    </div>
		                    @endforeach
                    	</div>
                    
                        {!! $things->links() !!}
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
