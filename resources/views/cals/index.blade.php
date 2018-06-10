@extends('layouts.app')

@section('content')
<script type="text/javascript">
$(document).ready(function () {

	$("#check_url").click(function(){
		
	});
});
</script>
    <div class="container">
    
    	<div class="col-md-12">
    		@include('common.success')
            <div class="card">
                <div class="card-header">
                    	配置说明
                    	<div style="float:right">
                    		<a href="{{'/'}}">[返回]</a>
                    	</div>
                </div>

                <div class="card-body">
                	<div style="float:left">
                		<img alt=""  class="" src="/img/kindle.jpg" width="150px">
                	</div>
                	<div>
                		<p>
						步骤:
						</p>
						
	                	<p>
						1、中亚用户,点击<a href="https://www.amazon.cn/gp/digital/fiona/manage?ie=UTF8&ref_=ya_myk&#manageDevices">这里</a>配置,<a href="/img/kindle_amazon_cn.jpg" target="_blank">图示1</a> <a href="/img/kindle_amazon_cn2.jpg" target="_blank">图示2</a>
						
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
						</p>
                	</div>
                </div>
            </div>

        </div>
    
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    	个人日历地址
                </div>

                <div class="card-body">

                    <a href="{{$person_cal_url}}">{{$person_cal_url}}</a>
                    
                </div>
            </div>
        </div>
        
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    	公共日历地址
                </div>

                <div class="card-body">
					@foreach ($cals as $cal)
							<div class="col-md-12" >
								<span class = "col-md-3">
									{{$cal['theme']}}
								</span>
								<span class = "col-md-9">
									 <a href="{{$cal['url']}}">{{$cal['url']}}</a>
								</span>
							</div>
					@endforeach
                    
                    
                </div>
            </div>
        </div>
    </div>
@endsection
