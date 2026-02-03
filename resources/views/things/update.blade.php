@extends('layouts.app')

@section('title', '修改事情记录 - 蒙太奇')
@section('description', '编辑您的事情记录信息')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- 卡片头部 -->
        <div class="card mb-6">
            <div class="card-header !border-b-0 !pb-0">
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
        </div>

        <!-- 表单卡片 -->
        <div class="card">
            <div class="p-6">
                <!-- 错误提示 -->
                @include('common.errors')

                <!-- 编辑表单 -->
                <form action="{{ url('thing/'.$thing->id) }}" method="POST" class="space-y-6">
                    {!! csrf_field() !!}
                    @method('PUT')

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
                                    value="{{ old('name', $thing->name) }}"
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
                                        value="{{ old('start_time', $thing->start_time) }}"
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
                                        value="{{ old('end_time', $thing->end_time) }}"
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
                        @php
                            try {
                                $start = new DateTime($thing->start_time);
                                $end = new DateTime($thing->end_time);
                                $interval = $start->diff($end);
                                $hours = $interval->h;
                                $minutes = $interval->i;
                                $totalMinutes = $hours * 60 + $minutes;
                            } catch (Exception $e) {
                                $totalMinutes = 0;
                            }
                        @endphp

                        @if($totalMinutes > 0)
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hourglass-half text-blue-500"></i>
                                        <span class="text-sm font-medium text-gray-700">耗时：</span>
                                    </div>
                                    <div class="text-lg font-bold text-blue-600">
                                        {{ floor($totalMinutes / 60) }}小时{{ $totalMinutes % 60 }}分钟
                                    </div>
                                </div>
                                @if($totalMinutes <= 25)
                                    <div class="mt-2">
                                        <div class="flex items-center text-xs text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            <span>这是一个完整的番茄钟时间 🍅</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
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
        /* 自定义样式 */
        .card-header {
            border-bottom: 1px solid var(--gray-200);
            padding: 1.5rem 1.5rem 0.75rem;
        }

        .card-header.border-b-0 {
            border-bottom: 0 !important;
        }

        .card-header.pb-0 {
            padding-bottom: 0 !important;
        }

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

        // 时间选择器配置
        document.addEventListener('DOMContentLoaded', function() {
            // 确保WdatePicker可用
            if (typeof WdatePicker !== 'undefined') {
                // 可以在这里添加全局配置
                console.log('日期选择器已加载');
            }

            // 表单验证
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
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
                    event.preventDefault();
                    showErrorToast(errorMessage);
                }
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