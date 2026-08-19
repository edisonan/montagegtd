@extends('layouts.app')

@section('title', '创建课程 - 蒙太奇')
@section('description', '创建新的学习课程，系统化您的学习计划')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题和导航 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center text-sm text-gray-600 mb-3">
                        <a href="{{ url('/') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            <i class="fas fa-home mr-1"></i>首页
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <a href="{{ url('/mycourse') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            我的课程
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <a href="{{ url('/courses') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            课程管理
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <span class="text-gray-900 font-medium">创建课程</span>
                    </nav>

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center mr-4 shadow-sm">
                            <i class="fas fa-plus-circle text-blue-600 text-xl"></i>
                        </div>
                        创建新课程
                    </h1>
                    <p class="text-gray-600 mt-2">记录您的学习资源，开启新的学习旅程</p>
                </div>

                <!-- 返回按钮 -->
                <a href="{{ url('/courses') }}" class="btn btn-outline flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>
        </div>

        <!-- 创建课程卡片 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-primary-color mr-2"></i>
                    课程基本信息
                </h2>
                <p class="text-sm text-gray-600 mt-1">填写课程的核心信息，带 <span class="text-red-500">*</span> 为必填项</p>
            </div>

            <div class="p-6">
                <!-- 成功/错误消息 -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-green-800">创建成功</h4>
                                <p class="text-green-700 text-sm mt-1">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-red-800">提交失败</h4>
                                <ul class="text-red-700 text-sm mt-1 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 创建课程表单 -->
                <form action="javascript:void(0)" method="POST" id="createCourseForm" class="space-y-8">

                    <!-- 第一行：课程标题和讲师 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label for="title" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-book text-gray-400 mr-2 text-sm"></i>
                                课程标题
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        id="title"
                                        name="title"
                                        value="{{ old('title') }}"
                                        class="input w-full pl-10"
                                        placeholder="例如：React从入门到实战"
                                        required
                                        autofocus
                                        maxlength="100"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-heading"></i>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm">
                                <p class="text-gray-500">清晰简洁的标题有助于识别</p>
                                <span id="titleCharCount" class="text-gray-400">0/100</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label for="instructor" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-chalkboard-teacher text-gray-400 mr-2 text-sm"></i>
                                讲师/机构
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        id="instructor"
                                        name="instructor"
                                        value="{{ old('instructor') }}"
                                        class="input w-full pl-10"
                                        placeholder="例如：张三老师、Coursera机构"
                                        maxlength="50"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">填写讲师姓名或机构名称</p>
                        </div>
                    </div>

                    <!-- 第二行：平台和难度 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label for="platform" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-desktop text-gray-400 mr-2 text-sm"></i>
                                学习平台
                            </label>
                            <div class="relative">
                                <select id="platform" name="platform" class="input w-full pl-10 appearance-none">
                                    <option value="">选择平台（可选）</option>
                                    <option value="Coursera" {{ old('platform') == 'Coursera' ? 'selected' : '' }}>
                                        Coursera
                                    </option>
                                    <option value="Udemy" {{ old('platform') == 'Udemy' ? 'selected' : '' }}>
                                        Udemy
                                    </option>
                                    <option value="edX" {{ old('platform') == 'edX' ? 'selected' : '' }}>
                                        edX
                                    </option>
                                    <option value="B站" {{ old('platform') == 'B站' ? 'selected' : '' }}>
                                        Bilibili
                                    </option>
                                    <option value="YouTube" {{ old('platform') == 'YouTube' ? 'selected' : '' }}>
                                        YouTube
                                    </option>
                                    <option value="慕课网" {{ old('platform') == '慕课网' ? 'selected' : '' }}>
                                        慕课网
                                    </option>
                                    <option value="极客时间" {{ old('platform') == '极客时间' ? 'selected' : '' }}>
                                        极客时间
                                    </option>
                                    <option value="其他" {{ old('platform') == '其他' ? 'selected' : '' }}>
                                        其他平台
                                    </option>
                                </select>
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-tv"></i>
                                </div>
                                <div class="absolute right-3 top-3 text-gray-400 pointer-events-none">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label for="difficulty" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-chart-line text-gray-400 mr-2 text-sm"></i>
                                难度级别
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="relative cursor-pointer">
                                    <input
                                            type="radio"
                                            name="difficulty"
                                            value="beginner"
                                            class="sr-only peer"
                                            {{ old('difficulty') == 'beginner' ? 'checked' : '' }}
                                    >
                                    <div class="p-3 text-center border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50
                                          peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-sm">
                                        <div class="font-medium text-gray-900">初级</div>
                                        <div class="text-xs text-gray-600 mt-1">适合新手入门</div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input
                                            type="radio"
                                            name="difficulty"
                                            value="intermediate"
                                            class="sr-only peer"
                                            {{ old('difficulty', 'intermediate') == 'intermediate' ? 'checked' : '' }}
                                    >
                                    <div class="p-3 text-center border border-gray-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50
                                          peer-checked:border-yellow-500 peer-checked:bg-yellow-50 peer-checked:shadow-sm">
                                        <div class="font-medium text-gray-900">中级</div>
                                        <div class="text-xs text-gray-600 mt-1">需要基础知识</div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input
                                            type="radio"
                                            name="difficulty"
                                            value="advanced"
                                            class="sr-only peer"
                                            {{ old('difficulty') == 'advanced' ? 'checked' : '' }}
                                    >
                                    <div class="p-3 text-center border border-gray-200 rounded-lg hover:border-red-300 hover:bg-red-50
                                          peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:shadow-sm">
                                        <div class="font-medium text-gray-900">高级</div>
                                        <div class="text-xs text-gray-600 mt-1">深入专业知识</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 第三行：学习时长和课程链接 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label for="estimated_hours" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="far fa-clock text-gray-400 mr-2 text-sm"></i>
                                预计学习时长（小时）
                            </label>
                            <div class="relative">
                                <input
                                        type="number"
                                        id="estimated_hours"
                                        name="estimated_hours"
                                        value="{{ old('estimated_hours') }}"
                                        class="input w-full pl-10"
                                        placeholder="例如：24"
                                        min="0"
                                        max="1000"
                                        step="1"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">预估完成课程需要的时间</p>
                        </div>

                        <div class="space-y-3">
                            <label for="public_url" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-link text-gray-400 mr-2 text-sm"></i>
                                课程链接
                            </label>
                            <div class="relative">
                                <input
                                        type="url"
                                        id="public_url"
                                        name="public_url"
                                        value="{{ old('public_url') }}"
                                        class="input w-full pl-10"
                                        placeholder="https://example.com/course"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-external-link-alt"></i>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">课程的官方链接或学习地址</p>
                        </div>
                    </div>

                    <!-- 第四行：封面图片和标签 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label for="cover_image_url" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-image text-gray-400 mr-2 text-sm"></i>
                                封面图片URL
                            </label>
                            <div class="relative">
                                <input
                                        type="url"
                                        id="cover_image_url"
                                        name="cover_image_url"
                                        value="{{ old('cover_image_url') }}"
                                        class="input w-full pl-10"
                                        placeholder="https://example.com/course-cover.jpg"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-photo-video"></i>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">课程封面图片链接（可选）</p>

                            <!-- 图片预览 -->
                            <div id="imagePreview" class="hidden mt-3">
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <p class="text-sm text-gray-700 mb-2">封面预览：</p>
                                    <img id="previewImage" src="" alt="封面预览" class="max-w-full h-32 object-cover rounded-lg">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label for="tags" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-tags text-gray-400 mr-2 text-sm"></i>
                                课程标签
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        id="tags"
                                        name="tags"
                                        value="{{ old('tags') }}"
                                        class="input w-full pl-10"
                                        placeholder="例如：编程, JavaScript, React, 前端开发"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm">
                                <p class="text-gray-500">用逗号分隔多个标签</p>
                                <span id="tagsCharCount" class="text-gray-400">0/100</span>
                            </div>

                            <!-- 热门标签建议 -->
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">常用标签：</p>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" onclick="addTag('编程')" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm hover:bg-blue-200 transition-colors">
                                        编程
                                    </button>
                                    <button type="button" onclick="addTag('前端')" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm hover:bg-green-200 transition-colors">
                                        前端
                                    </button>
                                    <button type="button" onclick="addTag('后端')" class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm hover:bg-purple-200 transition-colors">
                                        后端
                                    </button>
                                    <button type="button" onclick="addTag('数据分析')" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm hover:bg-yellow-200 transition-colors">
                                        数据分析
                                    </button>
                                    <button type="button" onclick="addTag('设计')" class="px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-sm hover:bg-pink-200 transition-colors">
                                        设计
                                    </button>
                                    <button type="button" onclick="addTag('英语')" class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm hover:bg-indigo-200 transition-colors">
                                        英语
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 第五行：课程描述 -->
                    <div class="space-y-3">
                        <label for="description" class="block text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-align-left text-gray-400 mr-2 text-sm"></i>
                            课程描述
                        </label>
                        <div class="relative">
                        <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="input w-full resize-none"
                                placeholder="描述课程的主要内容、特色、适用人群等..."
                                maxlength="500"
                        >{{ old('description') }}</textarea>
                        </div>
                        <div class="flex justify-between text-sm">
                            <p class="text-gray-500">详细介绍课程内容，最多500字符</p>
                            <span id="descCharCount" class="text-gray-400">0/500</span>
                        </div>
                    </div>

                    <!-- 第六行：公开状态 -->
                    <div class="space-y-3">
                        <label for="public_status" class="block text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-eye text-gray-400 mr-2 text-sm"></i>
                            公开状态
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="relative cursor-pointer">
                                <input
                                        type="radio"
                                        name="public_status"
                                        value="1"
                                        class="sr-only peer"
                                        {{ old('public_status') == 1 ? 'checked' : '' }}
                                >
                                <div class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50
                                      peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-md bg-blue-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-lock text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">私有</div>
                                            <div class="text-sm text-gray-600">仅自己可见</div>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input
                                        type="radio"
                                        name="public_status"
                                        value="2"
                                        class="sr-only peer"
                                        {{ old('public_status', 2) == 2 ? 'checked' : '' }}
                                >
                                <div class="p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50
                                      peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-sm">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-md bg-green-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-users text-green-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">公开待审核</div>
                                            <div class="text-sm text-gray-600">审核通过后对所有人可见</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                            公开课程需等待管理员审核，审核通过后所有用户均可查看
                        </p>
                    </div>

                    <!-- 表单操作按钮 -->
                    <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                        <div>
                            <a href="{{ url('/courses') }}" class="btn btn-secondary flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                取消
                            </a>
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" onclick="resetForm()" class="btn btn-outline flex items-center">
                                <i class="fas fa-redo mr-2"></i>
                                重置
                            </button>
                            <button type="submit" class="btn btn-primary flex items-center" id="submitBtn">
                                <i class="fas fa-plus-circle mr-2"></i>
                                创建课程
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 创建提示卡片 -->
        <div class="card mt-6">
            <div class="p-6">
                <div class="flex items-start">
                    <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-lightbulb text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">创建课程的小贴士</h3>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                <span><strong>标题要明确</strong>：清晰描述课程内容，便于后续查找</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                <span><strong>添加准确标签</strong>：合理标签可以帮助您和其他用户分类查找课程</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                <span><strong>设置适当难度</strong>：准确评估难度有助于制定学习计划</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                <span><strong>提供完整链接</strong>：课程链接方便直接访问学习资源</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <script>
            // 字符计数功能
            document.addEventListener('DOMContentLoaded', function() {
                var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                    ? window.TaskApiBridge.requestWithFallback
                    : null;
                const titleInput = document.getElementById('title');
                const tagsInput = document.getElementById('tags');
                const descInput = document.getElementById('description');
                const coverImageInput = document.getElementById('cover_image_url');

                // 初始化字符计数
                updateCharCount(titleInput, 'titleCharCount', 100);
                updateCharCount(tagsInput, 'tagsCharCount', 100);
                updateCharCount(descInput, 'descCharCount', 500);

                // 实时更新字符计数
                titleInput.addEventListener('input', () => updateCharCount(titleInput, 'titleCharCount', 100));
                tagsInput.addEventListener('input', () => updateCharCount(tagsInput, 'tagsCharCount', 100));
                descInput.addEventListener('input', () => updateCharCount(descInput, 'descCharCount', 500));

                // 封面图片预览
                coverImageInput.addEventListener('input', function() {
                    const url = this.value.trim();
                    const previewDiv = document.getElementById('imagePreview');
                    const previewImg = document.getElementById('previewImage');

                    if (isValidImageUrl(url)) {
                        previewImg.src = url;
                        previewDiv.classList.remove('hidden');
                    } else {
                        previewDiv.classList.add('hidden');
                    }
                });

                // 自动聚焦到标题输入框
                titleInput.focus();

                // 表单提交处理
                document.getElementById('createCourseForm').addEventListener('submit', function(e) {
                    const title = titleInput.value.trim();

                    if (!title) {
                        e.preventDefault();
                        titleInput.focus();
                        showToast('error', '请输入课程标题');
                        return;
                    }
                    if (!apiRequest) {
                        e.preventDefault();
                        showToast('error', 'API客户端未初始化');
                        return;
                    }

                    e.preventDefault();

                    // 显示加载状态
                    const submitBtn = document.getElementById('submitBtn');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin mr-2"></i>
                创建中...
                    `;
                    submitBtn.disabled = true;

                    var selectedDifficulty = document.querySelector('input[name="difficulty"]:checked');
                    var selectedPublicStatus = document.querySelector('input[name="public_status"]:checked');

                    apiRequest('POST', '/courses', {
                        title: document.getElementById('title') ? document.getElementById('title').value.trim() : '',
                        instructor: document.getElementById('instructor') ? document.getElementById('instructor').value.trim() : '',
                        platform: document.getElementById('platform') ? document.getElementById('platform').value : '',
                        difficulty: selectedDifficulty ? selectedDifficulty.value : 'intermediate',
                        estimated_hours: document.getElementById('estimated_hours') ? document.getElementById('estimated_hours').value : '',
                        public_url: document.getElementById('public_url') ? document.getElementById('public_url').value.trim() : '',
                        cover_image_url: document.getElementById('cover_image_url') ? document.getElementById('cover_image_url').value.trim() : '',
                        description: document.getElementById('description') ? document.getElementById('description').value.trim() : '',
                        public_status: selectedPublicStatus ? Number(selectedPublicStatus.value) : 2,
                        tags: document.getElementById('tags') ? document.getElementById('tags').value.split(',').map(function(t){ return t.trim(); }).filter(Boolean) : []
                    }).then(function(response) {
                        if (response && response.code === 9999) {
                            showToast('success', response.msg || '课程创建成功');
                            setTimeout(function() {
                                window.location.href = "{{ url('/courses') }}";
                            }, 300);
                            return;
                        }
                        showToast('error', (response && response.msg) ? response.msg : '课程创建失败');
                    }).catch(function() {
                        showToast('error', '网络错误，请稍后重试');
                    }).finally(function() {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            });

            // 更新字符计数
            function updateCharCount(input, counterId, maxLength) {
                const counter = document.getElementById(counterId);
                const length = input.value.length;
                counter.textContent = `${length}/${maxLength}`;

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

            // 添加标签
            function addTag(tag) {
                const tagsInput = document.getElementById('tags');
                const currentTags = tagsInput.value.trim();
                const tagsArray = currentTags ? currentTags.split(',').map(t => t.trim()) : [];

                // 如果标签已存在，则不添加
                if (!tagsArray.includes(tag)) {
                    tagsArray.push(tag);
                    tagsInput.value = tagsArray.join(', ');
                    updateCharCount(tagsInput, 'tagsCharCount', 100);
                }
            }

            // 重置表单
            function resetForm() {
                if (confirm('确定要重置表单吗？所有输入的内容将会被清空。')) {
                    document.getElementById('createCourseForm').reset();
                    document.getElementById('imagePreview').classList.add('hidden');

                    // 重置字符计数显示
                    document.querySelectorAll('[id$="CharCount"]').forEach(counter => {
                        counter.textContent = '0/' + counter.textContent.split('/')[1];
                        counter.className = 'text-gray-400';
                    });

                    document.getElementById('title').focus();
                    showToast('info', '表单已重置');
                }
            }

            // 验证图片URL
            function isValidImageUrl(url) {
                if (!url) return false;

                // 基本的URL验证
                try {
                    new URL(url);
                } catch (_) {
                    return false;
                }

                // 检查常见的图片扩展名
                const imageExtensions = /\.(jpg|jpeg|png|gif|webp|svg|bmp)(\?.*)?$/i;
                return imageExtensions.test(url);
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
            /* 表单元素样式 */
            .input:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            /* 难度级别选择效果 */
            input[name="difficulty"]:checked + div {
                transform: translateY(-2px);
                transition: all 0.2s ease;
            }

            /* 公开状态选择效果 */
            input[name="public_status"]:checked + div {
                animation: statusSelect 0.3s ease-out;
            }

            @keyframes statusSelect {
                0% { transform: scale(1); }
                50% { transform: scale(1.01); }
                100% { transform: scale(1); }
            }

            /* 标签按钮效果 */
            button[onclick^="addTag"] {
                transition: all 0.2s ease;
            }

            button[onclick^="addTag"]:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            /* 面包屑导航动画 */
            nav a {
                position: relative;
                transition: all 0.2s ease;
            }

            nav a:hover {
                transform: translateY(-1px);
            }

            /* 提交按钮加载动画 */
            button[disabled] {
                opacity: 0.7;
                cursor: not-allowed;
            }

            .fa-spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* 图片预览样式 */
            #previewImage {
                transition: opacity 0.3s ease;
            }

            /* 表单网格间隙 */
            .space-y-8 > * + * {
                margin-top: 2rem;
            }
        </style>
    @endsection
