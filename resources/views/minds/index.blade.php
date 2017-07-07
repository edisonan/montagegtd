@extends('layouts.app')

@section('content')

<script type="text/javascript">
$(document).ready(function () {
	$("#check_url").unbind("click").click(function(){
// 		$("#processTips").text("处理中");
		
// 		url = $("#url").val();
// 		$.get("{{ url('mind/checkFeedUrl') }}",{url:url},function(result){
// 			result_arr = JSON.parse(result);
// 			if(result_arr.code != 9999){
// 				alert('该url未检测到内容，请确认！');
// 			}
// 			$("#mind_name").val(result_arr.result.title);
// 			$("#processTips").text("处理完成");
// 		});
	});
});
</script>
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	思维导图
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('mind') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Task Name -->
                        <div class="form-group" id="task_form_div1" >
                            <div class="col-sm-9" style="display: -webkit-inline-box;width: 75%;">
                            	<input type="text" name="name" id="name" class="form-control" value="">
                            </div>
                            <div class="col-sm-3" style="display: -webkit-inline-box;width: 25%;">
                            	<button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>新想法！
                                </button>
                            </div>
                        </div>
                        
                    </form>
                    
                    
                    @if (count($minds) > 0)
                    <table class="table table-striped task-table">
                            <thead>
                                <th>想法</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($minds as $mind)
                                    <tr >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre">
                                        		<a href="{{url('mind/' . $mind->id)}}" title="">{{ $mind->name }}</a>
                                        	</pre>
                                        </td>

										<td  width="1"  align='right'>
                                            <a href="{{ url('mind/'.$mind->id)}}" style="color:blue">✎</span>
                                        </td>
                                        
                                        <!-- Task Delete Button -->
                                        <td  width="1"  align='right'>
                                            <form action="{{url('mind/' . $mind->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-task-{{ $mind->id }}" class="btn btn-link" title="删除!!!">
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
                         {!! $minds->links() !!}
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
