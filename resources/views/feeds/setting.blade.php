@extends('layouts.app')

@section('title', '分类管理 - 蒙太奇')
@section('description', '管理您的订阅分类，拖拽调整分类和订阅源顺序，高效组织阅读内容。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和操作栏 -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">分类管理</h1>
                    <p class="text-gray-600 mt-1">拖拽调整分类和订阅源的顺序</p>
                </div>

                <div class="flex gap-3">
                    <a href="{{ url('/feeds') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        添加订阅
                    </a>
                    <a href="{{ url('/articles') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回阅读
                    </a>
                </div>
            </div>
        </div>

        <!-- 成功消息 -->
        @include('common.success')

        <!-- 主内容卡片 -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">分类展示</h2>
                    <p class="text-sm text-gray-500 mt-1">拖拽分类标题调整顺序，拖拽订阅源调整分类</p>
                </div>

                <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                    <i class="fas fa-hand-pointer mr-1"></i>可拖拽
                </span>
                </div>
            </div>

            <div class="p-6">
                <!-- 错误消息 -->
                @include('common.errors')

                @if (count($nav_infos) > 0)
                    <!-- 拖拽区域 -->
                    <div id="multi" class="space-y-4">
                        @foreach ($nav_infos as $nav_info)
                            <div id="{{ $nav_info['category_info']['category_id'] }}"
                                 class="category-card card hover:border-gray-300 transition-colors">
                                <!-- 分类标题（可拖拽手柄） -->
                                <div class="tile__handle px-5 py-3 border-b border-gray-200 bg-gray-50 cursor-move flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-folder text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800 text-lg">
                                                {{ $nav_info['category_info']['category_name'] }}
                                            </h3>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ count($nav_info['list']) }} 个订阅源
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-gray-400">
                                        <i class="fas fa-arrows-alt"></i>
                                    </div>
                                </div>

                                <!-- 订阅源列表 -->
                                <div class="tile__list p-4 bg-gray-50">
                                    <div class="space-y-2">
                                        @foreach($nav_info['list'] as $feed)
                                            <div id="{{ $feed['feed_sub_id'] }}"
                                                 data-category-id="{{ $nav_info['category_info']['category_id'] }}"
                                                 class="feed-item group cursor-move bg-white border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:shadow-sm transition-all duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        <div class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded flex items-center justify-center">
                                                            <i class="fas fa-rss text-gray-400 text-xs"></i>
                                                        </div>
                                                        <span class="font-medium text-gray-700 truncate">
                                            {{ $feed['feed_name'] }}
                                        </span>
                                                    </div>

                                                    <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                                        <a href="{{ url('feed/'.$feed['feed_sub_id']) }}"
                                                           class="p-2 text-gray-400 hover:text-blue-500 rounded-lg hover:bg-blue-50 transition-colors"
                                                           title="编辑">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button"
                                                                data-feed-id="{{ $feed['feed_sub_id'] }}"
                                                                class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors delete-feed"
                                                                title="删除">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(empty($nav_info['list']))
                                        <div class="text-center py-6">
                                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <i class="fas fa-rss text-gray-300"></i>
                                            </div>
                                            <p class="text-gray-500 text-sm">暂无订阅源</p>
                                            <a href="{{ url('/feeds') }}" class="text-sm text-blue-500 hover:text-blue-600 mt-2 inline-block">
                                                <i class="fas fa-plus mr-1"></i>添加订阅源
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 使用提示 -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <div>
                                <h4 class="font-medium text-blue-800 mb-1">操作指南</h4>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• 拖拽分类标题可以调整分类的排序顺序</li>
                                    <li>• 拖拽订阅源可以调整在同一分类内的顺序</li>
                                    <li>• 将订阅源拖拽到其他分类可以更改其所属分类</li>
                                    <li>• 所有更改都会自动保存</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- 空状态 -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-folder-open text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">暂无分类</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">
                            您还没有创建任何分类，请先添加订阅源或创建分类。
                        </p>
                        <div class="flex gap-3 justify-center">
                            <a href="{{ url('/feeds') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>
                                添加订阅
                            </a>
                            <a href="{{ url('/articles') }}" class="btn btn-secondary">
                                <i class="fas fa-newspaper mr-2"></i>
                                浏览文章
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 删除确认模态框 -->
    <div id="deleteModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">确认删除</h3>
                <button type="button" class="modal-close p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                </div>
                <p class="text-gray-700 text-center">确定要删除这个订阅源吗？此操作不可恢复。</p>
            </div>

            <div class="flex gap-3">
                <button type="button" class="modal-close btn btn-secondary flex-1">取消</button>
                <button type="button" id="confirmDelete" class="btn btn-danger flex-1">
                    <i class="fas fa-trash-alt mr-2"></i>
                    确认删除
                </button>
            </div>
        </div>
    </div>

    <style>
        /* 拖拽样式 */
        .sortable-chosen {
            background-color: rgba(59, 130, 246, 0.05);
            border-color: var(--primary-color) !important;
        }

        .sortable-ghost {
            opacity: 0.4;
            background-color: var(--gray-100);
        }

        .category-card.sortable-drag {
            transform: rotate(2deg);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .feed-item.sortable-drag {
            transform: rotate(1deg);
        }

        /* 拖拽手柄悬停效果 */
        .tile__handle:hover {
            background-color: var(--gray-100);
        }

        /* 模态框样式 */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, var(--danger-color));
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }
    </style>

    <!-- 引入Sortable.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.6.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;

            // 模态框功能
            const deleteModal = document.getElementById('deleteModal');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            let currentFeedId = null;

            // 打开模态框
            function openModal(feedId) {
                currentFeedId = feedId;
                deleteModal.classList.add('show');
            }

            // 关闭模态框
            function closeModal() {
                deleteModal.classList.remove('show');
                currentFeedId = null;
            }

            // 绑定关闭按钮事件
            modalCloseButtons.forEach(button => {
                button.addEventListener('click', closeModal);
            });

            // 点击模态框背景关闭
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    closeModal();
                }
            });

            // ESC键关闭模态框
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && deleteModal.classList.contains('show')) {
                    closeModal();
                }
            });

            // 删除订阅源功能
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.delete-feed');
                if (deleteBtn) {
                    e.preventDefault();
                    const feedId = deleteBtn.getAttribute('data-feed-id');
                    openModal(feedId);
                }
            });

            // 确认删除
            document.getElementById('confirmDelete').addEventListener('click', function() {
                if (!currentFeedId) return;
                if (!apiRequest) {
                    showNotification('error', 'API客户端未初始化');
                    return;
                }

                const button = this;
                const originalHtml = button.innerHTML;

                // 显示加载状态
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>删除中...';

                apiRequest('DELETE', "/feeds/" + currentFeedId, {}).then(function(response) {
                        if (response.code === 9999) {
                            // 从DOM中移除元素
                            const feedElement = document.getElementById(currentFeedId);
                            if (feedElement) {
                                feedElement.parentNode.removeChild(feedElement);
                            }

                            // 显示成功消息
                            showNotification('success', response.msg || '删除成功');

                            // 更新分类计数
                            updateCategoryCounts();
                        } else {
                            showNotification('error', response.msg || '删除失败');
                        }
                        closeModal();
                    }).catch(function() {
                        showNotification('error', '网络错误，请稍后重试');
                        closeModal();
                    }).finally(function() {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    });
            });

            // 拖拽排序功能
            const multiContainer = document.getElementById('multi');

            if (multiContainer) {
                // 分类排序
                const categorySortable = Sortable.create(multiContainer, {
                    animation: 150,
                    handle: ".tile__handle",
                    draggable: ".category-card",
                    ghostClass: "sortable-ghost",
                    chosenClass: "sortable-chosen",
                    dragClass: "sortable-drag",
                    onEnd: function(evt) {
                        // 更新分类顺序
                        if (evt.oldIndex !== evt.newIndex) {
                            const categoryIds = Array.from(document.querySelectorAll('.category-card'))
                                .map(card => card.id)
                                .join(',');

                            if (!apiRequest) {
                                showNotification('warning', 'API客户端未初始化');
                                return;
                            }
                            apiRequest('POST', '/categories/sort', {
                                category_ids: categoryIds
                            }).then(function(response) {
                                    if (response.code !== 9999) {
                                        showNotification('warning', '排序保存失败，请稍后再试');
                                    }
                                }).catch(function() {
                                    showNotification('warning', '排序保存失败，请稍后再试');
                                });
                        }
                    }
                });

                // 每个分类内的订阅源排序
                document.querySelectorAll('.tile__list').forEach(list => {
                    Sortable.create(list, {
                        group: 'feeds',
                        animation: 150,
                        handle: ".feed-item",
                        ghostClass: "sortable-ghost",
                        chosenClass: "sortable-chosen",
                        dragClass: "sortable-drag",
                        onEnd: function(evt) {
                            const feedItem = evt.item;
                            const newCategoryId = feedItem.closest('.category-card').id;
                            const originalCategoryId = feedItem.getAttribute('data-category-id');

                            // 更新数据属性
                            feedItem.setAttribute('data-category-id', newCategoryId);

                            // 获取当前分类下的所有订阅源ID
                            const feedIds = Array.from(feedItem.parentNode.querySelectorAll('.feed-item'))
                                .map(item => item.id)
                                .join(',');

                            // 准备发送的数据
                            const data = {
                                "feed_sub_ids": feedIds
                            };

                            // 如果更换了分类，添加相关信息
                            if (originalCategoryId !== newCategoryId) {
                                data.change_feed_sub_id = feedItem.id;
                                data.change_feed_sub_category = newCategoryId;
                            }

                            if (!apiRequest) {
                                showNotification('warning', 'API客户端未初始化');
                                return;
                            }
                            apiRequest('POST', '/feeds/sort', data).then(function(response) {
                                    if (response.code !== 9999) {
                                        showNotification('warning', '排序保存失败，请稍后再试');
                                    } else if (originalCategoryId !== newCategoryId) {
                                        showNotification('success', '已移动到新分类');
                                        updateCategoryCounts();
                                    }
                                }).catch(function() {
                                    showNotification('warning', '排序保存失败，请稍后再试');
                                });
                        }
                    });
                });
            }

            // 辅助函数
            function updateCategoryCounts() {
                document.querySelectorAll('.category-card').forEach(card => {
                    const count = card.querySelectorAll('.feed-item').length;
                    const countElement = card.querySelector('.text-xs.text-gray-500');
                    if (countElement) {
                        countElement.textContent = `${count} 个订阅源`;
                    }
                });
            }

            function showNotification(type, message) {
                const notification = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500';
                const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

                notification.className = `fixed top-4 right-4 z-50 fade-in max-w-sm`;
                notification.innerHTML = `
                <div class="card ${bgColor} text-white shadow-xl">
                    <div class="p-4 flex items-center gap-3">
                        <i class="fas ${icon} text-lg"></i>
                        <div class="flex-1">${message}</div>
                        <button class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

                document.body.appendChild(notification);

                // 点击关闭
                notification.querySelector('button').addEventListener('click', () => {
                    notification.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 5000);
            }
        });
    </script>
@endsection
