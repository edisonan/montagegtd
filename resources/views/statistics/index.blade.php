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
            height: 280px;
            min-height: 280px;
            display: flex;
            flex-direction: column;
        }

        .chart-container {
            flex: 1;
            min-height: 200px;
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
        }

        .summary-label {
            color: var(--gray-600);
        }

        .summary-value {
            font-weight: 600;
            color: var(--gray-900);
        }

        .date-range-selector {
            background: var(--gray-100);
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 24px;
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
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和日期选择 -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">数据统计</h1>
                <p class="text-gray-600">分析您的工作效率和学习成长</p>
            </div>
            <div class="flex items-center space-x-4">
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
                            <p class="text-2xl font-bold text-gray-900">{{ $total_pomos ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        统计周期: {{$start_date}} - {{$end_date}}
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
                            <p class="text-2xl font-bold text-gray-900">{{ $completed_tasks ?? 0 }}</p>
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
                            <p class="text-2xl font-bold text-gray-900">{{ $total_articles ?? 0 }}</p>
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
                            <p class="text-2xl font-bold text-gray-900">{{ $total_notes ?? 0 }}</p>
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
                            <span class="summary-value">{{ $avg_daily_pomos ?? 0 }} 个</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">最长专注时间</span>
                            <span class="summary-value">{{ $max_focus_time ?? '0' }} 分钟</span>
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
                            <span class="summary-value">{{ $avg_completion_time ?? '0' }} 小时</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">四象限分布</span>
                            <span class="summary-value">{{ $quadrant_distribution ?? '-' }}</span>
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
                            <span class="summary-value">{{ $avg_note_length ?? '0' }} 字</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">最高产日期</span>
                            <span class="summary-value">{{ $most_productive_day ?? '-' }}</span>
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
                            <span class="summary-value">{{ $avg_reading_time ?? '0' }} 分钟</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">收藏率</span>
                            <span class="summary-value">{{ $star_rate ?? '0' }}%</span>
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
                            <span class="summary-value">{{ $avg_nodes ?? '0' }} 个</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">最大层级</span>
                            <span class="summary-value">{{ $max_depth ?? '0' }} 层</span>
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
                            <span class="summary-value">{{ $active_days ?? '0' }} 天</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">总投入时间</span>
                            <span class="summary-value">{{ $total_focus_hours ?? '0' }} 小时</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 详细数据表格 -->
        <div class="card mb-8">
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
                        <tbody>
                        @if(isset($daily_stats) && count($daily_stats) > 0)
                            @foreach($daily_stats as $stat)
                                <tr class="hover:bg-gray-50">
                                    <td class="font-medium">{{ $stat['date'] ?? '-' }}</td>
                                    <td>{{ $stat['pomos'] ?? 0 }}</td>
                                    <td>{{ $stat['completed_tasks'] ?? 0 }}</td>
                                    <td>{{ $stat['notes'] ?? 0 }}</td>
                                    <td>{{ $stat['articles'] ?? 0 }}</td>
                                    <td>{{ $stat['minds'] ?? 0 }}</td>
                                    <td>
                                        @php
                                            $score = $stat['efficiency_score'] ?? 0;
                                            $color = $score >= 80 ? 'text-green-600 bg-green-100' :
                                                     ($score >= 60 ? 'text-yellow-600 bg-yellow-100' :
                                                     'text-red-600 bg-red-100');
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                            {{ $score }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-database text-2xl mb-3 block"></i>
                                    <p>暂无详细统计数据</p>
                                </td>
                            </tr>
                        @endif
                        </tbody>
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

    <!-- ECharts脚本 -->

    <script type="text/javascript">
        $(document).ready(function() {

            console.log('统计页面已加载');
            console.log('检查 ECharts:', typeof echarts);

            // 先检查 echarts 是否加载
            if (typeof echarts === 'undefined') {
                console.error('ECharts 未加载，尝试重新加载...');
                loadEChartsManually();
            } else {
                console.log('ECharts 版本:', echarts.version);
                initCharts();
                initDateRangeSelector();
            }

            // 窗口大小变化时重绘图表
            $(window).on('resize', function() {
                if (typeof echarts !== 'undefined') {
                    setTimeout(initCharts, 300);
                }
            });
        });

        // 初始化图表
        function initCharts() {
            try {
                // 初始化ECharts实例
                const chartOptions = {
                    pomo: {
                        dom: document.getElementById('pomo_main'),
                        data: {!! $pomo_bar_statistics ?? 'null' !!}
                    },
                    task: {
                        dom: document.getElementById('task_main'),
                        data: {!! $task_bar_statistics ?? 'null' !!}
                    },
                    note: {
                        dom: document.getElementById('note_main'),
                        data: {!! $note_bar_statistics ?? 'null' !!}
                    },
                    article: {
                        dom: document.getElementById('article_main'),
                        data: {!! $article_bar_statistics ?? 'null' !!}
                    },
                    mind: {
                        dom: document.getElementById('mind_main'),
                        data: {!! $mind_bar_statistics ?? 'null' !!}
                    },
                    pie: {
                        dom: document.getElementById('pie_main'),
                        data: {!! $count_pie_statistics ?? 'null' !!}
                    }
                };

                // 渲染所有图表
                Object.entries(chartOptions).forEach(([key, option]) => {
                    if (option.dom && option.data) {
                        renderChart(option.dom, option.data, key);
                    } else {
                        showEmptyState(option.dom, key);
                    }
                });

            } catch(error) {
                console.error('初始化图表时出错:', error);
            }
        }

        // 渲染单个图表
        function renderChart(domElement, optionData, chartType) {
            try {
                const chart = echarts.init(domElement);

                // 统一主题和样式配置
                const defaultOptions = {
                    backgroundColor: 'transparent',
                    tooltip: {
                        trigger: 'item',
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        textStyle: {
                            color: '#334155',
                            fontSize: 12
                        },
                        formatter: function(params) {
                            if (params.seriesType === 'pie') {
                                return `${params.name}<br/>${params.value} (${params.percent}%)`;
                            }
                            return `${params.name}<br/>${params.seriesName}: ${params.value}`;
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

                // 合并配置
                const finalOption = Object.assign({}, defaultOptions, optionData);

                // 设置图表
                chart.setOption(finalOption);

                // 添加加载完成样式
                $(domElement).find('.empty-chart').fadeOut(200);

            } catch(error) {
                console.error(`渲染${chartType}图表时出错:`, error);
                showEmptyState(domElement, chartType, '渲染失败');
            }
        }

        // 显示空状态
        function showEmptyState(domElement, chartType, message = '暂无数据') {
            const emptyHtml = `
            <div class="empty-chart">
                <div>
                    <i class="fas fa-chart-${getChartIcon(chartType)}"></i>
                    <p>${message}</p>
                </div>
            </div>
        `;
            $(domElement).html(emptyHtml);
        }

        // 获取图表图标
        function getChartIcon(chartType) {
            const icons = {
                pomo: 'line',
                task: 'bar',
                note: 'area',
                article: 'pie',
                mind: 'sitemap',
                pie: 'pie'
            };
            return icons[chartType] || 'bar';
        }

        // 初始化日期范围选择器
        function initDateRangeSelector() {
            $('.date-btn').on('click', function() {
                const range = $(this).data('range');

                // 更新按钮状态
                $('.date-btn').removeClass('active');
                $(this).addClass('active');

                // 如果是自定义范围，显示日期选择器
                if (range === 'custom') {
                    showCustomDatePicker();
                    return;
                }

                // 发送请求获取新数据
                loadStatisticsByRange(range);
            });
        }

        // 显示自定义日期选择器
        function showCustomDatePicker() {
            // 这里可以添加自定义日期选择器的逻辑
            alert('自定义日期选择功能开发中...');
        }

        // 按范围加载统计数据
        function loadStatisticsByRange(range) {
            const ranges = {
                week: '本周',
                month: '本月',
                quarter: '本季',
                year: '本年'
            };

            // 显示加载状态
            showLoading();

            // 发送AJAX请求
            $.ajax({
                url: '{{ url("/statistics") }}',
                type: 'GET',
                data: { range: range },
                success: function(response) {
                    // 这里应该根据实际返回的数据结构来更新页面
                    // 由于这是示例，我们只刷新页面
                    location.reload();
                },
                error: function() {
                    hideLoading();
                    alert('加载数据失败，请重试');
                }
            });
        }

        // 显示加载状态
        function showLoading() {
            $('.stat-grid').addClass('opacity-50');
            $('.date-btn').prop('disabled', true);
        }

        // 隐藏加载状态
        function hideLoading() {
            $('.stat-grid').removeClass('opacity-50');
            $('.date-btn').prop('disabled', false);
        }
    </script>
@endsection