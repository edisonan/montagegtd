@extends('layouts.app')

@section('title', '池塘乐园 - 蒙太奇')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">池塘乐园</h1>
                <p class="text-sm text-gray-500 mt-1">购买鱼苗，放养后可持续投喂升级</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="btn btn-primary" onclick="openGoodsModal()"><i class="fas fa-shopping-cart mr-1"></i>鱼苗商城</button>
                <button class="btn btn-secondary" onclick="openFryModal()"><i class="fas fa-fish mr-1"></i>待放养鱼苗</button>
                <a href="/point-mall" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回商城</a>
            </div>
        </div>

        <div class="card">
            <div class="p-4 border-b border-gray-200 font-semibold text-gray-900">我的池塘鱼群</div>
            <div id="fishList" class="p-4 space-y-3 text-sm text-gray-700">加载中...</div>
        </div>
    </div>

    <div id="goodsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeGoodsModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">鱼苗商城</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeGoodsModal()"><i class="fas fa-times"></i></button>
            </div>
            <div id="fishGoods" class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-700">加载中...</div>
        </div>
    </div>

    <div id="fryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeFryModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">待放养鱼苗</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeFryModal()"><i class="fas fa-times"></i></button>
            </div>
            <div id="fryList" class="p-4 space-y-3 text-sm text-gray-700">加载中...</div>
        </div>
    </div>

    <script>
        function getResultData(resp) { return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {}; }
        function csrfToken() {
            const node = document.querySelector('meta[name="csrf-token"]');
            return node ? node.getAttribute('content') : '';
        }
        async function api(path, opts = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const options = Object.assign({ method: 'GET' }, opts);
            options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
            if (options.method !== 'GET') options.headers['X-CSRF-TOKEN'] = csrfToken();
            const resp = await fetcher('/api/v2' + path, options);
            return resp.json();
        }

        function renderFish(items) {
            const box = document.getElementById('fishList');
            if (!items || !items.length) {
                box.innerHTML = '<div class="text-gray-500">你还没有鱼，先去鱼苗商城兑换吧。</div>';
                return;
            }
            box.innerHTML = items.map(f => `
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">${f.name}（${f.species}）</div>
                            <div class="text-xs text-gray-500 mt-1">Lv.${f.level} · 成长值 ${f.growth_value} · 健康 ${f.health}</div>
                            <div class="text-xs text-gray-500 mt-1">图片：${f.image_url || '-'}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="btn btn-outline btn-sm" onclick="feedFish(${f.id}, 10)">投喂10AP</button>
                            <button class="btn btn-outline btn-sm" onclick="feedFish(${f.id}, 30)">投喂30AP</button>
                            <button class="btn btn-primary btn-sm" onclick="feedFish(${f.id}, 50)">投喂50AP</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderFry(items) {
            const box = document.getElementById('fryList');
            if (!items || !items.length) {
                box.innerHTML = '<div class="text-gray-500">暂无待放养鱼苗。</div>';
                return;
            }
            box.innerHTML = items.map(c => `
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-medium text-gray-900">权益 #${c.id}</div>
                    <div class="text-xs text-gray-500 mt-1">状态：${c.status} · 数量：${c.quantity}</div>
                    <button class="btn btn-primary btn-sm mt-2" onclick="releaseFish(${c.id})">立即放养</button>
                </div>
            `).join('');
        }

        function renderGoods(items) {
            const box = document.getElementById('fishGoods');
            if (!items || !items.length) {
                box.innerHTML = '<div class="text-gray-500">暂无鱼苗商品。</div>';
                return;
            }
            box.innerHTML = items.map(g => `
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-medium text-gray-900">${g.name}</div>
                    <div class="text-xs text-gray-500 mt-1">${g.description || ''}</div>
                    <div class="text-sm text-cyan-700 mt-2">${g.point_cost} AP</div>
                    <button class="btn btn-primary btn-sm mt-2" onclick="buyGoods(${g.id})">兑换</button>
                </div>
            `).join('');
        }

        async function loadOverview() {
            const resp = await api('/point-mall/pond/overview');
            const data = getResultData(resp);
            renderFish(data.fishes || []);
            renderFry(data.fry || []);
        }

        async function loadGoods() {
            const resp = await api('/point-mall/goods?scene=pond');
            const data = getResultData(resp);
            renderGoods(data.goods || []);
        }

        async function buyGoods(goodsId) {
            const resp = await api('/point-mall/purchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ goods_id: goodsId, quantity: 1 })
            });
            if (resp && Number(resp.code) === 9999) {
                alert('兑换成功');
                await loadOverview();
                return;
            }
            alert(resp && resp.msg ? resp.msg : '兑换失败');
        }

        async function releaseFish(entitlementId) {
            const name = window.prompt('给鱼起个名字', '我的鱼') || '我的鱼';
            const resp = await api('/point-mall/pond/release', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ entitlement_id: entitlementId, name })
            });
            if (resp && Number(resp.code) === 9999) {
                alert('放养成功');
                await loadOverview();
                return;
            }
            alert(resp && resp.msg ? resp.msg : '放养失败');
        }

        async function feedFish(fishId, pointCost) {
            const resp = await api('/point-mall/pond/' + fishId + '/feed', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ point_cost: pointCost })
            });
            if (resp && Number(resp.code) === 9999) {
                await loadOverview();
                return;
            }
            alert(resp && resp.msg ? resp.msg : '投喂失败');
        }

        function openGoodsModal() {
            const m = document.getElementById('goodsModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
            loadGoods();
        }
        function closeGoodsModal(event) {
            if (event && event.target !== event.currentTarget) return;
            const m = document.getElementById('goodsModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
        function openFryModal() {
            const m = document.getElementById('fryModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeFryModal(event) {
            if (event && event.target !== event.currentTarget) return;
            const m = document.getElementById('fryModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', async function() {
            await loadOverview();
            await loadGoods();
        });
    </script>
@endsection
