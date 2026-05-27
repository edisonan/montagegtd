// 任务管理模块
class TaskManager {
    constructor() {
        this.tasks = [];
        this.filteredTasks = [];
        this.currentFilter = 'all';
        this.currentPage = 1;
        this.pageSize = 20;
        this.initialize();
    }

    initialize() {
        this.bindFilterEvents();
        this.bindPaginationEvents();
        this.loadTasks();
    }

    // 绑定过滤器事件
    bindFilterEvents() {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const priorityFilter = document.getElementById('priorityFilter');
        const sortByDate = document.getElementById('sortByDate');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const filter = tab.getAttribute('data-filter');
                this.setFilter(filter);
                
                // 更新active状态
                filterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        if (priorityFilter) {
            priorityFilter.addEventListener('change', () => {
                this.applyFilters();
            });
        }

        if (sortByDate) {
            sortByDate.addEventListener('click', () => {
                this.sortByDate();
            });
        }
    }

    // 绑定分页事件
    bindPaginationEvents() {
        const prevPage = document.getElementById('prevPage');
        const nextPage = document.getElementById('nextPage');

        if (prevPage) {
            prevPage.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.renderTasks();
                }
            });
        }

        if (nextPage) {
            nextPage.addEventListener('click', () => {
                const totalPages = Math.ceil(this.filteredTasks.length / this.pageSize);
                if (this.currentPage < totalPages) {
                    this.currentPage++;
                    this.renderTasks();
                }
            });
        }
    }

    // 加载任务列表
    async loadTasks() {
        try {
            const response = await api.getAllTasks(1, 1); // 获取进行中的GTD任务
            this.tasks = response || [];
            this.applyFilters();
            this.updateTaskBadge();
        } catch (error) {
            console.error('Failed to load tasks:', error);
        }
    }

    // 设置过滤器
    setFilter(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        this.applyFilters();
    }

    // 应用过滤器
    applyFilters() {
        let filtered = [...this.tasks];

        // 应用状态过滤
        switch (this.currentFilter) {
            case 'active':
                filtered = filtered.filter(task => task.status === 1);
                break;
            case 'completed':
                filtered = filtered.filter(task => task.status === 2);
                break;
            case 'folded':
                filtered = filtered.filter(task => task.status === 3);
                break;
        }

        // 应用优先级过滤
        const priorityFilter = document.getElementById('priorityFilter');
        if (priorityFilter && priorityFilter.value) {
            const priority = parseInt(priorityFilter.value);
            filtered = filtered.filter(task => task.priority === priority);
        }

        this.filteredTasks = filtered;
        this.renderTasks();
    }

    // 按日期排序
    sortByDate() {
        this.filteredTasks.sort((a, b) => {
            const dateA = a.deadline ? new Date(a.deadline) : new Date(9999, 11, 31);
            const dateB = b.deadline ? new Date(b.deadline) : new Date(9999, 11, 31);
            return dateA - dateB;
        });
        this.renderTasks();
    }

    // 渲染任务列表
    renderTasks() {
        const taskList = document.getElementById('taskList');
        if (!taskList) return;

        const startIndex = (this.currentPage - 1) * this.pageSize;
        const endIndex = startIndex + this.pageSize;
        const pageTasks = this.filteredTasks.slice(startIndex, endIndex);

        if (pageTasks.length === 0) {
            taskList.innerHTML = `
                <div class="empty-state">
                    <i class="icon-tasks"></i>
                    <p>暂无任务</p>
                </div>
            `;
        } else {
            taskList.innerHTML = pageTasks.map(task => this.renderTaskItem(task)).join('');
            this.bindTaskItemEvents();
        }

        this.updatePagination();
    }

    // 渲染单个任务项
    renderTaskItem(task) {
        const priorityColors = {
            1: 'var(--danger-color)',
            2: 'var(--warning-color)',
            3: 'var(--info-color)',
            4: 'var(--gray-400)'
        };

        const priorityLabels = {
            1: '高优先级',
            2: '中优先级',
            3: '低优先级',
            4: '无优先级'
        };

        const statusClasses = {
            1: 'task-active',
            2: 'task-completed',
            3: 'task-folded'
        };

        const deadlineText = task.deadline ? this.formatDeadline(task.deadline) : '';
        const priorityColor = priorityColors[task.priority] || priorityColors[4];
        const priorityLabel = priorityLabels[task.priority] || priorityLabels[4];

        return `
            <div class="task-item ${statusClasses[task.status] || ''}" data-task-id="${task.id}">
                <div class="task-checkbox">
                    <input type="checkbox" ${task.status === 2 ? 'checked' : ''} 
                           onchange="taskManager.toggleTaskStatus(${task.id})">
                </div>
                <div class="task-content">
                    <div class="task-header">
                        <h4 class="task-title">${this.escapeHtml(task.name)}</h4>
                        <span class="task-priority" style="background-color: ${priorityColor}">
                            ${priorityLabel}
                        </span>
                    </div>
                    ${task.content ? `<p class="task-description">${this.escapeHtml(task.content)}</p>` : ''}
                    <div class="task-meta">
                        ${deadlineText ? `<span class="task-deadline"><i class="icon-clock"></i>${deadlineText}</span>` : ''}
                        <span class="task-created">创建于 ${this.formatDate(task.created_at)}</span>
                    </div>
                </div>
                <div class="task-actions">
                    <button class="btn-icon" onclick="taskManager.editTask(${task.id})" title="编辑">
                        <i class="icon-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="taskManager.deleteTask(${task.id})" title="删除">
                        <i class="icon-delete"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // 绑定任务项事件
    bindTaskItemEvents() {
        // 事件已经在HTML中通过onclick绑定
    }

    // 切换任务状态
    async toggleTaskStatus(taskId) {
        try {
            const task = this.tasks.find(t => t.id === taskId);
            if (!task) return;

            const newStatus = task.status === 2 ? 1 : 2; // 在已完成和进行中之间切换
            
            await api.updateTask(taskId, { status: newStatus });
            
            // 更新本地数据
            task.status = newStatus;
            this.applyFilters();
            this.updateTaskBadge();

            // 发送通知
            const statusText = newStatus === 2 ? '已完成' : '进行中';
            await window.electronAPI.sendNotification({
                title: '任务状态更新',
                body: `任务 "${task.name}" 已标记为${statusText}`
            });
        } catch (error) {
            console.error('Failed to update task status:', error);
            alert('更新任务状态失败');
        }
    }

    // 编辑任务
    editTask(taskId) {
        // 可以实现编辑模态框
        alert('编辑功能将在后续版本中实现');
    }

    // 删除任务
    async deleteTask(taskId) {
        if (!confirm('确定要删除这个任务吗？')) {
            return;
        }

        try {
            await api.deleteTask(taskId);
            
            // 从本地数据中移除
            this.tasks = this.tasks.filter(t => t.id !== taskId);
            this.applyFilters();
            this.updateTaskBadge();

            await window.electronAPI.sendNotification({
                title: '任务已删除',
                body: '任务已成功删除'
            });
        } catch (error) {
            console.error('Failed to delete task:', error);
            alert('删除任务失败');
        }
    }

    // 更新任务徽章
    updateTaskBadge() {
        const activeTasks = this.tasks.filter(t => t.status === 1).length;
        const badge = document.getElementById('taskBadge');
        if (badge) {
            badge.textContent = activeTasks;
            badge.style.display = activeTasks > 0 ? 'block' : 'none';
        }
    }

    // 更新分页信息
    updatePagination() {
        const currentPageEl = document.getElementById('currentPage');
        const prevPage = document.getElementById('prevPage');
        const nextPage = document.getElementById('nextPage');

        if (currentPageEl) {
            currentPageEl.textContent = this.currentPage;
        }

        if (prevPage) {
            prevPage.disabled = this.currentPage <= 1;
        }

        if (nextPage) {
            const totalPages = Math.ceil(this.filteredTasks.length / this.pageSize);
            nextPage.disabled = this.currentPage >= totalPages;
        }
    }

    // 刷新任务列表
    refresh() {
        this.loadTasks();
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

// 创建全局任务管理器实例
const taskManager = new TaskManager();
window.TaskManager = TaskManager;
window.taskManager = taskManager;
