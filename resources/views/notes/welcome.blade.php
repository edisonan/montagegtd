@extends('layouts.app')

@section('title', '记想法 - Montage GTD')
@section('description', 'Montage GTD记想法这里支持通过插件快速分享chrome等浏览器所浏览的网站、图片与文字，同时可以你可以实时去记录你的想法，其更支持语音录入极大方便你的学习生活')


@section('content')
    <div class="container">
    
        <div class="col-sm-offset-0 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
               		 记想法
                </div>

                <div class="panel-body">
                	<div style="margin-bottom: 30px;margin-top: 10px;">
                		<p>
                			<b>记想法</b>是Montage GTD的一项子栏目，这里支持通过插件快速分享chrome等浏览器所浏览的网站、图片与文字，同时可以你可以实时去记录你的想法，其更支持语音录入极大方便你的学习生活！<a rel="nofollow" href="{{url('/notes')}}">马上去体验！</a>
                		</p>
                	</div>
					<img alt="" src="/img/note.png" class="col-sm-offset-1 col-sm-10">
                </div>
            </div>

        </div>
    </div>
@endsection
