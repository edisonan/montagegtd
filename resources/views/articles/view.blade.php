@extends('layouts.app')

@section('title', $article->subject.' - Montage GTD')

@section('content')
<style>
		  
		  .post-text{
			padding: 10px;
			font-size: 20px;
		  }
		  
		  img{
			      max-width: 75%;
		  }
</style>
<script src="/js/lazyload.min.js"></script>
<script type="text/javascript" charset="utf-8">
  $(function() {
	//lazy img
	$("img.lazy").lazyload();
  });
</script>
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
    
        <div class=" mx-auto col-md-8">
            <div class="card">
                <div class="card-header">
                	<!-- 
                	
                		<span style="color: yellow">
                    	<a href="{{ url('article/star/'.$article->id) }}">★</a>
                    	<a href="{{ url('article/star/'.$article->id) }}">☆</a>
                    	</span>
                    	<a href="{{ $article->url }}" target="">
                    		全文
                    	</a>
                     -->
                     <a href="{{ url('article/list') }}?feed_id={{$article->feed->id}}"> 
                     {{$article->feed->feed_name}}
                     </a>
                    	<div style="float:right">
                    		@if(!$is_feed)
                    		<a href="javascript:void(0)" feed_id="{{ $article->feed->id }}" class="feed_quick_sub">[添加订阅]</a>
                    		@endif
                    		<a href="{{'/articles'}}">[继续阅读]</a>
                    	</div>
                </div>
                
                <div class="card" style="margin-bottom:10px">
					<div class="card-block" style="padding: 10px;">
					  @if(!empty($article->subject))
					  <h4 class="card-title">
							<a href="{{ $article->url }}">[原文]</a>
							{{ $article->subject }}
					  </h4>
				      @endif
					  <p class="card-text"><small class="text-muted">来源：<a href="{{ App\Http\Utils\CommonUtil::hostUrl($article->feed->url) }}" target="_blank">{{ $article->feed->feed_name}}</a> * {{$article->published}}</small></p>
					  
					  <div class="card-text post-text">
						<?php 
						$content = $article->content; 
						echo App\Http\Utils\CommonUtil::formatContentHtml($content); 
						?>
					  </div>
					  <p class="card-text text-right">
					  <a id="share" name="share"></a>
								<wb:share-button appkey="567683707" addition="simple" type="button" ralateUid="1671353227" title="{{ $article->subject }}" url="<?php echo $_SERVER['REQUEST_URI'];?>" pic="{{ $article->image_url }}"></wb:share-button>
					  </p>
					</div>
				  </div>

            </div>

        </div>
    </div>
@endsection
