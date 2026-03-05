@extends('layouts.app')

@section('title', '技能目标 - 蒙太奇')
@section('description', '管理您的技能发展目标，制定成长计划，追踪学习进度。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和操作栏 -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">技能目标管理</h1>
                    <p class="text-gray-600 mt-1">设定并追踪您的技能成长目标</p>
                </div>

                <a href="{{ url('/index') }}" class="btn btn-secondary self-start">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回主页
                </a>
            </div>
        </div>

        <!-- 添加目标表单卡片 -->
        <div class="card mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">添加新技能</h2>
                <p class="text-sm text-gray-500 mt-1">设定您想要学习或提升的技能</p>
            </div>

            <div class="p-6">
                <form action="javascript:void(0)" method="POST" class="space-y-5" id="goalCreateForm">
                    <!-- 技能名称输入 -->
                    <div class="space-y-2">
                        <label for="goal-name" class="block text-sm font-medium text-gray-700">
                            技能名称
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="goal-name"
                               value=""
                               placeholder="例如：Python编程、摄影技巧、英语口语..."
                               class="input w-full"
                               required>
                        <p class="text-xs text-gray-500">请输入您想要学习或提升的具体技能名称</p>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            添加技能目标
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 技能列表卡片 -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">我的技能列表</h2>
                        <p id="goalCountText" class="text-sm text-gray-500 mt-1">共 0 个技能目标</p>
                    </div>

                    <span id="goalCountBadge" class="hidden text-xs px-3 py-1 bg-blue-100 text-blue-600 rounded-full font-medium"></span>
                </div>
            </div>

            <div class="p-6">
                <div id="goalsListContainer" class="text-center py-12 text-gray-500">加载目标中...</div>
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
                <p class="text-gray-700 text-center">确定要删除这个技能目标吗？所有相关记录也将被删除。</p>
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
        /* 进度条样式 */
        .progress {
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* 动画效果 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .goal-item {
            transition: all 0.2s ease;
        }

        .goal-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
    </style>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var goalState = {
            items: [],
            currentGoalId: null
        };

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function(c) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[c];
            });
        }

        function showNotification(type, message) {
            document.querySelectorAll('.notification-item').forEach(function(el) { el.remove(); });
            var bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-yellow-500');
            var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
            var notification = document.createElement('div');
            notification.className = 'notification-item fixed top-4 right-4 z-50 fade-in max-w-sm';
            notification.innerHTML = '<div class="card ' + bgColor + ' text-white shadow-xl"><div class="p-4 flex items-center gap-3"><i class="fas ' + icon + ' text-lg"></i><div class="flex-1">' + escapeHtml(message) + '</div><button class="text-white hover:text-gray-200 close-notification"><i class="fas fa-times"></i></button></div></div>';
            document.body.appendChild(notification);
            notification.querySelector('.close-notification').addEventListener('click', function() { notification.remove(); });
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    setTimeout(function() { notification.parentNode.removeChild(notification); }, 300);
                }
            }, 5000);
        }

        function formatDate(dateText) {
            if (!dateText) return '-';
            var d = new Date(dateText);
            return isNaN(d.getTime()) ? '-' : d.toISOString().slice(0, 10);
        }

        function updateGoalCount() {
            var count = goalState.items.length;
            var text = document.getElementById('goalCountText');
            var badge = document.getElementById('goalCountBadge');
            if (text) text.textContent = '共 ' + count + ' 个技能目标';
            if (badge) {
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = count + ' 个目标';
                } else {
                    badge.classList.add('hidden');
                    badge.textContent = '';
                }
            }
        }

        function renderGoals() {
            var container = document.getElementById('goalsListContainer');
            if (!container) return;

            if (!goalState.items.length) {
                container.innerHTML = '<div class="text-center py-12"><div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-bullseye text-gray-400 text-xl"></i></div><h3 class="text-lg font-medium text-gray-700 mb-2">暂无技能目标</h3><p class="text-gray-500 max-w-md mx-auto mb-6">您还没有设定任何技能目标，从上面的表单开始添加第一个技能目标吧！</p></div>';
                updateGoalCount();
                return;
            }

            var html = '<div class="space-y-3">';
            goalState.items.forEach(function(goal) {
                var progress = Number(goal.progress || 0);
                var progressHtml = progress > 0
                    ? '<div class="flex items-center gap-2"><div class="progress w-20"><div class="progress-bar" style="width: ' + progress + '%"></div></div><span class="text-xs text-gray-600">' + progress + '%</span></div>'
                    : '';
                html += '<div id="' + Number(goal.id) + '" class="goal-item card hover:border-gray-300 transition-colors"><div class="p-4"><div class="flex items-center justify-between"><div class="flex items-center gap-4 flex-1 min-w-0"><div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center"><i class="fas fa-bullseye text-white"></i></div><div class="flex-1 min-w-0"><h3 class="font-medium text-gray-800 text-lg truncate">' + escapeHtml(goal.name) + '</h3><div class="flex items-center gap-3 mt-1"><span class="text-xs text-gray-500"><i class="fas fa-calendar-plus mr-1"></i>' + formatDate(goal.created_at) + '</span>' + progressHtml + '</div></div><div class="flex items-center gap-2 flex-shrink-0 ml-4"><a href="/goal/' + Number(goal.id) + '" class="p-2 text-gray-400 hover:text-blue-500 rounded-lg hover:bg-blue-50 transition-colors" title="编辑技能"><i class="fas fa-edit"></i></a><button type="button" data-goal-id="' + Number(goal.id) + '" class="delete-goal p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors" title="删除技能"><i class="fas fa-trash-alt"></i></button></div></div></div></div></div>';
            });
            html += '</div>';
            container.innerHTML = html;
            updateGoalCount();
        }

        function loadGoals() {
            var container = document.getElementById('goalsListContainer');
            if (!apiRequest) {
                if (container) container.innerHTML = '<div class="text-center py-12 text-gray-500">API客户端未初始化</div>';
                return;
            }
            apiRequest('GET', '/goals', { status: 1 }).then(function(response) {
                if (!response || response.code !== 9999) throw new Error((response && response.msg) || '加载失败');
                goalState.items = Array.isArray(response.result && response.result.goals) ? response.result.goals : [];
                renderGoals();
            }).catch(function() {
                if (container) container.innerHTML = '<div class="text-center py-12 text-gray-500">目标加载失败，请稍后重试</div>';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var deleteModal = document.getElementById('deleteModal');
            var goalForm = document.getElementById('goalCreateForm');
            var nameInput = document.getElementById('goal-name');
            var confirmDeleteBtn = document.getElementById('confirmDelete');

            loadGoals();

            document.querySelectorAll('.modal-close').forEach(function(button) {
                button.addEventListener('click', function() {
                    deleteModal.classList.remove('show');
                    goalState.currentGoalId = null;
                });
            });

            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove('show');
                    goalState.currentGoalId = null;
                }
            });

            document.addEventListener('click', function(e) {
                var deleteBtn = e.target.closest('.delete-goal');
                if (!deleteBtn) return;
                goalState.currentGoalId = deleteBtn.getAttribute('data-goal-id');
                deleteModal.classList.add('show');
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (!goalState.currentGoalId || !apiRequest) return;
                var button = this;
                var original = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>删除中...';
                apiRequest('DELETE', '/goals/' + goalState.currentGoalId, {}).then(function(response) {
                    if (response && response.code === 9999) {
                        goalState.items = goalState.items.filter(function(item) { return String(item.id) !== String(goalState.currentGoalId); });
                        renderGoals();
                        showNotification('success', '技能目标已删除');
                    } else {
                        showNotification('error', (response && response.msg) ? response.msg : '删除失败');
                    }
                }).catch(function() {
                    showNotification('error', '网络错误，请稍后重试');
                }).finally(function() {
                    deleteModal.classList.remove('show');
                    goalState.currentGoalId = null;
                    button.disabled = false;
                    button.innerHTML = original;
                });
            });

            if (goalForm) {
                goalForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!nameInput.value.trim()) {
                        showNotification('warning', '请输入技能名称');
                        nameInput.focus();
                        return;
                    }
                    if (!apiRequest) {
                        showNotification('error', 'API客户端未初始化');
                        return;
                    }
                    var submitBtn = goalForm.querySelector('button[type="submit"]');
                    var original = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>添加中...';
                    apiRequest('POST', '/goals', { name: nameInput.value.trim() }).then(function(response) {
                        if (response && response.code === 9999) {
                            nameInput.value = '';
                            loadGoals();
                            showNotification('success', '技能目标已添加');
                        } else {
                            showNotification('error', (response && response.msg) ? response.msg : '添加失败');
                        }
                    }).catch(function() {
                        showNotification('error', '网络错误，请稍后重试');
                    }).finally(function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = original;
                    });
                });
            }
        });
    </script>
@endsection
