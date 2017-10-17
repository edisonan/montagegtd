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
	document.getElementById('add_note_form').submit();
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

	$(".delete_note").click(function(){
		note_value = $(this).attr("note_value");
		note_token = $(this).attr("note_token");
		note_type = $(this).attr("note_type");

		if (note_type == 'delete' && !confirm("确认要删除此笔记咩？")) {
			return false;
		}
		
		$.ajax({
		    url: "{{ url('note') }}"+"/"+note_value,
		    type: 'DELETE',
		    data: {type:note_type,_token:note_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+note_value).remove();
				}
		    }
		});
	});
});
</script>
    <div class="container">
        <div class="col-md-offset-0 col-md-12">
        	@include('common.success')
            <div class="card">
                <div class="card-header">
                    	新的笔记
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New note Form -->
                    <form action="{{ url('note') }}"   method="POST" class="form-horizontal" id="add_note_form">
                        {{ csrf_field() }}

                        <!-- note Name -->
                        <div class="form-group row">
                            <label for="note-name" class="col-md-2 control-label">你在想什么呢</label>

                            <div class="col-md-10" >
                            	<textarea class="form-control" rows="4"  name="name" id="note-name" >{{ $add_content }}</textarea>
                            	
                            	<button id="start" class="ui-btn ui-btn-primary" disabled title="请尽量使用https请求访问本站，支持360、chrome、safari、firefox等高版本浏览器，支持ios11，请您保证有录音设备，更换浏览器后重试">录音</button>
						        <button id="stop" class="ui-btn ui-btn-primary" disabled title="请尽量使用https请求访问本站，支持360、chrome、safari、firefox等高版本浏览器，支持ios11，请您保证有录音设备，更换浏览器后重试">停止</button>
						        <div id="audio-container"></div>
						        
						        <input type="hidden" name="fname" id="fname" />
						        @if(!empty($add_image))
						        <input type="hidden" name="add_image" id="add_image"  value="{{$add_image}}"/>
						        <span>预览：</span><img  height="150px" alt="" src="{{$add_image}}">
						        @endif
                            	
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
                            	<input type="hidden" name="status" value="1" id="status_id">
                            	
                                <button type="button" class="btn btn-default" onclick="submitProcess(1)">
                                    <i class="fa fa-btn fa-plus"></i>私密发布
                                </button>
                            	
                                <button type="button" class="btn btn-primary" onclick="submitProcess(2)">
                                    <i class="fa fa-btn fa-plus"></i>公开发布
                                </button>
                                
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current notes -->
            @if (count($notes) > 0)
                <div class="card">
                    <div class="card-header">
                       	 大家在分享什么
                    </div>
				</div>
                    	@foreach ($notes as $note)
							<div class="card" style="margin-bottom:10px">
								<div class="card-block">
								  <h4 class="card-title"><img style="width:30px;margin:5px" src="https://gravatar.css.network/avatar/{{ md5(strtolower(trim($note->user->email))) }}?s=40&d=identicon&r=PG&f=1" class="img-fluid rounded" alt="Responsive image rounded" style="width:50px;"> {{ $note->user->name }}</h4>
								  <p class="card-text"><small class="text-muted" style="padding-left: 10px;"><?php echo date('Y年m月d日 H:i',strtotime($note->created_at));?></small></p>
								  <div class="card-text post-text">
								    @if($note->status != 2)
									<img alt=""     style="width: 15px;    margin-right: 10px;" src="/img/icon/security.png">
									@endif
									
									@if(!empty($note->record_path) && ($note->user_id == Auth::user()->id  || $note->status == 2))
									<audio src="{{ url('note/getRecord') }}/{{ $note->id }}" controls=""></audio>
									@endif
									
									@if(!empty($note->image_path) && ($note->user_id == Auth::user()->id  || $note->status == 2))
									<a href="{{ $note->image_path }}" title="点击查看原图" target="_blank">
										<image height="150px" src="{{ $note->image_path }}"/>
									</a>
									@endif
								  <?php echo App\Http\Utils\CommonUtil::formatContentHtml($note->name);?>
								  </div>
								  <p class="card-text text-right post-text">
								    @if($note->user_id == Auth::user()->id )
											<a href="javascript:void(0)" class="delete_note" note_type="delete" note_value="{{ $note->id }}"  note_token="{{ csrf_token() }}" style="cursor:pointer;">
											<img alt=""     style="width: 15px;" src="/img/icon/delete.png">
											</a> 
                                            @else
                                            <a href="javascript:void(0)" class="like_note" note_type="like" note_value="{{ $note->id }}" note_token="{{ csrf_token() }}" style="cursor:pointer;">
											<img alt=""     style="width: 15px;" src="/img/icon/like.png">
											</a> 
                                    @endif
								  </p>
								</div>
							  </div>
					  @endforeach
						{!! $notes->links() !!}
            @endif
        </div>
    </div>
@endsection
