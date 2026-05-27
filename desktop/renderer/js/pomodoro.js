// 番茄钟管理模块
class PomodoroManager {
    constructor() {
        this.timer = null;
        this.timeLeft = 25 * 60; // 默认25分钟
        this.totalTime = 25 * 60;
        this.isRunning = false;
        this.isWorkMode = true; // true为工作模式，false为休息模式
        this.sessionCount = 0;
        this.initialize();
    }

    initialize() {
        this.bindEvents();
        this.loadSettings();
        this.updateDisplay();
        this.loadTodayStats();
    }

    // 绑定事件
    bindEvents() {
        const startBtn = document.getElementById('startTimer');
        const pauseBtn = document.getElementById('pauseTimer');
        const resetBtn = document.getElementById('resetTimer');
        const workDuration = document.getElementById('workDuration');
        const restDuration = document.getElementById('restDuration');

        if (startBtn) {
            startBtn.addEventListener('click', () => {
                this.startTimer();
            });
        }

        if (pauseBtn) {
            pauseBtn.addEventListener('click', () => {
                this.pauseTimer();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetTimer();
            });
        }

        if (workDuration) {
            workDuration.addEventListener('change', () => {
                this.updateWorkDuration(parseInt(workDuration.value));
            });
        }

        if (restDuration) {
            restDuration.addEventListener('change', () => {
                this.updateRestDuration(parseInt(restDuration.value));
            });
        }

        // 监听来自主进程的消息
        if (window.electronAPI && window.electronAPI.onStartPomodoro) {
            window.electronAPI.onStartPomodoro(() => {
                this.startTimer();
            });
        }
    }

    // 加载设置
    async loadSettings() {
        try {
            const settings = await window.electronAPI.getConfig('settings');
            if (settings) {
                const workDuration = document.getElementById('workDuration');
                const restDuration = document.getElementById('restDuration');
                
                if (workDuration && settings.workDuration) {
                    workDuration.value = settings.workDuration;
                    this.totalTime = settings.workDuration * 60;
                    this.timeLeft = this.totalTime;
                }
                
                if (restDuration && settings.restDuration) {
                    restDuration.value = settings.restDuration;
                }
            }
        } catch (error) {
            console.error('Failed to load pomodoro settings:', error);
        }
    }

    // 保存设置
    async saveSettings() {
        try {
            const workDuration = document.getElementById('workDuration');
            const restDuration = document.getElementById('restDuration');
            
            await window.electronAPI.setConfig('settings', {
                workDuration: parseInt(workDuration.value),
                restDuration: parseInt(restDuration.value)
            });
        } catch (error) {
            console.error('Failed to save pomodoro settings:', error);
        }
    }

    // 开始计时器
    startTimer() {
        if (this.isRunning) return;

        this.isRunning = true;
        this.updateControls();
        
        this.timer = setInterval(() => {
            this.tick();
        }, 1000);

        this.updateStatus(this.isWorkMode ? '专注中...' : '休息中...');
    }

    // 暂停计时器
    pauseTimer() {
        if (!this.isRunning) return;

        this.isRunning = false;
        clearInterval(this.timer);
        this.updateControls();
        this.updateStatus('已暂停');
    }

    // 重置计时器
    resetTimer() {
        this.pauseTimer();
        this.timeLeft = this.isWorkMode ? 
            parseInt(document.getElementById('workDuration').value) * 60 :
            parseInt(document.getElementById('restDuration').value) * 60;
        this.totalTime = this.timeLeft;
        this.updateDisplay();
        this.updateStatus('准备开始');
    }

    // 计时器滴答
    tick() {
        this.timeLeft--;

        if (this.timeLeft <= 0) {
            this.completeSession();
        } else {
            this.updateDisplay();
            this.updateProgress();
        }
    }

    // 完成一个会话
    async completeSession() {
        this.pauseTimer();

        if (this.isWorkMode) {
            // 工作会话完成
            this.sessionCount++;
            await this.saveFocusSession();
            
            await window.electronAPI.sendNotification({
                title: '番茄钟完成！',
                body: `太棒了！你完成了第 ${this.sessionCount} 个番茄钟。现在休息一下吧。`
            });

            // 切换到休息模式
            this.switchToRestMode();
        } else {
            // 休息会话完成
            await window.electronAPI.sendNotification({
                title: '休息结束',
                body: '休息时间结束，准备好开始下一个番茄钟了吗？'
            });

            // 切换回工作模式
            this.switchToWorkMode();
        }

        this.updateDisplay();
        this.loadTodayStats();
    }

    // 切换到休息模式
    switchToRestMode() {
        this.isWorkMode = false;
        this.timeLeft = parseInt(document.getElementById('restDuration').value) * 60;
        this.totalTime = this.timeLeft;
        this.updateStatus('准备休息');
    }

    // 切换到工作模式
    switchToWorkMode() {
        this.isWorkMode = true;
        this.timeLeft = parseInt(document.getElementById('workDuration').value) * 60;
        this.totalTime = this.timeLeft;
        this.updateStatus('准备开始');
    }

    // 保存专注记录到后端
    async saveFocusSession() {
        try {
            const workDuration = parseInt(document.getElementById('workDuration').value);
            
            const focusData = {
                name: `番茄钟 #${this.sessionCount}`,
                duration: workDuration,
                status: 3 // 已完成状态
            };

            await api.startFocus(focusData);
        } catch (error) {
            console.error('Failed to save focus session:', error);
        }
    }

    // 更新显示
    updateDisplay() {
        const minutes = Math.floor(this.timeLeft / 60);
        const seconds = this.timeLeft % 60;
        const timeDisplay = document.getElementById('timeDisplay');
        
        if (timeDisplay) {
            timeDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        // 更新页面标题
        document.title = `${minutes}:${seconds.toString().padStart(2, '0')} - Montage GTD`;
    }

    // 更新进度环
    updateProgress() {
        const progressCircle = document.querySelector('.timer-progress');
        if (progressCircle) {
            const circumference = 2 * Math.PI * 45; // r=45
            const progress = this.timeLeft / this.totalTime;
            const dashoffset = circumference * (1 - progress);
            progressCircle.style.strokeDashoffset = dashoffset;
        }
    }

    // 更新状态文本
    updateStatus(status) {
        const statusEl = document.getElementById('timerStatus');
        if (statusEl) {
            statusEl.textContent = status;
        }
    }

    // 更新控制按钮状态
    updateControls() {
        const startBtn = document.getElementById('startTimer');
        const pauseBtn = document.getElementById('pauseTimer');

        if (startBtn) {
            startBtn.disabled = this.isRunning;
        }

        if (pauseBtn) {
            pauseBtn.disabled = !this.isRunning;
        }
    }

    // 更新工作时长
    updateWorkDuration(minutes) {
        if (!this.isRunning && this.isWorkMode) {
            this.totalTime = minutes * 60;
            this.timeLeft = this.totalTime;
            this.updateDisplay();
            this.saveSettings();
        }
    }

    // 更新休息时长
    updateRestDuration(minutes) {
        if (!this.isRunning && !this.isWorkMode) {
            this.totalTime = minutes * 60;
            this.timeLeft = this.totalTime;
            this.updateDisplay();
            this.saveSettings();
        }
    }

    // 加载今日统计
    async loadTodayStats() {
        try {
            const todayFocuss = await api.getTodayFocuss();
            const completedFocuss = (todayFocuss || []).filter(f => f.status === 3);
            
            const todayPomodoros = document.getElementById('todayPomodoros');
            const totalFocusTime = document.getElementById('totalFocusTime');
            
            if (todayPomodoros) {
                todayPomodoros.textContent = completedFocuss.length;
            }
            
            if (totalFocusTime) {
                const totalTime = completedFocuss.reduce((sum, focus) => {
                    return sum + (focus.duration || 25);
                }, 0);
                totalFocusTime.textContent = totalTime;
            }
        } catch (error) {
            console.error('Failed to load today stats:', error);
        }
    }

    // 刷新统计
    refresh() {
        this.loadTodayStats();
    }

    // 获取当前会话数
    getSessionCount() {
        return this.sessionCount;
    }

    // 检查是否正在运行
    isTimerRunning() {
        return this.isRunning;
    }
}

// 创建全局番茄钟管理器实例
const pomodoroManager = new PomodoroManager();
window.PomodoroManager = PomodoroManager;
window.pomodoroManager = pomodoroManager;
