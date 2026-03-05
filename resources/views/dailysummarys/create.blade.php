@extends('layouts.app')

@section('title', '创建日报 - 蒙太奇')
@section('description', '记录您的工作和生活总结，回顾每日成长')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- AI 助手模态框 -->
        @include('components.ai-ask-modal')

        <!-- 页面标题和导航 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center text-sm text-gray-600 mb-3">
                        <a href="{{ url('/') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            <i class="fas fa-home mr-1"></i>首页
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <a href="{{ url('/dailysummarys') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            日报记录
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <span class="text-gray-900 font-medium">创建日报</span>
                    </nav>

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-plus-circle text-primary-color mr-3"></i>
                        创建日报
                    </h1>
                    <p class="text-gray-600 mt-2">记录今日的工作与生活，为每一天画上圆满句号</p>
                </div>

                <!-- 返回按钮 -->
                <a href="{{ url('/dailysummarys') }}" class="btn btn-outline flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>

            <!-- 日期统计 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-day text-blue-600"></i>
                        </div>
                        <div>
                            <div class="text-sm text-blue-700">今日日期</div>
                            <div id="todayLabel" class="text-lg font-semibold text-blue-900">-</div>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                            <i class="fas fa-clipboard-check text-green-600"></i>
                        </div>
                        <div>
                            <div class="text-sm text-green-700">本月日报</div>
                            <div class="text-lg font-semibold text-green-900">0 篇</div>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                            <i class="fas fa-fire text-purple-600"></i>
                        </div>
                        <div>
                            <div class="text-sm text-purple-700">连续记录</div>
                            <div class="text-lg font-semibold text-purple-900">0 天</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 左侧：日报表单 -->
            <div>
                <div class="card">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-edit text-primary-color mr-2"></i>
                            填写日报内容
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">记录您的所思所感，所见所闻</p>
                    </div>

                    <div class="p-6">
                        <!-- 日报表单 -->
                        <form action="javascript:void(0)" method="POST" class="space-y-6" id="dailySummaryForm">
                            <div class="space-y-6">
                                <!-- 总结日期 -->
                                <div class="space-y-3">
                                    <label for="dailysummary-summarydate" class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i class="far fa-calendar text-gray-400 mr-2 text-sm"></i>
                                        总结日期
                                        <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="flex space-x-3">
                                        <div class="relative flex-1">
                                            <input
                                                    type="date"
                                                    name="summary_date"
                                                    id="dailysummary-summarydate"
                                                    value=""
                                                    class="input w-full pl-10"
                                                    max="{{ date('Y-m-d') }}"
                                                    required
                                            >
                                            <div class="absolute left-3 top-3 text-gray-400">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                        </div>
                                        <button type="button" id="gettip" class="btn btn-outline whitespace-nowrap">
                                            <i class="fas fa-lightbulb mr-2"></i>
                                            获取提示
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-500">选择您要总结的日期，默认为今天</p>
                                </div>

                                <!-- 工作总结 -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label for="dailysummary-workcontent" class="block text-sm font-medium text-gray-700 flex items-center">
                                            <i class="fas fa-briefcase text-gray-400 mr-2 text-sm"></i>
                                            工作总结
                                        </label>
                                        <div class="flex items-center space-x-2">
                                            <button type="button"
                                                    onclick="openAskAIModal('tipBody', 'dailysummary-workcontent', '工作')"
                                                    class="btn btn-sm btn-outline flex items-center">
                                                <i class="fas fa-robot mr-1"></i>
                                                AI助手
                                            </button>
                                            <button type="button" onclick="suggestWorkContent()" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-magic mr-1"></i>
                                                生成大纲
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative">
                                    <textarea
                                            name="work_content"
                                            id="dailysummary-workcontent"
                                            rows="6"
                                            class="input w-full resize-none"
                                            placeholder="记录今日工作成果、遇到的问题、学习收获等..."
                                            data-counter-id="workCounter"
                                    ></textarea>
                                        <div class="absolute bottom-3 right-3">
                                            <span id="workCounter" class="text-xs text-gray-400">0/1000</span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                        提示：可以记录完成的任务、学到的技能、遇到的挑战等
                                    </div>
                                </div>

                                <!-- 生活总结 -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label for="dailysummary-lifecontent" class="block text-sm font-medium text-gray-700 flex items-center">
                                            <i class="fas fa-heart text-gray-400 mr-2 text-sm"></i>
                                            生活总结
                                        </label>
                                        <div class="flex items-center space-x-2">
                                            <button type="button"
                                                    onclick="openAskAIModal('tipBody', 'dailysummary-lifecontent', '生活')"
                                                    class="btn btn-sm btn-outline flex items-center">
                                                <i class="fas fa-robot mr-1"></i>
                                                AI助手
                                            </button>
                                            <button type="button" onclick="suggestLifeContent()" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-magic mr-1"></i>
                                                生成大纲
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative">
                                    <textarea
                                            name="life_content"
                                            id="dailysummary-lifecontent"
                                            rows="6"
                                            class="input w-full resize-none"
                                            placeholder="记录今日生活点滴、心情变化、感悟收获等..."
                                            data-counter-id="lifeCounter"
                                    ></textarea>
                                        <div class="absolute bottom-3 right-3">
                                            <span id="lifeCounter" class="text-xs text-gray-400">0/1000</span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                        提示：可以记录健康、家庭、朋友、兴趣、感悟等
                                    </div>
                                </div>

                                <!-- 情绪评分（扩展功能） -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-smile text-gray-400 mr-2 text-sm"></i>
                                        今日心情（可选）
                                    </label>
                                    <div class="flex flex-wrap gap-3">
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="mood" value="非常棒" class="sr-only peer">
                                            <div class="px-4 py-3 border border-gray-200 rounded-lg flex flex-col items-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm hover:bg-gray-50 transition-all duration-200">
                                                <span class="text-2xl mb-1">😄</span>
                                                <span class="text-sm text-gray-700">非常棒</span>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="mood" value="还不错" class="sr-only peer">
                                            <div class="px-4 py-3 border border-gray-200 rounded-lg flex flex-col items-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm hover:bg-gray-50 transition-all duration-200">
                                                <span class="text-2xl mb-1">🙂</span>
                                                <span class="text-sm text-gray-700">还不错</span>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="mood" value="一般般" class="sr-only peer">
                                            <div class="px-4 py-3 border border-gray-200 rounded-lg flex flex-col items-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm hover:bg-gray-50 transition-all duration-200">
                                                <span class="text-2xl mb-1">😐</span>
                                                <span class="text-sm text-gray-700">一般般</span>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="mood" value="不太好" class="sr-only peer">
                                            <div class="px-4 py-3 border border-gray-200 rounded-lg flex flex-col items-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm hover:bg-gray-50 transition-all duration-200">
                                                <span class="text-2xl mb-1">😔</span>
                                                <span class="text-sm text-gray-700">不太好</span>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="mood" value="很糟糕" class="sr-only peer">
                                            <div class="px-4 py-3 border border-gray-200 rounded-lg flex flex-col items-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm hover:bg-gray-50 transition-all duration-200">
                                                <span class="text-2xl mb-1">😢</span>
                                                <span class="text-sm text-gray-700">很糟糕</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- 标签（扩展功能） -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-tags text-gray-400 mr-2 text-sm"></i>
                                        添加标签（可选）
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-3" id="selectedTags">
                                        <!-- 已选标签会动态显示在这里 -->
                                    </div>
                                    <div class="flex space-x-2">
                                        <input
                                                type="text"
                                                id="tagInput"
                                                class="input flex-1"
                                                placeholder="输入标签，按回车添加"
                                        >
                                        <button type="button" onclick="addTag()" class="btn btn-outline">
                                            <i class="fas fa-plus mr-1"></i>
                                            添加
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <button type="button" onclick="quickAddTag('工作突破')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">工作突破</button>
                                        <button type="button" onclick="quickAddTag('学习成长')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">学习成长</button>
                                        <button type="button" onclick="quickAddTag('家庭时光')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">家庭时光</button>
                                        <button type="button" onclick="quickAddTag('朋友聚会')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">朋友聚会</button>
                                        <button type="button" onclick="quickAddTag('运动健康')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">运动健康</button>
                                        <button type="button" onclick="quickAddTag('阅读思考')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">阅读思考</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 表单操作按钮 -->
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-history mr-1"></i>
                                    上次保存时间：<span id="lastSaveTime">尚未保存</span>
                                </div>
                                <div class="flex space-x-3">
                                    <button type="button" onclick="saveDraft()" class="btn btn-outline">
                                        <i class="far fa-save mr-2"></i>
                                        保存草稿
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        提交日报
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右侧：提示卡片 -->
            <div>
                <div class="card sticky top-20">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                日总结小提示
                            </h2>
                            <div class="flex items-center text-sm text-gray-600" id="tipDateDisplay">
                                <i class="far fa-calendar mr-2"></i>
                                <span id="tipdate">{{ date('Y年m月d日') }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">根据您选择的日期，为您提供总结参考</p>
                    </div>

                    <div class="p-6">
                        <div id="tipBody">
                            <!-- 加载状态 -->
                            <div id="tipLoading" class="text-center py-8">
                                <div class="w-16 h-16 mx-auto mb-4">
                                    <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                                </div>
                                <p class="text-gray-600">正在获取提示信息...</p>
                            </div>

                            <!-- 提示内容 -->
                            <div id="tips" class="space-y-6"></div>

                            <!-- 空状态 -->
                            <div id="tipEmpty" class="hidden text-center py-12">
                                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-info-circle text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">暂无提示信息</h3>
                                <p class="text-gray-600">点击"获取提示"按钮，获取当日的活动记录</p>
                            </div>

                            <!-- 错误状态 -->
                            <div id="tipError" class="hidden text-center py-12">
                                <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-exclamation-circle text-red-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">获取提示失败</h3>
                                <p class="text-gray-600">请检查网络连接后重试</p>
                                <button onclick="getInfos()" class="btn btn-outline mt-4">
                                    <i class="fas fa-redo mr-2"></i>
                                    重试
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 快速提示卡片 -->
                <div class="card mt-6">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-rocket text-green-500 mr-2"></i>
                            日报写作小技巧
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check text-blue-600 text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-600">记录具体事件，而非抽象感受</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check text-green-600 text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-600">关注成长和进步，而非单纯任务清单</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check text-purple-600 text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-600">平衡工作与生活，记录有意义的瞬间</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check text-yellow-600 text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-600">定期回顾，发现自己的成长轨迹</p>
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

            // 初始化函数
            document.addEventListener('DOMContentLoaded', function() {
                // 设置默认日期
                const dateInput = document.getElementById('dailysummary-summarydate');
                if (!dateInput.value) {
                    dateInput.value = new Date().toISOString().slice(0, 10);
                }
                const todayLabel = document.getElementById('todayLabel');
                if (todayLabel) {
                    todayLabel.textContent = formatDateForDisplay(dateInput.value);
                }

                // 初始化字符计数器
                initCharCounters();

                // 自动获取当天的提示
                getInfos(dateInput.value);

                // 日期变化时重新获取提示
                dateInput.addEventListener('change', function() {
                    getInfos(this.value);
                    const todayLabel = document.getElementById('todayLabel');
                    if (todayLabel) {
                        todayLabel.textContent = formatDateForDisplay(this.value);
                    }
                });

                // 标签输入框回车添加标签
                document.getElementById('tagInput').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addTag();
                    }
                });
            });

            // 初始化字符计数器
            function initCharCounters() {
                const textareas = document.querySelectorAll('textarea[data-counter-id]');
                textareas.forEach(textarea => {
                    const counterId = textarea.getAttribute('data-counter-id');
                    updateCharCounter(textarea, counterId);

                    textarea.addEventListener('input', () => {
                        updateCharCounter(textarea, counterId);
                    });
                });
            }

            // 更新字符计数器
            function updateCharCounter(textarea, counterId) {
                const length = textarea.value.length;
                const maxLength = textarea.getAttribute('maxlength') || 1000;
                const counter = document.getElementById(counterId);

                if (counter) {
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
            }

            // 获取提示信息
            function getInfos(summaryDate = null) {
                if (!summaryDate) {
                    summaryDate = document.getElementById('dailysummary-summarydate').value;
                }

                // 更新日期显示
                document.getElementById('tipdate').textContent = formatDateForDisplay(summaryDate);

                // 显示加载状态
                document.getElementById('tipLoading').classList.remove('hidden');
                document.getElementById('tips').classList.add('hidden');
                document.getElementById('tipEmpty').classList.add('hidden');
                document.getElementById('tipError').classList.add('hidden');

                if (!apiRequest) {
                    showTipError();
                    document.getElementById('tipLoading').classList.add('hidden');
                    return;
                }
                apiRequest('GET', '/daily-summaries/tips', {
                    summary_date: summaryDate
                }).then(function(response) {
                    if (response && response.code === 9999) {
                        displayTips(response.result.infos);
                        return;
                    }
                    showTipError();
                }).catch(function() {
                    showTipError();
                }).finally(function() {
                    document.getElementById('tipLoading').classList.add('hidden');
                });
            }

            // 格式化日期显示
            function formatDateForDisplay(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('zh-CN', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    weekday: 'long'
                });
            }

            // 显示提示信息
            function displayTips(infos) {
                const tipsContainer = document.getElementById('tips');
                tipsContainer.innerHTML = '';

                if (!infos || Object.keys(infos).length === 0) {
                    document.getElementById('tipEmpty').classList.remove('hidden');
                    return;
                }

                // 清空并显示提示区域
                document.getElementById('tips').classList.remove('hidden');
                document.getElementById('tipEmpty').classList.add('hidden');

                // 遍历并渲染提示信息
                Object.entries(infos).forEach(([subject, tipInfo]) => {
                    const itemCount = tipInfo.list ? Object.keys(tipInfo.list).length : 0;

                    const categoryCard = document.createElement('div');
                    categoryCard.className = 'bg-gray-50 border border-gray-200 rounded-xl overflow-hidden';

                    // 分类标题
                    const categoryHeader = document.createElement('div');
                    categoryHeader.className = 'px-4 py-3 bg-gray-100 border-b border-gray-200';
                    categoryHeader.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg bg-white border border-gray-300 flex items-center justify-center mr-3">
                            <i class="fas fa-folder text-gray-600 text-sm"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">${tipInfo.name}</h4>
                    </div>
                    <span class="badge badge-primary">${itemCount} 项</span>
                </div>
            `;

                    // 项目列表
                    const itemsContainer = document.createElement('div');
                    itemsContainer.className = 'divide-y divide-gray-200';

                    if (tipInfo.list && itemCount > 0) {
                        Object.entries(tipInfo.list).forEach(([index, item]) => {
                            const itemElement = document.createElement('div');
                            itemElement.className = 'px-4 py-3 hover:bg-white transition-colors duration-150';

                            if (item.url) {
                                itemElement.innerHTML = `
                            <a href="${item.url}" target="_blank" class="flex items-start group">
                                <i class="fas fa-link text-gray-400 mr-3 mt-1 text-sm group-hover:text-blue-500 transition-colors duration-150"></i>
                                <div class="flex-1">
                                    <p class="text-gray-800 group-hover:text-blue-600 transition-colors duration-150">${item.content}</p>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-300 ml-2 mt-1 text-sm"></i>
                            </a>
                        `;
                            } else {
                                itemElement.innerHTML = `
                            <div class="flex items-start">
                                <i class="far fa-circle text-gray-300 mr-3 mt-1 text-xs"></i>
                                <p class="text-gray-700">${item.content}</p>
                            </div>
                        `;
                            }

                            itemsContainer.appendChild(itemElement);
                        });
                    } else {
                        itemsContainer.innerHTML = `
                    <div class="px-4 py-8 text-center">
                        <p class="text-gray-500">暂无记录</p>
                    </div>
                `;
                    }

                    categoryCard.appendChild(categoryHeader);
                    categoryCard.appendChild(itemsContainer);
                    tipsContainer.appendChild(categoryCard);
                });
            }

            // 显示提示错误
            function showTipError() {
                document.getElementById('tipError').classList.remove('hidden');
                document.getElementById('tips').classList.add('hidden');
            }

            // 添加标签
            function addTag() {
                const tagInput = document.getElementById('tagInput');
                const tagValue = tagInput.value.trim();

                if (!tagValue) {
                    showToast('error', '请输入标签内容');
                    return;
                }

                if (tagValue.length > 10) {
                    showToast('error', '标签不能超过10个字符');
                    return;
                }

                // 检查是否已存在
                const existingTags = Array.from(document.querySelectorAll('#selectedTags .tag-badge'))
                    .map(badge => badge.querySelector('span').textContent);

                if (existingTags.includes(tagValue)) {
                    showToast('info', '标签已存在');
                    return;
                }

                // 添加标签
                const tagBadge = document.createElement('div');
                tagBadge.className = 'tag-badge inline-flex items-center px-3 py-1.5 rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200';
                tagBadge.innerHTML = `
            <span class="text-sm">${tagValue}</span>
            <input type="hidden" name="tags[]" value="${tagValue}">
            <button type="button" onclick="removeTag(this)" class="ml-2 text-blue-600 hover:text-blue-800">
                <i class="fas fa-times text-xs"></i>
            </button>
        `;

                document.getElementById('selectedTags').appendChild(tagBadge);
                tagInput.value = '';
                showToast('success', '标签添加成功');
            }

            // 快速添加标签
            function quickAddTag(tag) {
                const tagInput = document.getElementById('tagInput');
                tagInput.value = tag;
                addTag();
            }

            // 移除标签
            function removeTag(button) {
                button.closest('.tag-badge').remove();
            }

            // 生成工作总结大纲
            function suggestWorkContent() {
                const workTextarea = document.getElementById('dailysummary-workcontent');
                const suggestions = [
                    "📊 今日完成：",
                    "   • 项目进展：",
                    "   • 任务完成：",
                    "   • 会议记录：",
                    "",
                    "🎯 明日计划：",
                    "   • 重点项目：",
                    "   • 待办事项：",
                    "",
                    "💡 学习收获：",
                    "   • 新技能：",
                    "   • 经验总结：",
                    "",
                    "🚧 遇到问题：",
                    "   • 技术难点：",
                    "   • 协作挑战："
                ];

                workTextarea.value = suggestions.join('\n');
                updateCharCounter(workTextarea, 'workCounter');
                showToast('info', '工作总结大纲已生成');
            }

            // 生成生活总结大纲
            function suggestLifeContent() {
                const lifeTextarea = document.getElementById('dailysummary-lifecontent');
                const suggestions = [
                    "❤️ 健康生活：",
                    "   • 运动锻炼：",
                    "   • 饮食情况：",
                    "   • 睡眠质量：",
                    "",
                    "👨‍👩‍👧‍👦 家庭时光：",
                    "   • 家人互动：",
                    "   • 家庭活动：",
                    "",
                    "🎨 兴趣培养：",
                    "   • 阅读书籍：",
                    "   • 兴趣爱好：",
                    "   • 休闲娱乐：",
                    "",
                    "🤔 感悟思考：",
                    "   • 今日感悟：",
                    "   • 生活启示：",
                    "",
                    "🎯 明日期待：",
                    "   • 期待事项：",
                    "   • 生活计划："
                ];

                lifeTextarea.value = suggestions.join('\n');
                updateCharCounter(lifeTextarea, 'lifeCounter');
                showToast('info', '生活总结大纲已生成');
            }

            // 保存草稿
            function saveDraft() {
                // 这里可以添加草稿保存逻辑
                const now = new Date();
                const timeString = now.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('lastSaveTime').textContent = timeString;
                showToast('success', '草稿保存成功');
            }

            // 表单提交验证
            document.getElementById('dailySummaryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const dateInput = document.getElementById('dailysummary-summarydate');
                const workContent = document.getElementById('dailysummary-workcontent');
                const lifeContent = document.getElementById('dailysummary-lifecontent');

                if (!dateInput.value) {
                    dateInput.focus();
                    showToast('error', '请选择总结日期');
                    return;
                }

                const selectedDate = new Date(dateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate > today) {
                    showToast('error', '总结日期不能晚于今天');
                    return;
                }
                if (!apiRequest) {
                    showToast('error', 'API客户端未初始化');
                    return;
                }

                // 显示提交中状态
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>提交中...';
                submitBtn.disabled = true;

                apiRequest('POST', '/daily-summaries', {
                    summary_date: dateInput.value,
                    work_content: workContent.value || '',
                    life_content: lifeContent.value || ''
                }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        showToast('success', '日报提交成功');
                        setTimeout(function() {
                            window.location.href = '{{ url('/dailysummarys') }}';
                        }, 300);
                        return;
                    }
                    showToast('error', (resp && resp.msg) ? resp.msg : '提交失败');
                }).catch(function() {
                    showToast('error', '提交失败，请稍后重试');
                }).finally(function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
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
            /* 日期选择器样式 */
            input[type="date"]::-webkit-calendar-picker-indicator {
                opacity: 0;
                cursor: pointer;
                position: absolute;
                width: 100%;
                height: 100%;
                left: 0;
                top: 0;
            }

            /* 标签样式 */
            .tag-badge {
                transition: all 0.2s ease;
            }

            .tag-badge:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
            }

            /* 心情选择器动画 */
            input[name="mood"]:checked + div {
                animation: moodSelect 0.3s ease-out;
            }

            @keyframes moodSelect {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }

            /* 文本域聚焦效果 */
            textarea:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            /* 提示卡片动画 */
            .card.sticky {
                position: sticky;
                top: 6rem;
                transition: top 0.3s ease;
            }

            /* 加载动画 */
            .fa-spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* 渐变色背景 */
            .bg-gradient-to-br {
                background-size: 200% 200%;
                animation: gradientShift 15s ease infinite;
            }

            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
        </style>
    @endsection
