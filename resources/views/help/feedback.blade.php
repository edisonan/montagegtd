@extends('layouts.app')

@section('title', '提交反馈 - 蒙太奇')
@section('description', '向我们提供宝贵意见，帮助我们改进产品')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center text-sm text-gray-600 mb-3">
                        <a href="{{ url('/') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            <i class="fas fa-home mr-1"></i>首页
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <a href="{{ url('/help') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            帮助中心
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <span class="text-gray-900 font-medium">提交反馈</span>
                    </nav>

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-comment-dots text-primary-color mr-3"></i>
                        提交反馈
                    </h1>
                    <p class="text-gray-600 mt-2">您的每一条反馈对我们都非常重要，感谢您帮助我们改进</p>
                </div>

                <!-- 快捷操作 -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('/help') }}" class="btn btn-outline">
                        <i class="fas fa-question-circle mr-2"></i>
                        帮助中心
                    </a>
                    <a href="mailto:accacc@126.com?subject=蒙太奇反馈" class="btn btn-primary">
                        <i class="fas fa-envelope mr-2"></i>
                        邮件反馈
                    </a>
                </div>
            </div>

            <!-- 引导说明 -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-2xl p-6 mb-8">
                <div class="flex items-start">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-lightbulb text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">反馈指南</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-start">
                                <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </div>
                                <p class="text-sm text-gray-700">请尽量提供详细信息，帮助我们更好地理解问题</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                    <i class="fas fa-exclamation text-yellow-600 text-xs"></i>
                                </div>
                                <p class="text-sm text-gray-700">如果是Bug反馈，请提供复现步骤和截图</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                    <i class="fas fa-bolt text-purple-600 text-xs"></i>
                                </div>
                                <p class="text-sm text-gray-700">功能建议请描述使用场景和期望效果</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 反馈类型选择 -->
        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-clipboard-list text-primary-color mr-2"></i>
                    反馈类型
                </h2>
                <p class="text-sm text-gray-600 mt-1">请选择您要提交的反馈类型</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="feedback_type" value="bug" class="sr-only peer" checked>
                        <div class="p-5 border-2 border-gray-200 rounded-xl hover:border-red-300 hover:bg-red-50
                              peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:shadow-sm
                              transition-all duration-200">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-bug text-red-600"></i>
                                </div>
                                <span class="font-semibold text-gray-900">Bug反馈</span>
                            </div>
                            <p class="text-sm text-gray-600">报告功能异常、页面错误、系统问题等</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="feedback_type" value="feature" class="sr-only peer">
                        <div class="p-5 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50
                              peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm
                              transition-all duration-200">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-lightbulb text-blue-600"></i>
                                </div>
                                <span class="font-semibold text-gray-900">功能建议</span>
                            </div>
                            <p class="text-sm text-gray-600">提出新功能想法或现有功能改进建议</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="feedback_type" value="experience" class="sr-only peer">
                        <div class="p-5 border-2 border-gray-200 rounded-xl hover:border-green-300 hover:bg-green-50
                              peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-sm
                              transition-all duration-200">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-comments text-green-600"></i>
                                </div>
                                <span class="font-semibold text-gray-900">体验反馈</span>
                            </div>
                            <p class="text-sm text-gray-600">分享使用感受、界面体验、流程优化建议</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 反馈表单卡片 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-edit text-primary-color mr-2"></i>
                    填写反馈内容
                </h2>
                <p class="text-sm text-gray-600 mt-1">请详细描述您的问题或建议</p>
            </div>

            <div class="p-6">
                <!-- 成功消息 -->
                @if(session('success'))
                    <div class="mb-6 p-5 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-green-800 mb-1">反馈提交成功！</h4>
                                <p class="text-green-700">{{ session('success') }}</p>
                                <p class="text-sm text-green-600 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    我们会在3个工作日内回复您的反馈，感谢您的支持！
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 错误消息 -->
                @include('common.errors')

                <!-- 反馈表单 -->
                <form action="javascript:void(0)" method="POST" class="space-y-6" id="feedbackForm">
                    {!! csrf_field() !!}

                    <div class="space-y-6">
                        <!-- 反馈来源 -->
                        <div class="space-y-3">
                            <label for="from" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-user-circle text-gray-400 mr-2 text-sm"></i>
                                反馈来源
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        name="from"
                                        id="from"
                                        value="{{ old('from') }}"
                                        class="input w-full pl-10"
                                        placeholder="请输入您的名称或邮箱（可选）"
                                        maxlength="50"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">
                                填写后可方便我们与您联系，了解反馈详情。不填写将匿名提交。
                            </p>
                        </div>

                        <!-- 反馈内容 -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label for="content" class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-align-left text-gray-400 mr-2 text-sm"></i>
                                    反馈内容
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <span id="charCount" class="text-sm text-gray-400">0/2000</span>
                            </div>

                            <div class="relative">
                            <textarea
                                    name="content"
                                    id="content"
                                    rows="8"
                                    class="input w-full resize-none"
                                    placeholder="请详细描述您遇到的问题或建议，越详细越能帮助我们改进..."
                                    required
                                    maxlength="2000"
                                    data-counter-id="charCount"
                            >{{ old('content') }}</textarea>

                                <!-- 插入常用问题模板 -->
                                <div class="absolute bottom-3 right-3 flex space-x-2">
                                    <button type="button" onclick="insertTemplate('bug')"
                                            class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-full hover:bg-red-200">
                                        Bug模板
                                    </button>
                                    <button type="button" onclick="insertTemplate('feature')"
                                            class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200">
                                        功能模板
                                    </button>
                                </div>
                            </div>

                            <!-- 内容提示 -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start mb-2">
                                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                                    <p class="text-sm text-gray-700">建议包含以下信息：</p>
                                </div>
                                <ul class="text-sm text-gray-600 space-y-1 ml-6 list-disc">
                                    <li>问题发生的具体页面或功能模块</li>
                                    <li>重现问题的详细步骤</li>
                                    <li>期望的结果和实际结果</li>
                                    <li>浏览器/设备信息和截图（如有）</li>
                                </ul>
                            </div>
                        </div>

                        <!-- 附件上传 -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-paperclip text-gray-400 mr-2 text-sm"></i>
                                附件上传（可选）
                            </label>

                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors duration-200">
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-600 mb-2">拖放文件到此处，或点击选择文件</p>
                                <p class="text-sm text-gray-500 mb-4">支持图片、文档等，单个文件不超过5MB</p>
                                <input type="file" id="attachment" class="hidden" accept="image/*,.pdf,.doc,.docx">
                                <label for="attachment" class="btn btn-outline cursor-pointer">
                                    <i class="fas fa-folder-open mr-2"></i>
                                    选择文件
                                </label>
                            </div>

                            <!-- 文件列表预览 -->
                            <div id="fileList" class="space-y-2 hidden">
                                <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-image text-blue-500 mr-3 text-lg"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">screenshot.png</p>
                                            <p class="text-xs text-gray-500">2.4 MB</p>
                                        </div>
                                    </div>
                                    <button type="button" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 联系信息（可选） -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-envelope-open-text text-gray-400 mr-2 text-sm"></i>
                                联系方式（可选）
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-xs font-medium text-gray-600 mb-1">
                                        邮箱地址
                                    </label>
                                    <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            class="input w-full"
                                            placeholder="您的邮箱（用于接收回复）"
                                    >
                                </div>
                                <div>
                                    <label for="contact" class="block text-xs font-medium text-gray-600 mb-1">
                                        其他联系方式
                                    </label>
                                    <input
                                            type="text"
                                            name="contact"
                                            id="contact"
                                            class="input w-full"
                                            placeholder="微信/QQ/电话等"
                                    >
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">
                                填写联系方式，我们将在处理反馈后与您联系（如需）
                            </p>
                        </div>

                        <!-- 隐私声明 -->
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-shield-alt text-blue-500 mr-3 mt-0.5"></i>
                                <div>
                                    <h4 class="font-medium text-blue-800 mb-1">隐私声明</h4>
                                    <p class="text-sm text-blue-700">
                                        我们承诺保护您的隐私。您提交的反馈内容仅用于改进产品，联系方式仅用于回复反馈，不会被用于其他目的或与第三方分享。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 表单操作按钮 -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ url('/help') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>
                            返回帮助中心
                        </a>

                        <div class="flex space-x-3">
                            <button type="button" onclick="resetForm()" class="btn btn-outline">
                                <i class="fas fa-redo mr-2"></i>
                                重置表单
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-2"></i>
                                提交反馈
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 其他反馈渠道 -->
        <div class="card mt-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-external-link-alt text-primary-color mr-2"></i>
                    其他反馈渠道
                </h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="mailto:accacc@126.com?subject=蒙太奇反馈"
                       class="p-5 border border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-md transition-all duration-200 text-center group">
                        <div class="w-12 h-12 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                            <i class="fas fa-envelope text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">邮件反馈</h4>
                        <p class="text-sm text-gray-600">accacc@126.com</p>
                        <p class="text-xs text-gray-500 mt-2">适合详细问题描述和附件发送</p>
                    </a>

                    <a href="https://gitee.com/accacc/task/issues"
                       target="_blank"
                       class="p-5 border border-gray-200 rounded-xl hover:border-green-300 hover:shadow-md transition-all duration-200 text-center group">
                        <div class="w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                            <i class="fab fa-git-alt text-green-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Gitee Issues</h4>
                        <p class="text-sm text-gray-600">开源社区反馈</p>
                        <p class="text-xs text-gray-500 mt-2">技术问题讨论和功能建议</p>
                    </a>

                    <a href="{{ url('/about') }}"
                       class="p-5 border border-gray-200 rounded-xl hover:border-purple-300 hover:shadow-md transition-all duration-200 text-center group">
                        <div class="w-12 h-12 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">用户社区</h4>
                        <p class="text-sm text-gray-600">加入用户交流</p>
                        <p class="text-xs text-gray-500 mt-2">与其他用户交流使用经验</p>
                    </a>
                </div>
            </div>
        </div>
    </div>


        <script>
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;

            // 初始化函数
            document.addEventListener('DOMContentLoaded', function() {
                var fromInput = document.getElementById('from');
                if (fromInput && !fromInput.value) {
                    var qsFrom = new URLSearchParams(window.location.search).get('from');
                    if (qsFrom) {
                        fromInput.value = qsFrom;
                    }
                }

                // 初始化字符计数器
                initCharCounter();

                // 自动聚焦到反馈内容
                document.getElementById('content').focus();

                // 文件上传处理
                initFileUpload();

                // 表单提交处理
                initFormSubmit();
            });

            // 初始化字符计数器
            function initCharCounter() {
                const textarea = document.getElementById('content');
                const counter = document.getElementById('charCount');

                function updateCounter() {
                    const length = textarea.value.length;
                    const maxLength = textarea.getAttribute('maxlength');
                    counter.textContent = `${length}/${maxLength}`;

                    // 根据长度调整颜色
                    if (length > maxLength * 0.9) {
                        counter.classList.remove('text-gray-400', 'text-red-600');
                        counter.classList.add('text-yellow-600');
                    } else if (length > maxLength) {
                        counter.classList.remove('text-gray-400', 'text-yellow-600');
                        counter.classList.add('text-red-600');
                    } else {
                        counter.classList.remove('text-yellow-600', 'text-red-600');
                        counter.classList.add('text-gray-400');
                    }
                }

                // 初始更新
                updateCounter();

                // 监听输入
                textarea.addEventListener('input', updateCounter);
            }

            // 插入反馈模板
            function insertTemplate(type) {
                const textarea = document.getElementById('content');
                let template = '';

                if (type === 'bug') {
                    template = `## Bug反馈

### 问题描述


### 重现步骤
1.
2.
3.

### 期望结果


### 实际结果


### 环境信息
- 系统版本：
- 浏览器：
- 设备类型：

### 截图
（请描述截图内容）`;
                } else if (type === 'feature') {
                    template = `## 功能建议

### 建议内容


### 使用场景


### 期望效果


### 优先级
□ 高  □ 中  □ 低

### 相关参考
（如有类似功能参考，请说明）`;
                }

                // 插入模板并聚焦
                if (textarea.value.trim()) {
                    textarea.value += '\n\n' + template;
                } else {
                    textarea.value = template;
                }

                // 更新字符计数
                const event = new Event('input');
                textarea.dispatchEvent(event);

                // 显示提示
                showToast('success', `${type === 'bug' ? 'Bug反馈' : '功能建议'}模板已插入`);
            }

            // 初始化文件上传
            function initFileUpload() {
                const fileInput = document.getElementById('attachment');
                const fileList = document.getElementById('fileList');
                const dropZone = document.querySelector('.border-dashed');

                // 点击选择文件
                fileInput.addEventListener('change', handleFileSelect);

                // 拖放功能
                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('border-blue-500', 'bg-blue-50');
                });

                dropZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('border-blue-500', 'bg-blue-50');
                });

                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('border-blue-500', 'bg-blue-50');

                    if (e.dataTransfer.files.length) {
                        handleFileSelect({ target: { files: e.dataTransfer.files } });
                    }
                });
            }

            // 处理文件选择
            function handleFileSelect(e) {
                const files = e.target.files;
                const fileList = document.getElementById('fileList');

                if (files.length > 0) {
                    // 显示文件列表区域
                    fileList.classList.remove('hidden');

                    // 检查文件大小
                    for (let file of files) {
                        if (file.size > 5 * 1024 * 1024) {
                            showToast('error', `文件 ${file.name} 超过5MB限制`);
                            continue;
                        }

                        // 添加文件到列表
                        addFileToList(file);
                    }
                }
            }

            // 添加文件到列表
            function addFileToList(file) {
                const fileList = document.getElementById('fileList');

                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg animate-fade-in';

                // 根据文件类型选择图标
                let iconClass = 'fa-file';
                let iconColor = 'text-gray-500';

                if (file.type.startsWith('image/')) {
                    iconClass = 'fa-file-image';
                    iconColor = 'text-blue-500';
                } else if (file.type.includes('pdf')) {
                    iconClass = 'fa-file-pdf';
                    iconColor = 'text-red-500';
                } else if (file.type.includes('word') || file.type.includes('document')) {
                    iconClass = 'fa-file-word';
                    iconColor = 'text-blue-600';
                }

                fileItem.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${iconClass} ${iconColor} mr-3 text-lg"></i>
                <div>
                    <p class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${file.name}">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                </div>
            </div>
            <button type="button" onclick="removeFile(this)" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        `;

                fileList.appendChild(fileItem);
                showToast('success', `文件 ${file.name} 已添加`);
            }

            // 移除文件
            function removeFile(button) {
                const fileItem = button.closest('div');
                fileItem.classList.add('opacity-0', 'translate-x-4');

                setTimeout(() => {
                    fileItem.remove();

                    // 如果没有文件了，隐藏文件列表
                    if (document.querySelectorAll('#fileList > div').length === 1) {
                        document.getElementById('fileList').classList.add('hidden');
                    }
                }, 300);
            }

            // 格式化文件大小
            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                else return (bytes / 1048576).toFixed(1) + ' MB';
            }

            // 重置表单
            function resetForm() {
                if (confirm('确定要重置表单吗？所有填写的内容都将丢失。')) {
                    document.getElementById('feedbackForm').reset();
                    document.getElementById('fileList').classList.add('hidden');

                    // 重置字符计数器
                    const event = new Event('input');
                    document.getElementById('content').dispatchEvent(event);

                    showToast('info', '表单已重置');
                }
            }

            // 初始化表单提交
            function initFormSubmit() {
                const form = document.getElementById('feedbackForm');

                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // 验证必填字段
                    const content = document.getElementById('content').value.trim();
                    if (!content) {
                        document.getElementById('content').focus();
                        showToast('error', '请填写反馈内容');
                        return;
                    }

                    // 验证内容长度
                    if (content.length < 10) {
                        showToast('error', '反馈内容太短，请至少填写10个字符');
                        return;
                    }
                    if (!apiRequest) {
                        showToast('error', 'API客户端未初始化');
                        return;
                    }

                    // 显示提交状态
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>提交中...';
                    submitBtn.disabled = true;

                    apiRequest('POST', '/help/feedback', {
                        from: document.getElementById('from').value || '',
                        content: content
                    }).then(function(resp) {
                        if (resp && resp.code === 9999) {
                            showToast('success', '反馈提交成功，感谢你的建议');
                            form.reset();
                            const event = new Event('input');
                            document.getElementById('content').dispatchEvent(event);
                            setTimeout(function() {
                                window.location.href = '{{ url('/index') }}';
                            }, 500);
                            return;
                        }
                        showToast('error', (resp && resp.msg) ? resp.msg : '提交失败，请稍后重试');
                    }).catch(function() {
                        showToast('error', '提交失败，请稍后重试');
                    }).finally(function() {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }

            // 显示提示消息
            function showToast(type, message) {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' :
                        type === 'error' ? 'bg-red-500' :
                            type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' :
                    type === 'error' ? 'exclamation-circle' :
                        type === 'info' ? 'info-circle' : 'bell'} mr-3"></i>
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

        <style>
            /* 反馈类型选择动画 */
            input[name="feedback_type"]:checked + div {
                animation: typeSelect 0.3s ease-out;
                transform: translateY(-2px);
            }

            @keyframes typeSelect {
                0% { transform: translateY(0); }
                50% { transform: translateY(-4px); }
                100% { transform: translateY(-2px); }
            }

            /* 文件上传区域样式 */
            .border-dashed:hover {
                border-color: var(--primary-color);
                background-color: rgba(59, 130, 246, 0.02);
                transition: all 0.2s ease;
            }

            /* 字符计数动画 */
            #charCount {
                transition: color 0.2s ease;
            }

            /* 反馈卡片悬停效果 */
            .card a.group:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            /* 表单输入框聚焦效果 */
            .input:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            textarea.input:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            /* 文件项动画 */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in {
                animation: fadeIn 0.3s ease-out;
            }

            /* 按钮加载动画 */
            .fa-spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* 渐变背景动画 */
            .bg-gradient-to-r {
                background-size: 200% 200%;
                animation: gradientShift 15s ease infinite;
            }

            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
        </style>
    @endsection
