@extends('layouts.app')

@section('title', '手账列表 - 蒙太奇')
@section('description', '查看和管理已完成的手账记录')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('common.success')

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">手账列表</h1>
                <p class="text-gray-600">查看和回顾您已完成的手账记录</p>
            </div>
            <div class="flex items-center space-x-4">
                <button type="button" class="btn btn-primary" onclick="showJournalCreateModal()">
                    <i class="fas fa-plus mr-2"></i>新增手账
                </button>
                <a href="{{ url('/index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>返回首页
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">手账总数</p>
                            <p class="text-2xl font-bold text-gray-900" id="journalTotal">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-purple-100 p-3 mr-4">
                            <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">今日完成</p>
                            <p class="text-2xl font-bold text-gray-900" id="journalToday">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-hourglass-half text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">筛选时长</p>
                            <p class="text-2xl font-bold text-gray-900" id="journalDuration">0min</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="journal_success_alert" class="hidden mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="journal_success_text"></span>
        </div>

        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">手账记录</h2>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-info-circle"></i>
                        <span id="journalRecordCount">共 0 条记录</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="mb-6 grid grid-cols-1 lg:grid-cols-12 gap-3">
                    <div class="lg:col-span-4">
                        <label for="journalKeywordFilter" class="text-xs text-gray-500">关键词</label>
                        <input id="journalKeywordFilter" type="search" class="input w-full mt-1" placeholder="手账内容">
                    </div>
                    <div class="lg:col-span-2">
                        <label for="journalStartDateFilter" class="text-xs text-gray-500">开始日期</label>
                        <input id="journalStartDateFilter" type="date" class="input w-full mt-1">
                    </div>
                    <div class="lg:col-span-2">
                        <label for="journalEndDateFilter" class="text-xs text-gray-500">结束日期</label>
                        <input id="journalEndDateFilter" type="date" class="input w-full mt-1">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="journalTypeFilter" class="text-xs text-gray-500">类型</label>
                        <select id="journalTypeFilter" class="input w-full mt-1">
                            <option value="">全部类型</option>
                            <option value="1">手动记录</option>
                            <option value="2">待办完成</option>
                            <option value="3">专注完成</option>
                        </select>
                    </div>
                    <div class="lg:col-span-1 flex items-end gap-2">
                        <button type="button" class="btn btn-primary w-full" id="journalApplyFilters" title="筛选">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-secondary w-full" id="journalResetFilters" title="重置">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>

                <div id="journals_empty_state" class="text-center py-16 hidden">
                    <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-check-circle text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">暂无手账记录</h3>
                    <p class="text-gray-600 mb-6">完成待办或专注后，这里会展示您的手账历史。</p>
                    <a href="{{ url('/index') }}" class="btn btn-outline">
                        <i class="fas fa-home mr-2"></i>返回首页
                    </a>
                </div>

                <div id="journals_content_area">
                    <div class="hidden md:block overflow-x-auto" id="journals_desktop_wrap">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="w-24">日期</th>
                                <th class="w-40">时间段</th>
                                <th class="w-24">时长</th>
                                <th class="w-32">类型</th>
                                <th>手账内容</th>
                                <th class="w-40">操作</th>
                            </tr>
                            </thead>
                            <tbody id="journals_desktop_body"></tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-4" id="journals_mobile_list"></div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between" id="journals_pagination">
                        <div class="text-sm text-gray-500" id="journals_pagination_text"></div>
                        <div class="flex items-center space-x-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="journals_prev_page">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="text-sm text-gray-600" id="journals_page_text">第 1 / 1 页</span>
                            <button type="button" class="btn btn-sm btn-secondary" id="journals_next_page">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.journal-create-modal')

    <script src="{{ '/js/My97DatePicker/WdatePicker.js' }}"></script>
    <script type="text/javascript">
        function getApiRequest() {
            if (window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function') {
                return window.TaskApiBridge.requestWithFallback;
            }
            return null;
        }

        function webRequest(method, url, data) {
            return $.ajax({
                url: url,
                type: method || 'GET',
                dataType: 'json',
                data: data || {},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }

        function withApiReady(fn) {
            var bootstrap = window.__taskTokenBootstrapPromise;
            if (bootstrap && typeof bootstrap.then === 'function') {
                return bootstrap.then(function () {
                    return fn();
                }, function () {
                    return fn();
                });
            }
            fn();
            return Promise.resolve();
        }

        function getQueryParam(name) {
            var query = (window.location.search || '').replace(/^\?/, '');
            var pairs = query ? query.split('&') : [];
            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].split('=');
                if (decodeURIComponent(pair[0] || '') === name) {
                    return decodeURIComponent((pair[1] || '').replace(/\+/g, ' '));
                }
            }
            return null;
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function formatDateParts(value) {
            if (!value) {
                return {md: '-', hm: '-'};
            }
            var date = new Date(String(value).replace(' ', 'T'));
            if (isNaN(date.getTime())) {
                return {md: '-', hm: '-'};
            }
            var mm = pad2(date.getMonth() + 1);
            var dd = pad2(date.getDate());
            var hh = pad2(date.getHours());
            var mi = pad2(date.getMinutes());
            return {md: mm + '-' + dd, hm: hh + ':' + mi};
        }

        function pad2(value) {
            value = String(value);
            return value.length < 2 ? ('0' + value) : value;
        }

        function computeJournalDuration(journal) {
            var start = new Date(String(journal.start_time).replace(' ', 'T')).getTime();
            var end = new Date(String(journal.end_time).replace(' ', 'T')).getTime();
            if (isNaN(start) || isNaN(end) || end <= start) {
                return 0;
            }
            return Math.round((end - start) / 60000);
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

        function getJournalTypeMeta(type) {
            var id = Number(type || 1);
            var map = {
                1: {label: '手动记录', icon: 'fa-pen-to-square', color: 'text-blue-600', bg: 'bg-blue-50'},
                2: {label: '待办完成', icon: 'fa-list-check', color: 'text-emerald-600', bg: 'bg-emerald-50'},
                3: {label: '专注完成', icon: 'fa-clock', color: 'text-purple-600', bg: 'bg-purple-50'}
            };
            return map[id] || map[1];
        }

        function renderJournalType(type) {
            var meta = getJournalTypeMeta(type);
            return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ' + meta.bg + ' ' + meta.color + '">' +
                '<i class="fas ' + meta.icon + ' mr-1"></i>' +
                meta.label +
                '</span>';
        }

        function buildQueryString(params) {
            var pairs = [];
            params = params || {};
            for (var key in params) {
                if (!Object.prototype.hasOwnProperty.call(params, key)) {
                    continue;
                }
                var value = params[key];
                if (value !== null && value !== undefined && String(value) !== '') {
                    pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(String(value)));
                }
            }
            return pairs.join('&');
        }

        function getFilterValues() {
            return {
                keyword: ($('#journalKeywordFilter').val() || '').trim(),
                start_date: $('#journalStartDateFilter').val() || '',
                end_date: $('#journalEndDateFilter').val() || '',
                type: $('#journalTypeFilter').val() || ''
            };
        }

        function setFilterInputs(filters) {
            filters = filters || {};
            $('#journalKeywordFilter').val(filters.keyword || '');
            $('#journalStartDateFilter').val(filters.start_date || '');
            $('#journalEndDateFilter').val(filters.end_date || '');
            $('#journalTypeFilter').val(filters.type || '');
        }

        function readFiltersFromQuery() {
            return {
                keyword: getQueryParam('keyword') || '',
                start_date: getQueryParam('start_date') || '',
                end_date: getQueryParam('end_date') || '',
                type: getQueryParam('type') || ''
            };
        }

        var journalPageState = {
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: 0,
            journals: [],
            filters: {
                keyword: '',
                start_date: '',
                end_date: '',
                type: ''
            },
            summary: {
                total: 0,
                today: 0,
                duration_minutes: 0
            }
        };

        function syncUrlState() {
            var params = mergeObject({page: journalPageState.currentPage}, journalPageState.filters);
            var query = buildQueryString(params);
            var nextUrl = window.location.pathname + (query ? ('?' + query) : '');
            window.history.replaceState({}, '', nextUrl);
        }

        function mergeObject(base, extra) {
            base = base || {};
            extra = extra || {};
            for (var key in extra) {
                if (Object.prototype.hasOwnProperty.call(extra, key)) {
                    base[key] = extra[key];
                }
            }
            return base;
        }

        function renderSummary() {
            var summary = journalPageState.summary || {};
            var total = Number(summary.total || journalPageState.total || 0);
            var today = Number(summary.today || 0);
            var duration = Number(summary.duration_minutes || 0);

            $('#journalTotal').text(total);
            $('#journalToday').text(today);
            $('#journalDuration').text(formatDurationMinutes(duration));
            $('#journalRecordCount').text('共 ' + total + ' 条记录');
        }

        function renderJournalsList() {
            var journals = journalPageState.journals || [];
            var desktopBody = $('#journals_desktop_body');
            var mobileList = $('#journals_mobile_list');
            desktopBody.empty();
            mobileList.empty();

            renderSummary();

            var empty = journals.length === 0;
            $('#journals_empty_state').toggle(empty);
            $('#journals_content_area').toggle(!empty);
            if (empty) {
                syncUrlState();
                return;
            }

            var lastDate = '';
            journals.forEach(function(journal) {
                var start = formatDateParts(journal.start_time);
                var end = formatDateParts(journal.end_time);
                var showDate = start.md !== lastDate;
                lastDate = start.md;
                var durationText = formatDurationMinutes(computeJournalDuration(journal));
                var typeHtml = renderJournalType(journal.type);
                var name = escapeHtml(journal.name || '');

                desktopBody.append(
                    '<tr id="journal-row-' + journal.id + '" class="hover:bg-gray-50 transition-colors">' +
                    '<td><span class="font-medium text-gray-900">' + (showDate ? start.md : '<span class="text-gray-300">-- --</span>') + '</span></td>' +
                    '<td><div class="text-sm text-gray-600">' + start.hm + ' - ' + end.hm + '</div></td>' +
                    '<td><span class="text-sm text-gray-700">' + durationText + '</span></td>' +
                    '<td>' + typeHtml + '</td>' +
                    '<td><div class="text-gray-800 font-medium break-words" title="' + name + '">' + name + '</div></td>' +
                    '<td><div class="flex items-center justify-end space-x-3">' +
                    '<a href="/notes?source_type=4&source_id=' + journal.id + '" class="text-gray-400 hover:text-blue-600 transition-colors" title="记录更多当时的想法"><i class="fas fa-sticky-note"></i></a>' +
                    '<a href="/journal/' + journal.id + '" class="text-gray-400 hover:text-green-600 transition-colors" title="编辑手账"><i class="fas fa-edit"></i></a>' +
                    '<button type="button" class="delete_journal text-gray-400 hover:text-red-600 transition-colors" data-journal-id="' + journal.id + '" title="删除手账"><i class="fas fa-trash"></i></button>' +
                    '</div></td>' +
                    '</tr>'
                );

                mobileList.append(
                    '<div class="card hover:shadow-md transition-shadow" id="mobile-journal-' + journal.id + '">' +
                    '<div class="p-4">' +
                    '<div class="mb-2 flex flex-wrap items-center gap-3 text-sm text-gray-500">' +
                    '<span><i class="far fa-clock mr-1"></i>' + start.md + ' ' + start.hm + ' - ' + end.hm + '</span>' +
                    '<span><i class="fas fa-hourglass-half mr-1"></i>' + durationText + '</span>' +
                    '</div>' +
                    '<div class="mb-3">' + typeHtml + '</div>' +
                    '<div class="text-gray-800 font-medium mb-3 break-words">' + name + '</div>' +
                    '<div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-100">' +
                    '<a href="/notes?source_type=4&source_id=' + journal.id + '" class="text-sm text-blue-600 hover:text-blue-700"><i class="fas fa-sticky-note mr-1"></i>想法</a>' +
                    '<a href="/journal/' + journal.id + '" class="text-sm text-green-600 hover:text-green-700"><i class="fas fa-edit mr-1"></i>编辑</a>' +
                    '<button type="button" class="delete_journal text-sm text-red-600 hover:text-red-700" data-journal-id="' + journal.id + '"><i class="fas fa-trash mr-1"></i>删除</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );
            });

            var startIdx = journalPageState.total === 0 ? 0 : ((journalPageState.currentPage - 1) * journalPageState.perPage + 1);
            var endIdx = Math.min(journalPageState.total, journalPageState.currentPage * journalPageState.perPage);
            $('#journals_pagination_text').text('显示 ' + startIdx + ' - ' + endIdx + ' 条，共 ' + journalPageState.total + ' 条记录');
            $('#journals_page_text').text('第 ' + journalPageState.currentPage + ' / ' + journalPageState.lastPage + ' 页');
            $('#journals_prev_page').prop('disabled', journalPageState.currentPage <= 1);
            $('#journals_next_page').prop('disabled', journalPageState.currentPage >= journalPageState.lastPage);
            syncUrlState();
        }

        function loadJournals(page) {
            var targetPage = page || journalPageState.currentPage || 1;
            var params = mergeObject({
                page: targetPage,
                page_size: journalPageState.perPage
            }, journalPageState.filters);

            webRequest('GET', '/journals/data', params).done(function(resp) {
                if (!resp || resp.code !== 9999) {
                    alert((resp && resp.msg) ? resp.msg : '加载失败');
                    return;
                }

                var result = resp.result || {};
                var pagination = result.pagination || {};
                journalPageState.journals = result.journals || [];
                journalPageState.summary = result.summary || journalPageState.summary;
                journalPageState.currentPage = Number(pagination.current_page || targetPage || 1);
                journalPageState.lastPage = Math.max(1, Number(pagination.last_page || 1));
                journalPageState.total = Number(pagination.total || (result.summary && result.summary.total) || journalPageState.journals.length || 0);
                renderJournalsList();
            }).fail(function() {
                alert('加载失败，请重试');
            });
        }

        $(document).ready(function () {
            journalPageState.currentPage = Math.max(1, Number(getQueryParam('page') || 1));
            journalPageState.filters = readFiltersFromQuery();
            setFilterInputs(journalPageState.filters);

            $(document).on('click', '.delete_journal', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var journalId = $(this).data('journal-id');
                if (!confirm("确认要删除此手账吗？")) {
                    return false;
                }

                webRequest('DELETE', '/journal/' + journalId, {}).done(function(resp) {
                    if (!resp || resp.code !== 9999) {
                        alert((resp && resp.msg) || '删除失败');
                        return;
                    }
                    loadJournals(journalPageState.currentPage);
                }).fail(function() {
                    alert('删除失败，请重试');
                });
            });

            $('#journals_prev_page').on('click', function() {
                if (journalPageState.currentPage > 1) {
                    loadJournals(journalPageState.currentPage - 1);
                }
            });

            $('#journals_next_page').on('click', function() {
                if (journalPageState.currentPage < journalPageState.lastPage) {
                    loadJournals(journalPageState.currentPage + 1);
                }
            });

            $('#journalApplyFilters').on('click', function() {
                journalPageState.filters = getFilterValues();
                journalPageState.currentPage = 1;
                loadJournals(1);
            });

            $('#journalResetFilters').on('click', function() {
                journalPageState.filters = {
                    keyword: '',
                    start_date: '',
                    end_date: '',
                    type: ''
                };
                journalPageState.currentPage = 1;
                setFilterInputs(journalPageState.filters);
                loadJournals(1);
            });

            $('#journalKeywordFilter').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    journalPageState.filters = getFilterValues();
                    journalPageState.currentPage = 1;
                    loadJournals(1);
                }
            });

            loadJournals(journalPageState.currentPage);
        });
    </script>
@endsection
