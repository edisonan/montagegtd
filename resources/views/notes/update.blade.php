@extends('layouts.app')

<style>
            audio { display: block; margin-bottom: 10px; }
            #audio-container { padding: 20px 0; }
            .ui-btn { display: inline-block; padding: 5px 20px; font-size: 14px; line-height: 1.428571429; box-sizing:content-box; text-align: center; border: 1px solid #e8e8e8; border-radius: 3px; color: #555; background-color: #fff; border-color: #e8e8e8; white-space: nowrap; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
            .ui-btn:hover, .ui-btn.hover { color: #333; text-decoration: none; background-color: #f8f8f8; border:1px solid #ddd; }
            .ui-btn:focus, .ui-btn:active { color: #333; outline: 0; }
            .ui-btn.disabled, .ui-btn.disabled:hover, .ui-btn.disabled:active, .ui-btn[disabled], .ui-btn[disabled]:hover, .ui-state-disabled .ui-btn { cursor: not-allowed; background-color: #eee; border-color: #eee; color: #aaa; }
            .ui-btn-primary { color: #fff;  background-color: #39b54a;  border-color: #39b54a; }
            .ui-btn-primary:hover, .ui-btn-primary.hover { color: #fff; background-color: #16a329; border-color: #16a329; }
            .ui-btn-primary:focus, .ui-btn-primary:active { color: #fff; }
            .ui-btn-primary.disabled:focus{ color: #aaa; }
			
			.post-text{
				padding: 10px;
				font-size: 18px;
			  }
</style>

<script>
function submitProcess($status){
	document.getElementById('status_id').value = $status;
	document.getElementById('update_note_form').submit();
}

function addContent($content){
	note_name = document.getElementById('note-name');
	if($content == 'code'){
		note_name.value = note_name.value + "\n<code>\n</code>";
	}else {
		note_name.value = note_name.value + $content;
	}
}

</script>

<script src="js/recorder/recorder.js"></script>

<script>
	window.onload = function(){
		
		var start = document.querySelector('#start');
		var stop = document.querySelector('#stop');
		var container = document.querySelector('#audio-container');
		var recorder = new Recorder({
			sampleRate: 44100, //采样频率，默认为44100Hz(标准MP3采样率)
			bitRate: 128, //比特率，默认为128kbps(标准MP3质量)
			success: function(){ //成功回调函数
				start.disabled = false;
			},
			error: function(msg){ //失败回调函数
				start.value ='录音(该浏览器暂不支持,请使用chrome/360/firefox等)';
			},
			fix: function(msg){ //不支持H5录音回调函数
				start.value = '录音(该浏览器暂不支持,请使用chrome/360/firefox等)';
			}
		});

		//开始录音
		//recorder.start();

		//停止录音
		//recorder.stop();

		//获取MP3编码的Blob格式音频文件
		//recorder.getBlob(function(blob){ 获取成功回调函数，blob即为音频文件
		//    ...
		//},function(msg){ 获取失败回调函数，msg为错误信息
		//    ...
		//});

		//getUserMedia() no longer works on insecure origins. To use this feature, you should consider switching your application to a secure origin, such as HTTPS.

		start.addEventListener('click',function(){
			this.disabled = true;
			stop.disabled = false;
			var audio = document.querySelectorAll('audio');
			for(var i = 0; i < audio.length; i++){
				if(!audio[i].paused){
					audio[i].pause();
				}
			}
			recorder.start();
		});
		stop.addEventListener('click',function(){
			this.disabled = true;
			start.disabled = false;
			recorder.stop();
			recorder.getBlob(function(blob){
				if($("#note-name").val().indexOf("#分享语音#")==-1){
					$("#note-name").val("#分享语音#");
				}
				
				var childs = container.childNodes; 
				for(var i = 0; i < childs.length; i++) { 
				  container.removeChild(childs[i]); 
				} 
				
				var audio = document.createElement('audio');
				audio.src = URL.createObjectURL(blob);
				audio.controls = true;
				container.appendChild(audio);

				//upload
				var fd = new FormData();
				fname = '{{ md5(date('YmdHis').rand(0,99)) }}';
				fd.append('fname', fname);
				fd.append('file', blob);
				fd.append('_token', "{{ csrf_token() }}");
				
				$.ajax({
				    type: 'POST',
				    url: '{{ url("notes/upload") }}',
				    data: fd,
				    processData: false,
				    contentType: false
				}).done(function(data) {
						data_arr = JSON.parse(data);
						if(data_arr.code == 9999){
							$("#fname").val(fname);
						}
				});
			});
		});
	};
</script>

@section('content')
<script type="text/javascript">
$(document).ready(function () {

});
</script>
    <div class="container">
        <div class=" col-md-12">
        	@include('common.success')
            <div class="card">
                <div class="card-header">
                    	修改笔记
                    	<div class="form-inline" style="float:right">
                    		<form action="{{url('notes')}}" method="get">
    			                <input type="text" name="keyword" class="" placeholder="搜索笔记" />
    			                <input type="submit" value="搜 索"/>
                    		</form>
			            </div>
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New note Form -->
                    <form action="{{ url('noteupdate/'.$note->id) }}"   method="POST" class="form-horizontal" id="update_note_form">
                        {{ csrf_field() }}

                        <!-- note Name -->
                        <div class="form-group row">
                            <label for="note-name" class="col-md-2 control-label">你在想什么呢</label>

                            <div class="col-md-10" >
                            	<textarea class="form-control" rows="4"  name="name" id="note-name" >{{ $note->name }}</textarea>
                            	
                            	<br/>
                            	<span>推荐话题:</span>
                            	<a href="javascript:void(0)" onclick="addContent('#每日小目标#')">#每日小目标#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#每日总结#')">#每日总结#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#读书笔记#')">#读书笔记#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#分享#')">#分享#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#碎碎念#')">#碎碎念#</a> 
                            	<a href="javascript:void(0)"  onclick='addContent("code")'>[代码片段]</a>
                            </div>
                        </div>

                        <!-- Add note Button -->
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                            	<input type="hidden" name="status" value="{{ $note->status }}" id="status_id">
                            	
                                @if($note->status == 1)
                                    <button type="button" class="btn btn-secondary" onclick="submitProcess(2)">
                                        <i class="fa fa-btn fa-plus"></i>公开发布
                                    </button>
                                    
                                    <button type="button" class="btn btn-primary" onclick="submitProcess(1)">
                                        <i class="fa fa-btn fa-plus"></i>私密发布
                                    </button>
                            	@else 
                                    <button type="button" class="btn btn-secondary" onclick="submitProcess(1)">
                                        <i class="fa fa-btn fa-plus"></i>私密发布
                                    </button>
                                	
                                    <button type="button" class="btn btn-primary" onclick="submitProcess(2)">
                                        <i class="fa fa-btn fa-plus"></i>公开发布
                                    </button>
                            	@endif
                                
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

