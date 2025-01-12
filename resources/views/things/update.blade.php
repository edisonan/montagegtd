@extends('layouts.app') @section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                修改事情记录
                <div style="float: right">
                    <a href="{{'/things'}}">[返回]</a>
                </div>
            </div>

            <div class="card-body">
                <!-- Display Validation Errors -->
            @include('common.errors')

            <!-- New Task Form -->
                <form action="{{ url('thing/'.$thing->id) }}" method="POST"
                      class="form-horizontal">

                {{ csrf_field() }}

                <!-- thing Name -->
                    <div class="form-group row">
                        <label for="thing-name" class="col-md-3 control-label">完事内容</label>

                        <div class="col-md-8">
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ $thing->name }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="thing-name" class="col-md-3 control-label">完事时间</label>

                        <div class="col-md-8">
                            <div class="col-md-12" style="padding-left: 0px;">
                                <a href="javascript:void(0);" style="float: right"
                                   onclick="changeTime('start_time', 5)">&nbsp;+5分钟</a> <a
                                        href="javascript:void(0);" style="float: right"
                                        onclick="changeTime('start_time', -5)">&nbsp;-5分钟</a> <input
                                        type="text" name="start_time" id="start_time"
                                        class="form-control" value="{{ $thing->start_time }}"
                                        onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:m:00',maxDate:'%y-%M-%d'})">
                            </div>
                            -
                            <div class="col-md-12" style="padding-left: 0px;">
                                <a href="javascript:void(0);" style="float: right"
                                   onclick="changeTime('end_time', 5)">&nbsp;+5分钟</a> <a
                                        href="javascript:void(0);" style="float: right"
                                        onclick="changeTime('end_time', -5)">&nbsp;-5分钟</a> <input
                                        type="text" name="end_time" id="end_time" class="form-control"
                                        value="{{ $thing->end_time }}"
                                        onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:m:00',maxDate:'%y-%M-%d'})">
                            </div>
                        </div>
                    </div>

                    <!-- Add thing Button -->
                    <div class="form-group row">
                        <div class="col-md-offset-3 col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-btn fa-plus"></i>提交！
                            </button>
                        </div>
                    </div>
                </form>


            </div>
        </div>
    </div>
    </div>
@endsection
