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
                        <div id="categoryCountValue" class="text-2xl font-bold text-gray-900">0</div>
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
                <!-- 创建分类表单 -->
                <form action="javascript:void(0)" method="POST" class="space-y-6" id="createCategoryForm">
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
                                        value=""
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

        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list text-primary-color mr-2"></i>
                    分类列表
                    <span id="categoryCountBadge" class="ml-2 badge badge-primary hidden"></span>
                </h2>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    <div class="relative">
                        <input type="text" id="searchCategories" class="input w-full pl-10" placeholder="搜索分类...">
                        <div class="absolute left-3 top-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>

                <div id="categoryListContainer" class="space-y-4">
                    <div class="text-center py-12 text-gray-500">加载分类中...</div>
                </div>

                <div id="noResults" class="hidden text-center py-12">
                    <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">未找到匹配的分类</h3>
                    <p class="text-gray-600">尝试使用其他关键词搜索</p>
                </div>
            </div>
        </div>
    </div>

        <script>
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;
            var Swal = window.Swal || { fire: function(opts) { var ok = window.confirm(opts && opts.title ? String(opts.title).replace(/<[^>]*>/g, '') : '确认操作？'); return Promise.resolve({ isConfirmed: ok }); } };
            var categoryState = {
                items: []
            };

            function escapeHtml(text) {
                return String(text || '').replace(/[&<>"']/g, function(c) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[c];
                });
            }

            function resetForm() {
                document.getElementById('name').value = '';
                document.querySelectorAll('input[name="icon"]').forEach(function(radio) { radio.checked = false; });
                document.querySelectorAll('input[name="color"]').forEach(function(radio) { radio.checked = false; });
            }

            function showToast(type, message) {
                var toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ' + (type === 'success' ? 'bg-green-500' : 'bg-red-500');
                toast.innerHTML = '<div class="flex items-center"><i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-3"></i><span>' + escapeHtml(message) + '</span><button class="ml-4 hover:opacity-80"><i class="fas fa-times"></i></button></div>';
                document.body.appendChild(toast);
                toast.querySelector('button').addEventListener('click', function() { toast.remove(); });
                setTimeout(function() { if (toast.parentNode) toast.remove(); }, 3000);
            }

            function formatDate(value, shortTime) {
                if (!value) return '-';
                var d = new Date(value);
                if (isNaN(d.getTime())) return '-';
                if (shortTime) return d.toISOString().slice(5, 16).replace('T', ' ');
                return d.toISOString().slice(0, 10);
            }

            function updateCategoryCount() {
                var count = categoryState.items.length;
                var countValue = document.getElementById('categoryCountValue');
                var badge = document.getElementById('categoryCountBadge');
                if (countValue) countValue.textContent = String(count);
                if (badge) {
                    if (count > 0) {
                        badge.classList.remove('hidden');
                        badge.textContent = count + ' 个';
                    } else {
                        badge.classList.add('hidden');
                        badge.textContent = '';
                    }
                }
            }

            function renderCategoryList(searchTerm) {
                var container = document.getElementById('categoryListContainer');
                var noResults = document.getElementById('noResults');
                if (!container) return;

                var keyword = String(searchTerm || '').toLowerCase();
                var list = categoryState.items.filter(function(item) {
                    return !keyword || String(item.name || '').toLowerCase().indexOf(keyword) !== -1;
                });

                if (!list.length) {
                    container.innerHTML = keyword
                        ? ''
                        : '<div class="text-center py-12"><div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4"><i class="fas fa-folder-open text-gray-400 text-2xl"></i></div><h3 class="text-lg font-medium text-gray-900 mb-2">暂无分类</h3><p class="text-gray-600 mb-6">创建您的第一个分类，开始组织内容吧</p></div>';
                    if (noResults) noResults.style.display = keyword ? 'block' : 'none';
                    return;
                }

                var html = '';
                list.forEach(function(category) {
                    html += '<div id="' + Number(category.id) + '" class="group flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all duration-200">'
                        + '<div class="flex items-center space-x-4"><div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center"><i class="fas fa-folder text-blue-600 text-xl"></i></div><div><h3 class="font-semibold text-gray-900">' + escapeHtml(category.name) + '</h3><div class="flex items-center mt-1 space-x-4 text-sm text-gray-500"><span class="flex items-center"><i class="fas fa-calendar-alt mr-1 text-xs"></i>创建于 ' + formatDate(category.created_at, false) + '</span><span class="flex items-center"><i class="fas fa-sync-alt mr-1 text-xs"></i>更新于 ' + formatDate(category.updated_at, true) + '</span></div></div></div>'
                        + '<div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"><a href="/category/' + Number(category.id) + '" class="btn btn-sm btn-outline flex items-center" title="编辑分类"><i class="fas fa-edit mr-1"></i>编辑</a><button type="button" data-category-id="' + Number(category.id) + '" data-category-name="' + escapeHtml(category.name) + '" class="btn btn-sm btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center category-delete-btn" title="删除分类"><i class="fas fa-trash-alt mr-1"></i>删除</button></div>'
                        + '</div>';
                });
                container.innerHTML = html;
                if (noResults) noResults.style.display = 'none';
            }

            function loadCategories() {
                var container = document.getElementById('categoryListContainer');
                if (!apiRequest) {
                    if (container) container.innerHTML = '<div class="text-center py-12 text-gray-500">API客户端未初始化</div>';
                    return;
                }
                apiRequest('GET', '/categories', {}).then(function(resp) {
                    if (!resp || resp.code !== 9999) throw new Error((resp && resp.msg) || '加载失败');
                    categoryState.items = Array.isArray(resp.result) ? resp.result : [];
                    updateCategoryCount();
                    renderCategoryList('');
                }).catch(function() {
                    if (container) container.innerHTML = '<div class="text-center py-12 text-gray-500">分类加载失败，请稍后重试</div>';
                });
            }

            function deleteCategory(categoryId, categoryName) {
                Swal.fire({
                    title: '确认删除',
                    html: '确定要删除分类 <strong>"' + escapeHtml(categoryName) + '"</strong> 吗？',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '确认删除',
                    cancelButtonText: '取消',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    if (!apiRequest) {
                        showToast('error', 'API客户端未初始化');
                        return;
                    }
                    apiRequest('DELETE', '/categories/' + categoryId, {}).then(function(response) {
                        if (response && response.code === 9999) {
                            categoryState.items = categoryState.items.filter(function(item) { return String(item.id) !== String(categoryId); });
                            updateCategoryCount();
                            renderCategoryList(document.getElementById('searchCategories').value);
                            showToast('success', '分类删除成功');
                        } else {
                            showToast('error', (response && response.msg) ? response.msg : '删除失败');
                        }
                    }).catch(function() {
                        showToast('error', '操作失败，请稍后重试');
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                loadCategories();

                var searchInput = document.getElementById('searchCategories');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        renderCategoryList(this.value);
                    });
                }

                var form = document.getElementById('createCategoryForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        var nameInput = document.getElementById('name');
                        if (!nameInput.value.trim()) {
                            showToast('error', '请输入分类名称');
                            nameInput.focus();
                            return;
                        }
                        if (!apiRequest) {
                            showToast('error', 'API客户端未初始化');
                            return;
                        }
                        var submitBtn = form.querySelector('button[type="submit"]');
                        var originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>创建中...';
                        apiRequest('POST', '/categories', { name: nameInput.value.trim() }).then(function(resp) {
                            if (resp && resp.code === 9999) {
                                resetForm();
                                loadCategories();
                                showToast('success', '分类创建成功');
                            } else {
                                showToast('error', (resp && resp.msg) ? resp.msg : '创建失败');
                            }
                        }).catch(function() {
                            showToast('error', '创建失败，请稍后重试');
                        }).finally(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                    });
                }

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('.category-delete-btn');
                    if (!btn) return;
                    deleteCategory(btn.getAttribute('data-category-id'), btn.getAttribute('data-category-name'));
                });

                var nameInput = document.getElementById('name');
                if (nameInput) nameInput.focus();
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
