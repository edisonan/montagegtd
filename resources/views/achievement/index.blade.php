@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-md-12">
            @include('common.success')

            <div class="card">
                <div class="card-header">
                    成就中心
                </div>

                <div class="card-body">
                    @include('common.errors')

                    {{-- 自动成就 --}}
                    <h5>成就</h5>

                    @php
                        $achievementList = $list->where('category', 'achievement');
                    @endphp

                    @if($achievementList->isEmpty())
                        <p class="text-muted">暂无成就配置</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>成就</th>
                                <th>描述</th>
                                <th style="width: 120px;">奖励</th>
                                <th style="width: 140px;">状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($achievementList as $item)
                                <tr>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td>
                                        {{ $item->description }}
                                    </td>
                                    <td>
                                        @if($item->point_value > 0)
                                            {{ $item->point_value }} GP
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->achieved)
                                            <span class="text-success">
                                            已获得<br>
                                            <small>{{ $item->achieved_at }}</small>
                                        </span>
                                        @else
                                            <span class="text-muted">未完成</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif

                    <hr>

                    {{-- 勋章 --}}
                    <h5>勋章</h5>

                    @php
                        $badgeList = $list->where('category', 'badge');
                    @endphp

                    @if($badgeList->isEmpty())
                        <p class="text-muted">暂无勋章配置</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>勋章</th>
                                <th>描述</th>
                                <th style="width: 120px;">奖励</th>
                                <th style="width: 160px;">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($badgeList as $item)
                                <tr>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td>
                                        {{ $item->description }}
                                    </td>
                                    <td>
                                        @if($item->point_value > 0)
                                            {{ $item->point_value }} GP
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->achieved)
                                            <span class="text-success">
                                            已领取<br>
                                            <small>{{ $item->achieved_at }}</small>
                                        </span>
                                        @else
                                            <form method="POST" action="{{ url('/achievement/claim') }}">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="achievement_code"
                                                       value="{{ $item->code }}">
                                                <button type="submit"
                                                        class="btn btn-sm btn-primary">
                                                    领取
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
