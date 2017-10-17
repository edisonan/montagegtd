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
	      .active {
	      	color:red;
	      }
	      
	      .rowone{
	        overflow: hidden;
		    text-overflow: ellipsis;
		    display: -webkit-box;
		    -webkit-box-orient: vertical;
		    -webkit-line-clamp: 1;
	      }
		  
		  .post-text{
			padding: 10px;
			font-size: 18px;
		  }
		  
		  img{
			      max-width: fit-content;
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
					item.siblings().removeClass("active");
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

	$(".post-text").each(function(){
		height=$(this).height();
		if(height > 1000) {
			$(this).css("height","360");
			$(this).css("overflow","hidden");
			$(this).parent().find(".view-all").css("display","");
		}
	});
	
	$(".view-all").click(function(){
		$(this).parent().parent().children("div.post-text").css("height","auto");
		$(this).css("display","none");
	});

	//处理屏蔽图片
	$("#unable_img").click(function(){
		if($("#unable_img").is(':checked')){
			$.cookie('unable_img', true); 
		} else {
			$.cookie('unable_img', false); 
		}
		location.href="";
	});
	
	$(".post-text img").click(function(){
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
		location.href="";
	});
	
	if($.cookie("unable_desc") != null && $.cookie("unable_desc")=="true"){
		$("#unable_desc").prop('checked', true);
		
// 		$(".post-content").each(function(){
// 			$(this).css("display","none");
// 		});
	}

	var ua =  navigator.userAgent;
	isAndroid = /Android/i.test(ua);
	isBlackBerry = /BlackBerry/i.test(ua);
	isWindowPhone = /IEMobile/i.test(ua);
	isIOS = /iPhone|iPad|iPod/i.test(ua);
	isMobile = isAndroid || isBlackBerry || isWindowPhone || isIOS;
	if(isMobile){
		$(".category_item").each(function(){
			$(this).css("display","none");
		});
	}

	$(".category_items").click(function(){
		$(this).parent().find(".category_item").toggle();
	});
});
</script>
    <div class="container">
    	<div class="row">
    	<div class="col-md-offset-0 col-md-3">
    		@include('common.success')
    		<div class="card card-default">
                <div class="card-header">
                	订阅分类
                	<div style="float:right">
                		<a href="{{ url('kindles') }}">[SendKindle]</a>
                    	<a href="{{'/feeds/setting'}}">[管理]</a>
                    </div>
                </div>

                <div class="card-body">
		    		<ul class="nav nav-pills nav-stacked">
		    			@if(count($nav_infos)>0)
			    			@foreach($nav_infos as $nav_id=>$nav_info)
			    			<li role="presentation">
			    				<span class="category_items">
			    				{{ $nav_info['category_info']['category_name'] }}[{{count($nav_info['list'])}}]
			    				</span>
			    				@if(count($nav_info['list'])>0)
			    					<ul class="category_item">
			    					@foreach($nav_info['list'] as $item)
			    						<li class="rowone">
			    							<a href="{{ url('articles?feed_id='.$item['feed_id'].'&status='.$status) }}">
			    							<span style="/*display: block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;*/">
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
    
        <div class="col-md-offset-0 col-md-9">
            <div class="card card-default">
                <div class="card-header">
                		@if(count($articleSubs) == 0 && count($recommend_feeds) > 0)
                			尚无文章
                		@else
                			@if($status == 'unread')
                			未读
                			@elseif($status == 'read')
                			已读
                			@elseif($status == 'star')
                			收藏
                			@elseif($status == 'read_later')
                			稍后阅读
                			@endif
                    		文章
                    	@endif
                    	[<a href="{{ url('articles?status=unread&feed_id='.$feed_id) }}">未读</a>]
                    	[<a href="{{ url('articles?status=read&feed_id='.$feed_id) }}">已读</a>]
                    	[<a href="{{ url('articles?status=star&feed_id='.$feed_id) }}">加星</a>]
                    	[<a href="{{ url('articles?status=read_later&feed_id='.$feed_id) }}">稍后阅读</a>]
                    	<div style="float:right">
                    		<input type="checkbox" value="" id="unable_desc"/>一目十行
                    		<input type="checkbox" value="" id="unable_img"/>屏图
                    		<a href="{{ url('feed/checkNewFeed')}}"><img alt="" src="/img/icon/refresh.png" style="width: 15px;margin-right: 10px;"></a>
                    		<a href="{{ url('feeds/explorer')}}">[发现]</a>
                    		<a href="{{ url('feeds')}}">[添加订阅]</a>
                    	</div>
                </div>
			</div>
                <!--<div class="card-body">-->
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    		@if (count($articleSubs) > 0)
                    			<?php $article_sub_ids = array();?>
	                    		@foreach ($articleSubs as $articleSub)
	                    		<?php $article = $articleSub->article;if(empty($article)) continue;$article_sub_ids[] = $articleSub->id;?>
								<div class="card" style="margin-bottom:10px">
									<div class="card-block" style="padding: 10px;">
									  @if(!empty($article->subject))
									  <h4 class="card-title">
											<a href="{{ $article->url }}">[原文]</a>
											<a href="{{ url('article/view/'.$article->id) }}">{{ $article->subject }}</a>
									  </h4>
								      @endif
									  <p class="card-text"><small class="text-muted">来源：<a href="{{ $article->feed->url}}" target="_blank">{{ $article->feed->feed_name}}</a> * {{$article->published}}</small></p>
									  
									  @if($unable_desc == "false")
									  <div class="card-text post-text">
										<show>
										<?php 
										$content = $article->content; 
										if($unable_img == "true"){ 
											$content = str_replace('src="', 'src="/img/unable_img.png" orignal_src="', $content);
										} 
										echo App\Http\Utils\CommonUtil::formatContentHtml($content); 
										?>
										</show>
									  </div>
									  <p class="card-text text-right">
											 <a href="{{ url('article/view/'.$article->id) }}#share" target="_blank" class="btn btn-outline-primary" title="分享"><img src="/img/icon/share.png" width="20px"/>Share</a>
											 <a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-outline-primary set_read @if($articleSub->status == 'read') active @endif" title="已读"><img src="/img/icon/read_already.png" width="20px"/>Read</a>
											<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-outline-primary set_read_later @if($articleSub->status == 'read_later') active @endif" title="稍后阅读"><img src="/img/icon/read_later.png" width="20px"/>Later</a>
											<a href="javascript:void(0);"  article_sub_id="{{$articleSub->id}}" class="btn btn-outline-primary set_star @if($articleSub->status == 'star') active @endif" title="加星"><img src="/img/icon/read_star.png" width="20px"/>Star</a>
											<a href="javascript:void(0);" style="display:none" class="btn btn-outline-warning view-all"><img src="/img/icon/read_more.png" width="30px"/>View All</a>
									  </p>
									  @endif
									</div>
								  </div>
								
								
	                            
								@endforeach
                        		{!! $articleSubs->appends($page_params)->links() !!}
                        		
                        		@if(!isset($_GET['status']) || $_GET['status'] == 'unread')
                        		<button class="col-md-12 btn btn-outline-primary" id="marked_all_read" ids="<?php echo implode(',', $article_sub_ids);?>">Marked All Read</button>
                        		@endif
                        @endif
                        
                        
                        @if(count($articleSubs) == 0)
                        
	                        @if(!empty($next_recommend_feed))
	                        	<div class="text-center col-md-12" style="    font-size: 20px;">
	                        		这个订阅还有{{$next_recommend_feed['feed_count']}}篇文章:
	                        		<a href="{{ url('articles?feed_id='.$next_recommend_feed['feed_id'].'&status='.$status) }}">{{$next_recommend_feed['feed_name']}}</a>
	                        	</div>
	                        @endif
	                        
	                        @if(count($recommend_feeds) > 0)
	                        		<div class="text-center col-md-12" style="    font-size: 20px;">
	                        			还可以逛逛其他的资源~
	                        		</div>
	                        		<div class="row">
				                    	@foreach($recommend_feeds as $recommend_feed)
		                    			<div class="col-md-offset-0 col-md-6">
									        <div class="product-container">  
									          <div class="name"><a href="{{ url('article/list') }}?feed_id={{$recommend_feed->id}}" >{{ substr($recommend_feed->feed_name,0) }}</a></div>  
									          <div class="intro text-right">最近更新:{{ date('d日H时',strtotime($recommend_feed->updated_at)) }} &nbsp;&nbsp; <a href="{{ url('article/list') }}?feed_id={{$recommend_feed->id}}" >去阅读</a></div>  
									        </div>  
		                    			</div>
				                    	@endforeach
	                        		</div>
	                    	@endif
	                    @endif
                <!--</div>-->
            

        </div>
        </div>
    </div>
@endsection
