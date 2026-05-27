// API服务类 - 与后端接口交互
class ApiService {
    constructor() {
        this.baseURL = 'https://pretask.congcong.us/api/v2';
        this.token = null;
        this.initialize();
    }

    async initialize() {
        // 从electron存储中获取token
        this.token = await window.electronAPI.getUserToken();
    }

    // 设置认证token
    setToken(token) {
        this.token = token;
        window.electronAPI.setUserToken(token);
    }

    // 清除token
    clearToken() {
        this.token = null;
        window.electronAPI.setUserToken(null);
    }

    // 发送HTTP请求
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...(this.token && { 'Authorization': `Bearer ${this.token}` })
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // 检查业务状态码
            if (data.code !== 9999) {
                throw new Error(data.msg || 'Request failed');
            }
            
            return data.result;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    // GET请求
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    // POST请求
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    // PUT请求
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    // DELETE请求
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }

    // 用户认证相关API
    async login(email, password) {
        return this.post('/auth/login', { email, password });
    }

    async register(name, email, password) {
        return this.post('/auth/register', { name, email, password });
    }

    async logout() {
        return this.post('/auth/logout');
    }

    async getCurrentUser() {
        return this.get('/auth/me');
    }

    // 任务相关API
    async getTasks(params = {}) {
        return this.get('/tasks', params);
    }

    async getAllTasks(status = 1, mode = 1) {
        return this.get('/tasks/all', { status, mode });
    }

    async getTaskCounts() {
        return this.get('/tasks/tab-counts');
    }

    async getPriorityTasks() {
        return this.get('/tasks/priority');
    }

    async createTask(taskData) {
        return this.post('/tasks', taskData);
    }

    async updateTask(taskId, taskData) {
        return this.put(`/tasks/${taskId}`, taskData);
    }

    async deleteTask(taskId) {
        return this.delete(`/tasks/${taskId}`);
    }

    async getParentTasks(excludeTaskId = null) {
        const params = excludeTaskId ? { exclude_task_id: excludeTaskId } : {};
        return this.get('/tasks/parent-tasks', params);
    }

    // 番茄钟相关API
    async getFocuss(params = {}) {
        return this.get('/focuss', params);
    }

    async getTodayFocuss() {
        return this.get('/focuss/today');
    }

    async getFocusCounts() {
        return this.get('/focuss/tab-counts');
    }

    async startFocus(focusData) {
        return this.post('/focuss/start', focusData);
    }

    async updateFocus(focusId, focusData) {
        return this.put(`/focuss/${focusId}`, focusData);
    }

    async deleteFocus(focusId) {
        return this.delete(`/focuss/${focusId}`);
    }

    // 仪表盘数据
    async getDashboardData() {
        try {
            const [taskCounts, focusCounts, tasks] = await Promise.all([
                this.getTaskCounts(),
                this.getFocusCounts(),
                this.getAllTasks(1, 1) // 获取进行中的GTD任务
            ]);

            return {
                taskCounts,
                focusCounts,
                recentTasks: tasks.slice(0, 10) // 最近10个任务
            };
        } catch (error) {
            console.error('Failed to fetch dashboard data:', error);
            throw error;
        }
    }

    // 统计数据
    async getStatistics() {
        return this.get('/statistics');
    }

    // 笔记相关API
    async getNotes(params = {}) {
        return this.get('/notes', params);
    }

    async createNote(noteData) {
        return this.post('/notes', noteData);
    }

    // 日历相关API
    async getCalendarData() {
        return this.get('/calendar');
    }
}

// 创建全局API实例
const api = new ApiService();

// 导出API服务
window.ApiService = ApiService;
window.api = api;
