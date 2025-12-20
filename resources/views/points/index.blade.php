@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-md-12">
            @include('common.success')

            <div class="card">
                <div class="card-header">
                    积分中心
                </div>

                <div class="card-body">
                    @include('common.errors')

                    {{-- 积分概览 --}}
                    <div class="form-group row">
                        <label class="col-md-3 control-label">成长积分（GP）</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                {{ $account->gp_balance }}
                            </p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 control-label">可用积分（AP）</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                {{ $account->ap_balance }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    {{-- 积分流水 --}}
                    <h5>积分变动记录</h5>

                    @if($records->isEmpty())
                        <p class="text-muted">暂无积分记录</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th style="width: 80px;">类型</th>
                                <th style="width: 100px;">变动</th>
                                <th>说明</th>
                                <th style="width: 160px;">时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($records as $record)
                                <tr>
                                    <td>
                                        {{ $record->point_type }}
                                    </td>
                                    <td>
                                        @if($record->change_amount > 0)
                                            <span class="text-success">
                                            +{{ $record->change_amount }}
                                        </span>
                                        @else
                                            <span class="text-danger">
                                            {{ $record->change_amount }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $record->description }}
                                    </td>
                                    <td>
                                        {{ $record->created_at }}
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
