@extends('layouts.app')

@section('title', '积分中心 - 蒙太奇')
@section('description', '查看您的成长积分和可用积分，管理积分变动记录')

@section('content')
    <div class="fade-in">
        <div class="max-w-6xl mx-auto">
            <!-- 页面标题 -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-medal text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">积分中心</h1>
                    <p class="text-gray-600 mt-1">记录您的成长历程，兑换专属权益</p>
                </div>
            </div>

            <!-- 成功消息提示 -->
            @include('common.success')
            @include('common.errors')

            <!-- 积分概览卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- 成长积分卡片 -->
                <div class="card card-elevated">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-seedling text-yellow-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">成长积分（GP）</h3>
                                    <p class="text-sm text-gray-500">记录您的成长轨迹</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                            累计积分
                        </span>
                        </div>

                        <div class="text-center">
                            <div id="gpBalanceValue" class="text-5xl font-bold text-yellow-600 mb-2">0</div>
                            <div class="text-sm text-gray-500 mb-6">持续积累，见证成长</div>

                            <!-- 积分进度条 -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">当前等级进度</span>
                                    <span id="gpProgressText" class="font-medium text-gray-900">0%</span>
                                </div>
                                <div class="progress h-2">
                                    <div id="gpProgressBar" class="progress-bar bg-gradient-to-r from-yellow-400 to-yellow-600"
                                         style="width: 0%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-2">
                                    <span>初级</span>
                                    <span>中级</span>
                                    <span>高级</span>
                                    <span>专家</span>
                                </div>
                            </div>

                            <div class="text-sm text-gray-600">
                                <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                                完成每日任务可获得更多成长积分
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 可用积分卡片 -->
                <div class="card card-elevated">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-coins text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">可用积分（AP）</h3>
                                    <p class="text-sm text-gray-500">可用于兑换权益</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                            可兑换
                        </span>
                        </div>

                        <div class="text-center">
                            <div id="apBalanceValue" class="text-5xl font-bold text-green-600 mb-2">0</div>
                            <div class="text-sm text-gray-500 mb-6">随时兑换，享受特权</div>

                            <!-- 兑换按钮 -->
                            <div class="space-y-3">
                                <button class="btn btn-primary w-full" onclick="showExchangeModal()">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    积分兑换
                                </button>
                                <a href="/point-mall" class="btn btn-primary w-full" style="background: linear-gradient(135deg, #0f766e, #14b8a6);">
                                    <i class="fas fa-store mr-2"></i>
                                    进入积分商城
                                </a>
                                <button class="btn btn-outline w-full" onclick="showRewards()">
                                    <i class="fas fa-gift mr-2"></i>
                                    查看奖励
                                </button>
                            </div>

                            <div class="text-sm text-gray-600 mt-6">
                                <i class="fas fa-info-circle text-green-500 mr-1"></i>
                                积分可用于兑换高级功能和使用时长
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 积分统计卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">连续打卡</p>
                                <p id="continuousDaysValue" class="text-xl font-bold text-gray-900">0天</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tasks text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">任务完成</p>
                                <p id="completedTasksValue" class="text-xl font-bold text-gray-900">0个</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">本月获得</p>
                                <p id="monthlyPointsValue" class="text-xl font-bold text-gray-900">+0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-trophy text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">当前排名</p>
                                <p id="userRankValue" class="text-xl font-bold text-gray-900">#-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 积分流水卡片 -->
            <div class="card">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">积分变动记录</h2>
                            <p class="text-sm text-gray-500 mt-1">查看您的积分获取和消耗明细</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- 时间筛选 -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">时间范围:</label>
                                <select class="input text-sm py-1 px-2" id="timeRange">
                                    <option value="all">全部记录</option>
                                    <option value="month">本月</option>
                                    <option value="week">本周</option>
                                    <option value="today">今日</option>
                                </select>
                            </div>

                            <!-- 类型筛选 -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">类型:</label>
                                <select class="input text-sm py-1 px-2" id="typeFilter">
                                    <option value="all">全部类型</option>
                                    <option value="gain">获得积分</option>
                                    <option value="consume">消耗积分</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="custom-scrollbar overflow-x-auto">
                        <table class="table">
                            <thead>
                            <tr>
                                <th style="width: 100px;">类型</th>
                                <th style="width: 120px;">积分变动</th>
                                <th style="min-width: 200px;">说明</th>
                                <th style="width: 140px;">余额</th>
                                <th style="width: 160px;">时间</th>
                                <th style="width: 80px;">操作</th>
                            </tr>
                            </thead>
                            <tbody id="pointRecordsBody">
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500">加载积分记录中...</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 积分获取指南 -->
            <div class="mt-8 card">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">如何获取更多积分？</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clock text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">完成番茄专注</h4>
                                    <p class="text-sm text-gray-600">每完成一个番茄钟可获得5-10积分</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-tasks text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">完成任务清单</h4>
                                    <p class="text-sm text-gray-600">完成任务根据难度获得10-50积分</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-newspaper text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">阅读文章</h4>
                                    <p class="text-sm text-gray-600">每阅读5篇文章可获得5积分</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar-alt text-yellow-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">每日签到</h4>
                                    <p class="text-sm text-gray-600">连续签到可获得递增积分奖励</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-lightbulb text-red-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">记录想法</h4>
                                    <p class="text-sm text-gray-600">每次记录想法可获得3积分</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-graduation-cap text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-1">学习课程</h4>
                                    <p class="text-sm text-gray-600">完成课程章节可获得20-100积分</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 兑换模态框 -->
    <div id="exchange_modal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">积分兑换</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('exchange_modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span class="font-medium text-blue-900">兑换规则说明</span>
                    </div>
                    <p class="text-sm text-blue-700">
                        可用积分（AP）可直接兑换为平台功能或服务。成长积分（GP）用于记录成长轨迹，不可直接兑换。
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        选择兑换项目
                    </label>
                    <select id="exchange_item" class="input w-full">
                        <option value="">请选择兑换项目</option>
                        <option value="premium_1month">高级会员（1个月）- 500 AP</option>
                        <option value="export_times">数据导出次数（10次）- 200 AP</option>
                        <option value="cloud_space">云存储空间（5GB）- 300 AP</option>
                        <option value="ai_tokens">AI助手额度（100次）- 400 AP</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        当前可用积分
                    </label>
                    <div class="flex items-center gap-2">
                        <div id="currentApBalanceValue" class="input w-full bg-gray-50">0 AP</div>
                        <div class="text-sm text-gray-500">可用</div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('exchange_modal')">
                            取消
                        </button>
                        <button type="button" class="btn btn-primary" onclick="confirmExchange()">
                            确认兑换
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 记录详情模态框 -->
    <div id="record_detail_modal" class="modal">
        <div class="modal-content max-w-lg">
            <!-- 内容由JS动态加载 -->
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var allPointRecords = [];
        var currentApBalance = 0;

        function formatNumber(n) {
            try {
                return Number(n || 0).toLocaleString('zh-CN');
            } catch (e) {
                return String(n || 0);
            }
        }

        function updateAccountView(account) {
            var gp = Number(account && account.gp_balance ? account.gp_balance : 0);
            var ap = Number(account && account.ap_balance ? account.ap_balance : 0);
            var percent = Math.min(100, Math.round((gp / 1000) * 100));
            currentApBalance = ap;

            var gpEl = document.getElementById('gpBalanceValue');
            var apEl = document.getElementById('apBalanceValue');
            var gpProgressText = document.getElementById('gpProgressText');
            var gpProgressBar = document.getElementById('gpProgressBar');
            var apModalEl = document.getElementById('currentApBalanceValue');

            if (gpEl) gpEl.textContent = formatNumber(gp);
            if (apEl) apEl.textContent = formatNumber(ap);
            if (gpProgressText) gpProgressText.textContent = percent + '%';
            if (gpProgressBar) gpProgressBar.style.width = percent + '%';
            if (apModalEl) apModalEl.textContent = ap + ' AP';
        }

        function updateStatsView(records) {
            var list = Array.isArray(records) ? records : [];
            var now = new Date();
            var currentYear = now.getFullYear();
            var currentMonth = now.getMonth();
            var monthlyGain = 0;
            var completedTasks = 0;

            list.forEach(function(record) {
                var amount = Number(record.change_amount || 0);
                var created = new Date(String(record.created_at || '').replace(' ', 'T'));
                if (!isNaN(created.getTime()) && created.getFullYear() === currentYear && created.getMonth() === currentMonth && amount > 0) {
                    monthlyGain += amount;
                }
                if (String(record.point_type || '') === 'task_complete' && amount > 0) {
                    completedTasks += 1;
                }
            });

            var continuousEl = document.getElementById('continuousDaysValue');
            var completedEl = document.getElementById('completedTasksValue');
            var monthlyEl = document.getElementById('monthlyPointsValue');
            var rankEl = document.getElementById('userRankValue');

            if (continuousEl) continuousEl.textContent = '0天';
            if (completedEl) completedEl.textContent = completedTasks + '个';
            if (monthlyEl) monthlyEl.textContent = '+' + monthlyGain;
            if (rankEl) rankEl.textContent = '#-';
        }

        function normalizeResponseArray(payload) {
            if (!payload) return [];
            if (Array.isArray(payload)) return payload;
            if (Array.isArray(payload.data)) return payload.data;
            return [];
        }

        function typeStyle(pointType) {
            var map = {
                task_complete: { color: 'blue', icon: 'fa-tasks' },
                daily_checkin: { color: 'green', icon: 'fa-calendar-check' },
                article_read: { color: 'purple', icon: 'fa-newspaper' },
                pomo_complete: { color: 'red', icon: 'fa-clock' },
                system_grant: { color: 'yellow', icon: 'fa-gift' },
                exchange_use: { color: 'gray', icon: 'fa-exchange-alt' }
            };
            return map[pointType] || { color: 'gray', icon: 'fa-circle' };
        }

        function formatDateTime(raw) {
            if (!raw) return { date: '-', time: '-' };
            var text = String(raw).replace('T', ' ');
            return {
                date: text.slice(0, 10),
                time: text.slice(11, 19) || '-'
            };
        }

        function renderRecords(records) {
            var tbody = document.getElementById('pointRecordsBody');
            if (!tbody) return;

            if (!records.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">暂无积分记录</td></tr>';
                return;
            }

            tbody.innerHTML = records.map(function(record) {
                var cfg = typeStyle(record.point_type);
                var amount = Number(record.change_amount || 0);
                var amountClass = amount > 0 ? 'text-success-color' : 'text-danger-color';
                var amountIcon = amount > 0 ? 'fa-plus-circle' : 'fa-minus-circle';
                var amountText = amount > 0 ? ('+' + amount) : String(amount);
                var time = formatDateTime(record.created_at);
                var description = record.description || '';

                return ''
                    + '<tr class="hover:bg-gray-50 transition-colors">'
                    + '<td><div class="flex items-center gap-2">'
                    + '<div class="w-8 h-8 bg-' + cfg.color + '-100 rounded-lg flex items-center justify-center">'
                    + '<i class="fas ' + cfg.icon + ' text-' + cfg.color + '-600 text-sm"></i></div>'
                    + '<span class="text-sm font-medium text-gray-900">' + (record.point_type || '-') + '</span>'
                    + '</div></td>'
                    + '<td><div class="flex items-center gap-1 ' + amountClass + ' font-semibold">'
                    + '<i class="fas ' + amountIcon + ' text-xs"></i>' + amountText + '</div></td>'
                    + '<td><div class="text-gray-700">' + $('<div>').text(description).html() + '</div></td>'
                    + '<td><div class="text-sm text-gray-600">余额: ' + Number(record.balance_after || 0) + '</div></td>'
                    + '<td><div class="text-sm text-gray-600"><div>' + time.date + '</div><div class="text-gray-500">' + time.time + '</div></div></td>'
                    + '<td><button onclick="showRecordDetail(' + Number(record.id || 0) + ')" class="text-blue-600 hover:text-blue-800 text-sm">详情</button></td>'
                    + '</tr>';
            }).join('');
        }

        function isInTimeRange(record, range) {
            if (range === 'all') return true;
            var created = new Date(String(record.created_at || '').replace(' ', 'T'));
            if (isNaN(created.getTime())) return false;
            var now = new Date();
            var start = new Date(now);

            if (range === 'today') {
                start.setHours(0, 0, 0, 0);
            } else if (range === 'week') {
                start.setDate(now.getDate() - 7);
            } else if (range === 'month') {
                start.setMonth(now.getMonth() - 1);
            }
            return created >= start;
        }

        function filterRecords() {
            var range = $('#timeRange').val();
            var type = $('#typeFilter').val();
            var filtered = allPointRecords.filter(function(record) {
                if (!isInTimeRange(record, range)) return false;
                var amount = Number(record.change_amount || 0);
                if (type === 'gain' && amount <= 0) return false;
                if (type === 'consume' && amount >= 0) return false;
                return true;
            });
            renderRecords(filtered);
        }

        function loadPointData() {
            if (!apiRequest) {
                return;
            }
            apiRequest('GET', '/points', {}).then(function(resp) {
                if (resp.code !== 9999 || !resp.result) {
                    throw new Error(resp.msg || '加载失败');
                }
                updateAccountView(resp.result.account || {});
                allPointRecords = normalizeResponseArray(resp.result.records);
                updateStatsView(allPointRecords);
                filterRecords();
            }).catch(function(error) {
                console.error('load points failed:', error);
            });
        }

        function showExchangeModal() {
            if (currentApBalance <= 0) {
                showNotification('可用积分不足，请先积累更多积分', 'warning');
                return;
            }
            $('#exchange_modal').addClass('show');
        }

        function confirmExchange() {
            var item = $('#exchange_item').val();
            if (!item) {
                showNotification('请选择兑换项目', 'warning');
                return;
            }
            showNotification('兑换功能开发中，即将上线', 'info');
            closeModal('exchange_modal');
        }

        function showRecordDetail(recordId) {
            showNotification('详情功能开发中，即将上线', 'info');
        }

        function showRewards() {
            showNotification('奖励列表功能开发中，即将上线', 'info');
        }

        function closeModal(modalId) {
            $('#' + modalId).removeClass('show');
        }

        function showNotification(message, type) {
            var level = type || 'info';
            var colors = { success: 'green', error: 'red', warning: 'yellow', info: 'blue' };
            var icon = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle', info: 'info-circle' };

            var notification = $(
                '<div class="fixed top-4 right-4 z-50 fade-in">' +
                '<div class="card shadow-lg border-l-4 border-' + colors[level] + '-500">' +
                '<div class="p-4 flex items-start gap-3">' +
                '<i class="fas fa-' + icon[level] + ' text-' + colors[level] + '-500 text-lg"></i>' +
                '<p class="text-sm text-gray-800">' + $('<div>').text(message).html() + '</p>' +
                '<button class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>' +
                '</div></div></div>'
            );

            $('body').append(notification);
            notification.find('button').click(function() { notification.remove(); });
            setTimeout(function() {
                notification.fadeOut(300, function() { $(this).remove(); });
            }, 3000);
        }

        $(document).ready(function() {
            $('#timeRange').change(filterRecords);
            $('#typeFilter').change(filterRecords);
            loadPointData();
        });
    </script>

    <style>
        /* 积分类型颜色定义 */
        .text-success-color {
            color: var(--success-color);
        }

        .text-danger-color {
            color: var(--danger-color);
        }

        /* 表格行悬停效果 */
        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: var(--gray-50);
        }

        /* 进度条自定义颜色 */
        .progress-bar.bg-gradient-to-r.from-yellow-400.to-yellow-600 {
            background: linear-gradient(90deg, #fbbf24, #d97706);
        }

        /* 统计卡片图标动画 */
        .card .w-10.h-10 {
            transition: transform 0.2s ease;
        }

        .card:hover .w-10.h-10 {
            transform: scale(1.05);
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .table {
                font-size: 14px;
            }

            .table th, .table td {
                padding: 8px;
            }

            .text-5xl {
                font-size: 2.5rem;
            }
        }

        /* 打印样式 */
        @media print {
            .btn, .zoom-controls, .action-buttons {
                display: none !important;
            }
        }
    </style>
@endsection
