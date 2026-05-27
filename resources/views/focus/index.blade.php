@extends('layouts.app')

@section('title', '专注历史 - 蒙太奇')
@section('description', '查看已完成的专注工作记录，跟踪您的工作效率')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('common.success')

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">专注工作历史</h1>
                <p class="text-gray-600">查看和回顾您已完成的所有专注工作记录</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{'/index'}}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>返回首页
                </a>
                <a href="{{'/statistics'}}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar mr-2"></i>数据统计
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4"><i class="fas fa-clock text-blue-600 text-xl"></i></div>
                        <div>
                            <p class="text-sm text-gray-500">总计专注数</p>
                            <p class="text-2xl font-bold text-gray-900" id="focusTotal">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4"><i class="fas fa-calendar-alt text-purple-600 text-xl"></i></div>
                        <div>
                            <p class="text-sm text-gray-500">今日完成</p>
                            <p class="text-2xl font-bold text-gray-900" id="focusToday">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4"><i class="fas fa-fire text-green-600 text-xl"></i></div>
                        <div>
                            <p class="text-sm text-gray-500">当前页时长</p>
                            <p class="text-2xl font-bold text-gray-900" id="focusCurrentPageDuration">0min</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">专注记录</h2>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-info-circle"></i>
                        <span id="focusRecordCount">共 0 条记录</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div id="focusEmptyState" class="text-center py-16 hidden">
                    <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-clock text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">暂无专注记录</h3>
                    <p class="text-gray-600 mb-6">开始一个专注后，这里会展示您的工作历史。</p>
                    <a href="{{url('/index')}}" class="btn btn-outline">
                        <i class="fas fa-home mr-2"></i>返回首页
                    </a>
                </div>

                <div id="focusContentArea">
                    <div class="hidden md:block overflow-x-auto">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="w-24">日期</th>
                                <th class="w-40">时间段</th>
                                <th>专注描述</th>
                                <th class="w-32">操作</th>
                            </tr>
                            </thead>
                            <tbody id="focusTableBody"></tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-4" id="focusMobileList"></div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between" id="focusPaginationWrap">
                        <div class="text-sm text-gray-500" id="focusPaginationText"></div>
                        <div class="flex items-center space-x-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="focusPrevPage"><i class="fas fa-chevron-left"></i></button>
                            <span class="text-sm text-gray-600" id="focusPageIndicator"></span>
                            <button type="button" class="btn btn-sm btn-secondary" id="focusNextPage"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function getApiRequest() {
            if (window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function') {
                return window.TaskApiBridge.requestWithFallback;
            }
            return null;
        }

        function withApiReady(fn) {
            var bootstrap = window.__taskTokenBootstrapPromise;
            if (bootstrap && typeof bootstrap.then === 'function') {
                return bootstrap.finally(fn);
            }
            fn();
            return Promise.resolve();
        }

        function getQueryParam(name) {
            var params = new URLSearchParams(window.location.search || '');
            return params.get(name);
        }

        function formatDate(value) {
            var d = new Date(String(value).replace(' ', 'T'));
            if (isNaN(d.getTime())) {
                return {md: '-', hm: '-'};
            }
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var dd = String(d.getDate()).padStart(2, '0');
            var hh = String(d.getHours()).padStart(2, '0');
            var mi = String(d.getMinutes()).padStart(2, '0');
            return {md: mm + '-' + dd, hm: hh + ':' + mi};
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        var focusPageState = {
            page: 1,
            perPage: 20,
            total: 0,
            lastPage: 1,
            focuss: [],
            todayOnly: false,
            statusCounts: {
                total: 0,
                today: 0
            },
            hasStatsLoaded: false
        };

        function computePageDuration(focuss) {
            var totalMinutes = 0;
            (focuss || []).forEach(function(focus) {
                var start = new Date(String(focus.start_time).replace(' ', 'T')).getTime();
                var end = new Date(String(focus.end_time).replace(' ', 'T')).getTime();
                if (!isNaN(start) && !isNaN(end) && end > start) {
                    totalMinutes += Math.round((end - start) / 60000);
                }
            });
            return totalMinutes;
        }

        function renderPomos() {
            var focuss = focusPageState.focuss || [];
            var tableBody = $('#focusTableBody');
            var mobileList = $('#focusMobileList');
            tableBody.empty();
            mobileList.empty();

            var lastDate = '';
            focuss.forEach(function(focus) {
                var start = formatDate(focus.start_time);
                var end = formatDate(focus.end_time);
                var showDate = start.md !== lastDate;
                lastDate = start.md;

                tableBody.append(
                    '<tr data-focus-row-id="' + focus.id + '" class="hover:bg-gray-50 transition-colors">' +
                    '<td><span class="font-medium text-gray-900">' + (showDate ? start.md : '<span class="text-gray-300">— —</span>') + '</span></td>' +
                    '<td><div class="text-sm text-gray-600">' + start.hm + ' - ' + end.hm + '</div></td>' +
                    '<td><div class="flex items-center group"><span data-focus-name-id="' + focus.id + '" class="text-gray-800 font-medium">' + escapeHtml(focus.name || '') + '</span></div></td>' +
                    '<td><div class="flex items-center justify-end space-x-3">' +
                    '<button class="update_focus text-gray-400 hover:text-green-600 transition-colors" data-focus-id="' + focus.id + '" data-focus-name="' + escapeHtml(focus.name || '') + '" title="编辑描述"><i class="fas fa-edit"></i></button>' +
                    '<button class="delete_focus text-gray-400 hover:text-red-600 transition-colors" data-focus-id="' + focus.id + '" title="删除专注"><i class="fas fa-trash"></i></button>' +
                    '</div></td>' +
                    '</tr>'
                );

                mobileList.append(
                    '<div data-focus-row-id="' + focus.id + '" class="card hover:shadow-md transition-shadow"><div class="p-4">' +
                    '<div class="mb-2"><span class="text-sm text-gray-500"><i class="far fa-clock mr-1"></i>' + start.md + ' ' + start.hm + ' - ' + end.hm + '</span></div>' +
                    '<div class="text-gray-800 font-medium mb-3"><span data-focus-name-id="' + focus.id + '">' + escapeHtml(focus.name || '') + '</span></div>' +
                    '<div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-100">' +
                    '<button class="update_focus text-sm text-gray-600 hover:text-green-600" data-focus-id="' + focus.id + '" data-focus-name="' + escapeHtml(focus.name || '') + '"><i class="fas fa-edit mr-1"></i>编辑</button>' +
                    '<button class="delete_focus text-sm text-red-600 hover:text-red-800" data-focus-id="' + focus.id + '"><i class="fas fa-trash mr-1"></i>删除</button>' +
                    '</div></div></div>'
                );
            });

            $('#focusRecordCount').text('共 ' + focusPageState.total + ' 条记录');
            $('#focusTotal').text(focusPageState.statusCounts.total || 0);
            $('#focusToday').text(focusPageState.statusCounts.today || 0);
            $('#focusCurrentPageDuration').text(computePageDuration(focuss) + 'min');

            var empty = focuss.length === 0;
            $('#focusEmptyState').toggle(empty);
            $('#focusContentArea').toggle(!empty);

            var startIdx = focusPageState.total === 0 ? 0 : ((focusPageState.page - 1) * focusPageState.perPage + 1);
            var endIdx = Math.min(focusPageState.total, focusPageState.page * focusPageState.perPage);
            $('#focusPaginationText').text('显示 ' + startIdx + ' - ' + endIdx + ' 条，共 ' + focusPageState.total + ' 条记录');
            $('#focusPageIndicator').text('第 ' + focusPageState.page + ' / ' + focusPageState.lastPage + ' 页');
            $('#focusPrevPage').prop('disabled', focusPageState.page <= 1);
            $('#focusNextPage').prop('disabled', focusPageState.page >= focusPageState.lastPage);
        }

        function loadPomoStats() {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                return Promise.resolve();
            }

            return apiRequest('GET', '/focuss/tab-counts', {}).then(function(resp) {
                if (!resp || resp.code !== 9999 || !resp.result) {
                    return;
                }
                focusPageState.statusCounts = {
                    total: Number(resp.result.total || 0),
                    today: Number(resp.result.today || 0)
                };
                focusPageState.hasStatsLoaded = true;
                $('#focusTotal').text(focusPageState.statusCounts.total);
                $('#focusToday').text(focusPageState.statusCounts.today);
            }).catch(function() {
                // ignore stats failure
            });
        }

        function loadPomos() {
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }

            if (focusPageState.todayOnly) {
                apiRequest('GET', '/focuss/today', {}).then(function(resp) {
                    if (!resp || resp.code !== 9999) {
                        alert((resp && resp.msg) || '加载失败');
                        return;
                    }
                    var list = resp.result || [];
                    focusPageState.focuss = list;
                    focusPageState.total = list.length;
                    focusPageState.lastPage = 1;
                    focusPageState.page = 1;
                    renderPomos();
                }).catch(function() {
                    alert('请求失败，请稍后重试');
                });
                return;
            }

            var url = '/focuss?page_count=' + focusPageState.perPage + '&page=' + focusPageState.page;
            apiRequest('GET', url, {}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) || '加载失败');
                    return;
                }
                var result = resp.result || {};
                var pagination = result.pagination || {};
                focusPageState.focuss = result.focuss || [];
                focusPageState.page = Math.max(1, Number(pagination.current_page || focusPageState.page));
                if (focusPageState.hasStatsLoaded) {
                    focusPageState.total = Number(focusPageState.statusCounts.total || 0);
                } else {
                    focusPageState.total = Number(focusPageState.focuss.length || 0);
                }
                focusPageState.lastPage = Math.max(1, Math.ceil((focusPageState.total || 0) / focusPageState.perPage));
                if (focusPageState.lastPage < focusPageState.page) {
                    focusPageState.lastPage = focusPageState.page;
                }
                if (pagination.has_more_pages) {
                    focusPageState.lastPage = Math.max(focusPageState.lastPage, focusPageState.page + 1);
                }
                renderPomos();
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        function handleDeletePomo(focusId) {
            if (!confirm('确认要删除此专注咩？')) {
                return;
            }
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('DELETE', '/focuss/' + focusId, {}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) || '处理失败，请稍后再试');
                    return;
                }
                loadPomoStats().finally(function() {
                    loadPomos();
                });
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        function handleUpdatePomo(focusId, currentName) {
            var newName = prompt('请输入专注描述：', currentName || '');
            if (!newName || newName === currentName) {
                return;
            }
            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            apiRequest('PUT', '/focuss/' + focusId, {name: newName}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) || '处理失败，请稍后再试');
                    return;
                }
                $('[data-focus-name-id="' + focusId + '"]').text(newName);
                $('.update_focus[data-focus-id="' + focusId + '"]').attr('data-focus-name', newName);
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        $(document).ready(function() {
            focusPageState.page = Math.max(1, Number(getQueryParam('page') || 1));
            focusPageState.todayOnly = window.location.pathname.indexOf('/focusstoday') === 0;

            $('#focusPrevPage').on('click', function() {
                if (focusPageState.page > 1) {
                    focusPageState.page -= 1;
                    loadPomos();
                }
            });

            $('#focusNextPage').on('click', function() {
                if (focusPageState.page < focusPageState.lastPage) {
                    focusPageState.page += 1;
                    loadPomos();
                }
            });

            $(document).on('click', '.delete_focus', function() {
                handleDeletePomo($(this).data('focus-id'));
            });

            $(document).on('click', '.update_focus', function() {
                handleUpdatePomo($(this).data('focus-id'), $(this).attr('data-focus-name'));
            });

            withApiReady(function() {
                loadPomoStats().finally(function() {
                    loadPomos();
                });
            });
        });
    </script>
@endsection
