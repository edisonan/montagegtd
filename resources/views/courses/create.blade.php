@extends('layouts.app')

@section('title', '创建课程 - 蒙太奇')
@section('description', '创建新的学习课程，系统化您的学习计划')

@section('content')
    <div class="max-w-7xl mx-auto">
        @php $editCourseId = isset($editCourseId) ? (int)$editCourseId : 0; @endphp
        <!-- 页面标题和导航 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center text-sm text-gray-600 mb-3">
                        <a href="{{ url('/') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            <i class="fas fa-home mr-1"></i>首页
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <a href="{{ url('/course/management') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            课程中心
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <span class="text-gray-900 font-medium">{{ $editCourseId ? '编辑课程' : '创建课程' }}</span>
                    </nav>

                    @if(!$editCourseId)
                        @php $createType = (string)request('type', ''); @endphp
                        @if(in_array($createType, ['video', 'document', 'external'], true))
                            <div class="mb-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fas {{ $createType === 'video' ? 'fa-video' : ($createType === 'document' ? 'fa-file-alt' : 'fa-link') }}"></i>
                                正在创建：{{ $createType === 'video' ? '视频课程' : ($createType === 'document' ? '文档课程' : '外部课程') }}
                            </div>
                        @endif
                        <input type="hidden" id="courseSourceType" value="{{ $createType }}" />
                    @endif

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center mr-4 shadow-sm">
                            <i class="fas {{ $editCourseId ? 'fa-edit' : 'fa-plus-circle' }} text-blue-600 text-xl"></i>
                        </div>
                        {{ $editCourseId ? '编辑课程' : '创建新课程' }}
                    </h1>
                    <p class="text-gray-600 mt-2">{{ $editCourseId ? '修改课程信息，保存后即时生效' : '记录您的学习资源，开启新的学习旅程' }}</p>
                </div>

                <!-- 返回按钮 -->
                <a href="{{ url($editCourseId ? '/course/management' : '/courses') }}" class="btn btn-outline flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回{{ $editCourseId ? '课程中心' : '列表' }}
                </a>
            </div>
        </div>

        <!-- 创建课程卡片 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-primary-color mr-2"></i>
                    {{ $editCourseId ? '课程基本信息' : '创建课程基本信息' }}
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

                <!-- 创建课程表单（复用基础信息组件） -->
                @include('courses._basic-info-form', ['editCourseId' => $editCourseId])
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
                var EDIT_COURSE_ID = {{ $editCourseId }};
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

                // 编辑模式：加载课程数据回填表单
                if (EDIT_COURSE_ID > 0) {
                    loadEditCourseData();
                }

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
                ${EDIT_COURSE_ID > 0 ? '保存中...' : '创建中...'}
                    `;
                    submitBtn.disabled = true;

                    var selectedDifficulty = document.querySelector('input[name="difficulty"]:checked');
                    var selectedPublicStatus = document.querySelector('input[name="public_status"]:checked');

                    var apiMethod = EDIT_COURSE_ID > 0 ? 'PUT' : 'POST';
                    var apiPath = EDIT_COURSE_ID > 0 ? ('/courses/' + EDIT_COURSE_ID) : '/courses';
                    var payload = {
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
                    };
                    // 仅在创建时下发生成来源，编辑时保留课程原有 source_type
                    if (EDIT_COURSE_ID <= 0) {
                        payload.source_type = document.getElementById('courseSourceType') ? (document.getElementById('courseSourceType').value || 'manual') : 'manual';
                    }

                    apiRequest(apiMethod, apiPath, payload).then(function(response) {
                        if (response && response.code === 9999) {
                            showToast('success', response.msg || (EDIT_COURSE_ID > 0 ? '课程已保存' : '课程创建成功'));
                            setTimeout(function() {
                                window.location.href = "{{ url('/course/management') }}";
                            }, 300);
                            return;
                        }
                        showToast('error', (response && response.msg) ? response.msg : (EDIT_COURSE_ID > 0 ? '课程保存失败' : '课程创建失败'));
                    }).catch(function() {
                        showToast('error', '网络错误，请稍后重试');
                    }).finally(function() {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            });

            // 编辑模式：加载并回填课程数据
            function loadEditCourseData() {
                if (!apiRequest) {
                    showToast('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('GET', '/courses/' + EDIT_COURSE_ID, {}).then(function(response) {
                    if (!response || response.code !== 9999 || !response.result || !response.result.course) {
                        showToast('error', (response && response.msg) ? response.msg : '课程加载失败');
                        return;
                    }
                    var c = response.result.course;
                    if (!c) return;
                    document.getElementById('title').value = c.title || '';
                    updateCharCount(document.getElementById('title'), 'titleCharCount', 100);
                    document.getElementById('instructor').value = c.instructor || '';
                    document.getElementById('platform').value = c.platform || '';
                    document.getElementById('estimated_hours').value = (c.estimated_hours !== null && c.estimated_hours !== undefined) ? c.estimated_hours : '';
                    document.getElementById('public_url').value = c.public_url || '';
                    document.getElementById('cover_image_url').value = c.cover_image_url || '';
                    document.getElementById('tags').value = Array.isArray(c.tags) ? c.tags.join(', ') : (c.tags || '');
                    updateCharCount(document.getElementById('tags'), 'tagsCharCount', 100);
                    document.getElementById('description').value = c.description || '';
                    updateCharCount(document.getElementById('description'), 'descCharCount', 500);

                    var diff = document.querySelector('input[name="difficulty"][value="' + (c.difficulty || 'beginner') + '"]');
                    if (diff) diff.checked = true;
                    var pub = document.querySelector('input[name="public_status"][value="' + Number(c.public_status || 2) + '"]');
                    if (pub) pub.checked = true;

                    var coverUrl = (c.cover_image_url || '').trim();
                    if (coverUrl && isValidImageUrl(coverUrl)) {
                        document.getElementById('previewImage').src = coverUrl;
                        document.getElementById('imagePreview').classList.remove('hidden');
                    }
                }).catch(function() {
                    showToast('error', '课程加载失败，请稍后重试');
                });
            }

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
