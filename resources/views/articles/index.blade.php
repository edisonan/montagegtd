@extends('layouts.app')

@section('content')
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
			    							[
			    							@if($status == 'unread')
			    								{{$feed->unread_count}}
			    							@else
			    								{{$feed->read_count}}
			    							@endif
			    							]
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
                    	新的文章[<a href="{{ url('articles') }}">未读</a>][<a href="{{ url('articles?status=read') }}">已读</a>][<a href="{{ url('articles?status=star') }}">加星</a>]
                    	<div style="float:right">
                    		<a href="{{ url('feed/checkNewFeed')}}">[更新?]</a>
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
										<h1 class="post-title"><a href="{{ url('article/view/'.$article->id) }}">{{ $article->subject }}</a></h1>
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
									<div class="post-content">
										<p></p>
										<p><?php echo App\Http\Utils\CommonUtil::removeXSS($article->content); ?></p>
										<p></p>
									</div>
									<div class="post-permalink">
										<a href="{{$article->url}}" target="_blank" class="btn btn-default">阅读全文</a>
									</div>
									<footer class="post-footer clearfix"></footer>
								</article>
								@endforeach
                        		{!! $articles->links() !!}
                        @endif
                </div>
            </div>

        </div>
    </div>
@endsection
