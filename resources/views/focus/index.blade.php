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

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                            <p class="text-sm text-gray-500">筛选时长</p>
                            <p class="text-2xl font-bold text-gray-900" id="focusCurrentPageDuration">0min</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-amber-100 p-3 mr-4"><i class="fas fa-gauge-high text-amber-600 text-xl"></i></div>
                        <div>
                            <p class="text-sm text-gray-500">平均评分</p>
                            <p class="text-2xl font-bold text-gray-900" id="focusAverageRating">-</p>
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
                <div class="mb-6 grid grid-cols-1 lg:grid-cols-12 gap-3">
                    <div class="lg:col-span-3">
                        <label for="focusKeywordFilter" class="text-xs text-gray-500">关键词</label>
                        <input id="focusKeywordFilter" type="search" class="input w-full mt-1" placeholder="描述或备注">
                    </div>
                    <div class="lg:col-span-2">
                        <label for="focusStartDateFilter" class="text-xs text-gray-500">开始日期</label>
                        <input id="focusStartDateFilter" type="date" class="input w-full mt-1">
                    </div>
                    <div class="lg:col-span-2">
                        <label for="focusEndDateFilter" class="text-xs text-gray-500">结束日期</label>
                        <input id="focusEndDateFilter" type="date" class="input w-full mt-1">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="focusRatingFilter" class="text-xs text-gray-500">评分</label>
                        <select id="focusRatingFilter" class="input w-full mt-1">
                            <option value="">全部评分</option>
                            <option value="5">5分</option>
                            <option value="4">4分</option>
                            <option value="3">3分</option>
                            <option value="2">2分</option>
                            <option value="1">1分</option>
                        </select>
                    </div>
                    <div class="lg:col-span-1 flex items-end gap-2">
                        <button type="button" class="btn btn-primary w-full" id="focusApplyFilters" title="筛选">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-secondary w-full" id="focusResetFilters" title="重置">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>

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
                                <th class="w-24">时长</th>
                                <th>专注描述</th>
                                <th class="w-36">评分备注</th>
                                <th class="w-40">操作</th>
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

    <div id="focusReviewModal" class="hidden fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeReviewModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-xl">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">专注评分与备注</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-700" onclick="closeReviewModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">专注描述</div>
                        <div id="reviewTargetName" class="text-sm text-gray-800 break-words"></div>
                    </div>
                    <div>
                        <label for="reviewScoreInput" class="text-sm text-gray-700">评分</label>
                        <input type="hidden" id="reviewScoreInput" value="">
                        <div class="mt-2 flex items-center gap-1">
                            <button type="button" class="review-score-btn text-sm px-3 py-1.5 rounded bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700 transition-colors" data-score="1" onclick="selectReviewScore(1)" title="1分">
                                1
                            </button>
                            <button type="button" class="review-score-btn text-sm px-3 py-1.5 rounded bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700 transition-colors" data-score="2" onclick="selectReviewScore(2)" title="2分">
                                2
                            </button>
                            <button type="button" class="review-score-btn text-sm px-3 py-1.5 rounded bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700 transition-colors" data-score="3" onclick="selectReviewScore(3)" title="3分">
                                3
                            </button>
                            <button type="button" class="review-score-btn text-sm px-3 py-1.5 rounded bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700 transition-colors" data-score="4" onclick="selectReviewScore(4)" title="4分">
                                4
                            </button>
                            <button type="button" class="review-score-btn text-sm px-3 py-1.5 rounded bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700 transition-colors" data-score="5" onclick="selectReviewScore(5)" title="5分">
                                5
                            </button>
                            <button type="button" class="text-xs ml-2 px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200" onclick="selectReviewScore(0)">
                                清空
                            </button>
                        </div>
                        <div id="reviewScoreText" class="mt-1 text-xs text-gray-500">暂不评分</div>
                    </div>
                    <div>
                        <label for="reviewNoteInput" class="text-sm text-gray-700">备注</label>
                        <textarea id="reviewNoteInput" rows="4" maxlength="2000" class="input w-full mt-1" placeholder="写一点复盘或备注..."></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" class="btn btn-outline" onclick="closeReviewModal()">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveReview()">
                        <i class="fas fa-save mr-1"></i>保存
                    </button>
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

        function formatDurationMinutes(minutes) {
            minutes = Number(minutes || 0);
            if (minutes < 60) {
                return minutes + 'min';
            }
            var hours = Math.floor(minutes / 60);
            var remain = minutes % 60;
            return remain ? (hours + 'h ' + remain + 'min') : (hours + 'h');
        }

        function getTodayDateValue() {
            var date = new Date();
            var yyyy = date.getFullYear();
            var mm = String(date.getMonth() + 1).padStart(2, '0');
            var dd = String(date.getDate()).padStart(2, '0');
            return yyyy + '-' + mm + '-' + dd;
        }

        function computeFocusDuration(focus) {
            var start = new Date(String(focus.start_time).replace(' ', 'T')).getTime();
            var end = new Date(String(focus.end_time).replace(' ', 'T')).getTime();
            if (isNaN(start) || isNaN(end) || end <= start) {
                return 0;
            }
            return Math.round((end - start) / 60000);
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function renderQuickRatingControl(focus) {
            var current = Number(focus.rating || 0);
            var html = '<div class="inline-flex items-center gap-1" title="点击数字快速评分">';
            for (var score = 1; score <= 5; score += 1) {
                var active = score === current;
                html += '<button type="button" ' +
                    'class="quick_rating_focus w-7 h-7 rounded text-xs font-medium transition-colors ' +
                    (active ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700') + '" ' +
                    'data-focus-id="' + focus.id + '" data-rating="' + score + '" title="' + score + '分">' +
                    score +
                    '</button>';
            }
            html += '</div>';
            return html;
        }

        function renderReviewNote(focus, extraClass) {
            var note = focus.review_note;
            var text = String(note || '').trim();
            if (!text) {
                return '<button type="button" class="review_focus ' + (extraClass || 'mt-2') + ' block text-xs text-gray-400 hover:text-emerald-600" data-focus-id="' + focus.id + '">' +
                    '<i class="fas fa-pen-to-square mr-1"></i>添加备注' +
                    '</button>';
            }
            return '<button type="button" class="review_focus ' + (extraClass || 'mt-2') + ' block w-full text-left text-xs text-gray-500 hover:text-emerald-700 break-words" data-focus-id="' + focus.id + '" title="点击编辑备注">' +
                escapeHtml(text) +
                '</button>';
        }

        function findFocusById(focusId) {
            focusId = Number(focusId || 0);
            return (focusPageState.focuss || []).find(function(item) {
                return Number(item.id || 0) === focusId;
            }) || null;
        }

        var focusPageState = {
            page: 1,
            perPage: 20,
            total: 0,
            lastPage: 1,
            focuss: [],
            reviewTargetId: 0,
            todayOnly: false,
            filters: {
                keyword: '',
                start_date: '',
                end_date: '',
                rating: ''
            },
            summary: {
                total: 0,
                avg_rating: 0,
                duration_minutes: 0
            },
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

        function getFilterValues() {
            return {
                keyword: ($('#focusKeywordFilter').val() || '').trim(),
                start_date: $('#focusStartDateFilter').val() || '',
                end_date: $('#focusEndDateFilter').val() || '',
                rating: $('#focusRatingFilter').val() || ''
            };
        }

        function setFilterInputs(filters) {
            filters = filters || {};
            $('#focusKeywordFilter').val(filters.keyword || '');
            $('#focusStartDateFilter').val(filters.start_date || '');
            $('#focusEndDateFilter').val(filters.end_date || '');
            $('#focusRatingFilter').val(filters.rating || '');
        }

        function readFiltersFromQuery() {
            return {
                keyword: getQueryParam('keyword') || '',
                start_date: getQueryParam('start_date') || '',
                end_date: getQueryParam('end_date') || '',
                rating: getQueryParam('rating') || ''
            };
        }

        function buildQueryString(params) {
            var searchParams = new URLSearchParams();
            Object.keys(params || {}).forEach(function(key) {
                var value = params[key];
                if (value !== null && value !== undefined && String(value) !== '') {
                    searchParams.set(key, value);
                }
            });
            return searchParams.toString();
        }

        function syncUrlState() {
            var params = Object.assign({page: focusPageState.page}, focusPageState.filters);
            var query = buildQueryString(params);
            var nextUrl = window.location.pathname + (query ? ('?' + query) : '');
            window.history.replaceState({}, '', nextUrl);
        }

        function renderSummary() {
            var summary = focusPageState.summary || {};
            var total = Number(summary.total || focusPageState.total || 0);
            var avgRating = Number(summary.avg_rating || 0);
            var duration = Number(summary.duration_minutes || 0);

            $('#focusRecordCount').text('共 ' + total + ' 条记录');
            $('#focusTotal').text(focusPageState.statusCounts.total || total || 0);
            $('#focusToday').text(focusPageState.statusCounts.today || 0);
            $('#focusCurrentPageDuration').text(formatDurationMinutes(duration || computePageDuration(focusPageState.focuss || [])));
            $('#focusAverageRating').text(avgRating > 0 ? (avgRating + '分') : '-');
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
                var ratingHtml = renderQuickRatingControl(focus);
                var noteHtml = renderReviewNote(focus);
                var durationText = formatDurationMinutes(computeFocusDuration(focus));
                tableBody.append(
                    '<tr data-focus-row-id="' + focus.id + '" class="hover:bg-gray-50 transition-colors">' +
                    '<td><span class="font-medium text-gray-900">' + (showDate ? start.md : '<span class="text-gray-300">— —</span>') + '</span></td>' +
                    '<td><div class="text-sm text-gray-600">' + start.hm + ' - ' + end.hm + '</div></td>' +
                    '<td><span class="text-sm text-gray-700">' + durationText + '</span></td>' +
                    '<td><div class="flex items-center group"><span data-focus-name-id="' + focus.id + '" class="text-gray-800 font-medium">' + escapeHtml(focus.name || '') + '</span></div></td>' +
                    '<td><div data-focus-review-id="' + focus.id + '">' + ratingHtml + noteHtml + '</div></td>' +
                    '<td><div class="flex items-center justify-end space-x-3">' +
                    '<button class="review_focus text-gray-400 hover:text-emerald-600 transition-colors" data-focus-id="' + focus.id + '" title="评分备注"><i class="fas fa-pen-to-square"></i></button>' +
                    '<button class="update_focus text-gray-400 hover:text-green-600 transition-colors" data-focus-id="' + focus.id + '" data-focus-name="' + escapeHtml(focus.name || '') + '" title="编辑描述"><i class="fas fa-edit"></i></button>' +
                    '<button class="delete_focus text-gray-400 hover:text-red-600 transition-colors" data-focus-id="' + focus.id + '" title="删除专注"><i class="fas fa-trash"></i></button>' +
                    '</div></td>' +
                    '</tr>'
                );

                mobileList.append(
                    '<div data-focus-row-id="' + focus.id + '" class="card hover:shadow-md transition-shadow"><div class="p-4">' +
                    '<div class="mb-2 flex flex-wrap items-center gap-3 text-sm text-gray-500">' +
                    '<span><i class="far fa-clock mr-1"></i>' + start.md + ' ' + start.hm + ' - ' + end.hm + '</span>' +
                    '<span><i class="fas fa-hourglass-half mr-1"></i>' + durationText + '</span>' +
                    '</div>' +
                    '<div class="text-gray-800 font-medium mb-3"><span data-focus-name-id="' + focus.id + '">' + escapeHtml(focus.name || '') + '</span></div>' +
                    '<div data-focus-review-id="' + focus.id + '" class="mb-3">' + ratingHtml + renderReviewNote(focus, 'mt-2') + '</div>' +
                    '<div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-100">' +
                    '<button class="review_focus text-sm text-emerald-600 hover:text-emerald-700" data-focus-id="' + focus.id + '"><i class="fas fa-pen-to-square mr-1"></i>备注</button>' +
                    '<button class="update_focus text-sm text-gray-600 hover:text-green-600" data-focus-id="' + focus.id + '" data-focus-name="' + escapeHtml(focus.name || '') + '"><i class="fas fa-edit mr-1"></i>编辑</button>' +
                    '<button class="delete_focus text-sm text-red-600 hover:text-red-800" data-focus-id="' + focus.id + '"><i class="fas fa-trash mr-1"></i>删除</button>' +
                    '</div></div></div>'
                );
            });

            renderSummary();

            var empty = focuss.length === 0;
            $('#focusEmptyState').toggle(empty);
            $('#focusContentArea').toggle(!empty);

            var startIdx = focusPageState.total === 0 ? 0 : ((focusPageState.page - 1) * focusPageState.perPage + 1);
            var endIdx = Math.min(focusPageState.total, focusPageState.page * focusPageState.perPage);
            $('#focusPaginationText').text('显示 ' + startIdx + ' - ' + endIdx + ' 条，共 ' + focusPageState.total + ' 条记录');
            $('#focusPageIndicator').text('第 ' + focusPageState.page + ' / ' + focusPageState.lastPage + ' 页');
            $('#focusPrevPage').prop('disabled', focusPageState.page <= 1);
            $('#focusNextPage').prop('disabled', focusPageState.page >= focusPageState.lastPage);
            syncUrlState();
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
                var today = getTodayDateValue();
                var todayFilters = Object.assign({}, focusPageState.filters, {
                    start_date: today,
                    end_date: today
                });
                apiRequest('GET', '/focuss?' + buildQueryString(Object.assign({
                    page_count: 100,
                    page: 1
                }, todayFilters)), {}).then(function(resp) {
                    if (!resp || resp.code !== 9999) {
                        alert((resp && resp.msg) || '加载失败');
                        return;
                    }
                    var result = resp.result || {};
                    var list = result.focuss || [];
                    focusPageState.focuss = list;
                    focusPageState.total = Number((result.summary && result.summary.total) || list.length);
                    focusPageState.summary = result.summary || {
                        total: list.length,
                        avg_rating: 0,
                        duration_minutes: computePageDuration(list)
                    };
                    focusPageState.lastPage = 1;
                    focusPageState.page = 1;
                    renderPomos();
                }).catch(function() {
                    alert('请求失败，请稍后重试');
                });
                return;
            }

            var url = '/focuss?' + buildQueryString(Object.assign({
                page_count: focusPageState.perPage,
                page: focusPageState.page
            }, focusPageState.filters));
            apiRequest('GET', url, {}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) || '加载失败');
                    return;
                }
                var result = resp.result || {};
                var pagination = result.pagination || {};
                focusPageState.focuss = result.focuss || [];
                focusPageState.summary = result.summary || focusPageState.summary;
                focusPageState.page = Math.max(1, Number(pagination.current_page || focusPageState.page));
                focusPageState.total = Number(pagination.total || (result.summary && result.summary.total) || 0);
                focusPageState.lastPage = Math.max(1, Number(pagination.last_page || Math.ceil((focusPageState.total || 0) / focusPageState.perPage)));
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

        function openReviewModal(focusId) {
            var focus = findFocusById(focusId);
            if (!focus) {
                alert('专注记录不存在，请刷新后重试');
                return;
            }

            focusPageState.reviewTargetId = Number(focus.id || 0);
            $('#reviewTargetName').text(focus.name || '未命名专注');
            $('#reviewScoreInput').val(focus.rating ? String(focus.rating) : '');
            $('#reviewNoteInput').val(focus.review_note || '');
            renderReviewScoreUI();
            $('#focusReviewModal').removeClass('hidden');
        }

        function closeReviewModal() {
            focusPageState.reviewTargetId = 0;
            $('#focusReviewModal').addClass('hidden');
        }

        function renderReviewScoreUI() {
            var score = Number($('#reviewScoreInput').val() || 0);
            $('.review-score-btn').each(function() {
                var btnScore = Number($(this).attr('data-score') || 0);
                var active = btnScore === score && score > 0;
                $(this).toggleClass('bg-amber-500 text-white', active);
                $(this).toggleClass('bg-gray-100 text-gray-600', !active);
            });
            $('#reviewScoreText').text(score > 0 ? ('当前评分：' + score + ' 分') : '暂不评分');
        }

        function selectReviewScore(score) {
            var current = Number($('#reviewScoreInput').val() || 0);
            var next = Number(score || 0);
            $('#reviewScoreInput').val(next > 0 && current !== next ? String(next) : '');
            renderReviewScoreUI();
        }

        function saveReview() {
            var focusId = Number(focusPageState.reviewTargetId || 0);
            if (!focusId) {
                return;
            }

            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }

            var scoreValue = $('#reviewScoreInput').val();
            var payload = {
                rating: scoreValue ? Number(scoreValue) : null,
                review_note: ($('#reviewNoteInput').val() || '').trim() || null
            };

            apiRequest('PUT', '/focuss/' + focusId, payload).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) || '保存失败，请稍后再试');
                    return;
                }

                var updated = resp.result || {};
                focusPageState.focuss = (focusPageState.focuss || []).map(function(item) {
                    return Number(item.id || 0) === focusId ? Object.assign({}, item, updated) : item;
                });
                closeReviewModal();
                loadPomos();
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        function saveQuickRating(focusId, rating) {
            focusId = Number(focusId || 0);
            rating = Number(rating || 0);
            if (!focusId || rating < 1 || rating > 5) {
                return;
            }

            var focus = findFocusById(focusId);
            if (focus && Number(focus.rating || 0) === rating) {
                return;
            }

            var apiRequest = getApiRequest();
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }

            apiRequest('PUT', '/focuss/' + focusId, {rating: rating}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) || '保存失败，请稍后再试');
                    return;
                }

                var updated = resp.result || {};
                focusPageState.focuss = (focusPageState.focuss || []).map(function(item) {
                    return Number(item.id || 0) === focusId ? Object.assign({}, item, updated) : item;
                });
                loadPomos();
            }).catch(function() {
                alert('请求失败，请稍后重试');
            });
        }

        $(document).ready(function() {
            focusPageState.page = Math.max(1, Number(getQueryParam('page') || 1));
            focusPageState.todayOnly = window.location.pathname.indexOf('/focusstoday') === 0;
            focusPageState.filters = readFiltersFromQuery();
            setFilterInputs(focusPageState.filters);

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

            $(document).on('click', '.review_focus', function() {
                openReviewModal($(this).data('focus-id'));
            });

            $('#focusApplyFilters').on('click', function() {
                focusPageState.filters = getFilterValues();
                focusPageState.page = 1;
                loadPomos();
            });

            $('#focusResetFilters').on('click', function() {
                focusPageState.filters = {
                    keyword: '',
                    start_date: '',
                    end_date: '',
                    rating: ''
                };
                focusPageState.page = 1;
                setFilterInputs(focusPageState.filters);
                loadPomos();
            });

            $('#focusKeywordFilter').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    focusPageState.filters = getFilterValues();
                    focusPageState.page = 1;
                    loadPomos();
                }
            });

            $(document).on('click', '.quick_rating_focus', function() {
                saveQuickRating($(this).data('focus-id'), $(this).data('rating'));
            });

            withApiReady(function() {
                loadPomoStats().finally(function() {
                    loadPomos();
                });
            });
        });
    </script>
@endsection
