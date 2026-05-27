const { Notification, app } = require('electron');
const Store = require('electron-store');

class ReminderManager {
    constructor() {
        this.store = new Store();
        this.reminderTimers = [];
        this.isAppActive = false;
        this.lastActivityTime = Date.now();
        this.reminderCount = 0;
        
        // 指数退避配置（分钟）
        this.intervals = [5, 10, 20, 40, 80, 160]; // 5min, 10min, 20min, 40min, 80min, 160min
        
        this.initialize();
    }

    initialize() {
        // 监听应用活动状态
        this.setupActivityTracking();
        
        // 启动提醒检查
        this.startReminderCheck();
        
        // 监听应用生命周期
        app.on('browser-window-focus', () => {
            this.markAsActive();
        });

        app.on('browser-window-blur', () => {
            this.markAsInactive();
        });
    }

    // 设置活动跟踪
    setupActivityTracking() {
        // 监听鼠标和键盘活动
        const { globalShortcut } = require('electron');
        
        // 定期检查用户活动
        setInterval(() => {
            this.checkUserActivity();
        }, 60000); // 每分钟检查一次
    }

    // 检查用户活动
    checkUserActivity() {
        const now = Date.now();
        const timeSinceLastActivity = now - this.lastActivityTime;
        
        // 如果超过5分钟没有活动，认为应用不活跃
        if (timeSinceLastActivity > 5 * 60 * 1000) {
            this.markAsInactive();
        }
    }

    // 标记为活跃状态
    markAsActive() {
        this.isAppActive = true;
        this.lastActivityTime = Date.now();
        this.reminderCount = 0;
        
        // 清除所有提醒定时器
        this.clearAllReminders();
        
        console.log('应用标记为活跃状态，提醒已重置');
    }

    // 标记为非活跃状态
    markAsInactive() {
        if (!this.isAppActive) {
            return; // 已经是非活跃状态
        }
        
        this.isAppActive = false;
        console.log('应用标记为非活跃状态，开始提醒计时');
        
        // 开始指数退避提醒
        this.startExponentialBackoffReminders();
    }

    // 开始指数退避提醒
    startExponentialBackoffReminders() {
        this.reminderCount = 0;
        this.scheduleNextReminder();
    }

    // 安排下一次提醒
    scheduleNextReminder() {
        if (this.isAppActive || this.reminderCount >= this.intervals.length) {
            return;
        }

        const intervalMinutes = this.intervals[this.reminderCount];
        const intervalMs = intervalMinutes * 60 * 1000;

        console.log(`安排第 ${this.reminderCount + 1} 次提醒，${intervalMinutes} 分钟后`);

        const timer = setTimeout(() => {
            if (!this.isAppActive) {
                this.sendReminder();
                this.reminderCount++;
                this.scheduleNextReminder();
            }
        }, intervalMs);

        this.reminderTimers.push(timer);
    }

    // 发送提醒
    sendReminder() {
        const settings = this.store.get('settings') || {};
        
        if (!settings.notifications) {
            return; // 通知已禁用
        }

        const messages = [
            {
                title: '💪 该回来工作了！',
                body: '您已经离开5分钟了，回来继续专注吧！'
            },
            {
                title: '⏰ 时间过得真快',
                body: '已经10分钟了，您的任务还在等待您呢！'
            },
            {
                title: '🎯 保持专注',
                body: '20分钟过去了，高效工作从专注开始！'
            },
            {
                title: '🚀 加油！',
                body: '40分钟了，回来完成今天的任务目标吧！'
            },
            {
                title: '💡 温馨提醒',
                body: '已经超过1小时了，建议休息一下眼睛~'
            },
            {
                title: '🌟 最后提醒',
                body: '很久没看到您了，希望一切都好！'
            }
        ];

        const message = messages[Math.min(this.reminderCount, messages.length - 1)];

        const notification = new Notification({
            title: message.title,
            body: message.body,
            icon: './assets/icons/notification-icon.png',
            silent: false
        });

        notification.show();

        // 点击通知时打开应用
        notification.on('click', () => {
            this.openMainWindow();
        });

        console.log(`发送第 ${this.reminderCount + 1} 次提醒: ${message.title}`);
    }

    // 打开主窗口
    openMainWindow() {
        const { BrowserWindow } = require('electron');
        const windows = BrowserWindow.getAllWindows();
        
        if (windows.length > 0) {
            const mainWindow = windows[0];
            if (mainWindow.isMinimized()) {
                mainWindow.restore();
            }
            mainWindow.show();
            mainWindow.focus();
        }
        
        this.markAsActive();
    }

    // 清除所有提醒定时器
    clearAllReminders() {
        this.reminderTimers.forEach(timer => {
            clearTimeout(timer);
        });
        this.reminderTimers = [];
    }

    // 开始提醒检查
    startReminderCheck() {
        // 每分钟检查一次应用状态
        setInterval(() => {
            this.performStatusCheck();
        }, 60000);
    }

    // 执行状态检查
    performStatusCheck() {
        const { BrowserWindow } = require('electron');
        const windows = BrowserWindow.getAllWindows();
        
        if (windows.length === 0) {
            // 没有窗口，认为应用未开启
            if (!this.isAppActive) {
                this.markAsInactive();
            }
        } else {
            // 有窗口存在
            const mainWindow = windows[0];
            if (mainWindow.isFocused()) {
                this.markAsActive();
            }
        }
    }

    // 手动触发提醒（用于测试）
    triggerManualReminder() {
        this.reminderCount = 0;
        this.sendReminder();
    }

    // 获取当前状态
    getStatus() {
        return {
            isActive: this.isAppActive,
            reminderCount: this.reminderCount,
            lastActivity: new Date(this.lastActivityTime).toISOString(),
            nextReminderIn: this.reminderCount < this.intervals.length ? 
                this.intervals[this.reminderCount] : null
        };
    }

    // 清理资源
    cleanup() {
        this.clearAllReminders();
        console.log('提醒管理器已清理');
    }
}

module.exports = ReminderManager;
