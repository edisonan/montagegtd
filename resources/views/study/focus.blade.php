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
                <button class="btn btn-primary btn-sm w-full justify-center" onclick="setMode('countdown')">倒计时（番茄25分钟）</button>
                <button class="btn btn-outline btn-sm w-full justify-center" onclick="setMode('countup')">正计时</button>
            </div>

            <div class="text-center py-6 sm:py-8">
                <div id="timerText" class="text-4xl sm:text-6xl md:text-7xl font-bold text-gray-900 tracking-wider">25:00</div>
                <div id="modeText" class="text-sm text-gray-500 mt-2">当前模式：倒计时</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 mt-6 max-w-md mx-auto">
                    <button class="btn btn-primary w-full justify-center" onclick="startTimer()">开始</button>
                    <button class="btn btn-outline w-full justify-center" onclick="pauseTimer()">暂停</button>
                    <button class="btn btn-secondary w-full justify-center" onclick="resetTimer()">重置</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let mode = 'countdown';
        let running = false;
        let interval = null;
        let seconds = 25 * 60;

        function renderTimer() {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            const txt = h > 0
                ? String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0')
                : String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            document.getElementById('timerText').textContent = txt;
            document.getElementById('modeText').textContent = '当前模式：' + (mode === 'countdown' ? '倒计时' : '正计时');
        }

        function setMode(nextMode) {
            mode = nextMode;
            resetTimer();
        }

        function startTimer() {
            if (running) return;
            running = true;
            interval = setInterval(function() {
                if (mode === 'countdown') {
                    seconds = Math.max(0, seconds - 1);
                    if (seconds === 0) {
                        pauseTimer();
                        alert('本轮专注完成');
                    }
                } else {
                    seconds += 1;
                }
                renderTimer();
            }, 1000);
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
            seconds = mode === 'countdown' ? (25 * 60) : 0;
            renderTimer();
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderTimer();
        });
    </script>
@endsection
