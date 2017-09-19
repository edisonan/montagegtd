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

<script src="/js/jquery.cookie.js"></script>
<script src="/js/lazyload.min.js"></script>
<script type="text/javascript" charset="utf-8">
  $(function() {
	//lazy img
	$("img.lazy").lazyload();
  });
</script>
<script type="text/javascript">
$(document).ready(function () {

	$(".set_star,.set_read,.set_read_later").click(function(){
		article_sub_id = $(this).attr('article_sub_id');
		active = $(this).hasClass("active");

		if($(this).hasClass("set_star")){
			status = active?"read":"star";
		} else if($(this).hasClass("set_read")){
			status = active?"unread":"star";
		} else if($(this).hasClass("set_read_later")){
			status = active?"unread":"read_later";
		} else {
			return '';
		}

		item = $(this);
		$.get("{{ url('/articles/status') }}"+"/"+article_sub_id,{"status":status},function(result){
			result_arr = JSON.parse(result);
			if(result_arr.code != 9999){
				alert("设置失败");
			} else {
				if(active){
					item.removeClass("active");
				} else {
					item.addClass("active");
				}
			}
		});
	});

	$("#marked_all_read").click(function(){
		var ids = $(this).attr('ids');
		$.get("{{ url('/articles/allstatus') }}",{"ids":ids,"status":"read"},function(result){
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
		}
	});
	
	$(".morecon").click(function(){
		$(this).parent().children("div.post-content").css("height","auto");
		$(this).css("display","none");
	});

	//处理屏蔽图片
	$("#unable_img").click(function(){
		if($("#unable_img").is(':checked')){
			$.cookie('unable_img', true); 
			console.log($.cookie("unable_img"));
		} else {
			$.cookie('unable_img', false); 
			console.log($.cookie("unable_img"));
		}
	});
	
	$(".post img").click(function(){
		if($(this).attr("orignal_src") != null){
			$(this).attr("src", $(this).attr("orignal_src"));//修改图片路径
		}
	});
	if($.cookie("unable_img") != null && $.cookie("unable_img")=="true"){
		$("#unable_img").prop('checked', true);
		
		$(".post img").each(function(){
			if($(this).attr("src") != null && $(this).attr("src")!="" && $(this).attr("src") != "/img/unable_img.png") {
			    $(this).attr("orignal_src",$(this).attr("src"));//修改图片路径
			    $(this).attr("src","/img/unable_img.png");//修改图片路径
			}
		    
		    $(this).parent("a").attr("orignal_href", $(this).parent("a").attr("href"));//修改链接路径
		    $(this).parent("a").attr("href","javascript:void(0)");//修改链接路径
		});
	}
	
	//处理一目十行
	$("#unable_desc").click(function(){
		if($("#unable_desc").is(':checked')){
			$.cookie('unable_desc', true); 
		} else {
			$.cookie('unable_desc', false); 
		}
	});
	
	if($.cookie("unable_desc") != null && $.cookie("unable_desc")=="true"){
		$("#unable_desc").prop('checked', true);
		
// 		$(".post-content").each(function(){
// 			$(this).css("display","none");
// 		});
	}
});
</script>
    <div class="container">
    
    	<div class="col-sm-offset-0 col-sm-3">
    		@include('common.success')
    		<div class="panel panel-default">
                <div class="panel-heading">
                	订阅分类
                	<div style="float:right">
                    	<a href="{{'/feeds/setting'}}">[管理]</a>
                    </div>
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
                    	[<a href="{{ url('articles') }}">未读</a>][<a href="{{ url('articles?status=read') }}">已读</a>][<a href="{{ url('articles?status=star') }}">加星</a>][<a href="{{ url('articles?status=read_later') }}">稍后阅读</a>]
                    	<div style="float:right">
                    		<input type="checkbox" value="" id="unable_desc"/>一目十行
                    		<input type="checkbox" value="" id="unable_img"/>屏图
                    		<a href="{{ url('feed/checkNewFeed')}}"><img alt="" src="/img/icon/refresh.png" style="width: 15px;margin-right: 10px;"></a>
                    		<a href="{{ url('kindles') }}" target="_blank">[订阅到Kindle]</a>
                    		<a href="{{ url('feeds')}}">[添加订阅]</a>
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
									<!-- 
									<div class="featured-media">
										<a href="#">
											<img src="{{$article->image_url}}" alt="{{ $article->subject }}">
										</a>
									</div>
									 -->
									@endif
									
									@if($unable_desc == "false")
									<div class="post-content" style="    margin: 5px 0;">
										<p></p>
										<p><?php 
											$content = $article->content; 
											if($unable_img == "true"){ 
												$content = str_replace('src="', 'src="/img/unable_img.png" orignal_src="', $content);
											} 
											echo App\Http\Utils\CommonUtil::removeXSS($content); 
											?>
										</p>
										<p></p>
									</div>
									<div class="post-permalink text-right">
										<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-default set_read_later @if($articleSub->status == 'read_later') active @endif">Read Later</a>
										<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-default set_read @if($articleSub->status == 'read') active @endif">Read</a>
										<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-default set_star @if($articleSub->status == 'star') active @endif">Star</a>
									</div>
									<footer class="post-footer clearfix"></footer>
									@endif
								</article>
								@endforeach
                        		{!! $articleSubs->appends($page_params)->links() !!}
                        		
                        		@if(!isset($_GET['status']) || $_GET['status'] == 'unread')
                        		<button class="col-sm-12 btn btn-default" id="marked_all_read" ids="<?php echo implode(',', $article_sub_ids);?>">Marked All Read</button>
                        		@endif
                        @endif
                        
                        
                        @if(count($articleSubs) == 0)
                        
	                        @if(!empty($next_recommend_feed))
	                        	<div class="text-center col-sm-12">
	                        		这个订阅还有{{$next_recommend_feed['feed_count']}}篇文章:
	                        		<a href="{{ url('articles?feed_id='.$next_recommend_feed['feed_id'].'&status='.$status) }}">{{$next_recommend_feed['feed_name']}}</a>
	                        	</div>
	                        @endif
	                        @if(count($recommend_feeds) > 0)
	                        		<div class="text-center col-sm-12 post-head">
	                        			还可以逛逛其他的资源~
	                        		</div>
			                    	@foreach($recommend_feeds as $recommend_feed)
	                    			<div class="col-sm-offset-0 col-sm-6">
								        <div class="product-container">  
								          <div class="name"><a href="{{ url('article/list') }}?feed_id={{$recommend_feed->id}}" >{{ substr($recommend_feed->feed_name,0) }}</a></div>  
								          <div class="intro text-right">最近更新:{{ date('d日H时',strtotime($recommend_feed->updated_at)) }}</div>  
								        </div>  
	                    			</div>
			                    	@endforeach
	                    	@endif
	                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
