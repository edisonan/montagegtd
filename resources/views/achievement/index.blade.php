@extends('layouts.app')

@section('title', '成就勋章 - 蒙太奇')
@section('description', '查看和领取您的成就勋章，记录成长历程')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-trophy text-white text-2xl"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">成就中心</h1>
            <p class="text-gray-600">记录您的成长足迹，激励更好的自己</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="card"><div class="p-6"><div class="flex items-center justify-between"><div><p class="text-sm text-gray-500">已获得成就</p><p class="text-2xl font-bold text-gray-900 mt-1"><span id="achievementDoneCount">0</span> <span class="text-gray-400">/</span> <span id="achievementTotalCount">0</span></p></div><div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-blue-600 text-xl"></i></div></div></div></div>
                <div class="card"><div class="p-6"><div class="flex items-center justify-between"><div><p class="text-sm text-gray-500">已领取勋章</p><p class="text-2xl font-bold text-gray-900 mt-1"><span id="badgeDoneCount">0</span> <span class="text-gray-400">/</span> <span id="badgeTotalCount">0</span></p></div><div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"><i class="fas fa-medal text-purple-600 text-xl"></i></div></div></div></div>
                <div class="card"><div class="p-6"><div class="flex items-center justify-between"><div><p class="text-sm text-gray-500">总获得积分</p><p class="text-2xl font-bold text-gray-900 mt-1"><span id="totalPointValue">0</span> GP</p></div><div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"><i class="fas fa-coins text-green-600 text-xl"></i></div></div></div></div>
            </div>
        </div>

        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center"><i class="fas fa-star text-yellow-500 mr-3"></i>成就系统 <span id="achievementProgressBadge" class="ml-2 badge badge-primary">0 / 0</span></h2>
            </div>
            <div class="p-6">
                <div id="achievementListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="text-center py-12 text-gray-500 col-span-full">加载成就中...</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center"><i class="fas fa-medal text-purple-500 mr-3"></i>勋章系统 <span id="badgeProgressBadge" class="ml-2 badge badge-primary">0 / 0</span></h2>
            </div>
            <div class="p-6">
                <div id="badgeListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="text-center py-12 text-gray-500 col-span-full">加载勋章中...</div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center">
            <div class="inline-block p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl border border-blue-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">成就进度追踪</h3>
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2"><span>整体进度</span><span id="globalProgressText">0 / 0 (0%)</span></div>
                    <div class="progress"><div id="globalProgressBar" class="progress-bar" style="width: 0%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var achievementState = {
            list: []
        };

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function(c) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[c];
            });
        }

        function showToast(type, message) {
            var toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white ' + (type === 'success' ? 'bg-green-500' : 'bg-red-500');
            toast.innerHTML = '<div class="flex items-center"><i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-3"></i><span>' + escapeHtml(message) + '</span></div>';
            document.body.appendChild(toast);
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 3000);
        }

        function updateStats() {
            var all = achievementState.list;
            var achievements = all.filter(function(i) { return i.category === 'achievement'; });
            var badges = all.filter(function(i) { return i.category === 'badge'; });
            var achievedAll = all.filter(function(i) { return !!i.achieved; });
            var achievedAchievements = achievements.filter(function(i) { return !!i.achieved; });
            var achievedBadges = badges.filter(function(i) { return !!i.achieved; });
            var points = achievedAll.reduce(function(sum, item) { return sum + Number(item.point_value || 0); }, 0);

            document.getElementById('achievementDoneCount').textContent = String(achievedAchievements.length);
            document.getElementById('achievementTotalCount').textContent = String(achievements.length);
            document.getElementById('badgeDoneCount').textContent = String(achievedBadges.length);
            document.getElementById('badgeTotalCount').textContent = String(badges.length);
            document.getElementById('totalPointValue').textContent = String(points);
            document.getElementById('achievementProgressBadge').textContent = achievedAchievements.length + ' / ' + achievements.length;
            document.getElementById('badgeProgressBadge').textContent = achievedBadges.length + ' / ' + badges.length;

            var total = all.length;
            var done = achievedAll.length;
            var pct = total > 0 ? Math.round(done * 100 / total) : 0;
            document.getElementById('globalProgressText').textContent = done + ' / ' + total + ' (' + pct + '%)';
            document.getElementById('globalProgressBar').style.width = pct + '%';
        }

        function buildCard(item, isBadge) {
            var achieved = !!item.achieved;
            var progressTarget = Number(item.progress_target || 0);
            var progressPercent = Math.max(0, Math.min(100, Number(item.progress_percent || 0)));
            var showProgress = !achieved && progressTarget > 0;
            var status = achieved ? '<span class="badge badge-success">已获得</span>' : '<span class="badge badge-primary">进行中</span>';
            var reward = Number(item.point_value || 0) > 0
                ? '<div class="text-sm text-gray-700"><i class="fas fa-coins text-yellow-500 mr-1"></i>' + Number(item.point_value) + ' GP</div>'
                : '<div class="text-sm text-gray-400"><i class="fas fa-gift mr-1"></i>荣誉勋章</div>';
            var action = '';
            if (isBadge && !achieved) {
                var claimable = !!item.badge_claimable;
                action = '<button type="button" data-code="' + escapeHtml(item.code) + '" class="claim-achievement-btn btn btn-primary btn-sm ' + (claimable ? '' : 'opacity-50 cursor-not-allowed') + '" ' + (claimable ? '' : 'disabled') + '><i class="fas fa-gift mr-2"></i>' + (claimable ? '领取勋章' : '条件未达成') + '</button>';
            } else if (achieved) {
                action = '<div class="text-sm text-gray-500"><i class="fas fa-calendar-alt mr-1"></i>' + escapeHtml(item.achieved_at || '') + '</div>';
            }

            return '<div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-all duration-200 ' + (achieved ? 'bg-gradient-to-br from-white to-blue-50' : 'bg-white') + '">'
                + '<div class="flex items-start justify-between mb-4"><div class="w-14 h-14 rounded-xl flex items-center justify-center ' + (achieved ? 'bg-gradient-to-br from-yellow-400 to-orange-500' : 'bg-gray-100') + '"><i class="fas ' + (isBadge ? 'fa-medal' : 'fa-star') + ' ' + (achieved ? 'text-white' : 'text-gray-400') + ' text-xl"></i></div>' + status + '</div>'
                + '<h3 class="text-lg font-semibold text-gray-900 mb-2">' + escapeHtml(item.name) + '</h3>'
                + '<p class="text-gray-600 text-sm mb-4">' + escapeHtml(item.description) + '</p>'
                + (showProgress ? '<div class="mb-4"><div class="flex items-center justify-between text-xs text-gray-500 mb-1"><span>当前进度</span><span>' + escapeHtml(item.progress_text || ((Number(item.progress_current || 0)) + ' / ' + progressTarget)) + '</span></div><div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-blue-500 rounded-full" style="width:' + progressPercent + '%"></div></div></div>' : '')
                + '<div class="flex items-center justify-between pt-4 border-t border-gray-100">' + reward + '<div>' + action + '</div></div>'
                + '</div>';
        }

        function renderLists() {
            var achievements = achievementState.list.filter(function(i) { return i.category === 'achievement'; });
            var badges = achievementState.list.filter(function(i) { return i.category === 'badge'; });

            var achContainer = document.getElementById('achievementListContainer');
            var badgeContainer = document.getElementById('badgeListContainer');

            achContainer.innerHTML = achievements.length
                ? achievements.map(function(item) { return buildCard(item, false); }).join('')
                : '<div class="text-center py-12 text-gray-500 col-span-full">暂无成就配置</div>';

            badgeContainer.innerHTML = badges.length
                ? badges.map(function(item) { return buildCard(item, true); }).join('')
                : '<div class="text-center py-12 text-gray-500 col-span-full">暂无勋章配置</div>';
        }

        function loadAchievements() {
            if (!apiRequest) {
                document.getElementById('achievementListContainer').innerHTML = '<div class="text-center py-12 text-gray-500 col-span-full">API客户端未初始化</div>';
                document.getElementById('badgeListContainer').innerHTML = '<div class="text-center py-12 text-gray-500 col-span-full">API客户端未初始化</div>';
                return;
            }
            apiRequest('GET', '/achievements', {}).then(function(resp) {
                if (!resp || resp.code !== 9999) throw new Error((resp && resp.msg) || '加载失败');
                achievementState.list = Array.isArray(resp.result && resp.result.list) ? resp.result.list : [];
                updateStats();
                renderLists();
            }).catch(function() {
                document.getElementById('achievementListContainer').innerHTML = '<div class="text-center py-12 text-gray-500 col-span-full">加载失败，请稍后重试</div>';
                document.getElementById('badgeListContainer').innerHTML = '<div class="text-center py-12 text-gray-500 col-span-full">加载失败，请稍后重试</div>';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadAchievements();
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.claim-achievement-btn');
                if (!btn || btn.disabled) return;
                var code = btn.getAttribute('data-code');
                if (!code || !apiRequest) return;
                btn.disabled = true;
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>领取中...';
                apiRequest('POST', '/achievements/claim', { achievement_code: code }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        showToast('success', '领取成功');
                        loadAchievements();
                    } else {
                        showToast('error', (resp && resp.msg) ? resp.msg : '领取失败');
                    }
                }).catch(function() {
                    showToast('error', '领取失败，请稍后重试');
                }).finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        });
    </script>
@endsection
