@extends('layouts.app')
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>
<script language="javascript" type="text/javascript"> 

	/**
	* 尝试获取通知权限
	*/
	document.addEventListener('DOMContentLoaded', function () {
	  if (!Notification) {
	    alert('Desktop notifications not available in your browser. Try Chromium.'); 
	    return;
	  }

	  if (Notification.permission !== "granted"){
	    Notification.requestPermission();
	  }
	});

	/**
	* 通知消息
	*/
	function notify(message)
	{
		if (Notification.permission !== "granted")
		    Notification.requestPermission();
		  else {
		    var notification = new Notification('蒙太奇-专业GTD,剪辑你自己的生活', {
		      icon: 'https://task.congcong.us/favicon.ico',
		      body: message,
		    });

		    notification.onclick = function () {
		      window.open("{{'/index'}}");      
		    };
		  }
	}
	
	var interval = 1000; 
	var remain = {{ $current_pomo_remain }};//剩余时间
	var status = {{ $current_pomo_status }};//当前状态

	function ShowCountDown(leftsecond, pomoBtnId) 
	{ 
		var minute=Math.floor(leftsecond/60); 
		var second=Math.floor(leftsecond - minute * 60); 
		
		var cc = document.getElementById(pomoBtnId); 
		
		remain = remain - 1;
		if(remain == 0){
			if(status == 2){
				notify("您已经完成了一个番茄，快来记录一下吧~");
			} else if(status == 4){
				notify("休息完成，快来开始下一个番茄吧~");
			}
		}
		
		if(remain < 0){
			if(status == 2){
				document.getElementById('currentPomo').style.display = "none";
				document.getElementById('recordPomo').style.display = "";
				return false;
			} else {
				location.href = '{{url('/index')}}';
			}
		}

		var minute_label = (minute >= 10)?minute:"0"+minute ;
		var second_label = (second >= 10)?second:"0"+second ;

		var add_content = status == 2?'#此番茄还剩#':'#休息还剩#';
		
		cc.innerHTML = add_content + minute_label +":"+ second_label; 
	}

	function discard(){
		if (confirm("确认要放弃咩？")) {
			location.href = '{{ url("pomos/discard/") }}/{{ $active_pomo->id }}';
		}
	}
	
	function clearTips($suffix){
		setCookie('{{ date('Ymd') }}'+$suffix,"close",1);
	}

	function setCookie(c_name,value,expiredays)
	{
		var exdate=new Date()
		exdate.setDate(exdate.getDate()+expiredays)
		document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
	}
	
	if(status == 2 || status == 4){
		window.setInterval(function(){ShowCountDown( remain, "currentPomo" );}, interval); 
	}
</script>

@section('content')

<script src="{{'/js/bootstro.min.js'}}"></script>
<link href="{{'/css/bootstro.min.css'}}" rel="stylesheet">

<script type="text/javascript">
$(document).ready(function () {

	$("#startPomo").click(function(){
		$.ajax({
		    url: "{{ url('pomos/start') }}",
		    type: 'GET',
		    data: {"_token":"{{ csrf_token() }}"},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					window.setInterval(function(){ShowCountDown( result_arr.result.current_pomo_remain, "currentPomo" );}, interval); 
				}
		    }
		});
	});
	
	$(".finish_task, .delete_task").click(function(){
		task_value = $(this).attr("task_value");
		task_token = $(this).attr("task_token");
		task_type = $(this).attr("task_type");

		if (task_type == 'delete' && !confirm("确认要删除此任务咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('task') }}"+"/"+task_value,
		    type: 'DELETE',
		    data: {type:task_type,_token:task_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+task_value).remove();
				}
		    }
		});
	});

	$(".top_task").click(function(){
		task_value = $(this).attr("task_value");
		task_token = $(this).attr("task_token");
		task_is_top = $(this).attr("task_is_top");

		if(task_is_top != 1){
			task_is_top = 1;
		} else {
			task_is_top = 0;
		}
		
		$.ajax({
		    url: "{{ url('task') }}"+"/"+task_value,
		    type: 'POST',
		    data: {is_top:task_is_top,_token:task_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					location.href = '{{url('/index')}}';
				}
		    }
		});
	});

	$(".task_content").click(function(){
		task_value = $(this).text();
		pomo_value = $("#pomo_name").val();
		
		if(pomo_value == ''){
			$("#pomo_name").val(pomo_value+task_value);
		} else if(pomo_value.indexOf(task_value)==-1){
			$("#pomo_name").val(pomo_value+ ' + ' +task_value);
		} else {
			$("#pomo_name").val(pomo_value.replace(task_value,''));
		}
		
	});

	$(".task_li").hover(function(){
		$(this).find(".delete_task").show();
		$(this).find(".top_task").show();
	},function(){
		$(this).find(".delete_task").hide();
		if($(this).find(".top_task").attr("task_is_top") != 1){
			$(this).find(".top_task").hide();
		}
	});

	$(".new_user_guide").click(function(){
		 bootstro.start('.bootstro', {stopOnBackdropClick : true, stopOnEsc:true});       
    });

	$.ajax({
	    url: "{{ url('tasks') }}",
	    type: 'GET',
	    data: {"_token":"{{ csrf_token() }}"},
	    success: function(result) {
	    	result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert('处理失败，请稍后再试');
			} else {
				$.each( result_arr.data, function( index, data ){
					$("#tasks").append('<li><span>'+data.name+'</span></li>');
				});
			}
	    }
	});

	$.ajax({
	    url: "{{ url('pomos') }}",
	    type: 'GET',
	    data: {"_token":"{{ csrf_token() }}"},
	    success: function(result) {
	    	result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert('处理失败，请稍后再试');
			} else {
				$.each( result_arr.data, function( index, data ){
					$("#pomos").append('<li><span>'+data.name+'</span></li>');
				});
			}
	    }
	});
});

/**
 * 监听键盘回车事件
 */
$(document).keyup(function(event){  
	if(event.keyCode ==13){  
		if($("#task_name").is(":focus")){
			task_name = $("#task_name").val();
			$.ajax({
			    url: "{{ url('task') }}",
			    type: 'POST',
			    data: {"name":task_name,"_token":"{{ csrf_token() }}"},
			    success: function(result) {
			    	result_arr = JSON.parse(result);
					if(result_arr.code != 9999){
						alert('处理失败，请稍后再试');
					} else {
						$("#task_name").val("");
						console.log("ok");
					}
			    }
			});
		} else if($("#pomo_name").is(":focus")){
			add_pomo();
		}
	}  
}); 
</script>
<div class="container">
	@include('common.success')
	<div class="row">
		<div class=" col-md-6 bootstro" data-bootstro-step="0"
			data-bootstro-placement="bottom" data-bootstro-nextButtonText="下一步"
			data-bootstro-content="使用番茄工作法，选择一个待完成的任务，将番茄时间设为25分钟，专注工作，中途不允许做任何与该任务无关的事，直到番茄时钟响起，然后在纸上画一个X短暂休息一下（5分钟就行），每4个番茄时段多休息一会儿。"
			data-bootstro-finishButton="返回网站，开启高效生活~">
			<div class="card card-default">
				<div class="card-header">
					开蕃走起
					<div style="float: right">
						<a href="{{'pomos'}}">[历史]</a> <a href="{{'things'}}">[记事]</a> <a
							href="javascript:void(0)" class="new_user_guide">[?]</a>
					</div>
				</div>

				<div class="card-body">
					@if($current_pomo_status == 2 || $current_pomo_status == 4 ) <a
						class="btn btn-outline-info btn-shadow btn-block"
						href="javascript:void(0)" role="button" id="currentPomo"
						onclick="discard()"></a> @elseif($current_pomo_status == 1) <a
						class="btn btn-outline-info btn-shadow btn-block"
						href="javascript:void(0)" role="button" id="startPomo"> 开始一个新的番茄吧!
					</a> @endif

					<div class="form-group" @if($current_pomo_status !=3)
						style="display: none" @endif id="recordPomo">
						<div class="col-md-9"
							style="display: -webkit-inline-box; width: 75%;">
							<input type="text" name="name" id="pomo_name"
								class="form-control" value="" placeholder="记录刚完成的番茄内容？点击任务名快速添加">
						</div>
						<a href="javascript:void(0)" onclick="discard()" title="放弃此番茄"><small>x?</small></a>
					</div>

					<hr width=100% size=1 color=#bbbcbc
						style="FILTER: alpha(opacity = 100, finishopacity = 0)">

					<ul id="pomos">
					
					</ul>
				</div>
			</div>

		</div>



		<div class=" col-md-6 bootstro" data-bootstro-step="1"
			data-bootstro-placement="bottom" data-bootstro-prevButtonText="上一步"
			data-bootstro-content="在这里创建待办事项，高级功能里面可以增加提醒、优先级设定等功能"
			data-bootstro-finishButton="返回网站，开启高效生活~">
			<div class="card card-default">
				<div class="card-header">
					新的待办事项
					<div style="float: right">
						<a href="{{'tasks'}}">[已完成待办]</a> <a href="javascript:void(0)"
							class="new_user_guide">[?]</a>
					</div>
				</div>

				<div class="card-body">
					<div class="form-group">
						<input type="text" name="name" id="task_name" class="form-control"
							value="" style="" placeholder="添加新任务">
					</div>

					<hr width=100% size=1 color=#bbbcbc
						style="FILTER: alpha(opacity = 100, finishopacity = 0)">
					
					<ul id="tasks">
					
					</ul>
				</div>
			</div>

		</div>
	</div>
</div>
@endsection
