@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- 左侧会话列表 -->
        <div class="col-md-3 border-right d-flex flex-column" style="background-color: #f8f9fa; height: 100vh;">
            <div class="p-3 border-bottom bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">会话列表</h5>
                    <button id="new-session-btn" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            
            <!-- 会话列表 -->
            <div class="flex-grow-1 overflow-auto p-3" id="sessions-list">
                <!-- 会话列表将通过JavaScript加载 -->
                <div class="text-center text-muted py-5">
                    <i class="fa fa-spinner fa-spin"></i> 加载中...
                </div>
            </div>
        </div>
        
        <!-- 右侧聊天区域 -->
        <div class="col-md-9 d-flex flex-column" id="chat-container" style="height: 100vh;">
            <!-- 右侧顶部：智能体选择和会话操作 -->
            <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h5 id="session-title-display" class="mb-0 mr-3"></h5>
                </div>
                <div>
                    <button id="pin-session-btn" class="btn btn-outline-secondary btn-sm" style="display:none;" title="固定会话">
                        <i class="fa fa-thumb-tack"></i>
                    </button>
                    <button id="delete-session-btn" class="btn btn-outline-danger btn-sm ml-2" style="display:none;" title="删除会话">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <!-- 消息区域 -->
            <div class="flex-grow-1 overflow-auto p-3" id="messages-container" style="background-color: #f9f9f9;">
                <div id="initial-mode" class="d-flex flex-column h-100 justify-content-center align-items-center">
                    <div class="text-center mb-5">
                        <h3 class="mt-3">欢迎使用</h3>
                    </div>
                    
                    <div class="w-100">
                        <div class="form-group">
                            <label for="initial-question">请输入您的问题:</label>
                            <textarea class="form-control" id="initial-question" rows="4" placeholder="在这里输入您想问的问题..."></textarea>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <label for="agent-select" class="mr-2 mb-0">智能体:</label>
                            <div class="flex-grow-1 d-flex">
                                <select class="form-control" id="agent-select" style="min-width: 200px; max-width: 300px;">
                                    <option value="builtin_common">通用</option>
                                    <!-- 智能体选项将通过JavaScript加载 -->
                                </select>
                                <div class="dropdown ml-2">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="moreAgentsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="display: none;">
                                        更多智能体
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="moreAgentsDropdown" id="moreAgentsMenu">
                                        <!-- 额外的智能体选项将通过JavaScript加载 -->
                                    </div>
                                </div>
                            </div>
                            <button id="send-initial-btn" class="btn btn-primary ml-3">
                                <i class="fa fa-paper-plane"></i> 开始聊天
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="chat-mode" class="d-flex flex-column h-100 chat-mode-hidden">
                    <!-- 消息将通过JavaScript动态添加 -->
                    <div id="messages-list" class="d-flex flex-column flex-grow-1 pb-3 px-2" style="overflow-y: auto;"></div>
                    
                    <div class="mt-auto pt-3 bg-white border-top">
                        <div class="input-group">
                            <textarea class="form-control" id="message-input" rows="2" placeholder="输入消息..."></textarea>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="send-message-btn">
                                    <i class="fa fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">按 Enter 发送，Shift+Enter 换行</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 会话操作确认模态框 -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">确认操作</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirm-message">确定要执行此操作吗？</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">确定</button>
            </div>
        </div>
    </div>
</div>

<!-- 隐藏字段保存当前会话的agent_id -->
<input type="hidden" id="current-agent-id" value="">


<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded triggered'); // 调试日志
        let currentSessionId = null;
        let currentAgent = null;

        // 初始化页面
        console.log('About to call loadSessions and loadAgents'); // 调试日志
        loadSessions();
        loadAgents();

        // 加载会话列表
        async function loadSessions() {
            try {
                const response = await fetch('/llm/sessions', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const result = await response.json();

                if (result.success) {
                    displaySessions(result.data);
                } else {
                    console.error('加载会话失败:', result.message);
                    document.getElementById('sessions-list').innerHTML = '<div class="text-center text-danger py-5">加载失败</div>';
                }
            } catch (error) {
                console.error('请求错误:', error);
                document.getElementById('sessions-list').innerHTML = '<div class="text-center text-danger py-5">网络错误</div>';
            }
        }

        // 显示会话列表
        function displaySessions(sessions) {
            const container = document.getElementById('sessions-list');

            // 清空之前的内容
            container.innerHTML = '';

            if (sessions.length === 0) {
                container.appendChild(document.createElement('div').classList.add('text-center', 'text-muted', 'py-5') ||
                    (function() {
                        const div = document.createElement('div');
                        div.className = 'text-center text-muted py-5';
                        div.textContent = '暂无会话';
                        return div;
                    })()
                );
                return;
            }

            // 分组：固定的会话和普通会话
            const pinnedSessions = sessions.filter(session => session.is_pinned);
            const regularSessions = sessions.filter(session => !session.is_pinned);

            // 显示固定的会话
            if (pinnedSessions.length > 0) {
                const pinnedHeader = document.createElement('div');
                pinnedHeader.className = 'p-2 small text-muted border-bottom';
                pinnedHeader.textContent = '固定会话';
                pinnedHeader.style.backgroundColor = '#f1f3f5';
                container.appendChild(pinnedHeader);

                pinnedSessions.forEach(session => {
                    const sessionElement = createSessionElement(session);
                    container.appendChild(sessionElement);
                });
            }

            // 显示普通会话
            if (regularSessions.length > 0) {
                if (pinnedSessions.length > 0) {
                    const regularHeader = document.createElement('div');
                    regularHeader.className = 'p-2 small text-muted border-bottom';
                    regularHeader.textContent = '其他会话';
                    regularHeader.style.backgroundColor = '#f1f3f5';
                    container.appendChild(regularHeader);
                }

                regularSessions.forEach(session => {
                    const sessionElement = createSessionElement(session);
                    container.appendChild(sessionElement);
                });
            }
        }

        // 创建会话元素
        function createSessionElement(session) {
            const div = document.createElement('div');
            div.className = 'session-item p-3 cursor-pointer d-flex justify-content-between align-items-center mb-2';
            div.setAttribute('data-session-id', session.id);
            div.style.borderRadius = '5px';
            div.style.cursor = 'pointer';
            div.style.marginBottom = '5px';

            div.innerHTML = `
            <div class="session-info flex-grow-1">
                <div class="session-title" title="${session.title || '未命名会话'}" style="font-size: 14px; font-weight: 500;">${session.title || '未命名会话'}</div>
                <small class="text-muted" style="font-size: 12px;">${session.last_message_at || session.updated_at}</small>
            </div>
            <div class="session-actions">
                <button class="btn btn-sm btn-link pin-btn" onclick="togglePinSession(${session.id})" title="${session.is_pinned ? '取消固定' : '固定会话'}">
                    <i class="${session.is_pinned ? 'fas fa-thumbtack text-warning' : 'fas fa-thumbtack'}"></i>
                </button>
            </div>
        `;

            // 点击会话切换到聊天模式
            div.addEventListener('click', function() {
                switchToSession(session.id);
            });

            return div;
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
                const result = await response.json();

                if (result.success) {
                    currentSessionId = sessionId;
                    currentAgent = result.data.agent;

                    // 更新UI
                    document.getElementById('session-title-display').textContent = result.data.title;
                    document.getElementById('initial-mode').classList.add('initial-mode-hidden');
                    document.getElementById('chat-mode').classList.remove('chat-mode-hidden');
                    document.getElementById('pin-session-btn').style.display = 'inline-block';
                    document.getElementById('delete-session-btn').style.display = 'inline-block';

                    // 显示智能体名称（如果存在）
                    if (currentAgent) {
                        // 同时更新顶部的智能体选择框
                        document.getElementById('agent-select').value = currentAgent.id;
                        
                        // 保存当前会话的agent_id到隐藏字段
                        document.getElementById('current-agent-id').value = currentAgent.id;
                    } else {
                        // 如果会话没有关联智能体，清空隐藏字段
                        document.getElementById('current-agent-id').value = '';
                    }

                    // 设置固定按钮状态
                    const pinBtn = document.getElementById('pin-session-btn');
                    pinBtn.innerHTML = result.data.is_pinned ?
                        '<i class="fas fa-thumbtack text-warning"></i>' :
                        '<i class="fas fa-thumbtack"></i>';

                    // 清空当前消息列表
                    document.getElementById('messages-list').innerHTML = '';

                    // TODO: 在实际应用中，这里可以加载历史消息
                } else {
                    console.error('获取会话详情失败:', result.message);
                    alert('获取会话详情失败: ' + result.message);
                }
            } catch (error) {
                console.error('请求错误:', error);
                alert('网络请求失败');
            }
        }

        // 加载智能体列表
        async function loadAgents() {
            try {
                const response = await fetch('/llm/agents', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    console.error('HTTP错误:', response.status, response.statusText);
                    throw new Error(`HTTP错误: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    const select = document.getElementById('agent-select');
                    // 保留内置选项
                    select.innerHTML = '<option value="builtin_common">通用</option>';

                    result.data.forEach(agent => {
                        const option = document.createElement('option');
                        option.value = agent.id;
                        option.textContent = agent.name;
                        option.title = agent.description;
                        select.appendChild(option);
                    });
                } else {
                    console.error('加载智能体失败:', result.message);
                }
                
                // 处理智能体过多的情况
                handleTooManyAgents();
            } catch (error) {
                console.error('请求智能体列表失败:', error);
                // 即使出错也要显示错误信息
                const select = document.getElementById('agent-select');
                select.innerHTML = '<option value="">加载失败: ' + error.message + '</option>';
            }
        }
        
        // 处理智能体过多的情况
        function handleTooManyAgents() {
            const select = document.getElementById('agent-select');
            const options = select.querySelectorAll('option:not([value="builtin_common"])'); // 排除内置选项
            
            if (options.length > 5) { // 如果智能体超过5个，则启用更多下拉菜单
                const dropdownBtn = document.getElementById('moreAgentsDropdown');
                const dropdownMenu = document.getElementById('moreAgentsMenu');
                
                // 显示更多按钮
                dropdownBtn.style.display = 'block';
                
                // 清空下拉菜单
                dropdownMenu.innerHTML = '';
                
                // 将多余的智能体移到下拉菜单中
                const agentsToShow = 3; // 主下拉框显示3个智能体
                
                // 重新整理选项
                const allOptions = Array.from(select.querySelectorAll('option'));
                
                // 清空下拉框
                select.innerHTML = '<option value="builtin_common">通用</option>';
                
                // 添加前几个智能体到主下拉框
                for (let i = 1; i < allOptions.length && i <= agentsToShow; i++) {
                    select.appendChild(allOptions[i]);
                }
                
                // 将其余的智能体添加到下拉菜单中
                for (let i = agentsToShow + 1; i < allOptions.length; i++) {
                    const option = allOptions[i];
                    const menuItem = document.createElement('a');
                    menuItem.className = 'dropdown-item';
                    menuItem.href = '#';
                    menuItem.textContent = option.textContent;
                    menuItem.onclick = function(e) {
                        e.preventDefault();
                        select.value = option.value;
                        $(dropdownBtn).dropdown('toggle'); // 关闭下拉菜单
                    };
                    dropdownMenu.appendChild(menuItem);
                }
            } else {
                // 如果智能体不多，隐藏更多按钮
                document.getElementById('moreAgentsDropdown').style.display = 'none';
            }
        }

        // 创建新会话
        document.getElementById('new-session-btn').addEventListener('click', function() {
            document.getElementById('initial-mode').classList.remove('initial-mode-hidden');
            document.getElementById('chat-mode').classList.add('chat-mode-hidden');
            document.getElementById('session-title-display').textContent = '新建会话';
            document.getElementById('pin-session-btn').style.display = 'none';
            document.getElementById('delete-session-btn').style.display = 'none';
            document.getElementById('initial-question').value = '';
            currentSessionId = null;
            currentAgent = null;
        });

        // 开始聊天
        document.getElementById('send-initial-btn').addEventListener('click', async function() {
            const agentId = document.getElementById('agent-select').value;
            const question = document.getElementById('initial-question').value.trim();

            if (!question) {
                alert('请输入您的问题');
                return;
            }

            // 创建新会话
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
                        title: question.substring(0, 50) // 使用问题的前50个字符作为标题
                    })
                });
                const result = await response.json();

                if (result.success) {
                    currentSessionId = result.data.id;

                    // 切换到聊天模式
                    document.getElementById('initial-mode').classList.add('initial-mode-hidden');
                    document.getElementById('chat-mode').classList.remove('chat-mode-hidden');
                    document.getElementById('session-title-display').textContent = result.data.title;
                    document.getElementById('pin-session-btn').style.display = 'inline-block';
                    document.getElementById('delete-session-btn').style.display = 'inline-block';

                    // 显示智能体名称（如果存在）
                    if (agentId) {
                        const selectedAgent = Array.from(document.getElementById('agent-select').options)
                            .find(opt => opt.value == agentId);
                        if (selectedAgent) {
                        }
                    }

                    // 显示用户问题
                    addMessageToChat('user', question);

                    // 发送消息到AI
                    await sendMessageToAI(question, agentId);

                    // 重新加载会话列表
                    loadSessions();
                } else {
                    alert('创建会话失败: ' + result.message);
                }
            } catch (error) {
                console.error('请求错误:', error);
                alert('创建会话时发生错误');
            }
        });

        // 发送消息到AI
        async function sendMessageToAI(message, agentId = null) {
            try {
                // 显示AI正在思考的指示器
                const thinkingIndicator = showThinkingIndicator();

                // 如果没有明确传入agentId，尝试从当前会话的agent或隐藏字段获取
                let finalAgentId = agentId;
                if (!finalAgentId) {
                    // 优先使用参数传入的agentId，然后是当前选中的agentId，最后是会话关联的agentId
                    finalAgentId = document.getElementById('agent-select').value || document.getElementById('current-agent-id').value;
                }

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

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // 获取读取流
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                
                // 移除思考指示器
                removeThinkingIndicator(thinkingIndicator);
                
                // 创建一个新的AI消息气泡
                const aiMessageElement = document.createElement('div');
                aiMessageElement.className = 'ai-message';
                
                // 创建消息内容容器
                const messageContainer = document.createElement('div');
                messageContainer.className = 'd-flex';
                messageContainer.innerHTML = `
                    <div class="message-bubble">
                        <div class="ai-response-content"></div>
                    </div>
                `;
                
                aiMessageElement.appendChild(messageContainer);
                
                // 添加AI消息元素到聊天区域
                const messagesList = document.getElementById('messages-list');
                messagesList.appendChild(aiMessageElement);
                messagesList.scrollTop = messagesList.scrollHeight;
                
                // 获取内容显示元素
                const responseElement = messageContainer.querySelector('.ai-response-content');
                
                let accumulatedResponse = '';
                let streamingDone = false;
                let buffer = ''; // 用于存储不完整的数据块
                
                while (!streamingDone) {
                    const { done, value } = await reader.read();
                    if (done) {
                        streamingDone = true;
                        // 处理缓冲区中的最后剩余数据
                        if (buffer.trim()) {
                            await processChunk(buffer);
                        }
                        break;
                    }
                    
                    // 解码数据块
                    const chunk = decoder.decode(value, { stream: true });
                    
                    // 将新数据添加到缓冲区
                    buffer += chunk;
                    
                    // 按行分割数据块，因为SSE通常每行一个事件
                    const lines = buffer.split('\n');
                    
                    // 保留最后一个不完整的行到缓冲区
                    buffer = lines.pop() || '';
                    
                    // 处理完整的行
                    for (const line of lines) {
                        if (line.trim()) {
                            await processChunk(line);
                        }
                    }
                }
                
                // 流结束，确保滚动到底部
                messagesList.scrollTop = messagesList.scrollHeight;
                
                reader.releaseLock();
                
                // 处理单个数据块的辅助函数
                async function processChunk(chunkLine) {
                    if (chunkLine.startsWith('data: ')) {
                        try {
                            const data = chunkLine.slice(6); // 移除 'data: ' 前缀
                            if (data.trim()) {
                                const jsonData = JSON.parse(data);
                                
                                // 检查是否是包含内容的数据块
                                if (jsonData.choices && jsonData.choices[0] && jsonData.choices[0].delta) {
                                    const content = jsonData.choices[0].delta.content;
                                    if (content !== undefined && content !== null) { // 检查content是否存在
                                        accumulatedResponse += content;
                                        
                                        // 更新AI响应显示
                                        if (responseElement) {
                                            responseElement.innerHTML = accumulatedResponse;
                                            messagesList.scrollTop = messagesList.scrollHeight;
                                        }
                                    }
                                }
                                // 跳过处理usage数据块，根据经验教训内存
                                if (jsonData.usage) {
                                    return; // 直接返回，不处理usage数据
                                }
                            }
                        } catch (e) {
                            // 忽略解析错误，这可能是由于不完整的消息块造成的
                            console.warn('Error parsing JSON:', e, 'Data:', chunkLine);
                        }
                    }
                }
                
                // 流结束，确保滚动到底部
                messagesList.scrollTop = messagesList.scrollHeight;
                
                reader.releaseLock();
            } catch (error) {
                console.error('发送消息到AI失败:', error);

                // 移除思考指示器
                removeThinkingIndicator();

                addMessageToChat('ai', '抱歉，网络请求失败，请稍后重试。');
            }
        }

        // 显示AI思考指示器
        function showThinkingIndicator() {
            const messagesList = document.getElementById('messages-list');
            const indicatorDiv = document.createElement('div');
            indicatorDiv.className = 'ai-message ai-thinking-indicator mb-3';
            indicatorDiv.id = 'thinking-indicator';

            indicatorDiv.innerHTML = `
            <div class="d-flex">
                <div class="message-bubble">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;

            messagesList.appendChild(indicatorDiv);
            messagesList.scrollTop = messagesList.scrollHeight;

            return indicatorDiv;
        }

        // 移除思考指示器
        function removeThinkingIndicator(indicator) {
            if (indicator && indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
            } else {
                // 如果没有传入特定指示器，则移除所有思考指示器
                const indicators = document.querySelectorAll('.ai-thinking-indicator');
                indicators.forEach(ind => ind.remove());
            }
        }

        // 添加消息到聊天区域
        function addMessageToChat(sender, content) {
            const messagesList = document.getElementById('messages-list');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;

            if (sender === 'user') {
                messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble">
                        <div>${content}</div>
                    </div>
                </div>
            `;
            } else {
                messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble">
                        <div>${content}</div>
                    </div>
                </div>
            `;
            }

            messagesList.appendChild(messageDiv);
            messagesList.scrollTop = messagesList.scrollHeight;
        }

        // 发送消息事件
        document.getElementById('send-message-btn').addEventListener('click', async function() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();

            if (!message) return;

            if (!currentSessionId) {
                alert('请先选择一个会话');
                return;
            }

            // 清空输入框
            messageInput.value = '';

            // 显示用户消息
            addMessageToChat('user', message);

            // 发送消息到AI
            await sendMessageToAI(message, null); // 传递null，让函数内部处理agent_id
        });

        // 支持Shift+Enter换行，Ctrl+Enter发送
        document.getElementById('message-input').addEventListener('keydown', function(e) {
            // Ctrl+Enter 或 Cmd+Enter (Mac) 发送消息
            if ((e.key === 'Enter' && (e.ctrlKey || e.metaKey)) && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('send-message-btn').click();
            }
            // Shift+Enter 换行，不发送消息
            else if (e.key === 'Enter' && e.shiftKey) {
                // 让默认换行行为继续，不需要阻止事件
                // 不做任何事情，允许换行
            }
        });

        // 删除会话
        document.getElementById('delete-session-btn').addEventListener('click', function() {
            if (!currentSessionId) return;

            document.getElementById('confirm-message').textContent = '确定要删除这个会话吗？此操作不可撤销。';
            $('#confirmModal').modal('show');

            document.getElementById('confirm-delete-btn').onclick = async function() {
                try {
                    const response = await fetch(`/llm/sessions/${currentSessionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const result = await response.json();

                    if (result.success) {
                        $('#confirmModal').modal('hide');

                        // 重置到初始状态
                        document.getElementById('initial-mode').classList.remove('initial-mode-hidden');
                        document.getElementById('chat-mode').classList.add('chat-mode-hidden');
                        document.getElementById('session-title-display').textContent = '请选择一个会话开始聊天';
                        document.getElementById('pin-session-btn').style.display = 'none';
                        document.getElementById('delete-session-btn').style.display = 'none';
                        document.getElementById('messages-list').innerHTML = '';
                        currentSessionId = null;
                        currentAgent = null;

                        // 重新加载会话列表
                        loadSessions();
                    } else {
                        alert('删除会话失败: ' + result.message);
                    }
                } catch (error) {
                    console.error('请求错误:', error);
                    alert('删除会话时发生错误');
                }
            };
        });

        // 固定/取消固定会话
        document.getElementById('pin-session-btn').addEventListener('click', async function() {
            if (!currentSessionId) return;

            try {
                const response = await fetch(`/llm/sessions/${currentSessionId}/toggle-pin`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const result = await response.json();

                if (result.success) {
                    // 更新按钮状态
                    const pinBtn = document.getElementById('pin-session-btn');
                    pinBtn.innerHTML = result.data.is_pinned ?
                        '<i class="fas fa-thumbtack text-warning"></i>' :
                        '<i class="fas fa-thumbtack"></i>';

                    // 重新加载会话列表
                    loadSessions();
                } else {
                    alert('操作失败: ' + result.message);
                }
            } catch (error) {
                console.error('请求错误:', error);
                alert('操作时发生错误');
            }
        });

        // 当智能体选择改变时，如果当前没有会话，更新显示
        document.getElementById('agent-select').addEventListener('change', function() {
            if (!currentSessionId) {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                } else {
                }
            }
        });
    });

    // 全局函数供HTML调用
    async function togglePinSession(sessionId) {
        try {
            const response = await fetch(`/llm/sessions/${sessionId}/toggle-pin`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const result = await response.json();

            if (result.success) {
                // 重新加载会话列表
                loadSessions();
            } else {
                alert('操作失败: ' + result.message);
            }
        } catch (error) {
            console.error('请求错误:', error);
            alert('操作时发生错误');
        }
    }
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}

.session-item:hover {
    background-color: #e9ecef;
}

.user-message {
    margin-bottom: 1rem;
}

.user-message .message-bubble {
    background-color: #007bff;
    color: white;
    border-radius: 18px 18px 4px 18px;
    padding: 12px 16px;
    max-width: 80%;
    margin-left: auto;
    text-align: left;
}

.ai-message {
    margin-bottom: 1rem;
}

.ai-message .message-bubble {
    background-color: white;
    color: #333;
    border-radius: 18px 18px 18px 4px;
    padding: 12px 16px;
    max-width: 80%;
    margin-right: auto;
    text-align: left;
    border: 1px solid #e0e0e0;
}

.message-bubble {
    max-height: 400px;
    overflow-y: auto;
}

.message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#messages-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    overflow: hidden;
}

#messages-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

#messages-container .bg-white.border-top {
    margin-top: auto;
    flex-shrink: 0;
}

#sessions-list {
    height: calc(100vh - 100px);
    overflow-y: auto;
}

.chat-mode-hidden {
    display: none !important;
}

.initial-mode-hidden {
    display: none !important;
}

.session-info .session-title {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.session-info small {
    font-size: 0.75rem;
}

.typing-indicator {
    display: inline-block;
}
.typing-indicator span {
    height: 8px;
    width: 8px;
    float: left;
    margin: 0 2px;
    background-color: #9E9EA1;
    display: block;
    border-radius: 50%;
    opacity: 0.4;
}
.typing-indicator span:nth-of-type(1) {
    animation: typing 1s infinite;
}
.typing-indicator span:nth-of-type(2) {
    animation: typing 1s infinite 0.2s;
}
.typing-indicator span:nth-of-type(3) {
    animation: typing 1s infinite 0.4s;
}
@keyframes typing {
    0%, 100% {
        transform: translateY(0px);
        opacity: 0.4;
    }
    50% {
        transform: translateY(-5px);
        opacity: 1;
    }
}

/* 自定义滚动条样式 */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* 智能体选择器样式 */
.agent-selector-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.agent-selector-container .form-control {
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}

.agent-selector-container .btn {
    white-space: nowrap;
}
</style>
@endsection
