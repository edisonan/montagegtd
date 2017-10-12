@extends('layouts.app')

@section('content')
<script type="text/javascript">
$(document).ready(function () {

	$(".feed_quick_sub").click(function(){
		var feed_id = $(this).attr('feed_id');
		$.get("{{ url('/feeds/quickstore') }}",{"feed_id":feed_id},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert(result_arr.msg);
			} else {
				alert(result_arr.msg);
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
                    	发现新的订阅源
                    	<div style="float:right">
                    		<a href="{{'/articles'}}">[返回]</a>
                    	</div>
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if (count($feeds) > 0)
                    
                    	<div class="row" style="padding: 10px;">
		                    @foreach ($feeds as $feed)
			                    <div class="col-md-offset-0 col-md-3 text-center" style="height:100px;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;border-radius: 5px;    padding: 10px;">
			                    	<div style="height:60px">
					                    <img alt="" style="width: 15px;" src="{{ $feed->favicon }}">
				                    	<a href="{{ url('article/list') }}?feed_id={{$feed->id}}" >{{ $feed->feed_name }}</a>
			                    	</div>
			                    	<div style="float: right">
				                    	<a href="javascript:void(0)" feed_id="{{ $feed->id }}" class="feed_quick_sub">订阅</a>
			                    	</div>
			                    </div>
		                    @endforeach
                    	</div>
                    	<br>
                        {!! $feeds->links() !!}
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
