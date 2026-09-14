{{-- 课程基础信息表单（供 courses/create 与 courses/manage 复用，通过 #createCourseForm 提交） --}}
@php $editCourseId = isset($editCourseId) ? (int)$editCourseId : 0; @endphp
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
            <a href="{{ url($editCourseId ? '/course/management' : '/courses') }}" class="btn btn-secondary flex items-center">
                <i class="fas fa-times mr-2"></i>
                取消
            </a>
        </div>

        <div class="flex space-x-3">
            @if(!$editCourseId)
                <button type="button" onclick="resetForm()" class="btn btn-outline flex items-center">
                    <i class="fas fa-redo mr-2"></i>
                    重置
                </button>
            @endif
            <button type="submit" class="btn btn-primary flex items-center" id="submitBtn">
                <i class="fas {{ $editCourseId ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                {{ $editCourseId ? '保存修改' : '创建课程' }}
            </button>
        </div>
    </div>
</form>