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
			$(this).css("height","180");
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
});
</script>
    <div class="container">
    
        <div class="col-sm-offset-0 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的文章
                    	[<a href="{{ url('feeds') }}?url={{ $feed->url }}">订阅此源</a>]
                    	<div style="float:right">
                    		<a href="{{ url('feed/checkNewFeed')}}"><img alt="" src="/img/icon/refresh.png" style="width: 15px;margin-right: 10px;"></a>
                    		<a href="{{ url('kindles') }}" target="_blank">[Kindle订阅]</a>
                    		<a href="{{ url('categorys') }}" target="_blank">[分类管理]</a>
                    		<a href="{{ url('feeds')}}">[订阅管理]</a>
                    	</div>
                </div>

                <div class="panel-body">
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
												来源：<a href="{{ $article->feed->url}}" target="_blank">{{ $article->feed->feed_name}}</a>
											</span> 
											• 
											<time class="post-date" datetime="{{$article->published}}" title="{{$article->published}}">{{$article->published}}</time>
										</div>
									</div>
									@if(!empty($article->image_url))
									<div class="featured-media">
										<a href="#">
											<img src="{{$article->image_url}}" alt="{{ $article->subject }}">
										</a>
									</div>
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
                        		{!! $articleSubs->appends($page_params)->links() !!}
                        @else
                        @endif
                        
                </div>
            </div>

        </div>
    </div>
@endsection
