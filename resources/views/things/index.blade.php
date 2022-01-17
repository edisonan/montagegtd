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
		    success: function(result_arr) {
				if(result_arr.code != 9999){
					alert(result_arr.msg);
				} else {
					$('#'+thing_value).remove();
				}
		    }
		});
	});
});
Date.prototype.format = function (fmt) {
  var o = {
    "M+": this.getMonth() + 1, //月份
    "d+": this.getDate(), //日
    "h+": this.getHours(), //小时
    "m+": this.getMinutes(), //分
    "s+": this.getSeconds(), //秒
    "q+": Math.floor((this.getMonth() + 3) / 3), //季度
    S: this.getMilliseconds(), //毫秒
  };
  if (/(y+)/.test(fmt)) {
    fmt = fmt.replace(
      RegExp.$1,
      (this.getFullYear() + "").substr(4 - RegExp.$1.length)
    );
  }
  for (var k in o) {
    if (new RegExp("(" + k + ")").test(fmt)) {
      fmt = fmt.replace(
        RegExp.$1,
        RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length)
      );
    }
  }
  return fmt;
};
function changeTime($id, $interval) {
$idName = "#" + $id;
$time = Number(new Date($($idName).val()).getTime());
$time = $time + $interval*60000;
$($idName).attr("value", new Date($time).format("yyyy-MM-dd hh:mm:ss"));

}
</script>
    <div class="container">
    
        <div class=" col-md-12">
        	
        	@include('common.success')
        	
            <div class="card">
                <div class="card-header">
                    	新增事情记录
                    	<div style="float:right">
                    		<a href="{{'/index'}}">[返回]</a>
                    	</div>
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New thing Form -->
                    <form action="{{ url('thing') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- thing Name -->
                        <div class="form-group row">
                            <label for="thing-name" class="col-md-3 control-label">完事内容</label>
								
                            <div class="col-md-8">
	                               <input type="text" name="name" id="name" class="form-control" value="{{ old('thing') }}">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="thing-name" class="col-md-3 control-label">完事时间</label>
								
				<div class="col-md-8">
                            	<?php $time = time();$time = $time-$time%600;?>
                            	<div class="col-md-12" style="padding-left:0px;">
	                               <a href="javascript:void(0);" style="float:right" onclick="changeTime('start_time', 5)">&nbsp;+5分钟</a>
	                               <a href="javascript:void(0);" style="float:right" onclick="changeTime('start_time', -5)">&nbsp;-5分钟</a>
	                               <input type="text" name="start_time" id="start_time" class="form-control" value="{{ date('Y-m-d H:i:00', $time - 600)}}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:m:00',maxDate:'%y-%M-%d'})">
                            	</div>
	                               -
	                            <div class="col-md-12" style="padding-left:0px;">
	                               <a href="javascript:void(0);" style="float:right" onclick="changeTime('end_time', 5)">&nbsp;+5分钟</a>
	                               <a href="javascript:void(0);" style="float:right" onclick="changeTime('end_time', -5)">&nbsp;-5分钟</a>
	                               <input type="text" name="end_time" id="end_time" class="form-control" value="{{ date('Y-m-d H:i:00', $time)}}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:m:00',maxDate:'%y-%M-%d'})">
	                            </div>
                            </div>
                        </div>

                        <!-- Add thing Button -->
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i>提交！
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    	新完成事情记录
                </div>

                <div class="card-body">
                	@if (count($things) > 0)
                	<table class="table table-hover thing-table">
                            <thead>
                                <th>完成的事情</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
<?php $lastDate = '';?>
		                    @foreach ($things as $thing)
		                    <tr id="{{$thing->id}}">
                                        <td class="table-text" width="80%">
                                        <div class="rowone">
 <?php
$currentDate = date('m-d', strtotime($thing->start_time));
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
<small>{{ date('H:i', strtotime($thing->start_time)) }} - {{ date('H:i', strtotime($thing->end_time)) }} </small>
				                    <img alt=""     style="width: 15px;" src="/img/icon/thing{{ $thing->type }}.png">
				                   <span title="{{ $thing->name }}" > {{ $thing->name }}  </span>
				                   </div>
				                   </td>
				                   
				                   <td width="20%" align="right">
					                    <a href="{{ url('thing/'.$thing->id)}}" style="">
								<i class="bi-pencil-square" style="font-size: 1.5rem;"></i>
					                    </a>
					                    <a href="javascript:void(0)" class="delete_thing" thing_type="delete" thing_value="{{ $thing->id }}" thing_token="{{ csrf_token() }}"  style="cursor:pointer;">
									<i class="bi-trash" style="font-size: 1.5rem;"></i>
					                    </a>
				                   </td>
				                   </tr>
		                    @endforeach
                    </tbody>
                        </table>
                        {!! $things->links() !!}
                        @else
                        <div>
			<img src="/img/new/love.png" width="200px">
                    	暂时还没有事情哦，快去<a href="{{url('/index')}}" style="color:green">开始做点番茄或者考虑一下待办</a>吧！
                    	</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
