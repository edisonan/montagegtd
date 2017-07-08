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
					1、中亚用户,点击<a href="https://www.amazon.cn/gp/digital/fiona/manage?ie=UTF8&ref_=ya_myk&#manageDevices">这里</a>配置
					<img alt="" src="/img/kindle_amazon_cn.jpg">
					</p>
					
					<p>
					2、美亚用户 点击<a href="https://www.amazon.com/mn/dcw/myx.html#/home/devices/1">这里</a>配置
					<!-- 
					<img alt="" src="">
					 -->
					</p>
					
					<p>
					3、添加 noreply@congcong.us 到信任列表，根据亚马逊生成的邮箱在页面下面设置
					</p>
					
					<p>
					4、点击<a href="{{url('kindle/test')}}">测试链接</a>发送请求,查看kindle是否收到测试文件
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
								  <input type="radio" name="with_image_push" id="inlineRadio1" value="0" {{ empty($setting->with_image_push) ?'checked':'' }}><span>不开启</span>
								</label>
								<label class="radio-inline">
								  <input type="radio" name="with_image_push" id="inlineRadio2" value="1" {{ $setting->with_image_push == 1 ?'checked':'' }}><span>开启</span>
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
