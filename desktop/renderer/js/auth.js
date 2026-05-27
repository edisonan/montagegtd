// 认证管理模块
class AuthManager {
    constructor() {
        this.isAuthenticated = false;
        this.currentUser = null;
        this.initialize();
    }

    async initialize() {
        // 检查是否有保存的token
        const token = await window.electronAPI.getUserToken();
        if (token) {
            api.setToken(token);
            await this.verifyToken();
        } else {
            this.showLoginView();
        }
    }

    // 验证token有效性
    async verifyToken() {
        try {
            this.currentUser = await api.getCurrentUser();
            this.isAuthenticated = true;
            this.showMainView();
            this.updateUserInfo();
        } catch (error) {
            console.error('Token verification failed:', error);
            this.logout();
        }
    }

    // 登录处理
    async login(email, password) {
        try {
            this.showLoading(true);
            
            const result = await api.login(email, password);
            
            if (result && result.token) {
                api.setToken(result.token);
                this.isAuthenticated = true;
                
                // 获取用户信息
                this.currentUser = await api.getCurrentUser();
                
                this.showMainView();
                this.updateUserInfo();
                
                // 发送登录成功通知
                await window.electronAPI.sendNotification({
                    title: '登录成功',
                    body: `欢迎回来，${this.currentUser.name || this.currentUser.email}`
                });
                
                return true;
            }
        } catch (error) {
            console.error('Login failed:', error);
            this.showError('登录失败，请检查邮箱和密码');
            return false;
        } finally {
            this.showLoading(false);
        }
    }

    // 注册处理
    async register(name, email, password) {
        try {
            this.showLoading(true);
            
            const result = await api.register(name, email, password);
            
            if (result && result.token) {
                api.setToken(result.token);
                this.isAuthenticated = true;
                
                this.currentUser = await api.getCurrentUser();
                
                this.showMainView();
                this.updateUserInfo();
                
                await window.electronAPI.sendNotification({
                    title: '注册成功',
                    body: `欢迎加入Montage GTD，${name}`
                });
                
                return true;
            }
        } catch (error) {
            console.error('Registration failed:', error);
            this.showError('注册失败，请稍后重试');
            return false;
        } finally {
            this.showLoading(false);
        }
    }

    // 登出处理
    async logout() {
        try {
            if (this.isAuthenticated) {
                await api.logout();
            }
        } catch (error) {
            console.error('Logout API call failed:', error);
        } finally {
            // 清除本地状态
            api.clearToken();
            this.isAuthenticated = false;
            this.currentUser = null;
            this.showLoginView();
        }
    }

    // 显示登录视图
    showLoginView() {
        document.getElementById('loginView').classList.remove('hidden');
        document.getElementById('mainView').classList.add('hidden');
        this.bindLoginForm();
    }

    // 显示主视图
    showMainView() {
        document.getElementById('loginView').classList.add('hidden');
        document.getElementById('mainView').classList.remove('hidden');
    }

    // 绑定登录表单
    bindLoginForm() {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (email && password) {
                    await this.login(email, password);
                }
            });
        }

        // 绑定其他链接事件
        const forgotPassword = document.getElementById('forgotPassword');
        const register = document.getElementById('register');
        
        if (forgotPassword) {
            forgotPassword.addEventListener('click', (e) => {
                e.preventDefault();
                this.showForgotPasswordModal();
            });
        }
        
        if (register) {
            register.addEventListener('click', (e) => {
                e.preventDefault();
                this.showRegisterModal();
            });
        }
    }

    // 更新用户信息显示
    updateUserInfo() {
        if (this.currentUser) {
            const userNameElement = document.getElementById('userName');
            if (userNameElement) {
                userNameElement.textContent = this.currentUser.name || this.currentUser.email;
            }
        }
    }

    // 显示加载状态
    showLoading(show) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            if (show) {
                loadingIndicator.classList.remove('hidden');
            } else {
                loadingIndicator.classList.add('hidden');
            }
        }
    }

    // 显示错误信息
    showError(message) {
        // 可以使用更友好的方式显示错误，比如toast通知
        alert(message);
    }

    // 显示忘记密码模态框
    showForgotPasswordModal() {
        // 这里可以实现忘记密码的功能
        alert('忘记密码功能将在后续版本中实现');
    }

    // 显示注册模态框
    showRegisterModal() {
        // 这里可以实现注册功能
        alert('注册功能将在后续版本中实现');
    }

    // 检查是否已认证
    checkAuth() {
        return this.isAuthenticated;
    }

    // 获取当前用户
    getCurrentUser() {
        return this.currentUser;
    }
}

// 创建全局认证管理器实例
const authManager = new AuthManager();
window.AuthManager = AuthManager;
window.authManager = authManager;
