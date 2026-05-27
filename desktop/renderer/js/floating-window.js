// 悬浮窗逻辑
class FloatingWindowController {
    constructor() {
        this.isDragging = false;
        this.dragOffset = { x: 0, y: 0 };
        this.initialize();
    }

    initialize() {
        this.bindEvents();
        this.setupDraggable();
        this.requestInitialStatus();
    }

    // 绑定事件
    bindEvents() {
        const hideBtn = document.getElementById('hideBtn');
        const closeBtn = document.getElementById('closeBtn');
        const startPomodoro = document.getElementById('startPomodoro');
        const showMain = document.getElementById('showMain');
        const addTask = document.getElementById('addTask');
        const viewTasks = document.getElementById('viewTasks');

        if (hideBtn) {
            hideBtn.addEventListener('click', () => {
                this.hideWindow();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.closeWindow();
            });
        }

        if (startPomodoro) {
            startPomodoro.addEventListener('click', () => {
                this.startPomodoro();
            });
        }

        if (showMain) {
            showMain.addEventListener('click', () => {
                this.showMainWindow();
            });
        }

        if (addTask) {
            addTask.addEventListener('click', () => {
                this.addTask();
            });
        }

        if (viewTasks) {
            viewTasks.addEventListener('click', () => {
                this.viewTasks();
            });
        }

        // 监听来自主进程的状态更新
        if (window.electronAPI) {
            window.electronAPI.onUpdateStatus((event, data) => {
                this.updateDisplay(data);
            });
        }
    }

    // 设置可拖动
    setupDraggable() {
        const dragHandle = document.getElementById('dragHandle');
        const floatingWindow = document.getElementById('floatingWindow');

        if (!dragHandle || !floatingWindow) return;

        dragHandle.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.dragOffset.x = e.clientX;
            this.dragOffset.y = e.clientY;
            floatingWindow.classList.add('dragging');
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;

            const deltaX = e.clientX - this.dragOffset.x;
            const deltaY = e.clientY - this.dragOffset.y;

            const rect = floatingWindow.getBoundingClientRect();
            const newX = rect.left + deltaX;
            const newY = rect.top + deltaY;

            floatingWindow.style.left = newX + 'px';
            floatingWindow.style.top = newY + 'px';

            this.dragOffset.x = e.clientX;
            this.dragOffset.y = e.clientY;
        });

        document.addEventListener('mouseup', () => {
            if (this.isDragging) {
                this.isDragging = false;
                floatingWindow.classList.remove('dragging');
            }
        });
    }

    // 隐藏窗口
    hideWindow() {
        if (window.electronAPI) {
            window.electronAPI.hideFloatingWindow();
        }
    }

    // 关闭窗口
    closeWindow() {
        if (window.electronAPI) {
            window.electronAPI.closeFloatingWindow();
        }
    }

    // 开始番茄钟
    startPomodoro() {
        if (window.electronAPI) {
            window.electronAPI.startPomodoroFromFloating();
        }
    }

    // 显示主窗口
    showMainWindow() {
        if (window.electronAPI) {
            window.electronAPI.showMainWindow();
        }
    }

    // 添加任务
    addTask() {
        if (window.electronAPI) {
            window.electronAPI.showQuickAddTask();
        }
    }

    // 查看任务
    viewTasks() {
        if (window.electronAPI) {
            window.electronAPI.showMainWindow();
            setTimeout(() => {
                window.electronAPI.navigateToTasks();
            }, 100);
        }
    }

    // 请求初始状态
    requestInitialStatus() {
        if (window.electronAPI) {
            window.electronAPI.requestFloatingStatus();
        }
    }

    // 更新显示
    updateDisplay(data) {
        const timerTime = document.getElementById('timerTime');
        const timerLabel = document.getElementById('timerLabel');
        const taskCount = document.getElementById('taskCount');

        if (timerTime && data.timer) {
            timerTime.textContent = data.timer;
        }

        if (timerLabel && data.status) {
            timerLabel.textContent = data.status;
        }

        if (taskCount && data.taskCount !== undefined) {
            taskCount.textContent = data.taskCount;
        }
    }
}

// 初始化悬浮窗控制器
const floatingWindowController = new FloatingWindowController();
window.FloatingWindowController = FloatingWindowController;
window.floatingWindowController = floatingWindowController;
