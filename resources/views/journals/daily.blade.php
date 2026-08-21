@extends('layouts.app')

@section('title', '每日手账 - 蒙太奇')
@section('description', '按天查看 24 小时手账时间轴与当天手账内容')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('common.success')

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">每日手账</h1>
            <p class="text-gray-600">按 24 小时时间轴回顾当天完成的待办、专注与手动记录</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ url('/journals') }}" class="btn btn-outline">
                <i class="fas fa-list mr-2"></i>手账列表
            </a>
            <button type="button" class="btn btn-primary" onclick="showJournalCreateModal()">
                <i class="fas fa-plus mr-2"></i>新增手账
            </button>
        </div>
    </div>

    <div class="card mb-6">
        <div class="p-6">
            <!-- 日期选择 -->
            <div class="flex flex-wrap items-center gap-3">
                <label class="text-sm text-gray-600 font-medium">
                    <i class="fas fa-calendar-day mr-1 text-blue-500"></i>选择日期
                </label>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline" id="dailyPrevDay" title="前一天">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <input id="dailyDate" type="date" class="input text-sm" value="{{ date('Y-m-d') }}">
                    <button type="button" class="btn btn-sm btn-outline" id="dailyNextDay" title="后一天">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline" id="dailyToday" title="回到今天">
                        <i class="fas fa-redo mr-1"></i>今天
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="p-4 sm:p-6">
            <!-- 图例 / 模块切换 -->
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="text-sm text-gray-500">模块：</span>
                <button type="button" class="daily-lane-toggle daily-lane-btn is-on" data-type="2" data-color="todo">
                    <span class="daily-lane-dot" style="background:#10b981"></span>
                    <i class="fas fa-list-check mr-1"></i>待办
                </button>
                <button type="button" class="daily-lane-toggle daily-lane-btn is-on" data-type="3" data-color="focus">
                    <span class="daily-lane-dot" style="background:#8b5cf6"></span>
                    <i class="fas fa-clock mr-1"></i>专注
                </button>
                <button type="button" class="daily-lane-toggle daily-lane-btn is-on" data-type="1" data-color="manual">
                    <span class="daily-lane-dot" style="background:#3b82f6"></span>
                    <i class="fas fa-pen-to-square mr-1"></i>手动记录
                </button>
                <button type="button" class="daily-lane-toggle daily-lane-btn is-on" data-type="all" data-color="all" title="全部选中/取消所有模块">
                    <i class="fas fa-layer-group mr-1"></i>全部
                </button>
            </div>

            <!-- 范围控制（Kibana/ES 风格） -->
            <div class="daily-rangebar" id="dailyRangebar">
                <div class="daily-crumbs" id="dailyCrumbs"></div>
                <div class="flex items-center gap-2">
                    <button type="button" class="daily-range-btn" id="dailyZoomOut" title="放大到上一个范围/全屏">
                        <i class="fas fa-chevron-up mr-1"></i>放大范围
                    </button>
                    <button type="button" class="daily-range-btn" id="dailyReset" title="清除选择，恢复默认范围">
                        <i class="fas fa-times mr-1"></i>清除
                    </button>
                </div>
            </div>

            <!-- 24 小时时间轴（可拖选时间段，参考 ES/Kibana 轨迹图） -->
            <div class="daily-timeline" id="dailyTimeline">
                <div class="daily-tl-axis" id="dailyTlAxis"><!-- 小时刻度 JS 生成 --></div>
                <div class="daily-tl-body" id="dailyTlBody"><!-- 拖选区 -->
                    <div class="daily-tl-grid" id="dailyTlGrid"></div>
                    <div class="daily-tl-selection" id="dailyTlSelection"></div>
                    <!-- 三条轨道 -->
                    <div class="daily-tl-lane" data-lane="2" id="dailyLane-todo">
                        <div class="daily-tl-lane-label"><span class="daily-lane-dot" style="background:#10b981"></span>待办</div>
                        <div class="daily-tl-lane-track"></div>
                    </div>
                    <div class="daily-tl-lane" data-lane="3" id="dailyLane-focus">
                        <div class="daily-tl-lane-label"><span class="daily-lane-dot" style="background:#8b5cf6"></span>专注</div>
                        <div class="daily-tl-lane-track"></div>
                    </div>
                    <div class="daily-tl-lane" data-lane="1" id="dailyLane-manual">
                        <div class="daily-tl-lane-label"><span class="daily-lane-dot" style="background:#3b82f6"></span>手动记录</div>
                        <div class="daily-tl-lane-track"></div>
                    </div>
                </div>
                <div class="daily-tl-empty" id="dailyTlEmpty">该日暂无手账记录</div>
            </div>
            <div class="daily-range-hint">
                <i class="fas fa-mouse-pointer mr-1"></i>在时间轴上拖动选择时间段（放大），双击或点「清除」恢复默认范围
            </div>
        </div>
    </div>

    <!-- 当天手账内容 -->
    <div class="card">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">
                手账内容
                <span class="text-sm font-normal text-gray-500 ml-2" id="dailyContentDate"></span>
            </h2>
            <div class="text-sm text-gray-500">
                <span id="dailyRecordCount">共 0 条</span>
            </div>
        </div>
        <div class="p-6">
            <div id="dailyEmptyState" class="text-center py-14 hidden">
                <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-gray-100 mb-4">
                    <i class="fas fa-calendar-alt text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">这一天还没有手账记录</h3>
                <p class="text-gray-500 mb-6">完成待办、专注或手动记录后，会出现在这里。</p>
            </div>
            <div id="dailyRecordList" class="space-y-2"></div>
        </div>
    </div>
</div>

@include('components.journal-create-modal')

<style>
    /* —— 范围控制条 —— */
    .daily-rangebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }
    .daily-crumbs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        font-size: 11px;
        color: #6b7280;
        line-height: 1;
    }
    .daily-crumb {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 9px;
        border-radius: 4px;
        background: #eef2ff;
        color: #4f46e5;
        font-weight: 500;
        border: 1px solid #e0e7ff;
        cursor: pointer;
        transition: all .15s;
    }
    .daily-crumb:hover {
        background: #e0e7ff;
    }
    .daily-crumb-sep {
        color: #cbd5e1;
        font-size: 10px;
    }
    .daily-range-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        line-height: 1;
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        transition: all .15s;
    }
    .daily-range-btn:hover {
        border-color: #c7d2fe;
        color: #4f46e5;
        background: #f5f7ff;
    }

    /* —— 24 小时时间轴 —— */
    .daily-timeline {
        position: relative;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fafafa;
        overflow: hidden;
        user-select: none;
    }
    .daily-tl-axis {
        position: relative;
        height: 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f3f4f6;
    }
    .daily-tl-axis .tl-tick {
        position: absolute;
        top: 0;
        bottom: 0;
        border-left: 1px solid #e5e7eb;
    }
    .daily-tl-axis .tl-tick-label {
        position: absolute;
        top: 3px;
        transform: translateX(-50%);
        font-size: 9px;
        color: #9ca3af;
        line-height: 1;
        white-space: nowrap;
    }
    .daily-tl-body {
        position: relative;
        cursor: crosshair;
        touch-action: none;
    }
    .daily-tl-grid {
        position: absolute;
        top: 0;
        left: 84px;
        right: 0;
        bottom: 0;
        pointer-events: none;
        z-index: 0;
    }
    .daily-tl-grid .tl-grid-line {
        position: absolute;
        top: 0;
        bottom: 0;
        border-left: 1px dashed #e5e7eb;
    }
    .daily-tl-selection {
        position: absolute;
        top: 0;
        bottom: 0;
        z-index: 5;
        background: rgba(79, 70, 229, 0.18);
        border: 1px solid rgba(79, 70, 229, 0.55);
        border-radius: 3px;
        pointer-events: none;
        display: none;
    }
    .daily-tl-lane {
        position: relative;
        z-index: 1;
        display: flex;
        height: 46px;
        border-bottom: 1px solid #eee;
        opacity: 0.3;
        transition: opacity .2s;
    }
    .daily-tl-lane.is-on {
        opacity: 1;
    }
    .daily-tl-lane:last-child {
        border-bottom: none;
    }
    .daily-tl-lane-label {
        position: relative;
        z-index: 2;
        width: 84px;
        flex: none;
        display: flex;
        align-items: center;
        gap: 5px;
        padding-left: 10px;
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        border-right: 1px solid #e5e7eb;
    }
    .daily-lane-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        flex: none;
    }
    .daily-tl-lane-track {
        position: relative;
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }
    .daily-tl-bar {
        position: absolute;
        top: 50%;
        height: 18px;
        transform: translateY(-50%);
        border-radius: 3px;
        min-width: 3px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,0.12);
        transition: filter .12s, height .12s;
    }
    .daily-tl-bar:hover {
        filter: brightness(1.08);
        height: 22px;
    }
    .daily-tl-tick {
        position: absolute;
        top: 50%;
        width: 3px;
        height: 14px;
        transform: translate(-50%, -50%);
        border-radius: 2px;
        cursor: pointer;
    }
    .daily-tl-empty {
        display: none;
        padding: 22px;
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
    }
    .daily-range-hint {
        margin-top: 8px;
        font-size: 11px;
        color: #9ca3af;
    }
    .daily-lane-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        line-height: 1;
        padding: 6px 10px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        transition: all .15s;
    }
    .daily-lane-btn.is-on {
        border-color: currentColor;
        color: #111827;
        background: #f9fafb;
    }
    .daily-lane-btn.is-off {
        opacity: .45;
        text-decoration: line-through;
    }
    .daily-record-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fff;
        transition: box-shadow .15s;
    }
    .daily-record-item:hover {
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }
    .daily-record-time {
        flex: none;
        width: 108px;
        text-align: right;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
    }
    .daily-record-body {
        flex: 1;
        min-width: 0;
    }
    .daily-record-name {
        font-weight: 500;
        color: #111827;
        word-break: break-word;
    }
</style>

<script src="{{ '/js/My97DatePicker/WdatePicker.js' }}"></script>
<script type="text/javascript">
    (function () {
        var todayStr = (function () {
            var d = new Date();
            return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        })();

        var state = {
            date: todayStr,
            journals: [],
            activeTypes: { '1': true, '2': true, '3': true }, // 手动记录/待办/专注
            viewport: null,        // 当前可见时间段 {start,end}(分钟)，null=使用默认
            defaultViewport: null, // 根据数据自动计算的默认范围
            stack: []              // 用于「放大范围」逐步返回上一级的时段栈
        };

        function pad2(v) {
            v = String(v);
            return v.length < 2 ? ('0' + v) : v;
        }

        function formatDate(d) {
            return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        }

        function addDays(dateStr, days) {
            var d = new Date(dateStr + 'T00:00:00');
            d.setDate(d.getDate() + days);
            return formatDate(d);
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function parseTime(value) {
            if (!value) return NaN;
            var t = new Date(String(value).replace(' ', 'T')).getTime();
            return isNaN(t) ? NaN : t;
        }

        // 分钟换算为该日在 0..1440 之间的位置（跨天截断到 [0,1440]）
        function minuteOfDay(ms) {
            var d = new Date(ms);
            return d.getHours() * 60 + d.getMinutes() + d.getSeconds() / 60;
        }

        function getTypeColor(type) {
            var map = { '1': '#3b82f6', '2': '#10b981', '3': '#8b5cf6' };
            return map[String(type)] || '#9ca3af';
        }

        function getTypeMeta(type) {
            var map = {
                '1': { label: '手动记录', icon: 'fa-pen-to-square' },
                '2': { label: '待办', icon: 'fa-list-check' },
                '3': { label: '专注', icon: 'fa-clock' }
            };
            return map[String(type)] || { label: '手账', icon: 'fa-book' };
        }

        // —— 时间轴 ——

        // 取当前生效的可见范围（默认则用 defaultViewport）
        function currentViewport() {
            if (state.viewport) return state.viewport;
            if (state.defaultViewport) return state.defaultViewport;
            return { start: 0, end: 1440 };
        }
        function vpStart() { return currentViewport().start; }
        function vpEnd() { return currentViewport().end; }
        function vpDuration() { return Math.max(1, vpEnd() - vpStart()); }

        // 把一个分钟内时刻换算到当前视口内的百分比 (0..100)
        function pctInViewport(min) {
            return (Math.min(1440, Math.max(0, min)) - vpStart()) / vpDuration() * 100;
        }

        // 根据数据计算默认可见范围：0-8 点无数据则默认显示 8-24 点
        function computeDefaultViewport() {
            if (!state.journals || state.journals.length === 0) {
                return { start: 0, end: 1440 };
            }
            var hasEarly = false;
            state.journals.forEach(function (j) {
                var s = parseTime(j.start_time);
                if (isNaN(s)) return;
                var m = minuteOfDay(s);
                if (m < 480) hasEarly = true; // 0-8 点 (8*60)
            });
            if (!hasEarly) {
                return { start: 480, end: 1440 }; // 8-24 点
            }
            return { start: 0, end: 1440 };
        }

        function mmText(min) {
            var m = Math.round(min);
            return pad2(Math.floor(m / 60)) + ':' + pad2(m % 60);
        }

        function renderAxis() {
            var axis = document.getElementById('dailyTlAxis');
            axis.innerHTML = '';
            var dur = vpDuration();
            var step;
            if (dur <= 90) step = 15;
            else if (dur <= 240) step = 30;
            else if (dur <= 600) step = 60;
            else step = 120;
            // 从视口起始取整到 step
            var start = Math.floor(vpStart() / step) * step;
            for (var m = start; m <= vpEnd(); m += step) {
                if (m < vpStart() || m > vpEnd()) continue;
                var tick = document.createElement('span');
                tick.className = 'tl-tick';
                tick.style.left = pctInViewport(m) + '%';
                axis.appendChild(tick);
                var label = document.createElement('span');
                label.className = 'tl-tick-label';
                label.style.left = pctInViewport(m) + '%';
                label.textContent = mmText(m);
                axis.appendChild(label);
            }
        }

        function renderGrid() {
            var grid = document.getElementById('dailyTlGrid');
            grid.innerHTML = '';
            var dur = vpDuration();
            var step = dur <= 90 ? 15 : dur <= 240 ? 30 : dur <= 600 ? 60 : 120;
            var start = Math.floor(vpStart() / step) * step;
            for (var m = start; m <= vpEnd(); m += step) {
                if (m <= vpStart() || m >= vpEnd()) continue;
                var line = document.createElement('span');
                line.className = 'tl-grid-line';
                line.style.left = pctInViewport(m) + '%';
                grid.appendChild(line);
            }
        }

        function renderTimeline() {
            var empty = document.getElementById('dailyTlEmpty');
            var anyOn = state.activeTypes['1'] || state.activeTypes['2'] || state.activeTypes['3'];
            empty.style.display = state.journals.length === 0 ? 'block' : 'none';

            renderAxis();
            renderGrid();

            ['1', '2', '3'].forEach(function (type) {
                var lane = document.getElementById('dailyLane-' + (type === '1' ? 'manual' : type === '2' ? 'todo' : 'focus'));
                var track = lane.querySelector('.daily-tl-lane-track');
                lane.classList.toggle('is-on', state.activeTypes[type]);
                track.innerHTML = '';
                if (!state.activeTypes[type]) return;

                var entries = state.journals.filter(function (j) { return Number(j.type) === Number(type); });
                entries.forEach(function (j) {
                    var start = parseTime(j.start_time);
                    if (isNaN(start)) return;
                    var startMin = minuteOfDay(start);
                    var end = parseTime(j.end_time);
                    // 结束时间为空或 <= 开始：画一个短标记
                    var isTick = isNaN(end) || end <= start;
                    var endMin = isTick ? startMin : Math.max(startMin, minuteOfDay(end));
                    // 跨天（end 在第二天）截断到 24:00
                    if (!isTick && end > start) {
                        var endDate = new Date(end);
                        var startDate = new Date(start);
                        if (endDate.toDateString() !== startDate.toDateString()) {
                            endMin = 1440;
                        }
                    }
                    startMin = Math.min(1440, Math.max(0, startMin));
                    endMin = Math.min(1440, Math.max(startMin, endMin));

                    // 与视口不相交则跳过
                    if (endMin < vpStart() || startMin > vpEnd()) return;

                    var leftPct = pctInViewport(startMin);
                    var rightPct = pctInViewport(endMin);

                    var el = document.createElement('div');
                    var title = (getTypeMeta(j.type).label) + ' · ' + (j.start_time || '') + (j.end_time ? ' ~ ' + j.end_time : '') + '\n' + (j.name || '');
                    if (isTick) {
                        el.className = 'daily-tl-tick';
                        el.style.left = leftPct + '%';
                    } else {
                        el.className = 'daily-tl-bar';
                        el.style.left = leftPct + '%';
                        // 完全在视口内的宽度；超出视口边缘则贴近边界
                        el.style.width = Math.max(0, rightPct - leftPct) + '%';
                    }
                    el.style.background = getTypeColor(j.type);
                    el.title = title;
                    el.setAttribute('data-id', j.id);
                    el.addEventListener('click', function (ev) {
                        ev.stopPropagation();
                        scrollToRecord(j.id);
                    });
                    track.appendChild(el);
                });
            });
        }

        // —— 范围控制（crumbs） ——
        function renderRangebar() {
            var crumbs = document.getElementById('dailyCrumbs');
            crumbs.innerHTML = '';
            var vp = currentViewport();

            var fullCrumb = document.createElement('span');
            fullCrumb.className = 'daily-crumb';
            fullCrumb.innerHTML = '<i class="fas fa-calendar-day"></i>全天';
            fullCrumb.title = '点击显示全天范围';
            fullCrumb.addEventListener('click', function () {
                state.viewport = null;
                state.stack = [];
                renderRangebar();
                renderTimeline();
            });
            crumbs.appendChild(fullCrumb);

            if (vp.start !== 0 || vp.end !== 1440) {
                var sep = document.createElement('span');
                sep.className = 'daily-crumb-sep';
                sep.textContent = '›';
                crumbs.appendChild(sep);

                var rangeCrumb = document.createElement('span');
                rangeCrumb.className = 'daily-crumb';
                rangeCrumb.innerHTML = mmText(vp.start) + ' - ' + mmText(vp.end);
                rangeCrumb.title = '当前显示范围';
                crumbs.appendChild(rangeCrumb);
            }
        }

        function scrollToRecord(id) {
            var el = document.getElementById('dailyRec-' + id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('ring-2', 'ring-blue-300');
                setTimeout(function () { el.classList.remove('ring-2', 'ring-blue-300'); }, 1600);
            }
        }

        // —— 内容列表 ——
        function renderContent() {
            var list = document.getElementById('dailyRecordList');
            var emptyState = document.getElementById('dailyEmptyState');
            var count = document.getElementById('dailyRecordCount');
            var dateLabel = document.getElementById('dailyContentDate');

            dateLabel.textContent = state.date.replace(/-/g, '.') + ' · ' + weekdayName(state.date);

            var visible = state.journals.filter(function (j) { return state.activeTypes[String(j.type)] === true; });
            count.textContent = '共 ' + state.journals.length + ' 条，显示 ' + visible.length + ' 条';

            list.innerHTML = '';
            emptyState.classList.toggle('hidden', visible.length > 0);
            list.classList.toggle('hidden', visible.length === 0);

            visible.forEach(function (j) {
                var meta = getTypeMeta(j.type);
                var start = new Date(String(j.start_time).replace(' ', 'T'));
                var end = j.end_time ? new Date(String(j.end_time).replace(' ', 'T')) : null;
                var timeText = (isNaN(start.getTime()) ? '--' : pad2(start.getHours()) + ':' + pad2(start.getMinutes())) +
                    (end && !isNaN(end.getTime()) ? ' - ' + pad2(end.getHours()) + ':' + pad2(end.getMinutes()) : '');
                var dur = '';
                if (end && !isNaN(end.getTime()) && end > start) {
                    var mins = Math.round((end - start) / 60000);
                    dur = mins < 60 ? mins + ' 分钟' : Math.floor(mins / 60) + ' 小时 ' + (mins % 60 ? (mins % 60) + ' 分' : '');
                }

                var item = document.createElement('div');
                item.className = 'daily-record-item';
                item.id = 'dailyRec-' + j.id;
                item.innerHTML =
                    '<div class="daily-record-time">' +
                        '<span style="color:' + getTypeColor(j.type) + '"><i class="fas ' + meta.icon + '"></i></span> ' + timeText +
                        (dur ? '<div class="text-xs text-gray-400 mt-0.5"><i class="fas fa-hourglass-half"></i> ' + dur + '</div>' : '') +
                    '</div>' +
                    '<div class="daily-record-body">' +
                        '<div class="daily-record-name">' + escapeHtml(j.name || '') + '</div>' +
                        '<div class="text-xs text-gray-400 mt-1 flex items-center gap-2">' +
                            '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium" style="background:' + getTypeColor(j.type) + '20;color:' + getTypeColor(j.type) + '">' +
                                '<i class="fas ' + meta.icon + ' mr-1"></i>' + meta.label +
                            '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="flex items-center gap-2 text-gray-400 flex-none">' +
                        '<a href="/notes?source_type=4&source_id=' + j.id + '" class="hover:text-blue-600" title="记录想法"><i class="fas fa-sticky-note"></i></a>' +
                        '<a href="/journal/' + j.id + '" class="hover:text-green-600" title="编辑"><i class="fas fa-edit"></i></a>' +
                    '</div>';
                list.appendChild(item);
            });
        }

        function weekdayName(dateStr) {
            var names = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
            return names[new Date(dateStr + 'T00:00:00').getDay()];
        }

        // —— 数据加载 ——
        function loadData() {
            $('#dailyDate').val(state.date);
            $.ajax({
                url: '/journals/daily/data',
                type: 'GET',
                dataType: 'json',
                data: { date: state.date },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).done(function (resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) ? resp.msg : '加载失败');
                    return;
                }
                state.journals = (resp.result && resp.result.journals) || [];
                // 加载新日期后重置视图范围，并依据数据计算默认范围（0-8 点无数据则默认 8-24 点）
                state.defaultViewport = computeDefaultViewport();
                state.viewport = null;
                state.stack = [];
                renderRangebar();
                renderTimeline();
                renderContent();
            }).fail(function () {
                alert('加载失败，请重试');
            });
        }

        function setDate(dateStr) {
            state.date = dateStr;
            loadData();
        }

        // —— 事件绑定 ——
        $(document).ready(function () {
            // —— 时间轴拖选时间段（ES/Kibana 风格） ——
            var body = document.getElementById('dailyTlBody');
            var drag = null; // {startMin, minX}

            function xToMin(clientX) {
                var rect = body.getBoundingClientRect();
                var plot = document.getElementById('dailyTlGrid');
                var plotRect = plot.getBoundingClientRect();
                // 只在绘图区（轨道横向区域）内换算，忽略左侧 label 宽度
                var left = plotRect.left;
                var width = plotRect.width || 1;
                var frac = Math.min(1, Math.max(0, (clientX - left) / width));
                return vpStart() + frac * vpDuration();
            }

            function showSelection(a, b) {
                var sel = document.getElementById('dailyTlSelection');
                var min = Math.min(a, b);
                var max = Math.max(a, b);
                var bodyRect = body.getBoundingClientRect();
                var grid = document.getElementById('dailyTlGrid');
                var gridRect = grid.getBoundingClientRect();
                // 选区相对 body 偏移：格子起点(body 内) + 视口内比例
                var offset = gridRect.left - bodyRect.left;
                var leftPx = offset + (Math.min(1440, Math.max(0, min)) - vpStart()) / vpDuration() * gridRect.width;
                var rightPx = offset + (Math.min(1440, Math.max(0, max)) - vpStart()) / vpDuration() * gridRect.width;
                sel.style.display = 'block';
                sel.style.left = leftPx + 'px';
                sel.style.width = Math.max(1, rightPx - leftPx) + 'px';
            }

            body.addEventListener('mousedown', function (e) {
                if (e.button !== 0) return;
                if (e.target.closest('.daily-tl-bar') || e.target.closest('.daily-tl-tick')) return;
                drag = { startMin: xToMin(e.clientX), minX: e.clientX };
                document.getElementById('dailyTlSelection').style.display = 'none';
                e.preventDefault();
            });

            document.addEventListener('mousemove', function (e) {
                if (!drag) return;
                var current = xToMin(e.clientX);
                // 拖动超过阈值才显示选区
                if (Math.abs(e.clientX - drag.minX) > 3) {
                    showSelection(drag.startMin, current);
                }
            });

            document.addEventListener('mouseup', function (e) {
                if (!drag) return;
                var current = xToMin(e.clientX);
                var delta = Math.abs(e.clientX - drag.minX);
                var start = Math.min(drag.startMin, current);
                var end = Math.max(drag.startMin, current);
                drag = null;
                document.getElementById('dailyTlSelection').style.display = 'none';
                if (delta > 6 && (end - start) >= 5) {
                    // 选择并放大到该范围
                    state.stack.push({ start: vpStart(), end: vpEnd() });
                    state.viewport = { start: Math.max(0, start), end: Math.min(1440, end) };
                    renderRangebar();
                    renderTimeline();
                }
            });

            // 双击恢复默认范围
            body.addEventListener('dblclick', function (e) {
                state.viewport = null;
                state.stack = [];
                renderRangebar();
                renderTimeline();
            });

            // 放大范围（返回上一级）
            $('#dailyZoomOut').on('click', function () {
                if (state.stack.length > 0) {
                    state.viewport = state.stack.pop();
                } else {
                    state.viewport = null;
                    state.stack = [];
                }
                renderRangebar();
                renderTimeline();
            });

            // 清除/恢复默认
            $('#dailyReset').on('click', function () {
                state.viewport = null;
                state.stack = [];
                renderRangebar();
                renderTimeline();
            });

            $('#dailyDate').on('change', function () {
                if ($(this).val()) setDate($(this).val());
            });

            $('#dailyPrevDay').on('click', function () { setDate(addDays(state.date, -1)); });
            $('#dailyNextDay').on('click', function () { setDate(addDays(state.date, 1)); });
            $('#dailyToday').on('click', function () { setDate(todayStr); });

            // 模块开关（待办/专注/手动记录）-> 点击切换选中状态，默认全选中
            $('.daily-lane-toggle[data-type!="all"]').on('click', function () {
                var type = $(this).data('type') + '';
                state.activeTypes[type] = !state.activeTypes[type];
                $(this).toggleClass('is-on', state.activeTypes[type]);
                $(this).toggleClass('is-off', !state.activeTypes[type]);
                // 全部按钮状态
                updateAllButton();
                renderTimeline();
                renderContent();
            });

            // 全部按钮：全选或全部取消
            $('.daily-lane-toggle[data-type="all"]').on('click', function () {
                var allOn = state.activeTypes['1'] && state.activeTypes['2'] && state.activeTypes['3'];
                var newVal = !allOn; // 当前不全开则全部打开，全开则全部关闭
                state.activeTypes['1'] = newVal;
                state.activeTypes['2'] = newVal;
                state.activeTypes['3'] = newVal;
                $('.daily-lane-toggle[data-type!="all"]').each(function () {
                    var t = $(this).data('type') + '';
                    $(this).toggleClass('is-on', state.activeTypes[t]);
                    $(this).toggleClass('is-off', !state.activeTypes[t]);
                });
                updateAllButton();
                renderTimeline();
                renderContent();
            });

            function updateAllButton() {
                var allOn = state.activeTypes['1'] && state.activeTypes['2'] && state.activeTypes['3'];
                var allOff = !state.activeTypes['1'] && !state.activeTypes['2'] && !state.activeTypes['3'];
                var allBtn = $('.daily-lane-toggle[data-type="all"]');
                if (allOff) {
                    allBtn.addClass('is-off').removeClass('is-on');
                } else {
                    allBtn.addClass('is-on').removeClass('is-off');
                    if (!allOn) allBtn.removeClass('is-off');
                }
            }

            loadData();
        });
    })();
</script>
@endsection
