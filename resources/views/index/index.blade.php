@extends('layouts.app')
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>
<script language="javascript" type="text/javascript"> 

	document.addEventListener('DOMContentLoaded', function () {
	  if (!Notification) {
	    alert('Desktop notifications not available in your browser. Try Chromium.'); 
	    return;
	  }

	  if (Notification.permission !== "granted")
	    Notification.requestPermission();
	});
	
	var interval = 1000; 
	var remain = {{ $runing_pomo_remain }};
	var status = {{ $runing_pomo_status }};

	function ShowCountDown(leftsecond, divname) 
	{ 
		var minute=Math.floor(leftsecond/60); 
		var second=Math.floor(leftsecond - minute * 60); 
		
		var cc = document.getElementById(divname); 
		
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
				document.getElementById('divdown').style.display = "none";
				document.getElementById('formdiv1').style.display = "";
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

	function notify(message)
	{
		if (Notification.permission !== "granted")
		    Notification.requestPermission();
		  else {
		    var notification = new Notification('蒙太奇-专业GTD,剪辑你自己的生活', {
		      icon: 'https://congcong.us/favicon.ico',
		      body: message,
		    });

		    notification.onclick = function () {
		      window.open("{{'/index'}}");      
		    };
		  }
	}

	function discard(){
		if (confirm("确认要放弃咩？")) {
			location.href = '{{ url("pomos/discard/") }}/{{ $active_pomo->id }}';
		}
	}
	
	function displayATHiddenDiv(){
		document.getElementById('task_form_div1').style.display = "";
		document.getElementById('task_form_div2').style.display = "";
		document.getElementById('task_form_div3').style.display = "";
		document.getElementById('task_form_div4').style.display = "";
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
		window.setInterval(function(){ShowCountDown( remain, "divdown" );}, interval); 
	}

	window.onload=function(){  
		
	} 
</script> 

@section('content')

<script src="{{'/js/bootstro.min.js'}}"></script>
<link href="{{'/css/bootstro.min.css'}}" rel="stylesheet">

<script type="text/javascript">
$(document).ready(function () {

	$("#task_more").click(function(){
		$('#task_form_div1').toggle();
		$('#task_form_div2').toggle();
		$('#task_form_div3').toggle();
		$('#task_form_div4').toggle();
	})
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

	$(".task_tr").hover(function(){
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
});
</script>
    <div class="container">
    	@include('common.success')
    	<div class="row">
    	<div 
    		class=" col-md-5 bootstro" 
    		data-bootstro-step="0"
    		data-bootstro-placement="bottom"
    		data-bootstro-nextButtonText="下一步"
    		data-bootstro-content="使用番茄工作法，选择一个待完成的任务，将番茄时间设为25分钟，专注工作，中途不允许做任何与该任务无关的事，直到番茄时钟响起，然后在纸上画一个X短暂休息一下（5分钟就行），每4个番茄时段多休息一会儿。"
    		data-bootstro-finishButton="返回网站，开启高效生活~"
    	>
            <div class="card card-default">
                <div class="card-header">
                    	开蕃走起
                    	<div style="float:right">
                    		<a href="{{'pomos'}}">[历史]</a>
                    		<a href="{{'things'}}">[记事]</a>
                    		<a href="javascript:void(0)" class="new_user_guide">[?]</a>
                    	</div>
                </div>

                <div class="card-body">
                    @if($runing_pomo_status == 2 || $runing_pomo_status == 4 )
                    	<a class="btn btn-lg btn-outline-info btn-shadow btn-block" href="javascript:void(0)" role="button" id = "divdown" onclick="discard()" ></a>
                    @elseif($runing_pomo_status == 1)
                   		 <a class="btn btn-lg btn-outline-info btn-shadow btn-block" href="{{url('pomos/start')}}" role="button" > 开始一个新的番茄吧! </a>
                    @endif
                    
                    <form action='{{ url("pomo") }}/{{ $active_pomo->id }}' method="POST" class="form-horizontal">
                        {{ csrf_field() }}
                        <!-- Pomo Name -->
                        <div class="form-group row" @if($runing_pomo_status != 3) style="display:none" @endif id="formdiv1">
                            <div class="col-md-9"  style="display: -webkit-inline-box;width: 75%;">
                                <input type="text" name="name" id="pomo_name" class="form-control" value="{{ old('pomo') }}" placeholder="此做了什么？点击任务名快速添加">
                            </div>
                            <div class="col-md-3" style="display: -webkit-inline-box;width: 25%;">
                                <button type="submit" class="btn btn-success" >记录</button>
                                <a href="javascript:void(0)" onclick="discard()" title="放弃此番茄"><small>x?</small></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card card-default">
                <div class="card-header">
                	今日完成番茄
                </div>

                <div class="card-body">
                	空空如也!
                </div>
            </div>

        </div>
    
    
    
        <div 
        	class=" col-md-7 bootstro" 
        	data-bootstro-step="1"
    		data-bootstro-placement="bottom"
    		data-bootstro-prevButtonText="上一步"
    		data-bootstro-content="在这里创建待办事项，高级功能里面可以增加提醒、优先级设定等功能"
    		data-bootstro-finishButton="返回网站，开启高效生活~"
    		
        >
            <div class="card card-default">
                <div class="card-header">
                    	新的待办事项
                    	<div style="float:right">
                    		<a href="{{'tasks'}}">[已完成待办]</a>
                    		<a href="javascript:void(0)" class="new_user_guide">[?]</a>
                    	</div>
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('task') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group row">
                            <label for="task-name" class="col-md-3 control-label">待办内容</label>

                            <div class="col-md-8">
	                                <input type="text" name="name" id="task-name" class="form-control" value="{{ old('task') }}" style="display: -webkit-inline-box;width: 85%;">
								    <a href="javascript:void(0)" id="task_more"><small>高级</small></a>
                            </div>
                        </div>
                        
                        <div class="form-group row" id="task_form_div1" style="display:none">
                            <label for="task-name" class="col-md-3 control-label">优先级</label>
							
                            <div class="col-md-8">
                            	<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio1" value="1" title="不重要不紧急" checked><span title="不重要不紧急"><small>☆</small></span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio2" value="2" title="不重要紧急"><span title="不重要紧急"><small>☆☆</small></span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio3" value="3" title="重要不紧急"><span title="重要不紧急"><small>☆☆☆</small></span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio4" value="4" title="重要紧急 "><span title="重要紧急 "><small>☆☆☆☆</small></span>
								</label>
                            </div>
                        </div>
                        
                        <div class="form-group row" "form-group row" id="task_form_div2" style="display: none;">
                            <label for="task-name" class="col-md-3 control-label">提醒时间</label>

                            <div class="col-md-6">
                                <input type="text" name="remindtime" id="task-remindtime" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        
                        <div class="form-group row" "form-group row" id="task_form_div3" style="display: none;">
                            <label for="task-name" class="col-md-3 control-label">截止日期</label>

                            <div class="col-md-6">
                                <input type="text" name="deadline" id="task-deadline" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        
                        <div class="form-group row" "form-group row" id="task_form_div4" style="display: none;">
                            <label for="task-name" class="col-md-3 control-label">所属技能</label>

                            <div class="col-md-6">
	                            <select class="form-control" name="goal_id" style="display: -webkit-inline-box;width: 85%;">
	                            	<option checked></option>
		                              @foreach ($goals as $goal)
									  	<option value="{{ $goal->id }}">{{ $goal->name }}</option>
									  @endforeach
								</select>
								<a href="{{ url('goals') }}" ><small>新建</small></a>
                            </div>
                        </div>
                        
                        <!-- Add Task Button -->
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-outline-info">
                                    <i class="fa fa-btn fa-plus"></i>添加上去！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                    @if (count($tasks) > 0)
                    <table class="table table-striped task-table table-hover">
                            <thead>
                                <th>待办事项</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr 
	                                    @if($task->priority == 4) 
	                                    	class="danger task_tr" title="重要紧急事项" 
	                                    @elseif($task->priority == 3) 
	                                    	class="warning task_tr" title="重要不紧急事项"  
	                                    @elseif($task->priority == 2) 
	                                    	class="info task_tr" title="不重要紧急事项" 
	                                    @else
	                                    	class="task_tr" title="不重要不紧急事项" 
	                                    @endif
	                                    
	                                    id="{{$task->id}}"
                                    >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre" <?php if(!empty($task->deadline) && strtotime($task->deadline) < time()) echo 'style="color:red"';?>>
                                        	
											<a href="javascript:void(0)" class="finish_task" task_type="finish" task_value="{{ $task->id }}" task_token="{{ csrf_token() }}"><img alt=""     style="width: 20px;" src="/img/icon/unfinished_success.png"></a>                                        	
											
											@if(!empty($task->goal->name))
                                        	<a href="#{{$task->goal->id}}">[{{ $task->goal->name }}]</a>
                                        	@endif
                                        	<span class="task_content">{{ $task->name }}</span>
                                        	</pre>
                                        </td>
                                        

                                        <!-- Task Delete Button -->
                                        <td align='right'>
                                        	<div>
                                        		<a href="javascript:void(0)" class="delete_task" task_type="delete" task_value="{{ $task->id }}" task_token="{{ csrf_token() }}" style="display: none"><img alt=""     style="width: 15px;" src="/img/icon/delete.png"></a> 
                                        		<a href="javascript:void(0)" class="top_task" task_value="{{ $task->id }}" task_is_top="{{ $task->is_top }}" task_token="{{ csrf_token() }}" @if($task->is_top !=1) style="display: none"  @endif><img alt=""     style="width: 15px;" src="/img/icon/pin.png"></a> 
                                        	</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
        </div>
    </div>
@endsection
