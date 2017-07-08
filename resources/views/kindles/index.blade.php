@extends('layouts.app')

@section('content')
<script type="text/javascript">
$(document).ready(function () {

	$("#check_url").click(function(){
		
	});
});
</script>
    <div class="container">
    
    	<div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	配置说明
                </div>

                <div class="panel-body">
                	<p>
					1、中亚用户
					</p>
					
					<p>
					2、美亚用户
					</p>
					
					<p>
					3、设置推送邮箱，添加到信任列表
					</p>
					
					<p>
					4、发送测试  
					<?php ?>             
					</p>
                </div>
            </div>

        </div>
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	设置您的亚马逊邮箱
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('setting/'.$setting->id) }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}
                        
                        <div class="form-group" id="task_form_div1" >
                            <label for="task-name" class="col-sm-3 control-label">Kindle订阅地址</label>
                            
                            <div class="col-sm-8">
                            	<input type="text" name="kindle_email" id="kindle_email" class="form-control" value="{{ $setting->kindle_email }}">
                            </div>
							
                        </div>
                        
                        <div class="form-group" id="task_form_div1" >
                            <label for="task-name" class="col-sm-3 control-label">是否开启推送</label>
                            
							<label class="radio-inline">
								  <input type="radio" name="is_start_kindle" id="inlineRadio1" value="0" {{ empty($setting->is_start_kindle) ?'checked':'' }}><span>不开启</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="is_start_kindle" id="inlineRadio2" value="1" {{ $setting->is_start_kindle == 1 ?'checked':'' }}><span>开启</span>
								</label>
                        </div>
                        
                        <div class="form-group" id="task_form_div1" >
                            <label for="task-name" class="col-sm-3 control-label">是否带图推送</label>
                            
							<label class="radio-inline">
								  <input type="radio" name="with_image_push" id="inlineRadio1" value="0" {{ empty($setting->is_start_kindle) ?'checked':'' }}><span>不开启</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="with_image_push" id="inlineRadio2" value="1" {{ $setting->is_start_kindle == 1 ?'checked':'' }}><span>开启</span>
								</label>
                        </div>
                        
                        <input type="hidden" name="page_info" value="kindle_page">

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
