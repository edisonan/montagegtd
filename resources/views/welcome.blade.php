@extends('layouts.app')

@section('title', '蒙太奇 - 知而善用，行稳致远')
@section('description', '利用番茄工作法结合待办列表来高效完成每一件事，实时统计，笔记记录，RSS阅读，思维导图，订阅推送到kindle来帮助你记录更多想法，希望它可以帮你更多')

@section('content')
    <div class="container">
        <div class="jumbotron text-center"
             style="color: white;    background-position: -1595px -18px;    background-image: url('/img/index_background2.jpg');">
            <h3 style="padding-bottom: 20px;">蒙太奇 - 知而善用，行稳致远</h3>
            <p class="lead" style="padding-bottom: 100px;">不止于GTD，陪你做好每件事，在这里定格更多精彩瞬间。</p>
            <p>
                <a class="btn btn-lg btn-primary" href="{{url('/login')}}" role="button">现在就开始吧</a>
            </p>
        </div>
    </div> <!-- /container -->
@endsection
