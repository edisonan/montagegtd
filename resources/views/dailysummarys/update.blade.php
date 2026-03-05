@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和导航 -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">修改日报</h1>
                <p class="text-gray-600 mt-1">更新您的每日总结和反思</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ url('/dailysummarys') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>返回日报列表
                </a>
            </div>
        </div>

        <!-- 主要内容区域 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 左侧：日报编辑表单 -->
            <div class="lg:col-span-2">
                <div class="card card-elevated">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">日报信息</h2>
                        <p class="text-sm text-gray-500 mt-1">修改日期：<span id="summaryDateLabel">加载中...</span></p>
                    </div>

                    <div class="p-6">
                        <form action="#" method="POST" class="space-y-6" id="dailysummary-form">

                            <!-- 总结日期 -->
                            <div>
                                <label for="dailysummary-summarydate" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>总结日期 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date"
                                           name="summary_date"
                                           id="dailysummary-summarydate"
                                           class="input w-full pr-10 date-picker"
                                           value=""
                                           max="{{ date('Y-m-d') }}"
                                           required>
                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-sm text-gray-500">选择要总结的日期</p>
                                    <button type="button" id="gettip" class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                                        <i class="fas fa-lightbulb mr-1"></i>获取提示
                                    </button>
                                </div>
                            </div>

                            <!-- 工作总结 -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="dailysummary-workcontent" class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-briefcase text-gray-400 mr-2"></i>工作总结
                                    </label>
                                    <span class="text-xs text-gray-500" id="work-content-counter">0/1000</span>
                                </div>
                                <textarea class="input w-full min-h-[120px] resize-none"
                                          name="work_content"
                                          id="dailysummary-workcontent"
                                          rows="4"
                                          placeholder="记录今天的工作内容、进展、遇到的问题和解决方案..."
                                          maxlength="1000"></textarea>
                                <div class="mt-2 text-sm text-gray-500 space-y-1">
                                    <p class="flex items-center"><i class="fas fa-check-circle text-success-color text-xs mr-2"></i>完成了什么重要任务？</p>
                                    <p class="flex items-center"><i class="fas fa-question-circle text-warning-color text-xs mr-2"></i>遇到了什么问题？如何解决？</p>
                                    <p class="flex items-center"><i class="fas fa-bullseye text-primary-color text-xs mr-2"></i>明天的工作计划是什么？</p>
                                </div>
                            </div>

                            <!-- 生活总结 -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="dailysummary-lifecontent" class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-heart text-gray-400 mr-2"></i>生活总结
                                    </label>
                                    <span class="text-xs text-gray-500" id="life-content-counter">0/1000</span>
                                </div>
                                <textarea class="input w-full min-h-[120px] resize-none"
                                          name="life_content"
                                          id="dailysummary-lifecontent"
                                          rows="4"
                                          placeholder="记录今天的生活感悟、学习收获、健康状态和心情变化..."
                                          maxlength="1000"></textarea>
                                <div class="mt-2 text-sm text-gray-500 space-y-1">
                                    <p class="flex items-center"><i class="fas fa-book text-success-color text-xs mr-2"></i>学到了什么新知识？</p>
                                    <p class="flex items-center"><i class="fas fa-smile text-warning-color text-xs mr-2"></i>今天的心情如何？为什么？</p>
                                    <p class="flex items-center"><i class="fas fa-dumbbell text-primary-color text-xs mr-2"></i>运动、饮食、睡眠情况？</p>
                                </div>
                            </div>

                            <!-- 表单操作 -->
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    日报将在保存后同步到您的个人时间线
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i>取消
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>更新日报
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右侧：日总结提示 -->
            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">日总结小提示</h2>
                            <span id="tipdate" class="text-sm text-gray-500 badge badge-primary"></span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">基于您选择日期的活动智能生成</p>
                    </div>

                    <div class="p-6" id="tipBody">
                        <!-- 初始加载状态 -->
                        <div id="tips-loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-color mb-4"></div>
                            <p class="text-gray-500">正在加载提示信息...</p>
                        </div>

                        <!-- 提示内容容器 -->
                        <div id="tips-content" class="hidden">
                            <div class="space-y-4" id="tips">
                                <!-- 提示内容将通过JavaScript动态加载 -->
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                    <span>提示基于您当天的番茄钟、任务和阅读记录生成</span>
                                </div>
                            </div>
                        </div>

                        <!-- 无提示状态 -->
                        <div id="tips-empty" class="hidden text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-info-circle text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-600 font-medium">暂无提示信息</p>
                            <p class="text-sm text-gray-500 mt-1">选择日期后点击"获取提示"按钮</p>
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
        var currentDailySummaryId = null;

        function getDailySummaryIdFromPath() {
            var match = window.location.pathname.match(/\/dailysummary\/(\d+)/);
            return match ? Number(match[1]) : 0;
        }

        function loadDailySummary() {
            if (!currentDailySummaryId || !apiRequest) {
                return;
            }

            apiRequest('GET', '/daily-summaries/' + currentDailySummaryId, {}).then(function(data) {
                if (!data || data.code !== 9999 || !data.result) {
                    throw new Error((data && data.msg) || '加载失败');
                }
                var summary = data.result || {};
                document.getElementById('summaryDateLabel').textContent = summary.summary_date || '-';
                document.getElementById('dailysummary-summarydate').value = summary.summary_date || '';
                document.getElementById('dailysummary-workcontent').value = summary.work_content || '';
                document.getElementById('dailysummary-lifecontent').value = summary.life_content || '';
                document.getElementById('tipdate').textContent = formatDate(summary.summary_date || '');
                getInfos(summary.summary_date || '');

                var workCounter = document.getElementById('work-content-counter');
                var lifeCounter = document.getElementById('life-content-counter');
                workCounter.textContent = (summary.work_content || '').length + '/1000';
                lifeCounter.textContent = (summary.life_content || '').length + '/1000';
            }).catch(function() {
                showToast('日报加载失败，请稍后重试', 'error');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            currentDailySummaryId = getDailySummaryIdFromPath();
            if (!currentDailySummaryId) {
                showToast('日报ID无效', 'error');
                return;
            }

            // 日期选择器事件
            const dateInput = document.getElementById('dailysummary-summarydate');
            dateInput.addEventListener('change', function() {
                const selectedDate = this.value;
                document.getElementById('tipdate').textContent = formatDate(selectedDate);
                getInfos(selectedDate);
            });

            // 获取提示按钮事件
            document.getElementById('gettip').addEventListener('click', function() {
                const selectedDate = dateInput.value;
                getInfos(selectedDate);
            });

            // 字数统计
            const workContent = document.getElementById('dailysummary-workcontent');
            const lifeContent = document.getElementById('dailysummary-lifecontent');
            const workCounter = document.getElementById('work-content-counter');
            const lifeCounter = document.getElementById('life-content-counter');

            function updateCounter(textarea, counter) {
                const length = textarea.value.length;
                const maxLength = textarea.getAttribute('maxlength');
                counter.textContent = `${length}/${maxLength}`;

                if (length > maxLength * 0.9) {
                    counter.classList.add('text-warning-color');
                } else {
                    counter.classList.remove('text-warning-color');
                }
            }

            workContent.addEventListener('input', () => updateCounter(workContent, workCounter));
            lifeContent.addEventListener('input', () => updateCounter(lifeContent, lifeCounter));

            // 初始化计数器
            updateCounter(workContent, workCounter);
            updateCounter(lifeContent, lifeCounter);
            loadDailySummary();

            // 表单提交处理
            const form = document.getElementById('dailysummary-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                // 显示加载状态
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';
                submitBtn.disabled = true;

                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                apiRequest('PUT', '/daily-summaries/' + currentDailySummaryId, {
                    work_content: document.getElementById('dailysummary-workcontent').value || '',
                    life_content: document.getElementById('dailysummary-lifecontent').value || ''
                }).then(function(data) {
                    if (data && data.code === 9999) {
                        showToast('日报更新成功！', 'success');
                        setTimeout(function() {
                            window.location.href = '/dailysummarys';
                        }, 300);
                        return;
                    }
                    showToast((data && data.msg) ? data.msg : '保存失败，请稍后重试', 'error');
                }).catch(function(error) {
                    console.error('保存失败:', error);
                    showToast('网络错误，请稍后重试', 'error');
                }).finally(function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        });

        // 获取提示信息
        function getInfos(summaryDate) {
            const tipsContainer = document.getElementById('tips');
            const loadingDiv = document.getElementById('tips-loading');
            const contentDiv = document.getElementById('tips-content');
            const emptyDiv = document.getElementById('tips-empty');

            // 显示加载状态
            loadingDiv.classList.remove('hidden');
            contentDiv.classList.add('hidden');
            emptyDiv.classList.add('hidden');
            tipsContainer.innerHTML = '';

            if (!apiRequest) {
                loadingDiv.classList.add('hidden');
                emptyDiv.classList.remove('hidden');
                showToast('API客户端未初始化', 'error');
                return;
            }

            apiRequest('GET', '/daily-summaries/tips', {
                summary_date: summaryDate
            }).then(function(data) {
                    loadingDiv.classList.add('hidden');

                    if (data.code === 9999 && data.result && data.result.infos) {
                        const infos = data.result.infos;
                        let hasContent = false;

                        Object.entries(infos).forEach(([subject, tipInfo]) => {
                            const itemCount = Object.keys(tipInfo.list || {}).length;
                            if (itemCount > 0) {
                                hasContent = true;

                                const subjectCard = document.createElement('div');
                                subjectCard.className = 'bg-gray-50 rounded-lg p-4';

                                const subjectHeader = document.createElement('div');
                                subjectHeader.className = 'flex items-center justify-between mb-3';

                                const subjectTitle = document.createElement('h3');
                                subjectTitle.className = 'font-medium text-gray-900';
                                subjectTitle.innerHTML = `
                        <i class="${getSubjectIcon(subject)} text-primary-color mr-2"></i>
                        ${subject}
                    `;

                                const subjectCount = document.createElement('span');
                                subjectCount.className = 'badge badge-primary';
                                subjectCount.textContent = itemCount;

                                subjectHeader.appendChild(subjectTitle);
                                subjectHeader.appendChild(subjectCount);

                                const itemsList = document.createElement('div');
                                itemsList.className = 'space-y-2';

                                Object.entries(tipInfo.list).forEach(([index, item]) => {
                                    if (item.content) {
                                        const itemDiv = document.createElement('div');
                                        itemDiv.className = 'flex items-start text-sm';

                                        const itemIcon = document.createElement('div');
                                        itemIcon.className = 'mt-0.5 mr-2';
                                        itemIcon.innerHTML = '<i class="fas fa-circle text-gray-300 text-xs"></i>';

                                        const itemContent = document.createElement('div');
                                        itemContent.className = 'flex-1';

                                        const contentSpan = document.createElement('span');
                                        contentSpan.className = 'text-gray-700';
                                        contentSpan.textContent = item.content;

                                        itemContent.appendChild(contentSpan);

                                        if (item.url) {
                                            const link = document.createElement('a');
                                            link.href = item.url;
                                            link.className = 'ml-2 text-primary-600 hover:text-primary-800 text-xs whitespace-nowrap';
                                            link.target = '_blank';
                                            link.innerHTML = '<i class="fas fa-external-link-alt"></i>';
                                            itemContent.appendChild(link);
                                        }

                                        itemDiv.appendChild(itemIcon);
                                        itemDiv.appendChild(itemContent);
                                        itemsList.appendChild(itemDiv);
                                    }
                                });

                                subjectCard.appendChild(subjectHeader);
                                subjectCard.appendChild(itemsList);
                                tipsContainer.appendChild(subjectCard);
                            }
                        });

                        if (hasContent) {
                            contentDiv.classList.remove('hidden');
                        } else {
                            emptyDiv.classList.remove('hidden');
                        }
                    } else {
                        emptyDiv.classList.remove('hidden');
                        showToast('未找到相关提示信息', 'info');
                    }
                }).catch(function(error) {
                    console.error('获取提示失败:', error);
                    loadingDiv.classList.add('hidden');
                    emptyDiv.classList.remove('hidden');
                    showToast('获取提示失败，请稍后重试', 'error');
                });
        }

        // 获取主题图标
        function getSubjectIcon(subject) {
            const iconMap = {
                '番茄钟': 'fas fa-clock',
                '任务': 'fas fa-tasks',
                '阅读': 'fas fa-book',
                '想法': 'fas fa-lightbulb',
                '课程': 'fas fa-graduation-cap',
                '会议': 'fas fa-users',
                '项目': 'fas fa-project-diagram',
                '健康': 'fas fa-heartbeat',
                '学习': 'fas fa-brain',
                '工作': 'fas fa-briefcase'
            };

            return iconMap[subject] || 'fas fa-sticky-note';
        }

        // 格式化日期显示
        function formatDate(dateString) {
            if (!dateString) return '';

            const date = new Date(dateString);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            if (date.toDateString() === today.toDateString()) {
                return '今天';
            } else if (date.toDateString() === yesterday.toDateString()) {
                return '昨天';
            } else {
                return dateString;
            }
        }

        // 显示Toast提示
        function showToast(message, type = 'info') {
            // 实现同前一个优化的showToast函数
            // 这里可以复用之前的showToast函数
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
        .date-picker::-webkit-calendar-picker-indicator {
            opacity: 0;
            cursor: pointer;
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
        }

        .text-success-color {
            color: var(--success-color);
        }

        .text-warning-color {
            color: var(--warning-color);
        }

        .text-primary-color {
            color: var(--primary-color);
        }

        /* 动画效果 */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .lg\\:col-span-2, .lg\\:col-span-1 {
                grid-column: span 1;
            }

            .sticky {
                position: static;
            }
        }

        /* 滚动条美化 */
        #tipBody {
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) var(--gray-100);
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        #tipBody::-webkit-scrollbar {
            width: 4px;
        }

        #tipBody::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        #tipBody::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }
    </style>
@endsection
