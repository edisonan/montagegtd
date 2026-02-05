<!-- AI助手对话模态框 -->
<div id="askAIModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- 修复：使用fixed和flex居中容器 -->
    <div class="fixed inset-0 overflow-y-auto py-4 sm:py-8 px-4">
        <!-- 修复：添加flex居中容器 -->
        <div class="min-h-full flex items-center justify-center">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl transform transition-all">
                <!-- 模态框头部 -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900" id="askAIModalLabel">AI对话助手</h3>
                    <div class="flex items-center space-x-2">
                        <button type="button" class="minimize-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                id="minimizeBtn" title="最小化">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                onclick="hideAIModal()" title="关闭">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- 隐藏字段 -->
                <input type="hidden" id="refer_text_id" value="">
                <input type="hidden" id="replace_text_id" value="">
                <input type="hidden" id="current_session_id" value="">

                <!-- 模态框内容 -->
                <div class="p-0">
                    <!-- 聊天容器 -->
                    <div id="chatContainer" class="chat-container h-[400px] overflow-y-auto p-4 bg-gray-50">
                        <!-- 聊天消息将在这里动态添加 -->
                        <div class="message message-assistant">
                            <div class="avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="content">
                                <p>你好！我是AI助手，请问有什么可以帮助你的？</p>
                            </div>
                        </div>
                    </div>

                    <!-- 快速模板区域 -->
                    <div class="px-4 py-3 border-t border-gray-200">
                        <div class="templates-container flex overflow-x-auto space-x-2 pb-2">
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('润色', '请帮我润色这段文字')">润色</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('总结', '请帮我总结这段文字')">总结</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('翻译', '请帮我翻译成英文')">翻译</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('扩写', '请帮我扩写这段内容')">扩写</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('简化', '请简化这段文字')">简化</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('改写', '请改写这段文字')">改写</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('检查', '请检查这段文字的语法错误')">检查</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('建议', '请给这段文字提供改进建议')">建议</button>
                            <button type="button" class="btn btn-sm btn-outline flex-shrink-0" onclick="setTemplate('日报', '请基于提供的内容，生成我的工作和生活日报，如：\n工作日报:\n1.2.3. \n生活日报:\n1.2.3')">日报</button>
                        </div>
                    </div>

                    <!-- 输入区域 -->
                    <div class="p-4 border-t border-gray-200">
                        <div class="flex items-end space-x-2">
                            <div class="flex-1">
                                <textarea class="input w-full resize-none min-h-[50px] max-h-[150px]"
                                          id="query"
                                          placeholder="输入消息..."></textarea>
                            </div>
                            <button type="button" class="btn btn-primary h-[50px] px-4" id="sendBtn" onclick="triggerAskAI()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="askAILoading" class="hidden text-center mt-2">
                            <div class="flex items-center justify-center space-x-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-color"></div>
                                <small class="text-gray-600">AI正在思考中...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 最小化后的悬浮窗 -->
<div id="floatingWindow" class="fixed bottom-6 right-6 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 hidden transform transition-all" style="transform: translate3d(0px, 0px, 0px);">
    <div class="floating-window-header flex items-center justify-between px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-t-lg cursor-move">
        <span class="font-medium text-sm">AI助手</span>
        <div class="flex items-center space-x-1">
            <button type="button" class="restore-btn p-1 hover:bg-white/20 rounded transition-colors"
                    id="restoreBtn" title="恢复">
                <i class="fas fa-window-restore text-xs"></i>
            </button>
        </div>
    </div>
    <div class="p-3">
        <p class="text-sm text-gray-600">点击恢复按钮继续对话</p>
    </div>
</div>

<style>
    .chat-container {
        display: flex;
        flex-direction: column;
    }

    .message {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
        max-width: 90%;
    }

    .message.user {
        justify-content: flex-end;
        margin-left: auto;
    }

    .message.assistant {
        justify-content: flex-start;
        margin-right: auto;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
        color: white;
        font-size: 16px;
    }

    .message.user .avatar {
        background: linear-gradient(135deg, #a0d8d6, #c0e9e5);
        margin-right: 0;
        margin-left: 12px;
    }

    .content {
        position: relative;
        padding: 12px 16px;
        border-radius: 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
        max-width: 80%;
    }

    .message.user .content {
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.assistant .content {
        background: #f0f4f8;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .message-content {
        margin-bottom: 5px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .copy-btn {
        position: absolute;
        bottom: 5px;
        right: 8px;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid #ddd;
        color: #666;
        cursor: pointer;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 3px;
        opacity: 0;
        transition: opacity 0.2s, background-color 0.2s, color 0.2s;
        z-index: 10;
        min-height: 18px;
    }

    .copy-btn:hover {
        color: #333;
        background-color: #f0f0f0;
        border-color: #999;
    }

    .content:hover .copy-btn {
        opacity: 1;
    }

    /* 当鼠标悬停在消息内容上时，始终显示复制按钮 */
    .content:focus-within .copy-btn {
        opacity: 1;
    }

    .templates-container {
        scrollbar-width: thin;
    }

    .templates-container::-webkit-scrollbar {
        height: 6px;
    }

    .templates-container::-webkit-scrollbar-thumb {
        background-color: #c0c0c0;
        border-radius: 3px;
    }

    .input-area {
        position: relative;
    }

    /* 滚动条样式 */
    .chat-container::-webkit-scrollbar {
        width: 6px;
    }

    .chat-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .chat-container::-webkit-scrollbar-thumb {
        background: #c0c0c0;
        border-radius: 3px;
    }

    .chat-container::-webkit-scrollbar-thumb:hover {
        background: #a0a0a0;
    }

    .floating-window-header {
        cursor: move;
        user-select: none;
    }

    /* Markdown 样式 */
    .markdown-body h1,
    .markdown-body h2,
    .markdown-body h3 {
        border-left: 4px solid #7aa2f7;
        padding-left: 10px;
        margin-top: 24px;
        font-weight: 600;
    }

    .markdown-body h1 { font-size: 1.5em; }
    .markdown-body h2 { font-size: 1.3em; }
    .markdown-body h3 { font-size: 1.1em; }

    .markdown-body blockquote {
        margin: 20px 0;
        padding: 12px 18px;
        background: #eef4ff;
        border-left: 4px solid #6b8df7;
        border-radius: 6px;
    }

    /* 增强代码块可读性 */
    .markdown-body pre code {
        display: block;
        padding: 14px;
        background: #f3f3f3;
        color: #333;
        border-radius: 6px;
        overflow-x: auto;
        line-height: 1.5;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 13px;
    }

    .markdown-body code:not(pre code) {
        background: #f0f0f0;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 13px;
    }

    .markdown-body table {
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0;
        font-size: 14px;
    }

    .markdown-body th,
    .markdown-body td {
        border: 1px solid #ddd;
        padding: 8px 12px;
    }

    .markdown-body th {
        background: #fafafa;
        font-weight: 600;
    }

    .markdown-body ul, .markdown-body ol {
        padding-left: 24px;
        margin: 12px 0;
    }

    .markdown-body li {
        margin: 4px 0;
    }

    .markdown-body a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .markdown-body a:hover {
        text-decoration: underline;
    }
</style>

<script>
    // 存储聊天记录
    let chatMessages = [];
    let isModalOpen = false;
    let isMinimized = false;

    // 模态框控制函数
    function showAIModal() {
        const modal = document.getElementById('askAIModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        isModalOpen = true;

        // 添加动画效果
        setTimeout(() => {
            const dialog = modal.querySelector('.bg-white');
            dialog.classList.add('scale-100', 'opacity-100');
            dialog.classList.remove('scale-95', 'opacity-0');
        }, 10);

        // 自动聚焦到输入框
        setTimeout(() => {
            document.getElementById('query').focus();
        }, 300);
    }

    function hideAIModal() {
        const modal = document.getElementById('askAIModal');
        const dialog = modal.querySelector('.bg-white');

        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            isModalOpen = false;
        }, 300);
    }

    function toggleAIModal() {
        if (isModalOpen) {
            hideAIModal();
        } else {
            showAIModal();
        }
    }

    // 初始化 Markdown 渲染器
    if (typeof marked !== 'undefined') {
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && window.hljs) {
                    return hljs.highlightAuto(code, [lang]).value;
                } else {
                    return hljs.highlightAuto(code).value;
                }
            },
            langPrefix: 'hljs language-',
        });
    }

    // 初始化当前会话ID
    let currentSessionId = null;

    // 创建新会话
    async function createNewSession(agentId = 'builtin_common') {
        try {
            // 获取CSRF令牌
            let csrfToken = '';
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            const inputToken = document.querySelector('input[name="_token"]');

            if (metaToken) {
                csrfToken = metaToken.getAttribute('content');
            } else if (inputToken) {
                csrfToken = inputToken.value;
            }

            const response = await fetch('/llm/sessions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    agent_id: agentId,
                    title: 'AI助手对话' // 默认标题
                })
            });

            const result = await response.json();

            if (result.success) {
                // 更新当前会话ID
                currentSessionId = result.data.id;
                document.getElementById('current_session_id').value = result.data.id;
                return result.data.id;
            } else {
                console.error('创建会话失败:', result.message);
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('创建会话时出错:', error);
            throw error;
        }
    }

    // AI助手对话框函数
    function openAskAIModal($referTextId='', $replaceTextId='', createNewSessionFlag=false) {
        document.getElementById('refer_text_id').value = $referTextId;
        document.getElementById('replace_text_id').value = $replaceTextId;

        // 如果指定了创建新会话，清空当前会话ID
        if(createNewSessionFlag) {
            document.getElementById('current_session_id').value = '';
            currentSessionId = null;
        }

        // 清空之前的聊天记录
        chatMessages = [];
        updateChatDisplay();

        // 设置默认提示
        document.getElementById('query').value = '';

        // 显示模态框
        showAIModal();
    }

    // 添加消息到聊天记录
    function addMessage(role, content) {
        const timestamp = new Date().toLocaleTimeString();
        chatMessages.push({role, content, timestamp});
        updateChatDisplay();
    }

    // 更新聊天显示
    function updateChatDisplay() {
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.innerHTML = '';

        chatMessages.forEach(msg => {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message message-' + (msg.role === 'user' ? 'user' : 'assistant');

            $buttonHtml = '<button class="copy-btn" type="button" onclick="copyMessage(this)">复制</button>';
            const replaceTextId = document.getElementById('replace_text_id').value;
            if(replaceTextId != ''){
                $buttonHtml = '<button class="copy-btn" type="button" onclick="replaceMessage(this)">替换</button>';
            }

            // 渲染 Markdown 内容
            let renderedContent = msg.content;
            if (typeof marked !== 'undefined') {
                renderedContent = marked.parse(msg.content);
            } else {
                renderedContent = msg.content.replace(/\n/g, '<br>');
            }

            messageDiv.innerHTML = [
                '<div class="avatar">',
                '<i class="fas ' + (msg.role === 'user' ? 'fa-user' : 'fa-robot') + '"></i>',
                '</div>',
                '<div class="content markdown-body">',
                '<div class="message-content">' + renderedContent + '</div>',
                $buttonHtml,
                '</div>'
            ].join('');

            chatContainer.appendChild(messageDiv);
        });

        // 滚动到底部
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // 处理AI问答
    async function startAskAIProcess(referText, query) {
        // 检查是否有当前会话ID，如果没有则创建一个
        const sessionInput = document.getElementById('current_session_id');
        let sessionId = sessionInput.value;

        if (!sessionId) {
            // 如果没有会话ID，则创建一个新会话
            try {
                sessionId = await createNewSession();
            } catch (error) {
                console.error('创建会话失败:', error);
                addMessage('assistant', '创建会话失败: ' + error.message);
                document.getElementById('askAILoading').style.display = 'none';
                return;
            }
        }

        // 添加用户消息到聊天记录
        addMessage('user', query);

        // 显示加载状态
        document.getElementById('askAILoading').classList.remove('hidden');

        // 获取CSRF令牌
        let csrfToken = '';
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        const inputToken = document.querySelector('input[name="_token"]');

        if (metaToken) {
            csrfToken = metaToken.getAttribute('content');
        } else if (inputToken) {
            csrfToken = inputToken.value;
        } else {
            csrfToken = '{{ csrf_token() }}';
        }

        // 发送请求到后端进行AI问答
        fetch('/llm/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                refer_text: referText,
                query: query,
                session_id: sessionId
            })
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.error || 'HTTP error! status: ' + response.status);
                    });
                }

                // 检查是否为流式响应
                if (response.headers.get('content-type')?.includes('text/event-stream')) {
                    // 添加一个空的AI消息占位符
                    addMessage('assistant', '');

                    // 处理流式响应
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';
                    let completeResult = '';

                    return new Promise((resolve, reject) => {
                        function readStream() {
                            reader.read().then(({done, value}) => {
                                if (done) {
                                    resolve(completeResult);
                                    return;
                                }

                                buffer += decoder.decode(value, {stream: true});

                                // 按行分割处理SSE响应
                                const lines = buffer.split('\n');
                                buffer = lines.pop(); // 保留不完整的行

                                for(const line of lines) {
                                    if(line.startsWith('data: ') && line !== 'data: [DONE]') {
                                        const dataStr = line.slice(6);
                                        try {
                                            const parsed = JSON.parse(dataStr);
                                            // 检查是否为流式响应格式
                                            if (parsed.choices && parsed.choices[0]) {
                                                const delta = parsed.choices[0].delta;
                                                let content = "";

                                                // 优先使用 reasoning 内容（针对OpenRouter格式）
                                                if (delta.reasoning && delta.reasoning.trim()) {
                                                    content = delta.reasoning;
                                                }
                                                // 其次使用 content 内容（针对OpenAI格式）
                                                else if (delta.content && delta.content.trim()) {
                                                    content = delta.content;
                                                }

                                                // 如果有内容则处理
                                                if (content) {
                                                    completeResult += content;
                                                    // 实时更新AI回复
                                                    if (chatMessages.length > 0 && chatMessages[chatMessages.length - 1].role === 'assistant') {
                                                        chatMessages[chatMessages.length - 1].content = completeResult;
                                                        updateChatDisplay();
                                                    }
                                                }
                                            }
                                            else if (parsed.usage) {
                                                continue; // 忽略usage信息
                                            }
                                            else {
                                                // 处理非标准格式
                                                if(dataStr.trim()) {
                                                    completeResult += dataStr;
                                                    if (chatMessages.length > 0 && chatMessages[chatMessages.length - 1].role === 'assistant') {
                                                        chatMessages[chatMessages.length - 1].content = completeResult;
                                                        updateChatDisplay();
                                                    }
                                                }
                                            }
                                        } catch (e) {
                                            console.error('Error parsing JSON:', e);
                                            if(dataStr.trim()) {
                                                if(dataStr.trim() !== '[DONE]' && dataStr.trim() !== '') {
                                                    completeResult += dataStr;
                                                    if (chatMessages.length > 0 && chatMessages[chatMessages.length - 1].role === 'assistant') {
                                                        chatMessages[chatMessages.length - 1].content = completeResult;
                                                        updateChatDisplay();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                readStream();
                            }).catch(reject);
                        }
                        readStream();
                    });
                } else {
                    return response.text();
                }
            })
            .then(data => {
                if (typeof data === 'string' && data) {
                    if (chatMessages.length === 0 || chatMessages[chatMessages.length - 1].role !== 'assistant') {
                        addMessage('assistant', data);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = error.name === 'ReferenceError' && error.message.includes('response')
                    ? 'AI回复失败: 网络请求错误，请检查网络连接'
                    : 'AI回复失败: ' + error.message +
                    '\n\n请检查：\n1. 是否已配置LLM提供商\n2. 是否有可用的模型\n3. 凭据是否有效';

                if (chatMessages.length === 0 || chatMessages[chatMessages.length - 1].role !== 'assistant') {
                    addMessage('assistant', errorMessage);
                }
            })
            .finally(() => {
                document.getElementById('askAILoading').classList.add('hidden');
            });
    }

    // 触发AI对话
    function triggerAskAI() {
        const queryInput = document.getElementById('query');
        const query = queryInput.value.trim();

        if (!query) {
            return;
        }

        const referTextId = document.getElementById('refer_text_id').value;
        let referText = "";

        if (referTextId != '') {
            const element = document.getElementById(referTextId);
            if (element) {
                referText = element.value !== undefined ? element.value :
                    (element.textContent || element.innerText || "");
            }
        }

        // 清空输入框
        queryInput.value = '';

        startAskAIProcess(referText, query);
    }

    // 快捷模板函数
    function setTemplate(name, text) {
        const queryInput = document.getElementById('query');
        queryInput.value = text;
        queryInput.focus();

        // 给用户反馈
        const btn = event.target;
        const originalClass = btn.className;
        btn.className = 'btn btn-sm btn-primary flex-shrink-0';
        setTimeout(() => {
            btn.className = originalClass;
        }, 300);
    }

    function useAskAIText() {
        if (chatMessages.length > 0) {
            const lastAssistantMsg = chatMessages.filter(msg => msg.role === 'assistant').pop();
            if (lastAssistantMsg && lastAssistantMsg.content.trim()) {
                const referTextId = document.getElementById('refer_text_id').value;
                if (referTextId != '') {
                    document.getElementById(referTextId).value = lastAssistantMsg.content;
                }
                hideAIModal();
            } else {
                alert('没有AI回复可以使用');
            }
        } else {
            alert('没有AI回复可以使用');
        }
    }

    function clearAskAIResult() {
        chatMessages = [];
        updateChatDisplay();
    }

    // 替换按钮
    function replaceMessage(button) {
        const replaceTextId = document.getElementById('replace_text_id').value;
        if(replaceTextId == ''){
            return;
        }

        const contentElement = button.previousElementSibling;
        const content = contentElement.innerText || contentElement.textContent;

        try {
            document.getElementById(replaceTextId).value = content;
            button.textContent = '已替换';
            setTimeout(() => {
                button.textContent = '替换';
            }, 2000);
        } catch (err) {
            console.error('无法替换文本: ', err);
            alert('替换失败，请手动选择文本');
        }
    }

    // 复制消息内容
    function copyMessage(button) {
        const contentElement = button.previousElementSibling;
        const content = contentElement.innerText || contentElement.textContent;

        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);

        textarea.select();
        textarea.setSelectionRange(0, 99999);

        try {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(content);
                const originalText = button.textContent;
                button.textContent = '已复制';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } else {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = '已复制';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }
        } catch (err) {
            console.error('无法复制文本: ', err);
            try {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = '已复制';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } catch (fallbackErr) {
                alert('复制失败，请手动选择文本');
            }
        }

        document.body.removeChild(textarea);
    }

    // 最小化和恢复功能
    document.addEventListener('DOMContentLoaded', function() {
        const minimizeBtn = document.getElementById('minimizeBtn');
        const restoreBtn = document.getElementById('restoreBtn');
        const floatingWindow = document.getElementById('floatingWindow');
        const modal = document.getElementById('askAIModal');

        // 最小化功能
        minimizeBtn.addEventListener('click', function(e) {
            e.stopPropagation();

            // 隐藏模态框
            hideAIModal();

            // 显示悬浮窗
            floatingWindow.classList.remove('hidden');

            isMinimized = true;
        });

        // 恢复功能
        restoreBtn.addEventListener('click', function(e) {
            e.stopPropagation();

            // 隐藏悬浮窗
            floatingWindow.classList.add('hidden');

            // 显示模态框
            showAIModal();

            isMinimized = false;
        });

        // 悬浮窗拖拽功能
        let isDragging = false;
        let startX, startY, initialX, initialY;

        const floatingHeader = floatingWindow.querySelector('.floating-window-header');

        floatingHeader.addEventListener('mousedown', dragStart);
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('mousemove', drag);

        function dragStart(e) {
            if (e.target === floatingHeader || e.target.closest('.floating-window-header')) {
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;

                const style = window.getComputedStyle(floatingWindow);
                const matrix = new WebKitCSSMatrix(style.transform);
                initialX = matrix.m41;
                initialY = matrix.m42;

                floatingHeader.style.cursor = 'grabbing';
                e.preventDefault();
            }
        }

        function dragEnd(e) {
            if (!isDragging) return;

            isDragging = false;
            floatingHeader.style.cursor = 'move';
        }

        function drag(e) {
            if (!isDragging) return;

            e.preventDefault();

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

            const newX = initialX + dx;
            const newY = initialY + dy;

            floatingWindow.style.transform = `translate3d(${newX}px, ${newY}px, 0)`;
        }

        // 监听回车键发送消息
        const queryInput = document.getElementById('query');

        queryInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                triggerAskAI();
            }
        });

        // 点击模态框外部关闭
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideAIModal();
            }
        });

        // ESC键关闭模态框
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isModalOpen && !isMinimized) {
                hideAIModal();
            }
        });
    });
</script>

<!-- 引入 Markdown 解析库 -->
<script src="/js/marked.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    // 初始化 Markdown 渲染器的高亮功能
    if (typeof marked !== 'undefined') {
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && window.hljs) {
                    return hljs.highlightAuto(code, [lang]).value;
                } else {
                    return hljs.highlightAuto(code).value;
                }
            },
            langPrefix: 'hljs language-',
        });
    }
</script>