@extends('layouts.app')

@section('title', '学习打卡列表 - 蒙太奇')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">学习打卡列表</h1>
                <p class="text-sm text-gray-500 mt-1">查看学习任务打卡记录</p>
            </div>
            <a href="{{ url('/study') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回学习页</a>
        </div>

        <div class="card p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="text-sm text-gray-700">开始日期</label>
                    <input id="dateFrom" type="date" class="input w-full mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-700">结束日期</label>
                    <input id="dateTo" type="date" class="input w-full mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-700">每页数量</label>
                    <select id="pageSize" class="input w-full mt-1">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-primary w-full justify-center" onclick="loadCheckins(1)">查询</button>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div id="checkinList" class="divide-y divide-gray-100">
                <div class="text-sm text-gray-500 py-2">加载中...</div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                <button id="prevBtn" class="btn btn-outline btn-sm" onclick="changePage(-1)">上一页</button>
                <div id="pageInfo" class="text-sm text-gray-600">-</div>
                <button id="nextBtn" class="btn btn-outline btn-sm" onclick="changePage(1)">下一页</button>
            </div>
        </div>
    </div>

    <div id="checkinPopover" class="hidden fixed"></div>

    <style>
        .checkin-row { cursor: default; transition: background-color .12s; }
        .checkin-row:hover { background: #f8fafc; }
        #checkinPopover { z-index: 1000; }
        #checkinPopover.show { display: block; }
    </style>

    <script>
        let pageState = {
            current: 1,
            last: 1,
            total: 0,
            perPage: 20,
        };

        async function requestApi(path, options = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const opts = Object.assign({ method: 'GET' }, options);
            opts.headers = Object.assign({
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }, options.headers || {});
            const resp = await fetcher('/api/v2' + path, opts);
            return resp.json();
        }

        function getResult(resp) {
            return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {};
        }

        function escapeHtml(raw) {
            return String(raw || '').replace(/[&<>"']/g, function(m) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
            });
        }

        let checkinData = [];
        let popoverHideTimer = null;
        const popover = document.getElementById('checkinPopover');

        function mediaIcons(it) {
            let icons = '';
            if (it.audio_path) icons += '<i class="fas fa-microphone text-emerald-600" title="有语音打卡"></i>';
            if (it.image_path) icons += '<i class="fas fa-image text-blue-600" title="有图片"></i>';
            if (it.video_path) icons += '<i class="fas fa-video text-purple-600" title="有视频"></i>';
            return icons;
        }

        function renderList(items) {
            const node = document.getElementById('checkinList');
            if (!items || !items.length) {
                checkinData = [];
                node.innerHTML = '<div class="text-sm text-gray-500 py-2">暂无打卡记录。</div>';
                return;
            }
            checkinData = items.map(function(it) { return it; });
            node.innerHTML = items.map(function(it, i) {
                const icons = mediaIcons(it);
                return `
                    <div class="checkin-row flex items-center justify-between gap-3 px-3 py-2.5"
                         data-idx="${i}"
                         onmouseenter="showCheckinCard(this, ${i})"
                         onmouseleave="scheduleHideCheckinCard()">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-xs font-medium text-gray-900 truncate">${escapeHtml(it.task_name || '学习任务')}</span>
                            <span class="text-[11px] text-gray-400 whitespace-nowrap shrink-0">${escapeHtml(it.checkin_date || '')}</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-gray-500 shrink-0">
                            ${icons ? '<span class="flex items-center gap-1.5">' + icons + '</span>' : ''}
                            <span class="text-gray-300">·</span>
                            <span class="whitespace-nowrap">${escapeHtml(it.planned_start_time || '')}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function buildCheckinCardHtml(it) {
            return `
                <div class="w-80 max-w-[85vw] rounded-xl border border-gray-200 bg-white shadow-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="font-semibold text-gray-900 min-w-0">${escapeHtml(it.task_name || '学习任务')}</div>
                        <div class="text-xs text-gray-500 whitespace-nowrap shrink-0">${escapeHtml(it.checkin_date || '')}</div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">计划时间：${escapeHtml(it.planned_start_time || '-')}</div>
                    <div class="text-sm text-gray-700 mt-3 whitespace-pre-wrap max-h-64 overflow-y-auto">${escapeHtml(it.content || '（无文字打卡内容）')}</div>
                    <div class="text-xs text-gray-500 mt-3 pt-2 border-t border-gray-100 flex items-center gap-3">
                        <span class="flex items-center gap-1"><i class="fas fa-microphone text-emerald-600"></i>音频：${it.audio_path ? '有' : '无'}</span>
                        <span class="flex items-center gap-1"><i class="fas fa-image text-blue-600"></i>图片：${it.image_path ? '有' : '无'}</span>
                        <span class="flex items-center gap-1"><i class="fas fa-video text-purple-600"></i>视频：${it.video_path ? '有' : '无'}</span>
                    </div>
                </div>
            `;
        }

        function showCheckinCard(rowEl, idx) {
            clearTimeout(popoverHideTimer);
            const it = checkinData[idx] || {};
            popover.innerHTML = buildCheckinCardHtml(it);
            popover.classList.add('show');
            popover.style.visibility = 'hidden';
            const rect = rowEl.getBoundingClientRect();
            const pw = popover.offsetWidth;
            const ph = popover.offsetHeight;
            let left = Math.max(8, rect.left);
            let top = rect.bottom + 8;
            if (left + pw > window.innerWidth - 8) left = Math.max(8, window.innerWidth - pw - 8);
            if (top + ph > window.innerHeight - 8) top = Math.max(8, rect.top - ph - 8);
            popover.style.left = left + 'px';
            popover.style.top = top + 'px';
            popover.style.visibility = 'visible';
        }

        function scheduleHideCheckinCard() {
            clearTimeout(popoverHideTimer);
            popoverHideTimer = setTimeout(function() {
                popover.classList.remove('show');
            }, 150);
        }

        popover.addEventListener('mouseenter', function() { clearTimeout(popoverHideTimer); });
        popover.addEventListener('mouseleave', scheduleHideCheckinCard);
        window.addEventListener('scroll', function() { popover.classList.remove('show'); }, true);
        window.addEventListener('resize', function() { popover.classList.remove('show'); });

        function updatePager() {
            document.getElementById('pageInfo').textContent = `第 ${pageState.current} / ${pageState.last} 页，共 ${pageState.total} 条`;
            document.getElementById('prevBtn').disabled = pageState.current <= 1;
            document.getElementById('nextBtn').disabled = pageState.current >= pageState.last;
        }

        async function loadCheckins(page) {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const pageSize = Number(document.getElementById('pageSize').value || 20);
            const params = new URLSearchParams();
            params.set('page', String(page || 1));
            params.set('page_size', String(pageSize));
            if (dateFrom) params.set('date_from', dateFrom);
            if (dateTo) params.set('date_to', dateTo);

            const resp = await requestApi('/study/checkins?' + params.toString());
            if (!resp || Number(resp.code) !== 9999) {
                alert(resp && resp.msg ? resp.msg : '加载失败');
                return;
            }

            const data = getResult(resp);
            const pagination = data.pagination || {};
            pageState.current = Number(pagination.current_page || 1);
            pageState.last = Math.max(1, Number(pagination.last_page || 1));
            pageState.total = Number(pagination.total || 0);
            pageState.perPage = Number(pagination.per_page || pageSize);
            renderList(data.items || []);
            updatePager();
        }

        function changePage(delta) {
            const next = pageState.current + delta;
            if (next < 1 || next > pageState.last) return;
            loadCheckins(next);
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadCheckins(1);
        });
    </script>
@endsection
