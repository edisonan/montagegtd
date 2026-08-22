@extends('layouts.app')

@section('title', '学习专注 - 蒙太奇')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="card p-4 sm:p-6 md:p-8 max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">学习专注</h1>
                    <p class="text-sm text-gray-500 mt-1">当前任务：{{ $task->name }}</p>
                </div>
                <a href="/study" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-1"></i>返回学习页</a>
            </div>

            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 mb-6">
                <div class="text-sm text-blue-700">任务内容</div>
                <div class="text-gray-800 mt-1">{{ $task->content ?: '暂无内容' }}</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                <button class="btn btn-primary btn-sm w-full justify-center" onclick="setMode('countdown')">倒计时（番茄钟）</button>
                <button class="btn btn-outline btn-sm w-full justify-center" onclick="setMode('countup')">正计时</button>
            </div>

            <div class="flex items-center justify-center gap-3 mb-4" id="durationWrap">
                <label class="text-sm text-gray-600">单轮时长：</label>
                <select class="input text-sm py-1 px-2" id="durationSelect" onchange="updateDuration()">
                    <option value="15">15 分钟</option>
                    <option value="25" selected>25 分钟</option>
                    <option value="45">45 分钟</option>
                    <option value="60">60 分钟</option>
                </select>
            </div>

            <div class="text-center py-6 sm:py-8">
                <div id="timerText" class="text-4xl sm:text-6xl md:text-7xl font-bold text-gray-900 tracking-wider">25:00</div>
                <div id="modeText" class="text-sm text-gray-500 mt-2">当前模式：倒计时</div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 sm:gap-3 mt-6 max-w-xl mx-auto">
                    <button class="btn btn-primary w-full justify-center" onclick="startTimer()">开始</button>
                    <button class="btn btn-outline w-full justify-center" onclick="pauseTimer()">暂停</button>
                    <button class="btn btn-secondary w-full justify-center" onclick="resetTimer()">重置</button>
                    <button class="btn btn-outline w-full justify-center" onclick="endRound(false)">结束本轮</button>
                </div>
                <div class="text-xs text-gray-400 mt-4">专注结束会自动记录到学习时长，并可在下方打卡</div>
            </div>

            <!-- 完成/打卡面板 -->
            <div id="finishPanel" class="hidden rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-center gap-2 text-emerald-700 font-semibold">
                    <i class="fas fa-check-circle"></i>
                    <span id="finishTitle">本轮专注完成！</span>
                </div>
                <div class="text-sm text-emerald-800 mt-1" id="finishText">已记录到学习时长。</div>
                <div class="mt-4">
                    <textarea id="finishCheckinContent" rows="3" maxlength="5000" class="input w-full" placeholder="记录一下这轮学到了什么（可选）"></textarea>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button class="btn btn-primary btn-sm" id="finishCheckinBtn" onclick="submitFinishCheckin()">
                            <i class="fas fa-check mr-1"></i>提交打卡
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="resetTimer()">再专注一轮</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const taskId = {{ (int)$task->id }};
        const taskName = @json($task->name);
        let mode = 'countdown';
        let running = false;
        let interval = null;
        let durationSeconds = 25 * 60;
        let remaining = durationSeconds;
        let elapsed = 0;
        let finishAt = 0;        // 倒计时结束时间戳（ms）
        let segmentStart = 0;    // 本轮开始时间戳（ms）
        let lastRoundSeconds = 0; // 最近一轮的有效秒数（完成轮=整轮时长，手动结束=已计时长）
        let lastRecorded = false;

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function toast(message, type) {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    timerProgressBar: true,
                    icon: type || 'info',
                    title: message,
                    showConfirmButton: false
                });
                return;
            }
            alert(message);
        }

        function fmt(secs) {
            const s = Math.max(0, Math.floor(secs));
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            const ss = s % 60;
            return h > 0 ? pad(h) + ':' + pad(m) + ':' + pad(ss) : pad(m) + ':' + pad(ss);
        }

        function renderTimer() {
            document.getElementById('timerText').textContent = fmt(mode === 'countdown' ? remaining : elapsed);
            document.getElementById('modeText').textContent = '当前模式：' + (mode === 'countdown' ? '倒计时' : '正计时');
        }

        function setMode(nextMode) {
            if (running) {
                // 正在计时不允许切换，防止误触
                toast('请先暂停或结束本轮，再切换模式', 'warning');
                return;
            }
            mode = nextMode;
            resetTimer();
            document.getElementById('durationWrap').style.display = mode === 'countdown' ? 'flex' : 'none';
        }

        function updateDuration() {
            if (mode !== 'countdown' || running) return;
            durationSeconds = Number(document.getElementById('durationSelect').value || 25) * 60;
            remaining = durationSeconds;
            renderTimer();
        }

        function startTimer() {
            if (running) return;
            running = true;
            if (mode === 'countdown') {
                finishAt = Date.now() + remaining * 1000;
            } else {
                segmentStart = Date.now() - elapsed * 1000;
            }
            interval = setInterval(function() {
                if (mode === 'countdown') {
                    remaining = Math.max(0, (finishAt - Date.now()) / 1000);
                    if (remaining <= 0) {
                        pauseTimer();
                        completeRound();
                        return;
                    }
                } else {
                    elapsed = Math.floor((Date.now() - segmentStart) / 1000);
                }
                renderTimer();
            }, 250);
            renderTimer();
        }

        function pauseTimer() {
            running = false;
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        }

        function resetTimer() {
            pauseTimer();
            remaining = durationSeconds;
            elapsed = 0;
            finishAt = 0;
            segmentStart = 0;
            hideFinishPanel();
            renderTimer();
        }

        function currentSeconds() {
            if (mode === 'countdown') {
                return Math.max(0, Math.floor(remaining));
            }
            return Math.floor(elapsed);
        }

        function apiFetch(path, options) {
            const fetcher = window.taskApiFetch || window.fetch;
            const opts = Object.assign({ method: 'POST' }, options || {});
            if (!opts.headers) opts.headers = {};
            opts.headers['Accept'] = 'application/json';
            opts.headers['X-Requested-With'] = 'XMLHttpRequest';
            const csrfNode = document.querySelector('meta[name="csrf-token"]');
            if (csrfNode && !opts.headers['X-CSRF-TOKEN']) opts.headers['X-CSRF-TOKEN'] = csrfNode.getAttribute('content');
            return fetcher('/api/v2' + path, opts);
        }

        // 上报专注会话
        async function recordSession(completed, secondsOverride) {
            const secs = (typeof secondsOverride === 'number') ? Math.floor(secondsOverride) : currentSeconds();
            if (secs <= 0) return null;
            lastRoundSeconds = secs;
            const pad0 = n => String(n).padStart(2, '0');
            const now = new Date();
            const fmtDT = d => d.getFullYear() + '-' + pad0(d.getMonth() + 1) + '-' + pad0(d.getDate()) +
                ' ' + pad0(d.getHours()) + ':' + pad0(d.getMinutes()) + ':' + pad0(d.getSeconds());
            const ended = fmtDT(now);
            const started = new Date(now.getTime() - secs * 1000);
            const payload = {
                started_at: fmtDT(started),
                ended_at: ended,
                duration_seconds: secs,
                completed: completed ? 1 : 0
            };
            const resp = await apiFetch('/study/tasks/' + taskId + '/focus-sessions', {
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            try {
                return await resp.json();
            } catch (e) {
                return null;
            }
        }

        function completeRound() {
            lastRecorded = true;
            // 倒计时归零时 remaining 已为 0，必须用整轮时长上报
            recordSession(true, durationSeconds).then(function() {
                showFinishPanel();
            });
        }

        function endRound(completed) {
            const secs = currentSeconds();
            if (secs > 0) {
                recordSession(completed, secs);
            }
            pauseTimer();
            resetTimer();
            if (completed) {
                showFinishPanel();
            }
        }

        function formatDurationLabel(secs) {
            const s = Math.max(1, Math.floor(secs));
            if (s >= 60) {
                const m = Math.round(s / 60);
                return m + ' 分钟';
            }
            return s + ' 秒';
        }

        function showFinishPanel() {
            const secs = lastRoundSeconds || durationSeconds;
            document.getElementById('finishTitle').textContent = mode === 'countdown' ? '本轮专注完成！' : '本轮已结束';
            document.getElementById('finishText').textContent = '已记录 ' + formatDurationLabel(secs) + ' 到学习时长。';
            const panel = document.getElementById('finishPanel');
            panel.classList.remove('hidden');
            document.getElementById('finishCheckinBtn').disabled = false;
            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function hideFinishPanel() {
            document.getElementById('finishPanel').classList.add('hidden');
            document.getElementById('finishCheckinContent').value = '';
        }

        async function submitFinishCheckin() {
            const btn = document.getElementById('finishCheckinBtn');
            const content = document.getElementById('finishCheckinContent').value.trim();
            const fd = new FormData();
            fd.append('content', content);
            const pad0 = n => String(n).padStart(2, '0');
            const d = new Date();
            fd.append('date', d.getFullYear() + '-' + pad0(d.getMonth() + 1) + '-' + pad0(d.getDate()));
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>提交中...';
            try {
                const resp = await apiFetch('/study/tasks/' + taskId + '/checkin', { body: fd });
                const data = await resp.json();
                if (data && Number(data.code) === 9999) {
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i>已打卡';
                    const title = document.getElementById('finishTitle');
                    title.textContent = '打卡成功！';
                    const text = document.getElementById('finishText');
                    text.textContent = content ? '已记录打卡内容，回到学习页可查看。' : '已提交打卡，回到学习页可查看。';
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i>提交打卡';
                    toast((data && data.msg) ? data.msg : '打卡失败', 'error');
                }
            } catch (e) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i>提交打卡';
                toast('网络错误，请稍后重试', 'error');
            }
        }

        // 页面离开时尽量补记一次（keepalive，带 token）
        window.addEventListener('pagehide', function() {
            if (running) {
                const secs = currentSeconds();
                if (secs > 0) {
                    const pad0 = n => String(n).padStart(2, '0');
                    const now = new Date();
                    const fmtDT = d => d.getFullYear() + '-' + pad0(d.getMonth() + 1) + '-' + pad0(d.getDate()) +
                        ' ' + pad0(d.getHours()) + ':' + pad0(d.getMinutes()) + ':' + pad0(d.getSeconds());
                    const payload = {
                        started_at: fmtDT(new Date(now.getTime() - secs * 1000)),
                        ended_at: fmtDT(now),
                        duration_seconds: secs,
                        completed: 0
                    };
                    try {
                        apiFetch('/study/tasks/' + taskId + '/focus-sessions', {
                            keepalive: true,
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                    } catch (e) {}
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            renderTimer();
        });
    </script>
@endsection