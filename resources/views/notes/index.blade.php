@extends('layouts.app')

<script>

function submitProcess($status){
	document.getElementById('status_id').value = $status;
	document.getElementById('add_note_form').submit();
}

function addContent($content){
	note_name = document.getElementById('note-name');
	note_name.value = note_name.value + $content;
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
                    <form action="{{ url('note') }}" method="POST" class="form-horizontal" id="add_note_form">
                        {{ csrf_field() }}

                        <!-- note Name -->
                        <div class="form-group">
                            <label for="note-name" class="col-sm-2 control-label">你在想什么呢</label>

                            <div class="col-sm-10">
                            	<textarea class="form-control" rows="4"  name="name" id="note-name" >{{ $add_content }}</textarea>
                            	<br/>
                            	<span>推荐话题:</span><a href="javascript:void(0)" onclick="addContent('#每日小目标#')">#每日小目标#</a> <a href="javascript:void(0)"  onclick="addContent('#每日总结#')">#每日总结#</a> <a href="javascript:void(0)"  onclick="addContent('#读书笔记#')">#读书笔记#</a> <a href="javascript:void(0)"  onclick="addContent('#分享#')">#分享#</a> <a href="javascript:void(0)"  onclick="addContent('#碎碎念#')">#碎碎念#</a>
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
                    	<div class="col-sm-offset-0 col-sm-12">
							  <div class="col-sm-offset-0 col-sm-1">
							    <center>
							    		<img alt="" width="40px" src="https://gravatar.css.network/avatar/{{ md5(strtolower(trim($note->user->email))) }}?s=40&d=identicon&r=PG&f=1">
							            {{ $note->user->name }}
							    </center>
							  </div>
  
							<div class="col-sm-offset-0 col-sm-10">
		    					{{ $note->name; }}
							</div>
					
							<div class="col-sm-offset-0 col-sm-1">
										@if($note->user_id == Auth::user()->id )
                                            <form action="{{url('note/' . $note->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-note-{{ $note->id }}" class="btn btn-link" title="删除!!!">
                                                	<span style="color:red">×</span>
                                                	<!-- 
                                                    <i class="fa fa-btn fa-trash"></i>
                                                	 -->
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
					  </div>
					  @endforeach
                    
                    </div>
                </div>
                 {!! $notes->links() !!}
            @endif
        </div>
    </div>
@endsection
