@extends('layouts.app')

@section('content')
<style>
	      
	      .rowone{
	        overflow: hidden;
		    text-overflow: ellipsis;
		    display: -webkit-box;
		    -webkit-box-orient: vertical;
		    -webkit-line-clamp: 1;
	      }
	      
	      .rowtwo{
	        overflow: hidden;
		    text-overflow: ellipsis;
		    display: -webkit-box;
		    -webkit-box-orient: vertical;
		    -webkit-line-clamp: 1;
	      }
</style>

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
    
        <div class=" col-md-12">
        	@include('common.success')
            <div class="card" style="margin-bottom: 10px;">
                <div class="card-header">
                    	Search
                </div>
				
				<div class="card-body">
					<form action="/feeds/search">
						<div class="form-row">
							<div  class="col-7">
								<input type="text" value="" name="name" class="form-control" placeholder="input search value"/>
							</div>
							<div class="col">
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			<div class="card" style="margin-bottom: 10px;">
                <div class="card-header">
                    	Weibo
                </div>
				
				<div class="card-body">
					<form action="/feeds/">
						<fieldset disabled>
							<div class="form-row">
								<div  class="col-7">
									<input type="text" value="" class="form-control" placeholder="input weibo user_id"/> 
								</div>
								<!--
								<div  class="col">
									  <label for="disabledSelect">Disabled select menu</label>
									  <select id="disabledSelect" class="form-control">
										<option>Disabled select</option>
									  </select>
								</div>
								<div  class="col">
									<input type="text" value="" class="form-control" placeholder="input weibo user_id"/> 
								</div>
								-->
								<div class="col">
									<button type="submit" class="btn btn-primary">Weibo</button>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			
			<div class="card" style="margin-bottom: 10px;">
                <div class="card-header">
                    	Weixin
                </div>
				
				<div class="card-body">
					<form action="/feeds/">
						<fieldset disabled>
							<div class="form-row">
								<div  class="col-7">
									<input type="text" value="" class="form-control" placeholder="input weixin id"/>
								</div>
								<!--
								<div  class="col">
									  <label for="disabledSelect">Disabled select menu</label>
									  <select id="disabledSelect" class="form-control">
										<option>Disabled select</option>
									  </select>
								</div>
								
								<div  class="col">
									<input type="text" value="" class="form-control" placeholder=""/> 
								</div>
								-->
								<div class="col">
									<button type="submit" class="btn btn-primary">Weixin</button>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			
			<div class="card" style="margin-bottom: 10px;">
                <div class="card-header">
                    	Category
                </div>
				
				<div class="card-body">
					@foreach (array() as $feed)
					<div class="row col-md-6" style="padding:10px">
						<div class="card card-block">
							<b class="card-title rowone"> 
							
							</b>
						</div>
					</div>
					@endforeach
				</div>
			</div>
			
			<div class="card" style="margin-bottom: 10px;">
                <div class="card-header">
                    	Recommend
                </div>
				
                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if (count($feeds) > 0)
                    
                    	<div class="row">
		                    @foreach ($feeds as $feed)
			                    <div class="col-md-4" style="padding:10px">
			                    	<div class="card card-block">
								      <b class="card-title rowone"> 
								      	<img alt="" style="width: 15px;" src="{{ $feed->favicon }}">
				                    	<a href="{{ url('article/list') }}?feed_id={{$feed->id}}" >{{ $feed->feed_name }}</a>
				                      </b>
								      <p class="card-text rowtwo">{{ $feed->feed_desc }} &nbsp;</p>
  									  <a class="card-link text-right" href="javascript:void(0)" feed_id="{{ $feed->id }}" class="feed_quick_sub">直接订阅</a>
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
