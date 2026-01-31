<div class="modal fade" id="askAIModal" tabindex="-1" role="dialog" aria-labelledby="askAIModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="askAIModalLabel">AI对话助手</h5>
                <div class="header-controls">
                    <button type="button" class="minimize-btn" id="minimizeBtn" title="最小化">
                        <i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <input type="hidden" id="refer_text_id" value="">
            <input type="hidden" id="replace_text_id" value="">
            <input type="hidden" id="current_session_id" value="">
            <div class="modal-body p-0">
                <!-- 聊天容器 -->
                <div id="chatContainer" class="chat-container" style="height: 400px; overflow-y: auto; padding: 15px;">
                    <!-- 聊天消息将在这里动态添加 -->
                    <div class="message message-assistant">
                        <div class="avatar">
                            <i class="fa fa-robot"></i>
                        </div>
                        <div class="content">
                            <p>你好！我是AI助手，请问有什么可以帮助你的？</p>
                        </div>
                    </div>
                </div>
                
                <!-- 快速模板区域 -->
                <div class="px-3 py-2 border-top">
                    <div class="templates-container d-flex flex-nowrap overflow-auto" style="max-width: 100%; gap: 8px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('润色', '请帮我润色这段文字')">润色</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('总结', '请帮我总结这段文字')">总结</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('翻译', '请帮我翻译成英文')">翻译</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('扩写', '请帮我扩写这段内容')">扩写</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('简化', '请简化这段文字')">简化</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('改写', '请改写这段文字')">改写</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('检查', '请检查这段文字的语法错误')">检查</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('建议', '请给这段文字提供改进建议')">建议</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="setTemplate('日报', '请基于提供的内容，生成我的工作和生活日报，如：\n工作日报:\n1.2.3. \n生活日报:\n1.2.3')">日报</button>
                    </div>
                </div>
                
                <!-- 输入区域 -->
                <div class="input-area p-3 border-top">
                    <div class="input-group">
                        <textarea class="form-control" id="query" rows="1" placeholder="输入消息..." style="resize: none; height: 50px;"></textarea>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" id="sendBtn" onclick="triggerAskAI()">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                    <div id="askAILoading" class="text-center mt-2" style="display: none;">
                        <small>AI正在思考中...</small>
                        <div class="spinner-border spinner-border-sm ml-2" role="status">
                            <span class="sr-only">加载中...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 最小化后的悬浮窗 -->
<div id="floatingWindow" class="floating-window" style="display: none;">
    <div class="floating-window-header">
        <span class="floating-title">AI助手</span>
        <div class="floating-controls">
            <button type="button" class="restore-btn" id="restoreBtn" title="恢复">
                <i class="fa fa-window-restore"></i>
            </button>
        </div>
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
    background: linear-gradient(135deg, #17a2b8, #138a9e);
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
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.message-user .content {
    background: linear-gradient(to right, #17a2b8, #138a9e);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-assistant .content {
    background: #f0f4f8;
    color: #333;
    border-bottom-left-radius: 4px;
}

.message-content {
    margin-bottom: 5px;
}

/* 修改复制按钮样式 */
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

#query {
    min-height: 50px;
    max-height: 150px;
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

/* 最小化悬浮窗样式 */
.floating-window {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 200px;
    z-index: 9999;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.floating-window-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: linear-gradient(135deg, #17a2b8, #138a9e);
    color: white;
    border-radius: 8px 8px 0 0;
    cursor: move;
}

.floating-title {
    font-size: 14px;
    font-weight: bold;
}

.floating-controls {
    display: flex;
    gap: 5px;
}

.restore-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 2px;
    font-size: 12px;
}

.restore-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.header-controls {
    display: flex;
    gap: 5px;
}

.minimize-btn {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 2px;
    font-size: 16px;
    line-height: 1;
    margin-right: 10px;
}

.minimize-btn:hover {
    background: #f8f9fa;
    border-radius: 3px;
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
}

.markdown-body code {
    background: #f0f0f0;
    padding: 2px 4px;
    border-radius: 4px;
}

.markdown-body table {
    border-collapse: collapse;
    width: 100%;
    margin: 20px 0;
}

.markdown-body th,
.markdown-body td {
    border: 1px solid #ddd;
    padding: 8px;
}

.markdown-body th {
    background: #fafafa;
}
</style>

<script>
    // 存储聊天记录
    let chatMessages = [];

    // 初始化 Markdown 渲染器
    if (typeof marked !== 'undefined') {
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && window.hljs) {
                    return hljs.highlightAuto(code).value;
                } else {
                    return code;
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

    function setTemplate(name, text) {
        document.getElementById('query').value = text;
        document.getElementById('query').focus();

        // 给用户反馈
        const btn = event.target;
        const originalClass = btn.className;
        btn.className = 'btn btn-info btn-sm flex-shrink-0';
        setTimeout(() => {
            btn.className = originalClass;
        }, 300);
    }

    // AI问答功能
    function openAskAIModal($referTextId='', $replaceTextId='', createNewSession=false) {
        document.getElementById('refer_text_id').value = $referTextId;
        document.getElementById('replace_text_id').value = $replaceTextId;
        
        // 如果指定了创建新会话，清空当前会话ID
        if(createNewSession) {
            document.getElementById('current_session_id').value = '';
            currentSessionId = null;
        }

        // 清空之前的聊天记录
        chatMessages = [];
        updateChatDisplay();

        // 设置默认提示
        document.getElementById('query').value = '';

        $('#askAIModal').modal('show');

        // 自动聚焦到输入框
        setTimeout(() => {
            document.getElementById('query').focus();
        }, 300);
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
                '<i class="fa ' + (msg.role === 'user' ? 'fa-user' : 'fa-robot') + '"></i>',
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
        document.getElementById('askAILoading').style.display = 'block';

        // 获取CSRF令牌 - 修复可能找不到meta标签的问题
        let csrfToken = '';
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        const inputToken = document.querySelector('input[name="_token"]');

        if (metaToken) {
            csrfToken = metaToken.getAttribute('content');
        } else if (inputToken) {
            csrfToken = inputToken.value;
        } else {
            // 如果都没找到，尝试从页面其他地方获取
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
                                        const dataStr = line.slice(6); // 移除 'data: ' 前缀
                                        try {
                                            const parsed = JSON.parse(dataStr);
                                            // 检查是否为OpenAI格式的流式响应
                                            if (parsed.choices && parsed.choices[0]) {
                                                // 处理内容更新
                                                if (parsed.choices[0].delta && parsed.choices[0].delta.content) {
                                                    const content = parsed.choices[0].delta.content;
                                                    completeResult += content;
                                                    // 实时更新AI回复
                                                    // 更新最后一条AI消息
                                                    if (chatMessages.length > 0 && chatMessages[chatMessages.length - 1].role === 'assistant') {
                                                        chatMessages[chatMessages.length - 1].content = completeResult;
                                                        updateChatDisplay();
                                                    }
                                                }
                                                // 如果有finish_reason，说明响应结束，不需要特殊处理
                                            }
                                            // 如果存在usage字段，则跳过处理（这是一个统计信息，不是实际内容）
                                            else if (parsed.usage) {
                                                // 这是usage统计信息，不处理为内容
                                                continue;
                                            }
                                            else {
                                                // 处理简单文本响应
                                                if(dataStr.trim()) {
                                                    completeResult += dataStr;
                                                    // 更新最后一条AI消息
                                                    if (chatMessages.length > 0 && chatMessages[chatMessages.length - 1].role === 'assistant') {
                                                        chatMessages[chatMessages.length - 1].content = completeResult;
                                                        updateChatDisplay();
                                                    }
                                                }
                                            }
                                        } catch (e) {
                                            // 如果解析JSON失败，直接当作文本处理
                                            if(dataStr.trim()) {
                                                // 检查是否是[DONE]或仅包含空白字符
                                                if(dataStr.trim() !== '[DONE]' && dataStr.trim() !== '') {
                                                    completeResult += dataStr;
                                                    // 更新最后一条AI消息
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
                // 在非流式响应的情况下添加AI回复
                // 这里不能使用response.headers，因为在流式响应处理中我们已经添加了消息
                // 我们只需在非流式响应时添加消息
                if (typeof data === 'string' && data) {
                    // 检查最后一条消息是否是AI回复，避免重复
                    if (chatMessages.length === 0 || chatMessages[chatMessages.length - 1].role !== 'assistant') {
                        addMessage('assistant', data);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // 检查错误是否是"response is not defined"，如果是，则说明作用域问题
                const errorMessage = error.name === 'ReferenceError' && error.message.includes('response')
                    ? 'AI回复失败: 网络请求错误，请检查网络连接'
                    : 'AI回复失败: ' + error.message +
                      '\n\n请检查：\n1. 是否已配置LLM提供商\n2. 是否有可用的模型\n3. 凭据是否有效';

                // 检查最后一条消息是否是AI回复，避免重复
                if (chatMessages.length === 0 || chatMessages[chatMessages.length - 1].role !== 'assistant') {
                    addMessage('assistant', errorMessage);
                }
            })
            .finally(() => {
                document.getElementById('askAILoading').style.display = 'none';
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
                // 尝试获取value属性，如果没有则获取textContent
                referText = element.value !== undefined ? element.value :
                    (element.textContent || element.innerText || "");
            }
        }

        // 清空输入框
        queryInput.value = '';

        startAskAIProcess(referText, query);
    }

    // 监听回车键发送消息
    document.addEventListener('DOMContentLoaded', function() {
        const queryInput = document.getElementById('query');

        queryInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                triggerAskAI();
            }
        });
    });

    function useAskAIText() {
        if (chatMessages.length > 0) {
            const lastAssistantMsg = chatMessages.filter(msg => msg.role === 'assistant').pop();
            if (lastAssistantMsg && lastAssistantMsg.content.trim()) {
                const referTextId = document.getElementById('refer_text_id').value;
                if (referTextId != '') {
                    document.getElementById(referTextId).value = lastAssistantMsg.content;
                }
                $('#askAIModal').modal('hide');
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
        // 获取消息内容
        const contentElement = button.previousElementSibling; // 获取消息内容元素
        // 提取纯文本内容，而不是innerHTML，避免复制HTML标签
        const content = contentElement.innerText || contentElement.textContent;

        // 创建临时textarea用于复制
        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);

        // 选中文本
        textarea.select();
        textarea.setSelectionRange(0, 99999); // 移动设备兼容性

        try {
            document.getElementById(replaceTextId).value = content;
            button.textContent = '已替换';
            setTimeout(() => {
                button.textContent = originalText;
            }, 2000);
        } catch (err) {
            console.error('无法替换文本: ', err);
            // 降级方案：仍然尝试使用execCommand
            try {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = '已替换';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } catch (fallbackErr) {
                alert('替换失败，请手动选择文本');
            }
        }

        // 移除临时textarea
        document.body.removeChild(textarea);
    }

    // 复制消息内容
    function copyMessage(button) {
        // 获取消息内容
        const contentElement = button.previousElementSibling; // 获取消息内容元素
        // 提取纯文本内容，而不是innerHTML，避免复制HTML标签
        const content = contentElement.innerText || contentElement.textContent;

        // 创建临时textarea用于复制
        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);
        
        // 选中文本
        textarea.select();
        textarea.setSelectionRange(0, 99999); // 移动设备兼容性

        try {
            // 尝试使用现代Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(content);
                // 显示复制成功的提示
                const originalText = button.textContent;
                button.textContent = '已复制';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } else {
                // 回退到旧的document.execCommand方法
                document.execCommand('copy');
                // 显示复制成功的提示
                const originalText = button.textContent;
                button.textContent = '已复制';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }
        } catch (err) {
            console.error('无法复制文本: ', err);
            // 降级方案：仍然尝试使用execCommand
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

        // 移除临时textarea
        document.body.removeChild(textarea);
    }

    // 最小化和恢复功能
    document.addEventListener('DOMContentLoaded', function() {
        const minimizeBtn = document.getElementById('minimizeBtn');
        const restoreBtn = document.getElementById('restoreBtn');
        const modalDialog = document.querySelector('#askAIModal .modal-dialog');
        const floatingWindow = document.getElementById('floatingWindow');
        const modal = document.getElementById('askAIModal');
        
        let isMinimized = false;
        
        // 最小化功能
        minimizeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // 隐藏模态框
            $(modal).modal('hide');
            
            // 显示悬浮窗
            floatingWindow.style.display = 'block';
            
            isMinimized = true;
        });
        
        // 恢复功能
        restoreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // 隐藏悬浮窗
            floatingWindow.style.display = 'none';
            
            // 显示模态框
            $(modal).modal('show');
            
            isMinimized = false;
        });
        
        // 监听模态框隐藏事件，如果是因为最小化则不执行隐藏
        $('#askAIModal').on('hide.bs.modal', function (e) {
            if (isMinimized) {
                e.preventDefault();
                $(this).data('bs.modal')._isTransitioning = false;
            }
        });
        
        // 当模态框完全隐藏时，重置状态
        $('#askAIModal').on('hidden.bs.modal', function () {
            if (!isMinimized) {
                // 只有在非最小化状态下才真正关闭
                floatingWindow.style.display = 'none';
                isMinimized = false;
            }
        });
        
        // 当模态框显示时，确保悬浮窗隐藏
        $('#askAIModal').on('shown.bs.modal', function () {
            if (isMinimized) {
                isMinimized = false;
            }
            floatingWindow.style.display = 'none';
        });
        
        // 悬浮窗拖拽功能
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;
        
        const floatingHeader = floatingWindow.querySelector('.floating-window-header');
        
        floatingHeader.addEventListener('mousedown', dragStart);
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('mousemove', drag);
        
        function dragStart(e) {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            
            if (e.target === floatingHeader || e.target === document.querySelector('.floating-title') || e.target.classList.contains('restore-btn')) {
                isDragging = true;
            }
        }
        
        function dragEnd(e) {
            initialX = currentX;
            initialY = currentY;
            
            isDragging = false;
        }
        
        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
                
                xOffset = currentX;
                yOffset = currentY;
                
                setTranslate(currentX, currentY, floatingWindow);
            }
        }
        
        function setTranslate(xPos, yPos, el) {
            el.style.transform = "translate3d(" + xPos + "px, " + yPos + "px, 0)";
        }
    });
</script>

<!-- 引入 Markdown 解析库 -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
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