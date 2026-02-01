@extends('layouts.app')

@section('title', 'Kindle推送配置 - 蒙太奇')
@section('description', '配置Kindle推送设置，将文章内容推送到您的Kindle设备，享受更好的阅读体验。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题 -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Kindle推送配置</h1>
            <p class="text-gray-600 mt-1">配置推送设置，将精选内容发送到您的Kindle设备</p>
        </div>

        <!-- 成功消息 -->
        @include('common.success')

        <!-- 配置说明卡片 -->
        <div class="card mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">配置说明</h2>
                        <p class="text-sm text-gray-500 mt-1">按照步骤完成Kindle推送设置</p>
                    </div>
                </div>

                <a href="{{ url('/articles') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回阅读
                </a>
            </div>

            <div class="p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Kindle图片 -->
                    <div class="flex-shrink-0">
                        <div class="w-48 mx-auto lg:mx-0">
                            <div class="card-elevated p-4 rounded-xl">
                                <img src="/img/kindle.jpg" alt="Kindle设备" class="w-full rounded-lg shadow-md">
                            </div>
                            <p class="text-center text-xs text-gray-500 mt-2">Kindle阅读器</p>
                        </div>
                    </div>

                    <!-- 配置步骤 -->
                    <div class="flex-1">
                        <div class="space-y-4">
                            <!-- 步骤1 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                                    1
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">配置亚马逊推送邮箱</h3>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-600">中亚用户：</span>
                                            <a href="https://www.amazon.cn/gp/digital/fiona/manage?ie=UTF8&ref_=ya_myk&#manageDevices"
                                               target="_blank"
                                               class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                                点击配置
                                            </a>
                                            <a href="/img/kindle_amazon_cn.jpg" target="_blank"
                                               class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                                                图示1
                                            </a>
                                            <a href="/img/kindle_amazon_cn2.jpg" target="_blank"
                                               class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                                                图示2
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-600">美亚用户：</span>
                                            <a href="https://www.amazon.com/mn/dcw/myx.html#/home/devices/1"
                                               target="_blank"
                                               class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                                点击配置
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 步骤2 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                                    2
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">添加信任邮箱</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        在亚马逊设备管理页面，添加 <strong class="text-blue-600">noreply@congcong.us</strong> 到信任列表
                                    </p>
                                </div>
                            </div>

                            <!-- 步骤3 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                                    3
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">设置推送邮箱</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        在下方表单中填写亚马逊生成的推送邮箱地址
                                    </p>
                                </div>
                            </div>

                            <!-- 步骤4 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                                    4
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">测试推送功能</h3>
                                    <div class="mt-2">
                                        <a href="{{ url('kindle/test') }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                                            <i class="fas fa-paper-plane"></i>
                                            <span>发送测试文件到Kindle</span>
                                        </a>
                                        <p class="text-xs text-gray-500 mt-2">点击后等待几分钟，查看您的Kindle是否收到测试文件</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 配置表单卡片 -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cog text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">推送设置</h2>
                        <p class="text-sm text-gray-500 mt-1">配置您的Kindle推送参数</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- 错误消息 -->
                @include('common.errors')

                <form action="{{ url('setting/'.$setting->id) }}" method="POST" class="space-y-6">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="page_info" value="kindle_page">

                    <!-- Kindle邮箱地址 -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="kindle_email" class="block text-sm font-medium text-gray-700">
                                Kindle推送邮箱
                                <span class="text-red-500">*</span>
                            </label>
                            <button type="button"
                                    id="checkEmail"
                                    class="text-xs px-3 py-1 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors">
                                <i class="fas fa-question-circle mr-1"></i>如何获取？
                            </button>
                        </div>

                        <div class="relative">
                            <input type="email"
                                   name="kindle_email"
                                   id="kindle_email"
                                   value="{{ old('kindle_email', $setting->kindle_email) }}"
                                   placeholder="例如：username_12345@kindle.cn"
                                   class="input w-full pl-10"
                                   required>
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 mt-1">
                            <i class="fas fa-info-circle text-gray-400 mt-0.5"></i>
                            <p class="text-xs text-gray-500">
                                此邮箱地址来自亚马逊的"个人文档设置"页面，通常以 @kindle.cn 或 @kindle.com 结尾
                            </p>
                        </div>
                    </div>

                    <!-- 推送开关 -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            推送开关
                        </label>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="is_start_kindle"
                                       value="0"
                                       {{ empty($setting->is_start_kindle) ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-times text-gray-500"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-700">关闭推送</div>
                                            <div class="text-xs text-gray-500 mt-1">不向Kindle发送内容</div>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="is_start_kindle"
                                       value="1"
                                       {{ $setting->is_start_kindle == 1 ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-green-500"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-700">开启推送</div>
                                            <div class="text-xs text-gray-500 mt-1">允许向Kindle发送内容</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 图片推送选项 -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            图片推送选项
                        </label>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="with_image_push"
                                       value="0"
                                       {{ empty($setting->with_image_push) ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-image-slash text-gray-500"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-700">纯文本推送</div>
                                            <div class="text-xs text-gray-500 mt-1">推送时不包含图片</div>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="with_image_push"
                                       value="1"
                                       {{ $setting->with_image_push == 1 ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-image text-purple-500"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-700">图文推送</div>
                                            <div class="text-xs text-gray-500 mt-1">推送时包含文章图片</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="flex items-start gap-2 mt-2">
                            <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                            <div class="text-xs text-gray-500">
                                <strong>提示：</strong>包含图片的推送文件体积较大，传输时间可能更长，但阅读体验更好
                            </div>
                        </div>
                    </div>

                    <!-- 推送时间设置（扩展功能） -->
                    <div class="space-y-2 pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700">
                            推送时间偏好
                            <span class="text-xs text-gray-400">（可选）</span>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs text-gray-600">每日推送时间</label>
                                <select name="push_time" class="input w-full">
                                    <option value="morning" {{ ($setting->push_time ?? '') == 'morning' ? 'selected' : '' }}>早上 (6:00-8:00)</option>
                                    <option value="noon" {{ ($setting->push_time ?? '') == 'noon' ? 'selected' : '' }}>中午 (12:00-14:00)</option>
                                    <option value="evening" {{ ($setting->push_time ?? '') == 'evening' ? 'selected' : '' }}>晚上 (18:00-20:00)</option>
                                    <option value="night" {{ ($setting->push_time ?? '') == 'night' ? 'selected' : '' }}>夜间 (22:00-24:00)</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs text-gray-600">推送频率</label>
                                <select name="push_frequency" class="input w-full">
                                    <option value="daily" {{ ($setting->push_frequency ?? '') == 'daily' ? 'selected' : '' }}>每日推送</option>
                                    <option value="weekly" {{ ($setting->push_frequency ?? '') == 'weekly' ? 'selected' : '' }}>每周推送</option>
                                    <option value="manual" {{ ($setting->push_frequency ?? '') == 'manual' ? 'selected' : '' }}>手动推送</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs text-gray-600">推送格式</label>
                                <select name="push_format" class="input w-full">
                                    <option value="mobi" {{ ($setting->push_format ?? '') == 'mobi' ? 'selected' : '' }}>MOBI格式</option>
                                    <option value="epub" {{ ($setting->push_format ?? '') == 'epub' ? 'selected' : '' }}>EPUB格式</option>
                                    <option value="pdf" {{ ($setting->push_format ?? '') == 'pdf' ? 'selected' : '' }}>PDF格式</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="btn btn-primary flex-1">
                                <i class="fas fa-save mr-2"></i>
                                保存配置
                            </button>

                            <div class="flex gap-2">
                                <a href="{{ url('/articles') }}" class="btn btn-secondary flex-1">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    返回阅读
                                </a>

                                <a href="{{ url('kindle/test') }}"
                                   class="btn btn-outline flex-1 {{ empty($setting->kindle_email) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ empty($setting->kindle_email) ? 'disabled' : '' }}>
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    测试推送
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 配置帮助卡片 -->
        <div class="card mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-question-circle text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">常见问题</h2>
                        <p class="text-sm text-gray-500 mt-1">配置过程中可能遇到的问题</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Q: 如何找到我的Kindle推送邮箱？</h4>
                        <p class="text-sm text-gray-600">
                            登录亚马逊账户 → 进入"管理我的内容和设备" → 点击"设置" → 在"个人文档设置"中找到您的Kindle邮箱地址
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Q: 推送后多久能收到文件？</h4>
                        <p class="text-sm text-gray-600">
                            通常需要5-15分钟，具体时间取决于亚马逊服务器处理速度和网络状况。请确保Kindle设备已连接Wi-Fi。
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Q: 为什么收不到推送？</h4>
                        <p class="text-sm text-gray-600">
                            请检查：1) Kindle邮箱地址是否正确 2) 发送邮箱是否添加到信任列表 3) Kindle是否连接网络 4) 推送功能是否开启
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Q: 可以推送哪些内容？</h4>
                        <p class="text-sm text-gray-600">
                            目前支持推送已标记"稍后阅读"和"收藏"的文章内容，未来会支持更多自定义推送规则。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* 步骤编号样式 */
        .step-number {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        /* 卡片悬停效果 */
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* 单选卡片选中效果 */
        input[type="radio"]:checked + div {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        /* 测试按钮禁用状态 */
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn:disabled:hover {
            transform: none;
            box-shadow: none;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 邮箱格式验证
            const emailInput = document.getElementById('kindle_email');
            const testButton = document.querySelector('a[href="{{ url("kindle/test") }}"]');

            if (emailInput && testButton) {
                function validateEmail(email) {
                    const regex = /^[^\s@]+@(kindle\.(cn|com)|.*kindle\.amazon\..*)$/i;
                    return regex.test(email);
                }

                function updateTestButton() {
                    const email = emailInput.value.trim();
                    const isValid = validateEmail(email) && email.length > 0;

                    if (isValid) {
                        testButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        testButton.removeAttribute('disabled');
                    } else {
                        testButton.classList.add('opacity-50', 'cursor-not-allowed');
                        testButton.setAttribute('disabled', 'disabled');
                    }
                }

                emailInput.addEventListener('input', updateTestButton);
                emailInput.addEventListener('change', updateTestButton);

                // 初始验证
                updateTestButton();

                // 表单提交验证
                const form = document.querySelector('form[action="{{ url("setting/'.$setting->id.'") }}"]');
                const submitBtn = form?.querySelector('button[type="submit"]');

                if (form && submitBtn) {
                    form.addEventListener('submit', function(e) {
                        const email = emailInput.value.trim();

                        if (!email) {
                            e.preventDefault();
                            emailInput.focus();
                            showNotification('warning', '请输入Kindle推送邮箱地址');
                            return false;
                        }

                        if (!validateEmail(email)) {
                            e.preventDefault();
                            emailInput.focus();
                            showNotification('warning', '请输入有效的Kindle邮箱地址（通常以@kindle.cn或@kindle.com结尾）');
                            return false;
                        }

                        // 显示加载状态
                        const originalBtnText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

                        // 3秒后恢复（防止无限加载）
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        }, 3000);
                    });
                }
            }

            // 帮助按钮点击
            const helpButton = document.getElementById('checkEmail');
            if (helpButton) {
                helpButton.addEventListener('click', function() {
                    showNotification('info',
                        '登录亚马逊 → "管理我的内容和设备" → "设置" → "个人文档设置" → 找到"发送至Kindle"邮箱地址');
                });
            }

            // 测试推送按钮点击
            if (testButton) {
                testButton.addEventListener('click', function(e) {
                    if (this.hasAttribute('disabled')) {
                        e.preventDefault();
                        showNotification('warning', '请先填写有效的Kindle邮箱地址');
                        return false;
                    }

                    // 确认对话框
                    if (!confirm('即将发送测试文件到您的Kindle，确认继续吗？')) {
                        e.preventDefault();
                        return false;
                    }

                    // 显示加载状态
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>发送中...';
                    this.classList.add('opacity-50', 'cursor-not-allowed');

                    // 5秒后恢复
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('opacity-50', 'cursor-not-allowed');
                    }, 5000);
                });
            }

            // 通知函数
            function showNotification(type, message) {
                // 移除已有的通知
                document.querySelectorAll('.notification-item').forEach(el => el.remove());

                const notification = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
                const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

                notification.className = `notification-item fixed top-4 right-4 z-50 fade-in max-w-sm`;
                notification.innerHTML = `
                <div class="card ${bgColor} text-white shadow-xl">
                    <div class="p-4 flex items-center gap-3">
                        <i class="fas ${icon} text-lg"></i>
                        <div class="flex-1">${message}</div>
                        <button class="text-white hover:text-gray-200 close-notification">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

                document.body.appendChild(notification);

                // 点击关闭
                notification.querySelector('.close-notification').addEventListener('click', () => {
                    notification.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.opacity = '0';
                        setTimeout(() => {
                            notification.parentNode.removeChild(notification);
                        }, 300);
                    }
                }, 5000);
            }

            // 扩展功能提示
            const advancedSettings = document.querySelectorAll('select[name="push_time"], select[name="push_frequency"], select[name="push_format"]');
            advancedSettings.forEach(select => {
                select.addEventListener('change', function() {
                    if (this.value !== '') {
                        showNotification('info', '高级推送设置已变更，保存后生效');
                    }
                });
            });
        });
    </script>
@endsection