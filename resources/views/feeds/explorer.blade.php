@extends('layouts.app')

@section('content')
    <style>

        .rowone {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
            padding-left: 10px;
            padding-top: 10px;
        }

        .suggest_feed_title {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
            padding-left: 10px;
            padding-top: 10px;
            max-width: 60%;
        }

        .suggest_feed_content {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            display: box;
            -webkit-box-orient: vertical;
            box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            padding-left: 10px;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            min-height: calc(2 * 1.5em); /* 根据行高和行数计算最小高度，这里假设行高为 1.5em，显示两行 */
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function () {

            $(".feed_quick_sub").click(function () {
                var feed_id = $(this).attr('feed_id');
                $.get("{{ url('/feeds/quickstore') }}", {"feed_id": feed_id}, function (result_arr) {
                    if (result_arr.code != 9999) {
                        alert(result_arr.msg);
                    } else {
                        alert(result_arr.msg);
                    }
                });
            });

        });
    </script>
    <div class="container">

            <div class="card" style="margin-bottom: 10px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>订阅推荐</div>
                    <div class="d-flex align-items-center">
                        <form action="/feeds/search" class="d-flex">
                            <input type="text" value="" name="name" class="form-control me-2" placeholder="请输入想要搜索的关键字"/>
                            <button type="submit" class="btn btn-primary">即刻搜索</button>
                        </form>
{{--                        [<a href="{{ url('articles') }}">返回阅读</a>]--}}
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3" style="padding:10px">
                            <div class="card card-block" style="text-align:center">
                                <b class="card-title rowone">
                                    <a href="/feeds">直接订阅</a>
                                </b>
                            </div>
                        </div>
                        <div class="col-md-3" style="padding:10px">
                            <div class="card card-block" style="text-align:center">
                                <b class="card-title rowone">
                                    <a href="/feeds/weiborss">订阅微博</a>
                                </b>
                            </div>
                        </div>
                        <div class="col-md-3" style="padding:10px">
                            <div class="card card-block" style="text-align:center">
                                <b class="card-title rowone">
                                    <a href="/feeds/weixinrss">订阅公众号</a>
                                </b>
                            </div>
                        </div>
                        <div class="col-md-3" style="padding:10px">
                            <div class="card card-block" style="text-align:center">
                                <b class="card-title rowone">
                                    <a href="/feeds/opml">OPML导入</a>
                                </b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="card" style="margin-bottom: 10px;">
            <div class="card-header">
                分类订阅
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($recommend_categorys as $id => $name)
                        <div class="col-md-3" style="padding:10px;">
                            <div class="card card-block" style="text-align:center;">
                                <b class="card-title rowone">
                                    <a href="/feeds/search?recommend_category_id={{ $id }}">{{ $name }}</a>
                                </b>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

            <div class="card" style="margin-bottom: 10px;">
                <div class="card-header">
                    推荐订阅源
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    @if (count($feeds) > 0)

                        <div class="row">
                            @foreach ($feeds as $feed)
                                <div class="col-md-4" style="padding:10px">
                                    <div class="card card-block">
                                        <b class="card-title suggest_feed_title">
                                            <img alt="" style="width: 15px;" src="{{ $feed->favicon }}">
                                            <a href="{{ url('article/list') }}?feed_id={{$feed->id}}">{{ $feed->feed_name }}</a>
                                        </b>
                                        <p class="card-text suggest_feed_content"><small>{{ $feed->feed_desc }} &nbsp;</small></p>
                                        <a class="card-link text-right" style="padding:10px"
                                           href="javascript:void(0)" feed_id="{{ $feed->id }}" class="feed_quick_sub">直接订阅</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <br>
                        {!! $feeds->links() !!}
                    @endif
                </div>
            </div>


        </div>
    </div>
@endsection
