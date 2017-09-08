@extends('layouts.app')

@section('content')
<style>
        .lazy{width:100%;height:0;background-size:100%;}
       
        .product-container {  
	      width: 260px;  
	      margin: 5px auto;  
	      border-radius: 10px;  
	      background: #f6f8f7;  
	  	}
	      .name {  
		        border-bottom: 1px solid @gray-light;  
		        font-size: 20px;  
		        padding: 2px;  
	      }  
	      
	      .interest {  
		        color: #1da427;  
		        font-size: 70px;  
		        font-weight: bold;  
		        padding: 0px;  
		        margin-bottom: -30px;  
	      }  
	      
	      .percent {  
		         	 font-size: 31px;  
		   }
	      
	      .intro {  
	        	padding: 5px;  
	      }  
	      
	      .strong {  
		        padding: 3px;  
		        font-size: 17px;  
		        color: @white;  
		        background: #326c84;  
		        border-radius: 0 0 10px 10px;  
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
	
	$(".set_star").click(function(){
		article_sub_id = $(this).attr('article_sub_id');
		$.get("{{ url('/article/star') }}"+"/"+article_sub_id,{},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert("设置失败");
			} else {
				alert("收藏成功");
			}
		});
	});

	$(".set_read").click(function(){
		article_sub_id = $(this).attr('article_sub_id');
		$.get("{{ url('/article/read') }}"+"/"+article_sub_id,{status:"read"},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert("设置失败");
			} else {
				alert("设置已读成功");
			}
		});
	});

	$("#marked_all_read").click(function(){
		var ids = $(this).attr('ids');
		$.get("{{ url('/article/reads') }}",{ids:ids,status:"read"},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert("设置失败");
			} else {
				location.href="";
			}
		});
	});

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
});
</script>
    <div class="container">
    
    	<div class="col-sm-offset-0 col-sm-3">
    		@include('common.success')
    		<div class="panel panel-default">
                <div class="panel-heading">
                	订阅分类
                </div>

                <div class="panel-body">
		    		<ul class="nav nav-pills nav-stacked">
		    			@if(count($nav_infos)>0)
			    			@foreach($nav_infos as $nav_id=>$nav_info)
			    			<li role="presentation" class="">
			    				{{ $nav_info['category_info']['category_name'] }}
			    				@if(count($nav_info['list'])>0)
			    					<ul>
			    					@foreach($nav_info['list'] as $item)
			    						<li>
			    							<a href="{{ url('articles?feed_id='.$item['feed_id'].'&status='.$status) }}">
			    							<span style="display: block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
			    							[
			    							<?php echo $item['feed_count']?>
			    							]
			    							{{ $item['feed_name'],0,8 }}
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
                		@if(count($articleSubs) == 0 && count($recommend_feeds) > 0)
                			暂无文章,看看大家推荐的订阅源吧~
                		@else
                    		新的文章
                    	@endif
                    	[<a href="{{ url('articles') }}">未读</a>][<a href="{{ url('articles?status=read') }}">已读</a>][<a href="{{ url('articles?status=star') }}">加星</a>]
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
                    
                    		@if (count($articleSubs) > 0)
                    			<?php $article_sub_ids = array();?>
	                    		@foreach ($articleSubs as $articleSub)
	                    		<?php $article = $articleSub->article;if(empty($article)) continue;$article_sub_ids[] = $articleSub->id;?>
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
										@if($article->status == 'unread')
										<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-default set_read">设为已读</a>
										@endif
										@if($article->status != 'star')
										<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-default set_star">加入收藏</a>
										@endif
									</div>
									<footer class="post-footer clearfix"></footer>
								</article>
								@endforeach
                        		{!! $articleSubs->appends($page_params)->links() !!}
                        		
                        		@if(!isset($_GET['status']) || $_GET['status'] == 'unread')
                        		<button class="col-sm-12 btn btn-default" id="marked_all_read" ids="<?php echo implode(',', $article_sub_ids);?>">Marked All Read</button>
                        		@endif
                        @else
                        @endif
                        
                        @if(count($articleSubs) == 0 && count($recommend_feeds) > 0)
		                    	@foreach($recommend_feeds as $recommend_feed)
                    			<div class="col-sm-offset-0 col-sm-6">
							        <div class="product-container">  
							          <div class="name"><a href="{{ url('article/list') }}?feed_id={{$recommend_feed->id}}" >{{ substr($recommend_feed->feed_name,0) }}</a></div>  
							          <div class="intro text-right">更新于:{{ date('d日H时',strtotime($recommend_feed->updated_at)) }}</div>  
							        </div>  
                    			</div>
								<!-- 
		                    	<div class="col-sm-offset-0 col-sm-6"  style="display: block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;    padding: 2px;">
									<span style="color:green;">推荐:</span><a href="{{ url('feeds') }}?url={{ $recommend_feed->url }}" >{{ $recommend_feed->feed_name }}</a>										                    		
								</div>
								 -->
		                    	@endforeach
                    	@endif
                </div>
            </div>

        </div>
    </div>
@endsection
