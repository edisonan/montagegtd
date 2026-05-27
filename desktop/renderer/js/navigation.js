// 导航管理模块
class NavigationManager {
    constructor() {
        this.currentView = 'dashboard';
        this.initialize();
    }

    initialize() {
        this.bindNavigationEvents();
        this.bindSidebarEvents();
        this.bindTopBarEvents();
    }

    // 绑定侧边栏导航事件
    bindNavigationEvents() {
        const navItems = document.querySelectorAll('.nav-item');
        
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                const view = item.getAttribute('data-view');
                if (view) {
                    this.switchToView(view);
                }
            });
        });
    }

    // 绑定侧边栏其他事件
    bindSidebarEvents() {
        const settingsBtn = document.getElementById('settingsBtn');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', () => {
                this.showSettingsModal();
            });
        }
    }

    // 绑定顶部工具栏事件
    bindTopBarEvents() {
        const searchBtn = document.getElementById('searchBtn');
        const notificationBtn = document.getElementById('notificationBtn');
        const quickAddBtn = document.getElementById('quickAddBtn');

        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.showSearchModal();
            });
        }

        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                this.showNotificationsPanel();
            });
        }

        if (quickAddBtn) {
            quickAddBtn.addEventListener('click', () => {
                this.showQuickAddModal();
            });
        }
    }

    // 切换到指定视图
    switchToView(viewName) {
        // 隐藏所有视图
        const allViews = document.querySelectorAll('.view-content');
        allViews.forEach(view => {
            view.classList.remove('active');
        });

        // 移除所有导航项的active状态
        const allNavItems = document.querySelectorAll('.nav-item');
        allNavItems.forEach(item => {
            item.classList.remove('active');
        });

        // 显示目标视图
        const targetView = document.getElementById(`${viewName}View`);
        if (targetView) {
            targetView.classList.add('active');
        }

        // 激活对应的导航项
        const targetNavItem = document.querySelector(`[data-view="${viewName}"]`);
        if (targetNavItem) {
            targetNavItem.classList.add('active');
        }

        // 更新页面标题
        this.updatePageTitle(viewName);

        // 记录当前视图
        this.currentView = viewName;

        // 触发视图切换事件
        this.triggerViewChange(viewName);
    }

    // 更新页面标题
    updatePageTitle(viewName) {
        const titles = {
            'dashboard': '仪表盘',
            'tasks': '任务管理',
            'pomodoro': '番茄钟',
            'calendar': '日历',
            'notes': '笔记',
            'statistics': '统计分析'
        };

        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) {
            pageTitle.textContent = titles[viewName] || viewName;
        }
    }

    // 触发视图切换事件
    triggerViewChange(viewName) {
        // 可以在这里添加视图特定的初始化逻辑
        switch (viewName) {
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

    // 显示快速添加模态框
    showQuickAddModal() {
        const modal = document.getElementById('quickAddModal');
        if (modal) {
            modal.classList.remove('hidden');
            this.bindQuickAddForm();
            
            // 聚焦到任务名称输入框
            setTimeout(() => {
                const taskNameInput = document.getElementById('taskName');
                if (taskNameInput) {
                    taskNameInput.focus();
                }
            }, 100);
        }
    }

    // 隐藏快速添加模态框
    hideQuickAddModal() {
        const modal = document.getElementById('quickAddModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // 绑定快速添加表单
    bindQuickAddForm() {
        const form = document.getElementById('quickAddForm');
        const closeBtn = document.getElementById('closeQuickAdd');
        const cancelBtn = document.getElementById('cancelQuickAdd');
        const overlay = document.querySelector('.modal-overlay');

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleQuickAddSubmit(form);
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.hideQuickAddModal();
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.hideQuickAddModal();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                this.hideQuickAddModal();
            });
        }
    }

    // 处理快速添加表单提交
    async handleQuickAddSubmit(form) {
        try {
            const formData = new FormData(form);
            const taskData = {
                name: formData.get('name'),
                priority: parseInt(formData.get('priority')),
                mode: parseInt(formData.get('mode')),
                deadline: formData.get('deadline') || null,
                content: formData.get('content') || ''
            };

            // 验证必填字段
            if (!taskData.name || !taskData.name.trim()) {
                alert('请输入任务名称');
                return;
            }

            // 调用API创建任务
            const result = await api.createTask(taskData);
            
            if (result) {
                // 发送成功通知
                await window.electronAPI.sendNotification({
                    title: '任务创建成功',
                    body: `任务 "${taskData.name}" 已添加`
                });

                // 关闭模态框
                this.hideQuickAddModal();
                
                // 重置表单
                form.reset();
                
                // 刷新相关视图
                if (this.currentView === 'tasks' && window.taskManager) {
                    window.taskManager.refresh();
                } else if (this.currentView === 'dashboard' && window.dashboardManager) {
                    window.dashboardManager.refresh();
                }
            }
        } catch (error) {
            console.error('Failed to create task:', error);
            alert('创建任务失败，请稍后重试');
        }
    }

    // 显示搜索模态框
    showSearchModal() {
        // 可以实现全局搜索功能
        alert('搜索功能将在后续版本中实现');
    }

    // 显示通知面板
    showNotificationsPanel() {
        // 可以实现通知面板
        alert('通知功能将在后续版本中实现');
    }

    // 显示设置模态框
    showSettingsModal() {
        // 可以实现设置功能
        alert('设置功能将在后续版本中实现');
    }

    // 获取当前视图
    getCurrentView() {
        return this.currentView;
    }
}

// 创建全局导航管理器实例
const navigationManager = new NavigationManager();
window.NavigationManager = NavigationManager;
window.navigationManager = navigationManager;
