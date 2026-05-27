@extends('layouts.app')

@section('title', '宠物乐园 - 蒙太奇')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">宠物乐园</h1>
                <p class="text-sm text-gray-500 mt-1">购买宠物伙伴，领养后可持续喂养升级</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="btn btn-primary" onclick="openGoodsModal()"><i class="fas fa-shopping-cart mr-1"></i>宠物商城</button>
                <button class="btn btn-secondary" onclick="openCompanionModal()"><i class="fas fa-paw mr-1"></i>待领养伙伴</button>
                <a href="/point-mall" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回商城</a>
            </div>
        </div>

        <div class="card">
            <div class="p-4 border-b border-gray-200 font-semibold text-gray-900">我的宠物</div>
            <div id="petList" class="p-4 space-y-3 text-sm text-gray-700">加载中...</div>
        </div>
    </div>

    <div id="goodsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeGoodsModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">宠物商城</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeGoodsModal()"><i class="fas fa-times"></i></button>
            </div>
            <div id="petGoods" class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-700">加载中...</div>
        </div>
    </div>

    <div id="companionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeCompanionModal(event)">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="font-semibold text-gray-900">待领养伙伴</div>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeCompanionModal()"><i class="fas fa-times"></i></button>
            </div>
            <div id="companionList" class="p-4 space-y-3 text-sm text-gray-700">加载中...</div>
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

        function renderPets(items) {
            const box = document.getElementById('petList');
            if (!items || !items.length) {
                box.innerHTML = '<div class="text-gray-500">你还没有宠物，先去宠物商城兑换一个吧。</div>';
                return;
            }
            box.innerHTML = items.map(p => `
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">${p.name}（${p.species}）</div>
                            <div class="text-xs text-gray-500 mt-1">Lv.${p.level} · 成长值 ${p.growth_value} · 健康 ${p.health}</div>
                            <div class="text-xs text-gray-500 mt-1">图片：${p.image_url || '-'}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="btn btn-outline btn-sm" onclick="feedPet(${p.id}, 10)">喂养10AP</button>
                            <button class="btn btn-outline btn-sm" onclick="feedPet(${p.id}, 30)">喂养30AP</button>
                            <button class="btn btn-primary btn-sm" onclick="feedPet(${p.id}, 50)">喂养50AP</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderCompanions(items) {
            const box = document.getElementById('companionList');
            if (!items || !items.length) {
                box.innerHTML = '<div class="text-gray-500">暂无待领养伙伴。</div>';
                return;
            }
            box.innerHTML = items.map(c => `
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-medium text-gray-900">权益 #${c.id}</div>
                    <div class="text-xs text-gray-500 mt-1">状态：${c.status} · 数量：${c.quantity}</div>
                    <button class="btn btn-primary btn-sm mt-2" onclick="adoptPet(${c.id})">立即领养</button>
                </div>
            `).join('');
        }

        function renderGoods(items) {
            const box = document.getElementById('petGoods');
            if (!items || !items.length) {
                box.innerHTML = '<div class="text-gray-500">暂无宠物商品。</div>';
                return;
            }
            box.innerHTML = items.map(g => `
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-medium text-gray-900">${g.name}</div>
                    <div class="text-xs text-gray-500 mt-1">${g.description || ''}</div>
                    <div class="text-sm text-rose-600 mt-2">${g.point_cost} AP</div>
                    <button class="btn btn-primary btn-sm mt-2" onclick="buyGoods(${g.id})">兑换</button>
                </div>
            `).join('');
        }

        async function loadOverview() {
            const resp = await api('/point-mall/pet/overview');
            const data = getResultData(resp);
            renderPets(data.pets || []);
            renderCompanions(data.companions || []);
        }

        async function loadGoods() {
            const resp = await api('/point-mall/goods?scene=pet');
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

        async function adoptPet(entitlementId) {
            const name = window.prompt('给宠物起个名字', '我的宠物') || '我的宠物';
            const resp = await api('/point-mall/pet/adopt', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ entitlement_id: entitlementId, name })
            });
            if (resp && Number(resp.code) === 9999) {
                alert('领养成功');
                await loadOverview();
                return;
            }
            alert(resp && resp.msg ? resp.msg : '领养失败');
        }

        async function feedPet(petId, pointCost) {
            const resp = await api('/point-mall/pet/' + petId + '/feed', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ point_cost: pointCost })
            });
            if (resp && Number(resp.code) === 9999) {
                await loadOverview();
                return;
            }
            alert(resp && resp.msg ? resp.msg : '喂养失败');
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
        function openCompanionModal() {
            const m = document.getElementById('companionModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeCompanionModal(event) {
            if (event && event.target !== event.currentTarget) return;
            const m = document.getElementById('companionModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', async function() {
            await loadOverview();
            await loadGoods();
        });
    </script>
@endsection
