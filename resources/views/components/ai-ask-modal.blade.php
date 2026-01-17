<div class="modal fade" id="askAIModal" tabindex="-1" role="dialog" aria-labelledby="askAIModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="askAIModalLabel">AI对话助手</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <input type="hidden" id="refer_text_id" value="">
            <input type="hidden" id="replace_text_id" value="">
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
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
    color: white;
    font-size: 16px;
}

.message.user .avatar {
    background: linear-gradient(135deg, #ff9a9e, #fecfef);
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
    background: linear-gradient(to right, #667eea, #764ba2);
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
</style>

<script>
    // 存储聊天记录
    let chatMessages = [];

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
    function openAskAIModal($referTextId='', $replaceTextId='') {
        document.getElementById('refer_text_id').value = $referTextId;
        document.getElementById('replace_text_id').value = $replaceTextId;

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

            messageDiv.innerHTML = [
                '<div class="avatar">',
                '<i class="fa ' + (msg.role === 'user' ? 'fa-user' : 'fa-robot') + '"></i>',
                '</div>',
                '<div class="content">',
                '<div class="message-content">' + msg.content.replace(/\n/g, '<br>') + '</div>',
                $buttonHtml,
                '</div>'
            ].join('');

            chatContainer.appendChild(messageDiv);
        });

        // 滚动到底部
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // 处理AI问答
    function startAskAIProcess(referText, query) {
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
                query: query
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
</script>