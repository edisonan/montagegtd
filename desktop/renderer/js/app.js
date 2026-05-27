// 主应用入口文件
class App {
    constructor() {
        this.initialize();
    }

    async initialize() {
        // 等待DOM加载完成
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        console.log('Montage GTD Desktop App initialized');
        
        // 初始化各个模块
        this.setupEventListeners();
        this.checkAuth();
        this.setupKeyboardShortcuts();
        
        // 显示欢迎信息
        this.showWelcomeMessage();
    }

    // 设置事件监听器
    setupEventListeners() {
        // 监听来自导航模块的视图切换事件
        document.addEventListener('viewChange', (event) => {
            console.log('View changed to:', event.detail.view);
        });

        // 监听窗口大小变化
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // 监听在线/离线状态
        window.addEventListener('online', () => {
            this.handleOnlineStatus(true);
        });

        window.addEventListener('offline', () => {
            this.handleOnlineStatus(false);
        });
        
        // 监听来自主进程的悬浮窗相关消息
        if (window.electronAPI) {
            // 监听快速添加模态框显示请求
            window.electronAPI.onShowQuickAddModal && window.electronAPI.onShowQuickAddModal(() => {
                if (window.navigationManager) {
                    window.navigationManager.showQuickAddModal();
                }
            });
            
            // 监听视图导航请求
            window.electronAPI.onNavigateToView && window.electronAPI.onNavigateToView((event, view) => {
                if (window.navigationManager) {
                    window.navigationManager.switchToView(view);
                }
            });
        }
    }

    // 检查认证状态
    checkAuth() {
        if (window.authManager) {
            // authManager会自动处理认证检查
            console.log('Authentication manager initialized');
        }
    }

    // 设置键盘快捷键
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + N: 快速添加任务
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                if (window.navigationManager) {
                    window.navigationManager.showQuickAddModal();
                }
            }

            // Ctrl/Cmd + T: 切换到任务视图
            if ((e.ctrlKey || e.metaKey) && e.key === 't') {
                e.preventDefault();
                if (window.navigationManager) {
                    window.navigationManager.switchToView('tasks');
                }
            }

            // Ctrl/Cmd + P: 切换到番茄钟视图
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                if (window.navigationManager) {
                    window.navigationManager.switchToView('pomodoro');
                }
            }

            // Ctrl/Cmd + D: 切换到仪表盘视图
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                if (window.navigationManager) {
                    window.navigationManager.switchToView('dashboard');
                }
            }

            // Escape: 关闭模态框
            if (e.key === 'Escape') {
                if (window.navigationManager) {
                    window.navigationManager.hideQuickAddModal();
                }
            }

            // F5: 刷新当前视图数据
            if (e.key === 'F5') {
                e.preventDefault();
                this.refreshCurrentView();
            }
        });
    }

    // 处理窗口大小变化
    handleResize() {
        // 可以在这里添加响应式布局的调整逻辑
        const isMobile = window.innerWidth <= 768;
        document.body.classList.toggle('mobile-view', isMobile);
    }

    // 处理在线状态变化
    handleOnlineStatus(isOnline) {
        if (isOnline) {
            console.log('Application is online');
            // 可以同步离线数据
            this.syncOfflineData();
        } else {
            console.log('Application is offline');
            // 显示离线提示
            this.showOfflineNotification();
        }
    }

    // 同步离线数据
    async syncOfflineData() {
        // 实现离线数据同步逻辑
        console.log('Syncing offline data...');
    }

    // 显示离线通知
    async showOfflineNotification() {
        if (window.electronAPI) {
            await window.electronAPI.sendNotification({
                title: '离线模式',
                body: '您当前处于离线状态，部分功能可能不可用'
            });
        }
    }

    // 刷新当前视图
    refreshCurrentView() {
        const currentView = window.navigationManager ? 
            window.navigationManager.getCurrentView() : 'dashboard';

        switch (currentView) {
            case 'dashboard':
                if (window.dashboardManager) {
                    window.dashboardManager.refresh();
                }
                break;
            case 'tasks':
                if (window.taskManager) {
                    window.taskManager.refresh();
                }
                break;
            case 'pomodoro':
                if (window.pomodoroManager) {
                    window.pomodoroManager.refresh();
                }
                break;
        }
    }

    // 显示欢迎信息
    async showWelcomeMessage() {
        // 延迟显示欢迎信息，确保其他组件已初始化
        setTimeout(async () => {
            if (window.authManager && window.authManager.isAuthenticated) {
                const user = window.authManager.getCurrentUser();
                if (user && user.name) {
                    await window.electronAPI.sendNotification({
                        title: '欢迎使用 Montage GTD',
                        body: `你好，${user.name}！开始高效的一天吧。`
                    });
                }
            }
        }, 2000);
    }

    // 获取应用版本信息
    getVersionInfo() {
        return {
            app: '1.0.0',
            electron: window.versions ? window.versions.electron() : 'unknown',
            node: window.versions ? window.versions.node() : 'unknown',
            chrome: window.versions ? window.versions.chrome() : 'unknown'
        };
    }

    // 清理资源
    cleanup() {
        // 清理定时器
        if (window.pomodoroManager) {
            window.pomodoroManager.pauseTimer();
        }

        // 清理事件监听器
        console.log('App cleanup completed');
    }
}

// 创建全局应用实例
const app = new App();
window.App = App;
window.app = app;

// 页面卸载时清理资源
window.addEventListener('beforeunload', () => {
    if (window.app) {
        window.app.cleanup();
    }
});

// 导出应用实例供其他模块使用
export default app;
