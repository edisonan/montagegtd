@extends('layouts.app')

@section('title', '分类管理 - 蒙太奇')
@section('description', '管理和组织您的分类系统')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-folder-tree text-primary-color mr-3"></i>
                        分类管理
                    </h1>
                    <p class="text-gray-600 mt-2">创建和管理您的分类，更好地组织内容</p>
                </div>

                <!-- 快速统计 -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">分类总数</div>
                        <div class="text-2xl font-bold text-gray-900">{{ count($categorys) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 创建新分类卡片 -->
        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                    创建新分类
                </h2>
            </div>

            <div class="p-6">
                <!-- 成功消息 -->
                @include('common.success')

                <!-- 错误消息 -->
                @include('common.errors')

                <!-- 创建分类表单 -->
                <form action="{{ url('/api/v2/categories') }}" method="POST" class="space-y-6" id="createCategoryForm">
                    {!! csrf_field() !!}

                    <div class="space-y-4">
                        <!-- 分类名称 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-tag text-gray-400 mr-2 text-sm"></i>
                                分类名称
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        value="{{ old('name') }}"
                                        class="input w-full pl-10"
                                        placeholder="请输入分类名称"
                                        required
                                        autofocus
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-folder"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">简洁明了的名称有助于更好地组织内容</p>
                        </div>

                        <!-- 分类图标（扩展功能） -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-icons text-gray-400 mr-2 text-sm"></i>
                                分类图标（可选）
                            </label>
                            <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
                                @php
                                    $icons = [
                                        'fas fa-inbox', 'fas fa-book', 'fas fa-briefcase', 'fas fa-home',
                                        'fas fa-heart', 'fas fa-star', 'fas fa-flag', 'fas fa-trophy',
                                        'fas fa-lightbulb', 'fas fa-calendar', 'fas fa-clock', 'fas fa-bell',
                                        'fas fa-comment', 'fas fa-chart-bar', 'fas fa-cog', 'fas fa-users'
                                    ];
                                @endphp
                                @foreach($icons as $icon)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="icon" value="{{ $icon }}" class="sr-only peer">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center border-2 border-gray-200
                                              peer-checked:border-primary-color peer-checked:bg-blue-50
                                              hover:bg-gray-50 transition-all duration-200">
                                            <i class="{{ $icon }} text-gray-600 text-lg"></i>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 分类颜色（扩展功能） -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-palette text-gray-400 mr-2 text-sm"></i>
                                分类颜色（可选）
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $colors = [
                                        'bg-blue-500' => '蓝色',
                                        'bg-green-500' => '绿色',
                                        'bg-yellow-500' => '黄色',
                                        'bg-red-500' => '红色',
                                        'bg-purple-500' => '紫色',
                                        'bg-pink-500' => '粉色',
                                        'bg-indigo-500' => '靛蓝',
                                        'bg-gray-500' => '灰色'
                                    ];
                                @endphp
                                @foreach($colors as $colorClass => $colorName)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="color" value="{{ $colorClass }}" class="sr-only peer">
                                        <div class="w-8 h-8 rounded-full {{ $colorClass }} border-2 border-white
                                              peer-checked:border-gray-800 peer-checked:ring-2 peer-checked:ring-offset-2
                                              hover:scale-110 transition-all duration-200"
                                             title="{{ $colorName }}">
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="resetForm()" class="btn btn-secondary">
                            <i class="fas fa-redo mr-2"></i>
                            重置
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            创建分类
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 分类列表卡片 -->
        @if(count($categorys) > 0)
            <div class="card">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-list text-primary-color mr-2"></i>
                        分类列表
                        <span class="ml-2 badge badge-primary">{{ count($categorys) }} 个</span>
                    </h2>
                </div>

                <div class="p-6">
                    <!-- 搜索和筛选（扩展功能） -->
                    <div class="mb-6">
                        <div class="relative">
                            <input
                                    type="text"
                                    id="searchCategories"
                                    class="input w-full pl-10"
                                    placeholder="搜索分类..."
                            >
                            <div class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 分类列表 -->
                    <div class="space-y-4">
                        @foreach($categorys as $category)
                            <div id="{{ $category->id }}" class="group flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all duration-200">
                                <!-- 分类信息 -->
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-folder text-blue-600 text-xl"></i>
                                    </div>

                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                        <div class="flex items-center mt-1 space-x-4 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-1 text-xs"></i>
                                    创建于 {{ $category->created_at->format('Y-m-d') }}
                                </span>
                                            <span class="flex items-center">
                                    <i class="fas fa-sync-alt mr-1 text-xs"></i>
                                    更新于 {{ $category->updated_at->format('m-d H:i') }}
                                </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 操作按钮 -->
                                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <!-- 编辑按钮 -->
                                    <a
                                            href="{{ url('category/'.$category->id) }}"
                                            class="btn btn-sm btn-outline flex items-center"
                                            title="编辑分类"
                                    >
                                        <i class="fas fa-edit mr-1"></i>
                                        编辑
                                    </a>

                                    <!-- 删除按钮 -->
                                    <button
                                            type="button"
                                            onclick="confirmDelete('{{ $category->id }}', '{{ $category->name }}')"
                                            class="btn btn-sm btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center"
                                            title="删除分类"
                                    >
                                        <i class="fas fa-trash-alt mr-1"></i>
                                        删除
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 空状态提示（搜索时） -->
                    <div id="noResults" class="hidden text-center py-12">
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-search text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">未找到匹配的分类</h3>
                        <p class="text-gray-600">尝试使用其他关键词搜索</p>
                    </div>
                </div>
            </div>
        @else
            <!-- 空状态 -->
            <div class="card">
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">暂无分类</h3>
                    <p class="text-gray-600 mb-6">创建您的第一个分类，开始组织内容吧</p>
                    <a href="#create-form" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        创建第一个分类
                    </a>
                </div>
            </div>
        @endif
    </div>

        <script>
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;

            // 重置表单
            function resetForm() {
                document.getElementById('name').value = '';
                document.querySelectorAll('input[name="icon"]').forEach(radio => radio.checked = false);
                document.querySelectorAll('input[name="color"]').forEach(radio => radio.checked = false);
            }

            // 确认删除分类
            function confirmDelete(categoryId, categoryName) {
                Swal.fire({
                    title: '确认删除',
                    html: `确定要删除分类 <strong>"${categoryName}"</strong> 吗？`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '确认删除',
                    cancelButtonText: '取消',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteCategory(categoryId);
                    }
                });
            }

            // 删除分类
            function deleteCategory(categoryId) {
                if (!apiRequest) {
                    showToast('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('DELETE', '/categories/' + categoryId, {}).then(function(response) {
                    if (response && response.code === 9999) {
                        // 移除分类元素
                        $(`#${categoryId}`).fadeOut(300, function() {
                            $(this).remove();
                            showToast('success', '分类删除成功');

                            // 检查是否还有分类
                            if ($('.group').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        showToast('error', (response && response.msg) ? response.msg : '删除失败');
                    }
                }).catch(function() {
                    showToast('error', '操作失败，请稍后重试');
                });
            }

            // 显示提示消息
            function showToast(type, message) {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
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

            // 分类搜索功能
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchCategories');
                const noResults = document.getElementById('noResults');

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        let hasVisibleItems = false;

                        document.querySelectorAll('.group').forEach(item => {
                            const categoryName = item.querySelector('h3').textContent.toLowerCase();
                            if (categoryName.includes(searchTerm)) {
                                item.style.display = 'flex';
                                hasVisibleItems = true;
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        // 显示/隐藏无结果提示
                        if (noResults) {
                            noResults.style.display = hasVisibleItems || searchTerm === '' ? 'none' : 'block';
                        }
                    });
                }

                // 表单提交验证
                const form = document.getElementById('createCategoryForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const nameInput = document.getElementById('name');
                        if (!nameInput.value.trim()) {
                            e.preventDefault();
                            showToast('error', '请输入分类名称');
                            nameInput.focus();
                            return;
                        }
                        if (!apiRequest) {
                            e.preventDefault();
                            showToast('error', 'API客户端未初始化');
                            return;
                        }

                        e.preventDefault();
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>创建中...';

                        apiRequest('POST', '/categories', {
                            name: nameInput.value.trim()
                        }).then(function(resp) {
                            if (resp && resp.code === 9999) {
                                showToast('success', '分类创建成功');
                                setTimeout(function() {
                                    window.location.reload();
                                }, 300);
                                return;
                            }
                            showToast('error', (resp && resp.msg) ? resp.msg : '创建失败');
                        }).catch(function() {
                            showToast('error', '创建失败，请稍后重试');
                        }).finally(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                    });
                }

                // 自动聚焦到名称输入框
                const nameInput = document.getElementById('name');
                if (nameInput) {
                    nameInput.focus();
                }
            });
        </script>

        <style>
            /* 动画效果 */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .group {
                animation: fadeInUp 0.3s ease-out;
                animation-fill-mode: both;
            }

            .group:nth-child(1) { animation-delay: 0.1s; }
            .group:nth-child(2) { animation-delay: 0.2s; }
            .group:nth-child(3) { animation-delay: 0.3s; }
            .group:nth-child(4) { animation-delay: 0.4s; }

            /* 悬停效果 */
            .group:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            /* 图标选中效果 */
            input[name="icon"]:checked + div {
                transform: scale(1.1);
            }

            /* 颜色选中效果 */
            input[name="color"]:checked + div {
                transform: scale(1.2);
            }
        </style>
    @endsection
