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

window.onload=function(){  
	var allElements = document.getElementsByTagName('*'); 
	for (var i=0; i< allElements.length; i++ ) 
	{ 
		console.log(allElements[i].className);
		if (allElements[i].className == "preprepre" ) { 
			var html = html_encode(allElements[i].textContent);
			
			var reg = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
			html = html.replace(reg, "<a href='$1$2'>$1$2</a>");
			
// 			reg = /(#(\w+|[\u4e00-\u9fa5]+)#)/g;
// 			html = html.replace(reg, "<a href='javascript:void(0)'>$1</a>");
			
			allElements[i].innerHTML = html;
		} 
	}
} 

function html_encode(str)   
{   
  var s = "";   
  if (str.length == 0) return "";   
  s = str.replace(/&/g, "&gt;");   
  s = s.replace(/</g, "&lt;");   
  s = s.replace(/>/g, "&gt;");   
  s = s.replace(/ /g, "&nbsp;");   
  s = s.replace(/\'/g, "&#39;");   
  s = s.replace(/\"/g, "&quot;");   
  s = s.replace(/\n/g, "<br>");   
  return s;   
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
				alert(msg);
			},
			fix: function(msg){ //不支持H5录音回调函数
				alert(msg);
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
				var childs = container.childNodes; 
				for(var i = 0; i < childs.length; i++) { 
				  alert(childs[i].nodeName); 
				  container.removeChild(childs[i]); 
				} 
				
				var audio = document.createElement('audio');
				audio.src = URL.createObjectURL(blob);
				audio.controls = true;
				container.appendChild(audio);

				var link = document.createElement("a");
			    link.innerHTML = fileName;
			    link.download = fileName;
			    link.href = URL.createObjectURL(blob);
			    container.appendChild(link);

				var record_file = document.querySelector('#record_file');
				record_file.val(URL.createObjectURL(blob));

				var record_file = document.querySelector('#record_file2');
				record_file.val(blob);
				
			});
		});
	};
</script>

@section('content')
    <div class="container">
        <div class="col-sm-offset-0 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的笔记
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New note Form -->
                    <form action="{{ url('note') }}"  enctype="multipart/form-data" method="POST" class="form-horizontal" id="add_note_form">
                        {{ csrf_field() }}

                        <!-- note Name -->
                        <div class="form-group">
                            <label for="note-name" class="col-sm-2 control-label">你在想什么呢</label>

                            <div class="col-sm-10">
                            	<textarea class="form-control" rows="4"  name="name" id="note-name" >{{ $add_content }}</textarea>
                            	
                            	<button id="start" class="ui-btn ui-btn-primary" disabled>录音</button>
						        <button id="stop" class="ui-btn ui-btn-primary" disabled>停止</button>
						        <div id="audio-container"></div>
						        
						        <input type="file" name="record_file" id="record_file" style="display: none"/>
                            	<input type="file" name="record_file2" id="record_file2" style="display: none"/>
                            	
                            	<br/>
                            	<span>推荐话题:</span>
                            	<a href="javascript:void(0)" onclick="addContent('#每日小目标#')">#每日小目标#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#每日总结#')">#每日总结#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#读书笔记#')">#读书笔记#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#分享#')">#分享#</a> 
                            	<a href="javascript:void(0)"  onclick="addContent('#碎碎念#')">#碎碎念#</a> 
                            	<a href="javascript:void(0)"  onclick='addContent("code")'>代码片段</a>
                            </div>
                        </div>

                        <!-- Add note Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
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
                <div class="panel panel-default">
                    <div class="panel-heading">
                       	 大家在分享什么
                    </div>

                    <div class="panel-body">
                    	@foreach ($notes as $note)
							<article class="post" style="padding: 0px;">
								<div class="post-head">
									<div class="post-meta" style="text-align: left">
										<div class="col-sm-offset-0 col-sm-1" style="width: 50px;padding-right: 0px;padding-left: 0px;">
											<img src="https://gravatar.css.network/avatar/{{ md5(strtolower(trim($note->user->email))) }}?s=40&d=identicon&r=PG&f=1" style="width:30px; height:30px;margin:5px;border-radius: 50%;" class="img-thumbnail avatar"></a>
										</div>
										<div class="col-sm-offset-0 col-sm-11">
											<span class="author">
												<a href="#" target="_blank">{{ $note->user->name }}</a>
											</span> 
											<br/>
											<time class="post-date" datetime="<?php echo $note->created_at;?>" title="<?php echo $note->created_at;?>"><?php echo date('Y年m月d日 H:i',strtotime($note->created_at));?></time>
											<br/>
										</div>
									</div>
								</div>
								<div class="post-content col-sm-offset-0 col-sm-12">
									<?php echo $note->name;?>
								</div>
								<div class="col-sm-offset-11 col-sm-1">
										@if($note->user_id == Auth::user()->id )
                                            <form action="{{url('note/' . $note->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-note-{{ $note->id }}" class="btn btn-link" title="删除!!!">
                                                	<span style="color:red">×</span>
                                                </button>
                                            </form>
                                            @else
                                            <form action="{{url('note/like/' . $note->id)}}" method="POST">
                                                {{ csrf_field() }}

                                                <button type="button" id="like-note-{{ $note->id }}" class="btn btn-info">
                                                    <i class="fa fa-btn fa-thumbs-o-up"></i>
                                                </button>
                                            </form>
                                            @endif
						  	</div>
								<footer class="post-footer clearfix"></footer>
							</article>
					  @endforeach
                    
                    </div>
                </div>
                 {!! $notes->links() !!}
            @endif
        </div>
    </div>
@endsection
