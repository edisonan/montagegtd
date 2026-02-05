@extends('layouts.app')

@section('title', '修改目标 - 蒙太奇')
@section('description', '编辑和更新您的技能目标，调整学习计划和进度设置。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和操作栏 -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">修改目标</h1>
                    <p class="text-gray-600 mt-1">编辑您的技能目标信息</p>
                </div>

                <a href="{{ url('/goals') }}" class="btn btn-secondary self-start">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>

            <!-- 面包屑导航 -->
            <div class="flex items-center gap-2 mt-4 text-sm">
                <a href="{{ url('/index') }}" class="text-gray-500 hover:text-gray-700">
                    首页
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <a href="{{ url('/goals') }}" class="text-gray-500 hover:text-gray-700">
                    技能目标
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <span class="text-gray-700 font-medium truncate max-w-xs">
                编辑：{{ App\Http\Utils\CommonUtil::strLimit($goal->name, 30) }}
            </span>
            </div>
        </div>

        <!-- 主内容卡片 -->
        <div class="card max-w-3xl mx-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bullseye text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">编辑技能目标</h2>
                        <p class="text-sm text-gray-500 mt-1">修改目标信息并保存</p>
                    </div>
                </div>

                <div class="text-xs px-3 py-1 bg-gray-100 text-gray-600 rounded-full">
                    目标ID: {{ $goal->id }}
                </div>
            </div>

            <div class="p-6">
                <!-- 错误消息 -->
                @include('common.errors')

                <!-- 基本信息卡片 -->
                <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <strong>创建时间：</strong>{{ $goal->created_at->format('Y年m月d日 H:i') }}
                            @if($goal->updated_at && $goal->updated_at->notEqualTo($goal->created_at))
                                <br><strong>最后更新：</strong>{{ $goal->updated_at->format('Y年m月d日 H:i') }}
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ url('goal/'.$goal->id) }}" method="POST" class="space-y-6" id="goalForm">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">

                    <!-- 目标名称 -->
                    <div class="space-y-2">
                        <label for="goal-name" class="block text-sm font-medium text-gray-700">
                            目标名称
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="goal-name"
                               value="{{ old('name', $goal->name) }}"
                               placeholder="输入您的技能目标名称..."
                               class="input w-full"
                               required
                               autofocus>
                        <div class="flex items-center gap-2 mt-1">
                            <i class="fas fa-lightbulb text-gray-400 text-sm"></i>
                            <p class="text-xs text-gray-500">清晰的名称有助于更好地追踪目标进展</p>
                        </div>
                    </div>

                    <!-- 目标描述（扩展字段，如果数据库支持） -->
                    <div class="space-y-2">
                        <label for="goal-description" class="block text-sm font-medium text-gray-700">
                            目标描述
                            <span class="text-xs text-gray-400">（可选）</span>
                        </label>
                        <textarea name="description"
                                  id="goal-description"
                                  rows="3"
                                  placeholder="描述这个目标的具体内容、学习计划或期望成果..."
                                  class="input w-full resize-none">{{ old('description', $goal->description ?? '') }}</textarea>
                        <p class="text-xs text-gray-500">简要描述目标的重要性和实现路径</p>
                    </div>

                    <!-- 目标分类（扩展字段） -->
                    <div class="space-y-2">
                        <label for="goal-category" class="block text-sm font-medium text-gray-700">
                            目标分类
                            <span class="text-xs text-gray-400">（可选）</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $categories = ['学习成长', '职业发展', '个人兴趣', '健康生活', '财务目标', '人际关系'];
                                $currentCategory = old('category', $goal->category ?? '');
                            @endphp

                            @foreach($categories as $category)
                                <label class="cursor-pointer">
                                    <input type="radio"
                                           name="category"
                                           value="{{ $category }}"
                                           {{ $currentCategory == $category ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <span class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium peer-checked:bg-blue-100 peer-checked:text-blue-600 transition-colors">
                                {{ $category }}
                            </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 目标进度（扩展字段） -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="goal-progress" class="block text-sm font-medium text-gray-700">
                                当前进度
                                <span class="text-xs text-gray-400">（可选）</span>
                            </label>
                            <span id="progress-value" class="text-sm font-medium text-blue-600">
                            {{ old('progress', $goal->progress ?? 0) }}%
                        </span>
                        </div>

                        <div class="space-y-3">
                            <input type="range"
                                   name="progress"
                                   id="goal-progress"
                                   min="0"
                                   max="100"
                                   step="5"
                                   value="{{ old('progress', $goal->progress ?? 0) }}"
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-500">

                            <div class="flex justify-between text-xs text-gray-500">
                                <span>0%</span>
                                <span>25%</span>
                                <span>50%</span>
                                <span>75%</span>
                                <span>100%</span>
                            </div>
                        </div>

                        <!-- 进度条预览 -->
                        <div class="mt-2">
                            <div class="progress">
                                <div id="progress-preview" class="progress-bar"
                                     style="width: {{ old('progress', $goal->progress ?? 0) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 目标状态（扩展字段） -->
                    <div class="space-y-2">
                        <label for="goal-status" class="block text-sm font-medium text-gray-700">
                            目标状态
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @php
                                $statuses = [
                                    'planning' => ['label' => '计划中', 'color' => 'bg-gray-100 text-gray-600', 'icon' => 'far fa-clock'],
                                    'in_progress' => ['label' => '进行中', 'color' => 'bg-blue-100 text-blue-600', 'icon' => 'fas fa-spinner'],
                                    'completed' => ['label' => '已完成', 'color' => 'bg-green-100 text-green-600', 'icon' => 'fas fa-check-circle'],
                                    'paused' => ['label' => '已暂停', 'color' => 'bg-yellow-100 text-yellow-600', 'icon' => 'fas fa-pause-circle']
                                ];
                                $currentStatus = old('status', $goal->status ?? 'planning');
                            @endphp

                            @foreach($statuses as $key => $status)
                                <label class="cursor-pointer">
                                    <input type="radio"
                                           name="status"
                                           value="{{ $key }}"
                                           {{ $currentStatus == $key ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <div class="p-3 rounded-lg border border-gray-200 peer-checked:border-blue-300 transition-all duration-200">
                                        <div class="flex items-center gap-2">
                                            <i class="{{ $status['icon'] }} text-lg"></i>
                                            <span class="text-sm font-medium">{{ $status['label'] }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="btn btn-primary flex-1">
                                <i class="fas fa-save mr-2"></i>
                                保存修改
                            </button>

                            <div class="flex gap-2">
                                <a href="{{ url('/goals') }}" class="btn btn-secondary flex-1">
                                    <i class="fas fa-times mr-2"></i>
                                    取消
                                </a>

                                <button type="button"
                                        id="deleteGoal"
                                        class="btn btn-outline text-red-600 border-red-300 hover:bg-red-50 flex-1">
                                    <i class="fas fa-trash-alt mr-2"></i>
                                    删除
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 删除确认模态框 -->
    <div id="deleteModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">确认删除目标</h3>
                <button type="button" class="modal-close p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                </div>
                <p class="text-gray-700 text-center mb-2">确定要删除目标 <strong>"{{ $goal->name }}"</strong> 吗？</p>
                <p class="text-sm text-gray-500 text-center">此操作不可恢复，所有相关数据将被永久删除。</p>
            </div>

            <div class="flex gap-3">
                <button type="button" class="modal-close btn btn-secondary flex-1">取消</button>
                <form action="{{ url('goal/'.$goal->id) }}" method="POST" class="flex-1" id="deleteForm">
                    {!! csrf_field() !!}
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-full">
                        <i class="fas fa-trash-alt mr-2"></i>
                        确认删除
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* 进度条样式 */
        .progress {
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        /* 自定义滑块样式 */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 进度条实时更新
            const progressSlider = document.getElementById('goal-progress');
            const progressValue = document.getElementById('progress-value');
            const progressPreview = document.getElementById('progress-preview');

            if (progressSlider && progressValue && progressPreview) {
                progressSlider.addEventListener('input', function() {
                    const value = this.value;
                    progressValue.textContent = value + '%';
                    progressPreview.style.width = value + '%';
                });
            }

            // 模态框功能
            const deleteModal = document.getElementById('deleteModal');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const deleteButton = document.getElementById('deleteGoal');

            // 打开模态框
            function openModal() {
                deleteModal.classList.add('show');
            }

            // 关闭模态框
            function closeModal() {
                deleteModal.classList.remove('show');
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

            // 删除按钮点击事件
            if (deleteButton) {
                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    openModal();
                });
            }

            // 表单提交优化
            const goalForm = document.getElementById('goalForm');
            const submitBtn = goalForm?.querySelector('button[type="submit"]');
            const deleteForm = document.getElementById('deleteForm');
            const deleteFormBtn = deleteForm?.querySelector('button[type="submit"]');

            if (goalForm && submitBtn) {
                goalForm.addEventListener('submit', function() {
                    // 显示加载状态
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

                    // 3秒后恢复（防止无限加载）
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }, 3000);
                });
            }

            if (deleteForm && deleteFormBtn) {
                deleteForm.addEventListener('submit', function() {
                    // 显示加载状态
                    const originalBtnText = deleteFormBtn.innerHTML;
                    deleteFormBtn.disabled = true;
                    deleteFormBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>删除中...';
                });
            }

            // 自动保存草稿功能
            const nameInput = document.getElementById('goal-name');
            const descriptionInput = document.getElementById('goal-description');

            if (nameInput) {
                let saveTimer;

                nameInput.addEventListener('input', function() {
                    clearTimeout(saveTimer);
                    saveTimer = setTimeout(() => {
                        saveDraft();
                    }, 1000);
                });

                if (descriptionInput) {
                    descriptionInput.addEventListener('input', function() {
                        clearTimeout(saveTimer);
                        saveTimer = setTimeout(() => {
                            saveDraft();
                        }, 1000);
                    });
                }

                function saveDraft() {
                    // 这里可以添加自动保存到localStorage的功能
                    console.log('表单内容已变更，可以添加自动保存功能');
                }

                // 页面离开前提示
                window.addEventListener('beforeunload', function(e) {
                    if (nameInput.value !== "{{ $goal->name }}" ||
                        (descriptionInput && descriptionInput.value !== "{{ $goal->description ?? '' }}")) {
                        e.preventDefault();
                        e.returnValue = '您有未保存的修改，确定要离开吗？';
                    }
                });
            }

            // 表单验证
            if (goalForm) {
                goalForm.addEventListener('submit', function(e) {
                    const nameInput = document.getElementById('goal-name');
                    if (!nameInput.value.trim()) {
                        e.preventDefault();
                        nameInput.focus();
                        showNotification('warning', '请输入目标名称');
                        return false;
                    }
                });
            }

            // 通知函数
            function showNotification(type, message) {
                // 移除已有的通知
                document.querySelectorAll('.notification-item').forEach(el => el.remove());

                const notification = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' : 'bg-yellow-500';
                const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

                notification.className = `notification-item fixed top-4 right-4 z-50 fade-in max-w-sm`;
                notification.innerHTML = `
                <div class="card ${bgColor} text-white shadow-xl">
                    <div class="p-4 flex items-center gap-3">
                        <i class="fas ${icon} text-lg"></i>
                        <div class="flex-1">${message}</div>
                        <button class="text-white hover:text-gray-200 close-notification">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

                document.body.appendChild(notification);

                // 点击关闭
                notification.querySelector('.close-notification').addEventListener('click', () => {
                    notification.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.opacity = '0';
                        setTimeout(() => {
                            notification.parentNode.removeChild(notification);
                        }, 300);
                    }
                }, 5000);
            }
        });
    </script>
@endsection