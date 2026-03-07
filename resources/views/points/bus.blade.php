@extends('layouts.app')

@section('title', '积分公交车 - 蒙太奇')

@section('content')
    <style>
        .line-card {
            border-left: 6px solid #0ea5e9;
            background: linear-gradient(90deg, rgba(14,165,233,0.08), rgba(255,255,255,0.96));
        }
        .run-progress-wrap {
            width: 100%;
            height: 10px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
            position: relative;
        }
        .run-progress-inner {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #2563eb, #22c55e);
            position: relative;
            transition: width .35s ease;
        }
        .run-progress-inner::after {
            content: '';
            position: absolute;
            top: 0;
            right: -12px;
            width: 12px;
            height: 100%;
            background: rgba(255,255,255,0.45);
            filter: blur(2px);
        }
        .arrival-board {
            border: 1px solid #bfdbfe;
            background: linear-gradient(90deg, #eff6ff, #f8fafc);
            color: #1e3a8a;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .station-strip {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
        }
        .station-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #d1d5db;
        }
        .station-dot.active { background: #16a34a; box-shadow: 0 0 0 2px rgba(22,163,74,.16); }
    </style>
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">积分公交车</h1>
                <p class="text-sm text-gray-500 mt-1">购买线路后可在地图上模拟公交运行</p>
            </div>
            <a href="/point-mall" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回商城</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card lg:col-span-1">
                <div class="p-4 border-b border-gray-200 font-semibold text-gray-900">可购买线路</div>
                <div class="p-4 space-y-3" id="lineList">加载中...</div>
            </div>
            <div class="card lg:col-span-2">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="font-semibold text-gray-900">我的线路与运行模拟</div>
                    <div class="text-xs text-gray-500">支持高德地图，未配置 key 时自动降级</div>
                </div>
                <div class="p-4">
                    <div id="arrivalBoard" class="arrival-board">等待发车...</div>
                    <div id="busStats" class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4"></div>
                    <div id="ownedLineList" class="space-y-3 mb-4">加载中...</div>
                    <div id="runList" class="space-y-2 mb-4"></div>
                    <div id="amapContainer" class="w-full h-96 rounded-lg border border-gray-200 bg-gray-50"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const AMAP_KEY = '{{ config('services.amap.key') }}';
        let busMap = null;
        let busPolyline = null;
        let busMarker = null;
        let stationCircles = [];
        let activeRunId = 0;
        let activeRunPath = [];

        function getResultData(resp) { return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {}; }
        function api(path, opts = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const tokenNode = document.querySelector('meta[name="csrf-token"]');
            const csrf = tokenNode ? tokenNode.getAttribute('content') : '';
            const options = Object.assign({ method: 'GET' }, opts);
            options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
            if (options.method !== 'GET') options.headers['X-CSRF-TOKEN'] = csrf;
            return fetcher('/api/v2' + path, options).then(async r => {
                const text = await r.text();
                try {
                    return JSON.parse(text || '{}');
                } catch (e) {
                    return { code: 0, msg: '响应解析失败', raw: text };
                }
            });
        }

        function parsePath(payload) {
            if (!payload) return [];
            if (Array.isArray(payload)) return payload;
            try { return JSON.parse(payload || '[]'); } catch (e) { return []; }
        }

        function ensureMapReady(callback) {
            if (!AMAP_KEY) {
                callback(false);
                return;
            }
            if (window.AMap) {
                callback(true);
                return;
            }
            const script = document.createElement('script');
            script.src = 'https://webapi.amap.com/maps?v=2.0&key=' + AMAP_KEY;
            script.onload = function() { callback(true); };
            script.onerror = function() { callback(false); };
            document.head.appendChild(script);
        }

        function initMap() {
            ensureMapReady(function(ok) {
                if (!ok) {
                    document.getElementById('amapContainer').innerHTML = '<div class="h-full w-full flex items-center justify-center text-gray-500">未配置高德 key，使用文字模拟</div>';
                    return;
                }
                busMap = new AMap.Map('amapContainer', {
                    zoom: 11,
                    center: [116.397428, 39.90923]
                });
            });
        }

        function render(data) {
            const lines = data.lines || [];
            const owned = data.owned_lines || [];
            const runs = data.recent_runs || [];
            const stats = data.stats || {};

            const lineList = document.getElementById('lineList');
            if (!lines.length) {
                lineList.innerHTML = '<div class="text-sm text-gray-500">暂无线路</div>';
            } else {
                lineList.innerHTML = lines.map(l => `
                    <div class="border border-sky-200 rounded-lg p-3 bg-sky-50 line-card">
                        <div class="font-semibold text-sky-900">${l.name}</div>
                        <div class="text-sm text-sky-800 mt-1">价格：${l.price_ap} AP</div>
                        <button class="btn btn-primary btn-sm mt-2" onclick="buyLine(${l.id})">购买线路</button>
                    </div>
                `).join('');
            }

            const ownedLineList = document.getElementById('ownedLineList');
            if (!owned.length) {
                ownedLineList.innerHTML = '<div class="text-sm text-gray-500">还未购买线路</div>';
            } else {
                ownedLineList.innerHTML = owned.map(o => `
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">${o.name}</div>
                                <div class="text-xs text-gray-500">购买时间：${o.bought_at || '-'}</div>
                            </div>
                            <button class="btn btn-outline btn-sm" data-line-name="${String(o.name || '').replace(/"/g, '&quot;')}" onclick="runLine(${o.user_line_id}, this.dataset.lineName || '')">开始运行</button>
                        </div>
                    </div>
                `).join('');
            }

            const runList = document.getElementById('runList');
            if (!runs.length) {
                runList.innerHTML = '<div class="text-sm text-gray-500">暂无运行记录</div>';
            } else {
                runList.innerHTML = runs.map(r => `
                    <div class="border border-gray-100 rounded-lg p-2">
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                            <span>运行#${r.id}</span><span>${r.run_status}</span>
                        </div>
                        <div class="run-progress-wrap">
                            <div class="run-progress-inner" style="width:${Math.max(0, Math.min(100, Number(r.progress || 0)))}%"></div>
                        </div>
                        ${renderStationStrip(r)}
                    </div>
                `).join('');
            }

            const busStats = document.getElementById('busStats');
            busStats.innerHTML = `
                <div class="border border-gray-100 rounded p-2"><div class="text-xs text-gray-500">总运行</div><div class="text-lg font-semibold text-gray-900">${Number(stats.total_runs || 0)}</div></div>
                <div class="border border-gray-100 rounded p-2"><div class="text-xs text-gray-500">已到站</div><div class="text-lg font-semibold text-emerald-700">${Number(stats.arrived_runs || 0)}</div></div>
                <div class="border border-gray-100 rounded p-2"><div class="text-xs text-gray-500">奖励次数</div><div class="text-lg font-semibold text-blue-700">${Number(stats.rewarded_runs || 0)}</div></div>
                <div class="border border-gray-100 rounded p-2"><div class="text-xs text-gray-500">累计奖励AP</div><div class="text-lg font-semibold text-amber-700">${Number(stats.total_reward_ap || 0)}</div></div>
            `;
        }

        function renderStationStrip(run) {
            let path = [];
            try {
                const metaObj = run.meta_payload ? JSON.parse(run.meta_payload) : {};
                path = Array.isArray(metaObj.path) ? metaObj.path : [];
            } catch (e) {}
            const count = Math.max(2, Math.min(10, path.length || 0));
            const activeIndex = Math.max(0, Math.min(count - 1, Math.round((Number(run.progress || 0) / 100) * (count - 1))));
            return `<div class="station-strip">${Array.from({ length: count }).map((_, idx) => `<span class="station-dot ${idx <= activeIndex ? 'active' : ''}"></span>`).join('')}</div>`;
        }

        function announce(message) {
            const board = document.getElementById('arrivalBoard');
            if (!board) return;
            board.textContent = message;
        }

        function syncStationHighlightByProgress(progress) {
            if (!stationCircles.length || !activeRunPath.length) return;
            const idx = Math.max(0, Math.min(stationCircles.length - 1, Math.round((Number(progress || 0) / 100) * (stationCircles.length - 1))));
            stationCircles.forEach((circle, i) => {
                circle.setOptions({
                    fillColor: i <= idx ? '#16a34a' : '#0ea5e9',
                    radius: i <= idx ? 8 : 6
                });
            });
            announce(`车辆运行中：第 ${idx + 1}/${stationCircles.length} 站`);
        }

        function loadOverview() {
            api('/point-mall/bus/overview').then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    document.getElementById('lineList').innerHTML = '<div class="text-sm text-red-500">线路加载失败</div>';
                    document.getElementById('ownedLineList').innerHTML = '<div class="text-sm text-red-500">我的线路加载失败</div>';
                    document.getElementById('runList').innerHTML = '<div class="text-sm text-red-500">运行记录加载失败</div>';
                    announce(resp && resp.msg ? ('加载失败：' + resp.msg) : '加载失败');
                    return;
                }
                render(getResultData(resp));
            }).catch(() => {
                document.getElementById('lineList').innerHTML = '<div class="text-sm text-red-500">线路加载失败</div>';
                document.getElementById('ownedLineList').innerHTML = '<div class="text-sm text-red-500">我的线路加载失败</div>';
                document.getElementById('runList').innerHTML = '<div class="text-sm text-red-500">运行记录加载失败</div>';
                announce('加载失败：网络异常');
            });
        }

        function buyLine(lineId) {
            api('/point-mall/bus/buy-line', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ line_id: lineId })
            }).then(resp => {
                if (resp && Number(resp.code) === 9999) { loadOverview(); return; }
                alert(resp && resp.msg ? resp.msg : '购买失败');
            });
        }

        function runLine(userLineId, lineName) {
            api('/point-mall/bus/start-run', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_line_id: userLineId })
            }).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    alert(resp && resp.msg ? resp.msg : '启动失败');
                    return;
                }
                const run = getResultData(resp).run || {};
                activeRunId = Number(run.id || 0);
                let pathData = [];
                try {
                    const metaObj = run.meta_payload ? JSON.parse(run.meta_payload) : {};
                    pathData = Array.isArray(metaObj.path) ? metaObj.path : [];
                } catch (e) {
                    pathData = [];
                }
                const meta = parsePath(pathData);
                activeRunPath = meta;
                if (!window.AMap || !busMap || !meta.length) {
                    announce('线路已启动：' + lineName + '（简化模拟）');
                    runTickLoop(run.id);
                    return;
                }
                const path = meta.map(p => [Number(p.lng), Number(p.lat)]);
                if (busPolyline) busMap.remove(busPolyline);
                if (busMarker) busMap.remove(busMarker);
                if (stationCircles.length) busMap.remove(stationCircles);
                busPolyline = new AMap.Polyline({ path: path, strokeColor: '#2563eb', strokeWeight: 6 });
                busMap.add(busPolyline);
                stationCircles = path.map(p => new AMap.CircleMarker({
                    center: p,
                    radius: 6,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    fillColor: '#0ea5e9',
                    fillOpacity: 0.95
                }));
                busMap.add(stationCircles);
                busMap.setFitView([busPolyline]);
                busMarker = new AMap.Marker({
                    position: path[0],
                    content: '<div style="width:26px;height:26px;border-radius:999px;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 6px 12px rgba(22,163,74,.35);">🚌</div>',
                    offset: new AMap.Pixel(-13, -13)
                });
                busMap.add(busMarker);
                announce('线路已发车：' + lineName + '，前往第 1 站');

                let i = 0;
                const timer = setInterval(() => {
                    i++;
                    if (i >= path.length) {
                        clearInterval(timer);
                        return;
                    }
                    busMarker.setPosition(path[i]);
                    syncStationHighlightByProgress(Math.round((i / (path.length - 1 || 1)) * 100));
                }, 1000);
                runTickLoop(run.id);
            });
        }

        function runTickLoop(runId) {
            let ticks = 0;
            const t = setInterval(() => {
                ticks++;
                api('/point-mall/bus/run/' + runId + '/tick', { method: 'POST' }).then(resp => {
                    if (!resp || Number(resp.code) !== 9999) return;
                    const run = (getResultData(resp).run || {});
                    syncStationHighlightByProgress(Number(run.progress || 0));
                    if (Number(run.progress || 0) >= 100 || (run.run_status || '') === 'arrived' || ticks > 20) {
                        clearInterval(t);
                        if ((run.run_status || '') === 'arrived') {
                            let rewardAp = 0;
                            try {
                                const metaObj = run.meta_payload ? JSON.parse(run.meta_payload) : {};
                                rewardAp = Number(metaObj.reward_ap || 0);
                            } catch (e) {}
                            announce(`到站完成，奖励 ${rewardAp || 0} AP 已到账`);
                        }
                    }
                    loadOverview();
                });
            }, 1500);
        }

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            loadOverview();
        });
    </script>
@endsection
