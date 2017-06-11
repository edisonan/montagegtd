@extends('layouts.app')

@section('content')
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                		<span style="color: yellow">
                    	@if($article->status == 'star')
                    	<a href="{{ url('article/star/'.$article->id) }}">★</a>
                    	@else
                    	<a href="{{ url('article/star/'.$article->id) }}">☆</a>
                    	@endif;
                    	</span>
                    	
                    	<a href="{{ $article->url }}" target="">
                    	{{ $article->subject }}
                    	</a>
                </div>

                <div class="panel-body">

                    <?php echo  $article->content; ?>
                    
                </div>
            </div>

        </div>
    </div>
@endsection
