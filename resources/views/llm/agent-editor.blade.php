@extends('layouts.app')

@section('content')
    <style>
        :root {
            --user-bubble: linear-gradient(135deg, #4CA1D7, #0F959D);
            --bot-bubble: #f8f9fa;
            --border-color: #e9ecef;
            --card-bg: #ffffff;
            --text-muted: #6c757d;
        }

        .chat-message {
            animation: fadeIn 0.3s ease-out;
            margin-bottom: 1rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            line-height: 1.5;
            word-break: break-word;
        }
        .user-message .message-bubble {
            background: var(--user-bubble);
            color: white;
            border-radius: 16px 16px 4px 16px;
        }
        .bot-message .message-bubble {
            background: var(--bot-bubble);
            color: #333;
            border: 1px solid var(--border-color);
            border-radius: 16px 16px 16px 4px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .collapse-card.collapsed .collapse-icon {
            transform: rotate(-90deg);
        }
        .collapse-icon {
            transition: transform 0.2s ease;
        }

        .font-monospace {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 0.875rem;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.375rem;
        }

        .btn-modern {
            border-radius: 6px;
            padding: 0.45rem 1rem;
            font-weight: 500;
        }

        .toast {
            min-width: 260px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-header {
            background-color: #fafafa;
            border-bottom: 1px solid var(--border-color);
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>

    <script>
        // 全局变量（由 Blade 注入）
        let currentAgentId = {{ $agent->id }};
        let draftVersionModelId = {{ $draftVersion && isset($draftVersion->model_id) ? $draftVersion->model_id : 0 }};
        let agentName = '{{ addslashes($agent->name ?? "") }}';
        let agentDescription = '{{ addslashes($agent->description ?? "") }}';
        let chatHistory;

        function initializePageScripts() {
            chatHistory = localStorage.getItem('agent_chat_' + currentAgentId)
                ? JSON.parse(localStorage.getItem('agent_chat_' + currentAgentId))
                : [];

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $(document).ready(function () {
                loadModels();
                updateCharCount();
                loadChatHistory();

                $('#chat-message').on('input', function () {
                    updateCharCount();
                    toggleSendButton();
                });

                // 双向绑定滑块与数字输入
                $('#temperature-range, #top-p-range').on('input', function () {
                    $('#' + this.id.replace('-range', '')).val(this.value);
                });
                $('#temperature, #top-p').on('input', function () {
                    $('#' + this.id + '-range').val(this.value);
                });

                // 初始化折叠面板
                setTimeout(() => {
                    if ($('#advancedParamsBody').length) {
                        $('#advancedParamsBody').removeClass('show');
                        $('.collapse-card').addClass('collapsed');
                    }
                }, 100);
            });
        }

        // 确保 jQuery 加载后执行
        if (typeof $ !== 'undefined') {
            document.readyState === 'loading'
                ? document.addEventListener('DOMContentLoaded', initializePageScripts)
                : initializePageScripts();
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof $ !== 'undefined') initializePageScripts();
                else console.error('jQuery 未加载');
            });
        }

        // ========== 功能函数 ==========
        function loadModels() {
            $.get('/llm/api/models')
                .done(response => {
                    const modelSelect = $('#model-select');
                    if (!modelSelect.length) return;

                    modelSelect.empty();
                    const data = response.result?.models || response;
                    if (!data || data.length === 0) {
                        modelSelect.append('<option>暂无可用模型</option>');
                        return;
                    }

                    data.forEach(model => {
                        const selected = draftVersionModelId == model.id ? 'selected' : '';
                        const provider = model.provider?.name || '未知供应商';
                        modelSelect.append(`<option value="${model.id}" ${selected}>${model.name} (${provider})</option>`);
                    });

                    const selectedText = modelSelect.find('option:selected').text();
                    $('#current-model').text(selectedText || '未选择');
                })
                .fail(xhr => {
                    $('#model-select').html('<option>加载失败</option>');
                    console.error('模型加载失败:', xhr);
                });
        }

        function saveDraft() {
            if (!validateForm()) return;

            let toolsConfig = null;
            const toolsVal = $('#tools-config').val().trim();
            if (toolsVal) {
                try {
                    toolsConfig = JSON.parse(toolsVal);
                } catch (e) {
                    showToast('工具配置 JSON 格式错误: ' + e.message, 'danger');
                    return;
                }
            }

            const formData = {
                name: agentName,
                description: agentDescription,
                model_id: $('#model-select').val(),
                system_prompt: $('#system-prompt').val().trim(),
                temperature: parseFloat($('#temperature').val()),
                top_p: parseFloat($('#top-p').val()),
                max_tokens: parseInt($('#max-tokens').val()),
                context_length: parseInt($('#context-length').val()),
                tools_config: toolsConfig,
                is_public: $('#is-public').is(':checked') ? 1 : 0,
                is_active: $('#is-active').is(':checked') ? 1 : 0
            };

            if (!formData.system_prompt) return showToast('请填写系统提示词', 'warning');
            if (!formData.model_id) return showToast('请选择 AI 模型', 'warning');

            const $btn = $(event.target);
            const orig = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>保存中...');

            $.ajax({
                url: `/llm/api/llm-agents/${currentAgentId}/draft`,
                method: 'PUT',
                data: formData,
                success: res => {
                    if (res.success) {
                        showToast('草稿已保存', 'success');
                        if (res.version) {
                            $('.version-info strong').text(`草稿 v${res.version}`);
                        }
                    } else {
                        showToast('保存失败: ' + res.message, 'danger');
                    }
                },
                error: xhr => {
                    showToast('保存失败: ' + (xhr.responseJSON?.error || '网络错误'), 'danger');
                },
                complete: () => $btn.html(orig).prop('disabled', false)
            });
        }

        function publishDraft() {
            if (!confirm('确定要发布此版本吗？发布后，所有用户将看到新版本的 Agent。')) return;

            const $btn = $(event.target);
            const orig = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>发布中...');

            $.post(`/llm/api/llm-agents/${currentAgentId}/publish`)
                .done(res => {
                    if (res.success) {
                        showToast('发布成功！', 'success');
                        setTimeout(() => window.location.href = '{{ route("llm.agents.index") }}', 1500);
                    } else {
                        showToast('发布失败: ' + res.message, 'danger');
                    }
                })
                .fail(xhr => {
                    showToast('发布失败: ' + (xhr.responseJSON?.error || '网络错误'), 'danger');
                })
                .always(() => $btn.html(orig).prop('disabled', false));
        }

        function sendMessage() {
            const msg = $('#chat-message').val().trim();
            if (!msg) return;

            addToChat(msg, 'user');
            $('#chat-message').val('').trigger('input');
            saveChatToHistory(msg, 'user');

            const $btn = $('#send-btn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 思考中');

            $.post(`/llm/api/llm-agents/${currentAgentId}/test-chat`, { message: msg })
                .done(res => {
                    if (res.success) {
                        addToChat(res.response, 'bot');
                        saveChatToHistory(res.response, 'bot');
                    } else {
                        addToChat('抱歉，我遇到了问题: ' + (res.error || '未知错误'), 'bot');
                    }
                })
                .fail(xhr => {
                    addToChat('网络错误: ' + (xhr.responseJSON?.error || '请检查网络'), 'bot');
                })
                .always(() => {
                    $btn.html('<i class="fa fa-paper-plane"></i> 发送').prop('disabled', !$('#chat-message').val().trim());
                });
        }

        function handleKeyDown(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
            if (e.key === 'Enter' && !e.shiftKey) e.preventDefault();
        }

        function addToChat(message, sender) {
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            const senderName = sender === 'user' ? '您' : agentName;
            const avatarText = sender === 'user' ? '我' : 'AI';
            const avatarClass = sender === 'user' ? 'bg-success' : 'bg-primary';

            const html = `
        <div class="chat-message ${sender}-message">
            <div class="d-flex ${sender === 'user' ? 'flex-row-reverse' : ''}">
                <div class="flex-shrink-0">
                    <div class="avatar rounded-circle ${avatarClass} text-white">${avatarText}</div>
                </div>
                <div class="flex-grow-1 ${sender === 'user' ? 'me-3 text-end' : 'ms-3'}">
                    <div class="fw-bold small mb-1">${senderName}</div>
                    <div class="message-bubble">${sender === 'user' ? message : formatBotResponse(message)}</div>
                    <small class="text-muted d-block mt-1">${timeStr}</small>
                </div>
            </div>
        </div>`;

            $('#chat-container').append(html);
            $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
        }

        function formatBotResponse(text) {
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code class="bg-light px-1 rounded">$1</code>')
                .replace(/\n/g, '<br>');
        }

        function saveChatToHistory(msg, sender) {
            chatHistory.push({ message: msg, sender, timestamp: new Date().toISOString() });
            if (chatHistory.length > 50) chatHistory = chatHistory.slice(-50);
            localStorage.setItem('agent_chat_' + currentAgentId, JSON.stringify(chatHistory));
        }

        function loadChatHistory() {
            if (chatHistory.length > 0) {
                chatHistory.forEach(item => addToChat(item.message, item.sender));
            }
        }

        function clearChat() {
            if (confirm('确定清空对话记录？')) {
                $('#chat-container').empty();
                chatHistory = [];
                localStorage.removeItem('agent_chat_' + currentAgentId);
                addToChat(`您好！我是 ${agentName}，已准备好为您服务。`, 'bot');
            }
        }

        function loadExampleMessage() {
            const examples = [
                "请介绍一下你自己",
                "你能帮我做什么？",
                "写一个简单的问候语",
                "用一句话总结今天的天气"
            ];
            const ex = examples[Math.floor(Math.random() * examples.length)];
            $('#chat-message').val(ex).trigger('input').focus();
        }

        function validateJson() {
            const val = $('#tools-config').val().trim();
            if (!val) {
                showToast('工具配置为空', 'info');
                return;
            }
            try {
                const parsed = JSON.parse(val);
                $('#tools-config').val(JSON.stringify(parsed, null, 2));
                showToast('JSON 格式正确，已美化', 'success');
            } catch (e) {
                showToast('JSON 错误: ' + e.message, 'danger');
            }
        }

        function showToolsHelp() {
            $('#toolsHelpModal').modal('show');
        }

        function formatSystemPrompt() {
            const current = $('#system-prompt').val();
            const formatted = current.replace(/^(你是|你是一个|你是我的)?/i, '').trim();
            if (formatted !== current) {
                $('#system-prompt').val(formatted);
                showToast('已格式化提示词', 'info');
            }
        }

        function updateCharCount() {
            $('#char-count').text($('#chat-message').val().length);
        }

        function toggleSendButton() {
            $('#send-btn').prop('disabled', !$('#chat-message').val().trim());
        }

        function showToast(message, type = 'info') {
            const id = 'toast-' + Date.now();
            const toast = `
        <div id="${id}" class="toast align-items-center text-white bg-${type} border-0 position-fixed"
             style="bottom: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;
            $('body').append(toast);
            $('#' + id).toast({ delay: 3000 }).toast('show');
            setTimeout(() => $('#' + id).remove(), 3100);
        }

        function validateForm() {
            let valid = true;
            $('.form-control[required]:visible').each(function () {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    valid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            return valid;
        }

        function toggleCollapse(el) {
            const card = el.closest('.collapse-card');
            const body = card.querySelector('.card-body');
            $(body).collapse('toggle');
            card.classList.toggle('collapsed');
        }
    </script>

    <div class="container py-4">
        <!-- 标题与操作 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">
                <i class="fa fa-robot text-primary me-2"></i>
                编辑 AI Agent: <span class="text-primary">{{ $agent->name }}</span>
            </h2>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-info btn-modern" onclick="saveDraft()">
                    <i class="fa fa-save me-1"></i>保存草稿
                </button>
                <button type="button" class="btn btn-outline-info btn-modern" onclick="publishDraft()">
                    <i class="fa fa-paper-plane me-1"></i>发布
                </button>
                <a href="{{ route('llm.agents.index') }}" class="btn btn-outline-info  btn-modern">
                    <i class="fa fa-arrow-left me-1"></i>返回
                </a>
            </div>
        </div>

        <!-- 版本信息 -->
{{--        <div class="alert alert-light border rounded mb-4 p-2 d-flex justify-content-between align-items-center">--}}
{{--            <div class="alert alert-light border rounded mb-4 p-2 d-flex justify-content-between align-items-center">--}}
{{--                <div>--}}
{{--                    <i class="fa fa-file-alt text-info me-1"></i>--}}
{{--                    当前编辑：<strong class="text-primary">草稿 v{{ $draftVersion->version ?? '新版本' }}</strong>--}}
{{--                    @if($agent->published_version)--}}
{{--                        · 已发布：<strong class="text-success">v{{ $agent->published_version }}</strong>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <small class="text-muted">--}}
{{--                    <i class="fa fa-clock-o me-1"></i>--}}
{{--                    {{ ($draftVersion && $draftVersion->updated_at) ? $draftVersion->updated_at->format('Y-m-d H:i') : '暂无' }}--}}
{{--                </small>--}}
{{--            </div>--}}
{{--        </div>--}}

        <div class="row g-4">
            <!-- 左侧：配置 -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-cog me-2"></i>Agent 配置</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4">
                            <label class="form-label">AI 模型 <span class="text-danger">*</span></label>
                            <select class="form-control" id="model-select" required>
                                <option>加载中...</option>
                            </select>
                            <small class="form-text text-muted">选择 Agent 使用的 AI 模型</small>
                        </div>

                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">
                                    系统提示词 <span class="text-danger">*</span>
                                    <small class="text-muted ms-2">(定义 Agent 的角色和能力)</small>
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatSystemPrompt()">
                                    <i class="fa fa-magic"></i> 格式化
                                </button>
                            </div>
                            <textarea class="form-control" id="system-prompt" rows="6" placeholder="例如：你是一个专业的写作助手..." required>{{ $draftVersion->system_prompt ?? '' }}</textarea>
                            <small class="form-text text-muted mt-1">约 {{ strlen($draftVersion->system_prompt ?? '') }} 字符</small>
                        </div>

                        <!-- 高级参数 -->
                        <div class="card mb-4 border collapse-card">
                            <div class="card-header py-2 cursor-pointer" onclick="toggleCollapse(this)">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fa fa-sliders-h me-2"></i>高级参数配置</h6>
                                    <i class="fa fa-chevron-down collapse-icon"></i>
                                </div>
                            </div>
                            <div class="card-body collapse" id="advancedParamsBody">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Temperature</label>
                                            <input type="range" class="form-control-range" id="temperature-range" min="0" max="2" step="0.01" value="{{ $draftVersion->temperature ?? 0.7 }}">
                                            <div class="d-flex align-items-center mt-1">
                                                <input type="number" class="form-control form-control-sm w-50" id="temperature" min="0" max="2" step="0.01" value="{{ $draftVersion->temperature ?? 0.7 }}">
                                                <small class="text-muted ms-2">随机性 (0–2)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Top P</label>
                                            <input type="range" class="form-control-range" id="top-p-range" min="0" max="1" step="0.01" value="{{ $draftVersion->top_p ?? 0.9 }}">
                                            <div class="d-flex align-items-center mt-1">
                                                <input type="number" class="form-control form-control-sm w-50" id="top-p" min="0" max="1" step="0.01" value="{{ $draftVersion->top_p ?? 0.9 }}">
                                                <small class="text-muted ms-2">核心采样 (0–1)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Max Tokens</label>
                                            <input type="number" class="form-control" id="max-tokens" min="1" max="32000" value="{{ $draftVersion->max_tokens ?? 2000 }}">
                                            <small class="form-text text-muted">最大生成长度</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Context Length</label>
                                            <input type="number" class="form-control" id="context-length" min="1" max="128000" value="{{ $draftVersion->context_length ?? 4000 }}">
                                            <small class="form-text text-muted">上下文窗口大小</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 工具配置 -->
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">
                                    工具配置
                                    <button type="button" class="btn btn-sm btn-link p-0 text-muted" onclick="showToolsHelp()" title="查看示例">
                                        <i class="fa fa-question-circle"></i>
                                    </button>
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="validateJson()">
                                    <i class="fa fa-check"></i> 验证并美化
                                </button>
                            </div>
                            <textarea class="form-control font-monospace" id="tools-config" rows="6" placeholder='[{"name": "tool_name", ...}]'>{{ $draftVersion->tools_config ? json_encode($draftVersion->tools_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '' }}</textarea>
                            <small class="form-text text-muted mt-1">Agent 可调用的函数工具（符合 OpenAI Function Calling 格式）</small>
                            <div id="json-error" class="text-danger small mt-1" style="display: none;"></div>
                        </div>

                        <!-- 状态开关 -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is-public" {{ $agent->is_public ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is-public">公开可见</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is-active" {{ $agent->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is-active">启用 Agent</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右侧：测试 -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-comments me-2"></i>实时测试</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearChat()">
                                <i class="fa fa-trash"></i> 清空
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="loadExampleMessage()">
                                <i class="fa fa-lightbulb"></i> 示例
                            </button>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column p-0">
                        <div id="chat-container" class="flex-grow-1 p-3" style="height: 450px; overflow-y: auto; background-color: #fbfcfd;">
                            <div class="chat-message bot-message">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar rounded-circle bg-primary text-white">AI</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-bold small mb-1">{{ $agent->name }}</div>
                                        <div class="message-bubble">
                                            您好！我是 <strong>{{ $agent->name }}</strong>，已准备好为您服务。
                                            <div class="mt-2 small text-muted">
                                                <i class="fa fa-info-circle"></i> 您可以在这里测试我的功能和响应。
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">{{ now()->format('H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-top p-3 bg-white">
                            <div class="input-group">
                            <textarea class="form-control" id="chat-message" rows="1"
                                      placeholder="输入消息测试 Agent 功能 (按 Ctrl+Enter 发送)"
                                      onkeydown="handleKeyDown(event)"
                                      style="resize: none;"></textarea>
                                <button class="btn btn-primary" type="button" onclick="sendMessage()" id="send-btn" disabled>
                                    <i class="fa fa-paper-plane"></i> 发送
                                </button>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">
                                    <i class="fa fa-keyboard me-1"></i> 当前模型: <span id="current-model">加载中...</span>
                                </small>
                                <small class="text-muted"><span id="char-count">0</span> 字符</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 工具帮助模态框 -->
    <div class="modal fade" id="toolsHelpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">工具配置帮助</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <pre class="bg-light p-3 rounded font-monospace" style="font-size: 0.85rem;">{
  "tools": [
    {
      "type": "function",
      "function": {
        "name": "get_weather",
        "description": "获取指定城市的天气信息",
        "parameters": {
          "type": "object",
          "properties": {
            "location": { "type": "string", "description": "城市名称，如：北京" },
            "unit": { "type": "string", "enum": ["celsius", "fahrenheit"] }
          },
          "required": ["location"]
        }
      }
    }
  ]
}</pre>
                    <p class="mt-3">配置后，Agent 可在对话中自动调用这些工具。</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>

@endsection