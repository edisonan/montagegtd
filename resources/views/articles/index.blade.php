@extends('layouts.app')

@section('content')
<style>
        .lazy{width:100%;height:0;background-size:100%;}
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
	
	$(".set_star").click(function(){
		article_id = $(this).attr('article_id');
		$.get("{{ url('/article/star') }}"+"/"+article_id,{},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert("设置失败");
			} else {
				$(this).remove();
			}
		});
	});

	$(".set_read").click(function(){
		article_id = $(this).attr('article_id');
		$.get("{{ url('/article/read') }}"+"/"+article_id,{status:"read"},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert("设置失败");
			} else {
				$(this).remove();
			}
		});
	});

	$(".post .post-content").each(function(){
		height=$(this).height();
		if(height>80) {
			$(this).css("height","180");
			$(this).css("overflow","hidden");
			$(this).parent().children("div.post-permalink").children("a").last().after("<a class=\"morecon btn btn-primary\" style=\"cursor:pointer; font-size: 2em; \"><img style=\"width:15px;\" src=\"/img/icon/pull.png\">点开更多内容</a>");
		}
	});
	
	$(".morecon").click(function(){
		$(this).parent().parent().children("div.post-content").last().css("height","auto");
		$(this).css("display","none");
	});
});
</script>
    <div class="container">
    
    	<div class="col-sm-offset-0 col-sm-3">
    		<div class="panel panel-default">
                <div class="panel-heading">
                	订阅分类
                </div>

                <div class="panel-body">
		    		<ul class="nav nav-pills nav-stacked">
		    			@if(count($categorys)>0)
			    			@foreach($categorys as $category)
			    			<li role="presentation" class="">
			    				{{ $category->name }}
			    				@if(count($category->feeds)>0)
			    					<ul>
			    					@foreach($category->feeds as $feed)
			    						<li>
			    							<a href="{{ url('articles?feed_id='.$feed->id.'&status='.$status) }}">
			    							<span style="display: block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
			    							<?php /**
			    							[
			    							@if($status == 'unread')
			    								{{$feed->unread_count}}
			    							@else
			    								{{$feed->read_count}}
			    							@endif
			    							]
			    							*/?>
			    							{{ $feed->feed_name,0,10 }}
			    							</span>
			    							</a>
			    						</li>
			    					@endforeach
			    					</ul>
			    				@endif
			    			</li>
			    			@endforeach
			    		@endif
		    		</ul>
		    	</div>	
    		</div>
    	</div>
    
        <div class="col-sm-offset-0 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                		@if(count($articles) == 0 && count($recommend_feeds) > 0)
                			看看大家推荐的订阅源吧~
                		@else
                    		新的文章
                    	@endif
                    	[<a href="{{ url('articles') }}">未读</a>][<a href="{{ url('articles?status=read') }}">已读</a>][<a href="{{ url('articles?status=star') }}">加星</a>]
                    	<div style="float:right">
                    		<a href="{{ url('feed/checkNewFeed')}}">更新?</a>
                    		<a href="{{ url('categorys') }}" target="_blank">[分类管理]</a>
                    		<a href="{{ url('feeds')}}">[订阅管理]</a>
                    	</div>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    		@if(count($articles) == 0 && count($recommend_feeds) > 0)
		                    	@foreach($recommend_feeds as $recommend_feed)
		                    	<div class="col-sm-offset-0 col-sm-6"  style="display: block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;    padding: 2px;">
									<span style="color:green;">推荐:</span><a href="{{ url('feeds') }}?url={{ $recommend_feed->url }}" >{{ $recommend_feed->feed_name }}</a>										                    		
								</div>
		                    	@endforeach
                    		@endif
                    
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
										<a href="/post/laravel-5-4-is-now-released/">
											<img src="{{$article->image_url}}" alt="{{ $article->subject }}">
										</a>
									</div>
									@endif
									<div class="post-content" style="    margin: 5px 0;">
										<p></p>
										<p><?php echo App\Http\Utils\CommonUtil::removeXSS($article->content); ?></p>
										<p></p>
									</div>
									<div class="post-permalink">
										@if($article->status == 'unread')
										<a href="javascript:void(0);"  article_id="{{$article->id}}" class="btn btn-default set_read">设为已读</a>
										@endif
										@if($article->status != 'star')
										<a href="javascript:void(0);"  article_id="{{$article->id}}" class="btn btn-default set_star">加入收藏</a>
										@endif
									</div>
									<footer class="post-footer clearfix"></footer>
								</article>
								@endforeach
                        		{!! $articles->appends($page_params)->links() !!}
                        @else
                        	<hr></hr>
                        	<div class="col-sm-offset-0 col-sm-12" style="    padding: 10px;">
                        		<b>暂时没有最新文章,可以点击这里多添加自己喜欢的订阅源~~ <a href="{{ url('feeds')}}">[订阅管理]</a></b>
                        	</div>
                        @endif
                </div>
            </div>

        </div>
    </div>
@endsection
