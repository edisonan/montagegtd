<!-- 引入日期选择器 -->
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

<!-- 新增事情记录模态框 -->
<div id="thingCreateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl transform transition-all">
            <!-- 模态框头部 -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center space-x-2">
                    <h3 class="text-xl font-semibold text-gray-900">新增事情记录</h3>
                    <span class="text-gray-400">|</span>
                    <a href="/things" class="text-blue-600 hover:text-blue-800 hover:underline text-sm font-medium">
                        <i class="fas fa-external-link-alt mr-1"></i>查看事情列表
                    </a>
                </div>
                <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                        onclick="hideThingCreateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- 模态框内容 -->
            <div class="p-6">
                @include('common.errors')

                <form id="thingCreateForm" action="{{ url('thing') }}" method="POST" class="space-y-6">
                    {{ csrf_field() }}

                    <!-- 事情内容 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="name" class="font-medium text-gray-700">
                            <i class="fas fa-tasks mr-2 text-blue-500"></i>完成内容
                        </label>
                        <div class="md:col-span-3">
                            <input type="text" name="name" id="name"
                                   class="input w-full"
                                   value="{{ old('thing') }}"
                                   placeholder="请输入完成的事情内容"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <!-- 快速时间选择 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-medium text-gray-700">
                            <i class="fas fa-bolt mr-2 text-purple-500"></i>快速选择
                        </div>
                        <div class="md:col-span-3">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline quick-time-btn" data-minutes="10">
                                    <i class="far fa-clock mr-1"></i>刚刚10分钟
                                </button>
                                <button type="button" class="btn btn-sm btn-outline quick-time-btn" data-minutes="25">
                                    <i class="fas fa-hourglass-start mr-1"></i>25分钟前
                                </button>
                                <button type="button" class="btn btn-sm btn-outline quick-time-btn" data-minutes="60">
                                    <i class="fas fa-hourglass-half mr-1"></i>1小时前
                                </button>
                                <button type="button" class="btn btn-sm btn-outline quick-time-btn" data-minutes="120">
                                    <i class="fas fa-hourglass-end mr-1"></i>2小时前
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 时间选择器组 -->
                    <div class="space-y-6">
                        <!-- 开始时间 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                            <label for="start_time" class="font-medium text-gray-700">
                                <i class="fas fa-play mr-2 text-green-500"></i>开始时间
                            </label>
                            <div class="md:col-span-3">
                                <div class="flex items-center space-x-3">
                                    <div class="relative flex-1">
                                        <input type="text" name="start_time" id="start_time"
                                               class="input w-full pl-10 datepicker-input"
                                               onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                               placeholder="选择开始时间">
                                        <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" class="btn btn-sm btn-outline time-adjust-btn"
                                                onclick="changeTime('start_time', -5)">
                                            <i class="fas fa-minus"></i>5m
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline time-adjust-btn"
                                                onclick="changeTime('start_time', 5)">
                                            <i class="fas fa-plus"></i>5m
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 结束时间 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                            <label for="end_time" class="font-medium text-gray-700">
                                <i class="fas fa-stop mr-2 text-red-500"></i>结束时间
                            </label>
                            <div class="md:col-span-3">
                                <div class="flex items-center space-x-3">
                                    <div class="relative flex-1">
                                        <input type="text" name="end_time" id="end_time"
                                               class="input w-full pl-10 datepicker-input"
                                               onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                               placeholder="选择结束时间">
                                        <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" class="btn btn-sm btn-outline time-adjust-btn"
                                                onclick="changeTime('end_time', -5)">
                                            <i class="fas fa-minus"></i>5m
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline time-adjust-btn"
                                                onclick="changeTime('end_time', 5)">
                                            <i class="fas fa-plus"></i>5m
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 时长选择和预览 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-medium text-gray-700">
                            <i class="fas fa-history mr-2 text-orange-500"></i>时长设置
                        </div>
                        <div class="md:col-span-3 space-y-4">
                            <!-- 常用时长选择 -->
                            <div class="flex items-center space-x-4">
                                <div class="relative flex-1">
                                    <select id="duration" class="input w-full duration-select">
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
                                    <i class="fas fa-clock absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" onclick="setDefaultTimes()">
                                    <i class="fas fa-redo mr-1"></i>重置时间
                                </button>
                            </div>

                            <!-- 时间预览 -->
                            <div id="time-preview" class="time-preview card p-4 bg-blue-50 border border-blue-200">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-blue-700">时间预览</div>
                                        <div class="text-sm text-blue-600">请先设置开始和结束时间</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 模态框底部 -->
            <div class="flex items-center justify-end p-6 border-t border-gray-200 space-x-3">
                <button type="button" class="btn btn-outline" onclick="hideThingCreateModal()">
                    <i class="fas fa-times mr-2"></i>取消
                </button>
                <button type="button" class="btn btn-primary" onclick="submitThingCreateForm()">
                    <i class="fas fa-check mr-2"></i>提交记录
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .datepicker-input {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .datepicker-input:hover {
        border-color: var(--primary-color);
    }

    .time-preview {
        min-height: 60px;
        display: flex;
        align-items: center;
    }

    .quick-time-btn {
        transition: all 0.2s ease;
    }

    .quick-time-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .time-adjust-btn {
        min-width: 60px;
    }

    /* 时长选择器样式 */
    .duration-select {
        cursor: pointer;
    }

    /* 错误样式 */
    .has-error {
        border-color: var(--danger-color);
    }

    .error-message {
        color: var(--danger-color);
        font-size: 12px;
        margin-top: 4px;
    }
</style>

<script>
    let isThingModalOpen = false;

    // 模态框控制函数
    function showThingCreateModal() {
        const modal = document.getElementById('thingCreateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        isThingModalOpen = true;

        // 添加动画效果
        setTimeout(() => {
            const dialog = modal.querySelector('.bg-white');
            dialog.classList.add('scale-100', 'opacity-100');
            dialog.classList.remove('scale-95', 'opacity-0');
        }, 10);

        // 设置默认时间
        setDefaultTimes();
        updateTimePreview();

        // 自动聚焦到内容输入框
        setTimeout(() => {
            document.getElementById('name').focus();
        }, 300);
    }

    function hideThingCreateModal() {
        const modal = document.getElementById('thingCreateModal');
        const dialog = modal.querySelector('.bg-white');

        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            isThingModalOpen = false;
        }, 300);
    }

    // 表单提交函数
    function submitThingCreateForm() {
        const form = document.getElementById('thingCreateForm');

        // 验证必填字段
        const nameInput = document.getElementById('name');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');

        let hasError = false;

        // 清除之前的错误样式
        [nameInput, startTimeInput, endTimeInput].forEach(input => {
            input.classList.remove('has-error');
            const errorMsg = input.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.remove();
            }
        });

        // 验证内容
        if (!nameInput.value.trim()) {
            showFieldError(nameInput, '请填写完成内容');
            hasError = true;
        }

        // 验证开始时间
        if (!startTimeInput.value) {
            showFieldError(startTimeInput, '请选择开始时间');
            hasError = true;
        }

        // 验证结束时间
        if (!endTimeInput.value) {
            showFieldError(endTimeInput, '请选择结束时间');
            hasError = true;
        }

        // 验证时间顺序
        if (startTimeInput.value && endTimeInput.value) {
            const startTime = new Date(startTimeInput.value);
            const endTime = new Date(endTimeInput.value);

            if (endTime <= startTime) {
                showFieldError(endTimeInput, '结束时间必须晚于开始时间');
                hasError = true;
            }
        }

        if (!hasError) {
            form.submit();
        }
    }

    // 显示字段错误
    function showFieldError(input, message) {
        input.classList.add('has-error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        input.parentNode.insertBefore(errorDiv, input.nextSibling);

        // 滚动到错误位置
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

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
        updateTimePreview();
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
        var timePreview = $('#time-preview');

        if (startTime && endTime) {
            var start = new Date(startTime);
            var end = new Date(endTime);

            // 检查时间顺序
            if (end <= start) {
                timePreview.html(`
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                        <div>
                            <div class="font-medium text-red-700">时间错误</div>
                            <div class="text-sm text-red-600">结束时间必须晚于开始时间</div>
                        </div>
                    </div>
                `);
                return;
            }

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

            // 格式化显示时间
            var formattedStart = start.getHours().toString().padStart(2, '0') + ':' +
                start.getMinutes().toString().padStart(2, '0');
            var formattedEnd = end.getHours().toString().padStart(2, '0') + ':' +
                end.getMinutes().toString().padStart(2, '0');

            timePreview.html(`
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <div class="text-xs text-gray-500">开始时间</div>
                        <div class="font-medium text-gray-900">${formattedStart}</div>
                        <div class="text-xs text-gray-400">${start.getMonth()+1}月${start.getDate()}日</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-gray-500">结束时间</div>
                        <div class="font-medium text-gray-900">${formattedEnd}</div>
                        <div class="text-xs text-gray-400">${end.getMonth()+1}月${end.getDate()}日</div>
                    </div>
                    <div class="col-span-2">
                        <div class="mt-2 p-3 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-hourglass-half text-green-500 mr-2"></i>
                                    <span class="font-bold text-green-700">总计时长: ${durationText}</span>
                                </div>
                                <span class="text-sm text-gray-500">${duration} 分钟</span>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        } else {
            timePreview.html(`
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                    <div>
                        <div class="font-medium text-blue-700">时间预览</div>
                        <div class="text-sm text-blue-600">请先设置开始和结束时间</div>
                    </div>
                </div>
            `);
        }
    }

    // 日期格式化函数
    Date.prototype.format = function (fmt) {
        var o = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S": this.getMilliseconds(),
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

    // 初始化事件监听
    $(document).ready(function() {
        // 快速时间选择功能
        $('.quick-time-btn').click(function() {
            var minutes = parseInt($(this).data('minutes'));
            var now = new Date();
            var start = new Date(now.getTime() - minutes * 60000);

            $('#start_time').val(formatDate(start));
            $('#end_time').val(formatDate(now));
            updateTimePreview();

            // 按钮反馈效果
            $(this).addClass('btn-primary').removeClass('btn-outline');
            setTimeout(() => {
                $(this).removeClass('btn-primary').addClass('btn-outline');
            }, 300);
        });

        // 时长选择变化事件
        $('#duration').change(function() {
            var duration = parseInt($(this).val());
            var startTime = $('#start_time').val();
            if (startTime && duration) {
                var start = new Date(startTime);
                var end = new Date(start.getTime() + duration * 60000);
                $('#end_time').val(formatDate(end));
                updateTimePreview();
            }
        });

        // 时间输入变化事件
        $('#start_time, #end_time').on('change input', function() {
            updateTimePreview();
        });

        // 点击模态框外部关闭
        $('#thingCreateModal').on('click', function(e) {
            if (e.target === this) {
                hideThingCreateModal();
            }
        });

        // ESC键关闭模态框
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && isThingModalOpen) {
                hideThingCreateModal();
            }
        });

        // 表单回车键提交
        $('#thingCreateForm input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitThingCreateForm();
            }
        });

        // 时间加减按钮点击效果
        $('.time-adjust-btn').on('click', function() {
            $(this).addClass('bg-gray-100');
            setTimeout(() => {
                $(this).removeClass('bg-gray-100');
            }, 200);
        });
    });

    // 暴露模态框控制函数给全局使用
    window.showThingCreateModal = showThingCreateModal;
    window.hideThingCreateModal = hideThingCreateModal;
    window.submitThingCreateForm = submitThingCreateForm;
</script>