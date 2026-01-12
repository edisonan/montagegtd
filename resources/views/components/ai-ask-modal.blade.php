<div class="modal-body d-flex flex-column" style="height: 60vh;">
    <!-- 聊天消息区域 -->
    <div id="chatMessages" class="flex-grow-1 overflow-auto mb-3" style="display: flex; flex-direction: column; gap: 12px;">
        <!-- 示例消息（可选） -->
        <!-- <div class="d-flex justify-content-start"><div class="bg-light p-2 rounded">你好！我是你的 AI 助手。</div></div> -->
    </div>

    <!-- 快速模板（可折叠） -->
    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleTemplates">
            <i class="fas fa-caret-down"></i> 快速模板
        </button>
        <div id="templateButtons" class="mt-1 d-none" style="display: none; gap: 4px;">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('润色', '请帮我润色这段文字')">润色</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('总结', '请帮我总结这段文字')">总结</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('翻译', '请帮我翻译成英文')">翻译</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('扩写', '请帮我扩写这段内容')">扩写</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('简化', '请简化这段文字')">简化</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('改写', '请改写这段文字')">改写</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('检查', '请检查这段文字的语法错误')">检查</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTemplate('建议', '请给这段文字提供改进建议')">建议</button>
        </div>
    </div>

    <!-- 输入区域 -->
    <div class="input-group">
        <input type="text" class="form-control" id="query" placeholder="请输入你的问题..." onkeydown="if(event.key==='Enter') triggerAskAI()">
        <div class="input-group-append">
            <button class="btn btn-primary" type="button" onclick="triggerAskAI()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .chat-user {
        background-color: #d1e7ff;
        align-self: flex-end;
        border-radius: 12px 0 12px 12px;
        padding: 8px 12px;
        max-width: 80%;
    }
    .chat-ai {
        background-color: #f1f1f1;
        align-self: flex-start;
        border-radius: 0 12px 12px 12px;
        padding: 8px 12px;
        max-width: 80%;
    }
    #chatMessages {
        scroll-behavior: smooth;
    }
</style>

<script>
    // 切换模板显示/隐藏
    document.getElementById('toggleTemplates').addEventListener('click', function() {
        const tpl = document.getElementById('templateButtons');
        if (tpl.classList.contains('d-none')) {
            tpl.classList.remove('d-none');
            this.innerHTML = '<i class="fas fa-caret-up"></i> 快速模板';
        } else {
            tpl.classList.add('d-none');
            this.innerHTML = '<i class="fas fa-caret-down"></i> 快速模板';
        }
    });

    function setTemplate(name, text) {
        document.getElementById('query').value = text;
        // 视觉反馈
        const btn = event.target;
        const originalClass = btn.className;
        btn.className = 'btn btn-info btn-sm';
        setTimeout(() => btn.className = originalClass, 300);
    }

    // 添加消息到聊天区
    function addMessage(text, isUser = false) {
        const messagesDiv = document.getElementById('chatMessages');
        const msgDiv = document.createElement('div');
        msgDiv.className = `d-flex ${isUser ? 'justify-content-end' : 'justify-content-start'}`;
        const bubble = document.createElement('div');
        bubble.className = isUser ? 'chat-user' : 'chat-ai';
        bubble.textContent = text;
        msgDiv.appendChild(bubble);
        messagesDiv.appendChild(msgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight; // 自动滚动到底部
    }

    // 清空聊天（可选）
    function clearChat() {
        document.getElementById('chatMessages').innerHTML = '';
    }

    // 发送请求并处理流式响应（兼容 SSE / 普通文本）
    async function triggerAskAI() {
        const input = document.getElementById('query');
        const query = input.value.trim();
        if (!query) return;

        // 添加用户消息
        addMessage(query, true);
        input.value = '';

        // 获取 referText（如有）
        const referTextId = document.getElementById('refer_text_id')?.value || '';
        let referText = '';
        if (referTextId) {
            const el = document.getElementById(referTextId);
            referText = el?.value ?? (el?.textContent || '');
        }

        // 显示加载中的 AI 消息（临时占位）
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'd-flex justify-content-start';
        loadingMsg.innerHTML = '<div class="chat-ai"><i class="fas fa-spinner fa-spin"></i> 思考中...</div>';
        document.getElementById('chatMessages').appendChild(loadingMsg);
        document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value ||
                '{{ csrf_token() }}';

            const response = await fetch('/llm/ask-ai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ referText, query })
            });

            if (!response.ok) {
                const err = await response.json().catch(() => ({}));
                throw new Error(err.error || `HTTP ${response.status}`);
            }

            // 移除 loading 占位
            loadingMsg.remove();

            if (response.headers.get('content-type')?.includes('text/event-stream')) {
                // 流式处理
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                let aiMessageDiv = document.createElement('div');
                aiMessageDiv.className = 'd-flex justify-content-start';
                const bubble = document.createElement('div');
                bubble.className = 'chat-ai';
                bubble.textContent = ''; // 初始为空
                aiMessageDiv.appendChild(bubble);
                document.getElementById('chatMessages').appendChild(aiMessageDiv);

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    for (const line of lines) {
                        if (line.startsWith('data: ') && line !== 'data: [DONE]') {
                            const dataStr = line.slice(6).trim();
                            if (!dataStr) continue;

                            let content = '';
                            try {
                                const parsed = JSON.parse(dataStr);
                                // OpenAI 格式
                                if (parsed.choices?.[0]?.delta?.content) {
                                    content = parsed.choices[0].delta.content;
                                } else {
                                    content = dataStr; // fallback
                                }
                            } catch (e) {
                                content = dataStr; // 直接当文本
                            }

                            if (content) {
                                bubble.textContent += content;
                                document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
                            }
                        }
                    }
                }
            } else {
                // 非流式：一次性返回
                const result = await response.text();
                addMessage(result, false);
            }
        } catch (error) {
            console.error('AI 请求失败:', error);
            addMessage(`❌ 错误：${error.message}`, false);
        }
    }

    // 保留原 openAskAIModal，但清空聊天
    function openAskAIModal(referTextId = '') {
        document.getElementById('refer_text_id').value = referTextId;
        document.getElementById('query').value = '请帮我润色这段文字';
        clearChat(); // 每次打开新对话
        $('#askAIModal').modal('show');
    }
</script>