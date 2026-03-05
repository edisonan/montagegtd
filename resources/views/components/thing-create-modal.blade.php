<!-- 引入日期选择器 -->
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

<!-- 新增事情记录模态框 -->
<!-- 引入日期选择器 -->
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

<!-- 新增事情记录模态框 -->
<div id="thingCreateModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- 修复：使用flex居中容器 -->
    <div class="fixed inset-0 overflow-y-auto py-4 sm:py-8 px-4">
        <!-- 修复：使用flex和items-center实现真正的居中 -->
        <div class="min-h-full flex items-center justify-center">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl transform transition-all">
                <!-- 模态框头部 - 固定 -->
                <div class="sticky top-0 z-10 bg-white rounded-t-lg flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-2 flex-1 min-w-0">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 truncate">新增事情记录</h3>
                        <span class="text-gray-400 hidden sm:inline">|</span>
                        <a href="/things" class="text-blue-600 hover:text-blue-800 hover:underline text-xs sm:text-sm font-medium truncate ml-0 sm:ml-2">
                            <i class="fas fa-external-link-alt mr-1"></i>查看事情列表
                        </a>
                    </div>
                    <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
                            onclick="hideThingCreateModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- 模态框内容 - 可滚动区域 -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-240px)] p-4 sm:p-6">
                    @include('common.errors')

                    <form id="thingCreateForm" action="javascript:void(0)" method="POST" class="space-y-4 sm:space-y-6">
                        {{ csrf_field() }}

                        <!-- 事情内容 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 items-start">
                            <label for="name" class="font-medium text-gray-700 pt-2">
                                <i class="fas fa-tasks mr-2 text-blue-500"></i>完成内容
                            </label>
                            <div class="md:col-span-3">
                                <input type="text" name="name" id="name"
                                       class="input w-full text-sm sm:text-base"
                                       value="{{ old('thing') }}"
                                       placeholder="请输入完成的事情内容"
                                       required
                                       autofocus>
                            </div>
                        </div>

                        <!-- 快速时间选择 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                            <div class="font-medium text-gray-700 pt-2">
                                <i class="fas fa-bolt mr-2 text-purple-500"></i>快速选择
                            </div>
                            <div class="md:col-span-3">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline quick-time-btn text-xs sm:text-sm px-2 sm:px-3 py-1.5" data-minutes="10">
                                        <i class="far fa-clock mr-1"></i>10分钟前
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline quick-time-btn text-xs sm:text-sm px-2 sm:px-3 py-1.5" data-minutes="25">
                                        <i class="fas fa-hourglass-start mr-1"></i>25分钟前
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline quick-time-btn text-xs sm:text-sm px-2 sm:px-3 py-1.5" data-minutes="60">
                                        <i class="fas fa-hourglass-half mr-1"></i>1小时前
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline quick-time-btn text-xs sm:text-sm px-2 sm:px-3 py-1.5" data-minutes="120">
                                        <i class="fas fa-hourglass-end mr-1"></i>2小时前
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 时间选择器组 -->
                        <div class="space-y-4 sm:space-y-6">
                            <!-- 开始时间 -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 items-start">
                                <label for="start_time" class="font-medium text-gray-700 pt-2">
                                    <i class="fas fa-play mr-2 text-green-500"></i>开始时间
                                </label>
                                <div class="md:col-span-3">
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                                        <div class="relative flex-1">
                                            <input type="text" name="start_time" id="start_time"
                                                   class="input w-full pl-10 text-sm sm:text-base"
                                                   onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                                   placeholder="选择开始时间">
                                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                        </div>
                                        <div class="flex items-center space-x-2 self-start sm:self-center">
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn px-2 sm:px-3 py-1.5 text-xs sm:text-sm"
                                                    onclick="changeTime('start_time', -5)">
                                                <i class="fas fa-minus mr-1"></i>5m
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn px-2 sm:px-3 py-1.5 text-xs sm:text-sm"
                                                    onclick="changeTime('start_time', 5)">
                                                <i class="fas fa-plus mr-1"></i>5m
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 结束时间 -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 items-start">
                                <label for="end_time" class="font-medium text-gray-700 pt-2">
                                    <i class="fas fa-stop mr-2 text-red-500"></i>结束时间
                                </label>
                                <div class="md:col-span-3">
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                                        <div class="relative flex-1">
                                            <input type="text" name="end_time" id="end_time"
                                                   class="input w-full pl-10 text-sm sm:text-base"
                                                   onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                                   placeholder="选择结束时间">
                                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                        </div>
                                        <div class="flex items-center space-x-2 self-start sm:self-center">
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn px-2 sm:px-3 py-1.5 text-xs sm:text-sm"
                                                    onclick="changeTime('end_time', -5)">
                                                <i class="fas fa-minus mr-1"></i>5m
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn px-2 sm:px-3 py-1.5 text-xs sm:text-sm"
                                                    onclick="changeTime('end_time', 5)">
                                                <i class="fas fa-plus mr-1"></i>5m
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 时长选择和预览 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                            <div class="font-medium text-gray-700 pt-2">
                                <i class="fas fa-history mr-2 text-orange-500"></i>时长设置
                            </div>
                            <div class="md:col-span-3 space-y-3 sm:space-y-4">
                                <!-- 常用时长选择 -->
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
                                    <div class="relative flex-1">
                                        <select id="duration" class="input w-full duration-select text-sm sm:text-base">
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
                                    <button type="button" class="btn btn-sm btn-outline px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm" onclick="setDefaultTimes()">
                                        <i class="fas fa-redo mr-1"></i>重置时间
                                    </button>
                                </div>

                                <!-- 时间预览 -->
                                <div id="time-preview" class="time-preview card p-3 sm:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-500 mr-2 sm:mr-3"></i>
                                        <div>
                                            <div class="font-medium text-blue-700 text-sm sm:text-base">时间预览</div>
                                            <div class="text-xs sm:text-sm text-blue-600">请先设置开始和结束时间</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 模态框底部 - 固定 -->
                <div class="sticky bottom-0 bg-white rounded-b-lg flex items-center justify-end p-4 sm:p-6 border-t border-gray-200 space-x-2 sm:space-x-3">
                    <button type="button" class="btn btn-outline px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base" onclick="hideThingCreateModal()">
                        <i class="fas fa-times mr-1 sm:mr-2"></i>取消
                    </button>
                    <button type="button" class="btn btn-primary px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base" onclick="submitThingCreateForm()">
                        <i class="fas fa-check mr-1 sm:mr-2"></i>提交记录
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let isThingModalOpen = false;
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

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

            // 滚动到顶部
            modal.querySelector('.overflow-y-auto').scrollTop = 0;
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
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }

            const submitBtn = document.querySelector('button[onclick="submitThingCreateForm()"]');
            const original = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1 sm:mr-2"></i>提交中...';
            }

            apiRequest('POST', '/things', {
                name: nameInput.value.trim(),
                start_time: startTimeInput.value,
                end_time: endTimeInput.value
            }).then(function(resp) {
                if (resp && resp.code === 9999) {
                    hideThingCreateModal();
                    window.location.reload();
                    return;
                }
                alert((resp && resp.msg) ? resp.msg : '提交失败');
            }).catch(function() {
                alert('提交失败，请稍后重试');
            }).finally(function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = original;
                }
            });
        }
    }

    // 显示字段错误
    function showFieldError(input, message) {
        input.classList.add('has-error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-xs sm:text-sm mt-1 text-red-600';
        errorDiv.textContent = message;
        input.parentNode.insertBefore(errorDiv, input.nextSibling);

        // 滚动到错误位置
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // 时间调整函数
    function changeTime(id, interval) {
        const input = document.getElementById(id);
        if (!input.value) return;

        const time = new Date(input.value).getTime();
        const newTime = new Date(time + interval * 60000);

        // 格式化为 YYYY-MM-DD HH:mm:ss
        const year = newTime.getFullYear();
        const month = String(newTime.getMonth() + 1).padStart(2, '0');
        const day = String(newTime.getDate()).padStart(2, '0');
        const hours = String(newTime.getHours()).padStart(2, '0');
        const minutes = String(newTime.getMinutes()).padStart(2, '0');

        input.value = `${year}-${month}-${day} ${hours}:${minutes}:00`;
        updateTimePreview();
    }

    // 设置默认时间函数
    function setDefaultTimes() {
        const now = new Date();
        const roundedNow = new Date(Math.round(now.getTime() / 600000) * 600000);
        const tenMinutesAgo = new Date(roundedNow.getTime() - 10 * 60000);

        $('#start_time').val(formatDate(tenMinutesAgo));
        $('#end_time').val(formatDate(roundedNow));
        updateTimePreview();
    }

    // 格式化日期为字符串
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:00`;
    }

    // 更新时间预览
    function updateTimePreview() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        const timePreview = $('#time-preview');

        if (startTime && endTime) {
            const start = new Date(startTime);
            const end = new Date(endTime);

            // 检查时间顺序
            if (end <= start) {
                timePreview.html(`
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2 sm:mr-3"></i>
                        <div>
                            <div class="font-medium text-red-700 text-sm sm:text-base">时间错误</div>
                            <div class="text-xs sm:text-sm text-red-600">结束时间必须晚于开始时间</div>
                        </div>
                    </div>
                `);
                return;
            }

            const duration = Math.round((end - start) / 60000); // 分钟
            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;

            let durationText = '';
            if (hours > 0) {
                durationText += hours + '小时';
            }
            if (minutes > 0 || hours === 0) {
                durationText += minutes + '分钟';
            }

            // 格式化显示时间
            const formattedStart = start.getHours().toString().padStart(2, '0') + ':' +
                start.getMinutes().toString().padStart(2, '0');
            const formattedEnd = end.getHours().toString().padStart(2, '0') + ':' +
                end.getMinutes().toString().padStart(2, '0');

            timePreview.html(`
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="space-y-1">
                        <div class="text-xs text-gray-500">开始时间</div>
                        <div class="font-medium text-gray-900 text-sm sm:text-base">${formattedStart}</div>
                        <div class="text-xs text-gray-400">${start.getMonth()+1}月${start.getDate()}日</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-gray-500">结束时间</div>
                        <div class="font-medium text-gray-900 text-sm sm:text-base">${formattedEnd}</div>
                        <div class="text-xs text-gray-400">${end.getMonth()+1}月${end.getDate()}日</div>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <div class="mt-2 p-3 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                <div class="flex items-center">
                                    <i class="fas fa-hourglass-half text-green-500 mr-2"></i>
                                    <span class="font-bold text-green-700 text-sm sm:text-base">总计时长: ${durationText}</span>
                                </div>
                                <span class="text-xs sm:text-sm text-gray-500">${duration} 分钟</span>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        } else {
            timePreview.html(`
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2 sm:mr-3"></i>
                    <div>
                        <div class="font-medium text-blue-700 text-sm sm:text-base">时间预览</div>
                        <div class="text-xs sm:text-sm text-blue-600">请先设置开始和结束时间</div>
                    </div>
                </div>
            `);
        }
    }

    // 初始化事件监听
    $(document).ready(function() {
        // 快速时间选择功能
        $('.quick-time-btn').click(function() {
            const minutes = parseInt($(this).data('minutes'));
            const now = new Date();
            const start = new Date(now.getTime() - minutes * 60000);

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
            const duration = parseInt($(this).val());
            const startTime = $('#start_time').val();
            if (startTime && duration) {
                const start = new Date(startTime);
                const end = new Date(start.getTime() + duration * 60000);
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
            if (e.key === 'Enter' && !$(this).is('input[type="button"], input[type="submit"]')) {
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
    window.changeTime = changeTime;
    window.setDefaultTimes = setDefaultTimes;
</script>
