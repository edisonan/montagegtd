@extends('layouts.app')

@section('title', '主页 - 蒙太奇')
@section('description', '高效管理您的番茄钟和待办事项，提升个人生产力。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 左侧：番茄钟面板 -->
            <div class="space-y-6">
                <!-- 番茄操作卡片 -->
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
                                <a href="{{ url('pomos') }}"
                                   class="text-sm px-3 py-1 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-history mr-1"></i>历史
                                </a>
                                <button onclick="showThingCreateModal()"
                                        class="text-sm px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-plus mr-1"></i>记录事情
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- 番茄按钮区域 -->
                        <div class="mb-6">
                            @if($current_pomo_status == 1)
                                <!-- 开始番茄按钮 -->
                                <button id="pomoBtn"
                                        class="btn btn-outline w-full py-2 text-lg font-semibold hover:scale-[1.02] transition-transform">
                                    <i class="fas fa-play-circle mr-3 text-xl"></i>
                                    开始新的番茄钟
                                </button>
                            @elseif($current_pomo_status == 2 || $current_pomo_status == 4)
                                <!-- 倒计时显示 -->
                                <div class="relative">
                                    <div id="pomoTimerContainer" class="absolute inset-0 flex items-center justify-center transition-opacity duration-300">
                                        <div class="text-center">
                                            <div id="pomoTimer" class="text-4xl font-bold text-gray-900 mb-2">
                                                <!-- 倒计时动态显示 -->
                                            </div>
                                            <div id="pomoStatus" class="text-sm text-gray-600">
                                                {{ $current_pomo_status == 2 ? '专注进行中' : '休息时间' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 简洁的进度条 -->
                                    <div class="relative">
                                        <svg class="w-full h-48" viewBox="0 0 100 100">
                                            <!-- 背景圆 - 很淡的绿色 -->
                                            <circle cx="50" cy="50" r="45" fill="none"
                                                    stroke="#e8f5e9" stroke-width="2"/>

                                            <!-- 进度圆 - 简洁的绿色 -->
                                            <circle id="progressCircle" cx="50" cy="50" r="45" fill="none"
                                                    stroke="#4caf50" stroke-width="2"
                                                    stroke-linecap="round"
                                                    transform="rotate(-90 50 50)"
                                                    stroke-dasharray="283"
                                                    stroke-dashoffset="283"/>

                                            <!-- 中心文字（纯净模式下显示剩余分钟） -->
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
                            @elseif($current_pomo_status == 3)
                                <!-- 记录番茄内容 -->
                                <div id="recordPomo" class="space-y-4">
                                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                            <div>
                                                <div class="font-medium text-green-800">番茄完成！</div>
                                                <div class="text-sm text-green-700">记录您的成果吧</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm text-gray-600">
                                            <span id="pomo_start_time_show"></span>
                                            <span class="text-gray-400">→</span>
                                            <span id="pomo_end_time_show"></span>
                                        </div>

                                        <input type="text"
                                               name="name"
                                               id="pomo_name"
                                               value=""
                                               placeholder="记录刚完成的番茄内容（点击任务名快速添加）..."
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
                            @endif
                        </div>

                        <!-- 今日番茄统计 -->
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 rounded flex items-center justify-center">
                                        <i class="fas fa-chart-bar text-white text-xs"></i>
                                    </div>
                                    <h3 class="font-medium text-gray-800">今日成果</h3>
                                </div>
                                <div class="text-sm text-gray-500">
                                    已完成 <span id="pomoCount" class="font-semibold text-gray-900">0</span> 个番茄
                                </div>
                            </div>

                            <!-- 番茄列表 -->
                            <div class="space-y-2">
                                <div id="pomosLoading" class="text-center py-8">
                                    <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
                                    <p class="text-sm text-gray-500 mt-2">加载番茄记录...</p>
                                </div>
                                <ul id="pomos" class="space-y-2 hidden">
                                    <!-- 番茄列表动态加载 -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" value="{{ $active_pomo->id }}" id="pomo_id">
            </div>

            <!-- 右侧：待办事项面板 -->
            <div class="space-y-6">
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

    <!-- 原有模态框保留 -->
    @include('components.task-update-modal')
    @include('components.thing-create-modal')

    <style>
        /* 进度条样式 */
        #progressCircle {
            transition: stroke-dashoffset 0.3s ease, stroke 0.3s ease;
        }

        /* 纯净模式切换 */
        #pomoTimerContainer {
            transition: opacity 0.3s ease;
        }

        /* 任务和番茄项样式 */
        .task-item, .pomo-item {
            transition: all 0.2s ease;
        }

        .task-item:hover, .pomo-item:hover {
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
        .pomo-item:hover .task-actions {
            display: flex; /* 显示容器 */
            opacity: 1;
            gap: 4px;
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
        .task-item:hover, .pomo-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: var(--primary-color);
        }

        /* 确保任务内容区域足够紧凑 */
        .task-item .flex-1 {
            min-height: auto; /* 移除最小高度 */
        }

        /* 番茄项的操作按钮区域 */
        .pomo-item .task-actions {
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
    </style>
@endsection

@section('scripts')
    <script>
        // 初始化变量
        let timer;
        let calibrationTimer;
        let mode = 1;
        const interval = 1000;
        const calibrationInterval = 60000;
        let remain = {{ $current_pomo_remain }};
        const status = {{ $current_pomo_status }};
        const title = '蒙太奇 - 专注效率工具';
        const totalTime = {{ $current_pomo_status == 2 ? 25*60 : ($current_pomo_status == 4 ? 5*60 : 1500) }};
        let originalRemain = remain; // 保存原始剩余时间

        // 添加纯净模式状态
        let pureMode = false;

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

            showtasks();
            showpomos();

            // 启动番茄倒计时
            if (status == 2 || status == 4) {
                startPomoTimer();
                updateProgressCircle();
                updateDisplay();
            }

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

        function initializePage() {
            // 设置通知权限
            if (Notification.permission !== "granted") {
                Notification.requestPermission();
            }

            // 如果番茄完成，显示时间
            if (status == 3) {
                showPomoTime('pomo_start_time_show', "{{ $active_pomo->start_time }}");
                showPomoTime('pomo_end_time_show', "{{ $active_pomo->end_time }}");
            }
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
                document.getElementById('pomoTimer').textContent = timeText;
                document.getElementById('pomoStatus').textContent = statusText;
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
                    progressCircle.setAttribute('stroke', '#4caf50'); // 绿色
                } else if (progressPercentage >= 50) {
                    progressCircle.setAttribute('stroke', '#4caf50'); // 橙色
                } else if (progressPercentage >= 25) {
                    progressCircle.setAttribute('stroke', '#4caf50'); // 橙色
                } else {
                    progressCircle.setAttribute('stroke', '#4caf50'); // 红色
                }
            }
        }

        // 切换纯净模式
        function togglePureMode() {
            pureMode = !pureMode;
            const toggleBtn = document.getElementById('pureModeToggle');
            const timerContainer = document.getElementById('pomoTimerContainer');

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
                // 番茄完成
                document.title = '番茄完成！快来记录一下吧 - ' + title;
                notify('您已经完成了一个番茄，快来记录一下吧~');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else if (status == 4) {
                // 休息完成
                document.title = '休息完成，快来开始下一个番茄吧 - ' + title;
                notify('休息完成，快来开始下一个番茄吧~');
                setTimeout(() => {
                    window.location.href = '/index';
                }, 2000);
            }
        }

        // 浏览器通知
        function notify(message) {
            if (Notification.permission !== "granted") {
                Notification.requestPermission();
            } else {
                const notification = new Notification('蒙太奇', {
                    icon: '{{'/favicon.ico'}}',
                    body: message,
                });

                notification.onclick = function () {
                    window.location.href = "/index";
                };
            }
        }

        // 放弃番茄
        function discard() {
            if (confirm("确认要放弃当前番茄/休息吗？")) {
                const pomoId = document.getElementById('pomo_id').value;
                window.location.href = '{{ url("pomos/discard/") }}/' + pomoId;
            }
        }

        // 校准番茄状态
        function calibratePomoStatus() {
            $.ajax({
                url: "/pomos/pomostatus",
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.code == 9999) {
                        const newStatus = response.result.current_pomo_status;
                        const newRemain = response.result.current_pomo_remain;

                        // 如果状态发生变化，刷新页面
                        if (newStatus != status) {
                            window.location.reload();
                            return;
                        }

                        // 更新剩余时间（如果差异较大）
                        if (Math.abs(newRemain - remain) > 5) {
                            remain = newRemain;
                            if (status == 2 || status == 4) {
                                updateDisplay();
                            }
                        }
                    }
                }
            });
        }

        // 保存番茄记录
        function savePomoRecord() {
            const pomoName = document.getElementById('pomo_name').value.trim();
            const pomoId = document.getElementById('pomo_id').value;

            if (pomoName) {
                $.ajax({
                    url: "/pomo/" + pomoId,
                    type: 'POST',
                    data: {
                        "name": pomoName,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.code == 9999) {
                            window.location.href = '{{url('/index')}}';
                        } else {
                            showNotification('error', '保存失败');
                        }
                    }
                });
            } else {
                showNotification('warning', '请输入番茄内容');
            }
        }

        // 创建番茄列表项
        function createPomoListItem(pomoData) {
            const startTime = formatTime(new Date(pomoData.start_time));
            const endTime = formatTime(new Date(pomoData.end_time));

            return `
    <li id="pomo${pomoData.id}" class="pomo-item bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800 truncate">${escapeHtml(pomoData.name || '未命名番茄')}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500">${startTime} - ${endTime}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                <a href="/notes?source_type=1&source_id=${pomoData.id}"
                   class="action-button text-gray-400 hover:text-blue-500"
                   target="_blank"
                   title="记录想法">
                    <i class="fas fa-sticky-note"></i>
                </a>
            </div>
        </div>
    </li>
    `;
        }

        // 创建任务列表项
        function createTaskListItem(data) {
            const isChild = data.parent_task_id !== null;
            const isTop = data.is_top == 1;
            const isCompleted = data.status == 3;

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
                    ${isTop ? '<span class="priority-badge priority-high">置顶</span>' : ''}
                    <span class="font-medium text-gray-800 ${isCompleted ? 'line-through text-gray-400' : ''}">
                        ${escapeHtml(data.name)}
                    </span>
                </div>
            </div>
        </div>

        <!-- 操作按钮区域 - 保持在下方，但默认隐藏 -->
        <div class="task-actions">
            ${data.parent_task_id === null ? `
                <button class="action-button text-gray-400 hover:text-green-500"
                        onclick="addChildTask(${data.id}, '${escapeHtml(data.name)}')"
                        title="添加子任务">
                    <i class="fas fa-plus-circle"></i>
                </button>
            ` : ''}

            <button class="action-button text-gray-400 hover:text-yellow-500"
                    onclick="toggleTaskTop(${data.id}, ${isTop})"
                    title="${isTop ? '取消置顶' : '置顶'}">
                <i class="fas fa-thumbtack ${isTop ? 'text-yellow-500' : ''}"></i>
            </button>

            <button class="action-button text-gray-400 hover:text-blue-500"
                    onclick="editTask(${data.id})"
                    title="编辑">
                <i class="fas fa-edit"></i>
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

        // 任务操作函数
        function toggleTaskStatus(taskId, element) {
            const isCurrentlyCompleted = element.classList.contains('checked');
            const action = isCurrentlyCompleted ? 'restore' : 'finish';

            $.ajax({
                url: `/task/${taskId}`,
                type: 'DELETE',
                data: {
                    type: action,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.code == 9999) {
                        element.classList.toggle('checked');
                        const taskText = document.querySelector(`#task${taskId} .font-medium`);
                        taskText.classList.toggle('line-through');
                        taskText.classList.toggle('text-gray-400');

                        showNotification('success', `任务已${isCurrentlyCompleted ? '恢复' : '完成'}`);
                    } else {
                        showNotification('error', '操作失败');
                    }
                }
            });
        }

        function deleteTask(taskId) {
            if (confirm('确定要删除这个任务吗？此操作不可恢复。')) {
                $.ajax({
                    url: `/task/${taskId}`,
                    type: 'DELETE',
                    data: {
                        type: 'delete',
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.code == 9999) {
                            document.getElementById(`task${taskId}`).remove();
                            showNotification('success', '任务删除成功');
                            updateTaskCount();
                        } else {
                            showNotification('error', '删除失败');
                        }
                    }
                });
            }
        }

        function toggleTaskTop(taskId, isTop) {
            const newStatus = isTop ? 0 : 1;

            $.ajax({
                url: `/task/${taskId}`,
                type: 'POST',
                data: {
                    is_top: newStatus,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.code == 9999) {
                        window.location.reload();
                    } else {
                        showNotification('error', '操作失败');
                    }
                }
            });
        }

        function addChildTask(taskId, taskName) {
            const childName = prompt(`为"${taskName}"创建子任务：`, "");
            if (childName && childName.trim()) {
                $.ajax({
                    url: "{{ url('task') }}",
                    type: 'POST',
                    data: {
                        name: childName.trim(),
                        mode: mode,
                        parent_task_id: taskId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.code == 9999) {
                            const taskItem = createTaskListItem(response.result);
                            const parentElement = document.getElementById(`task${taskId}`);

                            // 找到父任务后面的位置插入
                            if (parentElement) {
                                // 如果父任务已经有子任务，找到最后一个子任务后面插入
                                let nextElement = parentElement.nextElementSibling;
                                let lastChildElement = parentElement;

                                // 找到父任务下的最后一个子任务元素
                                while (nextElement && nextElement.classList.contains('child-task')) {
                                    lastChildElement = nextElement;
                                    nextElement = nextElement.nextElementSibling;
                                }

                                // 在最后一个子任务后面插入新子任务
                                lastChildElement.insertAdjacentHTML('afterend', taskItem);
                            } else {
                                // 如果找不到父任务，就添加到列表末尾
                                const tasksList = document.getElementById('tasks');
                                tasksList.insertAdjacentHTML('beforeend', taskItem);
                            }

                            updateTaskCount();
                            showNotification('success', '子任务添加成功');
                        } else {
                            showNotification('error', '添加失败');
                        }
                    }
                });
            }
        }

        // 为首页加载任务数据并打开编辑弹窗的函数
        function editTask(taskId) {
            // 从服务器获取任务数据 - 使用现有的API
            $.get('/tasks/' + taskId, function(response) {
                if(response.code == 9999) {
                    var task = response.result;
                    openTaskUpdateModal(task);
                } else {
                    alert('获取任务数据失败：' + (response.msg || '未知错误'));
                }
            }).fail(function() {
                alert('获取任务数据失败');
            });
        }

        // 显示待办列表
        function showtasks() {
            $.ajax({
                url: "{{ url('tasksall') }}",
                type: 'GET',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "status": 1,
                    "mode": mode
                },
                success: function(response) {
                    if (response.code == 9999) {
                        const tasksList = document.getElementById('tasks');
                        const loading = document.getElementById('tasksLoading');
                        const empty = document.getElementById('tasksEmpty');

                        tasksList.innerHTML = '';
                        loading.classList.add('hidden');

                        if (response.result && Object.keys(response.result).length > 0) {
                            Object.values(response.result).forEach(data => {
                                tasksList.insertAdjacentHTML('beforeend', createTaskListItem(data));
                            });
                            tasksList.classList.remove('hidden');
                            empty.classList.add('hidden');
                        } else {
                            tasksList.classList.add('hidden');
                            empty.classList.remove('hidden');
                        }

                        updateTaskCount();
                    }
                }
            });
        }

        // 显示番茄列表
        function showpomos() {
            $.ajax({
                url: "{{ url('pomostoday') }}",
                type: 'GET',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'type': 'time'
                },
                success: function(response) {
                    if (response.code == 9999) {
                        const pomosList = document.getElementById('pomos');
                        const loading = document.getElementById('pomosLoading');

                        pomosList.innerHTML = '';
                        loading.classList.add('hidden');

                        if (response.result && Object.keys(response.result).length > 0) {
                            Object.values(response.result).forEach(data => {
                                pomosList.insertAdjacentHTML('beforeend', createPomoListItem(data));
                            });
                            pomosList.classList.remove('hidden');
                        }

                        document.getElementById('pomoCount').textContent =
                            Object.keys(response.result).length;
                    }
                }
            });
        }

        // 添加新任务
        function addNewTask(taskName) {
            $.ajax({
                url: "/task",
                type: 'POST',
                data: {
                    "name": taskName,
                    "mode": mode,
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.code == 9999) {
                        document.getElementById('task_name').value = '';
                        const tasksList = document.getElementById('tasks');
                        const empty = document.getElementById('tasksEmpty');

                        if (empty && !empty.classList.contains('hidden')) {
                            tasksList.innerHTML = '';
                            tasksList.classList.remove('hidden');
                            empty.classList.add('hidden');
                        }

                        tasksList.insertAdjacentHTML('afterbegin', createTaskListItem(response.result));
                        updateTaskCount();
                        showNotification('success', '任务添加成功');
                    } else {
                        showNotification('error', '添加失败');
                    }
                }
            });
        }

        // 开始番茄
        function startPomo() {
            $.ajax({
                url: "{{ url('pomos/start') }}",
                type: 'GET',
                data: {"_token": "{{ csrf_token() }}"},
                success: function (response) {
                    if (response.code != 9999) {
                        showNotification('error', '启动失败');
                    } else {
                        window.location.reload();
                    }
                }
            });
        }

        function updateTaskCount() {
            const taskCount = document.querySelectorAll('#tasks .task-item').length;
            document.getElementById('taskCount').textContent = taskCount;
        }

        function formatTime(date) {
            return date.getHours().toString().padStart(2, '0') + ':' +
                date.getMinutes().toString().padStart(2, '0');
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

            // 番茄按钮点击
            const pomoBtn = document.getElementById('pomoBtn');
            if (pomoBtn) {
                pomoBtn.addEventListener('click', function() {
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
        }

        function handleKeyPress(e) {
            // 任务输入框回车（兼容原有代码）
            if (e.keyCode == 13) {
                const taskInput = document.getElementById('task_name');
                const pomoInput = document.getElementById('pomo_name');

                if (taskInput && document.activeElement === taskInput) {
                    e.preventDefault();
                    const taskName = taskInput.value.trim();
                    if (taskName) {
                        addNewTask(taskName);
                    }
                } else if (pomoInput && document.activeElement === pomoInput) {
                    e.preventDefault();
                    savePomoRecord();
                }
            }
        }

        // 双击任务添加到番茄描述（兼容原有功能）
        document.addEventListener('dblclick', function(e) {
            if (e.target.closest('.task-item') || e.target.closest('.task-content')) {
                const taskElement = e.target.closest('.task-item') || e.target.closest('.task-content');
                const taskText = taskElement.querySelector('.font-medium')?.textContent ||
                    taskElement.textContent;
                const pomoInput = document.getElementById('pomo_name');

                if (pomoInput && taskText) {
                    const currentValue = pomoInput.value.trim();
                    if (!currentValue.includes(taskText)) {
                        pomoInput.value = currentValue ? `${currentValue} + ${taskText}` : taskText;
                        showNotification('info', '任务已添加到番茄描述');
                    }
                }
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

    </script>
@endsection