@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和导航 -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">系统设置</h1>
                <p class="text-gray-600 mt-1">个性化配置您的生产力工具</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>返回首页
                </a>
            </div>
        </div>

        <!-- 成功消息提示 -->
        @include('common.success')

        <!-- 主要内容区域 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 左侧：主要设置 -->
            <div class="lg:col-span-2">
                <div class="card card-elevated">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-cog text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">核心设置</h2>
                                <p class="text-sm text-gray-500 mt-1">配置您的生产力工具参数</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- 显示验证错误 -->
                        @include('common.errors')

                        <form action="{{ url('setting/'.$setting->id) }}" method="POST" class="space-y-8" id="settings-form">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}

                            <!-- 番茄工作法设置 -->
                            <div>
                                <div class="flex items-center mb-6">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-clock text-red-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">🍅 番茄工作法设置</h3>
                                        <p class="text-sm text-gray-500">配置您的专注时间和目标</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <!-- 番茄时间设置 -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="pomo_time" class="block text-sm font-medium text-gray-700 mb-2">
                                                番茄专注时间
                                            </label>
                                            <div class="relative">
                                                <input type="number"
                                                       name="pomo_time"
                                                       id="pomo_time"
                                                       class="input w-full pr-12"
                                                       value="{{ empty($setting->pomo_time) ? 25 : $setting->pomo_time }}"
                                                       min="10"
                                                       max="60"
                                                       step="1">
                                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                                                    <span class="text-sm">分钟</span>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex items-center">
                                                <i class="fas fa-info-circle text-blue-500 text-sm mr-2"></i>
                                                <p class="text-xs text-gray-500">建议 25 分钟，范围 10-60 分钟</p>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="pomo_rest_time" class="block text-sm font-medium text-gray-700 mb-2">
                                                番茄休息时间
                                            </label>
                                            <div class="relative">
                                                <input type="number"
                                                       name="pomo_rest_time"
                                                       id="pomo_rest_time"
                                                       class="input w-full pr-12"
                                                       value="{{ empty($setting->pomo_rest_time) ? 5 : $setting->pomo_rest_time }}"
                                                       min="1"
                                                       max="10"
                                                       step="1">
                                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                                                    <span class="text-sm">分钟</span>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex items-center">
                                                <i class="fas fa-info-circle text-blue-500 text-sm mr-2"></i>
                                                <p class="text-xs text-gray-500">建议 5 分钟，范围 1-10 分钟</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 目标设置 -->
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-4">🎯 目标设置</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="day_pomo_goal" class="block text-xs font-medium text-gray-500 mb-2">
                                                    <i class="fas fa-sun text-yellow-500 mr-1"></i> 日目标
                                                </label>
                                                <input type="number"
                                                       name="day_pomo_goal"
                                                       id="day_pomo_goal"
                                                       class="input w-full text-center"
                                                       value="{{ empty($setting->day_pomo_goal) ? 8 : $setting->day_pomo_goal }}"
                                                       min="1"
                                                       step="1">
                                                <p class="text-xs text-gray-500 mt-2 text-center">个番茄/天</p>
                                            </div>

                                            <div>
                                                <label for="week_pomo_goal" class="block text-xs font-medium text-gray-500 mb-2">
                                                    <i class="fas fa-calendar-week text-green-500 mr-1"></i> 周目标
                                                </label>
                                                <input type="number"
                                                       name="week_pomo_goal"
                                                       id="week_pomo_goal"
                                                       class="input w-full text-center"
                                                       value="{{ empty($setting->week_pomo_goal) ? 40 : $setting->week_pomo_goal }}"
                                                       min="1"
                                                       step="1">
                                                <p class="text-xs text-gray-500 mt-2 text-center">个番茄/周</p>
                                            </div>

                                            <div>
                                                <label for="month_pomo_goal" class="block text-xs font-medium text-gray-500 mb-2">
                                                    <i class="fas fa-calendar-alt text-blue-500 mr-1"></i> 月目标
                                                </label>
                                                <input type="number"
                                                       name="month_pomo_goal"
                                                       id="month_pomo_goal"
                                                       class="input w-full text-center"
                                                       value="{{ empty($setting->month_pomo_goal) ? 160 : $setting->month_pomo_goal }}"
                                                       min="1"
                                                       step="1">
                                                <p class="text-xs text-gray-500 mt-2 text-center">个番茄/月</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kindle推送设置 -->
                            <div>
                                <div class="flex items-center mb-6">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-book text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">📚 Kindle推送设置</h3>
                                        <p class="text-sm text-gray-500">配置文章推送到Kindle设备</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <!-- Kindle邮箱 -->
                                    <div>
                                        <label for="kindle_email" class="block text-sm font-medium text-gray-700 mb-2">
                                            Kindle订阅地址
                                        </label>
                                        <input type="email"
                                               name="kindle_email"
                                               id="kindle_email"
                                               class="input w-full"
                                               value="{{ $setting->kindle_email }}"
                                               placeholder="yourname@kindle.cn">
                                        <div class="mt-2 flex items-center">
                                            <i class="fas fa-info-circle text-blue-500 text-sm mr-2"></i>
                                            <p class="text-xs text-gray-500">您的Kindle专用邮箱地址</p>
                                        </div>
                                    </div>

                                    <!-- 推送开关 -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                                是否开启推送
                                            </label>
                                            <div class="flex items-center space-x-6">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio"
                                                           name="is_start_kindle"
                                                           value="0"
                                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300"
                                                            {{ empty($setting->is_start_kindle) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">关闭推送</span>
                                                </label>
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio"
                                                           name="is_start_kindle"
                                                           value="1"
                                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300"
                                                            {{ $setting->is_start_kindle == 1 ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">开启推送</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                                是否带图推送
                                            </label>
                                            <div class="flex items-center space-x-6">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio"
                                                           name="with_image_push"
                                                           value="0"
                                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300"
                                                            {{ empty($setting->with_image_push) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">纯文本</span>
                                                </label>
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio"
                                                           name="with_image_push"
                                                           value="1"
                                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300"
                                                            {{ $setting->with_image_push == 1 ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">图文推送</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- IFTTT通知设置 -->
                            <div>
                                <div class="flex items-center mb-6">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-bell text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">🔔 IFTTT通知设置</h3>
                                        <p class="text-sm text-gray-500">配置第三方通知服务</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="ifttt_notify" class="block text-sm font-medium text-gray-700 mb-2">
                                        IFTTT Webhook Key
                                    </label>
                                    <div class="relative">
                                        <input type="password"
                                               name="ifttt_notify"
                                               id="ifttt_notify"
                                               class="input w-full pr-10"
                                               value="{{ $setting->ifttt_notify }}"
                                               placeholder="输入您的IFTTT Webhook密钥">
                                        <button type="button"
                                                onclick="togglePasswordVisibility('ifttt_notify')"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye" id="ifttt_notify_eye"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2 flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 text-sm mr-2 mt-0.5"></i>
                                        <p class="text-xs text-gray-500">
                                            用于连接IFTTT服务，实现跨平台通知。获取帮助：
                                            <a href="https://ifttt.com/maker_webhooks" target="_blank" class="text-primary-600 hover:text-primary-800">
                                                查看文档
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 表单操作 -->
                            <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-save text-blue-500 mr-2"></i>
                                    设置将在保存后立即生效
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button type="button" onclick="resetToDefaults()" class="btn btn-secondary">
                                        <i class="fas fa-undo mr-2"></i>恢复默认
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>保存设置
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右侧：设置说明 -->
            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">设置说明</h2>
                        <p class="text-sm text-gray-500 mt-1">优化您的使用体验</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- 番茄工作法说明 -->
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-clock text-blue-500 text-lg mt-0.5 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-800 mb-2">🍅 番茄工作法</h4>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li>• 25分钟专注 + 5分钟休息为经典组合</li>
                                        <li>• 根据您的注意力周期调整时间</li>
                                        <li>• 目标设置应具有挑战性但可实现</li>
                                        <li>• 建议每日8个番茄钟（约4小时）</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Kindle推送说明 -->
                        <div class="bg-green-50 border border-green-100 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-book text-green-500 text-lg mt-0.5 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-green-800 mb-2">📚 Kindle推送</h4>
                                    <ul class="text-xs text-green-700 space-y-1">
                                        <li>• Kindle邮箱可在设备设置中查看</li>
                                        <li>• 开启推送后，收藏文章自动发送</li>
                                        <li>• 图文推送包含文章中的图片</li>
                                        <li>• 纯文本模式加载更快，体积更小</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- IFTTT说明 -->
                        <div class="bg-purple-50 border border-purple-100 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-bell text-purple-500 text-lg mt-0.5 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-purple-800 mb-2">🔔 IFTTT集成</h4>
                                    <ul class="text-xs text-purple-700 space-y-1">
                                        <li>• 实现跨平台自动化通知</li>
                                        <li>• 支持微信、Telegram、邮件等</li>
                                        <li>• 番茄钟完成、任务提醒等</li>
                                        <li>• 密钥保密，避免泄露</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 快速操作 -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">⚡ 快速操作</h4>
                            <div class="space-y-3">
                                <button onclick="testKindlePush()" class="btn btn-outline w-full justify-center">
                                    <i class="fas fa-paper-plane mr-2"></i>测试Kindle推送
                                </button>
                                <button onclick="testIFTTT()" class="btn btn-outline w-full justify-center">
                                    <i class="fas fa-bell mr-2"></i>测试IFTTT通知
                                </button>
                                <button onclick="exportSettings()" class="btn btn-outline w-full justify-center">
                                    <i class="fas fa-download mr-2"></i>导出设置备份
                                </button>
                            </div>
                        </div>

                        <!-- 统计信息 -->
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">📊 使用统计</h4>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">当前番茄时长</span>
                                    <span class="text-sm font-medium text-gray-900">{{ empty($setting->pomo_time) ? 25 : $setting->pomo_time }}分钟</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">今日目标完成</span>
                                    <span class="text-sm font-medium text-green-600">0/{{ empty($setting->day_pomo_goal) ? 8 : $setting->day_pomo_goal }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Kindle推送状态</span>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $setting->is_start_kindle ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $setting->is_start_kindle ? '已开启' : '已关闭' }}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 表单提交处理
            const form = document.getElementById('settings-form');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // 验证番茄时间范围
                const pomoTime = parseInt(document.getElementById('pomo_time').value);
                const pomoRestTime = parseInt(document.getElementById('pomo_rest_time').value);

                if (pomoTime < 10 || pomoTime > 60) {
                    showToast('番茄时间应在10-60分钟之间', 'error');
                    return;
                }

                if (pomoRestTime < 1 || pomoRestTime > 10) {
                    showToast('休息时间应在1-10分钟之间', 'error');
                    return;
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                // 显示加载状态
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';
                submitBtn.disabled = true;

                try {
                    const formData = new FormData(this);

                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success || (data.code && data.code === 9999)) {
                        showToast('设置保存成功！', 'success');
                        submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>已保存';

                        // 恢复按钮状态
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 2000);
                    } else {
                        showToast(data.message || '保存失败，请稍后重试', 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('保存失败:', error);
                    showToast('网络错误，请稍后重试', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });

            // 实时验证输入
            const pomoTimeInput = document.getElementById('pomo_time');
            const pomoRestInput = document.getElementById('pomo_rest_time');

            function validateTime(input, min, max) {
                const value = parseInt(input.value);
                if (value < min) input.value = min;
                if (value > max) input.value = max;
            }

            pomoTimeInput.addEventListener('blur', () => validateTime(pomoTimeInput, 10, 60));
            pomoRestInput.addEventListener('blur', () => validateTime(pomoRestInput, 1, 10));
        });

        // 切换密码可见性
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById(inputId + '_eye');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // 恢复默认设置
        function resetToDefaults() {
            if (confirm('确定要恢复默认设置吗？当前的自定义设置将会被覆盖。')) {
                document.getElementById('pomo_time').value = 25;
                document.getElementById('pomo_rest_time').value = 5;
                document.getElementById('day_pomo_goal').value = 8;
                document.getElementById('week_pomo_goal').value = 40;
                document.getElementById('month_pomo_goal').value = 160;
                document.querySelector('input[name="is_start_kindle"][value="0"]').checked = true;
                document.querySelector('input[name="with_image_push"][value="0"]').checked = true;

                showToast('已恢复默认设置', 'info');
            }
        }

        // 测试Kindle推送
        async function testKindlePush() {
            const kindleEmail = document.getElementById('kindle_email').value;

            if (!kindleEmail) {
                showToast('请先填写Kindle邮箱地址', 'warning');
                return;
            }

            if (!confirm('将发送测试文章到您的Kindle邮箱，确定要继续吗？')) {
                return;
            }

            showToast('正在发送测试文章...', 'info');

            try {
                const response = await fetch('{{ url("settings/test-kindle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ email: kindleEmail })
                });

                const data = await response.json();

                if (data.success || data.code === 9999) {
                    showToast('测试文章已发送，请检查您的Kindle设备', 'success');
                } else {
                    showToast('发送失败：' + (data.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('发送失败:', error);
                showToast('发送失败，请检查网络连接', 'error');
            }
        }

        // 测试IFTTT通知
        async function testIFTTT() {
            const iftttKey = document.getElementById('ifttt_notify').value;

            if (!iftttKey) {
                showToast('请先填写IFTTT Webhook密钥', 'warning');
                return;
            }

            if (!confirm('将发送测试通知到您的IFTTT服务，确定要继续吗？')) {
                return;
            }

            showToast('正在发送测试通知...', 'info');

            try {
                const response = await fetch('{{ url("settings/test-ifttt") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ key: iftttKey })
                });

                const data = await response.json();

                if (data.success || data.code === 9999) {
                    showToast('测试通知已发送，请检查您的设备', 'success');
                } else {
                    showToast('发送失败：' + (data.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('发送失败:', error);
                showToast('发送失败，请检查网络连接', 'error');
            }
        }

        // 导出设置备份
        async function exportSettings() {
            showToast('正在生成设置备份...', 'info');

            try {
                const response = await fetch('{{ url("settings/export") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success || data.code === 9999) {
                    // 创建下载链接
                    const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `montage-settings-${new Date().toISOString().split('T')[0]}.json`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    showToast('设置备份已下载', 'success');
                } else {
                    showToast('导出失败：' + (data.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('导出失败:', error);
                showToast('导出失败，请检查网络连接', 'error');
            }
        }

        // 显示Toast提示
        function showToast(message, type = 'info') {
            const existingToast = document.getElementById('global-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const typeConfig = {
                success: { icon: 'fa-check-circle', bgColor: 'bg-green-100', textColor: 'text-green-800', borderColor: 'border-green-200' },
                error: { icon: 'fa-exclamation-circle', bgColor: 'bg-red-100', textColor: 'text-red-800', borderColor: 'border-red-200' },
                warning: { icon: 'fa-exclamation-triangle', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', borderColor: 'border-yellow-200' },
                info: { icon: 'fa-info-circle', bgColor: 'bg-blue-100', textColor: 'text-blue-800', borderColor: 'border-blue-200' }
            };

            const config = typeConfig[type] || typeConfig.info;

            const toast = document.createElement('div');
            toast.id = 'global-toast';
            toast.className = `fixed top-6 right-6 ${config.bgColor} ${config.textColor} ${config.borderColor} border rounded-lg px-4 py-3 shadow-lg z-[10000] flex items-center space-x-3 max-w-sm fade-in`;
            toast.innerHTML = `
        <i class="fas ${config.icon} text-lg"></i>
        <span>${message}</span>
    `;

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, 3000);
        }
    </script>

    <style>
        /* 自定义样式 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* 设置区块样式 */
        section {
            scroll-margin-top: 20px;
        }

        /* 输入框焦点效果 */
        input:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .lg\\:col-span-2, .lg\\:col-span-1 {
                grid-column: span 1;
            }

            .sticky {
                position: static;
            }

            .grid-cols-2, .grid-cols-3 {
                grid-template-columns: 1fr;
            }
        }

        /* 卡片悬停效果 */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* 快速操作按钮 */
        .btn-outline {
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            transform: translateY(-1px);
        }

        /* 统计数字样式 */
        .stat-number {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }
    </style>
@endsection