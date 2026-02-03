@extends('layouts.app')

@section('title', 'AI助手 - 蒙太奇')
@section('description', '与智能助手对话，获取帮助和建议')

@section('content')
    <div class="h-screen flex bg-gray-50">
        <!-- 左侧会话列表 -->
        <div class="w-80 border-r border-gray-200 bg-white flex flex-col">
            <!-- 会话列表头部 -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">会话列表</h2>
                </div>

                <!-- 搜索会话 -->
                <div class="relative mb-3">
                    <input
                            type="text"
                            id="search-sessions"
                            class="input w-full pl-9 text-sm"
                            placeholder="搜索会话..."
                    >
                    <div class="absolute left-3 top-3 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- 会话列表内容 -->
            <div class="flex-1 overflow-hidden">
                <div id="sessions-list" class="p-2 overflow-y-auto h-full">
                    <div class="flex items-center justify-center h-40">
                        <div class="text-center">
                            <div class="w-10 h-10 mx-auto mb-2">
                                <i class="fas fa-spinner fa-spin text-primary-color text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">加载中...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 会话统计 -->
            <div class="p-3 border-t border-gray-200 bg-gray-50">
                <div class="text-xs text-gray-600">
                    <div class="flex justify-between items-center">
                        <span>会话总数：</span>
                        <span id="session-count" class="font-medium">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧聊天区域 -->
        <div class="flex-1 flex flex-col">
            <!-- ==================== 状态1：新建对话界面 ==================== -->
            <div id="initial-mode" class="flex-1 flex flex-col">
                <!-- Header: 新建对话 -->
                <div class="border-b border-gray-200 bg-white px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-comments text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg">新建对话</h3>
                            <div class="text-sm text-gray-500">开始一段新的AI对话</div>
                        </div>
                    </div>
                </div>

                <!-- 消息引导区域（固定高度） -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full flex flex-col items-center justify-center p-6">
                        <div class="max-w-2xl w-full text-center">
                            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-blue-100 to-purple-100 rounded-3xl flex items-center justify-center">
                                <i class="fas fa-robot text-primary-color text-4xl"></i>
                            </div>

                            <h2 class="text-2xl font-bold text-gray-900 mb-3">欢迎使用AI助手</h2>
                            <p class="text-gray-600 mb-8">选择智能体后开始您的对话</p>

                            <!-- 快捷问题示例 -->
                            <div class="mb-8">
                                <h4 class="text-sm font-medium text-gray-700 mb-4">尝试询问：</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-xl mx-auto">
                                    <button onclick="setQuickQuestion('如何提高工作效率？')" class="p-4 bg-white border border-gray-200 rounded-xl text-left hover:border-primary-color hover:shadow-sm transition-all duration-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-briefcase text-blue-600"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">提高工作效率</div>
                                                <div class="text-xs text-gray-500 mt-1">获取高效工作方法</div>
                                            </div>
                                        </div>
                                    </button>
                                    <button onclick="setQuickQuestion('如何制定学习计划？')" class="p-4 bg-white border border-gray-200 rounded-xl text-left hover:border-primary-color hover:shadow-sm transition-all duration-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-graduation-cap text-green-600"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">制定学习计划</div>
                                                <div class="text-xs text-gray-500 mt-1">定制个性化学习方案</div>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== 状态2：聊天界面 ==================== -->
            <div id="chat-mode" class="llm-hidden flex-1 flex flex-col">
                <!-- Header: 会话信息 -->
                <div class="border-b border-gray-200 bg-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-comments text-blue-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 id="session-title-display" class="font-semibold text-gray-900 text-lg">会话标题</h3>
                                <div class="flex items-center space-x-3 text-sm text-gray-500">
                                    <span id="session-agent">智能体：加载中...</span>
                                    <span id="session-time"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 会话操作按钮 -->
                        <div class="flex items-center space-x-2">
                            <div class="dropdown relative">
                                <button class="btn btn-sm btn-outline">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu llm-hidden" style="right: 0; left: auto;">
                                    <button id="pin-session-btn" class="dropdown-item">
                                        <i class="fas fa-thumbtack mr-2"></i>
                                        <span>固定会话</span>
                                    </button>
                                    <button id="rename-session-btn" class="dropdown-item">
                                        <i class="fas fa-edit mr-2"></i>
                                        <span>重命名</span>
                                    </button>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <button id="export-session-btn" class="dropdown-item">
                                        <i class="fas fa-download mr-2"></i>
                                        <span>导出会话</span>
                                    </button>
                                    <button id="clear-session-btn" class="dropdown-item">
                                        <i class="fas fa-trash mr-2"></i>
                                        <span>清空对话</span>
                                    </button>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <button id="delete-session-btn" class="dropdown-item text-red-600">
                                        <i class="fas fa-trash-alt mr-2"></i>
                                        <span>删除会话</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 消息气泡区域（固定高度） -->
                <div class="flex-1 overflow-hidden bg-gray-50">
                    <div id="messages-list" class="h-full overflow-y-auto p-6 space-y-6 custom-scrollbar">
                        <!-- 消息会动态插入到这里 -->
                    </div>
                </div>
            </div>

            <!-- ==================== 底部输入区域 ==================== -->
            <div class="border-t border-gray-200 bg-white">
                <div class="max-w-3xl mx-auto p-4">
                    <!-- 状态1：智能体选择 + 输入框 -->
                    <div id="initial-input-area">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                选择智能体
                            </label>
                            <select id="agent-select" class="input w-full">
                                <option value="">请选择智能体</option>
                                <option value="builtin_common">通用助手</option>
                                <!-- 智能体会动态加载 -->
                            </select>
                        </div>

                        <div class="relative bg-white rounded-lg border border-gray-300 focus-within:border-primary-color focus-within:ring-2 focus-within:ring-blue-100 transition-all duration-200">
                        <textarea
                                id="initial-message-input"
                                rows="3"
                                class="w-full p-4 text-gray-900 resize-none focus:outline-none bg-transparent"
                                placeholder="输入您的问题... (Enter 发送，Shift+Enter 换行)"
                        ></textarea>

                            <div class="flex items-center justify-between px-4 pb-3">
                                <div class="flex items-center space-x-3">
                                    <button id="initial-attachment-btn" class="text-gray-400 hover:text-gray-600" title="添加附件">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button id="initial-clear-btn" class="text-gray-400 hover:text-gray-600" title="清空输入">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <div id="initial-char-count" class="text-xs text-gray-400">0/2000</div>
                                    <button id="initial-send-btn" class="btn btn-primary flex items-center">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        发送
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 状态2：聊天输入框 -->
                    <div id="chat-input-area" class="llm-hidden">
                        <div class="relative bg-white rounded-lg border border-gray-300 focus-within:border-primary-color focus-within:ring-2 focus-within:ring-blue-100 transition-all duration-200">
                        <textarea
                                id="message-input"
                                rows="3"
                                class="w-full p-4 text-gray-900 resize-none focus:outline-none bg-transparent"
                                placeholder="输入消息... (Enter 发送，Shift+Enter 换行)"
                        ></textarea>

                            <div class="flex items-center justify-between px-4 pb-3">
                                <div class="flex items-center space-x-3">
                                    <button id="chat-attachment-btn" class="text-gray-400 hover:text-gray-600" title="添加附件">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button id="chat-clear-btn" class="text-gray-400 hover:text-gray-600" title="清空输入">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <div id="chat-char-count" class="text-xs text-gray-400">0/2000</div>
                                    <button id="chat-send-btn" class="btn btn-primary flex items-center">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        发送
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 输入提示 -->
                        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center space-x-4">
                            <span class="flex items-center">
                                <i class="fas fa-lightbulb mr-1"></i>
                                支持Markdown格式
                            </span>
                                <span class="flex items-center">
                                <i class="fas fa-keyboard mr-1"></i>
                                Enter 发送，Shift+Enter 换行
                            </span>
                            </div>
                            <button id="regenerate-btn" class="btn btn-outline btn-sm llm-hidden">
                                <i class="fas fa-redo mr-1"></i>
                                重新生成
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 模态框 -->
    <div class="modal" id="confirmModal">
        <div class="modal-content max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" id="confirm-title">确认操作</h3>
                <p class="text-gray-600 mb-6" id="confirm-message">确定要执行此操作吗？</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">取消</button>
                    <button type="button" id="confirm-action-btn" class="btn btn-danger">确认</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="renameModal">
        <div class="modal-content max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">重命名会话</h3>
                <div class="space-y-4">
                    <div>
                        <label for="new-session-name" class="block text-sm font-medium text-gray-700 mb-2">
                            新会话名称
                        </label>
                        <input
                                type="text"
                                id="new-session-name"
                                class="input w-full"
                                placeholder="请输入会话名称"
                        >
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRenameModal()" class="btn btn-secondary">取消</button>
                        <button type="button" id="save-rename-btn" class="btn btn-primary">保存</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 当前会话信息 -->
    <input type="hidden" id="current-agent-id" value="">
    <input type="hidden" id="current-session-id" value="">

    <script src="/js/marked.min.js"></script>

    <script>
        // 全局变量
        let currentSessionId = null;
        let currentSessionAgent = null;
        let allAgents = [];
        let isStreaming = false;
        let currentStreamController = null;

        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            console.log('AI助手初始化...');

            // 加载会话和智能体
            loadSessions();
            loadAllAgents();

            // 初始化事件监听器
            initEventListeners();

            // 初始化字符计数器
            initCharCounters();
        });

        // 初始化事件监听器
        function initEventListeners() {
            // 智能体选择
            document.getElementById('agent-select').addEventListener('change', function() {
                console.log('智能体选择变更:', this.value);
            });

            // ========== 初始状态（新建对话） ==========
            const initialInput = document.getElementById('initial-message-input');
            initialInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    startNewChat();
                }
            });
            initialInput.addEventListener('input', updateInitialCharCount);

            document.getElementById('initial-send-btn').addEventListener('click', startNewChat);
            document.getElementById('initial-clear-btn').addEventListener('click', function() {
                document.getElementById('initial-message-input').value = '';
                updateInitialCharCount();
                document.getElementById('initial-message-input').focus();
            });
            document.getElementById('initial-attachment-btn').addEventListener('click', showAttachmentOptions);

            // ========== 聊天状态 ==========
            const chatInput = document.getElementById('message-input');
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            chatInput.addEventListener('input', updateChatCharCount);

            document.getElementById('chat-send-btn').addEventListener('click', sendMessage);
            document.getElementById('chat-clear-btn').addEventListener('click', function() {
                document.getElementById('message-input').value = '';
                updateChatCharCount();
                document.getElementById('message-input').focus();
            });
            document.getElementById('chat-attachment-btn').addEventListener('click', showAttachmentOptions);
            document.getElementById('regenerate-btn').addEventListener('click', regenerateLastResponse);

            // 搜索会话
            document.getElementById('search-sessions').addEventListener('input', searchSessions);

            // 模态框按钮
            document.getElementById('save-rename-btn').addEventListener('click', saveRenameSession);
            document.getElementById('rename-session-btn')?.addEventListener('click', renameCurrentSession);
            document.getElementById('delete-session-btn')?.addEventListener('click', deleteCurrentSession);
            document.getElementById('pin-session-btn')?.addEventListener('click', togglePinCurrentSession);
            document.getElementById('clear-session-btn')?.addEventListener('click', clearCurrentSession);
            document.getElementById('export-session-btn')?.addEventListener('click', exportCurrentSession);

            // 模态框关闭事件
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                    }
                });
            });

            // 重命名输入框回车保存
            document.getElementById('new-session-name').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('save-rename-btn').click();
                }
            });
        }

        // 初始化字符计数器
        function initCharCounters() {
            updateInitialCharCount();
            updateChatCharCount();
        }

        // 更新初始输入字符计数
        function updateInitialCharCount() {
            const textarea = document.getElementById('initial-message-input');
            const counter = document.getElementById('initial-char-count');
            updateCharCounter(textarea, counter, 2000);
        }

        // 更新聊天输入字符计数
        function updateChatCharCount() {
            const textarea = document.getElementById('message-input');
            const counter = document.getElementById('chat-char-count');
            updateCharCounter(textarea, counter, 2000);
        }

        // 通用字符计数更新
        function updateCharCounter(textarea, counter, maxLength) {
            const length = textarea.value.length;
            counter.textContent = `${length}/${maxLength}`;

            if (length > maxLength * 0.9) {
                counter.classList.remove('text-gray-400', 'text-red-600');
                counter.classList.add('text-yellow-600');
            } else if (length > maxLength) {
                counter.classList.remove('text-gray-400', 'text-yellow-600');
                counter.classList.add('text-red-600');
            } else {
                counter.classList.remove('text-yellow-600', 'text-red-600');
                counter.classList.add('text-gray-400');
            }
        }

        // 加载会话列表
        async function loadSessions() {
            try {
                const response = await fetch('/llm/sessions', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    displaySessions(result.data);
                    updateSessionCount(result.data.length);
                } else {
                    showError('加载会话列表失败：' + result.message);
                }
            } catch (error) {
                console.error('加载会话列表失败:', error);
                showError('加载会话列表失败，请检查网络连接');
            }
        }

        // 显示会话列表
        function displaySessions(sessions) {
            const container = document.getElementById('sessions-list');

            if (!sessions || sessions.length === 0) {
                container.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-comment text-gray-400 text-xl"></i>
                </div>
                <p class="text-gray-500 text-sm">暂无会话记录</p>
                <p class="text-xs text-gray-400 mt-1">选择智能体开始新对话</p>
            </div>
        `;
                return;
            }

            let html = '';

            // 分组：固定的会话和普通会话
            const pinnedSessions = sessions.filter(s => s.is_pinned);
            const regularSessions = sessions.filter(s => !s.is_pinned);

            // 固定会话
            if (pinnedSessions.length > 0) {
                html += `
            <div class="px-2 py-2">
                <div class="flex items-center text-xs text-gray-500 mb-2">
                    <i class="fas fa-thumbtack mr-2 text-yellow-500"></i>
                    <span>固定会话</span>
                </div>
        `;

                pinnedSessions.forEach(session => {
                    html += createSessionItemHTML(session);
                });

                html += `</div>`;
            }

            // 普通会话
            if (regularSessions.length > 0) {
                if (pinnedSessions.length > 0) {
                    html += `<div class="border-t border-gray-100 my-2"></div>`;
                }

                html += `
            <div class="px-2 py-2">
                <div class="text-xs text-gray-500 mb-2">最近会话</div>
        `;

                regularSessions.forEach(session => {
                    html += createSessionItemHTML(session);
                });

                html += `</div>`;
            }

            container.innerHTML = html;

            // 添加点击事件
            document.querySelectorAll('.session-item').forEach(item => {
                item.addEventListener('click', function() {
                    const sessionId = this.dataset.sessionId;
                    switchToSession(sessionId);
                });
            });
        }

        // 创建会话项HTML
        function createSessionItemHTML(session) {
            const title = session.title || '未命名会话';
            const time = formatTime(session.updated_at || session.created_at);
            const isActive = currentSessionId === session.id;
            const agentName = session.agent_name || '通用';
            const agentColor = getAgentColor(session.agent_id);

            return `
        <div class="session-item p-3 mb-2 rounded-lg cursor-pointer transition-all duration-200 ${isActive ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50'}"
             data-session-id="${session.id}">
            <div class="flex justify-between items-start mb-1">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 text-sm truncate" title="${title}">${title}</div>
                    <div class="flex items-center mt-1">
                        <span class="text-xs px-2 py-0.5 ${agentColor} text-gray-700 rounded-full mr-2">
                            ${agentName}
                        </span>
                        <span class="text-xs text-gray-500">${time}</span>
                    </div>
                </div>
                <div class="flex items-center space-x-1 ml-2">
                    <button class="session-pin-btn p-1 text-gray-400 hover:text-yellow-500" onclick="event.stopPropagation(); togglePinSession('${session.id}', event)" title="${session.is_pinned ? '取消固定' : '固定会话'}">
                        <i class="fas fa-thumbtack ${session.is_pinned ? 'text-yellow-500' : ''} text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
        }

        // 获取智能体颜色
        function getAgentColor(agentId) {
            if (!agentId) return 'bg-gray-100';

            const colors = [
                'bg-blue-100', 'bg-green-100', 'bg-yellow-100',
                'bg-purple-100', 'bg-pink-100', 'bg-indigo-100'
            ];

            let hash = 0;
            for (let i = 0; i < agentId.length; i++) {
                hash = agentId.charCodeAt(i) + ((hash << 5) - hash);
            }

            const index = Math.abs(hash) % colors.length;
            return colors[index];
        }

        // 格式化时间
        function formatTime(timestamp) {
            if (!timestamp) return '';

            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffHours = diffMs / (1000 * 60 * 60);
            const diffDays = diffHours / 24;

            if (diffHours < 1) {
                return '刚刚';
            } else if (diffHours < 24) {
                return Math.floor(diffHours) + '小时前';
            } else if (diffDays < 7) {
                return Math.floor(diffDays) + '天前';
            } else {
                return date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' });
            }
        }

        // 更新会话计数
        function updateSessionCount(count) {
            document.getElementById('session-count').textContent = count;
        }

        // 加载所有智能体
        async function loadAllAgents() {
            try {
                const response = await fetch('/llm/agents', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    allAgents = result.data;
                    populateAgentsSelect(result.data);
                } else {
                    console.warn('加载智能体失败:', result.message);
                }
            } catch (error) {
                console.error('加载智能体列表失败:', error);
            }
        }

        // 填充智能体选择框
        function populateAgentsSelect(agents) {
            const select = document.getElementById('agent-select');
            let options = '<option value="">请选择智能体</option><option value="builtin_common">通用助手</option>';

            if (agents && agents.length > 0) {
                agents.forEach(agent => {
                    options += `<option value="${agent.id}">${agent.name}</option>`;
                });
            }

            select.innerHTML = options;
        }

        // 开始新聊天
        async function startNewChat() {
            const agentId = document.getElementById('agent-select').value;
            const message = document.getElementById('initial-message-input').value.trim();

            if (!agentId) {
                showToast('请选择智能体', 'error');
                return;
            }

            // 获取智能体名称
            const agentSelect = document.getElementById('agent-select');
            const agentName = agentSelect.options[agentSelect.selectedIndex].text;

            try {
                const response = await fetch('/llm/sessions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        agent_id: agentId,
                        title: message ? message.substring(0, 30) : `${agentName}对话`
                    })
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    currentSessionId = result.data.id;
                    currentSessionAgent = result.data.agent;

                    // 切换到聊天模式
                    switchToChatMode();

                    // 更新会话信息
                    updateSessionInfo(result.data);

                    // 设置当前智能体ID
                    document.getElementById('current-agent-id').value = agentId;
                    document.getElementById('current-session-id').value = result.data.id;

                    // 如果有消息，发送给AI
                    if (message) {
                        addMessage('user', message);
                        await sendMessageToAI(message, agentId);
                    } else {
                        // 显示欢迎消息
                        const welcomeMessage = `您好！我是${agentName}，有什么可以帮您的吗？`;
                        addMessage('ai', welcomeMessage);
                    }

                    // 清空初始输入
                    document.getElementById('initial-message-input').value = '';
                    updateInitialCharCount();
                    document.getElementById('agent-select').value = '';

                    // 重新加载会话列表
                    await loadSessions();

                    // 聚焦到聊天输入框
                    setTimeout(() => {
                        document.getElementById('message-input').focus();
                    }, 100);

                    showToast('新会话已创建', 'success');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('创建会话失败:', error);
                showToast('创建会话失败: ' + error.message, 'error');
            }
        }

        // 设置快捷问题
        function setQuickQuestion(question) {
            document.getElementById('initial-message-input').value = question;
            updateInitialCharCount();
            document.getElementById('initial-message-input').focus();
        }

        // 切换到聊天模式
        function switchToChatMode() {
            document.getElementById('initial-mode').classList.add('llm-hidden');
            document.getElementById('chat-mode').classList.remove('llm-hidden');
            document.getElementById('initial-input-area').classList.add('llm-hidden');
            document.getElementById('chat-input-area').classList.remove('llm-hidden');
        }

        // 切换到初始模式
        function switchToInitialMode() {
            currentSessionId = null;
            currentSessionAgent = null;

            document.getElementById('initial-mode').classList.remove('llm-hidden');
            document.getElementById('chat-mode').classList.add('llm-hidden');
            document.getElementById('initial-input-area').classList.remove('llm-hidden');
            document.getElementById('chat-input-area').classList.add('llm-hidden');

            document.getElementById('initial-message-input').value = '';
            updateInitialCharCount();
            document.getElementById('agent-select').value = '';

            document.getElementById('current-agent-id').value = '';
            document.getElementById('current-session-id').value = '';
        }

        // 更新会话信息
        function updateSessionInfo(sessionData) {
            document.getElementById('session-title-display').textContent = sessionData.title || '未命名会话';

            let agentName = '通用助手';
            let agentId = '';

            if (sessionData.agent && sessionData.agent.name) {
                agentName = sessionData.agent.name;
                agentId = sessionData.agent.id;
                document.getElementById('current-agent-id').value = agentId;
            } else if (sessionData.agent_name) {
                agentName = sessionData.agent_name;
            }

            document.getElementById('session-agent').textContent = `智能体：${agentName}`;

            if (sessionData.updated_at) {
                document.getElementById('session-time').textContent = formatTime(sessionData.updated_at);
            }

            // 更新当前会话ID
            document.getElementById('current-session-id').value = sessionData.id;
        }

        // 切换到指定会话
        async function switchToSession(sessionId) {
            try {
                const response = await fetch(`/llm/sessions/${sessionId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    currentSessionId = sessionId;
                    currentSessionAgent = result.data.agent;

                    // 切换到聊天模式
                    switchToChatMode();

                    // 更新会话信息
                    updateSessionInfo(result.data);

                    // 更新当前智能体ID
                    if (currentSessionAgent && currentSessionAgent.id) {
                        document.getElementById('current-agent-id').value = currentSessionAgent.id;
                    }

                    // 清空消息列表并加载历史消息
                    document.getElementById('messages-list').innerHTML = '';
                    if (result.data.messages && result.data.messages.length > 0) {
                        result.data.messages.forEach(msg => {
                            addMessage(msg.role === 'user' ? 'user' : 'ai', msg.content);
                        });
                    } else {
                        const agentName = result.data.agent?.name || '智能助手';
                        addMessage('ai', `您好！我是${agentName}，有什么可以帮您的吗？`);
                    }

                    // 聚焦到聊天输入框
                    setTimeout(() => {
                        document.getElementById('message-input').focus();
                    }, 100);
                }
            } catch (error) {
                console.error('切换会话失败:', error);
                showToast('切换会话失败: ' + error.message, 'error');
            }
        }

        // ========== 修复：添加消息到聊天 ==========
        function addMessage(role, content) {
            const messagesList = document.getElementById('messages-list');
            const messageId = 'msg-' + Date.now();

            if (role === 'user') {
                // 用户消息 - 修复样式
                const userMessageHTML = `
            <div id="${messageId}" class="flex justify-end mb-4">
                <div class="max-w-xl">
                    <div class="bg-primary-color text-white rounded-2xl rounded-br-none px-5 py-3">
                        <div class="whitespace-pre-wrap">${escapeHtml(content)}</div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 text-right">
                        ${formatTime(new Date().toISOString())}
                    </div>
                </div>
            </div>
        `;
                messagesList.insertAdjacentHTML('beforeend', userMessageHTML);
            } else {
                // AI消息
                const aiMessageHTML = `
            <div id="${messageId}" class="flex mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-robot text-blue-600 text-sm"></i>
                </div>
                <div class="max-w-xl flex-1">
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-5 py-3">
                        <div class="markdown-content whitespace-pre-wrap">${marked.parse(content)}</div>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-xs text-gray-500">
                            ${formatTime(new Date().toISOString())}
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-gray-600 p-1" title="复制">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
                messagesList.insertAdjacentHTML('beforeend', aiMessageHTML);
            }

            // 滚动到底部
            setTimeout(() => {
                messagesList.scrollTop = messagesList.scrollHeight;
            }, 50);
        }

        // 转义HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 发送消息
        async function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();

            if (!message) {
                showToast('请输入消息内容', 'error');
                return;
            }

            if (!currentSessionId) {
                showToast('请先创建会话', 'error');
                return;
            }

            // 获取当前智能体ID
            const agentId = document.getElementById('current-agent-id').value;
            if (!agentId) {
                showToast('当前会话没有指定智能体', 'error');
                return;
            }

            // 清空输入框
            messageInput.value = '';
            updateChatCharCount();

            // 显示用户消息
            addMessage('user', message);

            // 发送到AI
            await sendMessageToAI(message, agentId);
        }

        // 发送消息到AI
        async function sendMessageToAI(message, agentId = null) {
            if (isStreaming && currentStreamController) {
                currentStreamController.abort();
                isStreaming = false;
            }

            try {
                // 显示AI思考指示器
                const thinkingId = showThinkingIndicator();

                // 获取最终智能体ID
                let finalAgentId = agentId;
                if (!finalAgentId) {
                    finalAgentId = document.getElementById('current-agent-id').value;
                }

                isStreaming = true;

                const response = await fetch('/llm/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        query: message,
                        session_id: currentSessionId,
                        agent_id: finalAgentId
                    })
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                // 移除思考指示器
                removeThinkingIndicator(thinkingId);

                // 创建消息元素
                const messageId = 'ai-msg-' + Date.now();
                const messagesList = document.getElementById('messages-list');
                messagesList.insertAdjacentHTML('beforeend', `
            <div id="${messageId}" class="flex mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-robot text-blue-600 text-sm"></i>
                </div>
                <div class="max-w-xl flex-1">
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-5 py-3">
                        <div id="${messageId}-content" class="markdown-content"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        正在思考...
                    </div>
                </div>
            </div>
        `);

                // 处理流式响应
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let accumulatedContent = '';

                while (isStreaming) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = line.slice(6).trim();

                            if (data === '[DONE]') {
                                isStreaming = false;
                                break;
                            }

                            try {
                                const parsed = JSON.parse(data);

                                // 根据您的返回格式解析内容
                                if (parsed.choices && parsed.choices[0]) {
                                    const choice = parsed.choices[0];

                                    // 修复：检查delta.content字段
                                    if (choice.delta && choice.delta.content) {
                                        accumulatedContent += choice.delta.content;

                                        // 更新消息内容
                                        const contentElement = document.getElementById(`${messageId}-content`);
                                        if (contentElement) {
                                            contentElement.innerHTML = marked.parse(accumulatedContent);
                                        }
                                    }
                                }
                            } catch (e) {
                                console.warn('解析流数据失败:', e, '数据:', data);
                            }
                        }
                    }

                    // 滚动到底部
                    messagesList.scrollTop = messagesList.scrollHeight;
                }

                // 更新时间和操作按钮
                const messageElement = document.getElementById(messageId);
                const footer = messageElement.querySelector('.text-xs');
                if (footer) {
                    footer.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${formatTime(new Date().toISOString())}</span>
                    <div class="flex items-center space-x-2">
                        <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-gray-600 p-1" title="复制">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            `;
                }

                reader.releaseLock();

                // 重新加载会话列表以更新最后消息时间
                loadSessions();

            } catch (error) {
                console.error('发送消息到AI失败:', error);

                // 移除思考指示器
                removeThinkingIndicator();

                // 显示错误消息
                if (error.name !== 'AbortError') {
                    addMessage('ai', '抱歉，请求失败。请稍后重试。');
                    showToast('网络请求失败: ' + error.message, 'error');
                }

                isStreaming = false;
            }
        }

        // 显示思考指示器
        function showThinkingIndicator() {
            const messagesList = document.getElementById('messages-list');
            const indicatorId = 'thinking-' + Date.now();

            messagesList.insertAdjacentHTML('beforeend', `
        <div id="${indicatorId}" class="flex mb-6">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                <i class="fas fa-robot text-blue-600 text-sm"></i>
            </div>
            <div class="max-w-xl flex-1">
                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-5 py-3">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    `);

            // 滚动到底部
            messagesList.scrollTop = messagesList.scrollHeight;

            return indicatorId;
        }

        // 移除思考指示器
        function removeThinkingIndicator(indicatorId = null) {
            if (indicatorId) {
                const element = document.getElementById(indicatorId);
                if (element) element.remove();
            } else {
                document.querySelectorAll('.typing-indicator').forEach(ind => {
                    ind.closest('.flex.mb-6')?.remove();
                });
            }
        }

        // 搜索会话
        function searchSessions() {
            const searchTerm = document.getElementById('search-sessions').value.toLowerCase();
            const sessionItems = document.querySelectorAll('.session-item');

            sessionItems.forEach(item => {
                const title = item.querySelector('.font-medium').textContent.toLowerCase();
                if (title.includes(searchTerm) || !searchTerm) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // 固定/取消固定会话
        async function togglePinSession(sessionId, event) {
            if (event) event.stopPropagation();

            try {
                const response = await fetch(`/llm/sessions/${sessionId}/toggle-pin`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    // 重新加载会话列表
                    loadSessions();

                    showToast(result.data.is_pinned ? '已固定会话' : '已取消固定', 'success');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('固定会话失败:', error);
                showToast('操作失败', 'error');
            }
        }

        // 固定当前会话
        async function togglePinCurrentSession() {
            if (!currentSessionId) return;
            await togglePinSession(currentSessionId, { stopPropagation: () => {} });
        }

        // 清空当前会话
        async function clearCurrentSession() {
            if (!currentSessionId) return;

            showConfirmModal('清空对话', '确定要清空当前会话的所有消息吗？此操作不可撤销。', async () => {
                try {
                    const response = await fetch(`/llm/sessions/${currentSessionId}/clear`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast('会话已清空', 'success');

                        // 清空消息列表，显示欢迎消息
                        document.getElementById('messages-list').innerHTML = '';
                        const agentName = document.getElementById('session-agent').textContent.replace('智能体：', '');
                        addMessage('ai', `您好！我是${agentName}，有什么可以帮您的吗？`);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('清空会话失败:', error);
                    showToast('清空失败: ' + error.message, 'error');
                }
            });
        }

        // 导出当前会话
        function exportCurrentSession() {
            showToast('导出功能开发中...', 'info');
        }

        // 删除会话
        async function deleteCurrentSession() {
            if (!currentSessionId) return;

            showConfirmModal('删除会话', '确定要删除这个会话吗？此操作不可撤销。', async () => {
                try {
                    const response = await fetch(`/llm/sessions/${currentSessionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast('会话已删除', 'success');

                        // 切换回初始模式
                        switchToInitialMode();

                        // 重新加载会话列表
                        loadSessions();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('删除会话失败:', error);
                    showToast('删除失败: ' + error.message, 'error');
                }
            });
        }

        // 重命名会话
        async function renameCurrentSession() {
            if (!currentSessionId) return;

            const currentTitle = document.getElementById('session-title-display').textContent;
            document.getElementById('new-session-name').value = currentTitle;

            showRenameModal();
        }

        // 保存重命名
        async function saveRenameSession() {
            const newName = document.getElementById('new-session-name').value.trim();

            if (!newName) {
                showToast('请输入会话名称', 'error');
                return;
            }

            if (!currentSessionId) return;

            try {
                const response = await fetch(`/llm/sessions/${currentSessionId}/rename`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ title: newName })
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    showToast('会话已重命名', 'success');
                    document.getElementById('session-title-display').textContent = newName;
                    closeRenameModal();
                    loadSessions();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('重命名会话失败:', error);
                showToast('重命名失败: ' + error.message, 'error');
            }
        }

        // 显示附件选项
        function showAttachmentOptions() {
            showToast('附件功能开发中...', 'info');
        }

        // 重新生成最后一条消息
        async function regenerateLastResponse() {
            // TODO: 实现重新生成逻辑
            showToast('重新生成功能开发中...', 'info');
        }

        // 复制消息
        function copyMessage(messageId) {
            const messageElement = document.getElementById(messageId);
            const content = messageElement.querySelector('.markdown-content')?.textContent ||
                messageElement.querySelector('.bg-primary-color')?.textContent;

            if (content) {
                navigator.clipboard.writeText(content).then(() => {
                    showToast('已复制到剪贴板', 'success');
                });
            }
        }

        // 显示确认模态框
        function showConfirmModal(title, message, confirmCallback) {
            document.getElementById('confirm-title').textContent = title;
            document.getElementById('confirm-message').textContent = message;

            const confirmBtn = document.getElementById('confirm-action-btn');
            confirmBtn.onclick = function() {
                closeModal();
                confirmCallback();
            };

            document.getElementById('confirmModal').classList.add('show');
        }

        // 关闭模态框
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('show');
        }

        // 显示重命名模态框
        function showRenameModal() {
            document.getElementById('renameModal').classList.add('show');
            document.getElementById('new-session-name').focus();
        }

        // 关闭重命名模态框
        function closeRenameModal() {
            document.getElementById('renameModal').classList.remove('show');
        }

        // 显示提示消息
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                        type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
            }`;
            toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' :
                type === 'error' ? 'exclamation-circle' :
                    type === 'info' ? 'info-circle' : 'bell'} mr-3"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
        }

        // 显示错误
        function showError(message) {
            showToast(message, 'error');
        }
    </script>

    <style>
        /* 高度和布局 */
        .h-screen {
            height: calc(100vh - 64px);
        }

        /* 会话列表样式 */
        #sessions-list {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .session-item {
            transition: all 0.2s ease;
        }

        .session-item:hover {
            transform: translateX(2px);
        }

        /* 消息气泡样式 */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #9ca3af;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes typing {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.5;
            }
            40% {
                transform: translateY(-4px);
                opacity: 1;
            }
        }

        /* 消息滚动条 */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* 消息动画 */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .flex.justify-end.mb-4,
        .flex.mb-6 {
            animation: fadeInUp 0.3s ease-out;
        }

        /* 代码块样式 */
        .markdown-content pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            margin: 0.5rem 0;
        }

        .markdown-content code {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        .markdown-content pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
        }

        /* 隐藏类 */
        .llm-hidden {
            display: none !important;
        }

        /* 模态框动画 */
        .modal.show .modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 输入区域样式 */
        #initial-message-input,
        #message-input {
            min-height: 60px;
            max-height: 200px;
            line-height: 1.5;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .h-screen {
                height: calc(100vh - 56px);
            }

            #messages-list {
                padding: 1rem !important;
            }

            .markdown-content pre {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .grid-cols-1.md\:grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection