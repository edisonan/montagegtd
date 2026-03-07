@extends('layouts.app')

@section('title', '积分抽奖 - 蒙太奇')

@section('content')
    <style>
        :root {
            --l-green-1: #ecfdf5;
            --l-green-2: #d1fae5;
            --l-green-3: #6ee7b7;
            --l-green-4: #34d399;
            --l-green-5: #059669;
            --l-green-6: #065f46;
        }
        .draw-result-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(3px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
        }
        .draw-result-modal.show { display: flex; }
        .draw-result-card {
            width: min(520px, 92vw);
            border-radius: 20px;
            border: 1px solid var(--l-green-3);
            background: linear-gradient(180deg, #f0fdf4, #ffffff);
            box-shadow: 0 30px 60px -30px rgba(16, 185, 129, 0.45);
            padding: 20px;
            animation: popIn .25s ease;
        }
        .draw-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid var(--l-green-3);
            background: #ecfdf5;
            color: var(--l-green-6);
            padding: 4px 10px;
            font-size: 12px;
            margin: 0 6px 6px 0;
        }
        .draw-loading {
            animation: pulseRoll 0.8s ease infinite;
        }
        .rarity-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border: 1px solid transparent;
        }
        .rarity-ssr { color: #7c2d12; background: #ffedd5; border-color: #fdba74; }
        .rarity-sr { color: #5b21b6; background: #ede9fe; border-color: #c4b5fd; }
        .rarity-r { color: #1d4ed8; background: #dbeafe; border-color: #93c5fd; }
        .rarity-n { color: #374151; background: #f3f4f6; border-color: #d1d5db; }
        .pool-item-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 8px;
            align-items: center;
        }
        .lottery-board {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }
        .lottery-cell {
            min-height: 86px;
            border: 1px solid #a7f3d0;
            border-radius: 14px;
            background: linear-gradient(180deg, #f0fdf4, #fff);
            padding: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all .22s ease;
            box-shadow: 0 8px 18px -14px rgba(5, 150, 105, 0.35);
        }
        .lottery-cell-tone-0 { background: linear-gradient(180deg, #ecfdf5, #ffffff); }
        .lottery-cell-tone-1 { background: linear-gradient(180deg, #f0fdf4, #ffffff); }
        .lottery-cell-tone-2 { background: linear-gradient(180deg, #ecfeff, #ffffff); }
        .lottery-cell-tone-3 { background: linear-gradient(180deg, #f7fee7, #ffffff); }
        .lottery-cell-tone-4 { background: linear-gradient(180deg, #f0fdfa, #ffffff); }
        .lottery-cell-tone-5 { background: linear-gradient(180deg, #f0f9ff, #ffffff); }
        .lottery-cell-tone-6 { background: linear-gradient(180deg, #f7fee7, #ffffff); }
        .lottery-cell-tone-7 { background: linear-gradient(180deg, #ecfccb, #ffffff); }
        .lottery-cell-title {
            font-size: 12px;
            font-weight: 600;
            color: #065f46;
            line-height: 1.2;
        }
        .lottery-cell.is-active {
            transform: translateY(-2px) scale(1.03);
            border-color: var(--l-green-4);
            box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.25), 0 14px 24px -16px rgba(5, 150, 105, 0.65);
            background: linear-gradient(180deg, #dcfce7, #bbf7d0);
        }
        .lottery-cell.is-win {
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.28), 0 20px 32px -18px rgba(5, 150, 105, 0.75);
        }
        .lottery-center {
            border: 1px solid #10b981;
            background: radial-gradient(circle at top, #6ee7b7, #059669);
            color: #fff;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: inset 0 0 20px rgba(255,255,255,.2);
        }
        .draw-btn-primary {
            width: 100%;
            border: 0;
            border-radius: 12px;
            background: #ecfeff;
            color: #065f46;
            font-weight: 700;
            font-size: 16px;
            line-height: 1;
            padding: 14px 10px;
            box-shadow: 0 12px 20px -12px rgba(6, 95, 70, 0.65);
        }
        .draw-btn-primary:hover { transform: translateY(-1px); }
        .draw-log-panel {
            max-height: 640px;
            overflow: auto;
        }
        .pool-shell {
            border: 1px solid #86efac;
            border-radius: 16px;
            padding: 14px;
            background: linear-gradient(135deg, rgba(220, 252, 231, .75), rgba(255, 255, 255, .95));
            box-shadow: 0 22px 36px -28px rgba(5, 150, 105, .48);
            position: relative;
            overflow: hidden;
        }
        .pity-hint {
            margin-top: 6px;
            font-size: 12px;
            color: #047857;
            background: #ecfdf5;
            border: 1px dashed #6ee7b7;
            border-radius: 8px;
            padding: 5px 8px;
        }
        .pool-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .draw-ten-btn {
            border: 1px solid #059669;
            background: #065f46;
            color: #fff;
            border-radius: 8px;
            font-size: 12px;
            padding: 6px 10px;
        }
        .order-btn {
            border: 1px solid #6ee7b7;
            background: #f0fdf4;
            color: #065f46;
            border-radius: 8px;
            font-size: 12px;
            padding: 6px 10px;
        }
        .lottery-shop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
        }
        .shop-card {
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            background: linear-gradient(180deg, #f0fdf4, #fff);
            padding: 10px;
        }
        .order-modal {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.45);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1400;
        }
        .order-modal.show { display: flex; }
        .order-modal-card {
            width: min(780px, 94vw);
            max-height: 86vh;
            overflow: auto;
            border-radius: 14px;
            border: 1px solid #6ee7b7;
            background: #fff;
            box-shadow: 0 30px 60px -38px rgba(5, 150, 105, .6);
            padding: 14px;
        }
        .lottery-tip {
            position: fixed;
            right: 18px;
            top: 78px;
            z-index: 1300;
            min-width: 220px;
            max-width: min(420px, 90vw);
            border-radius: 10px;
            border: 1px solid #6ee7b7;
            background: #ecfdf5;
            color: #065f46;
            box-shadow: 0 16px 26px -18px rgba(5, 150, 105, .55);
            padding: 10px 12px;
            font-size: 13px;
            opacity: 0;
            transform: translateY(-6px);
            pointer-events: none;
            transition: all .2s ease;
        }
        .lottery-tip.show {
            opacity: 1;
            transform: translateY(0);
        }
        .lottery-tip.error {
            border-color: #fca5a5;
            background: #fef2f2;
            color: #991b1b;
        }
        .sound-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #065f46;
            padding: 6px 10px;
            border: 1px solid #6ee7b7;
            border-radius: 999px;
            background: #ecfdf5;
        }
        .lottery-fx {
            pointer-events: none;
            position: absolute;
            inset: 0;
            overflow: hidden;
        }
        .lottery-fx .spark {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: radial-gradient(circle, #d9f99d, #10b981);
            animation: sparkFly .9s ease-out forwards;
        }
        @keyframes sparkFly {
            0% { opacity: 0; transform: translate(0,0) scale(0.2); }
            18% { opacity: 1; }
            100% { opacity: 0; transform: translate(var(--tx), var(--ty)) scale(1.2); }
        }
        @keyframes popIn {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pulseRoll {
            0%,100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.06); opacity: 0.65; }
        }
    </style>
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">积分抽奖</h1>
                <p class="text-sm text-gray-500 mt-1">消耗积分抽一次机会，立即获得奖品</p>
            </div>
            <div class="flex items-center gap-2">
                <label class="sound-toggle">
                    <input type="checkbox" id="soundEnabled" checked>
                    音效
                </label>
                <button class="order-btn" onclick="openOrdersModal()">订单记录</button>
                <a href="/point-mall" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回商城</a>
            </div>
        </div>

        <div class="card mb-6">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">抽奖券购买</div>
                <div class="text-xs text-gray-500">先购买抽奖券，再参与抽奖</div>
            </div>
            <div class="p-4">
                <div id="lotteryGoodsList" class="lottery-shop-grid">
                    <div class="text-sm text-gray-500">加载抽奖券中...</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="card lg:col-span-3">
                <div class="p-4 border-b border-gray-200 font-semibold text-gray-900">九宫格抽奖</div>
                <div class="p-4 space-y-3" id="poolList">加载中...</div>
            </div>
            <div class="card">
                <div class="p-3 border-b border-gray-200 font-semibold text-gray-900 text-sm">最近抽奖记录</div>
                <div class="p-3 space-y-2 text-xs draw-log-panel" id="drawLogList">加载中...</div>
            </div>
        </div>
    </div>
    <div id="drawResultModal" class="draw-result-modal">
        <div class="draw-result-card">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold text-gray-900">抽奖结果</div>
                <button class="btn btn-outline btn-sm" onclick="closeDrawModal()">关闭</button>
            </div>
            <div id="drawResultContent" class="text-sm text-gray-700">加载中...</div>
        </div>
    </div>
    <div id="lotteryTip" class="lottery-tip"></div>
    <div id="ordersModal" class="order-modal">
        <div class="order-modal-card">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold text-gray-900">兑换订单记录</div>
                <button class="btn btn-outline btn-sm" onclick="closeOrdersModal()">关闭</button>
            </div>
            <div id="ordersModalList" class="space-y-2 text-sm text-gray-700">加载中...</div>
        </div>
    </div>

    <script>
        const lotteryBoardMeta = {};
        const lotteryBoardState = {};
        let latestOrders = [];

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

        function render(data) {
            const pools = data.pools || [];
            const logs = data.logs || [];
            const poolItems = data.pool_items || {};
            const pityMap = data.pity || {};

            const poolList = document.getElementById('poolList');
            if (!pools.length) {
                poolList.innerHTML = '<div class="text-sm text-gray-500">暂无奖池</div>';
            } else {
                const rarityClass = (rarity) => {
                    const value = String(rarity || 'N').toLowerCase();
                    if (value === 'ssr') return 'rarity-ssr';
                    if (value === 'sr') return 'rarity-sr';
                    if (value === 'r') return 'rarity-r';
                    return 'rarity-n';
                };
                const buildBoard = (poolId, costAp, pityProgress) => {
                    const source = (poolItems[poolId] || []);
                    const cells = [];
                    const rewardNames = [];
                    for (let i = 0; i < 8; i++) {
                        if (!source.length) {
                            rewardNames.push('待配置');
                            cells.push(`<div class="lottery-cell lottery-cell-tone-${i % 8}" id="lottery-cell-${poolId}-${i}" data-board-cell="${poolId}" data-cell-idx="${i}"><div class="lottery-cell-title">待配置</div><div class="text-xs text-gray-500">0.00%</div></div>`);
                            continue;
                        }
                        const item = source[i % source.length];
                        rewardNames.push(String(item.reward_name || ''));
                        cells.push(`
                            <div class="lottery-cell lottery-cell-tone-${i % 8}" id="lottery-cell-${poolId}-${i}" data-board-cell="${poolId}" data-cell-idx="${i}">
                                <div class="lottery-cell-title">${item.reward_name || '未知奖励'}</div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="rarity-badge ${rarityClass(item.rarity)}">${item.rarity || 'N'}</span>
                                    <span class="text-xs text-gray-500">${Number(item.probability || 0).toFixed(2)}%</span>
                                </div>
                            </div>
                        `);
                    }
                    cells.splice(4, 0, `
                        <div class="lottery-cell lottery-center">
                            <div class="text-xs opacity-90 mb-1">${costAp} AP / 次</div>
                            <button class="draw-btn-primary" onclick="draw(${poolId}, 1)">立即抽奖</button>
                            <div class="text-xs mt-1 opacity-90">保底 ${Math.min(29, Number(pityProgress || 0))}/29</div>
                        </div>
                    `);
                    lotteryBoardMeta[poolId] = { rewardNames: rewardNames };
                    return `<div class="lottery-board">${cells.join('')}</div>`;
                };
                poolList.innerHTML = pools.map(p => `
                    <div class="pool-shell">
                        <div class="lottery-fx" id="lottery-fx-${p.id}"></div>
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="font-semibold text-emerald-900">${p.name}</div>
                                <div class="text-xs text-emerald-800 mt-1">${p.description || ''}</div>
                            </div>
                            <div class="pool-actions">
                                <div class="text-xs text-emerald-900">保底：${Math.min(29, Number(pityMap[p.id] || 0))}/29</div>
                                <button class="draw-ten-btn" onclick="draw(${p.id}, 10)">十连抽</button>
                            </div>
                        </div>
                        <div class="w-full h-1.5 bg-emerald-100 rounded mt-1 overflow-hidden">
                            <div class="h-1.5 bg-emerald-500 rounded" style="width:${Math.round((Math.min(29, Number(pityMap[p.id] || 0)) / 29) * 100)}%"></div>
                        </div>
                        <div class="pity-hint">${buildPityHint(pityMap[p.id] || 0)}</div>
                        <div class="mt-3">${buildBoard(p.id, Number(p.cost_ap || 0), pityMap[p.id] || 0)}</div>
                    </div>
                `).join('');
            }

            const drawLogList = document.getElementById('drawLogList');
            if (!logs.length) {
                drawLogList.innerHTML = '<div class="text-gray-500">暂无记录</div>';
            } else {
                drawLogList.innerHTML = logs.map(l => {
                    let resultName = '-';
                    try {
                        const payload = JSON.parse(l.result_payload || '{}');
                        resultName = payload.reward_name || '-';
                    } catch (e) {}
                    return `<div class="border-b border-gray-100 pb-2 mb-2"><div class="font-medium text-gray-900">${resultName}</div><div class="text-xs text-gray-500">${l.created_at}</div></div>`;
                }).join('');
            }
        }

        function renderLotteryGoods(goods) {
            const node = document.getElementById('lotteryGoodsList');
            if (!goods || !goods.length) {
                node.innerHTML = '<div class="text-sm text-gray-500">暂无可购买抽奖券</div>';
                return;
            }
            node.innerHTML = goods.map(g => `
                <div class="shop-card">
                    <div class="font-medium text-emerald-900">${g.name || '抽奖券'}</div>
                    <div class="text-xs text-emerald-700 mt-1">${g.description || ''}</div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-sm font-semibold text-emerald-700">${Number(g.point_cost || 0)} AP</div>
                        <button class="btn btn-primary btn-sm" onclick="buyLotteryGoods(${Number(g.id || 0)})">购买</button>
                    </div>
                </div>
            `).join('');
        }

        function renderOrdersList(orders) {
            const node = document.getElementById('ordersModalList');
            if (!orders || !orders.length) {
                node.innerHTML = '<div class="text-gray-500">暂无订单</div>';
                return;
            }
            node.innerHTML = orders.map(o => {
                const goodsName = o.goods_snapshot && o.goods_snapshot.name ? o.goods_snapshot.name : ('商品#' + o.goods_id);
                return `
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900">${goodsName}</div>
                            <div class="text-xs text-gray-500">${o.created_at || ''}</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">订单号：${o.order_no}</div>
                        <div class="text-xs text-gray-600 mt-1">数量 ${o.quantity} · 消费 ${o.point_cost_total} AP · 状态 ${o.delivery_status || '-'}</div>
                    </div>
                `;
            }).join('');
        }

        function openOrdersModal() {
            renderOrdersList(latestOrders);
            document.getElementById('ordersModal').classList.add('show');
        }

        function closeOrdersModal() {
            document.getElementById('ordersModal').classList.remove('show');
        }

        async function loadLotteryGoods() {
            const resp = await api('/point-mall/goods?scene=lottery');
            if (!resp || Number(resp.code) !== 9999) {
                renderLotteryGoods([]);
                return;
            }
            const goods = (getResultData(resp).goods || []);
            renderLotteryGoods(goods);
        }

        async function loadOrders() {
            const resp = await api('/point-mall/orders?page_count=30');
            if (!resp || Number(resp.code) !== 9999) {
                latestOrders = [];
                return;
            }
            latestOrders = getResultData(resp).orders || [];
        }

        async function buyLotteryGoods(goodsId) {
            const qtyText = window.prompt('请输入购买数量', '1');
            if (qtyText === null) return;
            const quantity = Number(qtyText || 0);
            if (!quantity || quantity <= 0) {
                showTip('购买数量不合法', 'error');
                return;
            }
            const resp = await api('/point-mall/purchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ goods_id: Number(goodsId), quantity: quantity })
            });
            if (!resp || Number(resp.code) !== 9999) {
                showTip(resp && resp.msg ? resp.msg : '购买失败', 'error');
                return;
            }
            showTip('购买成功，抽奖券已到账');
            await loadOrders();
        }

        function load() {
            api('/point-mall/lottery/overview').then(resp => {
                if (!resp || Number(resp.code) !== 9999) { alert(resp && resp.msg ? resp.msg : '加载失败'); return; }
                render(getResultData(resp));
            });
        }

        function openDrawModal(contentHtml) {
            document.getElementById('drawResultContent').innerHTML = contentHtml;
            document.getElementById('drawResultModal').classList.add('show');
        }

        function closeDrawModal() {
            document.getElementById('drawResultModal').classList.remove('show');
        }

        function draw(poolId, times) {
            const canStart = startPendingSpin(poolId);
            if (!canStart) return;
            showTip('抽奖中，请稍候...');
            api('/point-mall/lottery/draw', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pool_id: poolId, times: times || 1 })
            }).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    forceStopSpin(poolId);
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                let targetReward = '';
                if (Array.isArray(result.results)) {
                    targetReward = String((result.results[0] && result.results[0].reward_name) || '');
                    const chips = result.results.map((r, idx) => `<span class="draw-chip">#${idx + 1} ${r.reward_name || '未知奖励'}</span>`).join('');
                    settleSpin(poolId, targetReward, function() {
                        triggerParticles(poolId);
                        playWinSound(2);
                        const firstName = (result.results[0] && result.results[0].reward_name) ? result.results[0].reward_name : '已出奖';
                        showTip('十连抽完成：' + firstName + ' 等 ' + result.results.length + ' 项');
                        load();
                    });
                } else {
                    targetReward = String(result.reward_name || '');
                    settleSpin(poolId, targetReward, function() {
                        triggerParticles(poolId);
                        playWinSound(1);
                        showTip('恭喜获得：' + (result.reward_name || '未知奖励'));
                        load();
                    });
                }
            }).catch(() => {
                forceStopSpin(poolId);
                showTip('抽奖失败：网络异常', 'error');
            });
        }

        function getBoardState(poolId) {
            const key = String(poolId);
            if (!lotteryBoardState[key]) {
                lotteryBoardState[key] = { current: -1, timer: null, locked: false };
            }
            return lotteryBoardState[key];
        }

        function clearBoardHighlight(poolId) {
            document.querySelectorAll(`[data-board-cell="${poolId}"]`).forEach(el => {
                el.classList.remove('is-active');
                el.classList.remove('is-win');
            });
        }

        function activateBoardCell(poolId, idx, isWin) {
            clearBoardHighlight(poolId);
            const node = document.getElementById(`lottery-cell-${poolId}-${idx}`);
            if (!node) return;
            node.classList.add('is-active');
            if (isWin) node.classList.add('is-win');
        }

        function startPendingSpin(poolId) {
            const state = getBoardState(poolId);
            if (state.locked) return false;
            state.locked = true;
            let idx = state.current >= 0 ? state.current : 0;
            state.timer = setInterval(() => {
                idx = (idx + 1) % 8;
                state.current = idx;
                activateBoardCell(poolId, idx, false);
            }, 80);
            return true;
        }

        function forceStopSpin(poolId) {
            const state = getBoardState(poolId);
            if (state.timer) {
                clearInterval(state.timer);
                state.timer = null;
            }
            state.locked = false;
            clearBoardHighlight(poolId);
        }

        function resolveTargetIndex(poolId, rewardName) {
            const names = (lotteryBoardMeta[poolId] && lotteryBoardMeta[poolId].rewardNames) ? lotteryBoardMeta[poolId].rewardNames : [];
            if (!names.length) return Math.floor(Math.random() * 8);
            const target = String(rewardName || '').trim();
            if (!target) return Math.floor(Math.random() * names.length);
            const exact = names.findIndex(n => String(n || '').trim() === target);
            if (exact >= 0) return exact;
            const fuzzy = names.findIndex(n => String(n || '').indexOf(target) >= 0 || target.indexOf(String(n || '')) >= 0);
            return fuzzy >= 0 ? fuzzy : Math.floor(Math.random() * names.length);
        }

        function settleSpin(poolId, rewardName, onDone) {
            const state = getBoardState(poolId);
            if (state.timer) {
                clearInterval(state.timer);
                state.timer = null;
            }
            const targetIdx = resolveTargetIndex(poolId, rewardName);
            let current = state.current >= 0 ? state.current : 0;
            let steps = 18 + ((targetIdx - current + 8) % 8);
            let delay = 70;
            const tick = () => {
                current = (current + 1) % 8;
                state.current = current;
                steps--;
                const isFinal = steps <= 0 && current === targetIdx;
                activateBoardCell(poolId, current, isFinal);
                if (isFinal) {
                    state.locked = false;
                    if (typeof onDone === 'function') onDone();
                    return;
                }
                delay = Math.min(210, delay + 7);
                setTimeout(tick, delay);
            };
            setTimeout(tick, 60);
        }

        function buildPityHint(progress) {
            const v = Math.min(29, Number(progress || 0));
            const left = Math.max(0, 30 - v);
            if (left <= 1) return '下一抽触发保底，必出非 AP 奖励';
            if (left <= 5) return `保底临近，还差 ${left} 次，建议攒一波十连`;
            return `距离保底还差 ${left} 次，继续冲`;
        }

        function isSoundEnabled() {
            const node = document.getElementById('soundEnabled');
            return !!(node && node.checked);
        }

        function playWinSound(level) {
            if (!isSoundEnabled()) return;
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            const ctx = new AC();
            const now = ctx.currentTime;
            const notes = level > 1 ? [523.25, 659.25, 783.99] : [523.25, 659.25];
            notes.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, now + idx * 0.08);
                gain.gain.exponentialRampToValueAtTime(0.06, now + idx * 0.08 + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.08 + 0.16);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + idx * 0.08);
                osc.stop(now + idx * 0.08 + 0.18);
            });
        }

        function triggerParticles(poolId) {
            const wrap = document.getElementById(`lottery-fx-${poolId}`);
            if (!wrap) return;
            wrap.innerHTML = '';
            for (let i = 0; i < 18; i++) {
                const el = document.createElement('span');
                el.className = 'spark';
                el.style.left = `${45 + (Math.random() * 10)}%`;
                el.style.top = `${40 + (Math.random() * 16)}%`;
                el.style.setProperty('--tx', `${(Math.random() - 0.5) * 220}px`);
                el.style.setProperty('--ty', `${(Math.random() - 0.5) * 180}px`);
                wrap.appendChild(el);
            }
            setTimeout(() => { wrap.innerHTML = ''; }, 1100);
        }

        function showTip(message, type) {
            const node = document.getElementById('lotteryTip');
            if (!node) return;
            node.classList.remove('error');
            if (type === 'error') node.classList.add('error');
            node.textContent = message;
            node.classList.add('show');
            if (node._timer) clearTimeout(node._timer);
            node._timer = setTimeout(() => {
                node.classList.remove('show');
            }, type === 'error' ? 2600 : 1900);
        }

        document.addEventListener('DOMContentLoaded', load);
        document.addEventListener('DOMContentLoaded', function() {
            loadLotteryGoods();
            loadOrders();
        });
    </script>
@endsection
