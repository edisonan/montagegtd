@extends('layouts.app')

@section('title', '修改事情记录 - 蒙太奇')
@section('description', '编辑您的事情记录信息')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-edit text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">修改事情记录</h2>
                            <p class="text-sm text-gray-500 mt-1">编辑您已完成的事情信息</p>
                        </div>
                    </div>
                    <a href="{{ '/things' }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回列表
                    </a>
                </div>
            </div>
            <div class="p-6">
                <!-- 错误提示 -->
                @include('common.errors')

                <!-- 编辑表单 -->
                <form action="javascript:void(0);" method="POST" class="space-y-6" id="thing-update-form">

                    <!-- 事情内容 -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            <span class="text-red-500 mr-1">*</span>事情内容
                        </label>
                        <div class="relative">
                            <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="input w-full pl-10"
                                    value=""
                                    placeholder="请输入事情内容"
                                    required
                            >
                            <div class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">简要描述您完成的事情</p>
                    </div>

                    <!-- 时间范围 -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">
                            <span class="text-red-500 mr-1">*</span>时间范围
                        </label>

                        <!-- 开始时间 -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="start_time" class="text-sm text-gray-600">开始时间</label>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="adjustTime('start_time', -5)" class="btn btn-outline btn-xs">
                                        <i class="fas fa-minus"></i> 5分钟
                                    </button>
                                    <button type="button" onclick="adjustTime('start_time', 5)" class="btn btn-outline btn-xs">
                                        <i class="fas fa-plus"></i> 5分钟
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <input
                                        type="text"
                                        name="start_time"
                                        id="start_time"
                                        class="input w-full pl-10"
                                        value=""
                                        placeholder="选择开始时间"
                                        onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                        readonly
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>

                        <!-- 结束时间 -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="end_time" class="text-sm text-gray-600">结束时间</label>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="adjustTime('end_time', -5)" class="btn btn-outline btn-xs">
                                        <i class="fas fa-minus"></i> 5分钟
                                    </button>
                                    <button type="button" onclick="adjustTime('end_time', 5)" class="btn btn-outline btn-xs">
                                        <i class="fas fa-plus"></i> 5分钟
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <input
                                        type="text"
                                        name="end_time"
                                        id="end_time"
                                        class="input w-full pl-10"
                                        value=""
                                        placeholder="选择结束时间"
                                        onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d'})"
                                        readonly
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>

                        <!-- 时间长度提示 -->
                        <div id="duration_hint" class="mt-3 p-3 bg-blue-50 rounded-lg hidden">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-hourglass-half text-blue-500"></i>
                                    <span class="text-sm font-medium text-gray-700">耗时：</span>
                                </div>
                                <div class="text-lg font-bold text-blue-600" id="duration_text">0小时0分钟</div>
                            </div>
                            <div class="mt-2 hidden" id="tomato_hint">
                                <div class="flex items-center text-xs text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <span>这是一个完整的番茄钟时间 🍅</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 表单操作 -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-2"></i>
                                准确记录时间有助于分析您的工作效率
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="{{ '/things' }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-2"></i>
                                    取消
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check mr-2"></i>
                                    保存修改
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 快捷操作提示 -->
        <div class="mt-6 card">
            <div class="p-4">
                <div class="flex items-center space-x-3 mb-3">
                    <i class="fas fa-lightbulb text-yellow-500 text-lg"></i>
                    <h3 class="text-sm font-medium text-gray-900">操作提示</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-clock text-blue-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">时间微调</span>
                        </div>
                        <p class="text-xs text-gray-600">使用±5分钟按钮快速调整时间</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-calendar text-green-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">时间选择</span>
                        </div>
                        <p class="text-xs text-gray-600">点击输入框使用日历控件选择时间</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-history text-purple-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">时间计算</span>
                        </div>
                        <p class="text-xs text-gray-600">系统会自动计算事情耗时并显示</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* 表单输入框聚焦效果 */
        .input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        /* 时间调整按钮样式 */
        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* 番茄钟标识 */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .tomato-indicator {
            animation: pulse 2s infinite;
        }
    </style>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var currentThingId = null;

        // 时间调整函数
        function adjustTime(inputId, minutes) {
            const input = document.getElementById(inputId);
            let timeValue = input.value;

            if (!timeValue) {
                // 如果没有值，使用当前时间
                const now = new Date();
                timeValue = now.toISOString().slice(0, 16).replace('T', ' ');
            }

            try {
                // 解析时间
                const date = new Date(timeValue.replace(' ', 'T') + ':00');

                // 调整分钟
                date.setMinutes(date.getMinutes() + minutes);

                // 格式化回字符串
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const mins = String(date.getMinutes()).padStart(2, '0');

                input.value = `${year}-${month}-${day} ${hours}:${mins}:00`;
                updateDurationHint();

                // 显示成功提示
                showTimeAdjustmentToast(minutes > 0 ? '增加' : '减少', Math.abs(minutes));

            } catch (error) {
                console.error('时间调整失败:', error);
            }
        }

        // 显示时间调整提示
        function showTimeAdjustmentToast(action, minutes) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 z-50 px-4 py-3 bg-gray-800 text-white rounded-lg shadow-lg text-sm animate-fade-in';
            toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-clock mr-3"></i>
                <span>已${action}${minutes}分钟</span>
            </div>
        `;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 2000);
        }

        function getThingIdFromPath() {
            var parts = window.location.pathname.split('/').filter(Boolean);
            return parts.length ? parts[parts.length - 1] : '';
        }

        function updateDurationHint() {
            var startValue = document.getElementById('start_time').value;
            var endValue = document.getElementById('end_time').value;
            if (!startValue || !endValue) {
                document.getElementById('duration_hint').classList.add('hidden');
                return;
            }
            var start = new Date(startValue.replace(' ', 'T'));
            var end = new Date(endValue.replace(' ', 'T'));
            if (isNaN(start.getTime()) || isNaN(end.getTime()) || end <= start) {
                document.getElementById('duration_hint').classList.add('hidden');
                return;
            }
            var totalMinutes = Math.round((end.getTime() - start.getTime()) / 60000);
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;
            document.getElementById('duration_text').textContent = hours + '小时' + minutes + '分钟';
            document.getElementById('duration_hint').classList.remove('hidden');
            document.getElementById('tomato_hint').classList.toggle('hidden', totalMinutes > 25);
        }

        function loadThingDetail() {
            if (!apiRequest) {
                showErrorToast('API客户端未初始化');
                return;
            }
            currentThingId = getThingIdFromPath();
            if (!currentThingId) {
                showErrorToast('无法识别事情ID');
                return;
            }
            apiRequest('GET', '/things/' + currentThingId, {}).then(function(resp) {
                if (!(resp && resp.code === 9999 && resp.result)) {
                    showErrorToast((resp && resp.msg) ? resp.msg : '加载失败');
                    return;
                }
                document.getElementById('name').value = resp.result.name || '';
                document.getElementById('start_time').value = resp.result.start_time || '';
                document.getElementById('end_time').value = resp.result.end_time || '';
                updateDurationHint();
            }).catch(function() {
                showErrorToast('加载失败，请稍后重试');
            });
        }

        // 时间选择器配置
        document.addEventListener('DOMContentLoaded', function() {
            loadThingDetail();

            // 确保WdatePicker可用
            if (typeof WdatePicker !== 'undefined') {
                // 可以在这里添加全局配置
                console.log('日期选择器已加载');
            }

            document.getElementById('start_time').addEventListener('change', updateDurationHint);
            document.getElementById('end_time').addEventListener('change', updateDurationHint);

            // 表单验证
            const form = document.getElementById('thing-update-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const nameInput = document.getElementById('name');
                const startTimeInput = document.getElementById('start_time');
                const endTimeInput = document.getElementById('end_time');

                let isValid = true;
                let errorMessage = '';

                // 验证事情内容
                if (!nameInput.value.trim()) {
                    isValid = false;
                    errorMessage = '请输入事情内容';
                    nameInput.focus();
                }
                // 验证开始时间
                else if (!startTimeInput.value) {
                    isValid = false;
                    errorMessage = '请选择开始时间';
                    startTimeInput.focus();
                }
                // 验证结束时间
                else if (!endTimeInput.value) {
                    isValid = false;
                    errorMessage = '请选择结束时间';
                    endTimeInput.focus();
                }
                // 验证时间逻辑
                else if (startTimeInput.value && endTimeInput.value) {
                    const start = new Date(startTimeInput.value.replace(' ', 'T'));
                    const end = new Date(endTimeInput.value.replace(' ', 'T'));

                    if (end <= start) {
                        isValid = false;
                        errorMessage = '结束时间必须晚于开始时间';
                        endTimeInput.focus();
                    }
                }

                if (!isValid) {
                    showErrorToast(errorMessage);
                    return;
                }

                if (!apiRequest) {
                    showErrorToast('API客户端未初始化');
                    return;
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                const original = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';
                }

                apiRequest('PUT', '/things/' + currentThingId, {
                    name: nameInput.value.trim(),
                    start_time: startTimeInput.value,
                    end_time: endTimeInput.value
                }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        window.location.href = '{{ url('/things') }}';
                        return;
                    }
                    showErrorToast((resp && resp.msg) ? resp.msg : '保存失败');
                }).catch(function() {
                    showErrorToast('保存失败，请稍后重试');
                }).finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = original;
                    }
                });
            });
        });

        // 显示错误提示
        function showErrorToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-50 px-6 py-4 bg-red-500 text-white rounded-lg shadow-lg animate-fade-in';
            toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
        }
    </script>
@endsection
