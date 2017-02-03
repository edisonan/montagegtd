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
			notify("您已经完成了一个小目标，快来记录一下吧~");
		}
		
		if(remain < 0){
			document.getElementById('divdown').style.display = "none";
			document.getElementById('formdiv1').style.display = "block";
			document.getElementById('formdiv2').style.display = "block";
			return false;
		}

		var minute_label = (minute >= 10)?minute:"0"+minute ;
		var second_label = (second >= 10)?second:"0"+second ;
		
		cc.innerHTML = "#Remain# " + minute_label +":"+ second_label; 
	}

	function notify(message)
	{
		if (Notification.permission !== "granted")
		    Notification.requestPermission();
		  else {
		    var notification = new Notification('Notification title', {
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
	}
	
	if(status == 2){
		window.setInterval(function(){ShowCountDown( remain, "divdown" );}, interval); 
	}

</script> 

@section('content')
    <div class="container">
    
    
    	<div class="col-sm-offset-2 col-sm-8">
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
                    	<a href="{{'pomos'}}">history</a>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if($runing_pomo_status == 2)
                    	<a class="btn btn-lg btn-primary btn-shadow btn-block" href="#" role="button" id = "divdown" onclick="discard()" ></a>
                    	<!-- 
                    	<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    	 -->
                    @elseif($runing_pomo_status == 1)
                   		 <a class="btn btn-lg btn-primary btn-shadow btn-block" href="{{url('pomos/start')}}" role="button" > 开始一个新的番茄吧! </a>
                    @endif
                    
                    <!-- New Task Form -->
                    <form action="{{ url('pomo') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Pomo Name -->
                        <div class="form-group" @if($runing_pomo_status != 3) style="display:none" @endif id="formdiv1">
                        	<!--  
                            <label for="pomo-name" class="col-sm-3 control-label">此番茄做了什么？</label>
                        	-->

                            <div class="col-sm-9">
                                <input type="text" name="name" id="pomo-name" class="form-control" value="{{ old('pomo') }}" placeholder="此番茄做了什么？">
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-btn fa-plus"></i>记录一下
                                </button>
                                <a href="#" onclick="discard()">放弃?</a>
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
                    	新的待办事项<a href="{{'tasks'}}">history</a>
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

                            <div class="col-sm-6">
                                <input type="text" name="name" id="task-name" class="form-control" value="{{ old('task') }}"><a href="#" onclick="displayATHiddenDiv()">高级选项</a>
                            </div>
                        </div>
                        
                        <div class="form-group" id="task_form_div1" style="display: none;">
                            <label for="task-name" class="col-sm-3 control-label">优先级</label>

                            <div class="col-sm-6">
                            	<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio1" value="1" checked> I不重要不紧急
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio2" value="2"> II不重要紧急
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio3" value="3"> III重要不紧急
								</label>
								<label class="radio-inline">
								  <input type="radio" name="priority" id="inlineRadio4" value="4"> IV重要紧急 
								</label>
								<!-- 
                                <input type="text" name="priority" id="task-priority" class="form-control" value="{{ old('task') }}">
								 -->
                            </div>
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div2" style="display: none;">
                            <label for="task-name" class="col-sm-3 control-label">提醒时间</label>

                            <div class="col-sm-6">
                                <input type="text" name="remindtime" id="task-remindtime" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})">
                            </div>
                        </div>
                        
                        <div class="form-group" "form-group" id="task_form_div3" style="display: none;">
                            <label for="task-name" class="col-sm-3 control-label">截止日期</label>

                            <div class="col-sm-6">
                                <input type="text" name="deadline" id="task-deadline" class="form-control" value="{{ old('task') }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})">
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
                                    <tr>
                                        <td class="table-text"  width="80%"><div>{{ $task->name }}</div></td>

                                        <!-- Task Delete Button -->
                                        <td  width="10%"  align='right'>
                                            <form action="{{url('task/' . $task->id)}}" method="POST" class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
												<input type="hidden" name="type" value="finish"/> 
                                                <button type="submit" id="delete-task-{{ $task->id }}" class="btn btn-success">
                                                    <i class="glyphicon glyphicon-ok"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td  width="10%"  align='right'>
                                            <form action="{{url('task/' . $task->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-task-{{ $task->id }}" class="btn btn-danger">
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
    </div>
@endsection
