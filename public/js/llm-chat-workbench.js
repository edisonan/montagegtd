(function () {
    'use strict';

    window.__LLM_CHAT_ENHANCED__ = true;

    var state = {
        sessions: [],
        agents: [],
        session: null,
        agentId: '',
        filter: 'all',
        streaming: false,
        controller: null,
        generationId: null,
        stopRequested: false,
        stopFallbackTimer: null,
        stickToBottom: true,
        lastFailedText: '',
        attachments: [],
        uploadingAttachments: 0
    };

    var $ = function (id) { return document.getElementById(id); };
    var text = function (value) { return String(value == null ? '' : value); };
    var escapeHtml = function (value) {
        return text(value).replace(/[&<>"']/g, function (character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
        });
    };
    var api = function (url, options) {
        options = options || {};
        var csrf = document.querySelector('meta[name="csrf-token"]');
        options.headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf ? csrf.content : ''
        }, options.headers || {});
        return window.taskApiFetch(url, options);
    };
    var payload = function (response) {
        if (!response) return {};
        if (response.data !== undefined) return response.data;
        if (response.result !== undefined) return response.result;
        return {};
    };

    function setStatus(label, mode) {
        $('topbarContext').textContent = label;
        $('aiWorkbench').classList.toggle('is-generating', mode === 'generating');
        $('aiWorkbench').classList.toggle('is-stopping', mode === 'stopping');
    }

    function markdown(value) {
        var raw = escapeHtml(value);
        try {
            if (window.marked && typeof window.marked.parse === 'function') {
                raw = window.marked.parse(text(value), {gfm: true, breaks: true});
            }
            if (window.DOMPurify) {
                return window.DOMPurify.sanitize(raw);
            }
        } catch (error) {
            console.error('markdown render failed', error);
        }
        return raw.replace(/\n/g, '<br>');
    }

    function enhanceCodeBlocks(scope) {
        Array.prototype.forEach.call(scope.querySelectorAll('pre'), function (pre) {
            if (pre.querySelector('.ai-code-copy')) return;
            var code = pre.querySelector('code');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'ai-code-copy';
            button.innerHTML = '<i class="far fa-copy"></i><span>复制代码</span>';
            button.addEventListener('click', function () {
                copyText(code ? code.innerText : pre.innerText).then(function () {
                    button.innerHTML = '<i class="fas fa-check"></i><span>已复制</span>';
                    setTimeout(function () {
                        button.innerHTML = '<i class="far fa-copy"></i><span>复制代码</span>';
                    }, 1400);
                });
            });
            pre.appendChild(button);
        });
    }

    function copyText(value) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text(value));
        }
        return new Promise(function (resolve) {
            var area = document.createElement('textarea');
            area.value = text(value);
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            area.remove();
            resolve();
        });
    }

    function formatTime(value) {
        if (!value) return '';
        var date = new Date(text(value).replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';
        if (date.toDateString() === new Date().toDateString()) {
            return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        }
        return (date.getMonth() + 1) + '/' + date.getDate();
    }

    function sessionGroup(value) {
        var date = new Date(text(value || '').replace(' ', 'T'));
        if (isNaN(date.getTime())) return '更早';
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var target = new Date(date);
        target.setHours(0, 0, 0, 0);
        var days = Math.round((today.getTime() - target.getTime()) / 86400000);
        if (days <= 0) return '今天';
        if (days === 1) return '昨天';
        if (days <= 7) return '过去 7 天';
        if (days <= 30) return '过去 30 天';
        return '更早';
    }

    function agentById(id) {
        return state.agents.find(function (agent) { return String(agent.id) === String(id); }) || null;
    }

    function agentName(id) {
        var agent = agentById(id);
        return agent ? agent.name : '通用助手';
    }

    function defaultAgentId() {
        var preferred = state.agents.find(function (agent) { return agent.builtin_slug === 'builtin_common'; }) || state.agents[0];
        return preferred ? String(preferred.id) : 'builtin_common';
    }

    function agentsFrom(response) {
        var data = payload(response);
        return Array.isArray(data) ? data : (data.agents || []);
    }

    function syncAgentSelects() {
        var options = '<option value="">选择智能体</option>' + state.agents.map(function (agent) {
            return '<option value="' + escapeHtml(agent.id) + '">' + escapeHtml(agent.name) + '</option>';
        }).join('');
        ['agentSelect', 'composerAgent'].forEach(function (id) {
            var select = $(id);
            if (!select) return;
            select.innerHTML = options;
            select.value = state.agentId || '';
        });
        $('sessionAgentFilter').innerHTML = '<option value="">所有智能体</option>' + state.agents.map(function (agent) {
            return '<option value="' + escapeHtml(agent.id) + '">' + escapeHtml(agent.name) + '</option>';
        }).join('');
    }

    async function loadAgents() {
        try {
            var response = await api('/api/v2/llm/agents');
            var body = await response.json();
            if (!response.ok) throw new Error(body.message || body.msg || '智能体加载失败');
            state.agents = agentsFrom(body);
        } catch (error) {
            console.error(error);
            state.agents = [];
        }
        if (!state.agents.length) state.agents = [{id: 'builtin_common', name: '通用助手'}];
        if (!state.agentId) state.agentId = defaultAgentId();
        syncAgentSelects();
    }

    async function loadSessions() {
        try {
            var response = await api('/api/v2/llm/sessions');
            var body = await response.json();
            if (!response.ok) throw new Error(body.message || body.msg || '会话加载失败');
            state.sessions = Array.isArray(body.data) ? body.data : (Array.isArray(body.result) ? body.result : []);
            renderSessions();
        } catch (error) {
            $('sessionList').innerHTML = '<div class="ai-list-loading">会话加载失败，请刷新重试</div>';
        }
    }

    function visibleSessions() {
        var query = ($('sessionSearch').value || '').trim().toLowerCase();
        var agentId = $('sessionAgentFilter').value;
        return state.sessions.filter(function (session) {
            var title = (session.title || '未命名会话').toLowerCase();
            if (query && title.indexOf(query) < 0) return false;
            if (agentId && String(session.agent_id) !== String(agentId)) return false;
            if (state.filter === 'pinned' && !session.is_pinned) return false;
            if (state.filter === 'active') {
                var activeAt = new Date(text(session.last_message_at || session.updated_at).replace(' ', 'T'));
                if (!activeAt.getTime() || Date.now() - activeAt.getTime() > 259200000) return false;
            }
            return true;
        });
    }

    function renderSessions() {
        var sessions = visibleSessions();
        var html = '';
        var lastGroup = '';
        $('sessionCount').textContent = state.sessions.length;
        if (!sessions.length) {
            $('sessionList').innerHTML = '<div class="ai-list-loading">还没有匹配的会话</div>';
            return;
        }
        sessions.forEach(function (session) {
            var group = sessionGroup(session.last_message_at || session.updated_at);
            if (group !== lastGroup) {
                html += '<div class="ai-session-group">' + group + '</div>';
                lastGroup = group;
            }
            var active = state.session && String(state.session.id) === String(session.id);
            html += '<button type="button" class="ai-session-item ' + (active ? 'active' : '') + '" data-session-id="' + escapeHtml(session.id) + '">' +
                '<i class="' + (session.is_pinned ? 'fas fa-bookmark ai-pin' : (session.parent_session_id ? 'fas fa-code-branch' : 'far fa-comment')) + '"></i>' +
                '<span class="ai-session-text"><strong>' + escapeHtml(session.title || '未命名会话') + '</strong>' +
                '<span>' + escapeHtml(session.agent_name || agentName(session.agent_id)) + ' · ' + formatTime(session.last_message_at || session.updated_at) + '</span></span>' +
                '<span class="ai-session-more" aria-hidden="true"><i class="fas fa-chevron-right"></i></span></button>';
        });
        $('sessionList').innerHTML = html;
    }

    function setMode(chat) {
        $('emptyState').hidden = chat;
        $('chatView').hidden = !chat;
        if (!state.streaming) setStatus(chat && state.session ? agentName(state.session.agent_id) : '准备开始');
    }

    function fillSessionHeader() {
        if (!state.session) return;
        $('chatTitle').textContent = state.session.title || '未命名会话';
        $('chatAgentName').textContent = '智能体 · ' + agentName(state.session.agent_id);
        state.agentId = String(state.session.agent_id || state.agentId || '');
        $('composerAgent').value = state.agentId;
        $('composerAgent').disabled = true;
        $('pinBtn').classList.toggle('is-pinned', !!state.session.is_pinned);
        $('pinBtn').innerHTML = '<i class="' + (state.session.is_pinned ? 'fas' : 'far') + ' fa-bookmark"></i>';
        var branches = state.session.branch_navigation || [];
        var currentBranchIndex = branches.findIndex(function (branch) { return String(branch.id) === String(state.session.id); });
        $('branchNav').hidden = branches.length <= 1;
        if (branches.length > 1) {
            var currentBranch = branches[currentBranchIndex] || branches[0];
            $('branchLabel').textContent = currentBranch.is_original ? '原对话' : ('分支 ' + currentBranch.branch_order);
            $('branchPrev').disabled = currentBranchIndex <= 0;
            $('branchNext').disabled = currentBranchIndex < 0 || currentBranchIndex >= branches.length - 1;
            $('branchPrev').dataset.sessionId = currentBranchIndex > 0 ? branches[currentBranchIndex - 1].id : '';
            $('branchNext').dataset.sessionId = currentBranchIndex < branches.length - 1 ? branches[currentBranchIndex + 1].id : '';
        }
        renderSessions();
    }

    function isNearBottom() {
        var messages = $('messages');
        return messages.scrollHeight - messages.scrollTop - messages.clientHeight < 120;
    }

    function updateJumpButton() {
        var shouldShow = $('chatView').hidden === false && !isNearBottom();
        $('jumpBottomBtn').hidden = !shouldShow;
    }

    function scrollToBottom(force) {
        if (!force && !state.stickToBottom) return;
        var messages = $('messages');
        messages.scrollTop = messages.scrollHeight;
        state.stickToBottom = true;
        updateJumpButton();
    }

    function messageActions(role, feedback) {
        if (role === 'user') {
            return '<div class="ai-message-actions"><button type="button" data-message-action="edit"><i class="far fa-pen-to-square"></i> 编辑</button><button type="button" data-message-action="copy"><i class="far fa-copy"></i> 复制</button></div>';
        }
        return '<div class="ai-message-actions"><button type="button" data-message-action="copy"><i class="far fa-copy"></i> 复制</button><button type="button" data-message-action="regenerate"><i class="fas fa-rotate-right"></i> 重试</button><button type="button" data-message-action="feedback-up" class="' + (Number(feedback) === 1 ? 'active' : '') + '" title="回答有帮助"><i class="far fa-thumbs-up"></i></button><button type="button" data-message-action="feedback-down" class="' + (Number(feedback) === -1 ? 'active' : '') + '" title="回答需要改进"><i class="far fa-thumbs-down"></i></button><button type="button" data-message-action="note"><i class="far fa-note-sticky"></i> 笔记</button><button type="button" data-message-action="mind"><i class="fas fa-sitemap"></i> 导图</button><button type="button" data-message-action="quote"><i class="fas fa-quote-left"></i> 引用</button></div>';
    }

    function messageAttachments(attachments) {
        if (!attachments || !attachments.length) return '';
        return '<div class="ai-message-attachments">' + attachments.map(function (attachment) {
            return '<div class="ai-message-attachment" title="' + escapeHtml(attachment.name) + '">' +
                '<i class="far fa-file-lines"></i><span>' + escapeHtml(attachment.name) + '</span></div>';
        }).join('') + '</div>';
    }

    function appendMessage(message, shouldScroll) {
        var role = message.role === 'user' ? 'user' : 'assistant';
        var row = document.createElement('div');
        row.className = 'ai-message ' + (role === 'user' ? 'user' : 'ai');
        row.dataset.content = text(message.content);
        row.dataset.conversationId = message.conversation_id || '';
        row.dataset.role = role;
        row.dataset.feedback = message.feedback || '';
        row._attachments = message.attachments || [];
        var userInitial = $('aiWorkbench').dataset.userInitial || '你';
        row.innerHTML = '<div class="ai-message-avatar">' + (role === 'user' ? escapeHtml(userInitial) : '✦') + '</div>' +
            '<div class="ai-message-body"><div class="ai-message-meta">' + (role === 'user' ? '你' : escapeHtml(agentName(state.session && state.session.agent_id))) + '</div>' +
            (role === 'user' ? messageAttachments(row._attachments) : '') +
            '<div class="ai-bubble ' + (role === 'user' ? 'plain' : 'markdown-content') + '">' +
            (role === 'user' ? escapeHtml(message.content).replace(/\n/g, '<br>') : markdown(message.content)) + '</div>' +
            messageActions(role, message.feedback) + '</div>';
        $('messages').appendChild(row);
        if (role === 'assistant') enhanceCodeBlocks(row);
        if (shouldScroll) scrollToBottom(true);
        return row;
    }

    function renderMessages(messages) {
        $('messages').innerHTML = '';
        (messages || []).forEach(function (message) { appendMessage(message, false); });
        requestAnimationFrame(function () { scrollToBottom(true); });
    }

    function showThinking() {
        removeThinking();
        var row = document.createElement('div');
        row.className = 'ai-message ai';
        row.id = 'thinking';
        row.innerHTML = '<div class="ai-message-avatar">✦</div><div class="ai-thinking"><span></span><span></span><span></span><b>正在思考</b></div>';
        $('messages').appendChild(row);
        scrollToBottom(true);
    }

    function removeThinking() {
        var thinking = $('thinking');
        if (thinking) thinking.remove();
    }

    function showInlineError(message, retryText) {
        var row = document.createElement('div');
        row.className = 'ai-inline-error';
        row.innerHTML = '<i class="fas fa-triangle-exclamation"></i><span>' + escapeHtml(message) + '</span>' +
            '<button type="button" data-retry-text="' + escapeHtml(retryText) + '">重试</button>';
        $('messages').appendChild(row);
        scrollToBottom(true);
    }

    async function openSession(id) {
        if (state.streaming) {
            toast('请先停止当前回答，再切换会话', 'info');
            return;
        }
        try {
            var response = await api('/api/v2/llm/sessions/' + id);
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || body.msg || '打开失败');
            state.session = payload(body);
            state.agentId = String(state.session.agent_id || '');
            setMode(true);
            fillSessionHeader();
            renderMessages(state.session.messages || []);
            closeSidebar();
            restoreDraft();
        } catch (error) {
            toast('打开会话失败：' + error.message, 'error');
        }
    }

    async function createSession(prompt) {
        var agentId = $('agentSelect').value || $('composerAgent').value || state.agentId || defaultAgentId();
        if (!agentId) {
            toast('暂无可用智能体，请先完成智能体配置', 'error');
            return false;
        }
        try {
            var response = await api('/api/v2/llm/sessions', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({agent_id: agentId, title: '新对话'})
            });
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || body.msg || '创建失败');
            state.session = payload(body);
            state.agentId = String(agentId);
            setMode(true);
            fillSessionHeader();
            renderMessages([]);
            await loadSessions();
            if (prompt) await sendMessage(prompt);
            else $('messageInput').focus();
            return true;
        } catch (error) {
            toast('创建会话失败：' + error.message, 'error');
            return false;
        }
    }

    function generationId() {
        if (window.crypto && window.crypto.getRandomValues) {
            var values = new Uint32Array(4);
            window.crypto.getRandomValues(values);
            return Array.prototype.map.call(values, function (value) { return value.toString(16); }).join('');
        }
        return Date.now().toString(36) + Math.random().toString(36).slice(2);
    }

    function formatFileSize(size) {
        size = Number(size || 0);
        if (size < 1024) return size + ' B';
        if (size < 1048576) return Math.round(size / 1024) + ' KB';
        return (size / 1048576).toFixed(1) + ' MB';
    }

    function renderAttachmentTray() {
        var tray = $('attachmentTray');
        var items = state.attachments.map(function (attachment) {
            return '<div class="ai-attachment-chip"><span class="ai-attachment-icon">' + escapeHtml(attachment.extension || 'file') + '</span>' +
                '<span class="ai-attachment-copy"><strong title="' + escapeHtml(attachment.name) + '">' + escapeHtml(attachment.name) + '</strong><span>' + formatFileSize(attachment.size) + '</span></span>' +
                '<button class="ai-attachment-remove" type="button" data-remove-attachment="' + escapeHtml(attachment.id) + '" title="移除附件"><i class="fas fa-xmark"></i></button></div>';
        });
        for (var index = 0; index < state.uploadingAttachments; index++) {
            items.push('<div class="ai-attachment-chip uploading"><span class="ai-attachment-icon"><i class="fas fa-spinner fa-spin"></i></span><span class="ai-attachment-copy"><strong>正在读取文件</strong><span>上传处理中…</span></span></div>');
        }
        tray.innerHTML = items.join('');
        tray.hidden = items.length === 0;
        $('attachBtn').disabled = state.streaming || state.uploadingAttachments > 0 || state.attachments.length >= 5;
    }

    async function uploadFiles(files) {
        files = Array.prototype.slice.call(files || []);
        if (!files.length) return;
        var remaining = Math.max(0, 5 - state.attachments.length - state.uploadingAttachments);
        if (!remaining) return toast('每条消息最多添加 5 个附件', 'info');
        if (files.length > remaining) toast('每条消息最多添加 5 个附件，已截取前 ' + remaining + ' 个', 'info');
        files = files.slice(0, remaining);
        files.forEach(async function (file) {
            if (file.size > 10 * 1024 * 1024) {
                toast(file.name + ' 超过 10MB，无法上传', 'error');
                return;
            }
            state.uploadingAttachments++;
            renderAttachmentTray();
            try {
                var data = new FormData();
                data.append('file', file, file.name);
                var response = await api('/api/v2/llm/attachments', {method: 'POST', body: data});
                var body = await response.json();
                if (!response.ok || !body.success) throw new Error(body.message || body.msg || '附件上传失败');
                state.attachments.push(payload(body));
            } catch (error) {
                toast(file.name + '：' + error.message, 'error');
            } finally {
                state.uploadingAttachments--;
                renderAttachmentTray();
            }
        });
    }

    async function removeAttachment(id) {
        var attachment = state.attachments.find(function (item) { return String(item.id) === String(id); });
        if (!attachment) return;
        try {
            var response = await api('/api/v2/llm/attachments/' + attachment.id, {method: 'DELETE'});
            if (!response.ok) throw new Error('移除失败');
            state.attachments = state.attachments.filter(function (item) { return String(item.id) !== String(id); });
            renderAttachmentTray();
        } catch (error) {
            toast(error.message, 'error');
        }
    }

    async function requestStop() {
        if (!state.streaming || state.stopRequested || !state.generationId) return;
        state.stopRequested = true;
        setStatus('正在停止…', 'stopping');
        $('sendBtn').disabled = true;
        try {
            var response = await api('/api/v2/llm/chat/stop', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({generation_id: state.generationId})
            });
            if (!response.ok) throw new Error('停止请求失败');
            state.stopFallbackTimer = setTimeout(function () {
                if (state.streaming && state.controller) state.controller.abort();
            }, 10000);
        } catch (error) {
            if (state.controller) state.controller.abort();
        }
    }

    async function sendMessage(value) {
        var input = $('messageInput');
        var messageText = text(value !== undefined ? value : input.value).trim();
        if (!messageText && state.attachments.length) messageText = '请阅读并分析这些附件。';
        if ((!messageText && !state.attachments.length) || state.streaming || state.uploadingAttachments) return;
        if (!state.session) {
            await createSession(messageText);
            return;
        }

        input.value = '';
        clearDraft();
        resizeInput();
        updateCount();
        var messageAttachmentList = state.attachments.slice();
        var userRow = appendMessage({role: 'user', content: messageText, attachments: messageAttachmentList}, true);
        state.streaming = true;
        state.stopRequested = false;
        state.generationId = generationId();
        state.controller = new AbortController();
        state.lastFailedText = '';
        renderAttachmentTray();
        $('composer').classList.add('streaming');
        $('sendBtn').disabled = false;
        $('sendBtn').innerHTML = '<i class="fas fa-stop"></i>';
        $('sendBtn').title = '停止生成';
        setStatus('正在思考…', 'generating');
        showThinking();

        var responseRow = null;
        var answer = '';
        var streamError = '';
        var conversationId = '';
        var stopped = false;

        function updateAnswer() {
            if (!responseRow) {
                removeThinking();
                responseRow = appendMessage({role: 'assistant', content: ''}, true);
            }
            responseRow.querySelector('.ai-bubble').innerHTML = markdown(answer || streamError || '正在生成…');
            responseRow.dataset.content = answer || streamError;
            if (conversationId) responseRow.dataset.conversationId = conversationId;
            enhanceCodeBlocks(responseRow);
            scrollToBottom(false);
        }

        function consumeEvent(eventText) {
            eventText.split(/\n/).forEach(function (line) {
                if (line.indexOf('data:') !== 0) return;
                var raw = line.replace(/^data:\s?/, '').trim();
                if (!raw || raw === '[DONE]') return;
                try {
                    var item = JSON.parse(raw);
                    var delta = item.choices && item.choices[0] && item.choices[0].delta;
                    if (item.type === 'error') {
                        streamError = item.message || '模型请求失败';
                        return;
                    }
                    if (item.type === 'stopped') stopped = true;
                    if (item.type === 'done') stopped = !!item.stopped;
                    if (item.session_title && state.session) {
                        state.session.title = item.session_title;
                        fillSessionHeader();
                    }
                    if (item.conversation_id) conversationId = String(item.conversation_id);
                    var piece = delta && delta.content !== undefined ? delta.content : (item.type === 'delta' ? item.content : '');
                    if (Array.isArray(piece)) {
                        piece = piece.map(function (part) { return part && part.text ? part.text : ''; }).join('');
                    }
                    answer += text(piece);
                } catch (parseError) {
                    if (raw.charAt(0) !== '{' && raw.charAt(0) !== '[') streamError += raw;
                }
            });
            if (answer || streamError) updateAnswer();
        }

        try {
            var response = await api('/api/v2/llm/chat', {
                method: 'POST',
                signal: state.controller.signal,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    query: messageText,
                    session_id: state.session.id,
                    agent_id: state.agentId,
                    generation_id: state.generationId,
                    attachment_ids: messageAttachmentList.map(function (attachment) { return attachment.id; })
                })
            });
            if (!response.ok) {
                var errorBody = await response.text();
                var errorMessage = '';
                try {
                    var errorJson = JSON.parse(errorBody);
                    errorMessage = errorJson.message || errorJson.msg || errorJson.error || '';
                    if (typeof errorMessage === 'object') errorMessage = JSON.stringify(errorMessage);
                } catch (ignore) {}
                throw new Error(errorMessage || errorBody || ('HTTP ' + response.status));
            }
            state.attachments = [];
            renderAttachmentTray();
            if (response.body && response.body.getReader) {
                var reader = response.body.getReader();
                var decoder = new TextDecoder();
                var buffer = '';
                while (true) {
                    var part = await reader.read();
                    buffer = (buffer + decoder.decode(part.value || new Uint8Array(), {stream: !part.done})).replace(/\r\n/g, '\n');
                    var events = buffer.split(/\n\n/);
                    buffer = events.pop();
                    events.forEach(consumeEvent);
                    if (part.done) break;
                }
                if (buffer.trim()) consumeEvent(buffer);
            } else {
                var json = await response.json();
                var data = payload(json);
                answer = data.content || data.answer || '';
                updateAnswer();
            }
            if (!answer && streamError) throw new Error(streamError);
            if (!answer && !stopped) throw new Error('模型没有返回可显示的内容');
            if (conversationId) {
                userRow.dataset.conversationId = conversationId;
                if (responseRow) responseRow.dataset.conversationId = conversationId;
            }
            if (stopped || state.stopRequested) toast('已停止生成，当前内容已保存', 'info');
            await loadSessions();
        } catch (error) {
            removeThinking();
            if (error.name === 'AbortError' && state.stopRequested) {
                toast('已停止生成', 'info');
            } else {
                if (responseRow && !answer) responseRow.remove();
                state.lastFailedText = messageText;
                showInlineError('发送失败：' + error.message, messageText);
            }
        } finally {
            if (state.stopFallbackTimer) clearTimeout(state.stopFallbackTimer);
            state.stopFallbackTimer = null;
            state.streaming = false;
            state.controller = null;
            state.generationId = null;
            state.stopRequested = false;
            $('composer').classList.remove('streaming');
            $('sendBtn').disabled = false;
            $('sendBtn').innerHTML = '<i class="fas fa-arrow-up"></i>';
            $('sendBtn').title = '发送';
            renderAttachmentTray();
            setStatus(state.session ? agentName(state.session.agent_id) : '准备开始');
        }
    }

    async function editMessage(row) {
        if (state.streaming) return;
        var conversationId = row.dataset.conversationId;
        if (!conversationId) {
            toast('消息仍在同步，请稍后再编辑', 'info');
            return;
        }
        var bubble = row.querySelector('.ai-bubble');
        var actions = row.querySelector('.ai-message-actions');
        var original = row.dataset.content || '';
        actions.hidden = true;
        bubble.innerHTML = '<textarea class="ai-message-editor" maxlength="8000"></textarea><div class="ai-message-editor-actions"><button type="button" data-edit-cancel>取消</button><button type="button" class="primary" data-edit-save>保存并重新发送</button></div>';
        var editor = bubble.querySelector('textarea');
        editor.value = original;
        editor.focus();
        editor.selectionStart = editor.selectionEnd = editor.value.length;
        bubble.querySelector('[data-edit-cancel]').onclick = function () {
            bubble.innerHTML = escapeHtml(original).replace(/\n/g, '<br>');
            actions.hidden = false;
        };
        bubble.querySelector('[data-edit-save]').onclick = async function () {
            var updated = editor.value.trim();
            if (!updated) return;
            try {
                var response = await api('/api/v2/llm/sessions/' + state.session.id + '/messages/' + conversationId + '/branch', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({query: updated})
                });
                var body = await response.json();
                if (!response.ok || !body.success) throw new Error(body.message || body.msg || '编辑失败');
                var branchData = payload(body);
                state.attachments = branchData.attachments || [];
                renderAttachmentTray();
                await openSession(branchData.session_id);
                await sendMessage(updated);
            } catch (error) {
                toast('编辑失败：' + error.message, 'error');
            }
        };
    }

    async function regenerate() {
        if (!state.session || state.streaming) return;
        try {
            var response = await api('/api/v2/llm/sessions/' + state.session.id + '/regenerate', {method: 'POST'});
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || body.msg || '无法重新生成');
            var data = payload(body);
            state.attachments = data.attachments || [];
            renderAttachmentTray();
            await openSession(data.session_id);
            await sendMessage(data.query);
        } catch (error) {
            toast('重新生成失败：' + error.message, 'error');
        }
    }

    async function submitFeedback(row, value) {
        var conversationId = row.dataset.conversationId;
        if (!state.session || !conversationId) return toast('消息仍在同步，请稍后评价', 'info');
        var current = Number(row.dataset.feedback || 0);
        var nextValue = current === value ? 0 : value;
        try {
            var response = await api('/api/v2/llm/sessions/' + state.session.id + '/messages/' + conversationId + '/feedback', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({feedback: nextValue})
            });
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || body.msg || '评价失败');
            row.dataset.feedback = nextValue || '';
            var up = row.querySelector('[data-message-action="feedback-up"]');
            var down = row.querySelector('[data-message-action="feedback-down"]');
            if (up) up.classList.toggle('active', nextValue === 1);
            if (down) down.classList.toggle('active', nextValue === -1);
            toast(nextValue ? '感谢反馈' : '已取消反馈', 'success');
        } catch (error) {
            toast(error.message, 'error');
        }
    }

    async function togglePin() {
        if (!state.session) return;
        try {
            var response = await api('/api/v2/llm/sessions/' + state.session.id + '/toggle-pin', {method: 'POST'});
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || '操作失败');
            state.session.is_pinned = payload(body).is_pinned;
            fillSessionHeader();
            await loadSessions();
        } catch (error) { toast('固定操作失败：' + error.message, 'error'); }
    }

    async function clearSession() {
        if (!state.session || !confirm('确定清空当前会话吗？此操作无法撤销。')) return;
        try {
            var response = await api('/api/v2/llm/sessions/' + state.session.id + '/clear', {method: 'POST'});
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || '清空失败');
            renderMessages([]);
            await loadSessions();
        } catch (error) { toast('清空失败：' + error.message, 'error'); }
    }

    async function deleteSession() {
        if (!state.session || !confirm('确定删除当前会话吗？此操作无法撤销。')) return;
        try {
            var response = await api('/api/v2/llm/sessions/' + state.session.id, {method: 'DELETE'});
            if (!response.ok) throw new Error('删除请求失败');
            state.session = null;
            $('composerAgent').disabled = false;
            if (!state.agentId) state.agentId = defaultAgentId();
            syncAgentSelects();
            setMode(false);
            await loadSessions();
            $('messageInput').focus();
        } catch (error) { toast('删除失败：' + error.message, 'error'); }
    }

    async function renameSession() {
        if (!state.session) return;
        var name = prompt('请输入新的会话标题', state.session.title || '未命名会话');
        if (!name || !name.trim()) return;
        try {
            var response = await api('/api/v2/llm/sessions/' + state.session.id + '/title', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({title: name.trim()})
            });
            var body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || '重命名失败');
            state.session.title = name.trim();
            fillSessionHeader();
            await loadSessions();
        } catch (error) { toast('重命名失败：' + error.message, 'error'); }
    }

    function exportSession() {
        if (!state.session) return;
        var lines = ['# ' + (state.session.title || '未命名会话'), '', '- 智能体：' + agentName(state.session.agent_id), ''];
        Array.prototype.forEach.call(document.querySelectorAll('.ai-message[data-role]'), function (row) {
            lines.push(row.dataset.role === 'user' ? '## 用户' : '## AI', row.dataset.content || '', '');
        });
        var blob = new Blob([lines.join('\n')], {type: 'text/markdown;charset=utf-8'});
        var anchor = document.createElement('a');
        anchor.href = URL.createObjectURL(blob);
        anchor.download = (state.session.title || 'ai-session') + '.md';
        anchor.click();
        setTimeout(function () { URL.revokeObjectURL(anchor.href); }, 0);
    }

    async function saveArtifact(row, type) {
        var content = row.dataset.content || '';
        if (!content) return;
        var firstLine = content.split('\n').find(function (line) { return line.trim(); }) || 'AI 内容';
        var title = (state.session.title || 'AI会话') + ' - ' + firstLine;
        try {
            var url = type === 'note' ? '/api/v2/notes' : '/api/v2/minds';
            var body = type === 'note'
                ? {name: '[AI] ' + title.slice(0, 240), content: content, tags: 'AI会话', status: 1}
                : {name: '[AI导图] ' + title.slice(0, 230), content: content, source_type: 'llm', source_id: state.session.id};
            var response = await api(url, {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body)});
            var result = await response.json();
            if (!response.ok) throw new Error(result.message || result.msg || '保存失败');
            var data = payload(result);
            var id = data.id || (data.mind && data.mind.id);
            toast(type === 'note' ? '已保存为笔记' : '已创建思维导图', 'success');
            if (type === 'mind' && id) window.open('/mind/' + id, '_blank');
        } catch (error) { toast('保存失败：' + error.message, 'error'); }
    }

    function toast(message, type) {
        var old = document.querySelector('.ai-toast');
        if (old) old.remove();
        var element = document.createElement('div');
        element.className = 'ai-toast ' + (type || '');
        element.setAttribute('role', 'status');
        element.textContent = message;
        document.body.appendChild(element);
        setTimeout(function () { if (element.parentNode) element.remove(); }, 3000);
    }

    function closeSidebar() {
        $('aiSidebar').classList.remove('open');
        $('sidebarBackdrop').classList.remove('open');
    }

    function resizeInput() {
        var input = $('messageInput');
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 180) + 'px';
    }

    function updateCount() {
        $('charCount').textContent = $('messageInput').value.length;
    }

    function draftKey() {
        return 'llm-chat-draft:' + (state.session ? state.session.id : ('new:' + (state.agentId || 'default')));
    }

    function saveDraft() {
        try { localStorage.setItem(draftKey(), $('messageInput').value); } catch (error) {}
    }

    function restoreDraft() {
        try { $('messageInput').value = localStorage.getItem(draftKey()) || ''; } catch (error) { $('messageInput').value = ''; }
        resizeInput();
        updateCount();
    }

    function clearDraft() {
        try { localStorage.removeItem(draftKey()); } catch (error) {}
    }

    function startNewChat() {
        if (state.streaming) {
            requestStop();
            toast('正在停止当前回答，完成后可开始新对话', 'info');
            return;
        }
        state.session = null;
        if (!state.agentId) state.agentId = defaultAgentId();
        $('composerAgent').disabled = false;
        syncAgentSelects();
        setMode(false);
        closeSidebar();
        restoreDraft();
        $('messageInput').focus();
    }

    function bindEvents() {
        document.addEventListener('click', function (event) {
            var sessionButton = event.target.closest('.ai-session-item');
            if (sessionButton) openSession(sessionButton.dataset.sessionId);

            var action = event.target.closest('[data-message-action]');
            if (action) {
                var row = action.closest('.ai-message');
                var name = action.dataset.messageAction;
                if (name === 'copy') copyText(row.dataset.content || '').then(function () { toast('已复制', 'success'); });
                if (name === 'edit') editMessage(row);
                if (name === 'regenerate') regenerate();
                if (name === 'feedback-up') submitFeedback(row, 1);
                if (name === 'feedback-down') submitFeedback(row, -1);
                if (name === 'note') saveArtifact(row, 'note');
                if (name === 'mind') saveArtifact(row, 'mind');
                if (name === 'quote') {
                    $('messageInput').value = '> ' + (row.dataset.content || '').replace(/\n/g, '\n> ') + '\n\n';
                    resizeInput(); updateCount(); saveDraft(); $('messageInput').focus();
                }
            }

            var retry = event.target.closest('[data-retry-text]');
            if (retry) {
                var errorRow = retry.closest('.ai-inline-error');
                if (errorRow) errorRow.remove();
                sendMessage(retry.dataset.retryText);
            }
            var remove = event.target.closest('[data-remove-attachment]');
            if (remove) removeAttachment(remove.dataset.removeAttachment);
            if (!event.target.closest('#chatMenuBtn') && !event.target.closest('#chatMenu')) $('chatMenu').classList.remove('open');
        });

        $('newChatBtn').onclick = startNewChat;
        $('startEmptyBtn').onclick = function () { createSession(''); };
        $('sendBtn').onclick = function () { state.streaming ? requestStop() : sendMessage(); };
        $('messageInput').addEventListener('input', function () { resizeInput(); updateCount(); saveDraft(); });
        $('messageInput').addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                sendMessage();
            }
        });
        $('agentSelect').onchange = function () { state.agentId = this.value; $('composerAgent').value = this.value; restoreDraft(); };
        $('composerAgent').onchange = function () { state.agentId = this.value; $('agentSelect').value = this.value; restoreDraft(); };
        $('attachBtn').onclick = function () { $('attachmentInput').click(); };
        $('attachmentInput').onchange = function () {
            uploadFiles(this.files);
            this.value = '';
        };
        var composer = $('composer');
        composer.addEventListener('dragover', function (event) {
            if (!event.dataTransfer || !event.dataTransfer.types || Array.prototype.indexOf.call(event.dataTransfer.types, 'Files') < 0) return;
            event.preventDefault();
            composer.classList.add('dragging');
        });
        composer.addEventListener('dragleave', function (event) {
            if (!composer.contains(event.relatedTarget)) composer.classList.remove('dragging');
        });
        composer.addEventListener('drop', function (event) {
            event.preventDefault();
            composer.classList.remove('dragging');
            uploadFiles(event.dataTransfer && event.dataTransfer.files);
        });
        $('messageInput').addEventListener('paste', function (event) {
            var files = event.clipboardData && event.clipboardData.files;
            if (files && files.length) uploadFiles(files);
        });
        $('sessionSearch').oninput = renderSessions;
        $('sessionAgentFilter').onchange = renderSessions;
        Array.prototype.forEach.call(document.querySelectorAll('.ai-filter'), function (button) {
            button.onclick = function () {
                Array.prototype.forEach.call(document.querySelectorAll('.ai-filter'), function (item) { item.classList.remove('active'); });
                button.classList.add('active');
                state.filter = button.dataset.filter;
                renderSessions();
            };
        });
        Array.prototype.forEach.call(document.querySelectorAll('.ai-suggestions button'), function (button) {
            button.onclick = function () { state.session ? sendMessage(button.dataset.prompt) : createSession(button.dataset.prompt); };
        });
        $('messages').addEventListener('scroll', function () {
            state.stickToBottom = isNearBottom();
            updateJumpButton();
        }, {passive: true});
        $('jumpBottomBtn').onclick = function () { scrollToBottom(true); };
        $('openSidebar').onclick = function () { $('aiSidebar').classList.add('open'); $('sidebarBackdrop').classList.add('open'); };
        $('closeSidebar').onclick = closeSidebar;
        $('sidebarBackdrop').onclick = closeSidebar;
        $('pinBtn').onclick = togglePin;
        $('branchPrev').onclick = function () { if (this.dataset.sessionId) openSession(this.dataset.sessionId); };
        $('branchNext').onclick = function () { if (this.dataset.sessionId) openSession(this.dataset.sessionId); };
        $('renameBtn').onclick = renameSession;
        $('chatMenuBtn').onclick = function () { $('chatMenu').classList.toggle('open'); };
        $('regenerateBtn').onclick = regenerate;
        $('clearBtn').onclick = clearSession;
        $('deleteBtn').onclick = deleteSession;
        $('exportBtn').onclick = exportSession;
        $('clearUnpinnedBtn').onclick = async function () {
            var removable = state.sessions.filter(function (session) { return !session.is_pinned; });
            if (!removable.length) return toast('没有可清理的会话', 'info');
            if (!confirm('确定删除 ' + removable.length + ' 个未固定会话吗？')) return;
            for (var index = 0; index < removable.length; index++) {
                await api('/api/v2/llm/sessions/' + removable[index].id, {method: 'DELETE'});
            }
            if (state.session && !state.session.is_pinned) startNewChat();
            await loadSessions();
        };
        document.addEventListener('keydown', function (event) {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault(); startNewChat();
            }
            if (event.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                event.preventDefault(); $('sessionSearch').focus();
            }
            if (event.key === 'Escape' && state.streaming) requestStop();
        });
    }

    document.addEventListener('DOMContentLoaded', async function () {
        bindEvents();
        renderAttachmentTray();
        await loadAgents();
        await loadSessions();
        restoreDraft();
        setMode(false);
    });
}());
