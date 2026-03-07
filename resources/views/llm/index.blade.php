@extends('layouts.app')

@section('title', 'AI助手 - 蒙太奇')
@section('description', '与智能助手对话，获取帮助和建议')

@section('content')
    <div class="llm-shell flex bg-white">
        <!-- 左侧会话列表 -->
        <aside class="w-72 llm-sidebar border-r border-gray-200 bg-white flex flex-col">
            <!-- 会话列表头部 -->
            <div class="p-5 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 tracking-tight">AI 会话</h2>
                    <button onclick="switchToInitialMode()" class="btn btn-sm btn-primary flex items-center llm-new-chat-btn">
                        <i class="fas fa-plus mr-1 text-xs"></i>
                        新建
                    </button>
                </div>

                <!-- 搜索会话 -->
                <div class="relative llm-search-wrap">
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

                <div class="mt-3">
                    <select id="session-agent-filter" class="input input-sm w-full">
                        <option value="">全部智能体</option>
                    </select>
                </div>

                <div class="mt-3 flex gap-2 llm-session-quick-filters">
                    <button type="button" class="llm-filter-chip is-active" data-filter="all">全部</button>
                    <button type="button" class="llm-filter-chip" data-filter="pinned">固定</button>
                    <button type="button" class="llm-filter-chip" data-filter="active">最近活跃</button>
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
            <div class="p-4 border-t border-gray-200 llm-sidebar-footer">
                <div class="text-xs text-gray-500 flex justify-between">
                    <span>对话总数</span>
                    <span id="session-count" class="font-medium">0</span>
                </div>
                <button id="clear-unpinned-btn" type="button" class="btn btn-sm btn-outline w-full mt-3">
                    清理未固定会话
                </button>
                <div class="text-[11px] text-gray-400 mt-2">模型能力由已配置 Agent 决定</div>
                <div id="llm-layout-debug" class="text-[10px] text-gray-400 mt-2 break-all"></div>
            </div>
        </aside>

        <!-- 右侧主区域 -->
        <main class="flex-1 flex flex-col overflow-hidden llm-main">
            <!-- ==================== 状态1：新建对话界面 ==================== -->
            <div id="initial-mode" class="flex-1 flex flex-col bg-gray-50 overflow-hidden llm-initial">
                <!-- 顶部留空 -->
                <div class="h-4 bg-white"></div>

                <!-- 主内容区 -->
                <div class="flex-1 bg-white flex flex-col items-center justify-center overflow-auto llm-hero">
                    <div class="llm-content-frame w-full text-center px-6">
                        <div class="mb-2 llm-initial-title-wrap">
                            <h1 class="text-xl font-semibold text-gray-800 tracking-tight">开始一个新对话</h1>
                        </div>
                    </div>
                </div>

                <!-- 底部输入区域 -->
                <div class="bg-white border-t border-gray-200 llm-composer-wrap">
                    <div class="llm-content-frame mx-auto p-4">
                        <!-- 输入框容器 -->
                        <div class="relative bg-white rounded-xl border border-gray-300 focus-within:border-primary-color focus-within:ring-2 focus-within:ring-blue-100 transition-all llm-composer">
                        <textarea
                                id="initial-message-input"
                                rows="2"
                                class="w-full p-4 text-gray-900 resize-none focus:outline-none bg-transparent placeholder-gray-500 text-sm"
                                placeholder="输入您的问题..."
                        ></textarea>

                            <div class="flex items-center justify-between px-4 pb-3">
                                <!-- 左侧功能按钮 -->
                                <div class="flex items-center space-x-1">
                                    <button id="initial-attachment-btn" class="btn-icon text-gray-500 hover:text-gray-700 hidden" title="上传文件">
                                        <i class="fas fa-paperclip text-sm"></i>
                                    </button>
                                    <button id="initial-voice-btn" class="btn-icon text-gray-500 hover:text-gray-700 hidden" title="语音输入">
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
                            <div class="px-4 pb-3 flex justify-center space-x-2 flex-wrap llm-scene-row">
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
            <div id="chat-mode" class="llm-hidden flex-1 flex flex-col overflow-hidden llm-chat-mode">
                <!-- 会话标题栏 -->
                <div class="border-b border-gray-200 bg-white px-6 py-4 shrink-0 llm-chat-header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center llm-chat-title-icon">
                                <i class="fas fa-comment text-blue-500 text-xs"></i>
                            </div>
                            <div>
                                <div id="session-title-inline-wrap" class="llm-inline-title-wrap">
                                    <h3 id="session-title-display" class="font-semibold text-gray-900 text-sm cursor-text" title="双击可重命名">会话标题</h3>
                                    <input
                                        id="session-title-inline-input"
                                        type="text"
                                        class="input input-sm llm-inline-title-input hidden"
                                        maxlength="255"
                                        placeholder="请输入会话标题"
                                    >
                                </div>
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <span id="session-agent">智能体：加载中...</span>
                                    <span>•</span>
                                    <span id="session-time"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 会话操作 -->
                        <div class="flex items-center space-x-1">
                            <button id="fork-session-btn" class="btn-icon-sm" title="基于当前智能体新建会话">
                                <i class="fas fa-plus-circle text-xs"></i>
                            </button>
                            <button id="pin-session-btn" class="btn-icon-sm" title="固定会话">
                                <i class="fas fa-thumbtack text-xs"></i>
                            </button>
                            <div class="dropdown relative">
                                <button class="btn-icon-sm">
                                    <i class="fas fa-ellipsis-h text-xs"></i>
                                </button>
                                <div class="dropdown-menu">
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
                <div class="flex-1 overflow-hidden bg-white llm-messages-pane">
                    <div id="messages-list" class="h-full overflow-y-auto p-6 space-y-4 custom-scrollbar">
                        <!-- 消息会动态插入到这里 -->
                    </div>
                </div>

                <!-- 底部输入区 - 固定 -->
                <div class="bg-white border-t border-gray-200 shrink-0 llm-composer-wrap">
                    <div class="llm-content-frame mx-auto p-4">
                        <div class="relative bg-white rounded-xl border border-gray-300 focus-within:border-primary-color focus-within:ring-2 focus-within:ring-blue-100 transition-all llm-composer">
                        <textarea
                                id="message-input"
                                rows="2"
                                class="w-full p-4 text-gray-900 resize-none focus:outline-none bg-transparent placeholder-gray-500 text-sm"
                                placeholder="输入消息...（Shift+Enter 换行，Enter 发送）"
                        ></textarea>

                            <div class="flex items-center justify-between px-4 pb-3">
                                <!-- 左侧功能按钮 -->
                                <div class="flex items-center space-x-1">
                                    <button id="chat-attachment-btn" class="btn-icon text-gray-500 hover:text-gray-700 hidden" title="上传文件">
                                        <i class="fas fa-paperclip text-sm"></i>
                                    </button>
                                    <button id="chat-voice-btn" class="btn-icon text-gray-500 hover:text-gray-700 hidden" title="语音输入">
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
                            <div class="px-4 pb-3 flex items-center justify-between text-xs text-gray-500 llm-tip-row">
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
                                <button id="edit-last-question-btn" class="btn-scene">
                                    <i class="fas fa-pen mr-1 text-xs"></i>编辑上一问
                                </button>
                                <button id="fork-last-question-btn" class="btn-scene">
                                    <i class="fas fa-code-branch mr-1 text-xs"></i>上一问开新聊
                                </button>
                                <button id="quote-last-answer-btn" class="btn-scene">
                                    <i class="fas fa-quote-left mr-1 text-xs"></i>引用上一答
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
    <script src="/plugins/purify/purify.min.js"></script>

    <script>
        // 全局变量
        let currentSessionId = null;
        let currentSessionAgent = null;
        let allAgents = [];
        let allSessions = [];
        let sessionQuickFilter = 'all';
        let isStreaming = false;
        let currentStreamController = null;
        let currentThinkingIndicatorId = null;
        let currentStreamingMessageId = null;
        let isInlineRenaming = false;
        let layoutSyncRaf = null;

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
            requestLayoutSync();
            setTimeout(requestLayoutSync, 120);
            setTimeout(requestLayoutSync, 500);
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
            document.getElementById('edit-last-question-btn').addEventListener('click', editLastQuestion);
            document.getElementById('fork-last-question-btn').addEventListener('click', forkLastQuestionToNewSession);
            document.getElementById('quote-last-answer-btn').addEventListener('click', quoteLastAnswer);
            document.getElementById('clear-unpinned-btn').addEventListener('click', clearUnpinnedSessions);

            // 搜索会话
            document.getElementById('search-sessions').addEventListener('input', searchSessions);
            document.getElementById('session-agent-filter').addEventListener('change', searchSessions);
            document.querySelectorAll('.llm-filter-chip').forEach((button) => {
                button.addEventListener('click', function() {
                    sessionQuickFilter = this.dataset.filter || 'all';
                    document.querySelectorAll('.llm-filter-chip').forEach((chip) => chip.classList.remove('is-active'));
                    this.classList.add('is-active');
                    searchSessions();
                });
            });

            // 使用事件委托处理会话列表点击（更可靠）
            document.getElementById('sessions-list').addEventListener('click', function(e) {
                const pinBtn = e.target.closest('.session-pin-btn');
                if (pinBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const sessionId = pinBtn.closest('.session-item')?.dataset.sessionId;
                    if (sessionId) {
                        togglePinSession(sessionId);
                    }
                    return;
                }

                const sessionItem = e.target.closest('.session-item');
                if (sessionItem) {
                    const sessionId = sessionItem.dataset.sessionId;
                    if (sessionId) {
                        switchToSession(sessionId);
                    }
                }
            });

            // 模态框按钮
            document.getElementById('save-rename-btn').addEventListener('click', saveRenameSession);
            document.getElementById('rename-session-btn')?.addEventListener('click', renameCurrentSession);
            document.getElementById('delete-session-btn')?.addEventListener('click', deleteCurrentSession);
            document.getElementById('fork-session-btn')?.addEventListener('click', forkSessionWithCurrentAgent);
            document.getElementById('pin-session-btn')?.addEventListener('click', togglePinCurrentSession);
            document.getElementById('clear-session-btn')?.addEventListener('click', clearCurrentSession);
            document.getElementById('export-session-btn')?.addEventListener('click', exportCurrentSession);
            document.getElementById('session-title-display')?.addEventListener('dblclick', startInlineRename);

            const inlineTitleInput = document.getElementById('session-title-inline-input');
            inlineTitleInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveInlineRename();
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelInlineRename();
                }
            });
            inlineTitleInput?.addEventListener('blur', function() {
                if (isInlineRenaming) {
                    saveInlineRename();
                }
            });

            window.addEventListener('resize', requestLayoutSync);
            window.addEventListener('orientationchange', requestLayoutSync);
            const sessionsList = document.getElementById('sessions-list');
            if (sessionsList && window.MutationObserver) {
                const observer = new MutationObserver(() => requestLayoutSync());
                observer.observe(sessionsList, { childList: true, subtree: true });
            }

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

        function requestLayoutSync() {
            if (layoutSyncRaf) {
                cancelAnimationFrame(layoutSyncRaf);
            }
            layoutSyncRaf = requestAnimationFrame(syncLlmLayoutHeights);
        }

        function syncLlmLayoutHeights() {
            const shell = document.querySelector('.llm-shell');
            const sidebar = document.querySelector('.llm-sidebar');
            const main = document.querySelector('.llm-main');
            const sessionsList = document.getElementById('sessions-list');
            if (!shell || !sidebar || !main || !sessionsList) {
                return;
            }

            const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 900;
            const shellTopRaw = shell.getBoundingClientRect().top;
            const shellTop = Math.max(shellTopRaw, 0);
            const bottomGap = window.innerWidth <= 768 ? 12 : 20;
            const minShellHeight = window.innerWidth <= 768 ? 520 : 620;
            const targetShellHeight = Math.floor(viewportHeight - shellTop - bottomGap);
            let shellHeight = Math.max(420, Math.min(Math.max(minShellHeight, targetShellHeight), viewportHeight - 6));
            shell.style.height = `${shellHeight}px`;

            const sidebarHeader = sidebar.querySelector('.p-5.border-b.border-gray-200');
            const sidebarFooter = sidebar.querySelector('.llm-sidebar-footer');
            const headerHeight = sidebarHeader ? sidebarHeader.offsetHeight : 0;
            const footerHeight = sidebarFooter ? sidebarFooter.offsetHeight : 0;

            let sidebarHeight = shellHeight;
            if (window.innerWidth <= 1024) {
                const listNaturalHeight = sessionsList.scrollHeight;
                const listVisibleHeight = Math.min(listNaturalHeight, window.innerWidth <= 768 ? 200 : 240);
                const sidebarMinHeight = headerHeight + footerHeight + 110;
                const sidebarMaxHeight = Math.min(300, Math.floor(shellHeight * 0.4));
                sidebarHeight = Math.max(
                    sidebarMinHeight,
                    Math.min(sidebarMaxHeight, headerHeight + footerHeight + listVisibleHeight)
                );
            }

            sidebar.style.height = `${sidebarHeight}px`;
            sidebar.style.maxHeight = `${sidebarHeight}px`;

            const mainHeight = window.innerWidth <= 1024
                ? Math.max(260, shellHeight - sidebarHeight)
                : shellHeight;
            main.style.height = `${mainHeight}px`;
            main.style.minHeight = '0';

            const metrics = {
                viewportHeight,
                shellTopRaw: Math.round(shellTopRaw),
                shellTop: Math.round(shellTop),
                shellHeight,
                sidebarHeaderHeight: headerHeight,
                sidebarFooterHeight: footerHeight,
                sessionsScrollHeight: sessionsList.scrollHeight,
                sidebarHeight,
                mainHeight,
                width: window.innerWidth,
                ts: new Date().toISOString()
            };
            console.log('[LLM_LAYOUT_SYNC]', metrics);

            const debugEl = document.getElementById('llm-layout-debug');
            if (debugEl) {
                debugEl.textContent = `layout shell:${metrics.shellHeight}px sidebar:${metrics.sidebarHeight}px main:${metrics.mainHeight}px list:${metrics.sessionsScrollHeight}px vw:${metrics.width}`;
            }
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

        // 兼容 success/data 与 code/result 两种返回格式
        function unwrapApiPayload(result) {
            if (!result || typeof result !== 'object') {
                return null;
            }

            if (Object.prototype.hasOwnProperty.call(result, 'success')) {
                return result.success ? (result.data ?? null) : null;
            }

            if (Object.prototype.hasOwnProperty.call(result, 'code')) {
                if (Number(result.code) === 9999 || Number(result.code) === 0) {
                    return Object.prototype.hasOwnProperty.call(result, 'result') ? result.result : null;
                }
                return null;
            }

            if (Object.prototype.hasOwnProperty.call(result, 'data')) {
                return result.data;
            }

            if (Object.prototype.hasOwnProperty.call(result, 'result')) {
                return result.result;
            }

            return result;
        }

        function normalizeArrayPayload(payload) {
            if (Array.isArray(payload)) {
                return payload;
            }
            if (payload && Array.isArray(payload.data)) {
                return payload.data;
            }
            if (payload && typeof payload === 'object') {
                return Object.values(payload);
            }
            return [];
        }

        function renderMarkdown(content) {
            const rawHtml = marked.parse(content || '');
            if (window.DOMPurify) {
                return window.DOMPurify.sanitize(rawHtml, {
                    USE_PROFILES: { html: true }
                });
            }
            return escapeHtml(content || '').replace(/\n/g, '<br>');
        }

        function setStreamingState(streaming) {
            isStreaming = streaming;

            const regenerateBtn = document.getElementById('regenerate-btn');
            if (regenerateBtn) {
                regenerateBtn.innerHTML = streaming
                    ? '<i class="fas fa-stop mr-1 text-xs"></i>停止生成'
                    : '<i class="fas fa-redo mr-1 text-xs"></i>重新生成';
                regenerateBtn.classList.toggle('text-red-600', streaming);
            }

            const sendButtons = [
                document.getElementById('initial-send-btn'),
                document.getElementById('chat-send-btn')
            ];
            sendButtons.forEach((button) => {
                if (!button) {
                    return;
                }
                button.disabled = streaming;
                button.classList.toggle('opacity-60', streaming);
                button.classList.toggle('cursor-not-allowed', streaming);
            });
        }

        function stopStreaming(options = {}) {
            const { silent = false, preserveMessage = true } = options;

            if (currentStreamController) {
                currentStreamController.abort();
                currentStreamController = null;
            }

            if (!silent && currentStreamingMessageId && preserveMessage) {
                const footer = document.getElementById(`${currentStreamingMessageId}-footer`);
                if (footer) {
                    footer.innerHTML = `
                    <div class="text-xs text-amber-600 flex items-center gap-2">
                        <i class="fas fa-pause-circle"></i>
                        <span>已停止生成</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="copyMessage('${currentStreamingMessageId}')" class="text-xs text-gray-400 hover:text-blue-600 p-1 rounded hover:bg-gray-100" title="复制消息">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                `;
                }
            }

            removeThinkingIndicator(currentThinkingIndicatorId);
            currentThinkingIndicatorId = null;
            currentStreamingMessageId = null;
            setStreamingState(false);
        }

        // 加载会话列表
        async function loadSessions() {
            try {
                const response = await window.taskApiFetch('/api/v2/llm/sessions', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                const payload = unwrapApiPayload(result);
                const sessions = normalizeArrayPayload(payload);
                allSessions = sessions;
                populateSessionAgentFilter(sessions);
                displaySessions(sessions);
                updateSessionCount(sessions.length);
                updateClearUnpinnedButton(sessions);
                requestLayoutSync();
            } catch (error) {
                console.error('加载会话列表失败:', error);
                showError('加载会话列表失败，请检查网络连接');
            }
        }

        function updateClearUnpinnedButton(sessions = allSessions) {
            const button = document.getElementById('clear-unpinned-btn');
            if (!button) {
                return;
            }

            const count = (sessions || []).filter((session) => !session.is_pinned).length;
            button.disabled = count === 0;
            button.classList.toggle('opacity-60', count === 0);
            button.classList.toggle('cursor-not-allowed', count === 0);
            button.textContent = count > 0 ? `清理未固定会话 (${count})` : '清理未固定会话';
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
            const pinnedSessions = sessions.filter(s => s.is_pinned);
            const regularSessions = sessions.filter(s => !s.is_pinned);

            if (pinnedSessions.length > 0) {
                html += createSessionGroupHTML('固定对话', pinnedSessions, 'fas fa-thumbtack text-yellow-500');
            }

            const datedGroups = [
                { key: 'today', label: '今天' },
                { key: 'recent', label: '近 7 天' },
                { key: 'older', label: '更早' }
            ];

            const groupedSessions = {
                today: [],
                recent: [],
                older: []
            };

            regularSessions.forEach((session) => {
                groupedSessions[getSessionDateGroup(session)].push(session);
            });

            datedGroups.forEach((group, index) => {
                if (groupedSessions[group.key].length === 0) {
                    return;
                }
                if (html && index >= 0) {
                    html += `<div class="border-t border-gray-100 my-2"></div>`;
                }
                html += createSessionGroupHTML(group.label, groupedSessions[group.key]);
            });

            container.innerHTML = html;
            requestLayoutSync();
        }

        function createSessionGroupHTML(label, sessions, iconClass = '') {
            const iconHtml = iconClass ? `<i class="${iconClass} mr-1.5 text-xs"></i>` : '';
            let html = `
                <div class="px-2 pt-2">
                    <div class="flex items-center text-xs text-gray-500 mb-2 px-2">
                        ${iconHtml}
                        <span>${label}</span>
                    </div>
            `;

            sessions.forEach((session) => {
                html += createSessionItemHTML(session);
            });

            html += `</div>`;
            return html;
        }

        function getSessionDateGroup(session) {
            const rawTime = session?.updated_at || session?.last_message_at || session?.created_at;
            if (!rawTime) {
                return 'older';
            }

            const date = new Date(rawTime);
            if (Number.isNaN(date.getTime())) {
                return 'older';
            }

            const now = new Date();
            const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const diffMs = startOfToday.getTime() - new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime();
            const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

            if (diffDays <= 0) {
                return 'today';
            }
            if (diffDays < 7) {
                return 'recent';
            }
            return 'older';
        }

        function populateSessionAgentFilter(sessions) {
            const filter = document.getElementById('session-agent-filter');
            if (!filter) {
                return;
            }

            const currentValue = filter.value;
            const options = new Map();
            options.set('', '全部智能体');

            (sessions || []).forEach((session) => {
                const agentId = session && session.agent_id ? String(session.agent_id) : '';
                const agentName = session && session.agent_name ? session.agent_name : '';
                if (agentId && agentName && !options.has(agentId)) {
                    options.set(agentId, agentName);
                }
            });

            filter.innerHTML = Array.from(options.entries()).map(([value, label]) => {
                const selected = value === currentValue ? 'selected' : '';
                return `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
            }).join('');
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

        function setCurrentPinButtonState(isPinned) {
            const pinButton = document.getElementById('pin-session-btn');
            const pinIcon = pinButton ? pinButton.querySelector('i') : null;
            if (!pinButton || !pinIcon) {
                return;
            }

            pinButton.title = isPinned ? '取消固定会话' : '固定会话';
            pinIcon.classList.toggle('text-yellow-500', !!isPinned);
        }

        function renderSessionMessages(messages, fallbackAgentName = '智能助手') {
            const messagesList = document.getElementById('messages-list');
            messagesList.innerHTML = '';

            const historyMessages = Array.isArray(messages) ? messages : normalizeArrayPayload(messages);
            if (historyMessages.length === 0) {
                addMessage('ai', `您好！我是${fallbackAgentName}，有什么可以帮您的吗？`);
                return 0;
            }

            historyMessages.forEach((msg) => {
                const role = msg && msg.role === 'user' ? 'user' : 'ai';
                const content = msg && typeof msg.content === 'string' ? msg.content : '';
                if (content.trim() !== '') {
                    addMessage(role, content);
                }
            });

            return historyMessages.length;
        }

        function enhanceMessageBlocks(root) {
            if (!root) {
                return;
            }

            root.querySelectorAll('.markdown-content pre').forEach((pre) => {
                if (pre.dataset.enhanced === '1') {
                    return;
                }

                pre.dataset.enhanced = '1';
                const code = pre.querySelector('code');
                const langClass = code ? Array.from(code.classList).find((item) => item.startsWith('language-')) : '';
                const language = langClass ? langClass.replace('language-', '') : 'text';
                const toolbar = document.createElement('div');
                toolbar.className = 'llm-code-toolbar';
                toolbar.innerHTML = `
                    <span class="llm-code-lang">${escapeHtml(language)}</span>
                    <div class="llm-code-actions">
                        <button type="button" class="llm-code-btn" data-action="copy">复制</button>
                        <button type="button" class="llm-code-btn" data-action="toggle">折叠</button>
                    </div>
                `;

                pre.parentNode.insertBefore(toolbar, pre);
                toolbar.querySelector('[data-action="copy"]').addEventListener('click', async () => {
                    const text = code ? (code.innerText || code.textContent || '') : (pre.innerText || pre.textContent || '');
                    try {
                        await navigator.clipboard.writeText(text);
                        showToast('代码已复制', 'success');
                    } catch (error) {
                        console.error('复制代码失败:', error);
                        showToast('复制代码失败', 'error');
                    }
                });
                toolbar.querySelector('[data-action="toggle"]').addEventListener('click', () => {
                    const collapsed = pre.classList.toggle('llm-code-collapsed');
                    toolbar.querySelector('[data-action="toggle"]').textContent = collapsed ? '展开' : '折叠';
                });
            });
        }

        // 加载所有智能体
        async function loadAllAgents() {
            try {
                const response = await window.taskApiFetch('/api/v2/llm/agents', {
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
            ensureAgentSelected();
        }

        function ensureAgentSelected() {
            const select = document.getElementById('agent-select');
            if (!select) return;

            const hasCurrent = !!(select.value && String(select.value).trim() !== '');
            if (hasCurrent) return;

            for (let i = 0; i < select.options.length; i++) {
                const value = select.options[i].value;
                if (value && String(value).trim() !== '') {
                    select.selectedIndex = i;
                    break;
                }
            }
        }

        function selectAgentById(agentId) {
            const select = document.getElementById('agent-select');
            if (!select || !agentId) {
                ensureAgentSelected();
                return;
            }

            const normalizedAgentId = String(agentId);
            let matched = false;
            for (let i = 0; i < select.options.length; i++) {
                if (String(select.options[i].value) === normalizedAgentId) {
                    select.selectedIndex = i;
                    matched = true;
                    break;
                }
            }

            if (!matched) {
                ensureAgentSelected();
            }
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
                const response = await window.taskApiFetch('/api/v2/llm/sessions', {
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
                    ensureAgentSelected();

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
        function switchToInitialMode(options = {}) {
            const preserveAgent = options.preserveAgent !== false;
            const preservedAgentId = preserveAgent
                ? ((currentSessionAgent && currentSessionAgent.id) || document.getElementById('current-agent-id').value || '')
                : '';

            if (isStreaming) {
                stopStreaming({ silent: true, preserveMessage: false });
            }

            currentSessionId = null;
            currentSessionAgent = null;

            document.getElementById('initial-mode').classList.remove('llm-hidden');
            document.getElementById('chat-mode').classList.add('llm-hidden');

            document.getElementById('initial-message-input').value = '';
            updateInitialCharCount();
            selectAgentById(preservedAgentId);

            document.getElementById('current-agent-id').value = '';
            document.getElementById('current-session-id').value = '';
        }

        function forkSessionWithCurrentAgent() {
            const currentAgentId = (currentSessionAgent && currentSessionAgent.id) || document.getElementById('current-agent-id').value;
            switchToInitialMode({
                preserveAgent: true
            });

            if (currentAgentId) {
                selectAgentById(currentAgentId);
            }

            const input = document.getElementById('initial-message-input');
            input.focus();
            showToast('已为你开启新会话，当前智能体已自动沿用', 'success');
        }

        function forkLastQuestionToNewSession() {
            const lastQuestion = getLastUserMessage();
            if (!lastQuestion) {
                showToast('当前会话还没有可带出的提问', 'info');
                return;
            }

            const currentAgentId = (currentSessionAgent && currentSessionAgent.id) || document.getElementById('current-agent-id').value;
            switchToInitialMode({
                preserveAgent: true
            });

            if (currentAgentId) {
                selectAgentById(currentAgentId);
            }

            const input = document.getElementById('initial-message-input');
            input.value = lastQuestion;
            updateInitialCharCount();
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
            showToast('已把上一条问题带到新会话草稿', 'success');
        }

        // 更新会话信息
        function updateSessionInfo(sessionData) {
            document.getElementById('session-title-display').textContent = sessionData.title || '未命名对话';

            let agentName = '通用助手';
            let agentId = '';

            if (sessionData.agent && sessionData.agent.name) {
                agentName = sessionData.agent.name;
                agentId = sessionData.agent.id;
            } else if (sessionData.agent_name) {
                agentName = sessionData.agent_name;
            }

            if (sessionData.agent_id) {
                agentId = sessionData.agent_id;
            }

            document.getElementById('current-agent-id').value = agentId;

            document.getElementById('session-agent').textContent = `智能体：${agentName}`;
            setCurrentPinButtonState(!!sessionData.is_pinned);

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

                const response = await window.taskApiFetch(`/api/v2/llm/sessions/${sessionId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();
                const sessionData = unwrapApiPayload(result);
                if (sessionData) {
                    currentSessionId = sessionId;
                    currentSessionAgent = sessionData.agent;

                    // 切换到聊天模式
                    switchToChatMode();

                    // 更新会话信息
                    updateSessionInfo(sessionData);

                    // 更新当前智能体ID
                    if (currentSessionAgent && currentSessionAgent.id) {
                        document.getElementById('current-agent-id').value = currentSessionAgent.id;
                    }

                    const agentName = sessionData.agent?.name || '智能助手';
                    const loadedCount = renderSessionMessages(sessionData.messages, agentName);
                    if (loadedCount > 0) {
                        showToast(`已加载 ${loadedCount} 条历史消息`, 'success');
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

        // 获取当前智能体名称
        function getCurrentAgentName() {
            if (currentSessionAgent && currentSessionAgent.name) {
                return currentSessionAgent.name;
            }
            const sessionAgentText = document.getElementById('session-agent')?.textContent || '';
            return sessionAgentText.replace('智能体：', '').trim() || '智能助手';
        }

        // 添加消息到聊天
        function addMessage(role, content) {
            const messagesList = document.getElementById('messages-list');
            const messageId = 'msg-' + Date.now();
            const nowText = formatTime(new Date().toISOString());

            if (role === 'user') {
                const userInitial = getUserAvatarInitial();
                const userMessageHTML = `
                <div id="${messageId}" class="llm-message-row llm-message-user mb-5">
                    <div class="llm-content-frame mx-auto w-full">
                        <div class="llm-message-track llm-message-track-user">
                            <div class="llm-message-main llm-message-main-user">
                                <div class="llm-message-meta llm-message-meta-user">
                                    <span class="llm-role-chip">你</span>
                                    <span>${nowText}</span>
                                </div>
                                <div class="bg-[#00b894] text-white rounded-2xl rounded-br-none px-4 py-3 llm-message-bubble-user">
                                    <div class="whitespace-pre-wrap break-words">${escapeHtml(content)}</div>
                                </div>
                            </div>
                            <div class="llm-message-avatar llm-message-avatar-user">${userInitial}</div>
                        </div>
                    </div>
                </div>
            `;
                messagesList.insertAdjacentHTML('beforeend', userMessageHTML);
            } else {
                const agentName = getCurrentAgentName();
                const agentAvatar = getAgentAvatar(agentName);
                const aiMessageHTML = `
                <div id="${messageId}" class="llm-message-row llm-message-ai mb-5">
                    <div class="llm-content-frame mx-auto w-full">
                        <div class="llm-message-track">
                            <div class="llm-message-avatar llm-message-avatar-ai">${agentAvatar}</div>
                            <div class="llm-message-main llm-message-main-ai">
                                <div class="llm-message-meta">
                                    <span class="llm-role-chip">${escapeHtml(agentName)}</span>
                                    <span>${nowText}</span>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3 llm-message-bubble-ai">
                                    <div class="markdown-content whitespace-pre-wrap break-words">${renderMarkdown(content)}</div>
                                </div>
                                <div class="flex items-center justify-between mt-2 llm-message-actions">
                                    <div class="text-xs text-gray-500">
                                        已完成
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-[#00b894] p-1 rounded hover:bg-gray-100" title="复制消息">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button onclick="saveMessageAsNote('${messageId}')" class="text-xs text-gray-400 hover:text-emerald-600 p-1 rounded hover:bg-gray-100" title="保存为笔记">
                                            <i class="fas fa-sticky-note"></i>
                                        </button>
                                        <button onclick="saveMessageAsMind('${messageId}')" class="text-xs text-gray-400 hover:text-cyan-600 p-1 rounded hover:bg-gray-100" title="生成思维导图">
                                            <i class="fas fa-sitemap"></i>
                                        </button>
                                        <button onclick="quoteMessage('${messageId}')" class="text-xs text-gray-400 hover:text-blue-600 p-1 rounded hover:bg-gray-100" title="引用消息">
                                            <i class="fas fa-quote-left"></i>
                                        </button>
                                        <button onclick="likeMessage('${messageId}')" class="text-xs text-gray-400 hover:text-red-500 p-1 rounded hover:bg-gray-100" title="点赞">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                messagesList.insertAdjacentHTML('beforeend', aiMessageHTML);
                enhanceMessageBlocks(document.getElementById(messageId));
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

        function buildQuoteText(content) {
            return String(content || '')
                .split('\n')
                .map((line) => `> ${line}`)
                .join('\n');
        }

        function appendToMessageInput(text) {
            const messageInput = document.getElementById('message-input');
            const currentValue = messageInput.value.trim();
            messageInput.value = currentValue ? `${currentValue}\n\n${text}\n\n` : `${text}\n\n`;
            updateChatCharCount();
            messageInput.focus();
            messageInput.setSelectionRange(messageInput.value.length, messageInput.value.length);
        }

        function quoteMessage(messageId) {
            const messageElement = document.getElementById(messageId);
            if (!messageElement) {
                showToast('未找到可引用的消息', 'error');
                return;
            }

            const contentElement = messageElement.querySelector('.markdown-content') ||
                messageElement.querySelector('.whitespace-pre-wrap');
            const text = contentElement ? (contentElement.innerText || contentElement.textContent || '').trim() : '';
            if (!text) {
                showToast('消息内容为空，无法引用', 'info');
                return;
            }

            appendToMessageInput(buildQuoteText(text));
            showToast('已将消息引用到输入框', 'success');
        }

        async function saveMessageAsNote(messageId) {
            const messageElement = document.getElementById(messageId);
            if (!messageElement) {
                showToast('未找到可保存的消息', 'error');
                return;
            }

            const contentElement = messageElement.querySelector('.markdown-content') ||
                messageElement.querySelector('.whitespace-pre-wrap');
            const text = contentElement ? (contentElement.innerText || contentElement.textContent || '').trim() : '';
            if (!text) {
                showToast('消息内容为空，无法保存为笔记', 'info');
                return;
            }

            const sessionTitle = (document.getElementById('session-title-display')?.textContent || 'AI会话').trim();
            const firstLine = text.split('\n').find((line) => line.trim() !== '') || 'AI回复摘录';
            const noteTitle = `[AI] ${sessionTitle} - ${firstLine}`.slice(0, 255);

            try {
                const response = await window.taskApiFetch('/api/v2/notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: `${noteTitle}\n\n${text}`,
                        status: 1
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (result.success || Number(result.code) === 0 || Number(result.code) === 9999) {
                    showToast('已保存为私密笔记', 'success');
                    return;
                }

                throw new Error(result.message || result.msg || '保存失败');
            } catch (error) {
                console.error('保存为笔记失败:', error);
                showToast('保存笔记失败: ' + error.message, 'error');
            }
        }

        async function saveMessageAsMind(messageId) {
            const messageElement = document.getElementById(messageId);
            if (!messageElement) {
                showToast('未找到可生成导图的消息', 'error');
                return;
            }

            const contentElement = messageElement.querySelector('.markdown-content') ||
                messageElement.querySelector('.whitespace-pre-wrap');
            const text = contentElement ? (contentElement.innerText || contentElement.textContent || '').trim() : '';
            if (!text) {
                showToast('消息内容为空，无法生成导图', 'info');
                return;
            }

            const sessionTitle = (document.getElementById('session-title-display')?.textContent || 'AI会话').trim();
            const firstLine = text.split('\n').find((line) => line.trim() !== '') || 'AI回复导图';
            const mindName = `[AI导图] ${sessionTitle} - ${firstLine}`.slice(0, 255);

            try {
                const response = await window.taskApiFetch('/api/v2/minds', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: mindName,
                        content: text,
                        source_type: 'llm',
                        source_id: currentSessionId || null
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                const payload = unwrapApiPayload(result);
                const mindId = payload && (payload.id || (payload.mind && payload.mind.id));
                if (!mindId) {
                    throw new Error(result.message || result.msg || '导图创建失败');
                }

                showToast('思维导图已创建，已为你打开编辑页', 'success');
                window.open(`/mind/${mindId}`, '_blank');
            } catch (error) {
                console.error('保存为思维导图失败:', error);
                showToast('生成思维导图失败: ' + error.message, 'error');
            }
        }

        function getConversationMessages() {
            return Array.from(document.querySelectorAll('#messages-list .llm-message-row')).map((row) => {
                const role = row.classList.contains('llm-message-user') ? 'user' : 'assistant';
                const contentElement = row.querySelector('.markdown-content') || row.querySelector('.whitespace-pre-wrap');
                const content = contentElement ? (contentElement.innerText || contentElement.textContent || '').trim() : '';
                return { role, content };
            }).filter((item) => item.content !== '');
        }

        function getLastUserMessage() {
            const messages = getConversationMessages().filter((item) => item.role === 'user');
            return messages.length > 0 ? messages[messages.length - 1].content : '';
        }

        function getLastAssistantMessage() {
            const messages = getConversationMessages().filter((item) => item.role === 'assistant');
            return messages.length > 0 ? messages[messages.length - 1].content : '';
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
            if (isStreaming) {
                stopStreaming({ silent: true });
            }

            try {
                let finalAgentId = agentId;
                if (!finalAgentId) {
                    finalAgentId = document.getElementById('current-agent-id').value;
                }

                currentStreamController = new AbortController();
                setStreamingState(true);

                currentThinkingIndicatorId = showThinkingIndicator();

                const response = await window.taskApiFetch('/api/v2/llm/chat', {
                    method: 'POST',
                    signal: currentStreamController.signal,
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

                removeThinkingIndicator(currentThinkingIndicatorId);
                currentThinkingIndicatorId = null;

                const requestStartAt = Date.now();

                // 创建消息元素
                const messageId = 'ai-msg-' + Date.now();
                const messagesList = document.getElementById('messages-list');
                const agentName = getCurrentAgentName();
                const agentAvatar = getAgentAvatar(agentName);
                currentStreamingMessageId = messageId;

                messagesList.insertAdjacentHTML('beforeend', `
                <div id="${messageId}" class="llm-message-row llm-message-ai mb-5">
                    <div class="llm-content-frame mx-auto w-full">
                        <div class="llm-message-track">
                            <div class="llm-message-avatar llm-message-avatar-ai">${agentAvatar}</div>
                            <div class="llm-message-main llm-message-main-ai">
                                <div class="llm-message-meta">
                                    <span class="llm-role-chip">${escapeHtml(agentName)}</span>
                                    <span>${formatTime(new Date().toISOString())}</span>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3 llm-message-bubble-ai">
                                    <div id="${messageId}-content" class="markdown-content whitespace-pre-wrap break-words"></div>
                                </div>
                                <div id="${messageId}-footer" class="flex items-center justify-between mt-2 llm-message-actions">
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
                                            contentElement.innerHTML = renderMarkdown(accumulatedContent);
                                            enhanceMessageBlocks(contentElement.closest('.llm-message-row'));
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
                const elapsedMs = Date.now() - requestStartAt;
                const elapsedText = elapsedMs >= 1000 ? `${(elapsedMs / 1000).toFixed(1)}s` : `${elapsedMs}ms`;
                const footer = document.getElementById(`${messageId}-footer`);
                if (footer) {
                    footer.innerHTML = `
                    <div class="text-xs text-gray-500 flex items-center gap-2">
                        <span>${formatTime(new Date().toISOString())}</span>
                        <span class="llm-sep-dot">•</span>
                        <span>耗时 ${elapsedText}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="copyMessage('${messageId}')" class="text-xs text-gray-400 hover:text-blue-600 p-1 rounded hover:bg-gray-100" title="复制消息">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button onclick="saveMessageAsNote('${messageId}')" class="text-xs text-gray-400 hover:text-emerald-600 p-1 rounded hover:bg-gray-100" title="保存为笔记">
                            <i class="fas fa-sticky-note"></i>
                        </button>
                        <button onclick="saveMessageAsMind('${messageId}')" class="text-xs text-gray-400 hover:text-cyan-600 p-1 rounded hover:bg-gray-100" title="生成思维导图">
                            <i class="fas fa-sitemap"></i>
                        </button>
                        <button onclick="likeMessage('${messageId}')" class="text-xs text-gray-400 hover:text-red-500 p-1 rounded hover:bg-gray-100" title="点赞">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                `;
                }

                reader.releaseLock();
                currentStreamController = null;
                currentStreamingMessageId = null;
                setStreamingState(false);

                // 重新加载会话列表以更新最后消息时间
                loadSessions();

            } catch (error) {
                console.error('发送消息到AI失败:', error);

                removeThinkingIndicator(currentThinkingIndicatorId);
                currentThinkingIndicatorId = null;

                // 显示错误消息
                if (error.name !== 'AbortError') {
                    addMessage('ai', '抱歉，请求失败。请稍后重试。');
                    showToast('网络请求失败: ' + error.message, 'error');
                }

                currentStreamController = null;
                currentStreamingMessageId = null;
                setStreamingState(false);
            }
        }

        // 显示思考指示器
        function showThinkingIndicator() {
            const messagesList = document.getElementById('messages-list');
            const indicatorId = 'thinking-' + Date.now();
            const agentName = getCurrentAgentName();
            const agentAvatar = getAgentAvatar(agentName);

            messagesList.insertAdjacentHTML('beforeend', `
            <div id="${indicatorId}" class="llm-message-row llm-message-ai mb-4">
                <div class="llm-content-frame mx-auto w-full">
                    <div class="llm-message-track">
                        <div class="llm-message-avatar llm-message-avatar-ai">${agentAvatar}</div>
                        <div class="llm-message-main llm-message-main-ai">
                            <div class="llm-message-meta">
                                <span class="llm-role-chip">${escapeHtml(agentName)}</span>
                                <span>思考中</span>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3">
                                <div class="typing-indicator flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-gray-300 rounded-full animate-pulse"></div>
                                    <div class="w-2 h-2 bg-gray-300 rounded-full animate-pulse delay-150"></div>
                                    <div class="w-2 h-2 bg-gray-300 rounded-full animate-pulse delay-300"></div>
                                </div>
                            </div>
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
                    ind.closest('.llm-message-row')?.remove();
                });
            }
        }

        // 搜索会话
        function searchSessions() {
            const searchTerm = document.getElementById('search-sessions').value.toLowerCase();
            const selectedAgentId = document.getElementById('session-agent-filter').value;
            const sessions = (allSessions || []).filter((session) => {
                const title = String(session.title || '').toLowerCase();
                const titleMatch = !searchTerm || title.includes(searchTerm);
                const agentMatch = !selectedAgentId || String(session.agent_id || '') === selectedAgentId;
                const quickFilterMatch = (() => {
                    if (sessionQuickFilter === 'pinned') {
                        return !!session.is_pinned;
                    }
                    if (sessionQuickFilter === 'active') {
                        const rawTime = session?.updated_at || session?.last_message_at || session?.created_at;
                        if (!rawTime) {
                            return false;
                        }
                        const date = new Date(rawTime);
                        if (Number.isNaN(date.getTime())) {
                            return false;
                        }
                        return (Date.now() - date.getTime()) <= 1000 * 60 * 60 * 24 * 3;
                    }
                    return true;
                })();
                return titleMatch && agentMatch && quickFilterMatch;
            });

            displaySessions(sessions);
            updateClearUnpinnedButton(sessions);
            requestLayoutSync();
        }

        // 固定/取消固定会话
        async function togglePinSession(sessionId) {
            try {
                const response = await window.taskApiFetch(`/api/v2/llm/sessions/${sessionId}/toggle-pin`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    if (String(currentSessionId) === String(sessionId)) {
                        setCurrentPinButtonState(!!result.data.is_pinned);
                    }

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
                    if (isStreaming) {
                        stopStreaming({ silent: true, preserveMessage: false });
                    }

                    const response = await window.taskApiFetch(`/api/v2/llm/sessions/${currentSessionId}/clear`, {
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
            if (!currentSessionId) {
                showToast('请先打开一个会话', 'error');
                return;
            }

            const title = (document.getElementById('session-title-display')?.textContent || '未命名对话').trim();
            const agent = getCurrentAgentName();
            const messages = getConversationMessages();

            if (messages.length === 0) {
                showToast('当前会话暂无可导出的内容', 'info');
                return;
            }

            const lines = [
                `# ${title}`,
                '',
                `- 智能体：${agent}`,
                `- 导出时间：${new Date().toLocaleString('zh-CN')}`,
                ''
            ];

            messages.forEach((item) => {
                lines.push(`## ${item.role === 'user' ? '用户' : 'AI'}`);
                lines.push(item.content);
                lines.push('');
            });

            const blob = new Blob([lines.join('\n')], { type: 'text/markdown;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${title.replace(/[\\\\/:*?"<>|]/g, '_') || 'llm-session'}.md`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(link.href);
            showToast('对话已导出', 'success');
        }

        // 删除会话
        async function deleteCurrentSession() {
            if (!currentSessionId) return;

            showConfirmModal('删除对话', '确定要删除这个对话吗？此操作不可撤销。', async () => {
                try {
                    if (isStreaming) {
                        stopStreaming({ silent: true, preserveMessage: false });
                    }

                    const response = await window.taskApiFetch(`/api/v2/llm/sessions/${currentSessionId}`, {
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

            const saved = await updateSessionTitle(newName);
            if (saved) {
                closeRenameModal();
            }
        }

        async function updateSessionTitle(newName) {
            if (!currentSessionId) return;

            try {
                const response = await window.taskApiFetch(`/api/v2/llm/sessions/${currentSessionId}/title`, {
                    method: 'PUT',
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
                    await loadSessions();
                    return true;
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('重命名会话失败:', error);
                showToast('重命名失败: ' + error.message, 'error');
                return false;
            }
        }

        function startInlineRename() {
            if (!currentSessionId || isInlineRenaming) {
                return;
            }

            const titleDisplay = document.getElementById('session-title-display');
            const titleInput = document.getElementById('session-title-inline-input');
            if (!titleDisplay || !titleInput) {
                return;
            }

            isInlineRenaming = true;
            titleInput.value = (titleDisplay.textContent || '').trim();
            titleDisplay.classList.add('hidden');
            titleInput.classList.remove('hidden');
            titleInput.focus();
            titleInput.select();
        }

        function cancelInlineRename() {
            const titleDisplay = document.getElementById('session-title-display');
            const titleInput = document.getElementById('session-title-inline-input');
            if (!titleDisplay || !titleInput) {
                return;
            }

            isInlineRenaming = false;
            titleInput.classList.add('hidden');
            titleDisplay.classList.remove('hidden');
        }

        async function saveInlineRename() {
            const titleInput = document.getElementById('session-title-inline-input');
            const titleDisplay = document.getElementById('session-title-display');
            if (!titleInput || !titleDisplay || !isInlineRenaming) {
                return;
            }

            const newName = titleInput.value.trim();
            if (!newName) {
                showToast('请输入对话名称', 'error');
                titleInput.focus();
                return;
            }

            const currentName = (titleDisplay.textContent || '').trim();
            if (newName === currentName) {
                cancelInlineRename();
                return;
            }

            const saved = await updateSessionTitle(newName);
            if (saved) {
                cancelInlineRename();
            } else {
                titleInput.focus();
                titleInput.select();
            }
        }

        // 显示附件选项
        function showAttachmentOptions() {
            showToast('附件能力暂未开放，后续会接入真实文件上下文。', 'info');
        }

        // 语音输入
        function showVoiceInput() {
            showToast('语音输入暂未开放，当前先使用文本会话。', 'info');
        }

        function editLastQuestion() {
            const lastUserMessage = getLastUserMessage();
            if (!lastUserMessage) {
                showToast('当前会话还没有可编辑的问题', 'info');
                return;
            }

            const messageInput = document.getElementById('message-input');
            messageInput.value = lastUserMessage;
            updateChatCharCount();
            messageInput.focus();
            messageInput.setSelectionRange(messageInput.value.length, messageInput.value.length);
            showToast('已载入上一条问题，可直接修改后重发', 'success');
        }

        function quoteLastAnswer() {
            const lastAssistantMessage = getLastAssistantMessage();
            if (!lastAssistantMessage) {
                showToast('当前会话还没有可引用的回复', 'info');
                return;
            }

            appendToMessageInput(buildQuoteText(lastAssistantMessage));
            showToast('已引用上一条 AI 回复', 'success');
        }

        async function clearUnpinnedSessions() {
            const unpinnedSessions = (allSessions || []).filter((session) => !session.is_pinned);
            if (unpinnedSessions.length === 0) {
                showToast('当前没有可清理的未固定会话', 'info');
                return;
            }

            showConfirmModal(
                '清理未固定会话',
                `确定要删除 ${unpinnedSessions.length} 个未固定会话吗？此操作不可撤销。`,
                async () => {
                    let removedCount = 0;

                    for (const session of unpinnedSessions) {
                        try {
                            await window.taskApiFetch(`/api/v2/llm/sessions/${session.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });

                            if (String(currentSessionId) === String(session.id)) {
                                switchToInitialMode();
                            }
                            removedCount += 1;
                        } catch (error) {
                            console.error('批量删除会话失败:', error);
                        }
                    }

                    await loadSessions();
                    showToast(`已清理 ${removedCount} 个未固定会话`, removedCount > 0 ? 'success' : 'info');
                }
            );
        }

        // 重新生成最后一条消息
        async function regenerateLastResponse() {
            if (isStreaming) {
                stopStreaming();
                return;
            }

            if (!currentSessionId) {
                showToast('请先打开一个会话', 'error');
                return;
            }

            try {
                const response = await window.taskApiFetch(`/api/v2/llm/sessions/${currentSessionId}/regenerate`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (!result.success || !result.data || !result.data.query) {
                    throw new Error(result.message || '没有可重新生成的问题');
                }

                await switchToSession(currentSessionId);
                await loadSessions();
                await sendMessageToAI(result.data.query, result.data.agent_id || document.getElementById('current-agent-id').value);
            } catch (error) {
                console.error('重新生成失败:', error);
                showToast('重新生成失败: ' + error.message, 'error');
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
        .llm-shell {
            --llm-bg: #f4f7fb;
            --llm-card: #ffffff;
            --llm-text: #111827;
            --llm-subtle: #6b7280;
            --llm-border: #e6eaf0;
            --llm-primary: #0f766e;
            --llm-primary-soft: #e6f8f5;
            --llm-user: #0f766e;
            --llm-user-grad: linear-gradient(130deg, #0f766e 0%, #0b5f58 100%);
            --llm-shadow: 0 18px 40px -24px rgba(15, 118, 110, 0.35);
            height: calc(100vh - 10rem);
            min-height: 680px;
            background:
                radial-gradient(circle at 6% 10%, rgba(15, 118, 110, 0.12) 0, rgba(15, 118, 110, 0) 28%),
                radial-gradient(circle at 94% 92%, rgba(30, 64, 175, 0.1) 0, rgba(30, 64, 175, 0) 24%),
                var(--llm-bg);
            color: var(--llm-text);
            font-family: "Plus Jakarta Sans", "Noto Sans SC", "PingFang SC", sans-serif;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e6eaf0;
        }

        .llm-sidebar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-color: var(--llm-border);
            height: 100%;
        }

        .llm-main {
            background: transparent;
            height: 100%;
            min-height: 0;
        }

        .llm-search-wrap .input {
            background: #f8fafc;
            border-color: #dde4ed;
            border-radius: 12px;
            height: 36px;
        }

        .llm-sidebar-footer {
            background: linear-gradient(180deg, rgba(249, 252, 255, 0.7), rgba(246, 251, 250, 0.95));
        }

        .llm-new-chat-btn {
            border-radius: 10px;
            padding: 0.45rem 0.7rem;
        }

        .llm-hero {
            background: transparent;
            justify-content: flex-end;
            padding-bottom: 0.75rem;
        }

        .llm-initial-title-wrap {
            opacity: 0.9;
        }

        .llm-chat-mode {
            background: transparent;
        }

        .llm-chat-header {
            background: rgba(255, 255, 255, 0.92);
            border-color: var(--llm-border);
            backdrop-filter: blur(12px);
        }

        .llm-chat-title-icon {
            background: var(--llm-primary-soft);
        }

        .llm-messages-pane {
            background: linear-gradient(180deg, rgba(247, 250, 252, 0.95), rgba(243, 247, 251, 0.7));
        }

        .llm-composer-wrap {
            background: rgba(255, 255, 255, 0.92);
            border-color: var(--llm-border);
            backdrop-filter: blur(12px);
        }

        .llm-composer {
            border-radius: 16px;
            border-color: #dfe6ef;
            box-shadow: var(--llm-shadow);
            background: #fff;
        }

        .llm-composer textarea {
            min-height: 70px;
            line-height: 1.6;
        }

        .llm-scene-row {
            row-gap: 0.45rem;
        }

        .llm-tip-row {
            border-top: 1px dashed #e6ecf3;
            margin: 0 0.8rem;
            padding-top: 0.65rem;
        }

        .llm-message-row {
            width: 100%;
        }

        .llm-content-frame {
            width: 100%;
            max-width: 100%;
        }

        .llm-message-track {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
        }

        .llm-message-track-user {
            justify-content: flex-end;
        }

        .llm-message-main {
            min-width: 0;
        }

        .llm-message-main-ai {
            width: calc(100% - 46px);
        }

        .llm-message-main-user {
            width: min(82%, 46rem);
        }

        .llm-message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            line-height: 1;
            flex: 0 0 32px;
        }

        .llm-message-avatar-ai {
            background: #dff6f3;
        }

        .llm-message-avatar-user {
            background: #0f766e;
            color: #fff;
            font-weight: 700;
        }

        .llm-message-meta {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.72rem;
            color: #6b7280;
            margin-bottom: 0.35rem;
            white-space: nowrap;
        }

        .llm-message-meta-user {
            justify-content: flex-end;
        }

        .llm-inline-title-wrap {
            min-width: 0;
        }

        .llm-inline-title-input {
            min-width: 16rem;
            max-width: min(28rem, 56vw);
            height: 2rem;
            font-weight: 600;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .llm-role-chip {
            display: inline-flex;
            align-items: center;
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0.14rem 0.45rem;
            border-radius: 999px;
            background: #eef4fb;
            color: #334155;
            font-weight: 600;
        }

        .llm-message-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .llm-sep-dot {
            opacity: 0.55;
        }

        .llm-shell .btn {
            padding: 0.4rem 0.78rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .llm-shell .btn-sm {
            padding: 0.26rem 0.58rem;
            font-size: 0.74rem;
        }

        .llm-shell .btn-primary {
            background: var(--llm-user-grad);
            color: #fff;
            border: none;
            box-shadow: 0 10px 18px -14px rgba(15, 118, 110, 0.7);
        }

        .llm-shell .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .llm-shell .btn-outline {
            background: #fff;
            border: 1px solid #d6dee8;
            color: #334155;
        }

        .llm-shell .btn-outline:hover {
            border-color: #9fb2c8;
        }

        .llm-shell .btn-icon,
        .llm-shell .btn-icon-sm {
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .llm-shell .btn-icon:hover,
        .llm-shell .btn-icon-sm:hover {
            background-color: #f2f7fa;
            color: var(--llm-primary);
        }

        #fork-session-btn:hover {
            color: #0f766e;
            background: #ecfdf5;
        }

        .llm-shell .btn-scene {
            padding: 0.34rem 0.75rem;
            font-size: 0.74rem;
            color: #4b5563;
            border: 1px solid #dbe3ec;
            border-radius: 999px;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .llm-shell .btn-scene:hover {
            color: var(--llm-primary);
            border-color: #98c5bf;
            background: #f2fffd;
        }

        .llm-filter-chip {
            flex: 1 1 0;
            border: 1px solid #dbe3ec;
            border-radius: 999px;
            background: #fff;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.36rem 0.2rem;
            transition: all 0.2s ease;
        }

        .llm-filter-chip:hover {
            color: var(--llm-primary);
            border-color: #98c5bf;
            background: #f2fffd;
        }

        .llm-filter-chip.is-active {
            color: #0f766e;
            border-color: #8bc9bf;
            background: linear-gradient(180deg, #f0fdfa 0%, #ecfeff 100%);
            box-shadow: 0 8px 16px -14px rgba(15, 118, 110, 0.75);
        }

        .llm-shell .input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d4dbe5;
            border-radius: 10px;
            font-size: 0.84rem;
            transition: all 0.2s ease;
        }

        .llm-shell .input:focus {
            outline: none;
            border-color: #80bfb7;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
        }

        .llm-shell .input-sm {
            padding: 0.38rem 0.74rem;
            font-size: 0.76rem;
        }

        #sessions-list .session-item {
            border: 1px solid transparent;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.75);
            transition: all 0.2s ease;
            margin-bottom: 0.45rem;
        }

        #sessions-list .session-item:hover {
            border-color: #d9e4ef;
            background: #f8fbff;
            transform: translateX(2px);
        }

        #sessions-list .session-item.bg-blue-50 {
            border-color: #8ec7bf !important;
            background: linear-gradient(180deg, #f0fdfa 0%, #ecfeff 100%) !important;
            box-shadow: 0 10px 20px -18px rgba(15, 118, 110, 0.7);
        }

        .llm-code-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.8rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #dbe5ef;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .llm-code-lang {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #4b5563;
        }

        .llm-code-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .llm-code-btn {
            border: 1px solid #cfe0dd;
            border-radius: 999px;
            padding: 0.22rem 0.62rem;
            font-size: 0.72rem;
            color: #0f766e;
            background: #ffffff;
            transition: all 0.18s ease;
        }

        .llm-code-btn:hover {
            background: #ecfdf5;
            border-color: #8cc9bf;
        }

        #messages-list > .flex {
            animation: llm-slide-up 0.22s ease-out;
        }

        .bg-\[\#00b894\] {
            background: var(--llm-user-grad) !important;
            box-shadow: 0 14px 28px -20px rgba(15, 118, 110, 0.8);
        }

        #messages-list .bg-white.border.border-gray-200.rounded-2xl {
            border-color: #dfe6ef !important;
            border-radius: 16px;
            box-shadow: 0 14px 28px -24px rgba(30, 41, 59, 0.42);
            background: #fff;
        }

        .llm-shell .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            min-width: 176px;
            background: #fff;
            border: 1px solid #dbe5ef;
            border-radius: 12px;
            box-shadow: 0 24px 30px -24px rgba(15, 23, 42, 0.6);
            z-index: 50;
            overflow: hidden;
        }

        .llm-shell .dropdown:hover .dropdown-menu {
            display: block;
        }

        .llm-shell .dropdown-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.56rem 0.95rem;
            font-size: 0.82rem;
            text-align: left;
            transition: background-color 0.2s ease;
        }

        .llm-shell .dropdown-item:hover {
            background: #f3f7fb;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #ced8e5;
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #b5c3d6;
        }

        main.max-w-7xl.mx-auto .modal {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.44);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
        }

        main.max-w-7xl.mx-auto .modal.show {
            display: flex;
        }

        main.max-w-7xl.mx-auto .modal-content {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #dce5ef;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            box-shadow: 0 30px 46px -30px rgba(15, 23, 42, 0.58);
        }

        .llm-hidden {
            display: none !important;
        }

        @keyframes llm-slide-up {
            from {
                opacity: 0;
                transform: translateY(9px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
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
                opacity: 0.45;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .animate-pulse {
            animation: pulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .delay-150 {
            animation-delay: 150ms;
        }

        .delay-300 {
            animation-delay: 300ms;
        }

        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3 {
            font-weight: 700;
            margin-top: 1em;
            margin-bottom: 0.5em;
            color: #0f172a;
        }

        .markdown-content p {
            margin-bottom: 0.75em;
            line-height: 1.72;
            color: #1f2937;
        }

        .markdown-content ul,
        .markdown-content ol {
            padding-left: 1.4em;
            margin-bottom: 0.75em;
        }

        .markdown-content li {
            margin-bottom: 0.25em;
        }

        .markdown-content code {
            background: #eef3f8;
            padding: 0.1rem 0.28rem;
            border-radius: 0.3rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.88em;
        }

        .markdown-content pre {
            background: #0f172a;
            color: #f8fafc;
            padding: 0.9rem;
            border-radius: 0 0 12px 12px;
            overflow-x: auto;
            margin: 0.7rem 0;
        }

        .markdown-content pre.llm-code-collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            margin-bottom: 0.7rem;
            overflow: hidden;
            border-width: 0 1px 1px 1px;
        }

        .markdown-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        .w-4\/5 {
            width: 82%;
        }

        .max-w-\[80\%\] {
            max-width: 82%;
        }

        @media (max-width: 1024px) {
            .llm-shell {
                flex-direction: column;
                height: calc(100vh - 8rem);
                min-height: 620px;
                border-radius: 18px;
            }

            .llm-sidebar {
                width: 100%;
                max-height: 280px;
                flex: 0 0 auto;
            }

            .llm-main {
                flex: 1 1 auto;
                height: auto;
                min-height: 0;
            }

            .llm-hero {
                justify-content: flex-end;
                padding-bottom: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .llm-shell {
                height: calc(100vh - 7.5rem);
                min-height: 520px;
                border-radius: 14px;
            }

            .llm-chat-header,
            .llm-composer-wrap {
                padding-left: 0.7rem;
                padding-right: 0.7rem;
            }

            #messages-list {
                padding: 1rem;
            }

            .llm-content-frame {
                width: 100%;
            }

            .w-4\/5,
            .max-w-\[80\%\] {
                width: 92%;
                max-width: 92%;
            }

            .llm-tip-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.6rem;
            }

            .llm-inline-title-input {
                min-width: 10rem;
                max-width: 70vw;
            }

            .llm-message-main-ai {
                width: calc(100% - 40px);
            }

            .llm-message-main-user {
                width: min(92%, 100%);
            }

            .llm-message-meta {
                white-space: normal;
            }
        }
    </style>
@endsection
