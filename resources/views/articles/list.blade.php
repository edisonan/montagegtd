@extends('layouts.app')

@section('content')

<script src="/js/lazyload.min.js"></script>
<script type="text/javascript" charset="utf-8">
  $(function() {
	//lazy img
	$("img.lazy").lazyload();
  });
</script>
<script type="text/javascript">
$(document).ready(function () {

	$(".post .post-content").each(function(){
		height=$(this).height();
		if(height > 1000) {
			$(this).css("height","360");
			$(this).css("overflow","hidden");
			$(this).after("<p class=\"morecon\" style=\"align-text: right;text-align: right; color: #337ab7; cursor:pointer; font-size: 2em; \">点开更多内容</p>");
			//$(this).parent().children("div.post-permalink").children("a").last().after("<a class=\"morecon btn btn-primary\" style=\"cursor:pointer;  \"><img style=\"width:20px;\" src=\"/img/icon/pull.png\">点开更多内容</a>");
		}
	});
	
	$(".morecon").click(function(){
		$(this).parent().children("div.post-content").css("height","auto");
// 		$(this).parent().parent().children("div.post-content").css("height","auto");
		$(this).css("display","none");
	});

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
    
        <div class="col-sm-offset-0 col-sm-12">
            <div class="card">
                <div class="card-header">
                    	{{ $feed->feed_name }}
                    	
                    	<div style="float:right">
                    		[<a href="javascript:void(0)"  feed_id="{{ $feed->id }}" class="feed_quick_sub">订阅此源</a>]
                    	</div>
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    		@if (count($articles) > 0)
	                    		@foreach ($articles as $article)
	                            <article class="post">
									<div class="post-head">
										<h1 class="post-title">
											<a href="{{ $article->url }}">[原文]</a>
											<a href="{{ url('article/view/'.$article->id) }}">{{ $article->subject }}</a>
										</h1>
										<div class="post-meta">
											<span class="author">
												来源：<a href="{{ App\Http\Utils\CommonUtil::hostUrl($article->feed->url) }}" target="_blank">{{ $article->feed->feed_name}}</a>
											</span> 
											• 
											<time class="post-date" datetime="{{$article->published}}" title="{{$article->published}}">{{$article->published}}</time>
										</div>
									</div>
									@if(!empty($article->image_url))
									<!-- 
									<div class="featured-media">
										<a href="#">
											<img src="{{$article->image_url}}" alt="{{ $article->subject }}">
										</a>
									</div>
									 -->
									@endif
									<div class="post-content" style="    margin: 5px 0;">
										<p></p>
										<p><?php echo App\Http\Utils\CommonUtil::removeXSS($article->content); ?></p>
										<p></p>
									</div>
									<div class="post-permalink text-right">
									</div>
									<footer class="post-footer clearfix"></footer>
								</article>
								@endforeach
                        		{!! $articles->appends($page_params)->links() !!}
                        @else
                        @endif
                        
                </div>
            </div>

        </div>
    </div>
@endsection
