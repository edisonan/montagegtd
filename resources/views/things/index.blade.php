@extends('layouts.app')
<style>
    .rowone {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
    }
    /* 新增样式 */
    .quick-time-btn {
        margin: 2px;
        padding: 4px 8px;
        font-size: 0.85rem;
    }
    .time-control-group {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 5px;
    }
    .duration-select {
        max-width: 150px;
    }
    .time-preview {
        font-size: 0.9rem;
        color: #666;
        margin-top: 5px;
    }
    .form-group.row {
        margin-bottom: 1.2rem;
    }
</style>
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            // // 初始化时间预览
            // updateTimePreview();
            //
            // // 设置默认时间
            // setDefaultTimes();

            $(".delete_thing").click(function () {
                thing_value = $(this).attr("thing_value");
                thing_token = $(this).attr("thing_token");
                thing_type = $(this).attr("thing_type");

                if (thing_type == 'delete' && !confirm("确认要删除此事情咩？")) {
                    return false;
                }

                $.ajax({
                    url: "{{ url('thing') }}" + "/" + thing_value,
                    type: 'DELETE',
                    data: {type: thing_type, _token: thing_token},
                    success: function (result_arr) {
                        if (result_arr.code != 9999) {
                            alert(result_arr.msg);
                        } else {
                            $('#' + thing_value).remove();
                        }
                    }
                });
            });

        //     // 时长选择变化事件
        //     $('#duration').change(function() {
        //         var duration = parseInt($(this).val());
        //         var startTime = $('#start_time').val();
        //         if (startTime) {
        //             var start = new Date(startTime);
        //             var end = new Date(start.getTime() + duration * 60000);
        //             $('#end_time').val(formatDate(end));
        //             updateTimePreview();
        //         }
        //     });
        //
        //     // 时间输入变化事件
        //     $('#start_time, #end_time').change(function() {
        //         updateTimePreview();
        //     });
        //
        //     // 快速时间按钮点击事件
        //     $('.quick-time-btn').click(function() {
        //         var minutes = parseInt($(this).data('minutes'));
        //         var now = new Date();
        //         var start = new Date(now.getTime() - minutes * 60000);
        //
        //         $('#start_time').val(formatDate(start));
        //         $('#end_time').val(formatDate(now));
        //         updateTimePreview();
        //     });
        // });
        //
        // // 格式化日期为字符串
        // function formatDate(date) {
        //     return date.getFullYear() + '-' +
        //         ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
        //         ('0' + date.getDate()).slice(-2) + ' ' +
        //         ('0' + date.getHours()).slice(-2) + ':' +
        //         ('0' + date.getMinutes()).slice(-2) + ':00';
        // }
        //
        // // 设置默认时间
        // function setDefaultTimes() {
        //     var now = new Date();
        //     var roundedNow = new Date(Math.round(now.getTime() / 600000) * 600000);
        //     var tenMinutesAgo = new Date(roundedNow.getTime() - 10 * 60000);
        //
        //     $('#start_time').val(formatDate(tenMinutesAgo));
        //     $('#end_time').val(formatDate(roundedNow));
        // }
        //
        // // 更新时间预览
        // function updateTimePreview() {
        //     // 检查当前上下文是模态框还是原页面
        //     var $context = $('#thingCreateModal').is(':visible') ? $('#thingCreateModal') : $(document);
        //     var startTime = $context.find('#start_time').val();
        //     var endTime = $context.find('#end_time').val();
        //
        //     if (startTime && endTime) {
        //         var start = new Date(startTime);
        //         var end = new Date(endTime);
        //         var duration = Math.round((end - start) / 60000); // 分钟
        //
        //         var hours = Math.floor(duration / 60);
        //         var minutes = duration % 60;
        //
        //         var durationText = '';
        //         if (hours > 0) {
        //             durationText += hours + '小时';
        //         }
        //         if (minutes > 0 || hours === 0) {
        //             durationText += minutes + '分钟';
        //         }
        //
        //         $context.find('#time-preview').text('总计时长: ' + durationText);
        //     }
        // }
        //
        // Date.prototype.format = function (fmt) {
        //     var o = {
        //         "M+": this.getMonth() + 1, //月份
        //         "d+": this.getDate(), //日
        //         "h+": this.getHours(), //小时
        //         "m+": this.getMinutes(), //分
        //         "s+": this.getSeconds(), //秒
        //         "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        //         S: this.getMilliseconds(), //毫秒
        //     };
        //     if (/(y+)/.test(fmt)) {
        //         fmt = fmt.replace(
        //             RegExp.$1,
        //             (this.getFullYear() + "").substr(4 - RegExp.$1.length)
        //         );
        //     }
        //     for (var k in o) {
        //         if (new RegExp("(" + k + ")").test(fmt)) {
        //             fmt = fmt.replace(
        //                 RegExp.$1,
        //                 RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length)
        //             );
        //         }
        //     }
        //     return fmt;
        // };
        //
        // function changeTime($id, $interval) {
        //     $idName = "#" + $id;
        //     $time = Number(new Date($($idName).val()).getTime());
        //     $time = $time + $interval * 60000;
        //     $($idName).val(new Date($time).format("yyyy-MM-dd hh:mm:ss"));
        //     updateTimePreview();
        // }
    </script>
    <div class="container">
        <div class="col-md-12">
            @include('common.success')

            <!-- 原有的列表部分保持不变 -->
            <div class="card" style="margin-top: 10px;">
                <div class="card-header">
                    新完成事情记录
                    <div style="float:right">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#thingCreateModal">
                            新增事情
                        </button>
                        <a href="{{'/index'}}" class="btn btn-sm btn-outline-secondary">返回</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (count($things) > 0)
                        <table class="table table-hover thing-table">
                            <thead>
                            <th>完成的事情</th>
                            <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                <?php $lastDate = '';?>
                            @foreach ($things as $thing)
                                <tr id="{{$thing->id}}">
                                    <td class="table-text">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                    <?php
                                                    $currentDate = date('m-d', strtotime($thing->start_time));
                                                    if ($currentDate != $lastDate) {
                                                        $lastDate = $currentDate;
                                                        $style = "";
                                                    } else {
                                                        $style = "color:rgba(0,0,0,0);";
                                                    }
                                                    ?>
                                                <span style="{{ $style}}">{{ $currentDate }}</span>
                                                <small> {{ date('H:i', strtotime($thing->start_time)) }}-{{ date('H:i', strtotime($thing->end_time)) }} </small>
                                            </div>
                                            <div>
                                                <img alt="" style="width: 15px;"
                                                     src="/img/icon/thing{{ $thing->type }}.png">
                                                <span title="{{ $thing->name }}" style="font-weight: 500;"> {{ $thing->name }}  </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ url('thing/'.$thing->id)}}" style="">
                                            <i class="bi-pencil-square" style="font-size: 1.5rem;"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="delete_thing" thing_type="delete"
                                           thing_value="{{ $thing->id }}" thing_token="{{ csrf_token() }}"
                                           style="cursor:pointer;">
                                            <i class="bi-trash" style="font-size: 1.5rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $things->links() !!}
                    @else
                        <div>
                            <img src="/img/new/love.png" width="200px">
                            暂时还没有事情哦，快去<a href="{{url('/index')}}" style="color:green">开始做点番茄或者考虑一下待办</a>吧！
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('components.thing-create-modal')
@endsection