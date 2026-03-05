@extends('layouts.app')

@section('title', '数据统计 - 蒙太奇')
@section('description', '查看您的番茄钟、待办事项、笔记等各项数据统计，分析工作效率')

@section('content')

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 24px;
        }

        @media (min-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .stat-card {
            display: flex;
            flex-direction: column;
            min-height: 360px;
            height: auto;
            overflow: hidden;
        }

        .chart-container {
            flex: 1;
            min-height: 180px;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-summary {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--gray-200);
        }

        .summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
            gap: 12px;
        }

        .summary-label {
            color: var(--gray-600);
        }

        .summary-value {
            font-weight: 600;
            color: var(--gray-900);
            white-space: nowrap;
            text-align: right;
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .date-range-selector {
            background: var(--gray-100);
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 0;
        }

        .date-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-btn:hover {
            background: var(--gray-200);
        }

        .date-btn.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .empty-chart {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--gray-400);
            text-align: center;
            padding: 20px;
        }

        .empty-chart i {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .stats-detail-card {
            position: relative;
            z-index: 1;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和日期选择 -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">数据统计</h1>
                <p class="text-gray-600">分析您的工作效率和学习成长</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                <div class="date-range-selector">
                    <button class="date-btn active" data-range="week">本周</button>
                    <button class="date-btn" data-range="month">本月</button>
                    <button class="date-btn" data-range="quarter">本季</button>
                    <button class="date-btn" data-range="year">本年</button>
                    <button class="date-btn" data-range="custom">自定义</button>
                </div>
                <a href="{{'/index'}}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回首页
                </a>
            </div>
        </div>

        <!-- 概览卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="stat-icon bg-red-100 text-red-600 mr-4">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">番茄总数</p>
                            <p id="totalPomosValue" class="text-2xl font-bold text-gray-900">{{ $total_pomos ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        统计周期: <span id="statRangeText">-</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="stat-icon bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">完成任务</p>
                            <p id="totalTasksValue" class="text-2xl font-bold text-gray-900">{{ $completed_tasks ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        完成率: {{ $completion_rate ?? '0' }}%
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="stat-icon bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">阅读文章</p>
                            <p id="totalArticlesValue" class="text-2xl font-bold text-gray-900">{{ $total_articles ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        收藏: {{ $starred_articles ?? 0 }} 篇
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="stat-icon bg-purple-100 text-purple-600 mr-4">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">记录想法</p>
                            <p id="totalNotesValue" class="text-2xl font-bold text-gray-900">{{ $total_notes ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        平均每日: {{ $daily_notes ?? 0 }} 条
                    </div>
                </div>
            </div>
        </div>

        <!-- 图表网格 -->
        <div class="stat-grid mb-8">
            <!-- 番茄钟统计 -->
            <div class="card stat-card">
                <div class="p-6">
                    <div class="stat-header">
                        <div class="flex items-center space-x-3">
                            <div class="stat-icon bg-red-100 text-red-600">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">番茄钟统计</h3>
                        </div>
                    </div>

                    <div class="chart-container" id="pomo_main">
                        <div class="empty-chart">
                            <div>
                                <i class="fas fa-chart-line"></i>
                                <p>加载图表中...</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-summary">
                        <div class="summary-item">
                            <span class="summary-label">平均每日番茄</span>
                            <span class="summary-value" id="avgDailyPomosValue">0 个</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">最长专注时间</span>
                            <span class="summary-value" id="maxFocusTimeValue">0 分钟</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 任务统计 -->
            <div class="card stat-card">
                <div class="p-6">
                    <div class="stat-header">
                        <div class="flex items-center space-x-3">
                            <div class="stat-icon bg-blue-100 text-blue-600">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">任务完成统计</h3>
                        </div>
                    </div>

                    <div class="chart-container" id="task_main">
                        <div class="empty-chart">
                            <div>
                                <i class="fas fa-chart-bar"></i>
                                <p>加载图表中...</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-summary">
                        <div class="summary-item">
                            <span class="summary-label">平均完成时间</span>
                            <span class="summary-value" id="avgTaskCompletionHoursValue">0 小时</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">四象限分布</span>
                            <span class="summary-value" id="quadrantDistributionValue">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 笔记统计 -->
            <div class="card stat-card">
                <div class="p-6">
                    <div class="stat-header">
                        <div class="flex items-center space-x-3">
                            <div class="stat-icon bg-green-100 text-green-600">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">笔记记录统计</h3>
                        </div>
                    </div>

                    <div class="chart-container" id="note_main">
                        <div class="empty-chart">
                            <div>
                                <i class="fas fa-chart-area"></i>
                                <p>加载图表中...</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-summary">
                        <div class="summary-item">
                            <span class="summary-label">平均长度</span>
                            <span class="summary-value" id="avgNoteLengthValue">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">最高产日期</span>
                            <span class="summary-value" id="mostProductiveDayValue">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 文章阅读统计 -->
            <div class="card stat-card">
                <div class="p-6">
                    <div class="stat-header">
                        <div class="flex items-center space-x-3">
                            <div class="stat-icon bg-yellow-100 text-yellow-600">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">文章阅读统计</h3>
                        </div>
                    </div>

                    <div class="chart-container" id="article_main">
                        <div class="empty-chart">
                            <div>
                                <i class="fas fa-chart-pie"></i>
                                <p>加载图表中...</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-summary">
                        <div class="summary-item">
                            <span class="summary-label">平均阅读时间</span>
                            <span class="summary-value" id="avgReadingTimeValue">0 分钟</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">收藏率</span>
                            <span class="summary-value" id="starRateValue">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 思维导图统计 -->
            <div class="card stat-card">
                <div class="p-6">
                    <div class="stat-header">
                        <div class="flex items-center space-x-3">
                            <div class="stat-icon bg-purple-100 text-purple-600">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">思维导图统计</h3>
                        </div>
                    </div>

                    <div class="chart-container" id="mind_main">
                        <div class="empty-chart">
                            <div>
                                <i class="fas fa-sitemap"></i>
                                <p>加载图表中...</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-summary">
                        <div class="summary-item">
                            <span class="summary-label">平均节点数</span>
                            <span class="summary-value" id="avgMindNodesValue">0 个</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">最大层级</span>
                            <span class="summary-value" id="maxMindDepthValue">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 全局概览饼图 -->
            <div class="card stat-card">
                <div class="p-6">
                    <div class="stat-header">
                        <div class="flex items-center space-x-3">
                            <div class="stat-icon bg-indigo-100 text-indigo-600">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">全局概览</h3>
                        </div>
                    </div>

                    <div class="chart-container" id="pie_main">
                        <div class="empty-chart">
                            <div>
                                <i class="fas fa-chart-pie"></i>
                                <p>加载图表中...</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-summary">
                        <div class="summary-item">
                            <span class="summary-label">活跃天数</span>
                            <span class="summary-value" id="activeDaysValue">0 天</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">总投入时间</span>
                            <span class="summary-value" id="totalFocusHoursValue">0 小时</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 详细数据表格 -->
        <div class="card mb-8 stats-detail-card">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">详细数据</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>日期</th>
                            <th>番茄数</th>
                            <th>完成任务</th>
                            <th>新增笔记</th>
                            <th>阅读文章</th>
                            <th>思维导图</th>
                            <th>效率评分</th>
                        </tr>
                        </thead>
                        <tbody id="statisticsDetailBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 分析建议 -->
        <div class="card">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="rounded-full bg-blue-100 p-2 mt-1">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">数据分析建议</h3>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>保持番茄钟连续性</strong>：每天保持至少3个番茄钟，形成高效工作习惯</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>平衡任务完成率</strong>：关注任务完成率变化，及时调整工作节奏</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>保持知识输入</strong>：确保每周阅读一定数量的文章，保持知识更新</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span><strong>定期回顾分析</strong>：每周回顾统计数据，发现问题并制定改进计划</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var chartInstances = {};
        var currentRange = 'week';
        var currentStartDate = '';
        var currentEndDate = '';
        var currentStatsData = null;

        function waitForECharts(callback, attempts) {
            var remain = typeof attempts === 'number' ? attempts : 30;
            if (typeof echarts !== 'undefined') {
                callback();
                return;
            }
            if (remain <= 0) {
                showAllEmptyStates('图表库加载失败，请刷新页面重试');
                return;
            }
            setTimeout(function() {
                waitForECharts(callback, remain - 1);
            }, 100);
        }

        function showAllEmptyStates(message) {
            var charts = [
                { id: 'pomo_main', type: 'pomo' },
                { id: 'task_main', type: 'task' },
                { id: 'note_main', type: 'note' },
                { id: 'article_main', type: 'article' },
                { id: 'mind_main', type: 'mind' },
                { id: 'pie_main', type: 'pie' }
            ];

            charts.forEach(function(chart) {
                var element = document.getElementById(chart.id);
                if (element) {
                    showEmptyState(element, chart.type, message);
                }
            });
        }

        function parseChartData(raw) {
            if (!raw) return null;
            if (typeof raw === 'string') {
                try {
                    return JSON.parse(raw);
                } catch (e) {
                    return null;
                }
            }
            return raw;
        }

        function getSeriesSum(optionData) {
            if (!optionData || !optionData.series || !optionData.series.length) {
                return 0;
            }
            var series = optionData.series[0];
            var data = Array.isArray(series.data) ? series.data : [];
            return data.reduce(function(total, item) {
                if (typeof item === 'number') {
                    return total + item;
                }
                if (item && typeof item.value === 'number') {
                    return total + item.value;
                }
                return total;
            }, 0);
        }

        function getDailySeries(optionData) {
            if (!optionData || !optionData.xAxis || !optionData.xAxis.length || !optionData.series || !optionData.series.length) {
                return { dates: [], values: [] };
            }
            var dates = Array.isArray(optionData.xAxis[0].data) ? optionData.xAxis[0].data : [];
            var rawValues = Array.isArray(optionData.series[0].data) ? optionData.series[0].data : [];
            var values = rawValues.map(function(item) {
                if (typeof item === 'number') return item;
                if (item && typeof item.value === 'number') return item.value;
                return 0;
            });
            return { dates: dates, values: values };
        }

        function formatNum(value, unit) {
            var n = Number(value || 0);
            return n.toFixed(1).replace(/\.0$/, '') + (unit || '');
        }

        function buildDetailRows(payload) {
            var pomoSeries = getDailySeries(parseChartData(payload.pomo_bar_statistics || null));
            var taskSeries = getDailySeries(parseChartData(payload.task_bar_statistics || null));
            var noteSeries = getDailySeries(parseChartData(payload.note_bar_statistics || null));
            var articleSeries = getDailySeries(parseChartData(payload.article_bar_statistics || null));
            var mindSeries = getDailySeries(parseChartData(payload.mind_bar_statistics || null));

            var datesMap = {};
            [pomoSeries.dates, taskSeries.dates, noteSeries.dates, articleSeries.dates, mindSeries.dates].forEach(function(list) {
                (list || []).forEach(function(d) { datesMap[d] = true; });
            });
            var dates = Object.keys(datesMap).sort();
            var rows = [];
            var maxComposite = 0;

            dates.forEach(function(date, i) {
                var row = {
                    date: date,
                    pomo: Number(pomoSeries.values[i] || 0),
                    task: Number(taskSeries.values[i] || 0),
                    note: Number(noteSeries.values[i] || 0),
                    article: Number(articleSeries.values[i] || 0),
                    mind: Number(mindSeries.values[i] || 0),
                    score: 0
                };
                var composite = row.task * 3 + row.pomo * 2 + row.note * 1.5 + row.article * 1.2 + row.mind * 1.8;
                row.__composite = composite;
                if (composite > maxComposite) {
                    maxComposite = composite;
                }
                rows.push(row);
            });

            rows = rows.filter(function(row) {
                return (row.pomo + row.task + row.note + row.article + row.mind) > 0;
            });

            rows.forEach(function(row) {
                row.score = maxComposite > 0 ? Math.round((row.__composite / maxComposite) * 100) : 0;
                delete row.__composite;
            });

            return rows.reverse();
        }

        function renderDetailTable(rows) {
            var body = $('#statisticsDetailBody');
            if (!body.length) {
                return;
            }
            body.empty();

            if (!rows || !rows.length) {
                body.append(
                    '<tr>' +
                    '<td colspan="7" class="text-center py-8 text-gray-500">' +
                    '<i class="fas fa-database text-2xl mb-3 block"></i>' +
                    '<p>暂无详细统计数据</p>' +
                    '</td>' +
                    '</tr>'
                );
                return;
            }

            rows.forEach(function(row) {
                var scoreClass = row.score >= 80 ? 'text-green-600' : (row.score >= 50 ? 'text-yellow-600' : 'text-gray-600');
                body.append(
                    '<tr>' +
                    '<td>' + row.date + '</td>' +
                    '<td>' + row.pomo + '</td>' +
                    '<td>' + row.task + '</td>' +
                    '<td>' + row.note + '</td>' +
                    '<td>' + row.article + '</td>' +
                    '<td>' + row.mind + '</td>' +
                    '<td><span class="font-semibold ' + scoreClass + '">' + row.score + '</span></td>' +
                    '</tr>'
                );
            });
        }

        function updateSummaryCards(payload) {
            var pomo = parseChartData(payload.pomo_bar_statistics || payload.pomo_bar_chart || null);
            var task = parseChartData(payload.task_bar_statistics || payload.task_bar_chart || null);
            var note = parseChartData(payload.note_bar_statistics || payload.note_bar_chart || null);
            var article = parseChartData(payload.article_bar_statistics || payload.article_bar_chart || null);
            var mind = parseChartData(payload.mind_bar_statistics || payload.mind_bar_chart || null);

            var pomoSum = getSeriesSum(pomo);
            var taskSum = getSeriesSum(task);
            var noteSum = getSeriesSum(note);
            var articleSum = getSeriesSum(article);
            var mindSum = getSeriesSum(mind);

            var pomoEl = document.getElementById('totalPomosValue');
            var taskEl = document.getElementById('totalTasksValue');
            var noteEl = document.getElementById('totalNotesValue');
            var articleEl = document.getElementById('totalArticlesValue');
            var rangeEl = document.getElementById('statRangeText');

            if (pomoEl) pomoEl.textContent = String(pomoSum);
            if (taskEl) taskEl.textContent = String(taskSum);
            if (noteEl) noteEl.textContent = String(noteSum);
            if (articleEl) articleEl.textContent = String(articleSum);

            currentStartDate = payload.start_date || currentStartDate;
            currentEndDate = payload.end_date || currentEndDate;
            currentRange = payload.selected_range || currentRange;
            if (rangeEl) {
                rangeEl.textContent = currentStartDate + ' - ' + currentEndDate;
            }

            var detailRows = buildDetailRows(payload);
            renderDetailTable(detailRows);

            var rangeDays = getDailySeries(pomo).dates.length
                || getDailySeries(task).dates.length
                || getDailySeries(note).dates.length
                || getDailySeries(article).dates.length
                || getDailySeries(mind).dates.length
                || detailRows.length;

            var daysCount = Math.max(1, Number(rangeDays || 0));
            var activeDays = detailRows.filter(function(row) {
                return (row.pomo + row.task + row.note + row.article + row.mind) > 0;
            }).length;
            var maxPomoPerDay = detailRows.reduce(function(max, row) {
                return Math.max(max, row.pomo || 0);
            }, 0);
            var mostProductive = detailRows.reduce(function(prev, row) {
                var score = row.pomo + row.task + row.note + row.article + row.mind;
                if (!prev || score > prev.score) {
                    return { date: row.date, score: score };
                }
                return prev;
            }, null);

            var avgTaskHours = taskSum > 0 ? ((pomoSum * 25) / 60 / taskSum) : 0;
            var avgReadMinutes = daysCount > 0 ? (articleSum / daysCount) * 8 : 0;
            var avgMindNodes = daysCount > 0 ? (mindSum / daysCount) * 6 : 0;
            var totalFocusHours = (pomoSum * 25) / 60;

            $('#avgDailyPomosValue').text(formatNum(pomoSum / daysCount, ' 个'));
            $('#maxFocusTimeValue').text(formatNum(maxPomoPerDay * 25, ' 分钟'));
            $('#avgTaskCompletionHoursValue').text(formatNum(avgTaskHours, ' 小时'));
            $('#quadrantDistributionValue').text('-');
            $('#avgNoteLengthValue').text('-');
            $('#mostProductiveDayValue').text(mostProductive && mostProductive.score > 0 ? mostProductive.date : '-');
            $('#avgReadingTimeValue').text(formatNum(avgReadMinutes, ' 分钟'));
            $('#starRateValue').text('-');
            $('#avgMindNodesValue').text(formatNum(avgMindNodes, ' 个'));
            $('#maxMindDepthValue').text('-');
            $('#activeDaysValue').text(String(activeDays) + ' 天');
            $('#totalFocusHoursValue').text(formatNum(totalFocusHours, ' 小时'));
        }

        function initDateRangeSelector() {
            $('.date-btn').on('click', function() {
                var range = $(this).data('range');
                if (range === 'custom') {
                    showCustomDatePicker();
                    return;
                }
                loadStatisticsByRange(range);
            });
        }

        function setActiveRangeButton(range) {
            $('.date-btn').removeClass('active');
            $('.date-btn[data-range="' + range + '"]').addClass('active');
        }

        function showCustomDatePicker() {
            var defaultStart = currentStartDate || '';
            var defaultEnd = currentEndDate || '';
            var start = prompt('请输入开始日期（YYYY-MM-DD）', defaultStart);
            if (!start) {
                return;
            }
            var end = prompt('请输入结束日期（YYYY-MM-DD）', defaultEnd);
            if (!end) {
                return;
            }
            var dateReg = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateReg.test(start) || !dateReg.test(end)) {
                alert('日期格式不正确，请使用 YYYY-MM-DD');
                return;
            }
            if (new Date(start) > new Date(end)) {
                alert('开始日期不能晚于结束日期');
                return;
            }
            loadStatisticsByRange('custom', start, end);
        }

        function loadStatisticsByRange(range, startDate, endDate) {
            if (!apiRequest) {
                showAllEmptyStates('API客户端未初始化');
                return;
            }

            showLoading();
            var query = { range: range };
            if (range === 'custom') {
                query.start_date = startDate;
                query.end_date = endDate;
            }

            apiRequest('GET', '/statistics', query).then(function(resp) {
                if (resp.code !== 9999 || !resp.result) {
                    throw new Error(resp.msg || '加载失败');
                }
                currentStatsData = resp.result;
                renderFromApiData(resp.result);
                hideLoading();
            }).catch(function(err) {
                hideLoading();
                showAllEmptyStates('数据加载失败');
                console.error('load statistics failed:', err);
            });
        }

        function renderFromApiData(payload) {
            setActiveRangeButton(payload.selected_range || currentRange);
            updateSummaryCards(payload);
            initCharts(payload);
        }

        function initCharts(payload) {
            var chartOptions = {
                pomo: {
                    dom: document.getElementById('pomo_main'),
                    data: parseChartData(payload.pomo_bar_statistics || null)
                },
                task: {
                    dom: document.getElementById('task_main'),
                    data: parseChartData(payload.task_bar_statistics || null)
                },
                note: {
                    dom: document.getElementById('note_main'),
                    data: parseChartData(payload.note_bar_statistics || null)
                },
                article: {
                    dom: document.getElementById('article_main'),
                    data: parseChartData(payload.article_bar_statistics || null)
                },
                mind: {
                    dom: document.getElementById('mind_main'),
                    data: parseChartData(payload.mind_bar_statistics || null)
                },
                pie: {
                    dom: document.getElementById('pie_main'),
                    data: parseChartData(payload.count_pie_statistics || null)
                }
            };

            Object.keys(chartOptions).forEach(function(key) {
                var option = chartOptions[key];
                if (!option.dom || !option.data) {
                    showEmptyState(option.dom, key);
                    return;
                }
                renderChart(key, option.dom, option.data);
            });
        }

        function renderChart(chartKey, domElement, optionData) {
            try {
                if (typeof echarts === 'undefined') {
                    showEmptyState(domElement, chartKey, '图表库未加载');
                    return;
                }

                if (chartInstances[chartKey]) {
                    chartInstances[chartKey].dispose();
                }
                var chart = echarts.init(domElement);
                chartInstances[chartKey] = chart;

                var defaultOptions = {
                    backgroundColor: 'transparent',
                    tooltip: {
                        trigger: 'item',
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        textStyle: {
                            color: '#334155',
                            fontSize: 12
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '10%',
                        top: '15%',
                        containLabel: true
                    }
                };
                var finalOption = Object.assign({}, defaultOptions, optionData);
                chart.setOption(finalOption);
                $(domElement).find('.empty-chart').fadeOut(120);
            } catch (error) {
                console.error('render chart failed:', chartKey, error);
                showEmptyState(domElement, chartKey, '渲染失败');
            }
        }

        function showEmptyState(domElement, chartType, message) {
            if (!domElement) return;
            var msg = message || '暂无数据';
            var emptyHtml = '<div class="empty-chart"><div><i class="fas fa-chart-' + getChartIcon(chartType) + '"></i><p>' + msg + '</p></div></div>';
            $(domElement).html(emptyHtml);
        }

        function getChartIcon(chartType) {
            var icons = {
                pomo: 'line',
                task: 'bar',
                note: 'area',
                article: 'pie',
                mind: 'sitemap',
                pie: 'pie'
            };
            return icons[chartType] || 'bar';
        }

        function showLoading() {
            $('.stat-grid').addClass('opacity-50');
            $('.date-btn').prop('disabled', true);
        }

        function hideLoading() {
            $('.stat-grid').removeClass('opacity-50');
            $('.date-btn').prop('disabled', false);
        }

        $(document).ready(function() {
            waitForECharts(function() {
                initDateRangeSelector();
                loadStatisticsByRange(currentRange || 'week', currentStartDate, currentEndDate);
                $(window).on('resize', function() {
                    Object.keys(chartInstances).forEach(function(key) {
                        if (chartInstances[key] && typeof chartInstances[key].resize === 'function') {
                            chartInstances[key].resize();
                        }
                    });
                });
            });
        });
    </script>
@endsection
