<div class="modal fade" id="thingCreateModal" tabindex="-1" aria-labelledby="thingCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thingCreateModalLabel">新增事情记录</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('common.errors')

                <form id="thingCreateForm" action="{{ url('thing') }}" method="POST" class="form-horizontal">
                    {{ csrf_field() }}

                    <!-- 事情内容 -->
                    <div class="form-group row mb-3">
                        <label for="thing-name" class="col-md-2 col-form-label">完成内容</label>
                        <div class="col-md-10">
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ old('thing') }}" placeholder="请输入完成的事情内容" required>
                        </div>
                    </div>

                    <!-- 快速时间选择 -->
                    <div class="form-group row mb-3">
                        <label class="col-md-2 col-form-label">快速选择</label>
                        <div class="col-md-10">
                            <div class="time-control-group">
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-time-btn" data-minutes="10">刚刚10分钟</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-time-btn" data-minutes="25">25分钟前</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-time-btn" data-minutes="60">1小时前</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-time-btn" data-minutes="120">2小时前</button>
                            </div>
                        </div>
                    </div>

                    <!-- 开始时间 -->
                    <div class="form-group row mb-3">
                        <label for="start_time" class="col-md-2 col-form-label">开始时间</label>
                        <div class="col-md-10">
                            <div class="input-group">
                                <input type="text" name="start_time" id="start_time" class="form-control"
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeTime('start_time', -5)">-5m</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeTime('start_time', 5)">+5m</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 结束时间 -->
                    <div class="form-group row mb-3">
                        <label for="end_time" class="col-md-2 col-form-label">结束时间</label>
                        <div class="col-md-10">
                            <div class="input-group">
                                <input type="text" name="end_time" id="end_time" class="form-control"
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeTime('end_time', -5)">-5m</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeTime('end_time', 5)">+5m</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 时长选择和预览 -->
                    <div class="form-group row mb-3">
                        <label class="col-md-2 col-form-label">常用时长</label>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-4">
                                    <select id="duration" class="form-control duration-select">
                                        <option value="">选择时长自动设置结束时间</option>
                                        <option value="5">5分钟</option>
                                        <option value="10">10分钟</option>
                                        <option value="15">15分钟</option>
                                        <option value="25">25分钟</option>
                                        <option value="30">30分钟</option>
                                        <option value="45">45分钟</option>
                                        <option value="60">60分钟</option>
                                        <option value="90">90分钟</option>
                                        <option value="120">120分钟</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <div id="time-preview" class="time-preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('thingCreateForm').submit()">提交记录</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 时间调整函数 - 与主页面保持一致
    function changeTime($id, $interval) {
        $idName = "#" + $id;
        $time = Number(new Date($($idName).val()).getTime());
        $time = $time + $interval * 60000;
        $($idName).val(new Date($time).format("yyyy-MM-dd hh:mm:ss"));
        updateTimePreview();
    }
    
    // 设置默认时间函数
    function setDefaultTimes() {
        var now = new Date();
        var roundedNow = new Date(Math.round(now.getTime() / 600000) * 600000);
        var tenMinutesAgo = new Date(roundedNow.getTime() - 10 * 60000);

        $('#start_time').val(formatDate(tenMinutesAgo));
        $('#end_time').val(formatDate(roundedNow));
    }
    
    // 格式化日期为字符串
    function formatDate(date) {
        return date.getFullYear() + '-' +
            ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
            ('0' + date.getDate()).slice(-2) + ' ' +
            ('0' + date.getHours()).slice(-2) + ':' +
            ('0' + date.getMinutes()).slice(-2) + ':00';
    }
    
    // 更新时间预览
    function updateTimePreview() {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();

        if (startTime && endTime) {
            var start = new Date(startTime);
            var end = new Date(endTime);
            var duration = Math.round((end - start) / 60000); // 分钟

            var hours = Math.floor(duration / 60);
            var minutes = duration % 60;

            var durationText = '';
            if (hours > 0) {
                durationText += hours + '小时';
            }
            if (minutes > 0 || hours === 0) {
                durationText += minutes + '分钟';
            }

            $('#time-preview').text('总计时长: ' + durationText);
        }
    }
    
    Date.prototype.format = function (fmt) {
        var o = {
            "M+": this.getMonth() + 1, //月份
            "d+": this.getDate(), //日
            "h+": this.getHours(), //小时
            "m+": this.getMinutes(), //分
            "s+": this.getSeconds(), //秒
            "q+": Math.floor((this.getMonth() + 3) / 3), //季度
            S: this.getMilliseconds(), //毫秒
        };
        if (/(y+)/.test(fmt)) {
            fmt = fmt.replace(
                RegExp.$1,
                (this.getFullYear() + "").substr(4 - RegExp.$1.length)
            );
        }
        for (var k in o) {
            if (new RegExp("(" + k + ")").test(fmt)) {
                fmt = fmt.replace(
                    RegExp.$1,
                    RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length)
                );
            }
        }
        return fmt;
    };

    // 快速时间选择功能
    document.addEventListener('DOMContentLoaded', function() {
        $('.quick-time-btn').click(function() {
            var minutes = parseInt($(this).data('minutes'));
            var now = new Date();
            var start = new Date(now.getTime() - minutes * 60000);

            $('#start_time').val(formatDate(start));
            $('#end_time').val(formatDate(now));
            updateTimePreview();
        });
        
        // 时长选择变化事件
        $('#duration').change(function() {
            var duration = parseInt($(this).val());
            var startTime = $('#start_time').val();
            if (startTime) {
                var start = new Date(startTime);
                var end = new Date(start.getTime() + duration * 60000);
                $('#end_time').val(formatDate(end));
                updateTimePreview();
            }
        });

        // 时间输入变化事件
        $('#start_time, #end_time').change(function() {
            updateTimePreview();
        });
    });
</script>