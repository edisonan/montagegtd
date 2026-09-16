@extends('layouts.app')

@section('title', '京城公交收藏馆 - 蒙太奇')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .bus-hero {
            background: radial-gradient(circle at top right, rgba(14,165,233,0.18), rgba(255,255,255,0.2)),
                        linear-gradient(120deg, #0f172a, #1e293b);
            color: #f8fafc;
            border-radius: 18px;
        }
        .bus-stat {
            border: 1px solid rgba(148,163,184,0.35);
            background: rgba(15,23,42,0.35);
            border-radius: 12px;
        }
        .bus-tab {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: all .15s;
        }
        .bus-tab.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }
        .bus-card {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #fff;
            transition: transform .15s ease, box-shadow .15s ease;
            cursor: pointer;
        }
        .bus-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 30px -20px rgba(15,23,42,.55);
        }
        .bus-card.locked { opacity: .92; }
        .bus-card-strip { height: 8px; }
        .bus-progress { height: 7px; border-radius: 999px; background: #eef2f7; overflow: hidden; }
        .bus-progress-inner { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #38bdf8, #22c55e); transition: width .35s ease; }
        .rarity-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            color: #fff;
            letter-spacing: .04em;
        }
        .rarity-N { background: #94a3b8; }
        .rarity-R { background: #3b82f6; }
        .rarity-SR { background: #8b5cf6; }
        .rarity-SSR { background: linear-gradient(90deg, #f59e0b, #ef4444); }
        .bus-modal {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1300;
            padding: 16px;
        }
        .bus-modal.show { display: flex; }
        .bus-modal-card {
            width: min(860px, 96vw);
            max-height: 90vh;
            overflow: auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 28px 60px -30px rgba(15,23,42,.7);
        }
        .station-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0;
        }
        .station-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #cbd5e1;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #cbd5e1;
            flex: 0 0 auto;
        }
        .station-dot.checked { background: #22c55e; box-shadow: 0 0 0 1px #22c55e; }
        .station-dot.next { background: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.2); }
        .achievement-item {
            border: 1px solid #eef2f7;
            border-radius: 12px;
            padding: 10px 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .achievement-item.unlocked { background: #f8fafc; }
        .achievement-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: #f1f5f9; color: #64748b; flex: 0 0 auto;
        }
        .achievement-item.unlocked .achievement-icon { background: #fef3c7; color: #b45309; }
        .bus-toast {
            position: fixed;
            right: 20px;
            bottom: 24px;
            z-index: 1400;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .bus-toast-item {
            background: #0f172a;
            color: #fff;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            box-shadow: 0 14px 30px -18px rgba(15,23,42,.9);
            animation: toastIn .2s ease;
        }
        .bus-toast-item.success { background: #065f46; }
        .bus-toast-item.error { background: #991b1b; }
        @keyframes toastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .bus-map-canvas { width: 100%; height: 320px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f1f5f9; z-index: 1; }
        .bus-marker-icon {
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; line-height: 1;
            filter: drop-shadow(0 4px 6px rgba(15,23,42,.4));
            transition: transform .1s linear;
        }
        .bus-station-icon {
            width: 12px; height: 12px; border-radius: 999px;
            background: #94a3b8; border: 2px solid #fff;
            box-shadow: 0 0 0 1px #94a3b8;
        }
        .bus-station-icon.checked { background: #22c55e; box-shadow: 0 0 0 1px #22c55e; }
        .bus-station-icon.next { background: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.25); }
        .leaflet-container { font: inherit; }
        .map-note { font-size: 12px; color: #64748b; margin-top: 6px; }
        .drive-controls { display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
    </style>

    <div class="max-w-6xl mx-auto">
        <div class="bus-hero p-6 mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fas fa-bus"></i> 京城公交收藏馆</h1>
                    <p class="text-sm text-slate-300 mt-1">收集北京真实公交 · 地铁 · BRT · 夜班线路，逐站打卡，集齐成就徽章</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bus-stat px-4 py-2 text-center">
                        <div class="text-xs text-slate-300">可用积分</div>
                        <div class="text-lg font-bold text-amber-300" id="apBalance">-- AP</div>
                    </div>
                    <a href="/point-mall" class="btn btn-outline btn-sm" style="border-color:rgba(255,255,255,.5);color:#fff;"><i class="fas fa-arrow-left mr-1"></i>返回商城</a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
                <div class="bus-stat px-4 py-3">
                    <div class="text-xs text-slate-300">收藏度 · 称号</div>
                    <div class="text-xl font-bold" id="statScore">0</div>
                    <div class="text-xs text-sky-300" id="statTitle">交通萌新</div>
                </div>
                <div class="bus-stat px-4 py-3">
                    <div class="text-xs text-slate-300">已收藏线路</div>
                    <div class="text-xl font-bold" id="statCollected">0 / 0</div>
                    <div class="text-xs text-slate-400" id="statNextTitle"></div>
                </div>
                <div class="bus-stat px-4 py-3">
                    <div class="text-xs text-slate-300">全线贯通</div>
                    <div class="text-xl font-bold text-emerald-300" id="statCompleted">0</div>
                    <div class="text-xs text-slate-400">完成全部站点打卡</div>
                </div>
                <div class="bus-stat px-4 py-3">
                    <div class="text-xs text-slate-300">累计打卡站点</div>
                    <div class="text-xl font-bold text-sky-300" id="statCheckins">0</div>
                    <div class="text-xs text-emerald-300" id="statFreeHint">今日首站免费</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="card mb-4">
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between flex-wrap gap-2">
                        <div class="font-semibold text-gray-900"><i class="fas fa-map-marked-alt text-sky-600 mr-1"></i>线路图鉴</div>
                        <div class="flex flex-wrap gap-2" id="typeTabs"></div>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="lineGrid">加载中...</div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-4 border-b border-gray-200 font-semibold text-gray-900"><i class="fas fa-history text-gray-500 mr-1"></i>最近打卡</div>
                    <div class="p-4 space-y-2" id="recentList">加载中...</div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="card">
                    <div class="p-4 border-b border-gray-200 font-semibold text-gray-900"><i class="fas fa-medal text-amber-500 mr-1"></i>成就徽章</div>
                    <div class="p-4 space-y-3" id="achievementList">加载中...</div>
                </div>
                <div class="card">
                    <div class="p-4 border-b border-gray-200 font-semibold text-gray-900"><i class="fas fa-trophy text-amber-500 mr-1"></i>收藏排行榜</div>
                    <div class="p-4 space-y-2" id="leaderboard">加载中...</div>
                </div>
            </div>
        </div>
    </div>

    <div id="lineModal" class="bus-modal" onclick="closeLineModal(event)">
        <div class="bus-modal-card" onclick="event.stopPropagation()">
            <div id="lineModalBody"></div>
        </div>
    </div>

    <div class="bus-toast" id="toastBox"></div>

    <script>
        const OSRM_ENDPOINTS = [
            'https://router.project-osrm.org/route/v1/driving/',
            'https://routing.openstreetmap.de/routed-car/route/v1/driving/'
        ];
        const TYPE_LABELS = { bus: '常规公交', brt: '快速公交', night: '夜班车', subway: '地铁', sightseeing: '观光专线' };
        const TYPE_ICONS = { bus: 'fa-bus', brt: 'fa-bolt', night: 'fa-moon', subway: 'fa-subway', sightseeing: 'fa-camera' };
        const RARITY_LABELS = { N: '普通', R: '稀有', SR: '史诗', SSR: '传说' };

        let overviewData = null;
        let activeFilter = 'all';
        let activeLineId = 0;
        let leafletMap = null;
        let drive = { raf: null, marker: null, path: [], cumulative: [], total: 0, startedAt: 0, playing: false, line: null };
        const routeCache = {};

        function getResultData(resp) { return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {}; }
        function api(path, opts) {
            opts = opts || {};
            const fetcher = window.taskApiFetch || window.fetch;
            const tokenNode = document.querySelector('meta[name="csrf-token"]');
            const csrf = tokenNode ? tokenNode.getAttribute('content') : '';
            const options = Object.assign({ method: 'GET' }, opts);
            options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
            if (options.method !== 'GET') options.headers['X-CSRF-TOKEN'] = csrf;
            return fetcher('/api/v2' + path, options).then(function(r) {
                return r.text();
            }).then(function(text) {
                try { return JSON.parse(text || '{}'); } catch (e) { return { code: 0, msg: '响应解析失败' }; }
            });
        }

        function escapeHtml(text) {
            return String(text == null ? '' : text).replace(/[&<>"']/g, function(c) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
            });
        }

        function showToast(message, type) {
            const box = document.getElementById('toastBox');
            if (!box) return;
            const item = document.createElement('div');
            item.className = 'bus-toast-item ' + (type || '');
            item.textContent = message;
            box.appendChild(item);
            setTimeout(function() { item.remove(); }, 3200);
        }

        function render(data) {
            overviewData = data;
            const stats = data.stats || {};
            const account = data.account || {};

            document.getElementById('apBalance').textContent = Number(account.ap_balance || 0) + ' AP';
            document.getElementById('statScore').textContent = Number(stats.score || 0);
            document.getElementById('statTitle').textContent = stats.title || '交通萌新';
            document.getElementById('statCollected').textContent = Number(stats.collected_lines || 0) + ' / ' + Number(stats.total_lines || 0);
            document.getElementById('statCompleted').textContent = Number(stats.completed_lines || 0);
            document.getElementById('statCheckins').textContent = Number(stats.checked_stations || 0) + ' / ' + Number(stats.total_stations || 0);
            if (stats.next_title) {
                document.getElementById('statNextTitle').textContent = '距离「' + stats.next_title + '」还差 ' + Math.max(0, Number(stats.next_title_score || 0) - Number(stats.score || 0)) + ' 分';
            } else {
                document.getElementById('statNextTitle').textContent = '已达最高称号';
            }
            document.getElementById('statFreeHint').textContent = stats.free_checkin_available ? '今日首站免费' : '今日免费机会已用';

            renderTabs(data.type_stats || []);
            renderLines();
            renderAchievements(data.achievements || []);
            renderLeaderboard(data.leaderboard || []);
            renderRecent(data.recent_checkins || []);
        }

        function typeCount(type) {
            if (!overviewData) return 0;
            if (type === 'all') return (overviewData.lines || []).length;
            return (overviewData.lines || []).filter(function(l) { return l.type === type; }).length;
        }

        function renderTabs(typeStats) {
            const tabs = [{ type: 'all', label: '全部' }];
            (typeStats || []).forEach(function(t) {
                tabs.push({ type: t.type, label: TYPE_LABELS[t.type] || t.type });
            });
            document.getElementById('typeTabs').innerHTML = tabs.map(function(t) {
                return '<button class="bus-tab ' + (activeFilter === t.type ? 'active' : '') + '" onclick="setFilter(\'' + t.type + '\')">' +
                    escapeHtml(t.label) + ' (' + typeCount(t.type) + ')</button>';
            }).join('');
        }

        function setFilter(type) {
            activeFilter = type;
            renderTabs((overviewData && overviewData.type_stats) || []);
            renderLines();
        }

        function renderLines() {
            const grid = document.getElementById('lineGrid');
            if (!overviewData) return;
            const lines = (overviewData.lines || []).filter(function(l) {
                return activeFilter === 'all' || l.type === activeFilter;
            });
            if (!lines.length) {
                grid.innerHTML = '<div class="text-sm text-gray-500 col-span-2">暂无线路</div>';
                return;
            }
            grid.innerHTML = lines.map(function(l) {
                const percent = l.station_count ? Math.round(l.progress / l.station_count * 100) : 0;
                let actionHtml;
                if (!l.unlocked) {
                    const priceText = l.is_free || Number(l.price_ap) === 0 ? '免费领取' : (Number(l.price_ap) + ' AP 解锁');
                    actionHtml = '<button class="btn btn-primary btn-sm w-full" onclick="event.stopPropagation(); unlockLine(' + l.id + ')">' + priceText + '</button>';
                } else if (l.completed) {
                    actionHtml = '<button class="btn btn-outline btn-sm w-full" disabled style="color:#059669;border-color:#a7f3d0;background:#ecfdf5;">已全线贯通</button>';
                } else {
                    actionHtml = '<button class="btn btn-primary btn-sm w-full" onclick="event.stopPropagation(); checkinLine(' + l.id + ')">' +
                        '打卡 · ' + escapeHtml(l.next_station || '下一站') + '</button>';
                }
                return '' +
                '<div class="bus-card ' + (l.unlocked ? '' : 'locked') + '" onclick="openLineModal(' + l.id + ')">' +
                    '<div class="bus-card-strip" style="background:' + escapeHtml(l.color) + '"></div>' +
                    '<div class="p-4">' +
                        '<div class="flex items-start justify-between gap-2">' +
                            '<div>' +
                                '<div class="font-bold text-gray-900">' + escapeHtml(l.name) + '</div>' +
                                '<div class="text-xs text-gray-500 mt-1"><i class="fas ' + (TYPE_ICONS[l.type] || 'fa-bus') + ' mr-1"></i>' + escapeHtml(TYPE_LABELS[l.type] || l.type) + ' · ' + escapeHtml(l.district) + '</div>' +
                            '</div>' +
                            '<span class="rarity-badge rarity-' + escapeHtml(l.rarity) + '">' + escapeHtml(l.rarity) + '</span>' +
                        '</div>' +
                        '<div class="text-xs text-gray-500 mt-3">站点 ' + Number(l.station_count) + ' · ' + Number(l.distance_km) + ' km · ' + escapeHtml(l.first_bus || '--') + '-' + escapeHtml(l.last_bus || '--') + '</div>' +
                        '<div class="bus-progress mt-3"><div class="bus-progress-inner" style="width:' + percent + '%"></div></div>' +
                        '<div class="flex items-center justify-between text-xs text-gray-500 mt-1 mb-3">' +
                            '<span>' + (l.unlocked ? ('进度 ' + l.progress + '/' + l.station_count) : '尚未解锁') + '</span>' +
                            '<span>' + (l.unlocked ? ('收藏度 +' + l.score) : ('+' + l.base_score + ' 分')) + '</span>' +
                        '</div>' +
                        actionHtml +
                    '</div>' +
                '</div>';
            }).join('');
        }

        function renderAchievements(list) {
            const node = document.getElementById('achievementList');
            if (!list.length) { node.innerHTML = '<div class="text-sm text-gray-500">暂无成就</div>'; return; }
            node.innerHTML = list.map(function(a) {
                let action = '';
                if (a.unlocked && !a.claimed) {
                    action = '<button class="btn btn-primary btn-sm" onclick="claimAchievement(\'' + a.code + '\')">领取 +' + a.reward_ap + '</button>';
                } else if (a.claimed) {
                    action = '<span class="text-xs text-emerald-600 font-medium"><i class="fas fa-check-circle mr-1"></i>已领取</span>';
                } else {
                    action = '<span class="text-xs text-gray-400">+' + a.reward_ap + ' AP</span>';
                }
                return '' +
                '<div class="achievement-item ' + (a.unlocked ? 'unlocked' : '') + '">' +
                    '<div class="achievement-icon"><i class="fas ' + escapeHtml(a.badge_icon || 'fa-medal') + '"></i></div>' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center justify-between gap-2">' +
                            '<div class="font-medium text-sm text-gray-900 truncate">' + escapeHtml(a.name) + '</div>' + action +
                        '</div>' +
                        '<div class="text-xs text-gray-500 mt-1">' + escapeHtml(a.description) + '</div>' +
                        '<div class="bus-progress mt-2"><div class="bus-progress-inner" style="width:' + Number(a.percent || 0) + '%"></div></div>' +
                        '<div class="text-xs text-gray-400 mt-1">' + Number(a.current || 0) + ' / ' + Number(a.target || 1) + '</div>' +
                    '</div>' +
                '</div>';
            }).join('');
        }

        function renderLeaderboard(list) {
            const node = document.getElementById('leaderboard');
            if (!list.length) { node.innerHTML = '<div class="text-sm text-gray-500">暂无上榜收藏家</div>'; return; }
            node.innerHTML = list.map(function(r, idx) {
                return '' +
                '<div class="flex items-center justify-between border-b border-gray-100 pb-2">' +
                    '<div class="flex items-center gap-2 min-w-0">' +
                        '<span class="w-6 text-center font-semibold ' + (idx < 3 ? 'text-amber-600' : 'text-gray-400') + '">' + (idx + 1) + '</span>' +
                        '<span class="font-medium text-gray-900 truncate">' + escapeHtml(r.name) + '</span>' +
                        '<span class="text-xs text-gray-400">' + escapeHtml(r.title || '') + '</span>' +
                    '</div>' +
                    '<span class="text-sky-700 font-semibold">' + Number(r.score || 0) + '</span>' +
                '</div>';
            }).join('');
        }

        function renderRecent(list) {
            const node = document.getElementById('recentList');
            if (!list.length) { node.innerHTML = '<div class="text-sm text-gray-500">还没有打卡记录，去解锁一条线路吧</div>'; return; }
            node.innerHTML = list.map(function(r) {
                return '' +
                '<div class="flex items-center justify-between border border-gray-100 rounded-lg px-3 py-2">' +
                    '<div class="flex items-center gap-2 min-w-0">' +
                        '<span style="width:8px;height:8px;border-radius:999px;background:' + escapeHtml(r.line_color || '#0ea5e9') + ';display:inline-block;"></span>' +
                        '<span class="text-sm text-gray-800 truncate">' + escapeHtml(r.line_name) + ' · ' + escapeHtml(r.station_name) + '</span>' +
                    '</div>' +
                    '<div class="text-xs text-gray-500 whitespace-nowrap ml-2">' +
                        (Number(r.ap_cost) > 0 ? ('-' + r.ap_cost + ' AP') : '免费') +
                        (Number(r.reward_ap) > 0 ? (' · +' + r.reward_ap + ' AP') : '') +
                    '</div>' +
                '</div>';
            }).join('');
        }

        function findLine(id) {
            return ((overviewData && overviewData.lines) || []).filter(function(l) { return Number(l.id) === Number(id); })[0] || null;
        }

        function openLineModal(id) {
            const line = findLine(id);
            if (!line) return;
            activeLineId = Number(id);
            const percent = line.station_count ? Math.round(line.progress / line.station_count * 100) : 0;

            let actionHtml;
            if (!line.unlocked) {
                const priceText = line.is_free || Number(line.price_ap) === 0 ? '免费领取线路' : ('消耗 ' + line.price_ap + ' AP 解锁');
                actionHtml = '<button class="btn btn-primary" onclick="unlockLine(' + line.id + ')">' + priceText + '</button>';
            } else if (line.completed) {
                actionHtml = '<span class="inline-flex items-center text-emerald-600 font-medium"><i class="fas fa-check-circle mr-1"></i>已全线贯通</span>';
            } else {
                actionHtml = '<button class="btn btn-primary" onclick="checkinLine(' + line.id + ')">打卡下一站 · ' + escapeHtml(line.next_station || '') + '</button>';
            }
            const driveButton = line.unlocked
                ? '<button class="btn btn-outline" id="driveToggleBtn" onclick="toggleDrive(' + line.id + ')"><i class="fas fa-play mr-1"></i>发车巡线</button>'
                : '';
            const stationsHtml = (line.stations || []).map(function(s, idx) {
                const isNext = line.unlocked && !line.completed && idx === line.progress;
                const cls = s.checked ? 'checked' : (isNext ? 'next' : '');
                return '' +
                '<div class="station-item">' +
                    '<span class="station-dot ' + cls + '"></span>' +
                    '<span class="text-sm ' + (s.checked ? 'text-gray-800' : 'text-gray-500') + '">' + escapeHtml(s.name) + '</span>' +
                    (isNext ? '<span class="text-xs text-sky-600 ml-1">下一站</span>' : '') +
                '</div>';
            }).join('');

            const body = '' +
            '<div style="height:8px;background:' + escapeHtml(line.color) + '"></div>' +
            '<div class="p-5">' +
                '<div class="flex items-start justify-between gap-3">' +
                    '<div>' +
                        '<div class="flex items-center gap-2">' +
                            '<h2 class="text-xl font-bold text-gray-900">' + escapeHtml(line.name) + '</h2>' +
                            '<span class="rarity-badge rarity-' + escapeHtml(line.rarity) + '">' + escapeHtml(line.rarity) + ' · ' + escapeHtml(RARITY_LABELS[line.rarity] || '') + '</span>' +
                        '</div>' +
                        '<p class="text-sm text-gray-500 mt-1">' + escapeHtml(line.description) + '</p>' +
                    '</div>' +
                    '<button class="text-gray-400 hover:text-gray-600" onclick="closeLineModal()"><i class="fas fa-times text-lg"></i></button>' +
                '</div>' +
                '<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">' +
                    '<div class="border border-gray-100 rounded-lg p-2 text-center"><div class="text-xs text-gray-500">运营区间</div><div class="text-sm font-medium text-gray-800">' + escapeHtml(line.first_bus || '--') + ' - ' + escapeHtml(line.last_bus || '--') + '</div></div>' +
                    '<div class="border border-gray-100 rounded-lg p-2 text-center"><div class="text-xs text-gray-500">站点数</div><div class="text-sm font-medium text-gray-800">' + Number(line.station_count) + ' 站</div></div>' +
                    '<div class="border border-gray-100 rounded-lg p-2 text-center"><div class="text-xs text-gray-500">线路长度</div><div class="text-sm font-medium text-gray-800">' + Number(line.distance_km) + ' km</div></div>' +
                    '<div class="border border-gray-100 rounded-lg p-2 text-center"><div class="text-xs text-gray-500">贯通奖励</div><div class="text-sm font-medium text-amber-600">+' + Number(line.reward_ap) + ' AP</div></div>' +
                '</div>' +
                '<div class="mt-4">' +
                    '<div class="flex items-center justify-between text-sm text-gray-600 mb-1"><span>打卡进度</span><span>' + line.progress + ' / ' + line.station_count + '（' + percent + '%）</span></div>' +
                    '<div class="bus-progress"><div class="bus-progress-inner" style="width:' + percent + '%"></div></div>' +
                '</div>' +
                '<div class="flex items-center justify-between gap-2 mt-4 flex-wrap">' +
                    '<div class="text-sm text-gray-600">' + (line.unlocked ? ('打卡消耗 ' + line.checkin_cost + ' AP（每日首站免费）') : '解锁后可逐站打卡收藏') + '</div>' +
                    '<div class="flex items-center gap-2">' + actionHtml + '</div>' +
                '</div>' +
                '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">' +
                    '<div>' +
                        '<div class="font-semibold text-gray-900 mb-2"><i class="fas fa-map-signs text-sky-600 mr-1"></i>站点时间轴</div>' +
                        '<div style="max-height:280px;overflow:auto;padding-right:6px;">' + stationsHtml + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="font-semibold text-gray-900 mb-2"><i class="fas fa-map text-sky-600 mr-1"></i>真实线路地图</div>' +
                        '<div id="lineMap" class="bus-map-canvas"></div>' +
                        '<div class="drive-controls">' +
                            driveButton +
                            '<span class="map-note" id="mapNote">正在基于 OpenStreetMap 规划真实道路路径…</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

            document.getElementById('lineModalBody').innerHTML = body;
            document.getElementById('lineModal').classList.add('show');
            drawLineMap(line);
        }

        function closeLineModal(event) {
            if (event && event.target !== event.currentTarget) return;
            stopDrive();
            destroyMap();
            document.getElementById('lineModal').classList.remove('show');
            activeLineId = 0;
        }

        function destroyMap() {
            stopDrive();
            if (leafletMap) {
                try { leafletMap.remove(); } catch (e) {}
                leafletMap = null;
            }
        }

        function stationPoints(line) {
            return (line.stations || []).filter(function(s) {
                return s.lng != null && s.lat != null;
            }).map(function(s) {
                return [Number(s.lat), Number(s.lng)];
            });
        }

        function routeStorageKey(code) { return 'busroute_v1_' + code; }

        function readCachedRoute(code) {
            try {
                const raw = window.localStorage.getItem(routeStorageKey(code));
                if (!raw) return null;
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) && parsed.length > 1 ? parsed : null;
            } catch (e) { return null; }
        }

        function writeCachedRoute(code, path) {
            try { window.localStorage.setItem(routeStorageKey(code), JSON.stringify(path)); } catch (e) {}
        }

        function osrmRoute(line) {
            const straight = stationPoints(line);
            if (line.type === 'subway' || straight.length < 2) {
                return Promise.resolve(straight);
            }
            const cached = readCachedRoute(line.code);
            if (cached) { routeCache[line.code] = cached; return Promise.resolve(cached); }
            const coords = straight.map(function(p) { return p[1] + ',' + p[0]; }).join(';');
            const query = coords + '?overview=full&geometries=geojson&steps=false';

            function attempt(index) {
                if (index >= OSRM_ENDPOINTS.length) return Promise.resolve(straight);
                return fetch(OSRM_ENDPOINTS[index] + query).then(function(r) { return r.json(); }).then(function(json) {
                    if (!json || json.code !== 'Ok' || !json.routes || !json.routes.length) {
                        return attempt(index + 1);
                    }
                    const geometry = json.routes[0].geometry && json.routes[0].geometry.coordinates;
                    if (!Array.isArray(geometry) || geometry.length < 2) {
                        return attempt(index + 1);
                    }
                    const path = geometry.map(function(c) { return [Number(c[1]), Number(c[0])]; });
                    writeCachedRoute(line.code, path);
                    routeCache[line.code] = path;
                    return path;
                }).catch(function() { return attempt(index + 1); });
            }

            return attempt(0);
        }

        function haversine(a, b) {
            const R = 6371000;
            const dLat = (b[0] - a[0]) * Math.PI / 180;
            const dLng = (b[1] - a[1]) * Math.PI / 180;
            const lat1 = a[0] * Math.PI / 180;
            const lat2 = b[0] * Math.PI / 180;
            const h = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return 2 * R * Math.asin(Math.min(1, Math.sqrt(h)));
        }

        function buildCumulative(path) {
            const cumulative = [0];
            for (let i = 1; i < path.length; i++) {
                cumulative.push(cumulative[i - 1] + haversine(path[i - 1], path[i]));
            }
            return cumulative;
        }

        function pointAtDistance(path, cumulative, dist) {
            if (path.length < 2) return path[0] || [39.90923, 116.397428];
            for (let i = 1; i < cumulative.length; i++) {
                if (dist <= cumulative[i]) {
                    const seg = (cumulative[i] - cumulative[i - 1]) || 1;
                    const t = (dist - cumulative[i - 1]) / seg;
                    const a = path[i - 1], b = path[i];
                    return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
                }
            }
            return path[path.length - 1];
        }

        function busIcon() {
            return L.divIcon({ className: 'bus-marker-icon', html: '🚌', iconSize: [30, 30], iconAnchor: [15, 15] });
        }

        function findStationPathIndex(line, path) {
            if (!line.stations || !line.stations.length) return 0;
            const idx = Math.max(0, Math.min(line.stations.length - 1, line.progress));
            const target = line.stations[idx];
            if (!target || target.lat == null) return 0;
            let best = 0, bestDist = Infinity;
            for (let i = 0; i < path.length; i++) {
                const d = haversine([Number(target.lat), Number(target.lng)], path[i]);
                if (d < bestDist) { bestDist = d; best = i; }
            }
            return best;
        }

        function drawLineMap(line) {
            const node = document.getElementById('lineMap');
            if (!node) return;
            if (typeof L === 'undefined') {
                node.innerHTML = '<div class="h-full w-full flex items-center justify-center text-gray-500 text-sm">地图组件加载失败（请检查网络是否可访问 CDN）</div>';
                return;
            }
            destroyMap();
            osrmRoute(line).then(function(path) {
                const current = document.getElementById('lineMap');
                if (!current || activeLineId !== Number(line.id)) return;
                if (!path || path.length < 2) {
                    current.innerHTML = '<div class="h-full w-full flex items-center justify-center text-gray-500 text-sm">暂无坐标数据</div>';
                    return;
                }

                leafletMap = L.map('lineMap', { zoomControl: true, attributionControl: true });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(leafletMap);

                const routeLine = L.polyline(path, { color: line.color || '#2563eb', weight: 6, opacity: 0.85 }).addTo(leafletMap);

                (line.stations || []).forEach(function(s, idx) {
                    if (s.lng == null || s.lat == null) return;
                    const isNext = line.unlocked && !line.completed && idx === line.progress;
                    const cls = s.checked ? 'checked' : (isNext ? 'next' : '');
                    const marker = L.marker([Number(s.lat), Number(s.lng)], {
                        icon: L.divIcon({ className: 'bus-station-icon ' + cls, iconSize: [12, 12], iconAnchor: [6, 6] })
                    }).addTo(leafletMap);
                    marker.bindTooltip((idx + 1) + '. ' + s.name, { direction: 'top' });
                });

                const startIndex = Math.max(0, Math.min(path.length - 1, line.progress > 0 ? findStationPathIndex(line, path) : 0));
                drive.path = path;
                drive.cumulative = buildCumulative(path);
                drive.total = drive.cumulative[drive.cumulative.length - 1] || 0;
                drive.marker = L.marker(path[startIndex], { icon: busIcon(), zIndexOffset: 1000 }).addTo(leafletMap);
                drive.line = line;
                drive.playing = false;
                drive.pauseProgress = 0;
                updateDriveButton();

                leafletMap.fitBounds(routeLine.getBounds(), { padding: [24, 24] });

                const note = document.getElementById('mapNote');
                if (note) {
                    note.textContent = line.type === 'subway'
                        ? '地铁线路按站点示意绘制（地下线路无地面道路）'
                        : '路径由 OpenStreetMap + OSRM 实时规划，公交车将沿真实道路行驶';
                }
                setTimeout(function() { if (leafletMap) leafletMap.invalidateSize(); }, 80);
            });
        }

        function updateDriveButton() {
            const btn = document.getElementById('driveToggleBtn');
            if (!btn) return;
            btn.innerHTML = drive.playing
                ? '<i class="fas fa-stop mr-1"></i>停车'
                : '<i class="fas fa-play mr-1"></i>发车巡线';
        }

        function toggleDrive(lineId) {
            if (!drive.path || drive.path.length < 2) {
                showToast('线路路径尚未就绪', 'error');
                return;
            }
            if (drive.playing) { stopDrive(); return; }
            startDrive();
        }

        function startDrive() {
            if (!drive.marker || !drive.total) return;
            const startDist = (drive.pauseProgress || 0) * drive.total;
            const duration = Math.max(12, Math.min(60, drive.total / 250));
            drive.playing = true;
            updateDriveButton();
            const baseTime = Date.now();

            function frame() {
                if (!drive.playing) return;
                const elapsed = (Date.now() - baseTime) / 1000;
                let dist = startDist + (elapsed / duration) * drive.total;
                if (dist >= drive.total) {
                    drive.marker.setLatLng(drive.path[drive.path.length - 1]);
                    drive.playing = false;
                    drive.pauseProgress = 0;
                    updateDriveButton();
                    showToast('公交车已到达终点站', 'success');
                    return;
                }
                drive.pauseProgress = dist / drive.total;
                drive.marker.setLatLng(pointAtDistance(drive.path, drive.cumulative, dist));
                drive.raf = requestAnimationFrame(frame);
            }
            frame();
        }

        function stopDrive() {
            drive.playing = false;
            if (drive.raf) { cancelAnimationFrame(drive.raf); drive.raf = null; }
            updateDriveButton();
        }

        function autoDriveWhenReady(lineId, attempts) {
            attempts = attempts || 0;
            if (activeLineId !== Number(lineId) || attempts > 30) return;
            if (drive.line && Number(drive.line.id) === Number(lineId) && drive.path && drive.path.length > 1) {
                if (!drive.playing) startDrive();
                return;
            }
            setTimeout(function() { autoDriveWhenReady(lineId, attempts + 1); }, 200);
        }

        function loadOverview() {
            return api('/point-mall/bus/collection').then(function(resp) {
                if (!resp || Number(resp.code) !== 9999) {
                    document.getElementById('lineGrid').innerHTML = '<div class="text-sm text-red-500 col-span-2">加载失败：' + escapeHtml((resp && resp.msg) || '网络异常') + '</div>';
                    return;
                }
                render(getResultData(resp));
            }).catch(function() {
                document.getElementById('lineGrid').innerHTML = '<div class="text-sm text-red-500 col-span-2">加载失败：网络异常</div>';
            });
        }

        function handleNewAchievements(list) {
            (list || []).forEach(function(a) {
                showToast('解锁成就「' + a.name + '」+' + a.reward_ap + ' AP 待领取', 'success');
            });
        }

        function unlockLine(id) {
            const wasOpen = activeLineId === Number(id);
            if (wasOpen) closeLineModal();
            api('/point-mall/bus/collection/unlock', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ catalog_id: id })
            }).then(function(resp) {
                if (!resp || Number(resp.code) !== 9999) {
                    showToast((resp && resp.msg) || '解锁失败', 'error');
                    return;
                }
                const data = getResultData(resp);
                showToast('成功解锁「' + data.line.name + '」，公交车即将发车', 'success');
                handleNewAchievements(data.new_achievements);
                loadOverview().then(function() {
                    openLineModal(id);
                    autoDriveWhenReady(id);
                });
            });
        }

        function checkinLine(id) {
            const wasOpen = activeLineId === Number(id);
            if (wasOpen) closeLineModal();
            api('/point-mall/bus/collection/checkin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ catalog_id: id })
            }).then(function(resp) {
                if (!resp || Number(resp.code) !== 9999) {
                    showToast((resp && resp.msg) || '打卡失败', 'error');
                    return;
                }
                const data = getResultData(resp);
                const checkin = data.checkin || {};
                let msg = '打卡「' + (checkin.station_name || '') + '」' + (checkin.free ? '（今日免费）' : (' -' + checkin.ap_cost + ' AP'));
                if (checkin.completed) {
                    msg += ' · 全线贯通 +' + checkin.reward_ap + ' AP';
                }
                showToast(msg, 'success');
                handleNewAchievements(data.new_achievements);
                loadOverview().then(function() {
                    if (wasOpen) openLineModal(id);
                });
            });
        }

        function claimAchievement(code) {
            api('/point-mall/bus/collection/claim', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            }).then(function(resp) {
                if (!resp || Number(resp.code) !== 9999) {
                    showToast((resp && resp.msg) || '领取失败', 'error');
                    return;
                }
                const data = getResultData(resp);
                showToast('领取成就「' + data.achievement.name + '」+' + data.achievement.reward_ap + ' AP', 'success');
                loadOverview();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadOverview();
        });
    </script>
@endsection
