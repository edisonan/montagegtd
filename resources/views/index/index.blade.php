@extends('layouts.app')

@section('title', '主页 - 蒙太奇')
@section('description', '高效管理您的番茄钟和待办事项，提升个人生产力。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div id="doingTaskCard" class="mb-6 hidden"></div>
        <div id="executionStrip" class="mb-6 hidden"></div>
        <div id="indexMainGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 左侧：番茄钟面板 -->
            <div class="space-y-6">
                <!-- 专注操作卡片 -->
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">番茄钟</h2>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <!-- 添加纯净模式切换按钮 -->
                                <button id="pureModeToggle"
                                        class="text-sm px-3 py-1 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors">
                                    <i class="fas fa-eye-slash mr-1"></i>纯净模式
                                </button>
                                <a href="{{ url('focuss') }}"
                                   class="text-sm px-3 py-1 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-history mr-1"></i>专注历史
                                </a>
                                <button onclick="showJournalCreateModal()"
                                        class="text-sm px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-plus mr-1"></i>记录手账
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- 专注按钮区域 -->
                        <div class="mb-6">
                            <!-- 开始专注按钮 -->
                            <div id="focusStartSection" class="hidden">
                                <button id="focusBtn"
                                        class="btn btn-outline w-full py-2 text-lg font-semibold hover:scale-[1.02] transition-transform">
                                    <i class="fas fa-play-circle mr-3 text-xl"></i>
                                    开始新的番茄钟
                                </button>
                            </div>

                            <!-- 倒计时显示 -->
                            <div id="focusTimerSection" class="hidden">
                                <div class="relative">
                                    <div id="focusTimerContainer" class="absolute inset-0 flex items-center justify-center transition-opacity duration-300">
                                        <div class="text-center">
                                            <div id="focusTimer" class="text-4xl font-bold text-gray-900 mb-2"></div>
                                            <div id="focusStatus" class="text-sm text-gray-600"></div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <svg class="w-full h-48" viewBox="0 0 100 100">
                                            <circle cx="50" cy="50" r="45" fill="none"
                                                    stroke="#e8f5e9" stroke-width="2"/>
                                            <circle id="progressCircle" cx="50" cy="50" r="45" fill="none"
                                                    stroke="#00b894" stroke-width="2"
                                                    stroke-linecap="round"
                                                    transform="rotate(-90 50 50)"
                                                    stroke-dasharray="283"
                                                    stroke-dashoffset="283"/>
                                            <text id="centerTimeText" x="50" y="55" text-anchor="middle"
                                                  fill="#2e7d32" font-size="14" font-weight="bold" class="hidden">
                                                25
                                            </text>
                                        </svg>

                                        <button onclick="discard()"
                                                class="absolute top-2 right-2 w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-colors">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 记录专注内容 -->
                            <div id="recordPomo" class="space-y-4 hidden">
                                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                        <div>
                                            <div class="font-medium text-green-800">专注完成！</div>
                                            <div class="text-sm text-green-700">记录您的成果吧</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span id="focus_start_time_show"></span>
                                        <span class="text-gray-400">→</span>
                                        <span id="focus_end_time_show"></span>
                                    </div>

                                    <input type="text"
                                           name="name"
                                           id="focus_name"
                                           value=""
                                           placeholder="记录刚完成的专注内容（点击任务名快速添加）..."
                                           class="input w-full">

                                    <div class="flex gap-2">
                                        <button onclick="discard()"
                                                class="btn btn-outline flex-1">
                                            <i class="fas fa-times mr-2"></i>放弃记录
                                        </button>
                                        <button onclick="savePomoRecord()"
                                                class="btn btn-primary flex-1">
                                            <i class="fas fa-save mr-2"></i>保存记录
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 今日专注统计 -->
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 rounded flex items-center justify-center">
                                        <i class="fas fa-chart-bar text-white text-xs"></i>
                                    </div>
                                    <h3 class="font-medium text-gray-800">今日成果</h3>
                                </div>
                                <div class="text-sm text-gray-500">
                                    已完成 <span id="focusCount" class="font-semibold text-gray-900">0</span> 个专注
                                </div>
                            </div>

                            <!-- 专注列表 -->
                            <div class="space-y-2">
                                <div id="focussLoading" class="text-center py-8">
                                    <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
                                    <p class="text-sm text-gray-500 mt-2">加载专注记录...</p>
                                </div>
                                <ul id="focuss" class="space-y-2 hidden">
                                    <!-- 专注列表动态加载 -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" value="" id="focus_id">
            </div>

            <!-- 右侧：待办事项面板 -->
            <div class="space-y-6" id="taskPanelColumn">
                <!-- 待办事项卡片 -->
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">待办事项</h2>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button id="collapseTaskPanelBtn"
                                        class="text-sm px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition-colors"
                                        title="缩小到右侧">
                                    <i class="fas fa-compress-alt mr-1"></i>缩小
                                </button>
                                <a href="{{ url('tasks') }}"
                                   class="text-sm px-3 py-1 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-list mr-1"></i>列表
                                </a>
                                <a href="{{ url('/taskpriority') }}"
                                   class="text-sm px-3 py-1 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors">
                                    <i class="fas fa-th-large mr-1"></i>四象限
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- 新增任务输入 -->
                        <div class="mb-6">
                            <div class="relative">
                                <input type="text"
                                       name="name"
                                       id="task_name"
                                       placeholder="添加新任务，按回车键保存..."
                                       class="input w-full pl-10"
                                       autocomplete="off">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-plus"></i>
                                </div>
                            </div>
                        </div>

                        <!-- 待办列表 -->
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 rounded flex items-center justify-center">
                                        <i class="fas fa-calendar-day text-white text-xs"></i>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span id="modeName" class="font-medium text-gray-800"></span>
                                        <button id="changeModeBtn" class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition-colors">
                                            切换
                                        </button>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    共 <span id="taskCount" class="font-semibold text-gray-900">0</span> 个任务
                                </div>
                            </div>

                            <!-- 任务列表 -->
                            <div class="space-y-2">
                                <div id="tasksLoading" class="text-center py-8">
                                    <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
                                    <p class="text-sm text-gray-500 mt-2">加载任务列表...</p>
                                </div>
                                <ul id="tasks" class="space-y-2 hidden">
                                    <!-- 任务列表动态加载 -->
                                </ul>

                                <!-- 空状态 -->
                                <div id="tasksEmpty" class="text-center py-8 hidden">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-check-circle text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500">暂无待办任务</p>
                                    <p class="text-sm text-gray-400 mt-1">在上方输入框添加第一个任务</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="taskPanelDock"
         class="hidden fixed right-3 top-1/2 -translate-y-1/2 z-40">
        <button id="restoreTaskPanelBtn"
                class="w-10 h-24 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg flex flex-col items-center justify-center gap-1"
                title="恢复待办面板">
            <i class="fas fa-list-check text-sm"></i>
            <i class="fas fa-chevron-left text-xs"></i>
        </button>
    </div>

    <!-- 原有模态框保留 -->
    @include('components.task-update-modal')
    @include('components.journal-create-modal')

    <div id="taskScheduleModal" class="hidden fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeTaskScheduleModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-xl">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">任务时间设置</h3>
                    <button class="text-gray-400 hover:text-gray-700" onclick="closeTaskScheduleModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">任务</div>
                        <div id="taskScheduleTitle" class="text-sm text-gray-800 break-words"></div>
                    </div>
                    <div>
                        <label for="taskPlannedStartInput" class="text-sm text-gray-700">预计开始时间</label>
                        <input id="taskPlannedStartInput" type="datetime-local" class="input w-full mt-1">
                    </div>
                    <div>
                        <label for="taskPlannedEndInput" class="text-sm text-gray-700">预计结束时间</label>
                        <input id="taskPlannedEndInput" type="datetime-local" class="input w-full mt-1">
                    </div>
                    <div>
                        <label for="taskRemindInput" class="text-sm text-gray-700">提醒时间</label>
                        <input id="taskRemindInput" type="datetime-local" class="input w-full mt-1">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button class="btn btn-outline" onclick="closeTaskScheduleModal()">取消</button>
                    <button class="btn btn-primary" onclick="saveTaskSchedule()">
                        <i class="fas fa-save mr-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="reviewModal" class="hidden fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeReviewModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-xl">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 id="reviewModalTitle" class="text-base font-semibold text-gray-900">评分与备注</h3>
                    <button class="text-gray-400 hover:text-gray-700" onclick="closeReviewModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">对象</div>
                        <div id="reviewTargetName" class="text-sm text-gray-800 break-words"></div>
                    </div>
                    <div>
                        <label for="reviewScoreInput" class="text-sm text-gray-700">评分</label>
                        <input type="hidden" id="reviewScoreInput" value="">
                        <div class="mt-2 flex items-center gap-1">
                            <button type="button" class="review-star-btn text-2xl text-gray-300 hover:text-amber-400 transition-colors" data-score="1" onclick="selectReviewScore(1)" title="1分">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="review-star-btn text-2xl text-gray-300 hover:text-amber-400 transition-colors" data-score="2" onclick="selectReviewScore(2)" title="2分">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="review-star-btn text-2xl text-gray-300 hover:text-amber-400 transition-colors" data-score="3" onclick="selectReviewScore(3)" title="3分">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="review-star-btn text-2xl text-gray-300 hover:text-amber-400 transition-colors" data-score="4" onclick="selectReviewScore(4)" title="4分">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="review-star-btn text-2xl text-gray-300 hover:text-amber-400 transition-colors" data-score="5" onclick="selectReviewScore(5)" title="5分">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="text-xs ml-2 px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200" onclick="selectReviewScore(0)">
                                清空
                            </button>
                        </div>
                        <div id="reviewScoreText" class="mt-1 text-xs text-gray-500">暂不评分</div>
                    </div>
                    <div>
                        <label for="reviewNoteInput" class="text-sm text-gray-700">备注</label>
                        <textarea id="reviewNoteInput" rows="4" maxlength="2000" class="input w-full mt-1" placeholder="写一点复盘或备注..."></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button class="btn btn-outline" onclick="closeReviewModal()">取消</button>
                    <button class="btn btn-primary" onclick="saveReview()">
                        <i class="fas fa-save mr-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* 进度条样式 */
        #progressCircle {
            transition: stroke-dashoffset 0.3s ease, stroke 0.3s ease;
        }

        /* 纯净模式切换 */
        #focusTimerContainer {
            transition: opacity 0.3s ease;
        }

        /* 任务和专注项样式 */
        .task-item, .focus-item {
            transition: all 0.2s ease;
        }

        .task-item:hover, .focus-item:hover {
            transform: translateX(2px);
        }

        /* 复选框样式优化 */
        .custom-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid var(--gray-300);
            border-radius: 4px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .custom-checkbox:hover {
            border-color: var(--primary-color);
        }

        .custom-checkbox.checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .custom-checkbox.checked:after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 11px;
            font-weight: bold;
        }

        /* 操作按钮样式 */
        .action-button {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            background: white;
            border: 1px solid var(--gray-200);
            margin: 0 2px;
        }

        /* 操作按钮区域容器 */
        .task-actions {
            display: none; /* 默认隐藏整个操作区域 */
            opacity: 0;
            transition: opacity 0.2s ease;
            margin-top: 8px; /* 与任务内容的间距 */
            padding-top: 8px;
            border-top: 1px dashed var(--gray-200);
        }

        /* 悬停时显示操作按钮区域 */
        .task-item:hover .task-actions,
        .focus-item:hover .task-actions {
            display: flex; /* 显示容器 */
            opacity: 1;
            gap: 4px;
        }

        .execution-strip {
            border: 1px solid var(--gray-200);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        }

        .execution-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(243, 244, 246, 0.9);
            color: var(--gray-700);
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .execution-chip strong {
            font-weight: 700;
        }

        .execution-chip.is-success {
            background: rgba(16, 185, 129, 0.1);
            color: #047857;
        }

        .execution-chip.is-warning {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
        }

        .execution-chip.is-info {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .execution-mini-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            transition: all 0.2s ease;
        }

        .execution-mini-btn:hover {
            transform: translateY(-1px);
            border-color: var(--gray-300);
            color: var(--gray-900);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        }

        .execution-progress-track {
            height: 6px;
            background: var(--gray-100);
            border-radius: 999px;
            overflow: hidden;
        }

        .execution-progress-bar {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #00b894 0%, #38bdf8 100%);
            transition: width 0.3s ease;
        }

        /* 操作按钮悬停效果 */
        .action-button:hover {
            background: var(--gray-50);
            transform: translateY(-1px);
            border-color: var(--gray-300);
        }

        /* 为不同的操作按钮设置不同的悬停颜色 */
        .action-button:hover.text-green-500 {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-color: var(--success-color);
        }
        .action-button:hover.text-yellow-500 {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
            border-color: var(--warning-color);
        }
        .action-button:hover.text-blue-500 {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .action-button:hover.text-red-500 {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-color: var(--danger-color);
        }
        .action-button:hover.text-purple-500 {
            background: rgba(139, 92, 246, 0.1);
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* 任务项悬停时的整体效果 */
        .task-item:hover, .focus-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: var(--primary-color);
        }

        /* 确保任务内容区域足够紧凑 */
        .task-item .flex-1 {
            min-height: auto; /* 移除最小高度 */
        }

        /* 专注项的操作按钮区域 */
        .focus-item .task-actions {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed var(--gray-200);
        }

        /* 状态指示器 */
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-active { background-color: var(--success-color); }
        .status-pending { background-color: var(--warning-color); }
        .status-completed { background-color: var(--gray-400); }

        /* 优先级标签 */
        .priority-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .priority-high { background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
        .priority-medium { background-color: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
        .priority-low { background-color: rgba(156, 163, 175, 0.1); color: var(--gray-500); }

        /* 子任务标识 */
        .child-task {
            position: relative;
        }

        .child-task:before {
            content: "";
            position: absolute;
            left: -24px;
            top: 50%;
            width: 16px;
            height: 2px;
            background: var(--gray-300);
            transform: translateY(-50%);
        }

        #taskPanelDock {
            transition: opacity .2s ease, transform .2s ease;
        }
    </style>
@endsection

@section('scripts')
    <script>
        var INDEX_DEBUG_ENABLED = (function() {
            try {
                if (window.location.search.indexOf('index_debug=1') !== -1) {
                    return true;
                }
                return window.localStorage && window.localStorage.getItem('index_debug') === '1';
            } catch (e) {
                return false;
            }
        })();

        function indexDebug(message, extra) {
            if (window.console && typeof window.console.log === 'function') {
                if (typeof extra !== 'undefined') {
                    console.log('[INDEX_DEBUG]', message, extra);
                } else {
                    console.log('[INDEX_DEBUG]', message);
                }
            }
            if (!INDEX_DEBUG_ENABLED) {
                return;
            }
            var panel = document.getElementById('indexDebugPanel');
            if (!panel) {
                panel = document.createElement('div');
                panel.id = 'indexDebugPanel';
                panel.style.cssText = 'position:fixed;left:8px;right:8px;bottom:8px;z-index:99999;max-height:38vh;overflow:auto;background:rgba(17,24,39,0.9);color:#e5e7eb;padding:8px;border-radius:8px;font:12px/1.4 monospace;';
                document.body.appendChild(panel);
            }
            var line = document.createElement('div');
            line.textContent = '[' + new Date().toLocaleTimeString() + '] ' + message + (typeof extra !== 'undefined' ? (' ' + JSON.stringify(extra)) : '');
            panel.appendChild(line);
            panel.scrollTop = panel.scrollHeight;
        }

        window.addEventListener('error', function(e) {
            indexDebug('window.error', {
                message: e.message,
                file: e.filename,
                line: e.lineno,
                col: e.colno
            });
        });
        window.addEventListener('unhandledrejection', function(e) {
            var reason = e && e.reason ? (e.reason.message || String(e.reason)) : 'unknown';
            indexDebug('unhandledrejection', { reason: reason });
        });

        // 初始化变量
        var apiRequest = function(method, apiPath, payload, fallback) {
            indexDebug('apiRequest call', {
                method: method,
                path: apiPath,
                hasBridge: !!(window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'),
                hasTaskApiFetch: typeof window.taskApiFetch === 'function'
            });
            if (window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function') {
                return window.TaskApiBridge.requestWithFallback(method, apiPath, payload, fallback).then(function(resp) {
                    indexDebug('apiRequest done(bridge)', { path: apiPath, code: resp && resp.code });
                    return resp;
                }).catch(function(err) {
                    indexDebug('apiRequest error(bridge)', { path: apiPath, err: err && err.message ? err.message : String(err) });
                    throw err;
                });
            }

            if (typeof window.taskApiFetch === 'function') {
                var url = '/api/v2' + (apiPath && apiPath.charAt(0) === '/' ? apiPath : ('/' + (apiPath || '')));
                var opts = { method: (method || 'GET').toUpperCase() };
                var data = payload || {};

                if (opts.method === 'GET') {
                    var queryParts = [];
                    for (var key in data) {
                        if (!Object.prototype.hasOwnProperty.call(data, key)) continue;
                        if (data[key] === undefined || data[key] === null) continue;
                        queryParts.push(encodeURIComponent(key) + '=' + encodeURIComponent(String(data[key])));
                    }
                    if (queryParts.length > 0) {
                        url += (url.indexOf('?') >= 0 ? '&' : '?') + queryParts.join('&');
                    }
                } else {
                    opts.headers = { 'Content-Type': 'application/json' };
                    opts.body = JSON.stringify(data);
                }

                return window.taskApiFetch(url, opts).then(function(resp) {
                    indexDebug('apiRequest response(fetch)', { url: url, status: resp && resp.status });
                    return resp.json();
                }).then(function(respJson) {
                    indexDebug('apiRequest done(fetch)', { path: apiPath, code: respJson && respJson.code });
                    return respJson;
                }).catch(function(err) {
                    indexDebug('apiRequest error(fetch)', { path: apiPath, err: err && err.message ? err.message : String(err) });
                    throw err;
                });
            }

            indexDebug('apiRequest unavailable', { path: apiPath });
            return Promise.reject(new Error('API client unavailable'));
        };
        let timer;
        let calibrationTimer;
        let mode = 1;
        const interval = 1000;
        const calibrationInterval = 60000;
        let remain = 0;
        let status = 1;
        const title = '蒙太奇 - 专注效率工具';
        let totalTime = 1500;
        let originalRemain = remain; // 保存原始剩余时间
        let activePomoStartTime = '';
        let activePomoEndTime = '';
        let currentFocusMeta = null;
        let currentDoingTask = null;

        // 添加纯净模式状态
        let pureMode = false;
        let isCreatingTask = false;
        let lastCreatedTaskName = '';
        let lastCreatedTaskAt = 0;
        let isSwitchingDoing = false;
        let isTaskPanelCollapsed = false;
        let currentScheduleTaskId = 0;
        let reviewTargetType = '';
        let reviewTargetId = 0;

        // 设置cookie
        function setCookie(c_name, value, expiredays) {
            const exdate = new Date();
            exdate.setDate(exdate.getDate() + expiredays);
            document.cookie = c_name + "=" + encodeURIComponent(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
        }

        // 获取cookie
        function getCookie(c_name) {
            if (document.cookie.length > 0) {
                let c_start = document.cookie.indexOf(c_name + "=");
                if (c_start != -1) {
                    c_start = c_start + c_name.length + 1;
                    let c_end = document.cookie.indexOf(";", c_start);
                    if (c_end == -1) c_end = document.cookie.length;
                    return decodeURIComponent(document.cookie.substring(c_start, c_end));
                }
            }
            return "";
        }

        // 日期格式化工具
        Date.prototype.format = function (fmt) {
            const o = {
                "M+": this.getMonth() + 1,
                "d+": this.getDate(),
                "h+": this.getHours(),
                "m+": this.getMinutes(),
                "s+": this.getSeconds(),
                "q+": Math.floor((this.getMonth() + 3) / 3),
                "S": this.getMilliseconds()
            };
            if (/(y+)/.test(fmt)) {
                fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
            }
            for (let k in o) {
                if (new RegExp("(" + k + ")").test(fmt)) {
                    fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ?
                        (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
                }
            }
            return fmt;
        }

        // 初始化页面
        document.addEventListener('DOMContentLoaded', function() {
            indexDebug('DOMContentLoaded');
            initializePage();

            // 设置模式
            const savedMode = getCookie("task_mode");
            if (savedMode == "2") {
                mode = 2;
                document.getElementById('modeName').textContent = "生活";
            } else {
                mode = 1;
                document.getElementById('modeName').textContent = "工作";
            }

            // 加载数据
            loadIndexState();
            indexDebug('about to load lists');
            showtasks();
            showfocuss();

            // 启动校准定时器
            calibrationTimer = setInterval(calibratePomoStatus, calibrationInterval);

            const pureModeToggle = document.getElementById('pureModeToggle');
            if (pureModeToggle) {
                pureModeToggle.addEventListener('click', togglePureMode);
            }

            // 绑定键盘事件
            document.addEventListener('keyup', handleKeyPress);

            // 绑定按钮事件
            bindEvents();
        });

        function renderPomoPanel() {
            var startSection = document.getElementById('focusStartSection');
            var timerSection = document.getElementById('focusTimerSection');
            var recordSection = document.getElementById('recordPomo');

            if (startSection) startSection.classList.add('hidden');
            if (timerSection) timerSection.classList.add('hidden');
            if (recordSection) recordSection.classList.add('hidden');

            if (status === 1) {
                if (startSection) startSection.classList.remove('hidden');
            } else if (status === 2 || status === 4) {
                if (timerSection) timerSection.classList.remove('hidden');
            } else if (status === 3) {
                if (recordSection) recordSection.classList.remove('hidden');
                if (activePomoStartTime) showPomoTime('focus_start_time_show', activePomoStartTime);
                if (activePomoEndTime) showPomoTime('focus_end_time_show', activePomoEndTime);
            }
            renderExecutionStrip();
        }

        function initializePage() {
            if (typeof Notification !== 'undefined' && Notification.permission !== "granted") {
                Notification.requestPermission();
            }

            renderPomoPanel();

            if (status == 2 || status == 4) {
                startPomoTimer();
                updateProgressCircle();
                updateDisplay();
            }
        }

        function applyPomoState(result) {
            const data = result || {};
            currentFocusMeta = data.active_focus || null;
            status = Number(data.current_focus_status || 1);
            remain = Number(data.current_focus_remain || 0);
            originalRemain = remain;
            totalTime = status === 2 ? 1500 : (status === 4 ? 300 : 1500);
            activePomoStartTime = data.active_focus && data.active_focus.start_time ? data.active_focus.start_time : '';
            activePomoEndTime = data.active_focus && data.active_focus.end_time ? data.active_focus.end_time : '';

            const focusIdInput = document.getElementById('focus_id');
            if (focusIdInput) {
                focusIdInput.value = (data.active_focus && data.active_focus.id) ? data.active_focus.id : '';
            }

            if (status === 2 || status === 4) {
                startPomoTimer();
            } else {
                clearInterval(timer);
            }
            renderPomoPanel();
            updateDisplay();
        }

        function syncPomoStatus(silent) {
            if (!apiRequest) {
                if (!silent) {
                    showNotification('error', 'API客户端未初始化');
                }
                return Promise.resolve();
            }
            return apiRequest('GET', '/focuss/status', {}).then(function(response) {
                if (!response || response.code != 9999 || !response.result) {
                    throw new Error('status_failed');
                }
                applyPomoState(response.result);
                if (!silent) {
                    showfocuss();
                }
            }).catch(function() {
                if (!silent) {
                    showNotification('error', '同步专注状态失败');
                }
            });
        }

        function loadIndexState() {
            if (!apiRequest) {
                indexDebug('loadIndexState skipped: apiRequest missing');
                return;
            }

            indexDebug('loadIndexState start');
            apiRequest('GET', '/index', {}).then(function(response) {
                if (response && response.code === 9999 && response.result) {
                    applyPomoState(response.result);
                    indexDebug('loadIndexState success', { status: status, remain: remain });
                }
            }).catch(function() {
                indexDebug('loadIndexState failed');
            });
        }

        function showPomoTime(elementId, timeString) {
            const time = new Date(timeString);
            document.getElementById(elementId).textContent = time.format("hh:mm");
        }

        function startPomoTimer() {
            clearInterval(timer);
            timer = setInterval(function() {
                updatePomoCountdown();
            }, interval);
        }

        function updatePomoCountdown() {
            if (remain <= 0) {
                clearInterval(timer);
                remain = 0;
                handlePomoComplete();
                return;
            }

            remain--;
            updateDisplay();
        }

        // 更新显示函数
        function updateDisplay() {
            const minute = Math.floor(remain / 60);
            const second = remain - minute * 60;

            const timeText = (minute < 10 ? "0" + minute : minute) + ":" +
                (second < 10 ? "0" + second : second);

            const statusText = status == 2 ? "专注进行中" : "休息时间";

            // 只在非纯净模式下显示时间
            if (!pureMode) {
                document.getElementById('focusTimer').textContent = timeText;
                document.getElementById('focusStatus').textContent = statusText;
                document.getElementById('centerTimeText').classList.add('hidden');
            } else {
                // 纯净模式下显示剩余分钟在中心
                const centerText = document.getElementById('centerTimeText');
                centerText.classList.remove('hidden');
                const displayMinutes = Math.ceil(remain / 60);
                centerText.textContent = displayMinutes > 0 ? displayMinutes : "✓";

                // 根据剩余时间改变颜色
                if (displayMinutes > 5) {
                    centerText.setAttribute('fill', '#2e7d32'); // 深绿
                } else if (displayMinutes > 1) {
                    centerText.setAttribute('fill', '#f57c00'); // 橙色
                } else {
                    centerText.setAttribute('fill', '#d32f2f'); // 红色
                }
            }

            document.title = `${statusText} ${timeText} - ${title}`;

            // 更新进度圈
            updateProgressCircle();
            renderExecutionStrip();
        }

        // 更新进度条显示的函数
        function updateProgressCircle() {
            const progressCircle = document.getElementById('progressCircle');

            if (progressCircle) {
                // 计算进度 (283是周长)
                const progress = ((totalTime - remain) / totalTime) * 283;
                progressCircle.style.strokeDashoffset = 283 - progress;

                // 根据进度改变进度条颜色
                const progressPercentage = ((totalTime - remain) / totalTime) * 100;

                if (progressPercentage >= 75) {
                    progressCircle.setAttribute('stroke', '#00b894'); // 绿色
                } else if (progressPercentage >= 50) {
                    progressCircle.setAttribute('stroke', '#00b894'); // 橙色
                } else if (progressPercentage >= 25) {
                    progressCircle.setAttribute('stroke', '#00b894'); // 橙色
                } else {
                    progressCircle.setAttribute('stroke', '#00b894'); // 红色
                }
            }
        }

        // 切换纯净模式
        function togglePureMode() {
            pureMode = !pureMode;
            const toggleBtn = document.getElementById('pureModeToggle');
            const timerContainer = document.getElementById('focusTimerContainer');

            if (pureMode) {
                // 切换到纯净模式
                if (timerContainer) {
                    timerContainer.classList.add('opacity-0');
                }

                toggleBtn.innerHTML = '<i class="fas fa-eye mr-1"></i>显示时间';
                toggleBtn.className = 'text-sm px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors';
            } else {
                // 切换到正常模式
                if (timerContainer) {
                    timerContainer.classList.remove('opacity-0');
                }

                toggleBtn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>纯净模式';
                toggleBtn.className = 'text-sm px-3 py-1 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors';
            }

            // 立即更新一次显示
            updateDisplay();
        }

        function handlePomoComplete() {
            if (status == 2) {
                // 专注完成
                document.title = '专注完成！快来记录一下吧 - ' + title;
                notify('您已经完成了一个专注，快来记录一下吧~');
                syncPomoStatus(true);
            } else if (status == 4) {
                // 休息完成
                document.title = '休息完成，快来开始下一个专注吧 - ' + title;
                notify('休息完成，快来开始下一个专注吧~');
                syncPomoStatus(true);
            }
        }

        // 浏览器通知
        function notify(message) {
            if (typeof Notification === 'undefined') {
                return;
            }
            if (Notification.permission !== "granted") {
                Notification.requestPermission();
            } else {
                const notification = new Notification('蒙太奇', {
                    icon: '/favicon.ico',
                    body: message,
                });

                notification.onclick = function () {
                    window.location.href = "/index";
                };
            }
        }

        // 放弃专注
        function discard() {
            if (confirm("确认要放弃当前专注/休息吗？")) {
                const focusId = document.getElementById('focus_id').value;
                if (!focusId) {
                    showNotification('warning', '当前没有可放弃的专注');
                    return;
                }
                if (!apiRequest) {
                    showNotification('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('POST', '/focuss/discard/' + focusId, {}).then(function(response) {
                    if (response.code == 9999) {
                        showNotification('success', '已放弃当前专注');
                        syncPomoStatus(true).then(function() {
                            showfocuss();
                        });
                    } else {
                        showNotification('error', response.msg || '放弃失败');
                    }
                }).catch(function() {
                    showNotification('error', '请求失败，请稍后重试');
                });
            }
        }

        // 校准专注状态
        function calibratePomoStatus() {
            syncPomoStatus(true);
        }

        // 保存专注记录
        function savePomoRecord() {
            const focusName = document.getElementById('focus_name').value.trim();
            const focusId = document.getElementById('focus_id').value;

            if (focusName) {
                if (!apiRequest) {
                    showNotification('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('POST', '/focuss/' + focusId, { name: focusName }).then(function(response) {
                        if (response.code == 9999) {
                            showNotification('success', '专注记录已保存');
                            document.getElementById('focus_name').value = '';
                            syncPomoStatus(true).then(function() {
                                showfocuss();
                            });
                        } else {
                            showNotification('error', '保存失败');
                        }
                    }).catch(function() {
                        showNotification('error', '请求失败，请稍后重试');
                    });
            } else {
                showNotification('warning', '请输入专注内容');
            }
        }

        // 创建专注列表项
        function createPomoListItem(focusData) {
            const startTime = formatTime(new Date(focusData.start_time));
            const endTime = formatTime(new Date(focusData.end_time));
            const fullName = escapeHtml((focusData.name || '未命名专注'));
            const ratingHtml = renderRatingStars(focusData.rating);
            const reviewNote = escapeHtml(focusData.review_note || '');

            return `
    <li id="focus${focusData.id}" class="focus-item bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800 truncate" title="${fullName}">${fullName}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500">${startTime} - ${endTime}</span>
                        ${ratingHtml}
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                <button class="action-button text-gray-400 hover:text-amber-500"
                        onclick='openReviewModal("focus", ${focusData.id}, ${JSON.stringify(String(focusData.name || '未命名专注'))}, ${focusData.rating || 'null'}, ${JSON.stringify(String(focusData.review_note || ''))})'
                        title="评分备注">
                    <i class="fas fa-star-half-alt"></i>
                </button>
                <a href="/notes?source_type=1&source_id=${focusData.id}"
                   class="action-button text-gray-400 hover:text-blue-500"
                   target="_blank"
                   title="记录想法">
                    <i class="fas fa-sticky-note"></i>
                </a>
            </div>
        </div>
        ${reviewNote ? `<div class="mt-2 text-xs text-gray-500 break-words">${reviewNote}</div>` : ''}
    </li>
    `;
        }

        // 创建任务列表项
        function createTaskListItem(data, listType) {
            const isChild = data.parent_task_id !== null;
            const isTop = data.is_top == 1;
            const isCompleted = data.status == 3;
            const isDoing = Number(data.is_doing || 0) === 1;
            const isDoingList = listType === 'doing';
            const fullTaskName = escapeHtml(data.name || '');
            const ratingHtml = renderRatingStars(data.rating);
            const reviewNote = escapeHtml(data.review_note || '');
            const scheduleSummary = [formatDateTime(data.planned_start_time), formatDateTime(data.planned_end_time)].filter(Boolean).join(' ~ ');
            const remindSummary = formatDateTime(data.remindtime);

            return `
    <li id="task${data.id}" class="task-item bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors ${isChild ? 'child-task ml-8' : ''}">
        <div class="flex items-start gap-3">
            <!-- 复选框 -->
            <div class="flex-shrink-0">
                <div class="custom-checkbox ${isCompleted ? 'checked' : ''}"
                     data-task-id="${data.id}"
                     onclick="toggleTaskStatus(${data.id}, this)">
                </div>
            </div>

            <!-- 任务内容 -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    ${isDoing ? '<span class="priority-badge bg-emerald-100 text-emerald-700">正在做</span>' : ''}
                    ${isTop ? '<span class="priority-badge priority-high">置顶</span>' : ''}
                    ${ratingHtml}
                    <span class="font-medium text-gray-800 truncate ${isCompleted ? 'line-through text-gray-400' : ''}" title="${fullTaskName}">
                        ${fullTaskName}
                    </span>
                </div>
                ${(scheduleSummary || remindSummary) ? `<div class="text-xs text-gray-500 break-words">${scheduleSummary ? `<span><i class="far fa-clock mr-1"></i>${scheduleSummary}</span>` : ''}${(scheduleSummary && remindSummary) ? '<span class="mx-2">|</span>' : ''}${remindSummary ? `<span><i class="far fa-bell mr-1"></i>${remindSummary}</span>` : ''}</div>` : ''}
                ${reviewNote ? `<div class="text-xs text-gray-500 mt-1 break-words">${reviewNote}</div>` : ''}
            </div>
        </div>

        <!-- 操作按钮区域 - 保持在下方，但默认隐藏 -->
        <div class="task-actions">
            ${data.parent_task_id === null ? `
                <button class="action-button text-gray-400 hover:text-green-500"
                        onclick='addChildTask(${data.id}, ${JSON.stringify(String(data.name || ''))})'
                        title="添加子任务">
                    <i class="fas fa-plus-circle"></i>
                </button>
            ` : ''}

            <button class="action-button text-gray-400 hover:text-yellow-500"
                    onclick="toggleTaskTop(${data.id}, ${isTop})"
                    title="${isTop ? '取消置顶' : '置顶'}">
                <i class="fas fa-thumbtack ${isTop ? 'text-yellow-500' : ''}"></i>
            </button>

            <button class="action-button text-gray-400 hover:text-emerald-500"
                    onclick="toggleTaskDoing(${data.id}, ${isDoing ? 1 : 0})"
                    title="${isDoing ? '取消正在做' : '设为正在做'}">
                <i class="fas fa-bolt ${isDoing || isDoingList ? 'text-emerald-500' : ''}"></i>
            </button>

            <button class="action-button text-gray-400 hover:text-blue-500"
                    onclick="editTask(${data.id})"
                    title="编辑">
                <i class="fas fa-edit"></i>
            </button>

            <button class="action-button text-gray-400 hover:text-indigo-500"
                    onclick="openTaskScheduleModal(${data.id})"
                    title="时间设置">
                <i class="far fa-clock"></i>
            </button>

            <button class="action-button text-gray-400 hover:text-amber-500"
                    onclick='openReviewModal("task", ${data.id}, ${JSON.stringify(String(data.name || ''))}, ${data.rating || 'null'}, ${JSON.stringify(String(data.review_note || ''))})'
                    title="评分备注">
                <i class="fas fa-star-half-alt"></i>
            </button>

            <button class="action-button text-gray-400 hover:text-red-500"
                    onclick="deleteTask(${data.id})"
                    title="删除">
                <i class="fas fa-trash-alt"></i>
            </button>

            <a href="/notes?source_type=3&source_id=${data.id}"
               class="action-button text-gray-400 hover:text-purple-500"
               target="_blank"
               title="记录想法">
                <i class="fas fa-sticky-note"></i>
            </a>
        </div>
    </li>
    `;
        }

        function createDoingTaskCard(task) {
            const taskName = escapeHtml(task.name || '未命名任务');
            const ratingHtml = renderRatingStars(task.rating);
            return `
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 task-content" data-task-name="${taskName}">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] uppercase tracking-wide text-emerald-700 mb-0.5">正在做</div>
                    <div class="font-medium text-gray-900 truncate">${taskName}</div>
                    ${ratingHtml ? `<div class="mt-1">${ratingHtml}</div>` : ''}
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button class="action-button text-gray-400 hover:text-green-500"
                        onclick="finishTask(${task.id})"
                        title="完成任务">
                    <i class="fas fa-check-circle"></i>
                </button>
                <button class="action-button text-gray-400 hover:text-emerald-500"
                        onclick="toggleTaskDoing(${task.id}, 1)"
                        title="取消正在做">
                    <i class="fas fa-bolt text-emerald-500"></i>
                </button>
                <button class="action-button text-gray-400 hover:text-blue-500"
                        onclick="editTask(${task.id})"
                        title="编辑">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-button text-gray-400 hover:text-indigo-500"
                        onclick="openTaskScheduleModal(${task.id})"
                        title="时间设置">
                    <i class="far fa-clock"></i>
                </button>
                <button class="action-button text-gray-400 hover:text-amber-500"
                        onclick='openReviewModal("task", ${task.id}, ${JSON.stringify(String(task.name || ''))}, ${task.rating || 'null'}, ${JSON.stringify(String(task.review_note || ''))})'
                        title="评分备注">
                    <i class="fas fa-star-half-alt"></i>
                </button>
                <button class="action-button text-gray-400 hover:text-red-500"
                        onclick="deleteTask(${task.id})"
                        title="删除">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>
    `;
        }

        function getExecutionStatusMeta() {
            if (status === 2) {
                return {
                    label: '专注进行中',
                    hint: '当前专注正在运行，结束后可直接记录。',
                    chipClass: 'is-success',
                    icon: 'fa-circle-notch',
                    primaryAction: '刷新状态',
                    primaryIcon: 'fa-rotate'
                };
            }
            if (status === 3) {
                return {
                    label: '专注待记录',
                    hint: '本次专注已完成，建议尽快补一条记录。',
                    chipClass: 'is-warning',
                    icon: 'fa-clipboard-check',
                    primaryAction: '记录',
                    primaryIcon: 'fa-save'
                };
            }
            if (status === 4) {
                return {
                    label: '休息时间',
                    hint: '休息结束后继续下一个专注。',
                    chipClass: 'is-info',
                    icon: 'fa-mug-hot',
                    primaryAction: '刷新状态',
                    primaryIcon: 'fa-rotate'
                };
            }
            return {
                label: '待开始',
                hint: currentDoingTask ? '你有一个正在做的任务，可以直接开启番茄钟。' : '先添加任务，再开始专注。',
                chipClass: '',
                icon: 'fa-play',
                primaryAction: '开始',
                primaryIcon: 'fa-play'
            };
        }

        function renderExecutionStrip() {
            const strip = document.getElementById('executionStrip');
            if (!strip) {
                return;
            }

            const focusId = currentFocusMeta && currentFocusMeta.id ? Number(currentFocusMeta.id) : 0;
            const focusName = currentFocusMeta && currentFocusMeta.name ? String(currentFocusMeta.name) : '';
            const taskId = currentDoingTask && currentDoingTask.id ? Number(currentDoingTask.id) : 0;
            const taskName = currentDoingTask && currentDoingTask.name ? String(currentDoingTask.name) : '';
            const meta = getExecutionStatusMeta();
            const progress = (status === 2 || status === 4) && totalTime > 0 ? Math.max(0, Math.min(100, Math.round(((totalTime - remain) / totalTime) * 100))) : (status === 3 ? 100 : 0);
            const primaryHandler = status === 1 ? 'startPomo()' : (status === 3 ? 'savePomoRecord()' : 'syncPomoStatus(false)');
            const focusNotesUrl = focusId ? '/notes?source_type=1&source_id=' + focusId : '';
            const taskNotesUrl = taskId ? '/notes?source_type=3&source_id=' + taskId : '';
            const taskReviewBtn = taskId ? `<button type="button" class="execution-mini-btn" onclick='openReviewModal("task", ${taskId}, ${JSON.stringify(taskName || "未命名任务")}, ${currentDoingTask.rating || "null"}, ${JSON.stringify(String(currentDoingTask.review_note || ""))})' title="评分备注"><i class="fas fa-star-half-alt"></i></button>` : '';
            const taskEditBtn = taskId ? `<button type="button" class="execution-mini-btn" onclick="editTask(${taskId})" title="编辑任务"><i class="fas fa-pen"></i></button>` : '';
            const focusLabel = focusName ? escapeHtml(focusName) : '';
            const taskLabel = taskName ? escapeHtml(taskName) : '';
            const hasFocus = !!focusId;
            const hasTask = !!taskId;

            strip.className = 'mb-6 execution-strip';
            strip.innerHTML = `
                <div class="px-4 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 flex items-start gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fas ${meta.icon}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="execution-chip ${meta.chipClass}">
                                        <strong>${meta.label}</strong>
                                    </span>
                                    ${hasFocus ? `<span class="execution-chip is-info"><i class="fas fa-bullseye"></i>${focusLabel || '当前专注'}</span>` : ''}
                                    ${hasTask ? `<span class="execution-chip"><i class="fas fa-list-check"></i>${taskLabel}</span>` : ''}
                                </div>
                                <div class="mt-2 text-sm text-gray-500">${escapeHtml(meta.hint)}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap lg:justify-end">
                            <button type="button" class="execution-mini-btn" onclick="${primaryHandler}" title="${meta.primaryAction}">
                                <i class="fas ${meta.primaryIcon}"></i>
                            </button>
                            ${hasFocus ? `<a class="execution-mini-btn" href="${focusNotesUrl}" target="_blank" title="专注笔记"><i class="fas fa-sticky-note"></i></a>` : ''}
                            ${hasFocus ? `<a class="execution-mini-btn" href="/focuss" title="专注历史"><i class="fas fa-clock-rotate-left"></i></a>` : ''}
                            ${hasTask ? `<a class="execution-mini-btn" href="${taskNotesUrl}" target="_blank" title="任务笔记"><i class="fas fa-bookmark"></i></a>` : ''}
                            ${taskEditBtn}
                            ${taskReviewBtn}
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <div class="execution-progress-track">
                        <div class="execution-progress-bar" style="width: ${progress}%"></div>
                    </div>
                </div>
            `;
            strip.classList.toggle('hidden', !(hasFocus || hasTask || status !== 1));
        }

        function updateTaskDoing(taskId, targetDoing) {
            return apiRequest('PUT', '/tasks/' + taskId, {
                is_doing: targetDoing ? 1 : 0
            });
        }

        function getTaskDetail(taskId) {
            if (!apiRequest) {
                return Promise.resolve(null);
            }
            return apiRequest('GET', '/tasks/' + taskId, {}).then(function(response) {
                if (response && response.code == 9999) {
                    return response.result;
                }
                return null;
            }).catch(function() {
                return null;
            });
        }

        function showTaskCompletionTip(taskData) {
            if (!taskData || !taskData.id) {
                return;
            }

            const oldTip = document.getElementById('taskCompleteTip');
            if (oldTip) {
                oldTip.remove();
            }

            const taskName = escapeHtml(taskData.name || '未命名任务');
            const tip = document.createElement('div');
            tip.id = 'taskCompleteTip';
            tip.className = 'fixed top-4 right-4 z-50 max-w-sm';
            tip.innerHTML = `
                <div class="card bg-green-500 text-white shadow-xl">
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-lg mt-0.5"></i>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium">任务已完成</div>
                                <div class="text-sm opacity-90 mt-1 break-words">${taskName}</div>
                                <button class="mt-3 px-3 py-1.5 rounded bg-white text-green-700 text-sm hover:bg-green-50 transition-colors review-task-btn">
                                    去评分与备注
                                </button>
                            </div>
                            <button class="text-white hover:text-green-100 close-task-tip">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(tip);

            tip.querySelector('.close-task-tip').addEventListener('click', function() {
                tip.remove();
            });

            tip.querySelector('.review-task-btn').addEventListener('click', function() {
                openReviewModal('task', taskData.id, taskData.name || '未命名任务', taskData.rating || null, taskData.review_note || '');
                tip.remove();
            });

            setTimeout(function() {
                if (tip.parentNode) {
                    tip.style.opacity = '0';
                    setTimeout(function() {
                        if (tip.parentNode) {
                            tip.parentNode.removeChild(tip);
                        }
                    }, 300);
                }
            }, 12000);
        }

        function finishTask(taskId) {
            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }

            let taskSnapshot = null;
            getTaskDetail(taskId).then(function(task) {
                taskSnapshot = task;
                return apiRequest('DELETE', '/tasks/' + taskId, {
                    type: 'finish'
                });
            }).then(function(response) {
                if (response.code == 9999) {
                    showtasks();
                    showNotification('success', '任务已完成');
                    showTaskCompletionTip(taskSnapshot || { id: taskId, name: '任务' });
                } else {
                    showNotification('error', response.msg || '操作失败');
                }
            }).catch(function() {
                showNotification('error', '请求失败');
            });
        }

        function toggleTaskStatus(taskId, element) {
            const isCurrentlyCompleted = element.classList.contains('checked');
            const action = isCurrentlyCompleted ? 'restore' : 'finish';
            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }
            if (action === 'finish') {
                let taskSnapshot = null;
                getTaskDetail(taskId).then(function(task) {
                    taskSnapshot = task;
                    return apiRequest('DELETE', '/tasks/' + taskId, {
                        type: action
                    });
                }).then(function(response) {
                    if (response.code == 9999) {
                        showtasks();
                        showNotification('success', '任务已完成');
                        showTaskCompletionTip(taskSnapshot || { id: taskId, name: '任务' });
                    } else {
                        showNotification('error', response.msg || '操作失败');
                    }
                }).catch(function() {
                    showNotification('error', '请求失败');
                });
                return;
            }

            apiRequest('DELETE', '/tasks/' + taskId, { type: action }).then(function(response) {
                if (response.code == 9999) {
                    showtasks();
                    showNotification('success', '任务已恢复');
                } else {
                    showNotification('error', '操作失败');
                }
            }).catch(function() {
                showNotification('error', '请求失败');
            });
        }

        function deleteTask(taskId) {
            if (confirm('确定要删除这个任务吗？此操作不可恢复。')) {
                if (!apiRequest) {
                    showNotification('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('DELETE', '/tasks/' + taskId, {
                    type: 'delete'
                }).then(function(response) {
                        if (response.code == 9999) {
                            showtasks();
                            showNotification('success', '任务删除成功');
                        } else {
                            showNotification('error', '删除失败');
                        }
                    }).catch(function() {
                        showNotification('error', '删除失败');
                    });
            }
        }

        function toggleTaskTop(taskId, isTop) {
            const newStatus = isTop ? 0 : 1;
            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }
            apiRequest('PUT', '/tasks/' + taskId, {
                is_top: newStatus
            }).then(function(response) {
                    if (response.code == 9999) {
                        showtasks();
                    } else {
                        showNotification('error', '操作失败');
                    }
                }).catch(function() {
                    showNotification('error', '请求失败');
                });
        }

        function toggleTaskDoing(taskId, isDoing) {
            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }
            if (isSwitchingDoing) {
                return;
            }
            isSwitchingDoing = true;

            if (isDoing) {
                updateTaskDoing(taskId, 0).then(function(response) {
                    if (response.code == 9999) {
                        showtasks();
                        showNotification('success', '已取消正在做');
                    } else {
                        showNotification('error', response.msg || '操作失败');
                    }
                }).catch(function() {
                    showNotification('error', '请求失败');
                }).finally(function() {
                    isSwitchingDoing = false;
                });
                return;
            }

            apiRequest('GET', '/tasks/all', {
                status: 1,
                mode: mode
            }).then(function(listResp) {
                if (!listResp || listResp.code != 9999) {
                    throw new Error('load_tasks_failed');
                }
                const list = Array.isArray(listResp.result) ? listResp.result : Object.keys(listResp.result || {}).map(function(k) {
                    return listResp.result[k];
                });
                const existingDoing = list.filter(function(task) {
                    return Number(task.is_doing || 0) === 1 && Number(task.status || 1) === 1 && Number(task.id) !== Number(taskId);
                });

                if (existingDoing.length > 0) {
                    const oldTask = existingDoing[0];
                    const oldName = String(oldTask.name || '未命名任务');
                    const shouldReplace = confirm('当前正在做："' + oldName + '"，是否替换为新任务？');
                    if (!shouldReplace) {
                        throw new Error('replace_cancelled');
                    }
                }

                let chain = Promise.resolve();
                existingDoing.forEach(function(task) {
                    chain = chain.then(function() {
                        return updateTaskDoing(task.id, 0).then(function(resp) {
                            if (!resp || resp.code != 9999) {
                                throw new Error('clear_doing_failed');
                            }
                        });
                    });
                });

                return chain.then(function() {
                    return updateTaskDoing(taskId, 1);
                }).then(function(setResp) {
                    if (!setResp || setResp.code != 9999) {
                        throw new Error('set_doing_failed');
                    }
                    showtasks();
                    showNotification('success', existingDoing.length > 0 ? '已替换正在做任务' : '已设为正在做');
                });
            }).catch(function(err) {
                if (err && err.message === 'replace_cancelled') {
                    return;
                }
                showNotification('error', '操作失败，请稍后重试');
            }).finally(function() {
                isSwitchingDoing = false;
            });
        }

        function addChildTask(taskId, taskName) {
            const childName = prompt(`为"${taskName}"创建子任务：`, "");
            if (childName && childName.trim()) {
                if (!apiRequest) {
                    showNotification('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('POST', '/tasks', {
                    name: childName.trim(),
                    mode: mode,
                    parent_task_id: taskId
                }).then(function(response) {
                        if (response.code == 9999) {
                            showtasks();
                            showNotification('success', '子任务添加成功');
                        } else {
                            showNotification('error', '添加失败');
                        }
                    }).catch(function() {
                        showNotification('error', '请求失败');
                    });
            }
        }

        // 为首页加载任务数据并打开编辑弹窗的函数
        function editTask(taskId) {
            // 从服务器获取任务数据 - 使用现有的API
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('GET', '/tasks/' + taskId, {}).then(function(response) {
                if(response.code == 9999) {
                    var task = response.result;
                    openTaskUpdateModal(task);
                } else {
                    alert('获取任务数据失败：' + (response.msg || '未知错误'));
                }
            }).catch(function() {
                alert('获取任务数据失败');
            });
        }

        // 显示待办列表
        function showtasks() {
            indexDebug('showtasks start');
            const tasksList = document.getElementById('tasks');
            const doingCard = document.getElementById('doingTaskCard');
            const loading = document.getElementById('tasksLoading');
            const empty = document.getElementById('tasksEmpty');
            function finishLoading() {
                if (loading) loading.classList.add('hidden');
            }
            function toList(data) {
                if (!data) return [];
                if (Array.isArray(data)) return data;
                const list = [];
                for (var k in data) {
                    if (Object.prototype.hasOwnProperty.call(data, k)) {
                        list.push(data[k]);
                    }
                }
                return list;
            }

            if (!apiRequest) {
                finishLoading();
                if (tasksList) tasksList.classList.add('hidden');
                if (empty) empty.classList.remove('hidden');
                return;
            }
            apiRequest('GET', '/tasks/all', {
                status: 1,
                mode: mode
                }).then(function(response) {
                    if (response && response.code == 9999) {
                        indexDebug('showtasks success', { size: (response.result && response.result.length) ? response.result.length : 'obj' });
                        const list = toList(response.result);
                        const doingTasksRaw = list.filter(function(task) {
                            return Number(task.is_doing || 0) === 1 && Number(task.status || 1) === 1;
                        });
                        const primaryDoingTask = doingTasksRaw.length > 0 ? doingTasksRaw[0] : null;
                        currentDoingTask = primaryDoingTask;
                        const normalTasks = list.filter(function(task) {
                            if (!primaryDoingTask) {
                                return true;
                            }
                            return Number(task.id) !== Number(primaryDoingTask.id);
                        });

                        if (tasksList) tasksList.innerHTML = '';
                        if (doingCard) {
                            doingCard.innerHTML = '';
                            doingCard.classList.add('hidden');
                        }

                        if (doingCard && primaryDoingTask) {
                            doingCard.innerHTML = createDoingTaskCard(primaryDoingTask);
                            doingCard.classList.remove('hidden');
                        }

                        if (tasksList && normalTasks.length > 0) {
                            normalTasks.forEach(function(data) {
                                tasksList.insertAdjacentHTML('beforeend', createTaskListItem(data));
                            });
                            tasksList.classList.remove('hidden');
                            if (empty) empty.classList.add('hidden');
                        } else {
                            if (tasksList) tasksList.classList.add('hidden');
                            if (empty) {
                                if (primaryDoingTask) {
                                    empty.classList.add('hidden');
                                } else {
                                    empty.classList.remove('hidden');
                                }
                            }
                        }
                    } else {
                        indexDebug('showtasks non-9999', { code: response && response.code, msg: response && response.msg });
                        currentDoingTask = null;
                        if (doingCard) {
                            doingCard.innerHTML = '';
                            doingCard.classList.add('hidden');
                        }
                        if (tasksList) tasksList.classList.add('hidden');
                        if (empty) empty.classList.remove('hidden');
                    }
                }).catch(function() {
                    indexDebug('showtasks failed');
                    currentDoingTask = null;
                    if (doingCard) {
                        doingCard.innerHTML = '';
                        doingCard.classList.add('hidden');
                    }
                    if (tasksList) tasksList.classList.add('hidden');
                    if (empty) empty.classList.remove('hidden');
                }).finally(function() {
                    finishLoading();
                    updateTaskCount();
                    renderExecutionStrip();
                });
        }

        // 显示专注列表
        function showfocuss() {
            indexDebug('showfocuss start');
            const focussList = document.getElementById('focuss');
            const loading = document.getElementById('focussLoading');
            function finishLoading() {
                if (loading) loading.classList.add('hidden');
            }
            function toList(data) {
                if (!data) return [];
                if (Array.isArray(data)) return data;
                const list = [];
                for (var k in data) {
                    if (Object.prototype.hasOwnProperty.call(data, k)) {
                        list.push(data[k]);
                    }
                }
                return list;
            }

            if (!apiRequest) {
                finishLoading();
                if (focussList) focussList.classList.remove('hidden');
                return;
            }
            apiRequest('GET', '/focuss/today', {
                type: 'time'
            }).then(function(response) {
                    if (response && response.code == 9999) {
                        indexDebug('showfocuss success', { size: (response.result && response.result.length) ? response.result.length : 'obj' });
                        const list = toList(response.result);
                        if (focussList) focussList.innerHTML = '';
                        if (focussList && list.length > 0) {
                            list.forEach(function(data) {
                                focussList.insertAdjacentHTML('beforeend', createPomoListItem(data));
                            });
                        }
                        if (focussList) focussList.classList.remove('hidden');
                        document.getElementById('focusCount').textContent = list.length;
                    } else {
                        indexDebug('showfocuss non-9999', { code: response && response.code, msg: response && response.msg });
                        if (focussList) focussList.classList.remove('hidden');
                        document.getElementById('focusCount').textContent = '0';
                    }
                }).catch(function() {
                    indexDebug('showfocuss failed');
                    if (focussList) focussList.classList.remove('hidden');
                    document.getElementById('focusCount').textContent = '0';
                }).finally(function() {
                    finishLoading();
                });
        }

        // 添加新任务
        function addNewTask(taskName) {
            const normalizedName = (taskName || '').trim();
            if (!normalizedName) {
                return;
            }

            const now = Date.now();
            if (isCreatingTask) {
                return;
            }
            if (lastCreatedTaskName === normalizedName && (now - lastCreatedTaskAt) < 1200) {
                return;
            }

            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }
            isCreatingTask = true;
            lastCreatedTaskName = normalizedName;
            lastCreatedTaskAt = now;

            apiRequest('POST', '/tasks', {
                name: normalizedName,
                mode: mode
            }).then(function(response) {
                    if (response.code == 9999) {
                        document.getElementById('task_name').value = '';
                        showtasks();
                        showNotification('success', '任务添加成功');
                    } else {
                        showNotification('error', '添加失败');
                    }
                }).catch(function() {
                    showNotification('error', '请求失败');
                }).finally(function() {
                    isCreatingTask = false;
                });
        }

        // 开始专注
        function startPomo() {
            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }
            apiRequest('POST', '/focuss/start', {}).then(function(response) {
                    if (response.code != 9999) {
                        showNotification('error', response.msg || '启动失败');
                    } else {
                        applyPomoState(response.result || {});
                        showNotification('success', '专注已开始');
                    }
                }).catch(function() {
                    showNotification('error', '启动失败');
                });
        }

        function updateTaskCount() {
            const taskCount =
                document.querySelectorAll('#tasks .task-item').length +
                (document.getElementById('doingTaskCard') && document.getElementById('doingTaskCard').innerHTML.trim() ? 1 : 0);
            document.getElementById('taskCount').textContent = taskCount;
        }

        function formatTime(date) {
            return date.getHours().toString().padStart(2, '0') + ':' +
                date.getMinutes().toString().padStart(2, '0');
        }

        function formatDateTime(value) {
            if (!value) return '';
            const date = new Date(value.replace(' ', 'T'));
            if (isNaN(date.getTime())) return '';
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            const hh = String(date.getHours()).padStart(2, '0');
            const mm = String(date.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${d} ${hh}:${mm}`;
        }

        function toLocalDatetimeValue(value) {
            if (!value) return '';
            const date = new Date(value.replace(' ', 'T'));
            if (isNaN(date.getTime())) return '';
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            const hh = String(date.getHours()).padStart(2, '0');
            const mm = String(date.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${d}T${hh}:${mm}`;
        }

        function toApiDatetimeValue(value) {
            if (!value) return null;
            return value.replace('T', ' ') + ':00';
        }

        function renderRatingStars(score) {
            const rating = Number(score || 0);
            if (!rating) return '';
            return `<span class="text-xs text-amber-500"><i class="fas fa-star mr-1"></i>${rating}分</span>`;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function bindEvents() {
            // 切换模式
            document.getElementById('changeModeBtn').addEventListener('click', function() {
                mode = mode == 2 ? 1 : 2;
                document.getElementById('modeName').textContent = mode == 1 ? "工作" : "生活";
                setCookie("task_mode", mode, 30);
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>切换中';
                showtasks();
                setTimeout(() => {
                    this.innerHTML = '切换';
                }, 1000);
            });

            // 专注按钮点击
            const focusBtn = document.getElementById('focusBtn');
            if (focusBtn) {
                focusBtn.addEventListener('click', function() {
                    if (status == 1) {
                        startPomo();
                    } else if (status == 2 || status == 4) {
                        discard();
                    }
                });
            }

            // 任务输入框回车
            const taskInput = document.getElementById('task_name');
            if (taskInput) {
                taskInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const taskName = this.value.trim();
                        if (taskName) {
                            addNewTask(taskName);
                        }
                    }
                });
            }

            const collapseBtn = document.getElementById('collapseTaskPanelBtn');
            if (collapseBtn) {
                collapseBtn.addEventListener('click', function() {
                    toggleTaskPanel(true);
                });
            }
            const restoreBtn = document.getElementById('restoreTaskPanelBtn');
            if (restoreBtn) {
                restoreBtn.addEventListener('click', function() {
                    toggleTaskPanel(false);
                });
            }
        }

        function toggleTaskPanel(collapse) {
            isTaskPanelCollapsed = !!collapse;
            const column = document.getElementById('taskPanelColumn');
            const dock = document.getElementById('taskPanelDock');
            const grid = document.getElementById('indexMainGrid');
            if (column) {
                column.style.display = isTaskPanelCollapsed ? 'none' : '';
            }
            if (dock) {
                dock.classList.toggle('hidden', !isTaskPanelCollapsed);
            }
            if (grid) {
                grid.classList.toggle('lg:grid-cols-2', !isTaskPanelCollapsed);
                grid.classList.toggle('lg:grid-cols-1', isTaskPanelCollapsed);
            }
            try {
                window.localStorage.setItem('index_task_panel_collapsed', isTaskPanelCollapsed ? '1' : '0');
            } catch (e) {}
        }

        function restoreTaskPanelState() {
            let collapsed = false;
            try {
                collapsed = window.localStorage.getItem('index_task_panel_collapsed') === '1';
            } catch (e) {}
            toggleTaskPanel(collapsed);
        }

        function openTaskScheduleModal(taskId) {
            if (!apiRequest) {
                showNotification('error', 'API客户端未初始化');
                return;
            }
            apiRequest('GET', '/tasks/' + taskId, {}).then(function(response) {
                if (!response || response.code != 9999 || !response.result) {
                    showNotification('error', '加载任务信息失败');
                    return;
                }
                const task = response.result;
                currentScheduleTaskId = Number(task.id || 0);
                document.getElementById('taskScheduleTitle').textContent = task.name || '未命名任务';
                document.getElementById('taskPlannedStartInput').value = toLocalDatetimeValue(task.planned_start_time);
                document.getElementById('taskPlannedEndInput').value = toLocalDatetimeValue(task.planned_end_time);
                document.getElementById('taskRemindInput').value = toLocalDatetimeValue(task.remindtime);
                document.getElementById('taskScheduleModal').classList.remove('hidden');
            }).catch(function() {
                showNotification('error', '加载任务信息失败');
            });
        }

        function closeTaskScheduleModal() {
            currentScheduleTaskId = 0;
            document.getElementById('taskScheduleModal').classList.add('hidden');
        }

        function saveTaskSchedule() {
            if (!currentScheduleTaskId) {
                return;
            }
            const plannedStart = document.getElementById('taskPlannedStartInput').value;
            const plannedEnd = document.getElementById('taskPlannedEndInput').value;
            const remind = document.getElementById('taskRemindInput').value;

            if (plannedStart && plannedEnd && new Date(plannedStart).getTime() > new Date(plannedEnd).getTime()) {
                showNotification('warning', '预计开始时间不能晚于预计结束时间');
                return;
            }

            apiRequest('PUT', '/tasks/' + currentScheduleTaskId, {
                planned_start_time: toApiDatetimeValue(plannedStart),
                planned_end_time: toApiDatetimeValue(plannedEnd),
                remindtime: toApiDatetimeValue(remind)
            }).then(function(response) {
                if (response && response.code == 9999) {
                    closeTaskScheduleModal();
                    showtasks();
                    showNotification('success', '任务时间已更新');
                } else {
                    showNotification('error', (response && response.msg) ? response.msg : '保存失败');
                }
            }).catch(function() {
                showNotification('error', '保存失败，请稍后重试');
            });
        }

        function openReviewModal(type, id, name, rating, note) {
            reviewTargetType = type;
            reviewTargetId = Number(id || 0);
            document.getElementById('reviewModalTitle').textContent = type === 'task' ? '任务评分与备注' : '专注评分与备注';
            document.getElementById('reviewTargetName').textContent = name || (type === 'task' ? '任务' : '专注');
            document.getElementById('reviewScoreInput').value = rating ? String(rating) : '';
            renderReviewScoreUI();
            document.getElementById('reviewNoteInput').value = note || '';
            document.getElementById('reviewModal').classList.remove('hidden');
        }

        function closeReviewModal() {
            reviewTargetType = '';
            reviewTargetId = 0;
            document.getElementById('reviewModal').classList.add('hidden');
        }

        function renderReviewScoreUI() {
            const rawScore = document.getElementById('reviewScoreInput').value;
            const score = Number(rawScore || 0);
            const scoreText = document.getElementById('reviewScoreText');
            const starButtons = document.querySelectorAll('.review-star-btn');

            starButtons.forEach(function(btn) {
                const btnScore = Number(btn.getAttribute('data-score'));
                if (btnScore <= score) {
                    btn.classList.remove('text-gray-300');
                    btn.classList.add('text-amber-400');
                } else {
                    btn.classList.remove('text-amber-400');
                    btn.classList.add('text-gray-300');
                }
            });

            if (scoreText) {
                scoreText.textContent = score > 0 ? ('当前评分：' + score + ' 分') : '暂不评分';
            }
        }

        function selectReviewScore(score) {
            const input = document.getElementById('reviewScoreInput');
            const current = Number(input.value || 0);
            const next = Number(score || 0);
            if (next > 0 && current === next) {
                input.value = '';
            } else {
                input.value = next > 0 ? String(next) : '';
            }
            renderReviewScoreUI();
        }

        function saveReview() {
            if (!reviewTargetType || !reviewTargetId) {
                return;
            }
            const targetType = reviewTargetType;
            const scoreValue = document.getElementById('reviewScoreInput').value;
            const noteValue = document.getElementById('reviewNoteInput').value.trim();
            const payload = {
                rating: scoreValue ? Number(scoreValue) : null,
                review_note: noteValue || null
            };
            const apiPath = targetType === 'task' ? '/tasks/' + reviewTargetId : '/focuss/' + reviewTargetId;
            apiRequest('PUT', apiPath, payload).then(function(response) {
                if (response && response.code == 9999) {
                    closeReviewModal();
                    if (targetType === 'task') {
                        showtasks();
                    } else {
                        showfocuss();
                    }
                    showNotification('success', '评分与备注已保存');
                } else {
                    showNotification('error', (response && response.msg) ? response.msg : '保存失败');
                }
            }).catch(function() {
                showNotification('error', '保存失败，请稍后重试');
            });
        }

        function handleKeyPress(e) {
            // 任务输入框回车（兼容原有代码）
            if (e.keyCode == 13) {
                const taskInput = document.getElementById('task_name');
                const focusInput = document.getElementById('focus_name');

                if (taskInput && document.activeElement === taskInput) {
                    e.preventDefault();
                    const taskName = taskInput.value.trim();
                    if (taskName) {
                        addNewTask(taskName);
                    }
                } else if (focusInput && document.activeElement === focusInput) {
                    e.preventDefault();
                    savePomoRecord();
                }
            }
        }

        function normalizeTaskText(text) {
            return String(text || '')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function extractTaskTextFromElement(target) {
            if (!target) return '';
            const taskNameHolder = target.closest('[data-task-name]');
            if (taskNameHolder) {
                return normalizeTaskText(taskNameHolder.getAttribute('data-task-name'));
            }
            const taskElement = target.closest('.task-item') || target.closest('.task-content');
            if (!taskElement) return '';
            const taskTextElement = taskElement.querySelector('.font-medium');
            const raw = taskTextElement ? taskTextElement.textContent : taskElement.textContent;
            return normalizeTaskText(raw);
        }

        // 双击任务添加到专注描述（兼容原有功能）
        document.addEventListener('dblclick', function(e) {
            const taskText = extractTaskTextFromElement(e.target);
            if (!taskText) {
                return;
            }
            const focusInput = document.getElementById('focus_name');
            if (!focusInput) {
                return;
            }
            const currentValue = normalizeTaskText(focusInput.value);
            if (!currentValue.includes(taskText)) {
                focusInput.value = currentValue ? `${currentValue} + ${taskText}` : taskText;
                showNotification('info', '任务已添加到专注描述');
            }
        });

        // 显示通知
        function showNotification(type, message) {
            // 移除已有的通知
            document.querySelectorAll('.notification-item').forEach(el => el.remove());

            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
            const icon = type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

            notification.className = `notification-item fixed top-4 right-4 z-50 fade-in max-w-sm`;
            notification.innerHTML = `
            <div class="card ${bgColor} text-white shadow-xl">
                <div class="p-4 flex items-center gap-3">
                    <i class="fas ${icon} text-lg"></i>
                    <div class="flex-1">${escapeHtml(message)}</div>
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

        restoreTaskPanelState();
    </script>
@endsection
