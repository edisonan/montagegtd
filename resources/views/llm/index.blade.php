@extends('layouts.app')

@section('title', 'AI助手 - 蒙太奇')
@section('description', '与智能助手对话，获取帮助和建议')

@section('content')
    <div class="h-screen flex bg-white">
        <!-- 左侧会话列表 -->
        <div class="w-64 border-r border-gray-200 bg-white flex flex-col">
            <!-- 会话列表头部 -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">对话</h2>
                    <button onclick="switchToInitialMode()" class="btn btn-sm btn-primary flex items-center">
                        <i class="fas fa-plus mr-1 text-xs"></i>
                        新建
                    </button>
                </div>

                <!-- 搜索会话 -->
                <div class="relative">
                    <input
                            type="text"
                            id="search-sessions"
                            class="input input-sm w-full pl-8"
                            placeholder="搜索对话..."
                    >
                    <div class="absolute left-2.5 top-2.5 text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- 会话列表内容 -->
            <div class="flex-1 overflow-hidden">
                <div id="sessions-list" class="overflow-y-auto h-full">
                    <!-- 会话会动态加载到这里 -->
                    <div class="flex items-center justify-center h-40">
                        <div class="text-center">
                            <div class="w-8 h-8 mx-auto mb-2">
                                <i class="fas fa-spinner fa-spin text-primary-color"></i>
                            </div>
                            <p class="text-gray-500 text-sm">加载中...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 底部信息 -->
            <div class="p-3 border-t border-gray-200">
                <div class="text-xs text-gray-500 flex justify-between">
                    <span>对话总数</span>
                    <span id="session-count" class="font-medium">0</span>
                </div>
            </div>
        </div>

        <!-- 右侧主区域 -->
        <div class="flex-1 flex flex-col">
            <!-- ==================== 状态1：新建对话界面 ==================== -->
            <div id="initial-mode" class="flex-1 flex flex-col bg-gray-50">
                <!-- 顶部留空 -->
                <div class="h-4 bg-white"></div>

                <!-- 主内容区 -->
                <div class="flex-1 bg-white flex flex-col items-center justify-center">
                    <div class="max-w-2xl w-full text-center">
                        <!-- 欢迎语 -->
                        <div class="mb-10">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">欢迎使用AI助手</h1>
                        </div>

                    </div>
                </div>

                <!-- 底部输入区域 -->
                <div class="bg-white">
                    <div class="max-w-3xl mx-auto p-4">
                        <!-- 输入框容器 -->
                        <div class="relative bg-white rounded-xl border border-gray-300 focus-within:border-primary-color focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                        <textarea
                                id="initial-message-input"
                                rows="2"
                                class="w-full p-4 text-gray-900 resize-none focus:outline-none bg-transparent placeholder-gray-500 text-sm"
                                placeholder="输入您的问题..."
                        ></textarea>

                            <div class="flex items-center justify-between px-4 pb-3">
                                <!-- 左侧功能按钮 -->
                                <div class="flex items-center space-x-1">
                                    <button id="initial-attachment-btn" class="btn-icon text-gray-500 hover:text-gray-700" title="上传文件">
                                        <i class="fas fa-paperclip text-sm"></i>
                                    </button>
                                    <button id="initial-voice-btn" class="btn-icon text-gray-500 hover:text-gray-700" title="语音输入">
                                        <i class="fas fa-microphone text-sm"></i>
                                    </button>
                                    <button id="initial-clear-btn" class="btn-icon text-gray-500 hover:text-gray-700" title="清空">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>

                                <!-- 右侧发送按钮 -->
                                <div class="flex items-center space-x-3">
                                    <div id="initial-char-count" class="text-xs text-gray-400">0/2000</div>
                                    <button id="initial-send-btn" class="btn btn-primary btn-sm px-4">
                                        <i class="fas fa-paper-plane mr-1 text-xs"></i>
                                        发送
                                    </button>
                                </div>
                            </div>

                            <!-- 场景功能按钮 -->
                            <div class="px-4 pb-3 flex justify-center space-x-2">
                                <select id="agent-select" class="btn-scene">
                                    <!-- 智能体会动态加载 -->
                                </select>
                                <button onclick="setQuickQuestion('帮我生成一段代码')" class="btn-scene">
                                    <i class="fas fa-code mr-1 text-xs"></i> 代码生成
                                </button>
                                <button onclick="setQuickQuestion('帮我优化文案')" class="btn-scene">
                                    <i class="fas fa-edit mr-1 text-xs"></i> 文案优化
                                </button>
                                <button onclick="setQuickQuestion('给我一些创意灵感')" class="btn-scene">
                                    <i class="fas fa-lightbulb mr-1 text-xs"></i> 创意生成
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== 状态2：聊天界面 ==================== -->
            <div id="chat-mode" class="llm-hidden flex-1 flex flex-col">
                <!-- 会话标题栏 -->
                <div class="border-b border-gray-200 bg-white px-6 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 rounded-md bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-comment text-blue-500 text-xs"></i>
                            </div>
                            <div>
                                <h3 id="session-title-display" class="font-semibold text-gray-900 text-sm">会话标题</h3>
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <span id="session-agent">智能体：加载中...</span>
                                    <span>•</span>
                                    <span id="session-time"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 会话操作 -->
                        <div class="flex items-center space-x-1">
                            <button id="pin-session-btn" class="btn-icon-sm" title="固定会话">
                                <i class="fas fa-thumbtack text-xs"></i>
                            </button>
                            <div class="dropdown relative">
                                <button class="btn-icon-sm">
                                    <i class="fas fa-ellipsis-h text-xs"></i>
                                </button>
                                <div class="dropdown-menu llm-hidden">
                                    <button id="rename-session-btn" class="dropdown-item">
                                        <i class="fas fa-edit mr-2 text-xs"></i>重命名
                                    </button>
                                    <button id="export-session-btn" class="dropdown-item">
                                        <i class="fas fa-download mr-2 text-xs"></i>导出对话
                                    </button>
                                    <div class="border-t my-1"></div>
                                    <button id="clear-session-btn" class="dropdown-item text-red-600">
                                        <i class="fas fa-trash mr-2 text-xs"></i>清空对话
                                    </button>
                                    <button id="delete-session-btn" class="dropdown-item text-red-600">
                                        <i class="fas fa-trash-alt mr-2 text-xs"></i>删除对话
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 消息列表 -->
                <div class="flex-1 overflow-hidden  bg-white ">
                    <div id="messages-list" class="h-full overflow-y-auto p-4 space-y-4">
                        <!-- 消息会动态插入到这里 -->
                    </div>
                </div>

                <!-- 底部输入区 -->
                <div class=" bg-white">
                    <div class="max-w-3xl mx-auto p-4">
                        <div class="relative bg-white rounded-xl border border-gray-300 focus-within:border-primary-color focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                        <textarea
                                id="message-input"
                                rows="2"
                                class="w-full p-4 text-gray-900 resize-none focus:outline-none bg-transparent placeholder-gray-500 text-sm"
                                placeholder="输入消息...（Shift+Enter 换行，Enter 发送）"
                        ></textarea>

                            <div class="flex items-center justify-between px-4 pb-3">
                                <!-- 左侧功能按钮 -->
                                <div class="flex items-center space-x-1">
                                    <button id="chat-attachment-btn" class="btn-icon text-gray-500 hover:text-gray-700" title="上传文件">
                                        <i class="fas fa-paperclip text-sm"></i>
                                    </button>
                                    <button id="chat-voice-btn" class="btn-icon text-gray-500 hover:text-gray-700" title="语音输入">
                                        <i class="fas fa-microphone text-sm"></i>
                                    </button>
                                    <button id="chat-clear-btn" class="btn-icon text-gray-500 hover:text-gray-700" title="清空">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>

                                <!-- 右侧发送按钮 -->
                                <div class="flex items-center space-x-3">
                                    <div id="chat-char-count" class="text-xs text-gray-400">0/2000</div>
                                    <button id="chat-send-btn" class="btn btn-primary btn-sm px-4">
                                        <i class="fas fa-paper-plane mr-1 text-xs"></i>
                                        发送
                                    </button>
                                </div>
                            </div>

                            <!-- 提示信息 -->
                            <div class="px-4 pb-3 flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center space-x-3">
                                <span class="flex items-center">
                                    <i class="fas fa-lightbulb mr-1 text-xs"></i>
                                    支持 Markdown
                                </span>
                                    <span class="flex items-center">
                                    <i class="fas fa-keyboard mr-1 text-xs"></i>
                                    Enter 发送
                                </span>
                                </div>
                                <button id="regenerate-btn" class="btn-scene">
                                    <i class="fas fa-redo mr-1 text-xs"></i>重新生成
                                </button>
                            </div>
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
                    <button type="button" onclick="closeModal()" class="btn btn-sm btn-outline">取消</button>
                    <button type="button" id="confirm-action-btn" class="btn btn-sm btn-primary">确认</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="renameModal">
        <div class="modal-content max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">重命名对话</h3>
                <div class="space-y-4">
                    <div>
                        <input
                                type="text"
                                id="new-session-name"
                                class="input w-full"
                                placeholder="请输入对话名称"
                        >
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRenameModal()" class="btn btn-sm btn-outline">取消</button>
                        <button type="button" id="save-rename-btn" class="btn btn-sm btn-primary">保存</button>
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
            document.getElementById('initial-voice-btn').addEventListener('click', showVoiceInput);

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
            document.getElementById('chat-voice-btn').addEventListener('click', showVoiceInput);
            document.getElementById('regenerate-btn').addEventListener('click', regenerateLastResponse);

            // 搜索会话
            document.getElementById('search-sessions').addEventListener('input', searchSessions);

            // 使用事件委托处理会话列表点击（更可靠）
            document.getElementById('sessions-list').addEventListener('click', function(e) {
                // 处理会话项点击
                const sessionItem = e.target.closest('.session-item');
                if (sessionItem) {
                    const sessionId = sessionItem.dataset.sessionId;
                    if (sessionId) {
                        switchToSession(sessionId);
                    }
                    return;
                }
                
                // 处理固定按钮点击
                const pinBtn = e.target.closest('.session-pin-btn');
                if (pinBtn) {
                    e.stopPropagation();
                    const sessionId = pinBtn.closest('.session-item').dataset.sessionId;
                    if (sessionId) {
                        togglePinSession(sessionId);
                    }
                }
            });

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

            // 场景按钮点击
            document.querySelectorAll('.btn-scene').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
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

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    displaySessions(result.data);
                    updateSessionCount(result.data.length);
                } else {
                    console.error('加载会话列表失败：', result.message);
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
                    <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-comment text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-sm">暂无对话记录</p>
                    <p class="text-xs text-gray-400 mt-1">开始新对话</p>
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
                <div class="px-2 pt-2">
                    <div class="flex items-center text-xs text-gray-500 mb-2 px-2">
                        <i class="fas fa-thumbtack mr-1.5 text-yellow-500 text-xs"></i>
                        <span>固定对话</span>
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
                <div class="px-2 pt-2">
                    <div class="text-xs text-gray-500 mb-2 px-2">最近对话</div>
            `;

                regularSessions.forEach(session => {
                    html += createSessionItemHTML(session);
                });

                html += `</div>`;
            }

            container.innerHTML = html;
        }

        // 创建会话项HTML
        function createSessionItemHTML(session) {
            const title = session.title || '未命名对话';
            const time = formatTime(session.updated_at || session.created_at);
            const isActive = currentSessionId === session.id;
            const agentName = session.agent_name || '通用';
            const agentColor = getAgentColor(session.agent_id);

            return `
            <div class="session-item p-2 mb-1 rounded-lg cursor-pointer transition-colors ${isActive ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50'}"
                 data-session-id="${session.id}">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 text-sm truncate mb-1" title="${title}">${title}</div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">${time}</span>
                            <span class="text-xs px-1.5 py-0.5 ${agentColor} text-gray-700 rounded-full">
                                ${agentName}
                            </span>
                        </div>
                    </div>
                    <button class="session-pin-btn p-1 ml-1 text-gray-400 hover:text-yellow-500"
                            title="${session.is_pinned ? '取消固定' : '固定对话'}">
                        <i class="fas fa-thumbtack ${session.is_pinned ? 'text-yellow-500' : ''} text-xs"></i>
                    </button>
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

            // 如果有智能体，默认选中第一个
            let options = '';
            if (agents && agents.length > 0) {
                options += `<option value="">请选择智能体</option>`;
                agents.forEach((agent, index) => {
                    const selected = index === 0 ? 'selected' : '';
                    options += `<option value="${agent.id}" ${selected}>${agent.name}</option>`;
                });
            } else {
                options = `
                <option value="">请选择智能体</option>
                <option value="builtin_common" selected>通用助手</option>
            `;
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

                    showToast('新对话已创建', 'success');
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
        }

        // 切换到初始模式
        function switchToInitialMode() {
            currentSessionId = null;
            currentSessionAgent = null;

            document.getElementById('initial-mode').classList.remove('llm-hidden');
            document.getElementById('chat-mode').classList.add('llm-hidden');

            document.getElementById('initial-message-input').value = '';
            updateInitialCharCount();
            document.getElementById('agent-select').selectedIndex = 0;

            document.getElementById('current-agent-id').value = '';
            document.getElementById('current-session-id').value = '';
        }

        // 更新会话信息
        function updateSessionInfo(sessionData) {
            document.getElementById('session-title-display').textContent = sessionData.title || '未命名对话';

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
                // 显示加载状态
                showSessionLoading();
                
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
                        // 逐条添加历史消息，保持时间顺序
                        for (let i = 0; i < result.data.messages.length; i++) {
                            const msg = result.data.messages[i];
                            addMessage(msg.role === 'user' ? 'user' : 'ai', msg.content);
                            
                            // 添加小延迟以获得更好的视觉效果
                            if (i < result.data.messages.length - 1) {
                                await new Promise(resolve => setTimeout(resolve, 50));
                            }
                        }
                        
                        showToast(`已加载 ${result.data.messages.length} 条历史消息`, 'success');
                    } else {
                        // 没有历史消息时显示欢迎语
                        const agentName = result.data.agent?.name || '智能助手';
                        addMessage('ai', `您好！我是${agentName}，有什么可以帮您的吗？`);
                    }

                    // 聚焦到聊天输入框
                    setTimeout(() => {
                        document.getElementById('message-input').focus();
                    }, 100);
                    
                    // 更新会话列表的选中状态
                    updateSessionActiveState(sessionId);
                }
            } catch (error) {
                console.error('切换会话失败:', error);
                showToast('切换会话失败: ' + error.message, 'error');
            } finally {
                hideSessionLoading();
            }
        }

        // 添加消息到聊天
        function addMessage(role, content) {
            const messagesList = document.getElementById('messages-list');
            const messageId = 'msg-' + Date.now();
            const inputContainerWidth = 'max-w-3xl'; // 与输入框完全相同的宽度类

            if (role === 'user') {
                // 用户消息 - 宽度80%，右侧与输入框右侧对齐
                const userMessageHTML = `
                <div id="${messageId}" class="flex justify-end mb-4">
                    <div class="${inputContainerWidth} w-4/5">
                        <div class="bg-[#00b894] text-white rounded-2xl rounded-br-none px-4 py-3">
                            <div class="whitespace-pre-wrap break-words">${escapeHtml(content)}</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1 text-right">
                            ${formatTime(new Date().toISOString())}
                        </div>
                    </div>
                </div>
            `;
                messagesList.insertAdjacentHTML('beforeend', userMessageHTML);
            } else {
                // AI助手消息 - 宽度100%，与输入框完全对齐
                const aiMessageHTML = `
                <div id="${messageId}" class="flex justify-start mb-4">
                    <div class="${inputContainerWidth} w-full">
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3">
                            <div class="markdown-content whitespace-pre-wrap break-words">${marked.parse(content)}</div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="text-xs text-gray-500">
                                ${formatTime(new Date().toISOString())}
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-[#00b894] p-1 rounded hover:bg-gray-100" title="复制消息">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button onclick="likeMessage('${messageId}')" class="text-xs text-gray-400 hover:text-red-500 p-1 rounded hover:bg-gray-100" title="点赞">
                                    <i class="far fa-heart"></i>
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

        // 获取用户头像首字母
        function getUserAvatarInitial(name) {
            if (!name) return '我';
            return name.charAt(0).toUpperCase();
        }

        // 获取智能体头像首字母
        function getAgentAvatarInitial(name) {
            if (!name) return 'AI';
            return name.charAt(0).toUpperCase();
        }

        // 获取智能体头像（可以根据名称返回不同颜色）
        function getAgentAvatar(name) {
            const avatars = {
                '通用助手': '🤖',
                '代码助手': '💻',
                '写作助手': '✍️',
                '翻译助手': '🌐',
                '学习助手': '📚'
            };
            return avatars[name] || '🤖';
        }

        // 点赞消息
        function likeMessage(messageId) {
            const button = event.currentTarget;
            const icon = button.querySelector('i');
            
            if (icon.classList.contains('fas')) {
                // 取消点赞
                icon.classList.remove('fas', 'text-red-500');
                icon.classList.add('far', 'text-gray-400');
                button.title = '点赞';
                showToast('已取消点赞', 'info');
            } else {
                // 点赞
                icon.classList.remove('far', 'text-gray-400');
                icon.classList.add('fas', 'text-red-500');
                button.title = '取消点赞';
                showToast('已点赞', 'success');
            }
        }

        // 复制消息
        function copyMessage(messageId) {
            const messageElement = document.getElementById(messageId);
            const contentElement = messageElement.querySelector('.markdown-content') || 
                                 messageElement.querySelector('.whitespace-pre-wrap');
            
            if (contentElement) {
                const text = contentElement.innerText || contentElement.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    showToast('已复制到剪贴板', 'success');
                }).catch(err => {
                    console.error('复制失败:', err);
                    showToast('复制失败', 'error');
                });
            }
        }

        // 显示会话加载状态
        function showSessionLoading() {
            const messagesList = document.getElementById('messages-list');
            messagesList.innerHTML = `
                <div class="flex justify-center items-center h-full">
                    <div class="text-center">
                        <div class="w-8 h-8 mx-auto mb-3">
                            <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">正在加载会话历史...</p>
                    </div>
                </div>
            `;
        }

        // 隐藏会话加载状态
        function hideSessionLoading() {
            // 加载完成后会被实际消息替换，无需特殊处理
        }

        // 更新会话列表的选中状态
        function updateSessionActiveState(sessionId) {
            // 移除所有会话的选中状态
            document.querySelectorAll('.session-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'border', 'border-blue-200');
            });
            
            // 为当前会话添加选中状态
            const activeSession = document.querySelector(`.session-item[data-session-id="${sessionId}"]`);
            if (activeSession) {
                activeSession.classList.add('bg-blue-50', 'border', 'border-blue-200');
            }
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
                const inputContainerClass = 'max-w-3xl'; // 与输入框完全相同的类名
                
                messagesList.insertAdjacentHTML('beforeend', `
                <div id="${messageId}" class="flex justify-start mb-4">
                    <div class="${inputContainerClass} w-full mx-auto">
                        <!-- 消息内容 -->
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3">
                            <div id="${messageId}-content" class="markdown-content whitespace-pre-wrap break-words"></div>
                        </div>
                        
                        <!-- 消息底部信息 -->
                        <div class="flex items-center justify-between mt-2">
                            <div class="text-xs text-gray-500 flex items-center space-x-1">
                                <div class="w-2 h-2 bg-[#00b894] rounded-full animate-pulse"></div>
                                <span>正在思考...</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-[#00b894] p-1 rounded hover:bg-gray-100" title="复制消息">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
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

                                    // 检查delta.content字段
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
                const footer = messageElement.querySelector('.text-xs.text-gray-500.flex.items-center');
                if (footer) {
                    footer.innerHTML = `
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-xs text-gray-500">
                            ${formatTime(new Date().toISOString())}
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-blue-600 p-1 rounded hover:bg-gray-100" title="复制消息">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button onclick="likeMessage('${messageId}')" class="text-xs text-gray-400 hover:text-red-500 p-1 rounded hover:bg-gray-100" title="点赞">
                                <i class="far fa-heart"></i>
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
            <div id="${indicatorId}" class="flex mb-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-robot text-blue-500 text-xs"></i>
                </div>
                <div class="max-w-xl flex-1">
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-2.5">
                        <div class="typing-indicator flex items-center space-x-1">
                            <div class="w-2 h-2 bg-gray-300 rounded-full animate-pulse"></div>
                            <div class="w-2 h-2 bg-gray-300 rounded-full animate-pulse delay-150"></div>
                            <div class="w-2 h-2 bg-gray-300 rounded-full animate-pulse delay-300"></div>
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
                    ind.closest('.flex.mb-3')?.remove();
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
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // 固定/取消固定会话
        async function togglePinSession(sessionId) {
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

                    showToast(result.data.is_pinned ? '已固定对话' : '已取消固定', 'success');
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
            await togglePinSession(currentSessionId);
        }

        // 清空当前会话
        async function clearCurrentSession() {
            if (!currentSessionId) return;

            showConfirmModal('清空对话', '确定要清空当前对话的所有消息吗？此操作不可撤销。', async () => {
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
                        showToast('对话已清空', 'success');

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

            showConfirmModal('删除对话', '确定要删除这个对话吗？此操作不可撤销。', async () => {
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
                        showToast('对话已删除', 'success');

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
                showToast('请输入对话名称', 'error');
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
                    showToast('对话已重命名', 'success');
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

        // 语音输入
        function showVoiceInput() {
            showToast('语音功能开发中...', 'info');
        }

        // 重新生成最后一条消息
        async function regenerateLastResponse() {
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
            toast.className = `fixed top-4 right-4 z-50 px-4 py-2.5 rounded-lg shadow-lg text-white animate-fade-in ${
                type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                        type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
            }`;
            toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' :
                type === 'error' ? 'exclamation-circle' :
                    type === 'info' ? 'info-circle' : 'bell'} mr-2 text-sm"></i>
                <span class="text-sm">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-3 hover:opacity-80">
                    <i class="fas fa-times text-xs"></i>
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
        /* 基础样式 */
        .h-screen {
            height: 100vh;
        }

        /* 按钮样式 */
        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: var(--primary-color, #3b82f6);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-outline {
            background: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            border-color: #9ca3af;
        }

        .btn-icon {
            padding: 0.375rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .btn-icon-sm {
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }

        .btn-icon:hover, .btn-icon-sm:hover {
            background-color: #f3f4f6;
        }

        .btn-scene {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            background: white;
            transition: all 0.2s;
        }

        .btn-scene:hover {
            color: var(--primary-color, #3b82f6);
            border-color: var(--primary-color, #3b82f6);
        }

        /* 输入框样式 */
        .input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .input:focus {
            outline: none;
            border-color: var(--primary-color, #3b82f6);
            ring: 2px solid var(--primary-color, #3b82f6);
        }

        .input-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* 下拉菜单 */
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 160px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            text-align: left;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f9fafb;
        }

        /* 滚动条 */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* 模态框 */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 0.5rem;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
        }

        /* 隐藏类 */
        .llm-hidden {
            display: none !important;
        }

        /* 动画 */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .delay-150 {
            animation-delay: 150ms;
        }

        .delay-300 {
            animation-delay: 300ms;
        }

        /* Markdown 内容样式 */
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3 {
            font-weight: 600;
            margin-top: 1em;
            margin-bottom: 0.5em;
        }

        .markdown-content p {
            margin-bottom: 0.75em;
            line-height: 1.6;
        }

        .markdown-content ul,
        .markdown-content ol {
            padding-left: 1.5em;
            margin-bottom: 0.75em;
        }

        .markdown-content li {
            margin-bottom: 0.25em;
        }

        .markdown-content code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 0.875em;
        }

        .markdown-content pre {
            background-color: #1f2937;
            color: #f9fafb;
            padding: 0.75rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            margin: 0.5rem 0;
        }

        .markdown-content pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .w-64 {
                width: 100%;
                position: absolute;
                z-index: 40;
                height: 100%;
            }

            .flex-1 {
                width: 100%;
            }

            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection