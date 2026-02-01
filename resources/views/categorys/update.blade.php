@extends('layouts.app')

@section('title', '修改分类 - 蒙太奇')
@section('description', '修改分类信息和设置')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题和导航 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center text-sm text-gray-600 mb-3">
                        <a href="{{ url('/') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            <i class="fas fa-home mr-1"></i>首页
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <a href="{{ url('category') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            分类管理
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <span class="text-gray-900 font-medium">修改分类</span>
                    </nav>

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-edit text-primary-color mr-3"></i>
                        修改分类
                    </h1>
                    <p class="text-gray-600 mt-2">更新分类信息，优化内容组织</p>
                </div>

                <!-- 返回按钮 -->
                <a href="{{ url('category') }}" class="btn btn-outline flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>
        </div>

        <!-- 修改分类卡片 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                            <i class="fas fa-folder text-blue-600 text-lg"></i>
                        </div>
                        修改分类信息
                    </h2>

                    <!-- 分类ID标签 -->
                    <div class="text-sm text-gray-500">
                        ID: <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $category->id }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- 错误消息 -->
                @include('common.errors')

                <!-- 成功消息 -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-green-800">修改成功</h4>
                                <p class="text-green-700 text-sm mt-1">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 分类基本信息 -->
                <div class="mb-8 p-5 bg-gray-50 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center">
                        <i class="fas fa-info-circle text-gray-400 mr-2"></i>
                        当前分类信息
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">分类名称</p>
                            <p class="font-medium text-gray-900">{{ $category->name }}</p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">创建时间</p>
                            <p class="font-medium text-gray-900">{{ $category->created_at->format('Y年m月d日 H:i') }}</p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">最后更新</p>
                            <p class="font-medium text-gray-900">{{ $category->updated_at->format('Y年m月d日 H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- 修改分类表单 -->
                <form action="{{ url('category/'.$category->id) }}" method="POST" class="space-y-6" id="editCategoryForm">
                    {!! csrf_field() !!}
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- 分类名称 -->
                        <div class="space-y-3">
                            <label for="name" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-tag text-gray-400 mr-2 text-sm"></i>
                                分类名称
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        value="{{ old('name', $category->name) }}"
                                        class="input w-full pl-10"
                                        placeholder="请输入分类名称"
                                        required
                                        autofocus
                                        maxlength="50"
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-font"></i>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm">
                                <p class="text-gray-500">建议使用简洁明了的名称，最多50个字符</p>
                                <span id="charCount" class="text-gray-400">0/50</span>
                            </div>
                        </div>

                        <!-- 分类图标 -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-icons text-gray-400 mr-2 text-sm"></i>
                                分类图标
                            </label>
                            <div class="grid grid-cols-6 sm:grid-cols-8 lg:grid-cols-10 gap-3">
                                @php
                                    $icons = [
                                        'fas fa-inbox', 'fas fa-book', 'fas fa-briefcase', 'fas fa-home',
                                        'fas fa-heart', 'fas fa-star', 'fas fa-flag', 'fas fa-trophy',
                                        'fas fa-lightbulb', 'fas fa-calendar', 'fas fa-clock', 'fas fa-bell',
                                        'fas fa-comment', 'fas fa-chart-bar', 'fas fa-cog', 'fas fa-users',
                                        'fas fa-folder', 'fas fa-folder-open', 'fas fa-archive', 'fas fa-box',
                                        'fas fa-tag', 'fas fa-tags', 'fas fa-bookmark', 'fas fa-thumbtack',
                                        'fas fa-paperclip', 'fas fa-file', 'fas fa-sticky-note', 'fas fa-edit',
                                        'fas fa-trash-alt', 'fas fa-share', 'fas fa-download', 'fas fa-upload'
                                    ];
                                @endphp
                                @foreach($icons as $icon)
                                    <label class="relative cursor-pointer">
                                        <input
                                                type="radio"
                                                name="icon"
                                                value="{{ $icon }}"
                                                class="sr-only peer"
                                                {{ old('icon', $category->icon) === $icon ? 'checked' : '' }}
                                        >
                                        <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center border-2 border-gray-200
                                              peer-checked:border-primary-color peer-checked:bg-blue-50 peer-checked:shadow-sm
                                              hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                                            <i class="{{ $icon }} text-gray-600 text-lg mb-1"></i>
                                            <span class="text-xs text-gray-500 truncate w-full text-center px-1">
                                            {{ str_replace(['fas fa-', '-'], ['', ' '], $icon) }}
                                        </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-500">选择最能代表此分类的图标</p>
                        </div>

                        <!-- 分类颜色 -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-palette text-gray-400 mr-2 text-sm"></i>
                                分类颜色
                            </label>
                            <div class="flex flex-wrap gap-3">
                                @php
                                    $colors = [
                                        'blue-500' => ['bg-blue-500', 'text-blue-500', '蓝色'],
                                        'green-500' => ['bg-green-500', 'text-green-500', '绿色'],
                                        'yellow-500' => ['bg-yellow-500', 'text-yellow-500', '黄色'],
                                        'red-500' => ['bg-red-500', 'text-red-500', '红色'],
                                        'purple-500' => ['bg-purple-500', 'text-purple-500', '紫色'],
                                        'pink-500' => ['bg-pink-500', 'text-pink-500', '粉色'],
                                        'indigo-500' => ['bg-indigo-500', 'text-indigo-500', '靛蓝'],
                                        'gray-500' => ['bg-gray-500', 'text-gray-500', '灰色'],
                                        'teal-500' => ['bg-teal-500', 'text-teal-500', '青色'],
                                        'orange-500' => ['bg-orange-500', 'text-orange-500', '橙色']
                                    ];
                                @endphp
                                @foreach($colors as $colorClass => [$bgClass, $textClass, $colorName])
                                    <label class="relative cursor-pointer">
                                        <input
                                                type="radio"
                                                name="color"
                                                value="{{ $colorClass }}"
                                                class="sr-only peer"
                                                {{ old('color', $category->color) === $colorClass ? 'checked' : '' }}
                                        >
                                        <div class="flex flex-col items-center space-y-2">
                                            <div class="w-10 h-10 rounded-full {{ $bgClass }} border-3 border-white
                                                  peer-checked:border-gray-800 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-{{ str_replace('-500', '', $colorClass) }}-300
                                                  hover:scale-110 transition-all duration-200 shadow-sm"
                                                 title="{{ $colorName }}">
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $colorName }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-500">选择分类的视觉主题色</p>
                        </div>

                        <!-- 分类描述 -->
                        <div class="space-y-3">
                            <label for="description" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-align-left text-gray-400 mr-2 text-sm"></i>
                                分类描述（可选）
                            </label>
                            <textarea
                                    name="description"
                                    id="description"
                                    rows="3"
                                    class="input w-full resize-none"
                                    placeholder="请输入分类描述，说明此分类的用途和范围"
                                    maxlength="200"
                            >{{ old('description', $category->description) }}</textarea>
                            <div class="flex justify-between text-sm">
                                <p class="text-gray-500">简要描述分类的用途，最多200个字符</p>
                                <span id="descCharCount" class="text-gray-400">0/200</span>
                            </div>
                        </div>
                    </div>

                    <!-- 表单操作按钮 -->
                    <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                        <!-- 危险操作区 -->
                        <div>
                            <button
                                    type="button"
                                    onclick="showDeleteConfirm()"
                                    class="btn btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center"
                            >
                                <i class="fas fa-trash-alt mr-2"></i>
                                删除此分类
                            </button>
                        </div>

                        <!-- 表单操作按钮 -->
                        <div class="flex space-x-3">
                            <a href="{{ url('category') }}" class="btn btn-secondary flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                取消
                            </a>
                            <button type="button" onclick="resetForm()" class="btn btn-outline flex items-center">
                                <i class="fas fa-redo mr-2"></i>
                                重置
                            </button>
                            <button type="submit" class="btn btn-primary flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                保存更改
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 使用统计卡片（扩展功能） -->
        @if($category->items_count > 0)
            <div class="card mt-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-chart-bar text-primary-color mr-2"></i>
                        使用统计
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <div class="text-3xl font-bold text-gray-900 mb-2">{{ $category->items_count ?? 0 }}</div>
                            <p class="text-sm text-gray-600">关联项目数</p>
                        </div>
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <div class="text-3xl font-bold text-gray-900 mb-2">
                                {{ $category->last_used_at ? $category->last_used_at->diffForHumans() : '从未使用' }}
                            </div>
                            <p class="text-sm text-gray-600">最后使用时间</p>
                        </div>
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <div class="text-3xl font-bold text-gray-900 mb-2">
                                {{ $category->usage_count ?? 0 }}
                            </div>
                            <p class="text-sm text-gray-600">使用次数</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

        <script>
            // 字符计数
            document.addEventListener('DOMContentLoaded', function() {
                const nameInput = document.getElementById('name');
                const charCount = document.getElementById('charCount');
                const descInput = document.getElementById('description');
                const descCharCount = document.getElementById('descCharCount');

                // 初始化字符计数
                updateCharCount(nameInput, charCount, 50);
                updateCharCount(descInput, descCharCount, 200);

                // 实时更新字符计数
                nameInput.addEventListener('input', () => updateCharCount(nameInput, charCount, 50));
                descInput.addEventListener('input', () => updateCharCount(descInput, descCharCount, 200));

                // 自动聚焦
                nameInput.focus();
                nameInput.select();
            });

            function updateCharCount(input, counter, maxLength) {
                const length = input.value.length;
                counter.textContent = `${length}/${maxLength}`;

                if (length > maxLength * 0.9) {
                    counter.classList.remove('text-gray-400');
                    counter.classList.add('text-yellow-600');
                } else if (length > maxLength) {
                    counter.classList.remove('text-gray-400', 'text-yellow-600');
                    counter.classList.add('text-red-600');
                } else {
                    counter.classList.remove('text-yellow-600', 'text-red-600');
                    counter.classList.add('text-gray-400');
                }
            }

            // 重置表单到原始值
            function resetForm() {
                const originalName = "{{ $category->name }}";
                const originalIcon = "{{ $category->icon }}";
                const originalColor = "{{ $category->color }}";
                const originalDescription = "{{ $category->description }}";

                document.getElementById('name').value = originalName;
                document.getElementById('description').value = originalDescription;

                // 重置图标选择
                document.querySelectorAll('input[name="icon"]').forEach(radio => {
                    radio.checked = radio.value === originalIcon;
                });

                // 重置颜色选择
                document.querySelectorAll('input[name="color"]').forEach(radio => {
                    radio.checked = radio.value === originalColor;
                });

                // 更新字符计数
                updateCharCount(document.getElementById('name'), document.getElementById('charCount'), 50);
                updateCharCount(document.getElementById('description'), document.getElementById('descCharCount'), 200);

                showToast('info', '表单已重置到原始值');
            }

            // 删除分类确认
            function showDeleteConfirm() {
                Swal.fire({
                    title: '确认删除分类',
                    html: `
                <div class="text-left">
                    <p class="mb-3">您确定要删除分类 <strong>"{{ $category->name }}"</strong> 吗？</p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                        <p class="text-sm text-red-700 flex items-start">
                            <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                            此操作将永久删除该分类，并且可能影响已关联的项目。
                        </p>
                    </div>
                </div>
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '确认删除',
                    cancelButtonText: '取消',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(`{{ url('category/'.$category->id) }}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                            },
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('删除失败');
                                }
                                return response.json();
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`请求失败: ${error}`);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (result.value && result.value.code === 9999) {
                            Swal.fire({
                                title: '删除成功',
                                text: '分类已成功删除',
                                icon: 'success',
                                confirmButtonText: '返回列表',
                                timer: 2000,
                                timerProgressBar: true,
                            }).then(() => {
                                window.location.href = "{{ url('category') }}";
                            });
                        } else {
                            Swal.fire(
                                '删除失败',
                                result.value?.msg || '操作失败，请稍后重试',
                                'error'
                            );
                        }
                    }
                });
            }

            // 表单提交验证
            document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
                const nameInput = document.getElementById('name');
                const name = nameInput.value.trim();

                if (!name) {
                    e.preventDefault();
                    nameInput.focus();
                    showToast('error', '请输入分类名称');
                    return;
                }

                if (name.length > 50) {
                    e.preventDefault();
                    nameInput.focus();
                    showToast('error', '分类名称不能超过50个字符');
                    return;
                }

                // 显示加载状态
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
            <i class="fas fa-spinner fa-spin mr-2"></i>
            保存中...
        `;
                submitBtn.disabled = true;

                // 2秒后恢复按钮状态（防止重复提交）
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            });

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
                <i class="fas fa-${
                    type === 'success' ? 'check-circle' :
                        type === 'error' ? 'exclamation-circle' :
                            type === 'info' ? 'info-circle' : 'bell'
                } mr-3"></i>
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
            /* 表单元素动画 */
            .input:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            /* 图标选择动画 */
            input[name="icon"]:checked + div {
                animation: iconSelect 0.3s ease-out;
                transform: scale(1.05);
            }

            @keyframes iconSelect {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1.05); }
            }

            /* 颜色选择动画 */
            input[name="color"]:checked + div div:first-child {
                animation: colorSelect 0.3s ease-out;
            }

            @keyframes colorSelect {
                0% { transform: scale(1); }
                50% { transform: scale(1.15); }
                100% { transform: scale(1); }
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
        </style>
    @endsection