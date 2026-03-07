@extends('layouts.app')

@section('title', '记录想法 - 蒙太奇')
@section('description', '记录和分享您的思考、笔记和灵感，支持Markdown、语音、图片多种形式')

@section('content')
    @include('components.ai-ask-modal')

    <!-- 引入Markdown编辑器 -->
    <link href="https://unpkg.com/easymde/dist/easymde.min.css" rel="stylesheet">
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <script src="/js/marked.min.js"></script>

    <style>
        /* Markdown编辑器样式 */
        .editor-toolbar {
            border: 1px solid var(--gray-200) !important;
            border-bottom: none !important;
            border-radius: 8px 8px 0 0;
            background: var(--gray-50) !important;
            opacity: 1 !important;
        }

        .editor-toolbar button {
            color: var(--gray-600) !important;
            border: none !important;
            background: transparent !important;
        }

        .editor-toolbar button:hover {
            color: var(--primary-color) !important;
            background: var(--gray-100) !important;
        }

        .editor-toolbar button.active {
            color: var(--primary-color) !important;
            background: var(--gray-200) !important;
        }

        .CodeMirror {
            border: 1px solid var(--gray-200) !important;
            border-radius: 0 0 8px 8px;
            font-family: 'Inter', 'SF Mono', Monaco, Menlo, Consolas, 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            min-height: 300px;
            background: white !important;
        }

        .CodeMirror-focused {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1) !important;
        }

        .editor-preview {
            background: white !important;
            border: 1px solid var(--gray-200) !important;
            border-radius: 8px !important;
            padding: 20px !important;
        }

        .editor-preview-side {
            border-left: 1px solid var(--gray-200) !important;
        }

        /* 录音相关样式 - 恢复以前的样式 */
        .audio-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }

        .audio-controls button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: white;
            font-size: 18px;
        }

        .audio-controls #start-record {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .audio-controls #start-record:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .audio-controls #start-record:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .audio-controls #stop-record {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .audio-controls #stop-record:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .audio-controls #stop-record:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .audio-timer {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            background: var(--gray-100);
            padding: 8px 16px;
            border-radius: 20px;
            min-width: 80px;
            text-align: center;
        }

        .audio-recording-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 8px;
            margin-top: 12px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* 笔记标签 */
        .note-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 16px 0;
        }

        .tag-badge {
            padding: 6px 12px;
            background: var(--gray-100);
            color: var(--gray-700);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .tag-badge:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
            border-color: var(--gray-300);
        }

        .tag-badge.active {
            background: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* 笔记卡片 */
        .note-card {
            transition: all 0.2s ease;
            border: 1px solid var(--gray-200);
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .note-card:hover {
            border-color: var(--gray-300);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .note-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-100);
            background: var(--gray-50);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .note-status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-private {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .status-public {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .note-time {
            font-size: 13px;
            color: var(--gray-500);
        }

        .note-operations {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .operation-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .operation-btn:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .operation-btn.edit:hover {
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .operation-btn.delete:hover {
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.1);
        }

        .operation-btn.like:hover {
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .operation-btn.ai:hover {
            color: #8a6cff;
            background: rgba(138, 108, 255, 0.1);
        }

        /* 笔记内容区域 */
        .note-body {
            padding: 24px;
        }

        .note-content {
            line-height: 1.8;
            color: var(--gray-800);
        }

        .note-content.collapsed {
            max-height: 200px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .note-content.collapsed::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0.9));
            pointer-events: none;
        }

        .expand-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background: var(--gray-50);
            border-radius: 8px;
            margin-top: 16px;
            color: var(--gray-600);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .expand-btn:hover {
            background: var(--gray-100);
            border-color: var(--gray-300);
        }

        .expand-btn i {
            margin-left: 4px;
            transition: transform 0.2s ease;
        }

        .note-content.expanded .expand-btn i {
            transform: rotate(180deg);
        }

        /* Markdown内容样式 */
        .markdown-content {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4 {
            color: var(--gray-900);
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
            line-height: 1.3;
        }

        .markdown-content h1 { font-size: 1.875rem; }
        .markdown-content h2 { font-size: 1.5rem; }
        .markdown-content h3 { font-size: 1.25rem; }
        .markdown-content h4 { font-size: 1.125rem; }

        .markdown-content p {
            margin: 1em 0;
            line-height: 1.8;
        }

        .markdown-content ul,
        .markdown-content ol {
            padding-left: 1.5em;
            margin: 1em 0;
        }

        .markdown-content li {
            margin: 0.5em 0;
        }

        .markdown-content blockquote {
            border-left: 4px solid var(--primary-color);
            margin: 1.5em 0;
            padding: 0.5em 1em;
            background: var(--gray-50);
            border-radius: 0 8px 8px 0;
            color: var(--gray-700);
        }

        .markdown-content code {
            background: var(--gray-100);
            color: var(--gray-800);
            padding: 0.2em 0.4em;
            border-radius: 4px;
            font-family: 'SF Mono', Monaco, Menlo, Consolas, 'Courier New', monospace;
            font-size: 0.9em;
        }

        .markdown-content pre {
            background: var(--gray-900);
            color: var(--gray-100);
            padding: 1.25em;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1.5em 0;
            border: 1px solid var(--gray-800);
        }

        .markdown-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
            font-size: 0.9em;
            line-height: 1.5;
        }

        .markdown-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5em 0;
            border: 1px solid var(--gray-200);
        }

        .markdown-content th,
        .markdown-content td {
            padding: 0.75em 1em;
            border: 1px solid var(--gray-200);
            text-align: left;
        }

        .markdown-content th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-900);
        }

        .markdown-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5em 0;
            border: 1px solid var(--gray-200);
        }

        /* 媒体内容 */
        .note-media {
            margin: 20px 0;
        }

        .audio-player {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: var(--gray-50);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .audio-player-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .audio-player-btn:hover {
            background: var(--primary-color);
            transform: scale(1.05);
        }

        .audio-duration {
            font-size: 14px;
            color: var(--gray-600);
            margin-left: auto;
        }

        /* AI助手按钮 */
        .ai-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .ai-action-btn {
            padding: 6px 12px;
            background: rgba(138, 108, 255, 0.1);
            color: #8a6cff;
            border: 1px solid rgba(138, 108, 255, 0.2);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .ai-action-btn:hover {
            background: rgba(138, 108, 255, 0.2);
            transform: translateY(-1px);
        }

        /* 操作区域 */
        .publish-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border-top: 1px solid var(--gray-100);
            background: var(--gray-50);
        }

        .ai-assist-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }

        .ai-assist-btn:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            transform: translateY(-1px);
        }

        /* 标签过滤 */
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
            padding: 16px;
            background: var(--gray-50);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .filter-tag {
            padding: 6px 16px;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 20px;
            font-size: 13px;
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-tag:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
        }

        .filter-tag.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>

    <script src="/js/recorder/recorder.js"></script>
    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : function(method, path, params) {
                const upperMethod = String(method || 'GET').toUpperCase();
                const fetcher = window.taskApiFetch || window.fetch;
                let url = '/api/v2' + path;
                const options = {
                    method: upperMethod,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };
                if (upperMethod === 'GET' && params && typeof params === 'object') {
                    const sp = new URLSearchParams();
                    Object.keys(params).forEach(function(key) {
                        const val = params[key];
                        if (val !== undefined && val !== null && val !== '') sp.append(key, val);
                    });
                    const qs = sp.toString();
                    if (qs) url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;
                } else if (params && typeof params === 'object') {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(params);
                }
                return fetcher(url, options).then(function(resp) { return resp.json(); });
            };

        function waitTokenReady() {
            if (window.__taskTokenBootstrapPromise && typeof window.__taskTokenBootstrapPromise.then === 'function') {
                return window.__taskTokenBootstrapPromise;
            }
            return Promise.resolve();
        }

        function getAccessToken() {
            try {
                if (window.TaskApiClient && typeof window.TaskApiClient.getAccessToken === 'function') {
                    return window.TaskApiClient.getAccessToken() || '';
                }
            } catch (e) {}
            return '';
        }

        // 全局变量 - 恢复以前的录音变量
        let recorder = null;
        let timerInterval = null;
        let startTime = null;
        let isRecording = false;

        // 初始化Markdown编辑器
        let easymde = null;
        let notesState = {
            items: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 0
            }
        };
        const CURRENT_USER_ID = Number('{{ Auth::id() }}' || 0);

        function initMarkdownEditor() {
            easymde = new EasyMDE({
                element: document.getElementById('markdown-editor'),
                spellChecker: false,
                status: false,
                autosave: {
                    enabled: false
                },
                placeholder: '使用Markdown记录您的想法、灵感和笔记...',
                renderingConfig: {
                    singleLineBreaks: false,
                    codeSyntaxHighlighting: true,
                },
                previewClass: ['editor-preview', 'markdown-content'],
                toolbar: [
                    'heading', 'bold', 'italic', 'strikethrough', '|',
                    'quote', 'unordered-list', 'ordered-list', '|',
                    'link', 'image', 'code',
                    'preview', 'side-by-side', 'fullscreen', '|',
                    {
                        name: "ai-assist",
                        action: function() {
                            openAskAIModal('markdown-editor', 'markdown-editor', '请帮我优化这段Markdown内容');
                        },
                        className: "fas fa-robot",
                        title: "AI助手"
                    }
                ],
                sideBySideFullscreen: false,
                minHeight: '30px',
                maxHeight: '300px',
                forceSync: true
            });

            // 监听编辑器内容变化
            easymde.codemirror.on('change', function() {
                updateCharCount();
            });

        }

        // 更新字符计数
        function updateCharCount() {
            const content = easymde ? easymde.value() : '';
            const count = content.length;
            const counter = document.getElementById('char-count');

            if (counter) {
                counter.textContent = `${count}/10000`;
                if (count > 10000) {
                    counter.style.color = 'var(--danger-color)';
                } else if (count > 9000) {
                    counter.style.color = 'var(--warning-color)';
                } else {
                    counter.style.color = 'var(--gray-400)';
                }
            }
        }

        // 添加内容到编辑器
        function addContent(type, content) {
            if (!easymde) return;

            switch(type) {
                case 'tag':
                    easymde.codemirror.replaceSelection(`#${content}# `);
                    break;
                case 'code':
                    easymde.codemirror.replaceSelection('\n```\n// 在这里输入代码\n```\n');
                    break;
                case 'heading':
                    easymde.codemirror.replaceSelection(`${content} `);
                    break;
                case 'list':
                    easymde.codemirror.replaceSelection(`- ${content}\n`);
                    break;
            }

            easymde.codemirror.focus();
        }

        // ================== 恢复以前的录音功能 ==================
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
            return `${mins}:${secs}`;
        }

        function updateTimer() {
            if (!startTime) return;

            const currentTime = new Date();
            const elapsedSeconds = Math.floor((currentTime - startTime) / 1000);
            const timerDisplay = document.getElementById('audio-timer');

            if (timerDisplay) {
                timerDisplay.textContent = formatTime(elapsedSeconds);
            }
        }

        function showRecordingIndicator() {
            const container = document.getElementById('audio-container');
            if (!container) return;

            container.innerHTML = `
                <div class="audio-recording-indicator" id="recording-indicator">
                    <div class="pulse-dot"></div>
                    <span style="color: var(--danger-color); font-weight: 500;">正在录音中...</span>
                    <span id="audio-timer">00:00</span>
                </div>
            `;
        }

        function hideRecordingIndicator() {
            const indicator = document.getElementById('recording-indicator');
            if (indicator) {
                indicator.remove();
            }
        }

        function startRecording() {
            if (!recorder) {
                alert('录音功能初始化失败，请刷新页面重试');
                return;
            }

            try {
                // 检查浏览器权限
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(function(stream) {
                        // 开始录音
                        recorder.start();

                        // 更新UI状态 - 恢复以前的按钮状态
                        document.getElementById('start-record').disabled = true;
                        document.getElementById('start-record').style.display = 'none';
                        document.getElementById('stop-record').disabled = false;
                        document.getElementById('stop-record').style.display = 'flex';

                        // 开始计时
                        startTime = new Date();
                        timerInterval = setInterval(updateTimer, 1000);
                        isRecording = true;

                        // 显示录音指示器
                        showRecordingIndicator();
                    })
                    .catch(function(err) {
                        console.error('麦克风权限被拒绝:', err);
                        alert('需要麦克风权限才能录音。请在浏览器设置中允许麦克风访问。');
                        resetRecordingState();
                    });
            } catch (error) {
                console.error('录音启动失败:', error);
                alert('录音功能启动失败，请确保浏览器支持录音功能');
                resetRecordingState();
            }
        }

        function stopRecording() {
            if (!recorder || !isRecording) return;

            try {
                // 停止录音
                recorder.stop();
                isRecording = false;

                // 停止计时
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }

                // 获取录音数据
                recorder.getBlob(function(blob) {
                    // 处理录音数据
                    processRecordingBlob(blob);
                });

                // 隐藏录音指示器
                hideRecordingIndicator();

            } catch (error) {
                console.error('停止录音失败:', error);
                alert('停止录音失败');
            } finally {
                resetRecordingState();
            }
        }

        function processRecordingBlob(blob) {
            // 创建音频播放器
            const container = document.getElementById('audio-container');
            const audioUrl = URL.createObjectURL(blob);
            const duration = Math.round(blob.size / (128 * 1000) * 8); // 估算时长

            container.innerHTML = `
                <div class="audio-player">
                    <button class="audio-player-btn" onclick="playAudio(this)">
                        <i class="fas fa-play"></i>
                    </button>
                    <div style="flex: 1">
                        <div style="font-weight: 500; color: var(--gray-700); margin-bottom: 4px;">录音回放</div>
                        <div style="font-size: 13px; color: var(--gray-500);">点击播放按钮收听录音</div>
                    </div>
                    <div class="audio-duration">${formatTime(duration)}</div>
                    <audio id="recorded-audio" src="${audioUrl}" preload="metadata"></audio>
                </div>
            `;

            // 设置编辑器内容 - 修改为适应Markdown编辑器
            if (easymde) {
                const currentValue = easymde.value();
                if (!currentValue.includes("#分享语音#")) {
                    const timestamp = new Date().toLocaleTimeString();
                    easymde.codemirror.replaceSelection(`\n\n#分享语音# (${timestamp})\n\n`);
                }
            }

            // 上传音频文件
            uploadAudioFile(blob);
        }

        function playAudio(button) {
            const audio = document.getElementById('recorded-audio');
            const icon = button.querySelector('i');

            if (audio.paused) {
                audio.play();
                icon.className = 'fas fa-pause';
                button.onclick = function() { pauseAudio(button); };
            } else {
                audio.pause();
                icon.className = 'fas fa-play';
                button.onclick = function() { playAudio(button); };
            }
        }

        function pauseAudio(button) {
            const audio = document.getElementById('recorded-audio');
            const icon = button.querySelector('i');

            audio.pause();
            icon.className = 'fas fa-play';
            button.onclick = function() { playAudio(button); };
        }

        function uploadAudioFile(blob) {
            const formData = new FormData();
            const filename = 'rec_' + Date.now() + '_' + Math.floor(Math.random() * 10000) + '.mp3';

            formData.append('file', blob, filename);
            formData.append('fname', filename.replace('.mp3', ''));

            waitTokenReady().then(function() {
                if (typeof window.taskApiFetch === 'function') {
                    return window.taskApiFetch('/api/v2/notes/upload', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                }

                const headers = { 'Accept': 'application/json' };
                const accessToken = getAccessToken();
                if (accessToken) {
                    headers['Authorization'] = 'Bearer ' + accessToken;
                }

                return fetch('/api/v2/notes/upload', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: headers,
                    body: formData
                });
            }).then(function(resp) {
                return resp.json().then(function(data) {
                    if (!resp.ok || !data || data.code != 9999) {
                        throw new Error((data && data.msg) ? data.msg : 'upload failed');
                    }
                    document.getElementById('fname').value = filename.replace('.mp3', '');
                    showToast('音频上传成功', 'success');
                });
            }).catch(function() {
                showToast('音频上传失败: 网络错误', 'error');
            });
        }

        function resetRecordingState() {
            const startBtn = document.getElementById('start-record');
            const stopBtn = document.getElementById('stop-record');

            if (startBtn) {
                startBtn.disabled = false;
                startBtn.style.display = 'flex';
            }

            if (stopBtn) {
                stopBtn.disabled = true;
                stopBtn.style.display = 'none';
            }

            startTime = null;
            isRecording = false;
        }

        function showToast(message, type = 'info') {
            // 恢复以前的toast提示
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#4a90e2'};
                color: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
            `;

            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
        }

        // 页面初始化
        document.addEventListener('DOMContentLoaded', function() {
            const addNoteForm = document.getElementById('add_note_form');
            if (addNoteForm) {
                addNoteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const statusVal = parseInt((document.getElementById('status_id') || {}).value || '1', 10);
                    submitProcess(isNaN(statusVal) ? 1 : statusVal);
                });
            }

            // 初始化Markdown编辑器
            initMarkdownEditor();
            updateCharCount();
            initCreateFormFromQuery();

            // 初始化录音器 - 恢复以前的初始化逻辑
            try {
                recorder = new Recorder({
                    sampleRate: 44100,
                    bitRate: 128,
                    success: function() {
                        console.log('录音器初始化成功');
                        document.getElementById('start-record').disabled = false;
                    },
                    error: function(msg) {
                        console.error('录音器初始化失败:', msg);
                        document.getElementById('start-record').disabled = true;
                        document.getElementById('start-record').innerHTML = '<i class="fas fa-microphone-slash"></i>';
                        document.getElementById('start-record').title = '录音功能不可用';
                    }
                });

                // 绑定按钮事件
                document.getElementById('start-record').addEventListener('click', startRecording);
                document.getElementById('stop-record').addEventListener('click', stopRecording);

            } catch (error) {
                console.error('页面初始化失败:', error);
            }

            loadNotes(getNoteQueryParams().page || 1);
        });

        // 打开AI助手
        function openNoteAI(noteId, noteContent) {
            openAskAIModal('note-' + noteId, 'note-content-' + noteId, '请帮我优化这段笔记内容');
        }

        // 切换笔记展开/折叠
        function toggleNoteExpand(noteId) {
            const content = document.getElementById('note-content-' + noteId);
            const btn = document.querySelector(`#note-${noteId} .expand-btn`);

            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-chevron-up mr-2"></i>收起';
                }
            } else {
                content.classList.add('collapsed');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>展开';
                }
            }
        }

        // 复制笔记内容
        function copyNoteContent(noteId) {
            const content = document.getElementById('note-content-' + noteId);
            if (!content) {
                showToast('未找到笔记内容', 'error');
                return;
            }

            const text = (content.innerText || content.textContent || '').trim();
            if (!text) {
                showToast('笔记内容为空，无法复制', 'warning');
                return;
            }

            function fallbackCopy(value) {
                const textArea = document.createElement('textarea');
                textArea.value = value;
                textArea.setAttribute('readonly', 'readonly');
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                let copied = false;
                try {
                    copied = document.execCommand('copy');
                } catch (e) {
                    copied = false;
                }
                document.body.removeChild(textArea);
                return copied;
            }

            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText(text).then(() => {
                    showToast('笔记内容已复制到剪贴板', 'success');
                }).catch(() => {
                    if (fallbackCopy(text)) {
                        showToast('笔记内容已复制到剪贴板', 'success');
                    } else {
                        showToast('复制失败，请手动复制', 'error');
                    }
                });
                return;
            }

            if (fallbackCopy(text)) {
                showToast('笔记内容已复制到剪贴板', 'success');
            } else {
                showToast('复制失败，请手动复制', 'error');
            }
        }

        // 标签过滤
        function filterNotes(tag, evt) {
            const currentFilter = document.querySelector('.filter-tag.active');
            if (currentFilter) currentFilter.classList.remove('active');

            if (evt && evt.target) {
                evt.target.classList.add('active');
            }

            // 这里可以添加AJAX请求来筛选笔记
            // 暂时先实现简单的客户端筛选
            const notes = document.querySelectorAll('.note-card');
            notes.forEach(note => {
                const content = note.querySelector('.note-content').innerText;
                if (tag === 'all' || content.includes(`#${tag}#`)) {
                    note.style.display = 'block';
                } else {
                    note.style.display = 'none';
                }
            });
        }
    </script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- 新建笔记卡片 -->
        <div class="card card-elevated mb-8">
            <form id="add_note_form" action="javascript:void(0)" method="POST">
                {!! csrf_field() !!}

                <div class="p-6">
                    <!-- Markdown编辑器 -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            笔记内容 <span class="text-gray-400 text-xs" id="char-count">0/10000</span>
                        </label>
                        <textarea id="markdown-editor" name="name" style="display: none;"></textarea>
                    </div>

                    <!-- 标签快捷输入 -->
                    <div class="mb-4">
                        <div class="text-sm font-medium text-gray-700 mb-2">快捷标签</div>
                        <div class="note-tags">
                            <div class="tag-badge" onclick="addContent('tag', '每日小目标')">#每日小目标#</div>
                            <div class="tag-badge" onclick="addContent('tag', '读书笔记')">#读书笔记#</div>
                            <div class="tag-badge" onclick="addContent('tag', '工作思考')">#工作思考#</div>
                            <div class="tag-badge" onclick="addContent('tag', '灵感闪现')">#灵感闪现#</div>
                            <div class="tag-badge" onclick="addContent('tag', '会议记录')">#会议记录#</div>
                            <div class="tag-badge" onclick="addContent('tag', '项目复盘')">#项目复盘#</div>
                            <div class="tag-badge" onclick="addContent('code', '')">
                                <i class="fas fa-code mr-1"></i>代码块
                            </div>
                            <div class="tag-badge" onclick="addContent('heading', '##')">
                                <i class="fas fa-heading mr-1"></i>标题
                            </div>
                        </div>
                    </div>

                    <!-- 录音功能 - 恢复以前的HTML结构 -->
                    <div class="mb-6">
                        <div class="text-sm font-medium text-gray-700 mb-2">语音记录</div>
                        <div class="audio-controls">
                            <button type="button" id="start-record" disabled title="开始录音" style="display: flex;">
                                <i class="fas fa-microphone"></i>
                            </button>
                            <button type="button" id="stop-record" disabled title="停止录音" style="display:none;">
                                <i class="fas fa-stop"></i>
                            </button>
                        </div>
                        <!-- 音频容器 -->
                        <div id="audio-container"></div>
                    </div>

                    <!-- 图片预览 -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg" id="add-image-preview-box" style="display:none;">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">图片预览</span>
                            <button type="button" onclick="hideAddImagePreview()"
                                    class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <img src="" alt="预览图片" class="rounded-lg max-h-[200px] mx-auto" id="add-image-preview-img">
                        <input type="hidden" name="add_image" value="" id="add_image_input">
                    </div>
                </div>

                <!-- 隐藏字段 -->
                <input type="hidden" name="source_type" value="0" id="source_type_input">
                <input type="hidden" name="source_id" value="0" id="source_id_input">
                <input type="hidden" name="fname" id="fname">
                <input type="hidden" name="status" id="status_id" value="">


                <!-- 发布操作 -->
                <div class="publish-actions">
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                        <span>支持Markdown格式和AI助手优化</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button"
                                class="btn btn-outline"
                                onclick="submitProcess(1)">
                            <i class="fas fa-lock mr-2"></i>私密保存
                        </button>
                        <button type="button"
                                class="btn btn-primary"
                                onclick="submitProcess(2)">
                            <i class="fas fa-globe mr-2"></i>公开发布
                        </button>
                        <button type="button"
                                class="btn ai-assist-btn"
                                onclick="openAskAIModal('markdown-editor', 'markdown-editor', '请帮我优化这段Markdown内容')">
                            <i class="fas fa-robot mr-2"></i>AI润色
                        </button>
                    </div>
                </div>
            </form>
        </div>


        <!-- 笔记列表 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-comments text-blue-500 mr-2"></i>
                    笔记列表
                </h2>
                <div class="text-sm text-gray-500" id="note-total-text">
                    共 0 条笔记
                </div>
            </div>

            <div id="notes-list"></div>

            <div class="text-center py-16 card" id="notes-empty" style="display:none;">
                <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-100 mb-6">
                    <i class="fas fa-sticky-note text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">还没有笔记</h3>
                <p class="text-gray-600 mb-6">开始记录您的第一个想法吧！</p>
                <button type="button" class="btn btn-primary" onclick="easymde && easymde.codemirror.focus()">
                    <i class="fas fa-plus mr-2"></i>
                    开始记录
                </button>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-center gap-3" id="notes-pagination" style="display:none;">
                <button type="button" class="btn btn-outline" id="notes-prev-btn">
                    <i class="fas fa-chevron-left mr-2"></i>上一页
                </button>
                <span class="text-sm text-gray-600" id="notes-page-text">第 1 / 1 页</span>
                <button type="button" class="btn btn-outline" id="notes-next-btn">
                    下一页<i class="fas fa-chevron-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatRelativeTime(value) {
            if (!value) return '刚刚';
            const date = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) return value;
            const diffSec = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
            if (diffSec < 60) return '刚刚';
            if (diffSec < 3600) return Math.floor(diffSec / 60) + '分钟前';
            if (diffSec < 86400) return Math.floor(diffSec / 3600) + '小时前';
            if (diffSec < 86400 * 30) return Math.floor(diffSec / 86400) + '天前';
            return value;
        }

        function getNoteQueryParams() {
            const query = new URLSearchParams(window.location.search || '');
            return {
                type: query.get('type') || '',
                add_content: query.get('add_content') || '',
                source_type: parseInt(query.get('source_type') || '0', 10) || 0,
                source_id: parseInt(query.get('source_id') || '0', 10) || 0,
                tag_id: parseInt(query.get('tag_id') || '0', 10) || 0,
                keyword: query.get('keyword') || '',
                page: parseInt(query.get('page') || '1', 10) || 1
            };
        }

        function hideAddImagePreview() {
            const box = document.getElementById('add-image-preview-box');
            const img = document.getElementById('add-image-preview-img');
            const input = document.getElementById('add_image_input');
            if (box) box.style.display = 'none';
            if (img) img.src = '';
            if (input) input.value = '';
        }

        function initCreateFormFromQuery() {
            const params = getNoteQueryParams();
            const sourceTypeInput = document.getElementById('source_type_input');
            const sourceIdInput = document.getElementById('source_id_input');
            if (sourceTypeInput) sourceTypeInput.value = String(params.source_type || 0);
            if (sourceIdInput) sourceIdInput.value = String(params.source_id || 0);

            if (params.type === 'image' && params.add_content) {
                const box = document.getElementById('add-image-preview-box');
                const img = document.getElementById('add-image-preview-img');
                const input = document.getElementById('add_image_input');
                if (box && img && input) {
                    img.src = params.add_content;
                    input.value = params.add_content;
                    box.style.display = '';
                }
            } else if (params.add_content && easymde) {
                easymde.value(params.add_content);
            }
        }

        function buildNoteCardHtml(note) {
            const isOwn = Number(note.user_id || 0) === CURRENT_USER_ID;
            const isPublic = Number(note.status || 0) === 2;
            const user = note.user || {};
            const userName = user.name || '用户';
            const avatarLetter = userName ? userName.substring(0, 1) : 'U';
            const color1 = user.color_1 || '#4a90e2';
            const color2 = user.color_2 || '#8a6cff';
            const canReadMedia = isOwn || isPublic;
            const contentHtml = (window.marked && typeof window.marked.parse === 'function')
                ? window.marked.parse(note.name || '')
                : (note.name || '');
            const actionsHtml = isOwn
                ? '<button class="operation-btn edit" onclick="window.location=\'/notes/' + Number(note.id) + '/edit\'" title="编辑笔记"><i class="fas fa-edit"></i></button>' +
                  '<button class="operation-btn delete delete_note" note_value="' + Number(note.id) + '" title="删除笔记"><i class="fas fa-trash"></i></button>'
                : '<button class="operation-btn like like_note" note_value="' + Number(note.id) + '" title="点赞"><i class="far fa-thumbs-up"></i></button>';

            return (
                '<div class="note-card" id="note-' + Number(note.id) + '">' +
                    '<div class="note-header">' +
                        '<div class="user-info">' +
                            '<div class="user-avatar" style="background: linear-gradient(135deg, ' + escapeHtml(color1) + ', ' + escapeHtml(color2) + ')">' + escapeHtml(avatarLetter) + '</div>' +
                            '<div class="user-details">' +
                                '<div class="user-name">' + escapeHtml(userName) +
                                    '<span class="note-status-badge ' + (isPublic ? 'status-public' : 'status-private') + '">' + (isPublic ? '公开' : '私密') + '</span>' +
                                '</div>' +
                                '<div class="note-time"><i class="far fa-clock mr-1"></i>' + escapeHtml(formatRelativeTime(note.created_at)) + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="note-operations">' +
                            '<button class="operation-btn ai" onclick="openNoteAI(\'' + Number(note.id) + '\')" title="AI助手"><i class="fas fa-robot"></i></button>' +
                            '<button class="operation-btn" onclick="copyNoteContent(\'' + Number(note.id) + '\')" title="复制内容"><i class="fas fa-copy"></i></button>' +
                            actionsHtml +
                        '</div>' +
                    '</div>' +
                    '<div class="note-body">' +
                        (canReadMedia && note.record_path
                            ? '<div class="note-media"><div class="audio-player"><button class="audio-player-btn" onclick="playNoteAudio(\'' + Number(note.id) + '\', this)"><i class="fas fa-play"></i></button><div style="flex: 1"><div style="font-weight: 500; color: var(--gray-700); margin-bottom: 4px;">语音记录</div><div style="font-size: 13px; color: var(--gray-500);">点击播放收听</div></div><audio id="audio-' + Number(note.id) + '" data-api-url="/api/v2/notes/' + Number(note.id) + '/record" preload="metadata"></audio></div></div>'
                            : ''
                        ) +
                        (canReadMedia && note.image_path
                            ? '<div class="note-media"><a href="' + escapeHtml(note.image_path) + '" target="_blank"><img src="' + escapeHtml(note.image_path) + '" alt="笔记图片" class="note-image"></a></div>'
                            : ''
                        ) +
                        '<div class="note-content markdown-content" id="note-content-' + Number(note.id) + '">' + contentHtml + '</div>' +
                    '</div>' +
                '</div>'
            );
        }

        function updateNotesPaginationUI() {
            const pager = document.getElementById('notes-pagination');
            const prevBtn = document.getElementById('notes-prev-btn');
            const nextBtn = document.getElementById('notes-next-btn');
            const pageText = document.getElementById('notes-page-text');
            const pagination = notesState.pagination || {};
            const currentPage = Number(pagination.current_page || 1);
            const lastPage = Number(pagination.last_page || 1);

            if (pageText) {
                pageText.textContent = '第 ' + currentPage + ' / ' + lastPage + ' 页';
            }
            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= lastPage;
            if (pager) {
                pager.style.display = lastPage > 1 ? '' : 'none';
            }
        }

        function bindNoteExpandState() {
            document.querySelectorAll('.note-content').forEach(function(content) {
                const noteId = content.id.replace('note-content-', '');
                const lineHeight = parseInt(getComputedStyle(content).lineHeight || '24', 10);
                const maxLines = 8;
                if (content.scrollHeight > lineHeight * maxLines) {
                    content.classList.add('collapsed');
                    const wrapper = content.parentNode;
                    if (wrapper && !wrapper.querySelector('.expand-btn')) {
                        const expandBtn = document.createElement('button');
                        expandBtn.className = 'expand-btn';
                        expandBtn.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>展开';
                        expandBtn.onclick = function() { toggleNoteExpand(noteId); };
                        wrapper.appendChild(expandBtn);
                    }
                }
            });
        }

        function renderNotesList() {
            const listEl = document.getElementById('notes-list');
            const emptyEl = document.getElementById('notes-empty');
            const totalText = document.getElementById('note-total-text');
            const items = Array.isArray(notesState.items) ? notesState.items : [];
            const total = Number((notesState.pagination || {}).total || 0);

            if (totalText) totalText.textContent = '共 ' + total + ' 条笔记';
            if (!listEl || !emptyEl) return;
            if (!items.length) {
                listEl.innerHTML = '';
                emptyEl.style.display = '';
                updateNotesPaginationUI();
                return;
            }

            emptyEl.style.display = 'none';
            listEl.innerHTML = items.map(buildNoteCardHtml).join('');
            bindNoteExpandState();
            updateNotesPaginationUI();
        }

        function loadNotes(page) {
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return Promise.resolve();
            }
            const params = getNoteQueryParams();
            const requestParams = {
                type: params.type,
                add_content: '',
                source_type: params.source_type,
                source_id: params.source_id,
                tag_id: params.tag_id,
                keyword: params.keyword,
                page: page || params.page || 1
            };
            return apiRequest('GET', '/notes', requestParams).then(function(response) {
                var payloadRoot = response && (response.result || response.data);
                if (!response || response.code != 9999 || !payloadRoot) {
                    throw new Error((response && response.msg) ? response.msg : '加载失败');
                }

                const notesPayload = payloadRoot.notes || {};
                notesState.items = Array.isArray(notesPayload.data)
                    ? notesPayload.data
                    : (Array.isArray(notesPayload) ? notesPayload : []);
                notesState.pagination = {
                    current_page: Number(notesPayload.current_page || 1),
                    last_page: Number(notesPayload.last_page || 1),
                    total: Number(notesPayload.total || notesState.items.length)
                };
                renderNotesList();
            }).catch(function(error) {
                showToast('笔记列表加载失败: ' + (error && error.message ? error.message : '网络错误'), 'error');
            });
        }

        // 提交表单
        function submitProcess(status) {
            if (easymde) {
                document.querySelector('#markdown-editor').value = easymde.value();
            }
            document.getElementById('status_id').value = status;

            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            const payload = {
                name: document.querySelector('#markdown-editor').value || '',
                status: status,
                source_type: document.querySelector('input[name="source_type"]') ? document.querySelector('input[name="source_type"]').value : 0,
                source_id: document.querySelector('input[name="source_id"]') ? document.querySelector('input[name="source_id"]').value : 0,
                fname: document.getElementById('fname') ? document.getElementById('fname').value : '',
                add_image: document.querySelector('input[name="add_image"]') ? document.querySelector('input[name="add_image"]').value : ''
            };

            apiRequest('POST', '/notes', payload).then(function(response) {
                if (response.code == 9999) {
                    showToast('笔记保存成功', 'success');
                    setTimeout(function() {
                        window.location.href = "{{ url('/notes') }}";
                    }, 300);
                } else {
                    showToast('保存失败: ' + (response.msg || '未知错误'), 'error');
                }
            }).catch(function() {
                showToast('保存失败: 网络错误', 'error');
            });
        }

        // 播放笔记音频
        function playNoteAudio(noteId, button) {
            const audio = document.getElementById('audio-' + noteId);
            const icon = button.querySelector('i');

            const startPlay = function() {
                if (audio.paused) {
                    audio.play();
                    icon.className = 'fas fa-pause';
                    button.onclick = function() { pauseNoteAudio(noteId, button); };
                } else {
                    audio.pause();
                    icon.className = 'fas fa-play';
                    button.onclick = function() { playNoteAudio(noteId, button); };
                }
            };

            if (!audio.dataset.loaded && audio.dataset.apiUrl) {
                waitTokenReady().then(function() {
                    if (typeof window.taskApiFetch === 'function') {
                        return window.taskApiFetch(audio.dataset.apiUrl, {
                            method: 'GET'
                        });
                    }

                    const headers = {};
                    const accessToken = getAccessToken();
                    if (accessToken) {
                        headers['Authorization'] = 'Bearer ' + accessToken;
                    }
                    return fetch(audio.dataset.apiUrl, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: headers
                    });
                }).then(function(resp) {
                    if (!resp.ok) {
                        throw new Error('audio fetch failed');
                    }
                    return resp.blob();
                }).then(function(blob) {
                    audio.src = URL.createObjectURL(blob);
                    audio.dataset.loaded = '1';
                    startPlay();
                }).catch(function() {
                    showToast('语音加载失败', 'error');
                });
                return;
            }

            startPlay();
        }

        function pauseNoteAudio(noteId, button) {
            const audio = document.getElementById('audio-' + noteId);
            const icon = button.querySelector('i');

            audio.pause();
            icon.className = 'fas fa-play';
            button.onclick = function() { playNoteAudio(noteId, button); };
        }

        // AI功能
        function summarizeNote(noteId) {
            const content = document.getElementById('note-content-' + noteId).innerText;
            openAskAIModal('note-summary-' + noteId, 'note-content-' + noteId, '请帮我总结这段笔记的主要内容');
        }

        function translateNote(noteId) {
            const content = document.getElementById('note-content-' + noteId).innerText;
            openAskAIModal('note-translate-' + noteId, 'note-content-' + noteId, '请帮我把这段笔记翻译成英文');
        }

        // 删除笔记
        $(document).ready(function(){
            $('#notes-prev-btn').on('click', function() {
                const currentPage = Number((notesState.pagination || {}).current_page || 1);
                if (currentPage > 1) {
                    loadNotes(currentPage - 1);
                }
            });
            $('#notes-next-btn').on('click', function() {
                const currentPage = Number((notesState.pagination || {}).current_page || 1);
                const lastPage = Number((notesState.pagination || {}).last_page || 1);
                if (currentPage < lastPage) {
                    loadNotes(currentPage + 1);
                }
            });

            $(document).off('click', '.delete_note');
            $(document).on('click', '.delete_note', function(){
                if(!confirm('确认要删除此笔记吗？删除后无法恢复。')) return;

                const noteId = $(this).attr('note_value');

                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    return;
                }

                apiRequest('DELETE', '/notes/' + noteId, {}).then(function(response) {
                    if (response.code == 9999) {
                        const currentPage = Number((notesState.pagination || {}).current_page || 1);
                        showToast('笔记删除成功', 'success');
                        loadNotes(currentPage);
                    } else {
                        showToast('删除失败: ' + (response.msg || '未知错误'), 'error');
                    }
                }).catch(function() {
                    showToast('网络错误，请稍后重试', 'error');
                });
            });

            // 点赞笔记
            $(document).on('click', '.like_note', function(){
                const noteId = $(this).attr('note_value');
                const button = $(this);
                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    return;
                }

                button.prop('disabled', true);
                apiRequest('POST', '/notes/' + noteId + '/like', {}).then(function(response) {
                        if (response.code == 9999) {
                            button.html('<i class="fas fa-thumbs-up"></i>');
                            button.attr('title', '已点赞');
                            button.removeClass('like_note');
                            showToast('点赞成功', 'success');
                        } else {
                            showToast('操作失败: ' + (response.msg || '未知错误'), 'error');
                        }
                    }).catch(function() {
                        showToast('网络错误，请稍后重试', 'error');
                    }).finally(function() {
                        button.prop('disabled', false);
                    });
            });
        });
    </script>
@endsection
