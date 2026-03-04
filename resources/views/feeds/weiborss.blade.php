@extends('layouts.app')

@section('title', '订阅微博 - 蒙太奇')
@section('description', '订阅微博用户，将微博内容转换为RSS订阅源')

@section('content')
    <div class="fade-in">
        <div class="max-w-3xl mx-auto">
            <!-- 页面标题和导航 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">订阅微博</h1>
                    <p class="text-gray-600 mt-2">将您关注的微博用户转换为RSS订阅源，统一管理阅读</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('feeds/explorer') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-compass mr-2"></i>
                        返回发现
                    </a>
                    <a href="{{ url('articles') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-newspaper mr-2"></i>
                        返回阅读
                    </a>
                </div>
            </div>

            <!-- 成功消息提示 -->
            @include('common.success')

            <!-- 订阅微博卡片 -->
            <div class="card card-elevated">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-red-600 rounded-lg flex items-center justify-center">
                            <i class="fab fa-weibo text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">添加微博订阅源</h2>
                            <p class="text-gray-500 text-sm">输入微博用户ID，系统将自动抓取并转换内容</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- 使用说明 -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 mb-2">如何获取微博用户ID？</h3>
                                <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                                    <li>打开微博网页版，进入要订阅的用户主页</li>
                                    <li>按 <kbd class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-xs font-mono">F12</kbd> 打开开发者工具</li>
                                    <li>在控制台(Console)中输入以下代码：</li>
                                </ol>
                                <div class="mt-3 bg-gray-900 text-gray-100 rounded-lg p-3 text-sm font-mono overflow-x-auto">
                                    <span class="text-green-400">/uid=(\d+)/.exec(</span>
                                    <span class="text-yellow-300">document.querySelector('.opt_box .btn_bed').getAttribute('action-data')</span>
                                    <span class="text-green-400">)[1]</span>
                                </div>
                                <p class="mt-3 text-sm text-gray-600">
                                    执行后将返回用户的数字ID，复制到下方输入框即可
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 订阅表单 -->
                    <form action="/api/v2/feeds" method="post" id="weiboSubscriptionForm" class="space-y-6">
                        {!! csrf_field() !!}csrf

                        <!-- 微博用户ID -->
                        <div class="space-y-2">
                            <label for="weibo_user_id" class="block text-sm font-medium text-gray-700">
                                微博用户ID
                                <span class="text-gray-500 font-normal">（必填）</span>
                            </label>

                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text"
                                       id="weibo_user_id"
                                       name="weibo_user_id"
                                       value="{{ old('weibo_user_id') }}"
                                       required
                                       pattern="\d+"
                                       placeholder="请输入微博用户数字ID，例如：1669879400"
                                       class="input pl-10 w-full {{ $errors->has('weibo_user_id') ? 'border-red-300' : '' }}"
                                       aria-describedby="weiboUserIdHelp">
                            </div>

                            @error('weibo_user_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <div id="weiboUserIdHelp" class="text-sm text-gray-500">
                                必须是纯数字的用户ID，可以在微博用户主页通过开发者工具获取
                            </div>
                        </div>

                        <!-- 分类选择 -->
                        <div class="space-y-2">
                            <label for="category_id" class="block text-sm font-medium text-gray-700">
                                选择分类
                                <span class="text-gray-500 font-normal">（可选）</span>
                            </label>

                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-folder text-gray-400"></i>
                                </div>
                                <select id="category_id"
                                        name="category_id"
                                        class="input pl-10 w-full appearance-none bg-white cursor-pointer">
                                    @foreach ($categorys as $category)
                                        <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>

                            @error('category_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 隐藏字段 -->
                        <input type="hidden" name="feed_type" value="weibo">
                        <input type="hidden" name="feed_name" value="weibo">
                        <input type="hidden" name="url" value="weibo">

                        <!-- 表单操作 -->
                        <div class="pt-6 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <button type="submit"
                                        id="submitButton"
                                        class="btn btn-primary flex-1 sm:flex-none sm:w-auto px-8">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    马上订阅
                                </button>

                                <button type="button"
                                        onclick="window.location.href='{{ url('feeds/explorer') }}'"
                                        class="btn btn-secondary flex-1 sm:flex-none sm:w-auto">
                                    取消
                                </button>
                            </div>

                            <p class="text-sm text-gray-500 mt-4">
                                <i class="fas fa-shield-alt mr-1"></i>
                                我们仅会抓取公开的微博内容，用于个人阅读管理
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 使用步骤说明 -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">订阅流程说明</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- 步骤1 -->
                    <div class="card">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-bold">1</span>
                                </div>
                                <h4 class="font-medium text-gray-900">获取用户ID</h4>
                            </div>
                            <p class="text-gray-600 text-sm">
                                访问微博用户主页，通过开发者工具获取用户的数字ID。
                            </p>
                            <div class="mt-3">
                                <span class="badge badge-primary">F12开发者工具</span>
                                <span class="badge badge-primary">控制台</span>
                            </div>
                        </div>
                    </div>

                    <!-- 步骤2 -->
                    <div class="card">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600 font-bold">2</span>
                                </div>
                                <h4 class="font-medium text-gray-900">填写订阅信息</h4>
                            </div>
                            <p class="text-gray-600 text-sm">
                                将获取的用户ID粘贴到输入框，选择分类（可选）。
                            </p>
                            <div class="mt-3">
                                <span class="badge badge-primary">用户ID</span>
                                <span class="badge badge-primary">分类管理</span>
                            </div>
                        </div>
                    </div>

                    <!-- 步骤3 -->
                    <div class="card">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-bold">3</span>
                                </div>
                                <h4 class="font-medium text-gray-900">开始订阅</h4>
                            </div>
                            <p class="text-gray-600 text-sm">
                                点击订阅按钮，系统将自动抓取微博内容并添加到阅读列表。
                            </p>
                            <div class="mt-3">
                                <span class="badge badge-primary">自动同步</span>
                                <span class="badge badge-primary">实时更新</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 注意事项 -->
            <div class="mt-8 card">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        注意事项
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>仅支持微博网页版的公开用户，非公开账号无法订阅</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>首次订阅可能需要几分钟时间抓取历史内容</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>微博内容更新会有一定延迟，通常在30分钟内同步</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>如有订阅失败，请检查用户ID是否正确或用户是否公开可见</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;
            // 表单验证
            const form = document.getElementById('weiboSubscriptionForm');
            const submitButton = document.getElementById('submitButton');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const userIdInput = document.getElementById('weibo_user_id');
                const userId = userIdInput.value.trim();
                const categoryIdInput = document.getElementById('category_id');

                // 验证用户ID是否为数字
                if (!/^\d+$/.test(userId)) {
                    showValidationError('微博用户ID必须是纯数字');
                    return false;
                }

                // 验证用户ID长度（微博用户ID通常是10位左右）
                if (userId.length < 5 || userId.length > 20) {
                    showValidationError('微博用户ID格式不正确，请检查是否正确获取');
                    return false;
                }
                if (!categoryIdInput || !categoryIdInput.value) {
                    showValidationError('请选择分类');
                    return false;
                }
                if (!apiRequest) {
                    showValidationError('API客户端未初始化');
                    return false;
                }

                // 显示加载状态
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>订阅中...';
                submitButton.disabled = true;

                apiRequest('POST', '/feeds', {
                    weibo_user_id: userId,
                    category_id: categoryIdInput.value,
                    feed_type: 'weibo',
                    feed_name: 'weibo',
                    url: 'weibo'
                }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        window.location.href = '{{ url('/feeds') }}';
                        return;
                    }
                    showValidationError((resp && resp.msg) ? resp.msg : '订阅失败，请稍后重试');
                }).catch(function() {
                    showValidationError('网络错误，请稍后重试');
                }).finally(function() {
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                });
            });

            // 显示验证错误
            function showValidationError(message) {
                // 移除旧的错误提示
                const oldError = document.querySelector('.validation-error');
                if (oldError) oldError.remove();

                // 创建新的错误提示
                const errorDiv = document.createElement('div');
                errorDiv.className = 'validation-error mt-2 p-3 bg-red-50 border border-red-200 rounded-lg';
                errorDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span class="text-sm text-red-700">${message}</span>
            </div>
        `;

                // 插入到用户ID输入框后面
                const userIdInput = document.getElementById('weibo_user_id');
                userIdInput.parentNode.appendChild(errorDiv);

                // 聚焦到输入框
                userIdInput.focus();

                // 5秒后自动移除错误提示
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }

            // 示例用户ID提示
            const exampleIds = ['1669879400', '1195242865', '1642634100'];
            let exampleIndex = 0;

            // 添加示例用户ID提示
            const userIdInput = document.getElementById('weibo_user_id');
            const helpText = document.getElementById('weiboUserIdHelp');

            if (userIdInput && helpText) {
                const exampleLink = document.createElement('a');
                exampleLink.href = '#';
                exampleLink.className = 'text-blue-600 hover:text-blue-800 ml-2';
                exampleLink.textContent = '查看示例';
                exampleLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    userIdInput.value = exampleIds[exampleIndex];
                    exampleIndex = (exampleIndex + 1) % exampleIds.length;
                });

                helpText.appendChild(exampleLink);
            }
        });
    </script>

    <style>
        /* 键盘按键样式 */
        kbd {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875em;
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
            background-color: var(--gray-100);
            border: 1px solid var(--gray-300);
        }

        /* 代码块样式 */
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875em;
            background-color: var(--gray-100);
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
        }

        /* 下拉菜单箭头隐藏 */
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* 表单焦点效果 */
        .input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: var(--primary-color);
        }

        /* 步骤卡片悬停效果 */
        .card:hover .card-step-number {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }
    </style>
@endsection
