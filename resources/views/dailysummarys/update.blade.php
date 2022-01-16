@extends('layouts.app')

<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

@section('content')

<script type="text/javascript">
$(document).ready(function () {
	getInfos('{{ $summary_date }}');

	$('#dailysummary-summarydate').on/*bind*/("input propertychange", function () {
		getInfos($(this).val());
	});
});

function getInfos($summary_date) {
	$.ajax({
	    url: "{{ url('dailytips') }}",
	    type: 'GET',
	    data: {"_token":"{{ csrf_token() }}","summary_date":$summary_date},
	    success: function(result_arr) {
			if(result_arr.code != 9999){
				alert('处理失败，请稍后再试');
			} else {
				$('#tips').html("");
				$.each( result_arr.result.infos, function( subject, tipInfo ){
					var li = '<li role="presentation" class="col-md-12" style="padding-top: 10px;"><span class="category_items">'+subject+'['+(Object.getOwnPropertyNames(tipInfo.list).length -1 )+']</span>';
					if(Object.getOwnPropertyNames(tipInfo.list).length > 0){
						li += '<ul class="category_item">';
						$.each(tipInfo.list,function(item){
							li += '<li class="rowone">';
							if(item.url != '') {
								li += '<a href="' +item.url+ '">[Go]</a>';
							}
							li += '<span>' + item.content + '</span>';
						});
						li += '</ul>';
					}
					li += '</li>'
					$("#tips").append(li);
				});
			}
	    }
	});
}
</script>
    <div class="container">
                <div class="card">
                    <div class="card-header">
                        	修改日报
                        	<div style="float:right">
	                    		<a href="{{'/dailysummarys'}}">[返回]</a>
	                    	</div>
                    </div>

                    <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('dailysummary/'.$dailysummary->id) }}" method="POST" class="form-horizontal">
                        
                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label for="dailysummary-summarydate" class="col-md-3 control-label">总结日期:</label>

                            <div class="col-md-8">
	                                <input type="text" name="summary_date" id="dailysummary-summarydate" class="form-control" value="{{ $dailysummary->summary_date }}" onClick="WdatePicker({dateFmt:'yyyy-MM-dd',maxDate:'%y-%M-%d'})">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="dailysummary-workcontent" class="col-md-3 control-label">工作总结:</label>

                            <div class="col-md-8">
				<textarea class="form-control" rows="4"  name="work_content" id="dailysummary-workcontent" >{{ $dailysummary->work_content }}</textarea>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="dailysummary-lifecontent" class="col-md-3 control-label">生活总结:</label>

                            <div class="col-md-8">
				<textarea class="form-control" rows="4"  name="life_content" id="dailysummary-lifecontent" >{{ $dailysummary->life_content }}</textarea>
                            </div>
                        </div>
 
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i>提交！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        Tips
                    </div>
                    
                    <div class="card-body" id="tipBody" >
                    	<ul class="nav nav-pills nav-stacked" id="tips">
					
						</ul>
                    </div>
                 </div>
        </div>
    </div>
@endsection
