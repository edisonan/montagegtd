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
				notify("您已经完成了一个小目标，快来记录一下吧~");
			} else if(status == 4){
				notify("休息完成，快来开始下一个番茄吧~");
			}
		}
		
		if(remain < 0){
			if(status == 2){
				document.getElementById('divdown').style.display = "none";
				document.getElementById('formdiv1').style.display = "block";
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
		    var notification = new Notification('小目标', {
		      icon: 'http://congcong.us/favicon.ico',
		      body: message,
		    });

		    notification.onclick = function () {
		      window.open("{{'/index'}}");      
		    };
		  }
	}

	function discard(){
		if (confirm("确认要放弃咩？")) {
			location.href = '{{'pomos/discard'}}';
		}
	}
	
	function displayATHiddenDiv(){
		document.getElementById('task_form_div1').style.display = "block";
		document.getElementById('task_form_div2').style.display = "block";
		document.getElementById('task_form_div3').style.display = "block";
		document.getElementById('task_form_div4').style.display = "block";
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
// 		var allElements = document.getElementsByTagName('*'); 
// 		for (var i=0; i< allElements.length; i++ ) 
// 		{ 
// 			if (allElements[i].className == "preprepre" ) { 
// 				var html = allElements[i].textContent;
				
// 				var reg = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
// 				html = html.replace(reg, "<a href='$1$2'>$1$2</a>");
				
// // 				reg = /(#(\w+|[\u4e00-\u9fa5]+)#)/g;
// // 				html = html.replace(reg, "<a href='javascript:void(0)'>$1</a>");
				
// 				allElements[i].innerHTML = html;
// 			} 
// 		}
	} 
</script> 

@section('content')
    <div class="container">
    
    
    	<div class="col-sm-offset-2 col-sm-8">
    		
    		@if($tip_type != 0)
	    		<div class="alert alert-success alert-dismissible" role="alert">
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="clearTips(@if($tip_type == 2) 'morning_tip' @elseif($tip_type == 3) 'afternoon_tip'  @endif)"><span aria-hidden="true">&times;</span></button>
					  <?php echo $tip_message;?>
				</div>
    		@endif
    		
            <div class="panel panel-default">
                <div class="panel-heading">
                    	@if($runing_pomo_status != 3)
                    		@if (count($pomos) > 0)
                    		开蕃走起
                    		@else
                    			@if (count($tasks) > 0)
                    			现在还有{{ count($tasks) }}项任务哦，赶紧开蕃走起吧
                    			@else
                    			开蕃走起
                    			@endif
                    		@endif
                    	@else
                    	快来记录一下这个番茄吧
                    	@endif
                    	<div style="float:right">
                    		<a href="{{'pomos'}}">[已完成番茄]</a>
                    	</div>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if($runing_pomo_status == 2 || $runing_pomo_status == 4 )
                    	<a class="btn btn-lg btn-default btn-shadow btn-block" href="javascript:void(0)" role="button" id = "divdown" onclick="discard()" ></a>
                    	<!-- 
                    	<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    	 -->
                    @elseif($runing_pomo_status == 1)
                   		 <a class="btn btn-lg btn-default btn-shadow btn-block" href="{{url('pomos/start')}}" role="button" > 开始一个新的番茄吧! </a>
                    @endif
                    
                    <!-- New Task Form -->
                    <form action="{{ url('pomo') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Pomo Name -->
                        <div class="form-group" @if($runing_pomo_status != 3) style="display:none" @endif id="formdiv1">
                            <div class="col-sm-9">
                                <input type="text" name="name" id="pomo-name" class="form-control" value="{{ old('pomo') }}" placeholder="此番茄做了什么？">
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-btn fa-plus"></i>记录一下
                                </button>
                                <a href="javascript:void(0)" onclick="discard()">放弃?</a>
                            </div>
                        </div>
                    </form>
                    
                    @if (count($pomos) > 0)
                    <br/><br/>
                    <table class="table table-striped task-table">
                            <thead>
                                <th>今天完成的番茄</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($pomos as $pomo)
                                    <tr class="active">
                                        <td class="table-text" width="80%"><div>{{ $pomo->name }}</div></td>

                                        <!-- Task Delete Button -->
                                        <td width="20%"  align='right'>
                                            <form action="{{url('pomo/' . $pomo->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-pomo-{{ $pomo->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                     @endif
                </div>
            </div>

        </div>
    
    
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的待办事项
                    	<div style="float:right">
                    		<a href="{{'tasks'}}">[已完成待办]</a>
                    	</div>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('task') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group">
                            <label for="task-name" class="col-sm-3 control-label">待办内容</label>

                            <div class="col-sm-8">
	                                <input type="text" name="name" id="task-name" class="form-control" value="{{ old('task') }}">
								    <a href="javascript:void(0)" onclick="displayATHiddenDiv()"><small>高级选项</small></a>
                            </div>
                        </div>
                        
                        <div class="form-group" id="task_form_div1" style="">
                            <label for="task-name" class="col-sm-3 control-label">优先级</label>
							
                            <div class="col-sm-6">
                            	<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio1" value="1" title="不重要不紧急" checked><span title="不重要不紧急">☆</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio2" value="2" title="不重要紧急"><span title="不重要紧急">☆☆</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio3" value="3" title="重要不紧急"><span title="重要不紧急">☆☆☆</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio4" value="4" title="重要紧急 "><span title="重要紧急 ">☆☆☆☆</span>
								</label>
                            </div>
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div2" style="display: none;">
                            <label for="task-name" class="col-sm-3 control-label">提醒时间</label>

                            <div class="col-sm-6">
                                <input type="text" name="remindtime" id="task-remindtime" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div3" style="display: none;">
                            <label for="task-name" class="col-sm-3 control-label">截止日期</label>

                            <div class="col-sm-6">
                                <input type="text" name="deadline" id="task-deadline" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        
                        <!-- Add Task Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>添加上去！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                    @if (count($tasks) > 0)
                    <table class="table table-striped task-table">
                            <thead>
                                <th>待办事项</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr 
	                                    @if($task->priority == 4) 
	                                    	class="danger" title="重要紧急事项" 
	                                    @elseif($task->priority == 3) 
	                                    	class="warning" title="重要不紧急事项"  
	                                    @elseif($task->priority == 2) 
	                                    	class="info" title="不重要紧急事项" 
	                                    @else
	                                    	title="不重要不紧急事项" 
	                                    @endif
                                    >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre" <?php if(!empty($task->deadline) && strtotime($task->deadline) < time()) echo 'style="color:red"';?>>
                                        	
                                        	@if($task->priority == 4) 
		                                    	<span style="color:#f0ad4e">▌</span>
		                                    @elseif($task->priority == 3) 
		                                    	<span style="color:#337ab7">▎</span>
		                                    @elseif($task->priority == 2) 
		                                    	<span style="color:#5bc0de">▏</span>
		                                    @else
		                                    	<span style="color:#5cb85c">|</span>
		                                    @endif
                                        	
                                        	{{ $task->name }}
                                        	</pre>
                                        </td>

                                        <!-- Task Delete Button -->
                                        <td  width="1"  align='right'>
                                            <form action="{{url('task/' . $task->id)}}" method="POST" class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
												<input type="hidden" name="type" value="finish"/> 
                                                <button type="submit" id="delete-task-{{ $task->id }}" class="btn btn-link"  title="已完成">
                                                	<!-- 
                                                    <i class="glyphicon glyphicon-ok"></i>
                                                	 -->
                                                	 <span style="color:green">Done √</span>
                                                    
                                                </button>
                                            </form>
                                        </td>
                                        <td  width="1"  align='right'>
                                            <form action="{{url('task/' . $task->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-task-{{ $task->id }}" class="btn btn-link" title="删除!!!">
                                                	<!-- 
                                                    <i class="fa fa-btn fa-trash"></i>
                                                	 -->
                                                	 <span style="color:red">×</span>
                                                </button>
                                            </form>
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
@endsection
