@extends('layouts.app')

@section('content')
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	{{ $article->subject }}
                </div>

                <div class="panel-body">

                    {{ $article->content }}
                    
                </div>
            </div>

        </div>
    </div>
@endsection
