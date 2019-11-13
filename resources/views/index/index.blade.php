@extends('layouts.app')
<script language="javascript" type="text/javascript"> 

	Date.prototype.format = function (fmt) {
	  var o = {
	      "M+": this.getMonth() + 1, //月份
	      "d+": this.getDate(), //日
	      "h+": this.getHours(), //小时
	      "m+": this.getMinutes(), //分
	      "s+": this.getSeconds(), //秒
	      "q+": Math.floor((this.getMonth() + 3) / 3), //季度
	      "S": this.getMilliseconds() //毫秒
	  };
	  if (/(y+)/.test(fmt)) {
	    fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	  }
	  for (var k in o) {
	    if (new RegExp("(" + k + ")").test(fmt)) {
	      fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ?
	        (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	    }
	  }
	  return fmt;
	}
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
				window.setInterval(function(){notify("您已经完成了一个番茄，快来记录一下吧~");}, 15*60*1000); 
			} else if(status == 4){
				notify("休息完成，快来开始下一个番茄吧~");
				window.setInterval(function(){notify("休息完成，快来开始下一个番茄吧~");}, 15*60*1000); 
			}
		}
		
		if(remain < 0){
			if(status == 2){
				document.getElementById('pomoBtn').style.display = "none";
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
			location.href = '{{ url("pomos/discard/") }}/'+$('#pomo_id').val();
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
		window.setInterval(function(){ShowCountDown( remain, "pomoBtn" );}, interval); 
	}
</script>

@section('content')

<script src="{{'/js/bootstro.min.js'}}"></script>
<link href="{{'/css/bootstro.min.css'}}" rel="stylesheet">
<style>
.recent-list {
    list-style: none;
    font-size: .9em;
    line-height: 1.5em;
    margin-left: -30px; 
}
.time {
    float: left;
    padding-right: .8em;
    color: #999;
}
.number {
    font-size: .9em;
    color: #666;
    float: right;
}
.head {
    padding-bottom: 25px;
}

input[type=checkbox]{        
   margin-right: 5px;        
   /*同样，首先去除浏览器默认样式*/  
   -webkit-appearance: none;        
   -moz-appearance: none;        
   appearance: none;        
   /*编辑我们自己的样式*/   
   position: relative;        
   width: 13px;        
   height: 13px;        
   background: transparent;        
   border:1px solid #0b91bd;        
   -webkit-border-radius: 4px;        
   -moz-border-radius: 4px;        
   border-radius: 4px;        
   outline: none;        
   cursor: pointer;    
}
input[type=checkbox]:after{        
   content: '√';        
   position: absolute;        
   display: block;        
   width: 100%;        
   height: 100%;        
   background: #00BFFF;        
   color: #fff;        
   text-align: center;        
   line-height: 18px;        
   /*增加动画*/   
   -webkit-transition: all ease-in-out 300ms;        
   -moz-transition: all ease-in-out 300ms;        
   transition: all ease-in-out 300ms;        
   /*利用border-radius和opacity达到填充的假象，首先隐藏此元素*/  
    -webkit-border-radius: 20px;        
   -moz-border-radius: 20px;        
   border-radius: 20px;        
   opacity: 0;    
}
input[type=checkbox]:checked:after{        
   -webkit-border-radius: 0;        
   -moz-border-radius: 0;        
   border-radius: 0;        
   opacity: 1;    
}
</style>
<script type="text/javascript">
$(document).ready(function () {

	$("#pomoBtn").click(function(){
		if(status == 1){
			$.ajax({
			    url: "{{ url('pomos/start') }}",
			    type: 'GET',
			    data: {"_token":"{{ csrf_token() }}"},
			    success: function(result) {
			    	result_arr = JSON.parse(result);
					if(result_arr.code != 9999){
						alert('处理失败，请稍后再试');
					} else {
						$("#pomo_id").val(result_arr.result.active_pomo.id);
						remain = result_arr.result.current_pomo_remain;
						status = result_arr.result.current_pomo_status;
						window.setInterval(function(){ShowCountDown( remain, "pomoBtn" );}, interval); 
					}
			    }
			});
		} else if( status == 2 || status == 4) {
			discard();
		}
	});
	
	$("#tasks").on('click','.finish_task',function(){
		task_value = $(this).attr("task_value");
		task_type = $(this).attr("task_type");

		if (task_type == 'delete' && !confirm("确认要删除此任务咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('task') }}"+"/"+task_value,
		    type: 'DELETE',
		    data: {"type":task_type,"_token":"{{ csrf_token() }}"},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#task'+task_value).remove();
				}
		    }
		});
	});

	$("#tasks").on('click','.top_task',function(){
		task_value = $(this).attr("task_value");
		task_is_top = $(this).attr("task_is_top");

		if(task_is_top != 1){
			task_is_top = 1;
		} else {
			task_is_top = 0;
		}
		
		$.ajax({
		    url: "{{ url('task') }}"+"/"+task_value,
		    type: 'POST',
		    data: {"is_top":task_is_top,"_token":"{{ csrf_token() }}"},
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

	$("#tasks").on('click','.update_task',function(){
		task_value = $(this).attr("task_value");
		task_name = $(this).attr("task_name");

		window.location.href='/task/'+task_value;
	});

	$(".task_content").on('click',function(){
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

	$(".task_li").on('hover',function(){
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
	    data: {"_token":"{{ csrf_token() }}","status":1},
	    success: function(result) {
	    	result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert('处理失败，请稍后再试');
			} else {
				$.each( result_arr.result, function( index, data ){
					$str = '<li id="task'+data.id+'">';;
					$str += '<p class="task_content" task_value="'+data.id+'" task_is_top="' + data.is_top + '">';
					$str += '<input type="checkbox" class="finish_task" task_type="finish" task_value="'+data.id+'"/>';
					$str += '<a href="javascript:void(0)" class="top_task" task_value="'+data.id+'" task_is_top="' + data.is_top + '">[置顶]</a>';
					$str += '<a href="javascript:void(0)" class="update_task" task_value="'+data.id+'">[更新]</a>';
					$str += '<a href="javascript:void(0)" class="finish_task"  task_type="delete" task_value="'+data.id+'">[删除]</a>';
					$str += '<a href="/notes?add_content=%23记录待办%23"'+encodeURIComponent(data.name)+'&task_id='+data.id+'" target="_blank">[记录]</a>';
					$str += data.name;
					$str += '</p>';
					$str += '</li>'
					$("#tasks").append($str);
				});
				$('#taskCount').text(Object.getOwnPropertyNames(result_arr.result).length - 1);
			}
	    }
	});

	$.ajax({
	    url: "{{ url('pomos') }}",
	    type: 'GET',
	    data: {"_token":"{{ csrf_token() }}",'type':'time'},
	    success: function(result) {
	    	result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert('处理失败，请稍后再试');
			} else {
				$.each( result_arr.result, function( index, data ){
					$str = '<li id="pomo'+data.id+'">';
					$str += '<span class="time">';
					$str += (new Date(data.created_at)).format("hh:mm") +' - '+ (new Date(data.updated_at)).format("hh:mm");
					$str += '</span>';
					$str += '<p>';
					$str += '<a href="/notes?add_content=%23记录番茄%23"'+encodeURIComponent(data.name)+'&task_id='+data.id+'" target="_blank">[记录]</a>';
					$str += data.name;
					$str += '</p>';
					$str += '</li>';
					$("#pomos").append($str);
				});
				$('#pomoCount').text(Object.getOwnPropertyNames(result_arr.result).length - 1);
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
						//temp
						$("#task_name").val("");
						var data = result_arr.result;
						$str = '<li id="task'+data.id+'">';
						$str += '<p class="task_content" task_value="'+data.id+'" task_is_top="' + data.is_top + '">';
						$str += '<input type="checkbox" class="finish_task" task_type="finish" task_value="'+data.id+'"/>';
						$str += '<a href="javascript:void(0)" class="top_task" task_value="'+data.id+'" task_is_top="' + data.is_top + '">[置顶]</a>';
						$str += '<a href="javascript:void(0)" class="update_task" task_value="'+data.id+'">[更新]</a>';
						$str += '<a href="javascript:void(0)" class="finish_task" task_type="delete" task_value="'+data.id+'">[删除]</a>';
						$str += '<a href="/notes?add_content=%23记录待办%23"'+encodeURIComponent(data.name)+'&task_id='+data.id+'" target="_blank">[记录]</a>';
						$str += data.name;
						$str += '</p>';
						$str += '</li>'
						$("#tasks").prepend($str);
					}
			    }
			});
		} else if($("#pomo_name").is(":focus")){
			pomo_name = $("#pomo_name").val();
			pomo_id = $("#pomo_id").val();
			$.ajax({
			    url: "{{ url('pomo') }}/"+pomo_id,
			    type: 'POST',
			    data: {"name":pomo_name,"_token":"{{ csrf_token() }}"},
			    success: function(result) {
			    	result_arr = JSON.parse(result);
					if(result_arr.code != 9999){
						alert('处理失败，请稍后再试');
					} else {
						$("#pomo_name").val("");
						$("#pomo_id").val("");
						remain = result_arr.result.current_pomo_remain;
						status = result_arr.result.current_pomo_status;
						window.setInterval(function(){ShowCountDown( remain, "pomoBtn" );}, interval); 
						$("#recordPomo").css("display", "none");
						$("#pomoBtn").css("display", "block");
						
						$str = '<li id="pomo'+result_arr.result.id+'">';
						$str += '<span class="time">';
						$str += (new Date(result_arr.result.active_pomo.created_at)).format("hh:mm") +' - '+ (new Date(result_arr.result.active_pomo.updated_at)).format("hh:mm");
						$str += '</span>';
						$str += '<p>';
						$str += '<a href="/notes?add_content=%23记录番茄%23"'+encodeURIComponent(result_arr.result.active_pomo.name)+'&task_id='+result_arr.result.active_pomo.id+'" target="_blank">[记录]</a>';
						$str += result_arr.result.active_pomo.name;
						$str += '</p>';
						$str += '</li>';
						$("#pomos").prepend($str);
					}
			    }
			});
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
					<a class="btn btn-outline-info btn-shadow btn-block" href="javascript:void(0)"  @if($current_pomo_status ==3) style="display: none" @endif role="button" id="pomoBtn">开始一个新的番茄吧!</a> 

					<div class="form-group" @if($current_pomo_status !=3) style="display: none" @endif id="recordPomo">
						<div class="col-md-12">
							<input type="text" name="name" id="pomo_name"
								class="form-control" value="" placeholder="记录刚完成的番茄内容？点击任务名快速添加">
							<a href="javascript:void(0)" onclick="discard()">x</a>
						</div>
					</div>
					
					<input type="hidden" value="{{ $active_pomo->id }}" id="pomo_id">

					<hr width=100% size=1 color=#bbbcbc
						style="FILTER: alpha(opacity = 100, finishopacity = 0)">

					<div class = "head">
						<datetime class="time">{{ date('m月d日') }}</datetime>
						<div class="number">完成了 <span id="pomoCount">0</span> 个番茄</div>
					</div>
					
					<ul id="pomos" class="recent-list">
					
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
						<a href="{{'tasks'}}">[待办列表]</a> <a href="javascript:void(0)"
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
						
					<div class = "head">
						<datetime class="time"></datetime>
						<div class="number">共 <span id="taskCount">0</span> 待办任务</div>
					</div>
						
					<ul id="tasks" class="recent-list">
					
					</ul>
				</div>
			</div>

		</div>
	</div>
</div>
@endsection
