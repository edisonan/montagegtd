@extends('layouts.app')

@section('title', '积分商城 - 蒙太奇')
@section('description', '积分抽奖、积分公交车、积分种树与通用积分商品兑换')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">积分商城</h1>
                <p class="text-sm text-gray-500 mt-1">统一商品架构，支持抽奖/公交车/种树等特殊发货</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="btn btn-primary" onclick="openOrdersModal()">
                    <i class="fas fa-list mr-1"></i>我的兑换订单
                </button>
                <a href="/points" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>返回积分中心
                </a>
            </div>
        </div>

        <div id="mallEntrance" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card p-5 text-center text-gray-500">加载入口中...</div>
        </div>

{{--        <div class="card mb-6">--}}
{{--            <div class="p-4 border-b border-gray-200 flex items-center justify-between">--}}
{{--                <div class="font-semibold text-gray-900" id="sceneTitle">商品列表</div>--}}
{{--                <div class="text-sm text-gray-500">使用 AP 兑换</div>--}}
{{--            </div>--}}
{{--            <div class="p-4">--}}
{{--                <div id="goodsList" class="grid grid-cols-1 md:grid-cols-2 gap-4">--}}
{{--                    <div class="text-gray-500">加载商品中...</div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <!-- 订单弹窗 -->
        <div id="ordersModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="closeOrdersModal(event)">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">我的兑换订单</h2>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeOrdersModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[calc(80vh-140px)]">
                    <div id="modalOrderList" class="space-y-3 text-sm text-gray-700">
                        <div class="text-gray-500 text-center py-8">加载订单中...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let mallEntrances = [];
        let currentScene = 'lottery';

        function csrfToken() {
            const node = document.querySelector('meta[name="csrf-token"]');
            return node ? node.getAttribute('content') : '';
        }

        async function requestApi(path, opts = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const options = Object.assign({ method: 'GET' }, opts);
            options.headers = Object.assign({
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }, opts.headers || {});
            if (options.method !== 'GET' && !options.headers['X-CSRF-TOKEN']) {
                options.headers['X-CSRF-TOKEN'] = csrfToken();
            }
            const resp = await fetcher('/api/v2' + path, options);
            return resp.json();
        }

        function getResultData(resp) {
            if (!resp) return {};
            if (resp.result) return resp.result;
            if (resp.data) return resp.data;
            return {};
        }

        function renderEntrances(items) {
            const container = document.getElementById('mallEntrance');
            if (!items || !items.length) {
                container.innerHTML = '<div class="card p-5 text-center text-gray-500">暂无入口</div>';
                return;
            }

            container.innerHTML = items.map(item => {
                const active = currentScene === item.scene;
                const targetMap = {
                    tree: '/point-mall/tree',
                    lottery: '/point-mall/lottery',
                    bus: '/point-mall/bus',
                    pet: '/point-mall/pet',
                    pond: '/point-mall/pond'
                };
                const target = targetMap[item.scene] || '/point-mall';
                return `
                    <a href="${target}" class="card block p-5 text-left border ${active ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 bg-white'}">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br ${item.color || 'from-gray-500 to-gray-600'} flex items-center justify-center text-white">
                                <i class="${item.icon || 'fas fa-gift'}"></i>
                            </div>
                            <div class="font-semibold text-gray-900">${item.title || item.scene}</div>
                        </div>
                        <div class="text-sm text-gray-600">${item.subtitle || ''}</div>
                    </a>
                `;
            }).join('');
        }

        // function renderGoods(goods) {
        //     const container = document.getElementById('goodsList');
        //     if (!goods || !goods.length) {
        //         container.innerHTML = '<div class="text-gray-500">该分类暂无商品</div>';
        //         return;
        //     }
        //
        //     container.innerHTML = goods.map(g => {
        //         const stockText = Number(g.stock) < 0 ? '无限库存' : ('库存 ' + Number(g.stock));
        //         return `
        //             <div class="border border-gray-200 rounded-xl p-4">
        //                 <div class="font-semibold text-gray-900 mb-1">${g.name}</div>
        //                 <div class="text-sm text-gray-600 mb-3">${g.description || ''}</div>
        //                 <div class="flex items-center justify-between">
        //                     <div>
        //                         <div class="text-emerald-600 font-bold">${Number(g.point_cost)} AP</div>
        //                         <div class="text-xs text-gray-500 mt-1">${stockText}</div>
        //                     </div>
        //                     <button class="btn btn-primary btn-sm" onclick="buyGoods(${Number(g.id)})">
        //                         立即兑换
        //                     </button>
        //                 </div>
        //             </div>
        //         `;
        //     }).join('');
        // }

        function renderOrders(orders, isModal = false) {
            const containerId = isModal ? 'modalOrderList' : 'orderList';
            const container = document.getElementById(containerId);
            if (!container) return;
            
            if (!orders || !orders.length) {
                container.innerHTML = '<div class="text-gray-500 text-center py-8">暂无兑换订单</div>';
                return;
            }

            container.innerHTML = orders.map(o => {
                const goodsName = o.goods_snapshot && o.goods_snapshot.name ? o.goods_snapshot.name : ('商品#' + o.goods_id);
                return `
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900">${goodsName}</div>
                            <div class="text-xs text-gray-500">${o.created_at || ''}</div>
                        </div>
                        <div class="text-sm text-gray-600 mt-1">订单号：${o.order_no}</div>
                        <div class="text-sm text-gray-600 mt-1">数量：${o.quantity}，消费：${o.point_cost_total} AP</div>
                        <div class="text-sm mt-1">
                            <span class="text-gray-700">发货状态：</span>
                            <span class="font-medium ${o.delivery_status === 'fulfilled' ? 'text-emerald-600' : 'text-amber-600'}">${o.delivery_status}</span>
                            ${o.delivery_message ? `<span class="text-gray-500 ml-2">(${o.delivery_message})</span>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function loadEntrances() {
            const resp = await requestApi('/point-mall/entrances');
            const result = getResultData(resp);
            mallEntrances = result.entrances || [];
            renderEntrances(mallEntrances);
        }

        async function loadGoods() {
            // const titleNode = document.getElementById('sceneTitle');
            // const entrance = mallEntrances.find(i => i.scene === currentScene);
            // titleNode.textContent = entrance ? entrance.title + ' 商品' : '商品列表';
            //
            // const resp = await requestApi('/point-mall/goods?scene=' + encodeURIComponent(currentScene));
            // const result = getResultData(resp);
            // renderGoods(result.goods || []);
        }

        async function loadOrders(isModal = false) {
            const resp = await requestApi('/point-mall/orders?page_count=20');
            const result = getResultData(resp);
            renderOrders(result.orders || [], isModal);
        }

        function openOrdersModal() {
            const modal = document.getElementById('ordersModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            loadOrders(true);
        }

        function closeOrdersModal(event) {
            if (event && event.target !== event.currentTarget) return;
            const modal = document.getElementById('ordersModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        async function buyGoods(goodsId) {
            const qtyText = window.prompt('请输入兑换数量', '1');
            if (qtyText === null) return;
            const quantity = Number(qtyText);
            if (!quantity || quantity <= 0) {
                alert('兑换数量不合法');
                return;
            }

            const resp = await requestApi('/point-mall/purchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ goods_id: goodsId, quantity })
            });

            if (resp && Number(resp.code) === 9999) {
                alert('兑换成功');
                await Promise.all([loadGoods(), loadOrders()]);
                return;
            }
            alert(resp && resp.msg ? resp.msg : '兑换失败');
        }

        async function switchScene(scene) {
            currentScene = scene;
            renderEntrances(mallEntrances);
            await loadGoods();
        }

        document.addEventListener('DOMContentLoaded', async function() {
            await loadEntrances();
            await loadGoods();
        });
    </script>
@endsection
