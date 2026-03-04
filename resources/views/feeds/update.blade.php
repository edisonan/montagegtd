@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和导航 -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">修改订阅</h1>
                <p class="text-gray-600 mt-1">更新订阅源信息和分类设置</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <a href="{{ url('feeds') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>返回订阅列表
                </a>
                <a href="{{ url('categorys') }}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-cog mr-2"></i>分类设置
                </a>
            </div>
        </div>

        <!-- 主要内容区域 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 左侧：订阅编辑表单 -->
            <div class="lg:col-span-2">
                <div class="card card-elevated">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-rss text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">订阅信息</h2>
                                <p class="text-sm text-gray-500 mt-1">修改当前订阅的配置</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- 显示验证错误 -->
                        @include('common.errors')

                        <form action="{{ url('/api/v2/feeds/'.$feedSub->id) }}" method="POST" class="space-y-6" id="feed-edit-form">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}

                            <!-- 订阅URL检测 -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">订阅源地址</h4>
                                        <p class="text-sm text-gray-500">当前订阅地址：<span class="font-mono text-xs">{{ $feedSub->url }}</span></p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $feedSub->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $feedSub->is_valid ? '✓ 有效' : '✗ 无效' }}
                                    </span>
                                        <button type="button" onclick="checkFeedUrl()" class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                                            <i class="fas fa-sync-alt mr-1"></i>重新检测
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 订阅名称 -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="feed_name" class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-heading text-gray-400 mr-2"></i>订阅名称 <span class="text-red-500">*</span>
                                    </label>
                                    <span class="text-xs text-gray-500" id="name-counter">{{ strlen($feedSub->feed_name) }}/100</span>
                                </div>
                                <input type="text"
                                       name="feed_name"
                                       id="feed_name"
                                       class="input w-full"
                                       value="{{ $feedSub->feed_name }}"
                                       placeholder="例如：TechCrunch 科技资讯"
                                       maxlength="100"
                                       required>
                                <p class="text-sm text-gray-500 mt-2">订阅源的显示名称，建议简洁明了</p>
                            </div>

                            <!-- 订阅排序 -->
                            <div>
                                <label for="feed_order" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sort-numeric-down text-gray-400 mr-2"></i>订阅排序
                                </label>
                                <div class="relative w-48">
                                    <input type="number"
                                           name="feed_order"
                                           id="feed_order"
                                           class="input w-full"
                                           value="{{ $feedSub->feed_order }}"
                                           min="0"
                                           step="1"
                                           placeholder="0">
                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                                        <span class="text-sm">数字越小越靠前</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">控制订阅在列表中的显示顺序</p>
                            </div>

                            <!-- 所属分类 -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-folder text-gray-400 mr-2"></i>所属分类 <span class="text-red-500">*</span>
                                    </label>
                                    <a href="{{ url('categorys') }}" target="_blank" class="text-xs text-primary-600 hover:text-primary-800">
                                        <i class="fas fa-plus-circle mr-1"></i>新增分类
                                    </a>
                                </div>

                                @if(count($categorys) == 0)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 text-lg mr-3"></i>
                                            <div>
                                                <p class="text-sm font-medium text-yellow-800 mb-1">尚未创建分类</p>
                                                <p class="text-sm text-yellow-700">所有订阅必须属于一个分类，请先创建分类</p>
                                                <div class="mt-3">
                                                    <a href="{{ url('categorys') }}" target="_blank" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus mr-2"></i>创建分类
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        @foreach ($categorys as $category)
                                            <label class="relative cursor-pointer">
                                                <input type="radio"
                                                       name="category_id"
                                                       value="{{ $category->id }}"
                                                       class="sr-only peer"
                                                        {{ $feedSub->category_id == $category->id ? 'checked' : '' }}>
                                                <div class="card border-2 border-gray-200 peer-checked:border-primary-500 peer-checked:ring-2 peer-checked:ring-primary-200 peer-checked:bg-primary-50 transition-all hover:border-gray-300 hover:bg-gray-50">
                                                    <div class="p-4">
                                                        <div class="flex items-center mb-2">
                                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background-color: {{ $category->color ?? '#4a90e2' }};">
                                                                <i class="{{ $category->icon ?? 'fas fa-folder' }} text-white text-sm"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="text-sm font-medium text-gray-900">{{ $category->name }}</h4>
                                                                <p class="text-xs text-gray-500 mt-1">
                                                                    包含 {{ $category->feeds_count ?? 0 }} 个订阅
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-3">
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                                            {{ $category->is_active ? '启用' : '停用' }}
                                                        </span>
                                                            <span class="text-xs font-medium text-primary-600">
                                                            {{ $feedSub->category_id == $category->id ? '已选择' : '' }}
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="text-sm text-gray-500 mt-3">选择一个分类来组织您的订阅源</p>
                                @endif
                            </div>

                            <!-- 高级设置折叠面板 -->
                            <div>
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button" onclick="toggleAdvancedSettings()"
                                            class="w-full flex items-center justify-between p-4 text-left bg-gray-50 hover:bg-gray-100 transition">
                                        <div class="flex items-center">
                                            <i class="fas fa-sliders-h text-gray-400 mr-3"></i>
                                            <span class="font-medium text-gray-900">高级设置</span>
                                        </div>
                                        <i id="advancedSettingsIcon" class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                                    </button>

                                    <div id="advancedSettingsContent" class="hidden border-t border-gray-200 p-4 bg-white">
                                        <div class="space-y-4">
                                            <!-- 更新频率 -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    更新频率
                                                </label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <label class="relative cursor-pointer">
                                                        <input type="radio" name="update_frequency" value="hourly"
                                                               class="sr-only peer" {{ ($feedSub->update_frequency ?? 'hourly') == 'hourly' ? 'checked' : '' }}>
                                                        <div class="px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 text-center hover:border-gray-300 transition">
                                                            <div class="text-sm font-medium text-gray-900">每小时</div>
                                                            <div class="text-xs text-gray-500 mt-1">快速更新</div>
                                                        </div>
                                                    </label>
                                                    <label class="relative cursor-pointer">
                                                        <input type="radio" name="update_frequency" value="daily"
                                                               class="sr-only peer" {{ ($feedSub->update_frequency ?? 'hourly') == 'daily' ? 'checked' : '' }}>
                                                        <div class="px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 text-center hover:border-gray-300 transition">
                                                            <div class="text-sm font-medium text-gray-900">每天</div>
                                                            <div class="text-xs text-gray-500 mt-1">标准更新</div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- 自动标记已读 -->
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <label class="text-sm font-medium text-gray-900">自动标记已读</label>
                                                    <p class="text-sm text-gray-500 mt-1">超过7天的文章自动标记为已读</p>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="auto_mark_read" value="1"
                                                           class="sr-only peer" {{ $feedSub->auto_mark_read ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                                </label>
                                            </div>

                                            <!-- 仅显示未读 -->
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <label class="text-sm font-medium text-gray-900">仅显示未读</label>
                                                    <p class="text-sm text-gray-500 mt-1">在列表中只显示未读文章</p>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="show_unread_only" value="1"
                                                           class="sr-only peer" {{ $feedSub->show_unread_only ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 表单操作 -->
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    保存后订阅将自动开始同步
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i>取消
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>更新订阅
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右侧：订阅信息统计 -->
            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">订阅统计</h2>
                        <p class="text-sm text-gray-500 mt-1">当前订阅的状态信息</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- 基本信息 -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">订阅状态</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $feedSub->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $feedSub->is_active ? '启用中' : '已停用' }}
                            </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">最后更新</span>
                                <span class="text-sm text-gray-900">{{ $feedSub->last_updated_at ? date('Y-m-d H:i', strtotime($feedSub->last_updated_at)) : '从未更新' }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">文章总数</span>
                                <span class="text-sm font-medium text-gray-900">{{ $feedSub->articles_count ?? 0 }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">未读文章</span>
                                <span class="text-sm font-medium text-primary-600">{{ $feedSub->unread_count ?? 0 }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">收藏文章</span>
                                <span class="text-sm font-medium text-yellow-600">{{ $feedSub->starred_count ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- 操作按钮 -->
                        <div class="space-y-3">
                            <button type="button" onclick="refreshFeed()" class="btn btn-secondary w-full justify-center">
                                <i class="fas fa-sync-alt mr-2"></i>立即同步
                            </button>

                            @if($feedSub->is_active)
                                <button type="button" onclick="toggleFeedStatus(false)" class="btn btn-outline w-full justify-center text-yellow-600 border-yellow-600 hover:bg-yellow-50">
                                    <i class="fas fa-pause mr-2"></i>暂停订阅
                                </button>
                            @else
                                <button type="button" onclick="toggleFeedStatus(true)" class="btn btn-outline w-full justify-center text-green-600 border-green-600 hover:bg-green-50">
                                    <i class="fas fa-play mr-2"></i>启用订阅
                                </button>
                            @endif

                            <button type="button" onclick="clearFeedArticles()" class="btn btn-outline w-full justify-center text-red-600 border-red-600 hover:bg-red-50">
                                <i class="fas fa-trash-alt mr-2"></i>清空文章
                            </button>
                        </div>

                        <!-- 提示信息 -->
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-blue-800 mb-1">提示</p>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li>• 修改订阅名称不会影响文章内容</li>
                                        <li>• 调整分类会重新组织订阅列表</li>
                                        <li>• 立即同步会强制更新最新文章</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        document.addEventListener('DOMContentLoaded', function() {
            // 字数统计
            const feedNameInput = document.getElementById('feed_name');
            const nameCounter = document.getElementById('name-counter');

            function updateNameCounter() {
                const length = feedNameInput.value.length;
                const maxLength = feedNameInput.getAttribute('maxlength');
                nameCounter.textContent = `${length}/${maxLength}`;

                if (length > maxLength * 0.9) {
                    nameCounter.classList.add('text-warning-color');
                } else {
                    nameCounter.classList.remove('text-warning-color');
                }
            }

            feedNameInput.addEventListener('input', updateNameCounter);
            updateNameCounter(); // 初始化

            // 表单提交处理
            const form = document.getElementById('feed-edit-form');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                // 显示加载状态
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';
                submitBtn.disabled = true;

                try {
                    if (!apiRequest) {
                        throw new Error('API客户端未初始化');
                    }

                    const data = await apiRequest('PUT', '/feeds/{{ $feedSub->id }}', {
                        feed_name: document.getElementById('feed_name').value.trim(),
                        category_id: document.getElementById('category_id').value
                    });

                    if (data.success || (data.code && data.code === 9999)) {
                        showToast('订阅更新成功！', 'success');

                        // 延迟跳转
                        setTimeout(() => {
                            window.location.href = '/feeds';
                        }, 1000);
                    } else {
                        showToast(data.message || '保存失败，请稍后重试', 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('保存失败:', error);
                    showToast('网络错误，请稍后重试', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        });

        // 检测订阅URL
        async function checkFeedUrl() {
            const url = '{{ $feedSub->url }}';

            if (!url) {
                showToast('订阅地址为空', 'warning');
                return;
            }
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            showToast('正在检测订阅地址...', 'info');

            try {
                const data = await apiRequest('GET', '/feeds/check-feed-url', { url: url });

                if (data.code === 9999) {
                    showToast('订阅地址检测成功！', 'success');

                    // 如果有新的标题，更新输入框
                    if (data.result && data.result.title) {
                        document.getElementById('feed_name').value = data.result.title;
                        document.getElementById('feed_name').dispatchEvent(new Event('input'));
                    }
                } else {
                    showToast('订阅地址检测失败，请确认地址正确', 'error');
                }
            } catch (error) {
                console.error('检测失败:', error);
                showToast('检测失败，请检查网络连接', 'error');
            }
        }

        // 立即同步订阅
        async function refreshFeed() {
            const feedId = '{{ $feedSub->id }}';

            showToast('正在同步订阅...', 'info');
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            try {
                const data = await apiRequest('POST', `/feeds/${feedId}/refresh`, {});

                if (data.success || data.code === 9999) {
                    showToast('同步完成！', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('同步失败：' + (data.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('同步失败:', error);
                showToast('同步失败，请检查网络连接', 'error');
            }
        }

        // 切换订阅状态
        async function toggleFeedStatus(enable) {
            const feedId = '{{ $feedSub->id }}';
            const action = enable ? '启用' : '暂停';

            if (!confirm(`确定要${action}这个订阅吗？`)) {
                return;
            }

            showToast(`${action}订阅中...`, 'info');
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            try {
                const data = await apiRequest('POST', `/feeds/${feedId}/toggle-status`, {
                    enable: enable ? 1 : 0
                });

                if (data.success || data.code === 9999) {
                    showToast(`订阅已${action}`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(`${action}失败：` + (data.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('状态切换失败:', error);
                showToast('操作失败，请检查网络连接', 'error');
            }
        }

        // 清空文章
        async function clearFeedArticles() {
            const feedId = '{{ $feedSub->id }}';

            if (!confirm('确定要清空这个订阅的所有文章吗？此操作不可撤销！')) {
                return;
            }

            showToast('正在清空文章...', 'info');
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            try {
                const data = await apiRequest('POST', `/feeds/${feedId}/clear-articles`, {});

                if (data.success || data.code === 9999) {
                    showToast('文章已清空', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('清空失败：' + (data.message || '未知错误'), 'error');
                }
            } catch (error) {
                console.error('清空失败:', error);
                showToast('操作失败，请检查网络连接', 'error');
            }
        }

        // 切换高级设置
        function toggleAdvancedSettings() {
            const content = document.getElementById('advancedSettingsContent');
            const icon = document.getElementById('advancedSettingsIcon');

            content.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }

        // 显示Toast提示（复用之前的函数）
        function showToast(message, type = 'info') {
            const existingToast = document.getElementById('global-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const typeConfig = {
                success: { icon: 'fa-check-circle', bgColor: 'bg-green-100', textColor: 'text-green-800', borderColor: 'border-green-200' },
                error: { icon: 'fa-exclamation-circle', bgColor: 'bg-red-100', textColor: 'text-red-800', borderColor: 'border-red-200' },
                warning: { icon: 'fa-exclamation-triangle', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', borderColor: 'border-yellow-200' },
                info: { icon: 'fa-info-circle', bgColor: 'bg-blue-100', textColor: 'text-blue-800', borderColor: 'border-blue-200' }
            };

            const config = typeConfig[type] || typeConfig.info;

            const toast = document.createElement('div');
            toast.id = 'global-toast';
            toast.className = `fixed top-6 right-6 ${config.bgColor} ${config.textColor} ${config.borderColor} border rounded-lg px-4 py-3 shadow-lg z-[10000] flex items-center space-x-3 max-w-sm fade-in`;
            toast.innerHTML = `
        <i class="fas ${config.icon} text-lg"></i>
        <span>${message}</span>
    `;

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, 3000);
        }
    </script>

    <style>
        /* 自定义样式 */
        .text-warning-color {
            color: var(--warning-color);
        }

        /* 分类选择卡片动画 */
        .card input:checked + div {
            animation: cardPulse 0.3s ease;
        }

        @keyframes cardPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 切换开关样式 */
        .toggle-checkbox:checked {
            right: 0;
            border-color: var(--primary-color);
        }

        .toggle-checkbox:checked + .toggle-label {
            background-color: var(--primary-color);
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .lg\\:col-span-2, .lg\\:col-span-1 {
                grid-column: span 1;
            }

            .sticky {
                position: static;
            }

            .grid-cols-2, .grid-cols-3 {
                grid-template-columns: 1fr;
            }
        }

        /* 滚动条美化 */
        .card {
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) var(--gray-100);
        }

        .card::-webkit-scrollbar {
            width: 4px;
        }

        .card::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .card::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }
    </style>
@endsection
