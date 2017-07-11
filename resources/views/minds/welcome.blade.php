@extends('layouts.app')

@section('title', '思维导图 - Montage GTD')
@section('description', 'Montage GTD思维导图这里通过思维导图来总结你的每一个想法，发散思维，认真思考每一个想法！')


@section('content')
    <div class="container">
    
        <div class="col-sm-offset-0 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
               		 思维导图
                </div>

                <div class="panel-body">
                	<div style="margin-bottom: 30px;margin-top: 10px;">
                		<p>
                			<b>思维导图</b>是Montage GTD的一项子栏目，这里通过思维导图来总结你的每一个想法，发散思维，认真思考每一个想法！<a rel="nofollow" href="{{url('/minds')}}">马上去体验！</a>
                		</p>
                	</div>
					<img alt="" src="/img/mind.png" class="col-sm-offset-1 col-sm-10">
                </div>
            </div>

        </div>
    </div>
@endsection
