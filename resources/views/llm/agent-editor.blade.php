@extends('layouts.app')


<script>
    // 等待 jQuery 加载完成后再执行
    // 定义全局变量
    let currentAgentId = {{ $agent->id }};
    let draftVersionModelId = {{ $draftVersion && isset($draftVersion->model_id) ? $draftVersion->model_id : 0 }};
    let agentName = '{{ addslashes($agent->name ?? "") }}';
    let agentDescription = '{{ addslashes($agent->description ?? "") }}';
    let agentAvatar = '{{ addslashes($agent->avatar ?? "") }}';
    let chatHistory;
    
    function initializePageScripts() {
        // 变量已在全局作用域定义
        
            
        chatHistory = localStorage.getItem('agent_chat_' + currentAgentId) ?
            JSON.parse(localStorage.getItem('agent_chat_' + currentAgentId)) : [];

        // 设置全局 AJAX 默认值，包括 CSRF 令牌
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 页面加载完成后初始化
        $(document).ready(function() {
            console.log('文档已准备就绪，开始加载模型列表');
            loadModels();
            updateCharCount();
            loadChatHistory();

            // 绑定事件
            $('#chat-message').on('input', function() {
                updateCharCount();
                toggleSendButton();
            });

            // 绑定滑块和数字输入框
            $('#temperature-range').on('input', function() {
                $('#temperature').val($(this).val());
            });
            $('#temperature').on('input', function() {
                $('#temperature-range').val($(this).val());
            });
            $('#top-p-range').on('input', function() {
                $('#top-p').val($(this).val());
            });
            $('#top-p').on('input', function() {
                $('#top-p-range').val($(this).val());
            });
            
            // 初始化折叠状态
            setTimeout(function() {
                if ($('#advancedParamsBody').length) {
                    // 检查内容高度，如果超过一定高度则保留折叠功能
                    const bodyHeight = $('#advancedParamsBody')[0].scrollHeight;
                    if (bodyHeight > 0) {
                        // 默认折叠内容
                        $('#advancedParamsBody').removeClass('show');
                        $('.collapse-card').addClass('collapsed');
                    }
                }
            }, 100); // 延迟执行确保DOM加载完成
        });
    }
        
    // 确保 jQuery 加载后再执行
    if (typeof $ !== 'undefined' && typeof jQuery !== 'undefined') {
        // 检查 DOM 是否已加载
        if (document.readyState === 'loading') {
            // DOM 仍在加载中，等待 DOMContentLoaded 事件
            document.addEventListener('DOMContentLoaded', function() {
                initializePageScripts();
            });
        } else {
            // DOM 已加载完成，直接执行
            initializePageScripts();
        }
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            // 再次检查 jQuery 是否可用
            if (typeof $ !== 'undefined' && typeof jQuery !== 'undefined') {
                initializePageScripts();
            } else {
                console.error('jQuery 未加载，请检查脚本引入顺序');
            }
        });
    }

    // 加载模型列表
    function loadModels() {
        console.log('正在请求模型列表...');
        console.log('draftVersionModelId:', draftVersionModelId);
        console.log('jQuery available:', typeof $ !== 'undefined');
        console.log('Document ready state:', document.readyState);
        
        $.get('/llm/api/models', function(response) {
            console.log('模型列表请求成功:', response);
            
            let modelSelect = $('#model-select');
            console.log('jQuery对象modelSelect:', modelSelect.length);
            console.log('DOM元素是否存在:', modelSelect.length > 0);
            
            if (modelSelect.length === 0) {
                console.error('错误：找不到ID为model-select的元素');
                return;
            }
            
            modelSelect.empty();
            console.log('下拉框已清空');

            // 检查响应结构并提取模型数据
            let data = response.result ? response.result.models : response;
            console.log('提取的模型数据:', data);

            if (!data || data.length === 0) {
                console.log('没有获取到模型数据');
                modelSelect.append('<option value="">暂无可用模型</option>');
                return;
            }

            console.log('开始遍历模型数据，总数:', data.length);
            data.forEach(function(model) {
                console.log('处理模型:', model);
                let selected = draftVersionModelId == model.id ? 'selected' : '';
                const providerName = model.provider ? model.provider.name : '未知供应商';
                const optionHtml = '<option value="' + model.id + '" ' + selected + '>' + model.name + ' (' + providerName + ')</option>';
                console.log('添加选项:', optionHtml);
                modelSelect.append(optionHtml);
            });
            console.log('模型选项添加完成');

            // 验证选项是否真的被添加了
            console.log('添加后的选项数量:', modelSelect.find('option').length);
            modelSelect.find('option').each(function(index, option) {
                console.log(`选项 ${index}:`, option.value, option.text, option.selected);
            });

            // 更新当前模型显示
            let selectedOption = modelSelect.find('option:selected');
            console.log('选中的选项数量:', selectedOption.length);
            if (selectedOption.length) {
                $('#current-model').text(selectedOption.text());
            }
            console.log('模型列表加载完成，总选项数:', modelSelect.find('option').length);
        }).fail(function(xhr, status, error) {
            console.error('模型列表请求失败:', error, xhr.responseText);
            console.error('XHR对象详情:', xhr.status, xhr.statusText);
            
            let modelSelect = $('#model-select');
            if (modelSelect.length > 0) {
                modelSelect.html('<option value="">加载失败，请刷新页面</option>');
            } else {
                console.error('无法找到model-select元素来显示错误信息');
            }
        });
    }

    // 保存草稿
    function saveDraft() {
        console.log('开始保存草稿...');
        if (!validateForm()) {
            console.log('表单验证失败');
            return;
        }

        let toolsConfig;
        try {
            toolsConfig = $('#tools-config').val().trim() ?
                JSON.parse($('#tools-config').val()) : null;
        } catch (e) {
            alert('工具配置 JSON 格式错误: ' + e.message);
            return;
        }

        const agentName = '{{ addslashes($agent->name ?? "") }}';
        const agentDescription = '{{ addslashes($agent->description ?? "") }}';
        const agentAvatar = '{{ addslashes($agent->avatar ?? "") }}';
                    
        const formData = {
            name: $('#agent-name').val().trim() || agentName,
            description: $('#agent-description').val().trim() || agentDescription,
            avatar: $('#agent-avatar').val().trim() || agentAvatar,
            model_id: $('#model-select').val(),
            system_prompt: $('#system-prompt').val(),
            temperature: parseFloat($('#temperature').val()),
            top_p: parseFloat($('#top-p').val()),
            max_tokens: parseInt($('#max-tokens').val()),
            context_length: parseInt($('#context-length').val()),
            tools_config: toolsConfig,
            is_public: $('#is-public').is(':checked') ? 1 : 0,
            is_active: $('#is-active').is(':checked') ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // 验证必填字段
        if (!formData.system_prompt) {
            alert('请填写系统提示词');
            return;
        }
        if (!formData.model_id) {
            alert('请选择 AI 模型');
            return;
        }

        console.log('发送草稿保存请求:', formData);

        const $saveBtn = $(this);
        const originalText = $saveBtn.html();
        $saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>保存中...');

        $.ajax({
            url: '/llm/api/llm-agents/' + currentAgentId + '/draft',
            method: 'PUT',
            data: formData,
            success: function(response) {
                console.log('草稿保存成功:', response);
                if (response.success) {
                    showToast('草稿已保存', 'success');
                    if (response.version) {
                        $('.alert-info strong').text('草稿 v' + response.version);
                    }
                } else {
                    showToast('保存失败: ' + response.message, 'danger');
                }
            },
            error: function(xhr) {
                console.error('草稿保存失败:', xhr.responseJSON?.error || '网络错误', xhr);
                showToast('保存失败: ' + (xhr.responseJSON?.error || '网络错误'), 'danger');
            },
            complete: function() {
                $saveBtn.prop('disabled', false).html(originalText);
            }
        });
    }

    // 发布草稿
    function publishDraft() {
        console.log('开始发布草稿...');
        if (!confirm('确定要发布此版本吗？发布后，所有用户将看到新版本的 Agent。')) {
            return;
        }

        const $publishBtn = $(this);
        const originalText = $publishBtn.html();
        $publishBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>发布中...');

        console.log('发送发布请求到:', '/llm/api/llm-agents/' + currentAgentId + '/publish');

        $.ajax({
            url: '/llm/api/llm-agents/' + currentAgentId + '/publish',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('发布请求成功:', response);
                if (response.success) {
                    showToast('已成功发布', 'success');
                    setTimeout(function() {
                        window.location.href = '{{ route("llm.agents.index") }}';
                    }, 1500);
                } else {
                    showToast('发布失败: ' + response.message, 'danger');
                }
            },
            error: function(xhr) {
                console.error('发布请求失败:', xhr.responseJSON?.error || '网络错误', xhr);
                showToast('发布失败: ' + (xhr.responseJSON?.error || '网络错误'), 'danger');
            },
            complete: function() {
                $publishBtn.prop('disabled', false).html(originalText);
            }
        });
    }

    // 发送测试消息
    function sendMessage() {
        console.log('开始发送测试消息...');
        const messageInput = $('#chat-message');
        const message = messageInput.val().trim();

        if (!message) {
            messageInput.focus();
            return;
        }

        // 添加用户消息到聊天窗口
        addToChat(message, 'user');
        messageInput.val('').trigger('input');

        // 禁用发送按钮，显示加载状态
        const $sendBtn = $('#send-btn');
        $sendBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 思考中');

        // 保存到本地历史
        saveChatToHistory(message, 'user');

        console.log('发送消息请求到:', '/llm/api/llm-agents/' + currentAgentId + '/test-chat', '消息内容:', message);

        // 发送请求到服务器
        $.ajax({
            url: '/llm/api/llm-agents/' + currentAgentId + '/test-chat',
            method: 'POST',
            data: {
                message: message,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('消息发送成功:', response);
                if (response.success) {
                    addToChat(response.response, 'bot');
                    saveChatToHistory(response.response, 'bot');
                } else {
                    const errorMsg = response.error || '未知错误';
                    addToChat('抱歉，我遇到了一些问题: ' + errorMsg, 'bot');
                }
            },
            error: function(xhr) {
                console.error('消息发送失败:', xhr.responseJSON?.error || '请检查网络连接', xhr);
                addToChat('网络错误: ' + (xhr.responseJSON?.error || '请检查网络连接'), 'bot');
            },
            complete: function() {
                $sendBtn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> 发送');
            }
        });
    }

    // 处理键盘事件
    function handleKeyDown(event) {
        if (event.ctrlKey && event.key === 'Enter') {
            event.preventDefault();
            sendMessage();
        }

        // 自动调整高度
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
        } else if (event.key === 'Enter' && event.shiftKey) {
            // 允许 Shift+Enter 换行
            return true;
        }
    }

    // 添加消息到聊天窗口
    function addToChat(message, sender) {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' +
            now.getMinutes().toString().padStart(2, '0');

        const senderClass = sender === 'user' ? 'user-message' : 'bot-message';
        const agentRealName = '{{ addslashes($agent->name ?? "") }}';
        const senderName = sender === 'user' ? '您' : agentRealName;
        const avatarBg = sender === 'user' ? 'bg-success' : 'bg-primary';

        const messageElement = '<div class="chat-message ' + senderClass + ' mb-3">' +
            '<div class="d-flex ' + (sender === 'user' ? 'flex-row-reverse' : '') + '">' +
                '<div class="flex-shrink-0">' +
                    '<div class="rounded-circle ' + avatarBg + ' text-white d-flex align-items-center justify-content-center"' +
                         'style="width: 32px; height: 32px; font-size: 0.8rem;">' +
                        (sender === 'user' ? '我' : 'AI') +
                    '</div>' +
                '</div>' +
                '<div class="flex-grow-1 ' + (sender === 'user' ? 'me-3 text-end' : 'ms-3') + '">' +
                    '<div class="fw-bold mb-1">' + senderName + '</div>' +
                    '<div class="p-3 ' + (sender === 'user' ? 'bg-primary text-white' : 'bg-white') + ' rounded shadow-sm">' +
                        (sender === 'user' ? message : formatBotResponse(message)) +
                    '</div>' +
                    '<small class="text-muted mt-1 d-block">' + timeString + '</small>' +
                '</div>' +
            '</div>' +
        '</div>';

        $('#chat-container').append(messageElement);

        // 滚动到底部
        const container = $('#chat-container')[0];
        container.scrollTop = container.scrollHeight;
    }

    // 格式化 AI 响应
    function formatBotResponse(text) {
        // 简单的 Markdown 转换
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>\$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>\$1</em>')
            .replace(/`(.*?)`/g, '<code class="bg-light px-1 rounded">\$1</code>')
            .replace(/\n/g, '<br>');
    }

    // 保存聊天记录到本地存储
    function saveChatToHistory(message, sender) {
        chatHistory.push({
            message: message,
            sender: sender,
            timestamp: new Date().toISOString()
        });

        // 限制历史记录数量
        if (chatHistory.length > 50) {
            chatHistory = chatHistory.slice(-50);
        }

        localStorage.setItem('agent_chat_' + currentAgentId, JSON.stringify(chatHistory));
    }

    // 加载聊天历史
    function loadChatHistory() {
        if (chatHistory.length > 0) {
            chatHistory.forEach(function(item) {
                addToChat(item.message, item.sender);
            });
        }
    }

    // 清空聊天
    function clearChat() {
        if (confirm('确定要清空对话记录吗？')) {
            $('#chat-container').empty();
            chatHistory = [];
            localStorage.removeItem('agent_chat_' + currentAgentId);

            // 添加初始欢迎消息
            const welcomeMsg = '您好！我是 {{ addslashes($agent->name ?? "") }}，我已经准备好为您服务。';
            addToChat(welcomeMsg, 'bot');
        }
    }

    // 加载示例消息
    function loadExampleMessage() {
        const examples = [
            "请介绍一下你自己",
            "你能帮我做什么？",
            "写一个简单的问候语",
            "用一句话总结今天的天气"
        ];
        const randomExample = examples[Math.floor(Math.random() * examples.length)];
        $('#chat-message').val(randomExample).trigger('input').focus();
    }

    // 验证 JSON 格式
    function validateJson() {
        const jsonText = $('#tools-config').val().trim();
        if (!jsonText) {
            $('#json-error').hide();
            showToast('工具配置为空', 'info');
            return;
        }

        try {
            const parsed = JSON.parse(jsonText);
            $('#json-error').hide();
            showToast('JSON 格式正确', 'success');
            // 美化 JSON
            $('#tools-config').val(JSON.stringify(parsed, null, 2));
        } catch (e) {
            $('#json-error').text('JSON 错误: ' + e.message).show();
            showToast('JSON 格式错误', 'danger');
        }
    }

    // 显示工具帮助
    function showToolsHelp() {
        $('#toolsHelpModal').modal('show');
    }

    // 格式化系统提示词
    function formatSystemPrompt() {
        const current = $('#system-prompt').val();
        const formatted = current
            .replace(/^(你是|你是一个|你是我的)?/i, '')
            .trim();

        if (formatted !== current) {
            $('#system-prompt').val(formatted);
            showToast('已格式化提示词', 'info');
        }
    }

    // 更新字符计数
    function updateCharCount() {
        const count = $('#chat-message').val().length;
        $('#char-count').text(count);
    }

    // 切换发送按钮状态
    function toggleSendButton() {
        const hasText = $('#chat-message').val().trim().length > 0;
        $('#send-btn').prop('disabled', !hasText);
    }

    // 显示 toast 提示
    function showToast(message, type = 'info') {
        // 简单的 toast 实现
        const toastId = 'toast-' + Date.now();
        const toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-white bg-' + type + ' border-0 position-fixed" ' +
             'style="bottom: 20px; right: 20px; z-index: 9999;">' +
            '<div class="d-flex">' +
                '<div class="toast-body">' +
                    message +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>' +
        '</div>';

        $('body').append(toastHtml);
        $('#' + toastId).toast({ delay: 3000 });
        $('#' + toastId).toast('show');

        setTimeout(function() {
            $('#' + toastId).remove();
        }, 3000);
    }

    // 表单验证
    function validateForm() {
        let isValid = true;

        $('.form-control:required:visible').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        return isValid;
    }
    
    // 折叠/展开功能
    function toggleCollapse(header) {
        const card = header.closest('.collapse-card');
        const body = card.querySelector('.card-body');
        const icon = header.querySelector('.collapse-icon');
        
        // 切换折叠状态
        $(body).collapse('toggle');
        
        // 添加/移除已折叠类以旋转图标
        card.classList.toggle('collapsed');
    }
    
    
</script>

<style>
    .chat-message {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .user-message .bg-primary {
        background: linear-gradient(135deg, #4CA1D7, #0F959D) !important;
    }

    .bot-message .bg-primary {
        background: linear-gradient(135deg, #6ba07d, #528c65) !important;
    }

    .font-monospace {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.875rem;
    }

    .toast {
        min-width: 250px;
    }
</style>

@section('content')

    <div class="container py-4">
        <!-- 页面标题和操作按钮 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h3 mb-0">
                            <i class="fa fa-robot text-primary mr-2"></i>
                            编辑 AI Agent: <span class="text-primary">{{ $agent->name }}</span>
                        </h2>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                            <i class="fa fa-save mr-1"></i>保存草稿
                        </button>
                        <button type="button" class="btn btn-success" onclick="publishDraft()">
                            <i class="fa fa-paper-plane mr-1"></i>发布版本
                        </button>
                        <a href="{{ route('llm.agents.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left mr-1"></i>返回列表
                        </a>
                    </div>
                </div>

                <!-- 版本状态提示 -->
                <div class="alert alert-info mt-3 p-2" style="background-color: #f0f7ff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>
                            <i class="fa fa-info-circle mr-1"></i>
                            当前编辑版本: <strong>草稿 v{{ $draftVersion->version ?? '新版本' }}</strong>
                            @if($agent->published_version)
                                | 已发布版本: <strong>v{{ $agent->published_version }}</strong>
                            @endif
                        </small>
                        <small class="text-muted">
                            最后更新: {{ $draftVersion->updated_at ?? '暂无' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 左侧编辑区域 -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-cog mr-2"></i>Agent 配置</h5>
                    </div>
                    <div class="card-body">
                        <form id="agent-form">
                            {{ csrf_field() }}
                            <input type="hidden" id="agent-id" value="{{ $agent->id }}">

                            <!-- Agent 基本信息 -->
{{--                            <div class="row mb-4">--}}
{{--                                <div class="col-md-8">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label for="agent-name" class="font-weight-bold">名称 <span class="text-danger">*</span></label>--}}
{{--                                        <input type="text" class="form-control" id="agent-name"--}}
{{--                                               value="{{ $agent->name }}" required>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-4">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label for="agent-avatar" class="font-weight-bold">头像 URL</label>--}}
{{--                                        <input type="text" class="form-control" id="agent-avatar"--}}
{{--                                               value="{{ $agent->avatar }}" placeholder="可选，支持图片链接">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="form-group mb-4">--}}
{{--                                <label for="agent-description" class="font-weight-bold">描述</label>--}}
{{--                                <textarea class="form-control" id="agent-description" rows="2"--}}
{{--                                          placeholder="简要描述此 Agent 的功能和用途">{{ $agent->description }}</textarea>--}}
{{--                            </div>--}}

{{--                            <hr class="my-4">--}}

                            <!-- 模型和系统提示词 -->
                            <div class="form-group mb-4">
                                <label for="model-select" class="font-weight-bold">AI 模型 <span class="text-danger">*</span></label>
                                <select class="form-control" id="model-select" required>
                                    <!-- 模型选项将通过JavaScript动态加载 -->
                                    <option value="">加载中...</option>
                                </select>
                                <small class="form-text text-muted">选择 Agent 使用的 AI 模型</small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="system-prompt" class="font-weight-bold">
                                    系统提示词 <span class="text-danger">*</span>
                                    <small class="text-muted ml-2">(定义 Agent 的角色和能力)</small>
                                </label>
                                <textarea class="form-control" id="system-prompt" rows="8"
                                          placeholder="例如：你是一个专业的写作助手，擅长..."
                                          required>{{ $draftVersion->system_prompt ?? '' }}</textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="form-text text-muted">约 {{ strlen($draftVersion->system_prompt ?? '') }} 字符</small>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="formatSystemPrompt()">
                                        <i class="fa fa-magic"></i> 格式化
                                    </button>
                                </div>
                            </div>

                            <!-- 参数配置 -->
                            <div class="card mb-4 border collapse-card">
                                <div class="card-header py-2 bg-light cursor-pointer" onclick="toggleCollapse(this)">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fa fa-sliders-h mr-2"></i>高级参数配置</h6>
                                        <i class="fa fa-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="card-body collapse" id="advancedParamsBody">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="temperature" class="font-weight-bold">Temperature</label>
                                                <input type="range" class="form-control-range" id="temperature-range"
                                                       min="0" max="2" step="0.01" value="{{ $draftVersion->temperature ?? 0.7 }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <input type="number" class="form-control form-control-sm w-50"
                                                           id="temperature" min="0" max="2" step="0.01"
                                                           value="{{ $draftVersion->temperature ?? 0.7 }}">
                                                    <small class="text-muted ml-2">控制随机性 (0-2)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="top-p" class="font-weight-bold">Top P</label>
                                                <input type="range" class="form-control-range" id="top-p-range"
                                                       min="0" max="1" step="0.01" value="{{ $draftVersion->top_p ?? 0.9 }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <input type="number" class="form-control form-control-sm w-50"
                                                           id="top-p" min="0" max="1" step="0.01"
                                                           value="{{ $draftVersion->top_p ?? 0.9 }}">
                                                    <small class="text-muted ml-2">核心采样 (0-1)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="max-tokens" class="font-weight-bold">Max Tokens</label>
                                                <input type="number" class="form-control" id="max-tokens"
                                                       min="1" max="32000" value="{{ $draftVersion->max_tokens ?? 2000 }}">
                                                <small class="form-text text-muted">最大生成长度</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="context-length" class="font-weight-bold">Context Length</label>
                                                <input type="number" class="form-control" id="context-length"
                                                       min="1" max="128000" value="{{ $draftVersion->context_length ?? 4000 }}">
                                                <small class="form-text text-muted">上下文窗口大小</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 工具配置 -->
                            <div class="form-group">
                                <label for="tools-config" class="font-weight-bold">
                                    工具配置
                                    <button type="button" class="btn btn-sm btn-link"
                                            onclick="showToolsHelp()">
                                        <i class="fa fa-question-circle"></i>
                                    </button>
                                </label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">JSON</span>
                                    </div>
                                    <textarea class="form-control font-monospace" id="tools-config" rows="4"
                                              placeholder='[{"name": "tool_name", "description": "功能描述", "parameters": {...}}]'>{{ $draftVersion->tools_config ? json_encode($draftVersion->tools_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '' }}</textarea>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="form-text text-muted">定义 Agent 可用的工具函数</small>
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            onclick="validateJson()">
                                        <i class="fa fa-check"></i> 验证 JSON
                                    </button>
                                </div>
                                <div id="json-error" class="text-danger small mt-1" style="display: none;"></div>
                            </div>

                            <!-- 可见性和状态 -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is-public"
                                                {{ $agent->is_public ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is-public">
                                            公开可见
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is-active"
                                                {{ $agent->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is-active">
                                            启用 Agent
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右侧测试区域 -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-comments mr-2"></i>实时测试</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="clearChat()">
                                <i class="fa fa-trash"></i> 清空对话
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ml-2"
                                    onclick="loadExampleMessage()">
                                <i class="fa fa-lightbulb"></i> 示例
                            </button>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column p-0">
                        <!-- 聊天区域 -->
                        <div id="chat-container" class="flex-grow-1 p-3"
                             style="height: 450px; overflow-y: auto; background-color: #f8f9fa;">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            AI
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-bold mb-1">{{ $agent->name }}</div>
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            您好！我是 <strong>{{ $agent->name }}</strong>，我已经准备好为您服务。
{{--                                            @if($agent->description)--}}
{{--                                                <div class="mt-2 text-muted small">{{ $agent->description }}</div>--}}
{{--                                            @endif--}}
                                            <div class="mt-2 small text-muted">
                                                <i class="fa fa-info-circle"></i> 您可以在这里测试我的功能和响应。
                                            </div>
                                        </div>
                                        <small class="text-muted mt-1 d-block">{{ now()->format('H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 输入区域 -->
                        <div class="border-top p-3 bg-white">
                            <div class="input-group">
                            <textarea class="form-control" id="chat-message" rows="1"
                                      placeholder="输入消息测试 Agent 功能 (按 Ctrl+Enter 发送)"
                                      onkeydown="handleKeyDown(event)"
                                      style="resize: none;"></textarea>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="sendMessage()"
                                            id="send-btn" disabled>
                                        <i class="fa fa-paper-plane"></i> 发送
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">
                                    <i class="fa fa-keyboard"></i> 当前模型: <span id="current-model">加载中...</span>
                                </small>
                                <small class="text-muted">
                                    <span id="char-count">0</span> 字符
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 工具配置帮助模态框 -->
    <div class="modal fade" id="toolsHelpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">工具配置帮助</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <pre class="bg-light p-3 rounded">
{
    "tools": [
        {
            "type": "function",
            "function": {
                "name": "get_weather",
                "description": "获取指定城市的天气信息",
                "parameters": {
                    "type": "object",
                    "properties": {
                        "location": {
                            "type": "string",
                            "description": "城市名称，如：北京"
                        },
                        "unit": {
                            "type": "string",
                            "enum": ["celsius", "fahrenheit"],
                            "description": "温度单位"
                        }
                    },
                    "required": ["location"]
                }
            }
        }
    ]
}</pre>
                    <p class="mt-3">配置工具后，Agent 可以根据需要使用这些功能。</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
@endsection
