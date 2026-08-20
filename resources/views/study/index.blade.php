@extends('layouts.app')

@section('title', '学习计划 - 蒙太奇')

@section('content')
    @php
        $displayName = optional(auth()->user())->nickname ?: optional(auth()->user())->name ?: '小溪';
    @endphp
    <style>
        .study-shell { max-width: 1080px; margin: 0 auto; }
        .study-top-card { background: linear-gradient(135deg, #fff9fb 0%, #f8fff6 100%); border: 1px solid #f5dce6; border-radius: 24px; padding: 18px; box-shadow: 0 8px 22px rgba(27, 43, 99, 0.06); }
        .study-quick-btn { width: 40px; height: 40px; border-radius: 999px; border: 0; background: #1f3d8a; color: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .study-summary-wrap { position: relative; }
        .study-summary-card { position: absolute; right: 0; top: calc(100% + 10px); width: 340px; max-width: 85vw; background: #fff; border: 1px solid #e6edf7; border-radius: 16px; box-shadow: 0 16px 40px rgba(27, 43, 99, 0.16); z-index: 60; }
        .study-summary-card.show { display: block; }
        .study-week-wrap { margin-top: 12px; background: rgba(255, 255, 255, 0.85); border: 1px solid #e9eef5; border-radius: 14px; padding: 8px; }
        .study-week-title { font-size: 12px; color: #667085; margin-bottom: 6px; padding: 0 2px; }
        .study-week-scroll { display: flex; gap: 8px; overflow-x: auto; overflow-y: hidden; padding: 2px 2px 6px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; }
        .study-day-item { flex: 0 0 64px; min-height: 62px; border-radius: 14px; padding: 8px 6px; border: 0; text-align: center; background: #d7f6ca; color: #111827; display: flex; flex-direction: column; align-items: center; justify-content: center; scroll-snap-align: start; white-space: nowrap; }
        .study-day-item.is-selected { background: #ff5f9d; color: #fff; box-shadow: 0 6px 16px rgba(255, 95, 157, 0.3); }
        .study-day-week { font-size: 11px; font-weight: 600; }
        .study-day-date { font-size: 12px; margin-top: 3px; }
        .study-day-today { font-size: 9px; margin-top: 3px; opacity: 0.9; }
        .study-panel { border-radius: 20px; border: 1px solid #e6edf7; background: #fff; padding: 16px; }
        .study-mascot { width: 64px; height: 64px; border-radius: 16px; background: #f2f4f7; border: 1px dashed #c8d2e0; display: inline-flex; align-items: center; justify-content: center; color: #74839b; font-size: 24px; }
        .study-level-line { height: 8px; background: #edf1f7; border-radius: 999px; overflow: hidden; }
        .study-level-progress { height: 100%; width: 68%; background: linear-gradient(90deg, #52be6b 0%, #1f7f44 100%); }
        .study-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .study-stat-item { border-radius: 14px; background: #f8fafc; padding: 10px 12px; }
        .study-stat-name { font-size: 12px; color: #475467; }
        .study-stat-value { font-size: 20px; line-height: 1.2; font-weight: 700; color: #0f172a; margin-top: 4px; }
        .study-reward-row { margin-top: 8px; display: flex; gap: 10px; }
        .study-reward-pill { border-radius: 999px; background: #eef4ff; color: #1e3a8a; font-size: 12px; padding: 5px 10px; display: inline-flex; align-items: center; gap: 6px; }
        .study-reward-pill.energy { background: #f4ebff; color: #6d28d9; }
        .study-motivation { margin-top: 16px; border-radius: 14px; background: #f2eaff; color: #5b21b6; font-size: 14px; text-align: center; padding: 11px 12px; font-weight: 600; }
        .study-section-title { margin-top: 14px; font-size: 18px; font-weight: 700; color: #101828; display: flex; align-items: center; gap: 8px; }
        .study-task-list { margin-top: 12px; display: grid; gap: 12px; }
        .study-task-card { border-radius: 18px; border: 1px solid #e4e7ec; background: #fff; padding: 14px; box-shadow: 0 4px 14px rgba(17, 24, 39, 0.04); }
        .study-tag { display: inline-block; border-radius: 999px; background: #ffecf4; color: #be185d; font-size: 11px; font-weight: 700; padding: 4px 10px; }
        .study-task-name { margin-top: 8px; font-size: 17px; font-weight: 700; color: #111827; }
        .study-task-meta { margin-top: 6px; font-size: 12px; color: #667085; }
        .study-task-desc { margin-top: 8px; font-size: 14px; color: #344054; }
        .study-task-foot { margin-top: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .study-task-reward { font-size: 13px; color: #ca8a04; font-weight: 600; }
        .study-task-actions { display: flex; gap: 8px; align-items: center; }
        .study-start-btn { background: #1e3a8a; color: #fff; border-radius: 12px; border: 0; padding: 8px 12px; font-size: 13px; font-weight: 600; }
        .study-checkin-btn { border-radius: 12px; padding: 8px 10px; }
        .study-tools-row { margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px; }
        .study-quick-create-btn { background: linear-gradient(135deg, #ff5f9d 0%, #ff8a5c 100%); color: #fff; border: 0; border-radius: 12px; padding: 8px 14px; font-size: 13px; font-weight: 700; box-shadow: 0 6px 16px rgba(255, 95, 157, 0.3); cursor: pointer; transition: transform .15s, box-shadow .15s; }
        .study-quick-create-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(255, 95, 157, 0.4); }
        .quick-plan-preset { border: 1px solid #e4e7ec; border-radius: 12px; padding: 10px; cursor: pointer; text-align: center; background: #fafbfc; transition: border-color .15s, background .15s; }
        .quick-plan-preset:hover, .quick-plan-preset.is-active { border-color: #ff5f9d; background: #fff5f9; }
        .quick-plan-preset-name { font-size: 13px; font-weight: 600; color: #111827; }
        .quick-plan-preset-desc { font-size: 11px; color: #667085; margin-top: 2px; }
        @media (min-width: 900px) {
            .study-week-scroll { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); overflow: visible; }
            .study-day-item { min-height: 68px; flex: initial; width: 100%; }
        }
    </style>

    <div class="study-shell">
        <div class="study-top-card">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold text-gray-900" id="greetingText">中午好，{{ $displayName }}</div>
                <div class="flex items-center gap-2">
                    <div class="study-summary-wrap" id="studySummaryWrap">
                        <button type="button" class="study-quick-btn" id="studySummaryBtn" title="宠物与学习统计">
                            <i class="fas fa-paw"></i>
                        </button>
                        <div id="studySummaryCard" class="study-summary-card hidden">
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="study-mascot">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm text-gray-500">宠物商店吉祥物</div>
                                        <div class="text-base font-semibold text-gray-900 mt-1">默认形象，点击可前往领取</div>
                                        <div class="text-sm text-gray-600 mt-2">Lv.6</div>
                                        <div class="study-level-line mt-2">
                                            <div class="study-level-progress"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="study-reward-row">
                                    <div class="study-reward-pill"><i class="fas fa-coins"></i>金币 x<span id="todayGoldReward">0</span></div>
                                    <div class="study-reward-pill energy"><i class="fas fa-circle"></i>能量球 x<span id="todayEnergyReward">0</span></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-4 pt-0">
                                <div class="study-stat-item">
                                    <div class="study-stat-name">学习总时长</div>
                                    <div class="study-stat-value"><span id="todayLearnedMinutes">0</span> 分钟</div>
                                </div>
                                <div class="study-stat-item">
                                    <div class="study-stat-name">预估总时长</div>
                                    <div class="study-stat-value"><span id="todayEstimatedMinutes">0</span> 分钟</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="/study/checkins" class="study-quick-btn" title="打卡日历">
                        <i class="fas fa-calendar-alt"></i>
                    </a>
                </div>
            </div>

            <div class="study-week-wrap">
                <div class="study-week-title" id="weekLabel">-</div>
                <div class="study-week-scroll" id="daysContainer">
                    <div class="text-sm text-gray-500">加载中...</div>
                </div>
            </div>

            <div class="study-tools-row">
                <button class="btn btn-outline btn-sm" onclick="switchDate(-1)">前一天</button>
                <button class="btn btn-outline btn-sm" onclick="switchDate(1)">后一天</button>
                <button class="btn btn-outline btn-sm" onclick="openPlanListModal()">
                    <i class="fas fa-list mr-1"></i>计划列表
                </button>
                <button class="study-quick-create-btn" onclick="openQuickPlanModal()">
                    <i class="fas fa-bolt mr-1"></i>快速创建计划
                </button>
                <button class="btn btn-primary btn-sm" onclick="openPlanModal()">
                    <i class="fas fa-plus mr-1"></i>高级创建
                </button>
            </div>
        </div>

        <div class="study-motivation">从第一个任务开始，一步步前进！</div>
        <div class="study-section-title">
            <i class="fas fa-tasks"></i>
            待办任务
        </div>
        <div class="study-task-list" id="taskCards">
            <div class="text-gray-500 text-sm">加载任务中...</div>
        </div>
    </div>

    <div id="planModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closePlanModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 flex flex-col max-h-[90vh]" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                <div class="font-semibold text-gray-900">添加学习计划</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closePlanModal()"><i class="fas fa-times"></i></button>
            </div>
            <form class="p-4 space-y-3 flex-1 min-h-0 overflow-y-auto" id="planForm">
                <div>
                    <label class="text-sm text-gray-700">计划名称</label>
                    <input class="input w-full mt-1" name="name" required maxlength="255" />
                </div>
                <div>
                    <label class="text-sm text-gray-700">任务内容类型</label>
                    <select class="input w-full mt-1" name="content_mode" id="contentMode" onchange="onPlanModeChange()">
                        <option value="fixed">固定内容</option>
                        <option value="by_repeat">按重复策略自定义</option>
                    </select>
                </div>
                <div id="fixedContentWrap">
                    <label class="text-sm text-gray-700">固定任务内容</label>
                    <textarea class="input w-full mt-1" name="content" rows="3" maxlength="3000" placeholder="每次任务都使用这段内容"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm text-gray-700">开始时间</label>
                        <input class="input w-full mt-1" type="datetime-local" name="start_time_local" required />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">自定义积分 SP</label>
                        <input class="input w-full mt-1" type="number" min="0" max="10000" name="sp_points" value="0" />
                    </div>
                </div>
                <div>
                    <label class="text-sm text-gray-700">重复策略</label>
                    <select class="input w-full mt-1" name="repeat_type" id="repeatType" onchange="onRepeatTypeChange()">
                        <option value="none">不重复</option>
                        <option value="daily">每天</option>
                        <option value="weekly">每周</option>
                        <option value="ebbinghaus">艾宾浩斯复习</option>
                    </select>
                </div>
                <div id="repeatDaysWrap" class="hidden">
                    <label class="text-sm text-gray-700">每周重复日</label>
                    <div class="mt-1 grid grid-cols-4 gap-2 text-sm">
                        <label><input type="checkbox" name="repeat_days" value="1"> 周一</label>
                        <label><input type="checkbox" name="repeat_days" value="2"> 周二</label>
                        <label><input type="checkbox" name="repeat_days" value="3"> 周三</label>
                        <label><input type="checkbox" name="repeat_days" value="4"> 周四</label>
                        <label><input type="checkbox" name="repeat_days" value="5"> 周五</label>
                        <label><input type="checkbox" name="repeat_days" value="6"> 周六</label>
                        <label><input type="checkbox" name="repeat_days" value="7"> 周日</label>
                    </div>
                </div>
                <div>
                    <label class="text-sm text-gray-700">预计时间类型</label>
                    <select class="input w-full mt-1" name="estimated_time_mode" id="estimatedTimeMode" onchange="onPlanModeChange()">
                        <option value="fixed">固定时长</option>
                        <option value="by_repeat">按重复策略自定义</option>
                    </select>
                </div>
                <div id="fixedEstimatedWrap">
                    <label class="text-sm text-gray-700">固定预计时长（分钟）</label>
                    <input class="input w-full mt-1" type="number" min="0" max="1440" name="estimated_minutes" value="30" />
                </div>
                <div id="slotConfigWrap" class="hidden">
                    <label class="text-sm text-gray-700">按重复策略自定义配置</label>
                    <div class="text-xs text-gray-500 mt-1">按当前重复策略填写内容和时长，可只填写其中一项。</div>
                    <div id="slotConfigList" class="mt-2 space-y-2"></div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="btn btn-outline" onclick="closePlanModal()">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>

    <div id="quickPlanModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeQuickPlanModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-xl w-full mx-4" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">快速创建学习计划</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeQuickPlanModal()"><i class="fas fa-times"></i></button>
            </div>
            <form class="p-4 space-y-3" id="quickPlanForm">
                <div>
                    <label class="text-sm text-gray-700">这次想学什么？</label>
                    <input class="input w-full mt-1" name="name" required maxlength="255" placeholder="例如：背诵英语单词、数学练习题、阅读打卡..."
                        id="quickPlanName" autocomplete="off" />
                    <div class="text-xs text-gray-400 mt-1">先简单起个名字，稍后可在“计划列表”里调整详细设置。</div>
                </div>
                <div>
                    <div class="text-sm text-gray-700 mb-2">选一个模板（可跳过）</div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="quick-plan-preset" data-preset="daily" onclick="selectQuickPreset(this)">
                            <div class="quick-plan-preset-name"><i class="fas fa-sun mr-1"></i>每天</div>
                            <div class="quick-plan-preset-desc">每天重复一次</div>
                        </div>
                        <div class="quick-plan-preset" data-preset="weekly" onclick="selectQuickPreset(this)">
                            <div class="quick-plan-preset-name"><i class="fas fa-calendar-week mr-1"></i>每周</div>
                            <div class="quick-plan-preset-desc">每周完成一次</div>
                        </div>
                        <div class="quick-plan-preset" data-preset="ebbinghaus" onclick="selectQuickPreset(this)">
                            <div class="quick-plan-preset-name"><i class="fas fa-brain mr-1"></i>艾宾浩斯</div>
                            <div class="quick-plan-preset-desc">科学循环复习</div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="text-sm text-gray-700">预计时长（分钟）</label>
                    <input class="input w-full mt-1" type="number" min="0" max="1440" name="estimated_minutes" value="30" />
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="btn btn-outline" onclick="closeQuickPlanModal()">取消</button>
                    <button type="submit" class="study-quick-create-btn" style="padding: 8px 18px;">创建计划</button>
                </div>
            </form>
        </div>
    </div>

    <div id="checkinModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeCheckinModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 flex flex-col max-h-[90vh]" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                <div class="font-semibold text-gray-900">学习打卡</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeCheckinModal()"><i class="fas fa-times"></i></button>
            </div>
            <form class="p-4 space-y-3 flex-1 min-h-0 overflow-y-auto" id="checkinForm">
                <input type="hidden" name="task_id" />
                <div>
                    <label class="text-sm text-gray-700">打卡文字</label>
                    <textarea class="input w-full mt-1" rows="4" name="content" maxlength="5000"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm text-gray-700">语音打卡（浏览器录音）</label>
                        <div class="flex items-center gap-2 mt-1">
                            <button type="button" id="recordStartBtn" class="btn btn-outline btn-sm" onclick="startRecording()">开始录音</button>
                            <button type="button" id="recordStopBtn" class="btn btn-outline btn-sm" onclick="stopRecording()" disabled>停止录音</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="clearRecordedAudio()">清空</button>
                        </div>
                        <div id="recordStatus" class="text-xs text-gray-500 mt-2">未录音</div>
                        <audio id="recordPreview" class="w-full mt-2 hidden" controls></audio>
                        <div class="text-xs text-gray-400 mt-2">也可直接上传音频文件</div>
                        <input class="input w-full mt-1" type="file" name="audio" id="audioFileInput" accept=".mp3,.mpeg,.wav,.m4a,.webm,.ogg,audio/*" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">相册媒体（图片/视频）</label>
                        <input class="input w-full mt-1" type="file" name="media" id="mediaFileInput" accept="image/*,video/*" />
                        <div id="mediaHint" class="text-xs text-gray-500 mt-2">可从相册选择照片或视频</div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="btn btn-outline" onclick="closeCheckinModal()">取消</button>
                    <button type="submit" class="btn btn-primary">提交打卡</button>
                </div>
            </form>
        </div>
    </div>

    <div id="planListModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closePlanListModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">计划管理</div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" onclick="generateUpcoming()">
                        <i class="fas fa-sync mr-1"></i>生成未来任务
                    </button>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closePlanListModal()"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                <div class="border-r border-gray-200 max-h-[78vh] overflow-y-auto">
                    <div class="p-4 space-y-2" id="planListContainer">
                        <div class="text-sm text-gray-500">加载中...</div>
                    </div>
                </div>
                <div class="max-h-[78vh] overflow-y-auto">
                    <div class="p-4" id="planDetailContainer">
                        <div class="text-sm text-gray-500">请在左侧选择计划查看详情。</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const profileName = @json($displayName);
        let selectedDate = '';
        let selectedTaskId = 0;
        let selectedPlanId = 0;
        let recordingStream = null;
        let mediaRecorder = null;
        let recordedAudioFile = null;
        const weeklySlots = [
            { key: '1', label: '周一' },
            { key: '2', label: '周二' },
            { key: '3', label: '周三' },
            { key: '4', label: '周四' },
            { key: '5', label: '周五' },
            { key: '6', label: '周六' },
            { key: '7', label: '周日' }
        ];
        const ebbinghausSlots = [
            { key: 'd0', label: '第0天（当天）' },
            { key: 'd1', label: '第1天' },
            { key: 'd2', label: '第2天' },
            { key: 'd4', label: '第4天' },
            { key: 'd7', label: '第7天' },
            { key: 'd15', label: '第15天' },
            { key: 'd30', label: '第30天' }
        ];

        function csrfToken() {
            const node = document.querySelector('meta[name="csrf-token"]');
            return node ? node.getAttribute('content') : '';
        }

        async function requestApi(path, options = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const opts = Object.assign({ method: 'GET' }, options);
            opts.headers = Object.assign({
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }, options.headers || {});
            if (opts.method !== 'GET' && !opts.headers['X-CSRF-TOKEN']) {
                opts.headers['X-CSRF-TOKEN'] = csrfToken();
            }
            const resp = await fetcher('/api/v2' + path, opts);
            return resp.json();
        }

        function getResult(resp) {
            return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {};
        }

        function getGreetingByHour() {
            const hour = new Date().getHours();
            if (hour < 6) return '凌晨好';
            if (hour < 11) return '早上好';
            if (hour < 14) return '中午好';
            if (hour < 18) return '下午好';
            return '晚上好';
        }

        function updateGreeting() {
            const node = document.getElementById('greetingText');
            if (!node) return;
            node.textContent = `${getGreetingByHour()}，${profileName}`;
        }

        function inferTaskType(task) {
            const raw = `${task.name || ''} ${task.content || ''}`.toLowerCase();
            if (raw.indexOf('语文') >= 0) return '语文';
            if (raw.indexOf('英语') >= 0 || raw.indexOf('english') >= 0) return '英语';
            return '其他';
        }

        function displayMinutes(task) {
            const minutes = Number(task.estimated_minutes || 0);
            return minutes > 0 ? minutes : 10;
        }

        function updateDashboard(data) {
            const stats = data.dashboard || {};
            const learned = Number(stats.learned_minutes || 0);
            const estimated = Number(stats.estimated_minutes || 0);
            const gold = Number(stats.gold_reward || 0);
            const energy = Number(stats.energy_reward || 0);
            document.getElementById('todayLearnedMinutes').textContent = String(learned);
            document.getElementById('todayEstimatedMinutes').textContent = String(estimated);
            document.getElementById('todayGoldReward').textContent = String(gold);
            document.getElementById('todayEnergyReward').textContent = String(energy);
        }

        function renderDays(data) {
            document.getElementById('weekLabel').textContent = data.week_label || '-';
            const node = document.getElementById('daysContainer');
            const days = data.days || [];
            if (!days.length) {
                node.innerHTML = '<div class="text-sm text-gray-500">暂无日期数据</div>';
                return;
            }
            node.innerHTML = days.map(d => `
                <button class="study-day-item ${d.is_selected ? 'is-selected' : ''}"
                    onclick="pickDate('${d.date}')">
                    <div class="study-day-week">${d.day_label}</div>
                    <div class="study-day-date">${d.day_of_month}</div>
                    ${d.is_today ? '<div class="study-day-today">今天</div>' : ''}
                </button>
            `).join('');
        }

        function renderTasks(data) {
            const node = document.getElementById('taskCards');
            const tasks = data.tasks || [];
            if (!tasks.length) {
                node.innerHTML = '<div class="study-panel text-sm text-gray-500">这一天没有学习任务。</div>' +
                    '<div class="text-center mt-2">' +
                    '<button class="study-quick-create-btn" onclick="openQuickPlanModal()">' +
                    '<i class="fas fa-bolt mr-1"></i>快速创建计划</button>' +
                    '</div>';
                return;
            }
            node.innerHTML = tasks.map(t => `
                <div class="study-task-card">
                    <span class="study-tag">${inferTaskType(t)}</span>
                    <div class="study-task-name">${escapeHtml(t.name || '复习当天功课')}</div>
                    <div class="study-task-meta">预计耗时 ${displayMinutes(t)} 分钟</div>
                    <div class="study-task-desc">${escapeHtml(t.content || '暂无任务描述')}</div>
                    <div class="study-task-foot">
                        <div class="study-task-reward"><i class="fas fa-coins mr-1"></i>x${Number(t.sp_points || 0)}</div>
                        <div class="study-task-actions">
                            <span class="text-xs px-2 py-1 rounded-full ${t.is_checked_in ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'}">
                                ${t.is_checked_in ? '已打卡' : '未打卡'}
                            </span>
                            <a class="btn btn-outline btn-sm" href="/studyfocus/${t.id}">进入专注</a>
                            <button class="study-start-btn study-checkin-btn" onclick="openCheckinModal(${t.id})">开始挑战</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function repeatTypeText(t) {
            if (t === 'daily') return '每天';
            if (t === 'weekly') return '每周';
            if (t === 'ebbinghaus') return '艾宾浩斯';
            return '不重复';
        }

        function contentModeText(v) {
            return v === 'by_repeat' ? '按策略自定义' : '固定内容';
        }

        function estimatedModeText(v) {
            return v === 'by_repeat' ? '按策略自定义' : '固定时长';
        }

        function renderPlanList(plans) {
            const node = document.getElementById('planListContainer');
            if (!plans || !plans.length) {
                node.innerHTML = '<div class="text-sm text-gray-500">暂无学习计划。</div>';
                return;
            }
            node.innerHTML = plans.map(p => `
                <div class="border rounded-lg p-3 ${Number(selectedPlanId) === Number(p.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 truncate">${escapeHtml(p.name || '')}</div>
                            <div class="text-xs text-gray-500 mt-1">重复：${repeatTypeText(p.repeat_type || 'none')} | SP：${Number(p.sp_points || 0)}</div>
                            <div class="text-xs text-gray-500 mt-1">任务：${Number(p.task_done || 0)}/${Number(p.task_total || 0)}</div>
                        </div>
                        <span class="text-[11px] px-2 py-1 rounded-full ${Number(p.status) === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'}">
                            ${Number(p.status) === 1 ? '启用' : '停用'}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                        <button class="btn btn-outline btn-sm" onclick="viewPlanDetail(${Number(p.id)})">详情</button>
                        <button class="btn btn-outline btn-sm" onclick="togglePlanStatus(${Number(p.id)}, ${Number(p.status)})">${Number(p.status) === 1 ? '停用' : '启用'}</button>
                        <button class="btn btn-outline btn-sm" onclick="generateByPlan(${Number(p.id)})">生成任务</button>
                        <button class="btn btn-outline btn-sm text-red-600 border-red-600 hover:bg-red-50" onclick="deletePlan(${Number(p.id)})">删除</button>
                    </div>
                </div>
            `).join('');
        }

        function renderPlanDetail(data) {
            const node = document.getElementById('planDetailContainer');
            const plan = data && data.plan ? data.plan : null;
            if (!plan) {
                node.innerHTML = '<div class="text-sm text-gray-500">请在左侧选择计划查看详情。</div>';
                return;
            }
            const tasks = data.tasks || [];
            node.innerHTML = `
                <div>
                    <div class="text-lg font-semibold text-gray-900">${escapeHtml(plan.name || '')}</div>
                    <div class="text-sm text-gray-600 mt-2 whitespace-pre-wrap">${escapeHtml(plan.content || '暂无内容')}</div>
                    <div class="grid grid-cols-2 gap-2 mt-4 text-sm">
                        <div class="text-gray-500">状态</div><div class="text-gray-900">${Number(plan.status) === 1 ? '启用' : '停用'}</div>
                        <div class="text-gray-500">开始时间</div><div class="text-gray-900">${escapeHtml(plan.start_time || '-')}</div>
                        <div class="text-gray-500">重复策略</div><div class="text-gray-900">${repeatTypeText(plan.repeat_type || 'none')}</div>
                        <div class="text-gray-500">重复日</div><div class="text-gray-900">${(plan.repeat_days || []).join(',') || '-'}</div>
                        <div class="text-gray-500">内容类型</div><div class="text-gray-900">${contentModeText(plan.content_mode || 'fixed')}</div>
                        <div class="text-gray-500">时间类型</div><div class="text-gray-900">${estimatedModeText(plan.estimated_time_mode || 'fixed')}</div>
                        <div class="text-gray-500">预计时长</div><div class="text-gray-900">${Number(plan.estimated_minutes || 0)} 分钟</div>
                        <div class="text-gray-500">SP积分</div><div class="text-gray-900">${Number(plan.sp_points || 0)}</div>
                        <div class="text-gray-500">任务进度</div><div class="text-gray-900">${Number(plan.task_done || 0)}/${Number(plan.task_total || 0)}</div>
                    </div>
                    <div class="mt-4 border-t border-gray-200 pt-3">
                        <div class="font-medium text-gray-900 mb-2">已生成任务（最多30条）</div>
                        ${tasks.length ? `
                            <div class="space-y-2">
                                ${tasks.map(t => `
                                    <div class="border border-gray-200 rounded p-2 text-sm">
                                        <div class="font-medium text-gray-900">${escapeHtml(t.name || '')}</div>
                                        <div class="text-xs text-gray-500 mt-1">${escapeHtml(t.study_scheduled_date || '')} ${escapeHtml(t.planned_start_time || '')}</div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<div class="text-sm text-gray-500">暂无任务记录。</div>'}
                    </div>
                </div>
            `;
        }

        function escapeHtml(raw) {
            return String(raw || '').replace(/[&<>"']/g, function(m) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
            });
        }

        async function loadOverview(date) {
            const resp = await requestApi('/study/overview?date=' + encodeURIComponent(date || ''));
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '加载失败');
                return;
            }
            const data = getResult(resp);
            selectedDate = data.selected_date || date;
            renderDays(data);
            updateDashboard(data);
            renderTasks(data);
        }

        function pickDate(date) {
            selectedDate = date;
            loadOverview(date);
        }

        function formatLocalDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function switchDate(offset) {
            const base = selectedDate ? new Date(selectedDate + 'T00:00:00') : new Date();
            base.setDate(base.getDate() + offset);
            pickDate(formatLocalDate(base));
        }

        async function loadPlanList(pickFirst = false) {
            const node = document.getElementById('planListContainer');
            node.innerHTML = '<div class="text-sm text-gray-500">加载中...</div>';
            const resp = await requestApi('/study/plans');
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '计划列表加载失败');
                return;
            }
            const data = getResult(resp);
            const plans = data.plans || [];
            renderPlanList(plans);
            if (pickFirst && plans.length) {
                selectedPlanId = Number(plans[0].id || 0);
                await viewPlanDetail(selectedPlanId);
                renderPlanList(plans);
            } else if (!plans.length) {
                selectedPlanId = 0;
                renderPlanDetail(null);
            }
        }

        async function viewPlanDetail(planId) {
            selectedPlanId = Number(planId || 0);
            const resp = await requestApi('/study/plans/' + selectedPlanId);
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '计划详情加载失败');
                return;
            }
            renderPlanDetail(getResult(resp));
            await loadPlanList(false);
        }

        function openPlanListModal() {
            const m = document.getElementById('planListModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
            loadPlanList(true);
        }

        function closePlanListModal(e) {
            if (e && e.target !== e.currentTarget) return;
            const m = document.getElementById('planListModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        async function togglePlanStatus(planId, currentStatus) {
            const target = Number(currentStatus) === 1 ? 0 : 1;
            const resp = await requestApi('/study/plans/' + Number(planId) + '/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: target })
            });
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '状态更新失败');
                return;
            }
            await loadPlanList(false);
            if (selectedPlanId === Number(planId)) {
                await viewPlanDetail(planId);
            }
        }

        async function generateByPlan(planId) {
            const dateFrom = selectedDate || formatLocalDate(new Date());
            const end = new Date(dateFrom + 'T00:00:00');
            end.setDate(end.getDate() + 14);
            const resp = await requestApi('/study/plans/' + Number(planId) + '/generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    date_from: dateFrom,
                    date_to: formatLocalDate(end)
                })
            });
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '生成失败');
                return;
            }
            const data = getResult(resp);
            alert('已生成任务：' + Number(data.generated || 0));
            await loadOverview(selectedDate);
            await loadPlanList(false);
            if (selectedPlanId === Number(planId)) {
                await viewPlanDetail(planId);
            }
        }

        async function deletePlan(planId) {
            if (!confirm('确认删除该计划吗？将以软删除方式隐藏计划。')) {
                return;
            }
            const resp = await requestApi('/study/plans/' + Number(planId), {
                method: 'DELETE'
            });
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '删除失败');
                return;
            }
            selectedPlanId = 0;
            renderPlanDetail(null);
            await loadOverview(selectedDate);
            await loadPlanList(true);
        }

        function openPlanModal() {
            const m = document.getElementById('planModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closePlanModal(e) {
            if (e && e.target !== e.currentTarget) return;
            const m = document.getElementById('planModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        function openQuickPlanModal() {
            document.getElementById('quickPlanForm').reset();
            document.querySelectorAll('.quick-plan-preset').forEach(function(node) {
                node.classList.remove('is-active');
                node.setAttribute('data-selected', '');
            });
            document.getElementById('quickPlanName').focus();
            const m = document.getElementById('quickPlanModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeQuickPlanModal(e) {
            if (e && e.target !== e.currentTarget) return;
            const m = document.getElementById('quickPlanModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        function selectQuickPreset(el) {
            const selected = el.getAttribute('data-selected');
            document.querySelectorAll('.quick-plan-preset').forEach(function(node) {
                node.classList.remove('is-active');
                node.setAttribute('data-selected', '');
            });
            if (selected) {
                // already selected -> 再次点击取消
                return;
            }
            el.classList.add('is-active');
            el.setAttribute('data-selected', '1');
        }

        document.getElementById('quickPlanForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            const name = String(fd.get('name') || '').trim();
            if (!name) {
                alert('请填写计划名称');
                return;
            }
            const preset = document.querySelector('.quick-plan-preset.is-active');
            const repeatType = preset ? preset.getAttribute('data-preset') : 'none';
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const startTime = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:00`;
            const payload = {
                name: name,
                content: '',
                start_time: startTime,
                repeat_type: repeatType,
                repeat_days: [],
                sp_points: 0,
                content_mode: 'fixed',
                estimated_time_mode: 'fixed',
                estimated_minutes: Number(fd.get('estimated_minutes') || 30)
            };
            const btn = this.querySelector('button[type="submit"]');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '创建中...';
            try {
                const resp = await requestApi('/study/plans', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (resp && Number(resp.code) === 9999) {
                    closeQuickPlanModal();
                    this.reset();
                    await loadOverview(selectedDate);
                    alert(`已创建计划「${name}」，并为你生成了任务！`);
                    return;
                }
                alert(resp && resp.msg ? resp.msg : '创建失败');
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        });

        function onRepeatTypeChange() {
            const t = document.getElementById('repeatType').value;
            const wrap = document.getElementById('repeatDaysWrap');
            if (t === 'weekly') {
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }
            onPlanModeChange();
        }

        function getRepeatSlots() {
            const repeatType = document.getElementById('repeatType').value;
            if (repeatType === 'weekly') {
                const selected = Array.from(document.querySelectorAll('input[name="repeat_days"]:checked')).map(function(x) {
                    return String(x.value || '');
                });
                if (!selected.length) {
                    return weeklySlots;
                }
                return weeklySlots.filter(function(s) {
                    return selected.indexOf(s.key) >= 0;
                });
            }
            if (repeatType === 'ebbinghaus') {
                return ebbinghausSlots;
            }
            return [{ key: 'default', label: '默认' }];
        }

        function collectSlotMap(attrName, parser) {
            const out = {};
            const nodes = document.querySelectorAll('[data-' + attrName + '-key]');
            nodes.forEach(function(node) {
                const key = node.getAttribute('data-' + attrName + '-key');
                const val = parser(node.value);
                if (key && val !== null) {
                    out[key] = val;
                }
            });
            return out;
        }

        function renderSlotConfigList() {
            const node = document.getElementById('slotConfigList');
            const contentCurrent = collectSlotMap('slot-content', function(v) {
                const t = String(v || '').trim();
                return t === '' ? null : t;
            });
            const estimatedCurrent = collectSlotMap('slot-estimated', function(v) {
                if (String(v || '').trim() === '') return null;
                const n = Number(v);
                return Number.isFinite(n) && n >= 0 ? Math.floor(n) : null;
            });
            const contentMode = document.getElementById('contentMode').value;
            const timeMode = document.getElementById('estimatedTimeMode').value;
            const slots = getRepeatSlots();
            node.innerHTML = slots.map(function(slot) {
                const contentValue = contentCurrent[slot.key] || '';
                const estimatedValue = estimatedCurrent[slot.key] != null ? estimatedCurrent[slot.key] : '';
                return `
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="font-medium text-sm text-gray-800 mb-2">${slot.label}</div>
                        ${contentMode === 'by_repeat' ? `
                            <div class="mb-2">
                                <label class="text-xs text-gray-600">任务内容</label>
                                <textarea class="input w-full mt-1" rows="2" maxlength="3000" data-slot-content-key="${slot.key}">${escapeHtml(contentValue)}</textarea>
                            </div>
                        ` : ''}
                        ${timeMode === 'by_repeat' ? `
                            <div>
                                <label class="text-xs text-gray-600">预计时长（分钟）</label>
                                <input class="input w-full mt-1" type="number" min="0" max="1440" data-slot-estimated-key="${slot.key}" value="${estimatedValue}">
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        function onPlanModeChange() {
            const contentMode = document.getElementById('contentMode').value;
            const timeMode = document.getElementById('estimatedTimeMode').value;
            const fixedContentWrap = document.getElementById('fixedContentWrap');
            const fixedEstimatedWrap = document.getElementById('fixedEstimatedWrap');
            const slotConfigWrap = document.getElementById('slotConfigWrap');
            if (contentMode === 'fixed') {
                fixedContentWrap.classList.remove('hidden');
            } else {
                fixedContentWrap.classList.add('hidden');
            }
            if (timeMode === 'fixed') {
                fixedEstimatedWrap.classList.remove('hidden');
            } else {
                fixedEstimatedWrap.classList.add('hidden');
            }
            if (contentMode === 'by_repeat' || timeMode === 'by_repeat') {
                slotConfigWrap.classList.remove('hidden');
                renderSlotConfigList();
            } else {
                slotConfigWrap.classList.add('hidden');
                document.getElementById('slotConfigList').innerHTML = '';
            }
        }

        function openCheckinModal(taskId) {
            selectedTaskId = taskId;
            const form = document.getElementById('checkinForm');
            form.task_id.value = taskId;
            clearRecordedAudio();
            const m = document.getElementById('checkinModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        async function startRecording() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === 'undefined') {
                alert('当前浏览器不支持录音功能，请直接上传音频文件。');
                return;
            }
            try {
                recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                const chunks = [];
                mediaRecorder = new MediaRecorder(recordingStream);
                mediaRecorder.ondataavailable = function(e) {
                    if (e.data && e.data.size > 0) {
                        chunks.push(e.data);
                    }
                };
                mediaRecorder.onstop = function() {
                    const mimeType = mediaRecorder && mediaRecorder.mimeType ? mediaRecorder.mimeType : 'audio/webm';
                    const ext = mimeType.indexOf('ogg') >= 0 ? 'ogg' : 'webm';
                    const blob = new Blob(chunks, { type: mimeType });
                    recordedAudioFile = new File([blob], 'study_record_' + Date.now() + '.' + ext, { type: mimeType });
                    const audioUrl = URL.createObjectURL(blob);
                    const preview = document.getElementById('recordPreview');
                    preview.src = audioUrl;
                    preview.classList.remove('hidden');
                    document.getElementById('recordStatus').textContent = '已录音，可直接提交';
                    if (recordingStream) {
                        recordingStream.getTracks().forEach(function(track) { track.stop(); });
                        recordingStream = null;
                    }
                };
                mediaRecorder.start();
                document.getElementById('recordStatus').textContent = '录音中...';
                document.getElementById('recordStartBtn').disabled = true;
                document.getElementById('recordStopBtn').disabled = false;
            } catch (e) {
                alert('无法启动录音，请检查麦克风权限。');
            }
        }

        function stopRecording() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            document.getElementById('recordStartBtn').disabled = false;
            document.getElementById('recordStopBtn').disabled = true;
        }

        function clearRecordedAudio() {
            recordedAudioFile = null;
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            if (recordingStream) {
                recordingStream.getTracks().forEach(function(track) { track.stop(); });
                recordingStream = null;
            }
            mediaRecorder = null;
            document.getElementById('recordStartBtn').disabled = false;
            document.getElementById('recordStopBtn').disabled = true;
            const preview = document.getElementById('recordPreview');
            preview.src = '';
            preview.classList.add('hidden');
            document.getElementById('recordStatus').textContent = '未录音';
            const audioInput = document.getElementById('audioFileInput');
            if (audioInput) audioInput.value = '';
        }

        async function generateUpcoming() {
            const resp = await requestApi('/study/generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            });
            if (resp && Number(resp.code) === 9999) {
                const data = getResult(resp);
                alert('已生成任务：' + Number(data.total_generated || 0));
                await loadOverview(selectedDate);
                return;
            }
            alert(resp && resp.msg ? resp.msg : '生成失败');
        }

        function closeCheckinModal(e) {
            if (e && e.target !== e.currentTarget) return;
            clearRecordedAudio();
            const m = document.getElementById('checkinModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.getElementById('planForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            const dt = fd.get('start_time_local');
            const startTime = dt ? (String(dt).replace('T', ' ') + ':00') : '';
            const repeatDays = Array.from(this.querySelectorAll('input[name="repeat_days"]:checked')).map(x => x.value);
            const contentMode = fd.get('content_mode') || 'fixed';
            const estimatedTimeMode = fd.get('estimated_time_mode') || 'fixed';
            const contentBySlot = collectSlotMap('slot-content', function(v) {
                const t = String(v || '').trim();
                return t === '' ? null : t;
            });
            const estimatedBySlot = collectSlotMap('slot-estimated', function(v) {
                if (String(v || '').trim() === '') return null;
                const n = Number(v);
                return Number.isFinite(n) && n >= 0 ? Math.floor(n) : null;
            });
            const payload = {
                name: fd.get('name'),
                content: contentMode === 'fixed' ? (fd.get('content') || '') : '',
                start_time: startTime,
                repeat_type: fd.get('repeat_type') || 'none',
                repeat_days: repeatDays,
                sp_points: Number(fd.get('sp_points') || 0),
                content_mode: contentMode,
                estimated_time_mode: estimatedTimeMode,
                estimated_minutes: Number(fd.get('estimated_minutes') || 0),
                content_by_slot: contentBySlot,
                estimated_by_slot: estimatedBySlot
            };
            const resp = await requestApi('/study/plans', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (resp && Number(resp.code) === 9999) {
                closePlanModal();
                this.reset();
                onPlanModeChange();
                await loadOverview(selectedDate);
                if (!document.getElementById('planListModal').classList.contains('hidden')) {
                    await loadPlanList(true);
                }
                return;
            }
            alert(resp && resp.msg ? resp.msg : '保存失败');
        });

        document.getElementById('checkinForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!selectedTaskId) return;
            const fd = new FormData(this);
            const mediaFile = document.getElementById('mediaFileInput').files[0];
            if (mediaFile) {
                if ((mediaFile.type || '').indexOf('video/') === 0) {
                    fd.append('video', mediaFile);
                } else {
                    fd.append('image', mediaFile);
                }
            }
            if (recordedAudioFile) {
                fd.set('audio', recordedAudioFile, recordedAudioFile.name);
            }
            fd.append('date', selectedDate || formatLocalDate(new Date()));
            const resp = await requestApi('/study/tasks/' + selectedTaskId + '/checkin', {
                method: 'POST',
                body: fd
            });
            if (resp && Number(resp.code) === 9999) {
                closeCheckinModal();
                this.reset();
                clearRecordedAudio();
                await loadOverview(selectedDate);
                return;
            }
            alert(resp && resp.msg ? resp.msg : '打卡失败');
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            const modalMap = [
                ['planModal', 'closePlanModal'],
                ['quickPlanModal', 'closeQuickPlanModal'],
                ['checkinModal', 'closeCheckinModal'],
                ['planListModal', 'closePlanListModal']
            ];
            for (let i = 0; i < modalMap.length; i++) {
                const m = document.getElementById(modalMap[i][0]);
                if (m && !m.classList.contains('hidden')) {
                    window[modalMap[i][1]]();
                    return;
                }
            }
        });

        let summaryHideTimer = null;

        function showStudySummary() {
            clearTimeout(summaryHideTimer);
            const card = document.getElementById('studySummaryCard');
            card.classList.remove('hidden');
            card.classList.add('show');
        }

        function scheduleHideStudySummary() {
            clearTimeout(summaryHideTimer);
            summaryHideTimer = setTimeout(function() {
                const card = document.getElementById('studySummaryCard');
                card.classList.remove('show');
                card.classList.add('hidden');
            }, 150);
        }

        function initStudySummary() {
            const btn = document.getElementById('studySummaryBtn');
            const card = document.getElementById('studySummaryCard');
            if (!btn || !card) return;
            btn.addEventListener('mouseenter', showStudySummary);
            btn.addEventListener('mouseleave', scheduleHideStudySummary);
            card.addEventListener('mouseenter', showStudySummary);
            card.addEventListener('mouseleave', scheduleHideStudySummary);
            // 点击按钮切换展示
            btn.addEventListener('click', function() {
                if (card.classList.contains('show')) {
                    card.classList.remove('show');
                    card.classList.add('hidden');
                } else {
                    showStudySummary();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const today = formatLocalDate(new Date());
            selectedDate = today;
            updateGreeting();
            onPlanModeChange();
            initStudySummary();
            document.querySelectorAll('input[name="repeat_days"]').forEach(function(node) {
                node.addEventListener('change', onPlanModeChange);
            });
            loadOverview(today);
        });
    </script>
@endsection
