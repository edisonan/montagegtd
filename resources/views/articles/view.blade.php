@extends('layouts.app')

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
                     -->
                    	<a href="{{ $article->url }}" target="">
                    	{{ $article->subject }}
                    	</a>
                    	<div style="float:right">
                    		<a href="{{'/feeds'}}">[添加订阅]</a>
                    		<a href="{{'/articles'}}">[继续阅读]</a>
                    	</div>
                </div>

                <div class="panel-body">

                    <?php echo  $article->content; ?>
                    
                </div>
            </div>

        </div>
    </div>
@endsection
