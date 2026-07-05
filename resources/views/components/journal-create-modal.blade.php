<!-- 引入日期选择器 -->
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

<!-- 新增手账记录模态框 -->
<div id="journalCreateModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <div class="fixed inset-0 overflow-y-auto py-4 sm:py-8 px-4">
        <div class="min-h-full flex items-center justify-center">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all">
                <div class="sticky top-0 z-10 bg-white rounded-t-lg flex items-center justify-between px-5 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">记录手账</h3>
                            <p class="text-xs text-gray-500 mt-0.5">快速补记刚完成的事情和耗时</p>
                        </div>
                        <a href="/journals" class="hidden sm:inline-flex items-center text-blue-600 hover:text-blue-800 text-xs font-medium ml-auto">
                            <i class="fas fa-external-link-alt mr-1"></i>查看手账列表
                        </a>
                    </div>
                    <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
                            onclick="hideJournalCreateModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="overflow-y-auto max-h-[calc(100vh-180px)] p-5">
                    @include('common.errors')

                    <form id="journalCreateForm" action="javascript:void(0)" method="POST" class="space-y-5">
                        {{ csrf_field() }}

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-pen mr-2 text-blue-500"></i>完成内容
                            </label>
                            <textarea name="name" id="name"
                                      class="input w-full min-h-[92px] resize-y text-sm"
                                      placeholder="写下刚完成的事，例如：整理本周任务、读完一篇文章、复盘上午工作..."
                                      required
                                      autofocus>{{ old('journal') }}</textarea>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button type="button" class="journal-template-btn text-xs px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200" data-template="整理任务：">
                                    整理任务
                                </button>
                                <button type="button" class="journal-template-btn text-xs px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200" data-template="阅读学习：">
                                    阅读学习
                                </button>
                                <button type="button" class="journal-template-btn text-xs px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200" data-template="工作推进：">
                                    工作推进
                                </button>
                                <button type="button" class="journal-template-btn text-xs px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200" data-template="复盘总结：">
                                    复盘总结
                                </button>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-bolt mr-2 text-purple-500"></i>快速时间
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">按“现在往前推”快速生成开始和结束时间</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" onclick="setDefaultTimes()">
                                    <i class="fas fa-redo mr-1"></i>重置
                                </button>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" class="quick-time-btn rounded-lg border border-gray-200 bg-white p-3 text-left hover:border-blue-300 hover:bg-blue-50 transition-colors" data-minutes="10">
                                    <div class="text-sm font-semibold text-gray-900">10 分钟</div>
                                    <div class="text-xs text-gray-500 mt-0.5">刚刚完成</div>
                                </button>
                                <button type="button" class="quick-time-btn rounded-lg border border-gray-200 bg-white p-3 text-left hover:border-blue-300 hover:bg-blue-50 transition-colors" data-minutes="25">
                                    <div class="text-sm font-semibold text-gray-900">25 分钟</div>
                                    <div class="text-xs text-gray-500 mt-0.5">一个番茄钟</div>
                                </button>
                                <button type="button" class="quick-time-btn rounded-lg border border-gray-200 bg-white p-3 text-left hover:border-blue-300 hover:bg-blue-50 transition-colors" data-minutes="60">
                                    <div class="text-sm font-semibold text-gray-900">1 小时</div>
                                    <div class="text-xs text-gray-500 mt-0.5">整段工作</div>
                                </button>
                                <button type="button" class="quick-time-btn rounded-lg border border-gray-200 bg-white p-3 text-left hover:border-blue-300 hover:bg-blue-50 transition-colors" data-minutes="120">
                                    <div class="text-sm font-semibold text-gray-900">2 小时</div>
                                    <div class="text-xs text-gray-500 mt-0.5">深度处理</div>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="start_time" class="block text-xs font-medium text-gray-600 mb-1">
                                        <i class="fas fa-play mr-1 text-green-500"></i>开始时间
                                    </label>
                                    <div class="space-y-2">
                                        <div class="relative flex-1">
                                            <input type="text" name="start_time" id="start_time"
                                                   class="input w-full pl-9 text-sm"
                                                   onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                                   placeholder="选择开始时间">
                                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn" onclick="changeTime('start_time', -5)">-5m</button>
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn" onclick="changeTime('start_time', 5)">+5m</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="end_time" class="block text-xs font-medium text-gray-600 mb-1">
                                        <i class="fas fa-stop mr-1 text-red-500"></i>结束时间
                                    </label>
                                    <div class="space-y-2">
                                        <div class="relative flex-1">
                                            <input type="text" name="end_time" id="end_time"
                                                   class="input w-full pl-9 text-sm"
                                                   onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                                   placeholder="选择结束时间">
                                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn" onclick="changeTime('end_time', -5)">-5m</button>
                                            <button type="button" class="btn btn-sm btn-outline time-adjust-btn" onclick="changeTime('end_time', 5)">+5m</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="duration" class="block text-xs font-medium text-gray-600 mb-1">
                                        <i class="fas fa-history mr-1 text-orange-500"></i>从开始时间推算结束
                                    </label>
                                    <select id="duration" class="input w-full duration-select text-sm">
                                        <option value="">选择常用时长</option>
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

                                <div id="time-preview" class="time-preview p-3 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                        <div>
                                            <div class="font-medium text-gray-800 text-sm">时间预览</div>
                                            <div class="text-xs text-gray-500">请先设置开始和结束时间</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="sticky bottom-0 bg-white rounded-b-lg flex items-center justify-between gap-3 px-5 py-4 border-t border-gray-200">
                    <a href="/journals" class="sm:hidden text-blue-600 text-sm">
                        <i class="fas fa-list mr-1"></i>手账列表
                    </a>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" class="btn btn-outline px-4 py-2 text-sm" onclick="hideJournalCreateModal()">
                            取消
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2 text-sm" onclick="submitJournalCreateForm()">
                            <i class="fas fa-check mr-1"></i>提交记录
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let isJournalModalOpen = false;
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

    // 模态框控制函数
    function showJournalCreateModal() {
        const modal = document.getElementById('journalCreateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        isJournalModalOpen = true;

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

    function hideJournalCreateModal() {
        const modal = document.getElementById('journalCreateModal');
        const dialog = modal.querySelector('.bg-white');

        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            isJournalModalOpen = false;

            // 滚动到顶部
            modal.querySelector('.overflow-y-auto').scrollTop = 0;
        }, 300);
    }

    // 表单提交函数
    function submitJournalCreateForm() {
        const form = document.getElementById('journalCreateForm');

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

            const submitBtn = document.querySelector('button[onclick="submitJournalCreateForm()"]');
            const original = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1 sm:mr-2"></i>提交中...';
            }

            apiRequest('POST', '/journals', {
                name: nameInput.value.trim(),
                start_time: startTimeInput.value,
                end_time: endTimeInput.value
            }).then(function(resp) {
                if (resp && resp.code === 9999) {
                    hideJournalCreateModal();
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
            $('#duration').val('');
            updateTimePreview();

            $('.quick-time-btn').removeClass('border-blue-400 bg-blue-50 ring-1 ring-blue-200');
            $(this).addClass('border-blue-400 bg-blue-50 ring-1 ring-blue-200');
            setTimeout(() => {
                $(this).removeClass('ring-1 ring-blue-200');
            }, 500);
        });

        $('.journal-template-btn').click(function() {
            const input = $('#name');
            const template = $(this).data('template') || '';
            const current = input.val();
            input.val(current ? current + ' ' + template : template);
            input.focus();
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
        $('#journalCreateModal').on('click', function(e) {
            if (e.target === this) {
                hideJournalCreateModal();
            }
        });

        // ESC键关闭模态框
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && isJournalModalOpen) {
                hideJournalCreateModal();
            }
        });

        // 表单回车键提交
        $('#journalCreateForm input').on('keydown', function(e) {
            if (e.key === 'Enter' && !$(this).is('input[type="button"], input[type="submit"]')) {
                e.preventDefault();
                submitJournalCreateForm();
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
    window.showJournalCreateModal = showJournalCreateModal;
    window.hideJournalCreateModal = hideJournalCreateModal;
    window.submitJournalCreateForm = submitJournalCreateForm;
    window.changeTime = changeTime;
    window.setDefaultTimes = setDefaultTimes;
</script>
