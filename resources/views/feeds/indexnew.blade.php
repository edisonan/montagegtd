@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.6.0/Sortable.min.js"></script>

<script type="text/javascript">
$(document).ready(function () {

	$(".delete_feed").click(function(){
		feed_value = $(this).attr("feed_value");
		feed_token = $(this).attr("feed_token");
		feed_type = $(this).attr("feed_type");

		if (feed_type == 'delete' && !confirm("确认要删除此订阅咩？")) {
			return false;
		}
		
	$.ajax({
		    url: "{{ url('feed') }}"+"/"+feed_value,
		    type: 'DELETE',
		    data: {type:feed_type,_token:feed_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$('#'+feed_value).remove();
				}
		    }
		});
	});
});

</script>

    <div class="container">
    
        <div class="col-md-offset-0 col-md-12">
        	@include('common.success')
            <div class="panel panel-default">
                <div class="panel-heading">
                    	订阅管理
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if (count($nav_infos) > 0)
                    	<div id="multi">
                    		@foreach ($nav_infos as $nav_info)
                                <div id="categorys{{ $nav_info['category_info']['category_id'] }}" class="tile" category_id="{{ $nav_info['category_info']['category_id'] }}">
                    					<div class="tile__name">{{ $nav_info['category_info']['category_name'] }}</div>
                                		@foreach($nav_info['list'] as $feed)
	                                	<div class="tile__list col-md-12">
	                                			<div class="col-md-12" curr_category_id="{{ $nav_info['category_info']['category_id'] }}"> 
	                                				
		                                			<div class="col-md-6" style="display: -webkit-inline-box;border-radius:25px;">
		                                				<span class="glyphicon glyphicon-move">{{ $feed['feed_name'] }}</span>
		                                			</div>
		                                			
		                                			<div class="col-md-6 text-right">
		                                				<a href="{{ url('feed/'.$feed['feed_sub_id'])}}" style="color:blue"><img alt=""     style="width: 15px;" src="/img/icon/edit.png"></span>
			                                        	<a href="javascript:void(0)" class="delete_feed" task_type="delete" feed_value="{{ $feed['feed_sub_id'] }}" feed_token="{{ csrf_token() }}"  style="cursor:pointer;">
			                                        		<img alt="" style="width: 15px;" src="/img/icon/delete.png">
				                        				</a> 
			                            			</div>
			                            			
			                            		</div>
	                                	</div>
                                		@endforeach
  								</div>
                        	@endforeach
                    	</div>
                    @endif
                    
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript">
		var multi =  document.getElementById('multi');
		Sortable.create(multi, {
			  animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
			  handle: ".tile__name", // Restricts sort start click/touch to the specified element
			  draggable: ".tile", // Specifies which items inside the element should be sortable
			  onEnd: function (evt){
				     var item = evt.item; // the current dragged HTMLElement
				     console.log($('#'+item.id).attr("category_id"));
			  }
		});
	
		[].forEach.call(multi.getElementsByClassName('tile__list'), function (el){
			Sortable.create(el, {
				group: 'photo',
				animation: 150, // Specifies which items inside the element should be sortable
				onEnd: function (evt){
					     var item = evt.item; // the current dragged HTMLElement
					     console.log(item);
				}
			});
		});

	</script>
@endsection
