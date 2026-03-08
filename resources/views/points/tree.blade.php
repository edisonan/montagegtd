@extends('layouts.app')

@section('title', '积分种树 - 蒙太奇')

@section('content')
    <style>
        .tree-stage-badge {
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 600;
        }
        .tree-stage-sapling { background: #dcfce7; color: #166534; }
        .tree-stage-young { background: #dbeafe; color: #1e3a8a; }
        .tree-stage-mature { background: #fef3c7; color: #92400e; }
        .tree-stage-giant { background: #ede9fe; color: #5b21b6; }
        .tree-card {
            background: radial-gradient(circle at top right, rgba(16,185,129,0.1), rgba(255,255,255,0.9));
        }
        .tree-avatar {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }
        .grow-bar { height: 8px; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
        .grow-bar-inner { height: 100%; background: linear-gradient(90deg, #22c55e, #16a34a); transition: width 0.35s ease; }
        .water-flash { animation: waterFlash 0.7s ease; }
        .deco-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
        }
        .deco-modal.show { display: flex; }
        .deco-card {
            width: min(720px, 94vw);
            max-height: 86vh;
            overflow: auto;
            border-radius: 16px;
            border: 1px solid #bbf7d0;
            background: #ffffff;
            box-shadow: 0 22px 50px -28px rgba(16, 185, 129, 0.45);
            padding: 16px;
        }
        .deco-chip {
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            color: #374151;
            background: #f9fafb;
            cursor: pointer;
        }
        .deco-chip.active {
            border-color: #10b981;
            color: #065f46;
            background: #d1fae5;
        }
        @keyframes waterFlash {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34,197,94,0.45); }
            50% { transform: scale(1.01); box-shadow: 0 0 0 8px rgba(34,197,94,0.12); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
    </style>
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">积分种树</h1>
                <p class="text-sm text-gray-500 mt-1">买树苗、浇水成长、持续经营你的绿色花园</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="btn btn-primary" onclick="openGoodsModal()">
                    <i class="fas fa-shopping-cart mr-1"></i>树苗商城
                </button>
                <button class="btn btn-secondary" onclick="openSeedlingModal()">
                    <i class="fas fa-seedling mr-1"></i>待种植的树苗券
                </button>
                <a href="/point-mall" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回商城</a>
            </div>
        </div>

        <!-- 我的树木 - 核心区域 -->
        <div class="card mb-6 border-2 border-emerald-300 shadow-lg">
            <div class="p-4 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 to-white">
                <div class="flex items-center gap-2">
                    <i class="fas fa-tree text-emerald-600 text-xl"></i>
                    <div>
                        <div class="font-bold text-emerald-900 text-lg">我的树木</div>
                        <div class="text-xs text-emerald-700 mt-1">精心培育属于你的绿色花园</div>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-3" id="treeList">加载中...</div>
        </div>
        <!-- 树苗商城弹窗 -->
        <div id="goodsModal" class="deco-modal" onclick="closeGoodsModal(event)">
            <div class="deco-card" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-emerald-600 text-xl"></i>
                        <div class="font-bold text-gray-900 text-lg">树苗商城</div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeGoodsModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3" id="seedlingGoods">加载中...</div>
            </div>
        </div>

        <!-- 待种植树苗券弹窗 -->
        <div id="seedlingModal" class="deco-modal" onclick="closeSeedlingModal(event)">
            <div class="deco-card" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-seedling text-emerald-600 text-xl"></i>
                        <div class="font-bold text-gray-900 text-lg">待种植的树苗券</div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeSeedlingModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-3" id="seedlingList">加载中...</div>
            </div>
        </div>

        <div class="card mt-6">
            <div class="p-4 border-b border-gray-200 font-semibold text-gray-900">森林排行榜 TOP10</div>
            <div class="p-4 space-y-2 text-sm" id="treeLeaderboard">加载中...</div>
        </div>
    </div>
    <div id="decoModal" class="deco-modal">
        <div class="deco-card">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold text-gray-900">树木装饰素材库</div>
                <button class="btn btn-outline btn-sm" onclick="closeDecoModal()">关闭</button>
            </div>
            <div class="text-xs text-gray-500 mb-3">选择主题色、挂件和背景效果，保存后立即生效</div>
            <div class="space-y-3">
                <div>
                    <div class="text-xs font-semibold text-gray-700 mb-1">主题色</div>
                    <div class="flex flex-wrap gap-2" id="decoThemeList"></div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-700 mb-1">挂件</div>
                    <div class="flex flex-wrap gap-2" id="decoOrnamentList"></div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-700 mb-1">背景</div>
                    <div class="flex flex-wrap gap-2" id="decoBackgroundList"></div>
                </div>
                <div class="rounded border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs text-emerald-800" id="decoPreviewText">预览：-</div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-primary btn-sm" onclick="saveDecoration()">保存装饰</button>
                    <button class="btn btn-outline btn-sm" onclick="closeDecoModal()">取消</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const DECORATION_LIBRARY = {
            themes: ['#22c55e', '#16a34a', '#10b981', '#059669', '#84cc16', '#14b8a6'],
            ornaments: ['风铃', '小鹿', '木屋', '萤火虫', '星灯', '秋千'],
            backgrounds: ['晨光草地', '森林夜色', '山谷薄雾', '海风晴空', '花海小径']
        };
        let currentDecoTreeId = null;
        let decoDraft = { theme: '#22c55e', ornament: '风铃', background: '晨光草地' };

        function getResultData(resp) { return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {}; }
        function api(path, opts = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const tokenNode = document.querySelector('meta[name="csrf-token"]');
            const csrf = tokenNode ? tokenNode.getAttribute('content') : '';
            const options = Object.assign({ method: 'GET' }, opts);
            options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
            if (options.method !== 'GET') options.headers['X-CSRF-TOKEN'] = csrf;
            return fetcher('/api/v2' + path, options).then(r => r.json());
        }

        function stageText(stage) {
            if (stage === 'giant') return '参天大树';
            if (stage === 'mature') return '成熟树';
            if (stage === 'young') return '青年树';
            return '树苗';
        }

        function stageClass(stage) {
            if (stage === 'giant') return 'tree-stage-giant';
            if (stage === 'mature') return 'tree-stage-mature';
            if (stage === 'young') return 'tree-stage-young';
            return 'tree-stage-sapling';
        }

        function stageEmoji(stage) {
            if (stage === 'giant') return '🌳';
            if (stage === 'mature') return '🌲';
            if (stage === 'young') return '🌿';
            return '🌱';
        }

        function render(data, section = 'all') {
            const seedlings = data.seedlings || [];
            const trees = data.trees || [];
            const treeList = document.getElementById('treeList');
            
            // 渲染树苗券列表（弹窗用）
            if (section === 'all' || section === 'seedlings') {
                const seedlingList = document.getElementById('seedlingList');
                if (!seedlings.length) {
                    seedlingList.innerHTML = '<div class="text-sm text-gray-500 text-center py-8">暂无树苗券，可去商城兑换种树商品</div>';
                } else {
                    seedlingList.innerHTML = seedlings.map(s => `
                        <div class="border border-emerald-200 rounded-lg p-3 bg-emerald-50">
                            <div class="font-medium text-emerald-800">树苗券 #${s.id}</div>
                            <div class="text-xs text-emerald-700 mt-1">状态：${s.status}</div>
                            <button class="btn btn-primary btn-sm mt-2" onclick="plantTree(${s.id}); closeSeedlingModal();">立即种植</button>
                        </div>
                    `).join('');
                }
            }

            // 渲染树木列表（核心区域）
            if (section === 'all' || section === 'trees') {
                if (!trees.length) {
                    treeList.innerHTML = '<div class="text-sm text-gray-500">你还没有树，先种下第一棵吧</div>';
                } else {
                    treeList.innerHTML = trees.map(t => {
                    let decoText = '未设置';
                    try {
                        const deco = t.decoration_payload ? JSON.parse(t.decoration_payload) : null;
                        if (deco) decoText = (deco.theme || '') + ' / ' + (deco.ornament || '');
                    } catch (e) {}
                    const growPercent = Math.max(0, Math.min(100, Math.round((Number(t.growth_value || 0) / 420) * 100)));
                    return `
                    <div class="border border-gray-200 rounded-lg p-3 tree-card" id="treeCard-${t.id}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="tree-avatar">${stageEmoji(t.stage)}</div>
                                <div>
                                    <div class="font-semibold text-gray-900">${t.name}</div>
                                    <div class="text-xs text-gray-500 mt-1">${decoText}</div>
                                </div>
                            </div>
                            <div class="tree-stage-badge ${stageClass(t.stage)}">${stageText(t.stage)}</div>
                        </div>
                        <div class="text-sm text-gray-600 mt-3">成长值：${t.growth_value} · 健康：${t.health}</div>
                        <div class="grow-bar mt-2"><div class="grow-bar-inner" style="width:${growPercent}%"></div></div>
                        <div class="text-xs text-gray-500 mt-1">成长进度：${growPercent}%</div>
                        <div class="text-xs text-gray-500 mt-1">上次浇水：${t.last_watered_at || '未浇水'}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <button class="btn btn-outline btn-sm" onclick="waterTree(${t.id})">浇水</button>
                            <button class="btn btn-outline btn-sm" onclick="decorateTree(${t.id})">装饰布局</button>
                        </div>
                    </div>
                `;}).join('');
                }
            }
        }

        function loadOverview() {
            api('/point-mall/tree/overview').then(resp => {
                if (!resp || Number(resp.code) !== 9999) { alert(resp && resp.msg ? resp.msg : '加载失败'); return; }
                const data = getResultData(resp);
                render(data, 'trees');
                if (data.season) {
                    const msg = `当前季节：${data.season.name || '-'} · ${data.season.hint || ''}`;
                    const titleNode = document.querySelector('h1.text-2xl');
                    const oldTip = document.getElementById('seasonTipInline');
                    if (oldTip) oldTip.remove();
                    const tip = document.createElement('div');
                    tip.id = 'seasonTipInline';
                    tip.className = 'text-xs text-emerald-700 mt-1';
                    tip.textContent = msg;
                    titleNode.parentNode.appendChild(tip);
                }
            });
        }

        function loadSeedlings() {
            api('/point-mall/tree/overview').then(resp => {
                if (!resp || Number(resp.code) !== 9999) return;
                const data = getResultData(resp);
                render(data, 'seedlings');
            });
        }

        function loadLeaderboard() {
            api('/point-mall/tree/leaderboard?limit=10').then(resp => {
                const node = document.getElementById('treeLeaderboard');
                if (!resp || Number(resp.code) !== 9999) {
                    node.innerHTML = '<div class="text-gray-500">排行榜加载失败</div>';
                    return;
                }
                const rows = (getResultData(resp).leaderboard || []);
                if (!rows.length) {
                    node.innerHTML = '<div class="text-gray-500">暂无数据</div>';
                    return;
                }
                node.innerHTML = rows.map((r, idx) => `
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 text-center font-semibold ${idx < 3 ? 'text-amber-600' : 'text-gray-500'}">${idx + 1}</span>
                            <span class="font-medium text-gray-900">${r.name}</span>
                            <span class="text-xs text-gray-500">${stageText(r.stage)}</span>
                        </div>
                        <span class="text-emerald-700 font-semibold">${r.growth_value}</span>
                    </div>
                `).join('');
            });
        }

        function openGoodsModal() {
            document.getElementById('goodsModal').classList.add('show');
            loadGoods();
        }

        function closeGoodsModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('goodsModal').classList.remove('show');
        }

        function openSeedlingModal() {
            document.getElementById('seedlingModal').classList.add('show');
            loadSeedlings();
        }

        function closeSeedlingModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('seedlingModal').classList.remove('show');
        }

        function loadGoods() {
            api('/point-mall/goods?scene=tree').then(resp => {
                if (!resp || Number(resp.code) !== 9999) return;
                const goods = (getResultData(resp).goods || []);
                const node = document.getElementById('seedlingGoods');
                if (!goods.length) {
                    node.innerHTML = '<div class="text-sm text-gray-500">暂无可兑换树苗</div>';
                    return;
                }
                node.innerHTML = goods.map(g => `
                    <div class="border border-emerald-200 bg-emerald-50 rounded-lg p-3">
                        <div class="font-medium text-emerald-900">${g.name}</div>
                        <div class="text-xs text-emerald-700 mt-1">${g.description || ''}</div>
                        <div class="text-sm text-emerald-900 mt-2">${g.point_cost} AP</div>
                        <button class="btn btn-primary btn-sm mt-2" onclick="buyGoods(${g.id}); closeGoodsModal();">兑换树苗</button>
                    </div>
                `).join('');
            });
        }

        function buyGoods(goodsId) {
            api('/point-mall/purchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ goods_id: goodsId, quantity: 1 })
            }).then(resp => {
                if (resp && Number(resp.code) === 9999) {
                    alert('兑换成功，树苗券已到账');
                    loadOverview();
                    return;
                }
                alert(resp && resp.msg ? resp.msg : '兑换失败');
            });
        }

        function plantTree(entitlementId) {
            const name = window.prompt('给这棵树起个名字', '我的树');
            if (name === null) return;
            api('/point-mall/tree/plant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ entitlement_id: entitlementId, name: name || '我的树' })
            }).then(resp => {
                if (resp && Number(resp.code) === 9999) { loadOverview(); return; }
                alert(resp && resp.msg ? resp.msg : '种植失败');
            });
        }

        function waterTree(treeId) {
            api('/point-mall/tree/' + treeId + '/water', { method: 'POST' }).then(resp => {
                if (resp && Number(resp.code) === 9999) {
                    const card = document.getElementById('treeCard-' + treeId);
                    if (card) {
                        card.classList.remove('water-flash');
                        void card.offsetWidth;
                        card.classList.add('water-flash');
                    }
                    loadOverview();
                    return;
                }
                alert(resp && resp.msg ? resp.msg : '浇水失败');
            });
        }

        function decorateTree(treeId) {
            currentDecoTreeId = Number(treeId || 0);
            decoDraft = { theme: '#22c55e', ornament: '风铃', background: '晨光草地' };
            renderDecoLibrary();
            refreshDecoPreview();
            document.getElementById('decoModal').classList.add('show');
        }

        function renderDecoLibrary() {
            const themeNode = document.getElementById('decoThemeList');
            const ornamentNode = document.getElementById('decoOrnamentList');
            const backgroundNode = document.getElementById('decoBackgroundList');
            themeNode.innerHTML = DECORATION_LIBRARY.themes.map(v =>
                `<button class="deco-chip ${decoDraft.theme === v ? 'active' : ''}" onclick="pickDeco('theme', '${v}')"><span style="display:inline-block;width:10px;height:10px;border-radius:999px;background:${v};margin-right:6px;"></span>${v}</button>`
            ).join('');
            ornamentNode.innerHTML = DECORATION_LIBRARY.ornaments.map(v =>
                `<button class="deco-chip ${decoDraft.ornament === v ? 'active' : ''}" onclick="pickDeco('ornament', '${v}')">${v}</button>`
            ).join('');
            backgroundNode.innerHTML = DECORATION_LIBRARY.backgrounds.map(v =>
                `<button class="deco-chip ${decoDraft.background === v ? 'active' : ''}" onclick="pickDeco('background', '${v}')">${v}</button>`
            ).join('');
        }

        function pickDeco(field, value) {
            decoDraft[field] = value;
            renderDecoLibrary();
            refreshDecoPreview();
        }

        function refreshDecoPreview() {
            document.getElementById('decoPreviewText').textContent =
                `预览：主题 ${decoDraft.theme} / 挂件 ${decoDraft.ornament} / 背景 ${decoDraft.background}`;
        }

        function closeDecoModal() {
            document.getElementById('decoModal').classList.remove('show');
            currentDecoTreeId = null;
        }

        function saveDecoration() {
            if (!currentDecoTreeId) return;
            api('/point-mall/tree/' + currentDecoTreeId + '/decorate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ decoration: decoDraft })
            }).then(resp => {
                if (resp && Number(resp.code) === 9999) {
                    closeDecoModal();
                    loadOverview();
                    return;
                }
                alert(resp && resp.msg ? resp.msg : '装饰失败');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadOverview();
            loadLeaderboard();
        });
    </script>
@endsection
