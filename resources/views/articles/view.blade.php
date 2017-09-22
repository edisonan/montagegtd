@extends('layouts.article')

@section('title', $article->subject.' - Montage GTD')

@section('content')
<script src="/js/lazyload.min.js"></script>
<script type="text/javascript" charset="utf-8">
  $(function() {
	//lazy img
	$("img.lazy").lazyload();
  });
</script>
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                	<!-- 
                	
                		<span style="color: yellow">
                    	<a href="{{ url('article/star/'.$article->id) }}">★</a>
                    	<a href="{{ url('article/star/'.$article->id) }}">☆</a>
                    	</span>
                    	<a href="{{ $article->url }}" target="">
                    		全文
                    	</a>
                     -->
                     {{$article->feed->feed_name}}
                    	<div style="float:right">
                    		@if(!$is_feed)
                    		<a href="{{ '/feeds?url='.$article->feed->url }}">[添加订阅]</a>
                    		@endif
                    		<a href="{{'/articles'}}">[继续阅读]</a>
                    	</div>
                </div>

                <div class="panel-body">
					<article class="post">
						@if(!empty($article->subject))
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
						@endif
						
						<div class="post-content" style="    margin: 5px 0;">
							<p></p>
							<p>											
								<?php echo  App\Http\Utils\CommonUtil::removeXSS($article->content); ?>
							</p>
							<p></p>
						</div>
						<footer class="post-footer clearfix">
							<div style="float: right">
								<a id="share" name="share"></a>
								<wb:share-button appkey="567683707" addition="simple" type="button" ralateUid="1671353227" title="{{ $article->subject }}" url="<?php echo $_SERVER['REQUEST_URI'];?>" pic="{{ $article->image_url }}"></wb:share-button>
							</div>
						</footer>
					</article>
                </div>
            </div>

        </div>
    </div>
@endsection
