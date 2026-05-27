// 仪表盘管理模块
class DashboardManager {
    constructor() {
        this.initialize();
    }

    initialize() {
        this.loadData();
    }

    // 加载仪表盘数据
    async loadData() {
        try {
            await Promise.all([
                this.loadOverviewStats(),
                this.loadQuadrantData(),
                this.loadRecentPomodoros(),
                this.loadUpcomingTasks()
            ]);
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    // 加载概览统计
    async loadOverviewStats() {
        try {
            const [taskCounts, focusCounts] = await Promise.all([
                api.getTaskCounts(),
                api.getFocusCounts()
            ]);

            const todayTasks = document.getElementById('todayTasks');
            const completedTasks = document.getElementById('completedTasks');
            const focusTime = document.getElementById('focusTime');

            if (todayTasks && taskCounts) {
                todayTasks.textContent = taskCounts.active || 0;
            }

            if (completedTasks && taskCounts) {
                completedTasks.textContent = taskCounts.completed || 0;
            }

            if (focusTime && focusCounts) {
                // 这里可以根据实际API返回的数据计算专注时间
                focusTime.textContent = focusCounts.today_count || 0;
            }
        } catch (error) {
            console.error('Failed to load overview stats:', error);
        }
    }

    // 加载四象限数据
    async loadQuadrantData() {
        try {
            const tasks = await api.getAllTasks(1, 1); // 获取进行中的GTD任务
            
            if (!tasks || tasks.length === 0) {
                this.renderEmptyQuadrants();
                return;
            }

            // 按优先级分组
            const quadrants = {
                q1: tasks.filter(t => t.priority === 1), // 重要且紧急
                q2: tasks.filter(t => t.priority === 2), // 重要不紧急
                q3: tasks.filter(t => t.priority === 3), // 紧急不重要
                q4: tasks.filter(t => t.priority === 4)  // 不重要不紧急
            };

            // 更新计数
            this.updateQuadrantCount('q1', quadrants.q1.length);
            this.updateQuadrantCount('q2', quadrants.q2.length);
            this.updateQuadrantCount('q3', quadrants.q3.length);
            this.updateQuadrantCount('q4', quadrants.q4.length);

            // 渲染任务列表（每个象限显示前3个任务）
            this.renderQuadrantTasks('q1', quadrants.q1.slice(0, 3));
            this.renderQuadrantTasks('q2', quadrants.q2.slice(0, 3));
            this.renderQuadrantTasks('q3', quadrants.q3.slice(0, 3));
            this.renderQuadrantTasks('q4', quadrants.q4.slice(0, 3));
        } catch (error) {
            console.error('Failed to load quadrant data:', error);
            this.renderEmptyQuadrants();
        }
    }

    // 更新象限计数
    updateQuadrantCount(quadrantId, count) {
        const countEl = document.getElementById(`${quadrantId}Count`);
        if (countEl) {
            countEl.textContent = count;
        }
    }

    // 渲染象限任务
    renderQuadrantTasks(quadrantId, tasks) {
        const container = document.getElementById(`${quadrantId}Tasks`);
        if (!container) return;

        if (tasks.length === 0) {
            container.innerHTML = '<div class="empty-text">暂无任务</div>';
            return;
        }

        container.innerHTML = tasks.map(task => `
            <div class="quadrant-task-item" onclick="dashboardManager.viewTask(${task.id})">
                <span class="task-name">${this.escapeHtml(task.name)}</span>
                ${task.deadline ? `<span class="task-deadline">${this.formatDeadline(task.deadline)}</span>` : ''}
            </div>
        `).join('');
    }

    // 渲染空象限
    renderEmptyQuadrants() {
        ['q1', 'q2', 'q3', 'q4'].forEach(q => {
            this.updateQuadrantCount(q, 0);
            this.renderQuadrantTasks(q, []);
        });
    }

    // 加载最近的番茄钟
    async loadRecentPomodoros() {
        try {
            const focuss = await api.getFocuss({ page_count: 5 });
            const container = document.getElementById('recentPomodorosList');
            
            if (!container) return;

            if (!focuss || focuss.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="icon-empty"></i>
                        <p>暂无番茄钟记录</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = focuss.map(focus => `
                <div class="pomodoro-item">
                    <div class="pomodoro-info">
                        <h4>${this.escapeHtml(focus.name || '番茄钟')}</h4>
                        <p class="pomodoro-meta">
                            <span>${focus.duration || 25} 分钟</span>
                            <span>•</span>
                            <span>${this.formatDate(focus.created_at)}</span>
                        </p>
                    </div>
                    <div class="pomodoro-status ${focus.status === 3 ? 'completed' : ''}">
                        ${focus.status === 3 ? '✓' : '○'}
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Failed to load recent pomodoros:', error);
        }
    }

    // 加载即将到期的任务
    async loadUpcomingTasks() {
        try {
            const tasks = await api.getAllTasks(1, 1);
            const container = document.getElementById('upcomingTasksList');
            
            if (!container) return;

            // 筛选有截止日期的任务并按日期排序
            const upcomingTasks = tasks
                .filter(task => task.deadline)
                .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
                .slice(0, 5);

            if (upcomingTasks.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="icon-empty"></i>
                        <p>暂无即将到期的任务</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = upcomingTasks.map(task => `
                <div class="upcoming-task-item" onclick="dashboardManager.viewTask(${task.id})">
                    <div class="task-info">
                        <h4>${this.escapeHtml(task.name)}</h4>
                        <p class="task-deadline">${this.formatDeadline(task.deadline)}</p>
                    </div>
                    <div class="task-priority-badge priority-${task.priority}"></div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Failed to load upcoming tasks:', error);
        }
    }

    // 查看任务详情
    viewTask(taskId) {
        // 切换到任务视图并选中该任务
        if (window.navigationManager) {
            window.navigationManager.switchToView('tasks');
        }
        
        // 可以在这里实现任务高亮或滚动到该任务
        setTimeout(() => {
            const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
            if (taskElement) {
                taskElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                taskElement.classList.add('highlighted');
                setTimeout(() => {
                    taskElement.classList.remove('highlighted');
                }, 2000);
            }
        }, 100);
    }

    // 刷新仪表盘
    refresh() {
        this.loadData();
    }

    // 工具方法：格式化截止日期
    formatDeadline(deadline) {
        const now = new Date();
        const deadlineDate = new Date(deadline);
        const diffTime = deadlineDate - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays < 0) {
            return `已过期 ${Math.abs(diffDays)} 天`;
        } else if (diffDays === 0) {
            return '今天到期';
        } else if (diffDays === 1) {
            return '明天到期';
        } else if (diffDays <= 7) {
            return `${diffDays} 天后到期`;
        } else {
            return this.formatDate(deadline);
        }
    }

    // 工具方法：格式化日期
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('zh-CN');
    }

    // 工具方法：转义HTML
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// 创建全局仪表盘管理器实例
const dashboardManager = new DashboardManager();
window.DashboardManager = DashboardManager;
window.dashboardManager = dashboardManager;
