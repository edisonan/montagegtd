@extends('layouts.app')

@section('title', '编辑笔记 - 蒙太奇')
@section('description', '编辑您的笔记内容')

@section('content')
    <!-- 引入Markdown编辑器 -->
    <link href="{{ asset('vendor_local/lib/easymde.min.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor_local/lib/easymde.min.js') }}"></script>

    <style>
        /* 整体布局优化 */
        .edit-note-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* 卡片样式优化 */
        .edit-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--gray-100);
        }

        /* 页面头部与卡片融合 */
        .edit-header {
            padding: 24px 28px;
            border-bottom: 1px solid var(--gray-100);
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .header-icon i {
            font-size: 20px;
            color: white;
        }

        .title-text h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .title-text p {
            font-size: 14px;
            color: var(--gray-500);
        }

        /* 状态标签优化 */
        .status-badge-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid transparent;
        }

        .status-private {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #dc2626;
            border-color: #fecaca;
        }

        .status-public {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            color: #16a34a;
            border-color: #bbf7d0;
        }

        /* Markdown编辑器样式优化 */
        .editor-section {
            padding: 28px;
        }

        .section-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label i {
            color: var(--primary-color);
        }

        .note-title-field {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 14px;
            margin-bottom: 20px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            transition: all 0.2s ease;
        }

        .note-title-field:focus-within {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .note-title-field i {
            color: var(--gray-400);
            font-size: 14px;
            flex: 0 0 auto;
        }

        .note-title-field:focus-within i {
            color: var(--primary-color);
        }

        .note-title-field input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--gray-900);
            font-size: 17px;
            font-weight: 600;
            line-height: 1.4;
            padding: 10px 0;
        }

        .note-title-field input::placeholder {
            color: var(--gray-400);
            font-weight: 500;
        }

        .note-title-ai-btn {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            padding: 0 10px;
            border: 0;
            border-radius: 6px;
            background: rgba(139, 92, 246, 0.1);
            color: #6d28d9;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .note-title-ai-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            transform: translateY(-1px);
        }

        .note-title-ai-btn i,
        .note-title-field:focus-within .note-title-ai-btn i {
            color: inherit;
        }

        .editor-toolbar {
            border: 1px solid var(--gray-200) !important;
            border-bottom: none !important;
            border-radius: 8px 8px 0 0;
            background: linear-gradient(to bottom, #ffffff, #f8fafc) !important;
            padding: 12px !important;
        }

        .editor-toolbar button {
            color: var(--gray-600) !important;
            border: 1px solid var(--gray-200) !important;
            background: white !important;
            border-radius: 6px !important;
            margin: 2px !important;
            padding: 8px !important;
        }

        .editor-toolbar button:hover {
            color: var(--primary-color) !important;
            background: #eff6ff !important;
            border-color: var(--primary-color) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
        }

        .editor-toolbar button.active {
            color: var(--primary-color) !important;
            background: #dbeafe !important;
            border-color: var(--primary-color) !important;
        }

        .CodeMirror {
            border: 1px solid var(--gray-200) !important;
            border-radius: 0 0 8px 8px;
            font-family: 'Inter', 'SF Mono', Monaco, Menlo, Consolas, 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            min-height: 320px;
            background: white !important;
            transition: all 0.2s ease;
        }

        .CodeMirror-focused {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        /* 快捷标签优化 */
        .quick-tags-container {
            margin: 20px 0;
        }

        .tags-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .quick-tag {
            padding: 10px 14px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .quick-tag:hover {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .tag-editor {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            padding: 8px 10px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: white;
            transition: all 0.2s ease;
        }

        .tag-editor:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            border: 1px solid rgba(59, 130, 246, 0.25);
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
            font-size: 13px;
            font-weight: 500;
            line-height: 1;
        }

        #note-tags-selected {
            display: contents;
        }

        .tag-chip button {
            border: 0;
            background: transparent;
            color: inherit;
            cursor: pointer;
            padding: 0;
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .tag-chip button:hover {
            background: rgba(59, 130, 246, 0.18);
        }

        .tag-entry-input {
            flex: 1;
            min-width: 140px;
            border: 0;
            outline: 0;
            padding: 4px 2px;
            font-size: 14px;
            color: var(--gray-700);
            background: transparent;
        }

        /* 录音功能优化 */
        .recording-section {
            padding: 24px 28px;
            background: linear-gradient(135deg, #fef2f2, #fecaca, 0.05);
            border-top: 1px solid var(--gray-100);
            border-bottom: 1px solid var(--gray-100);
        }

        .audio-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 16px;
        }

        .record-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .record-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        #start-record {
            background: linear-gradient(135deg, var(--primary-color), #2563eb);
            color: white;
        }

        #start-record:hover:not(:disabled) {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.3);
        }

        #stop-record {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        #stop-record:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            box-shadow: 0 6px 25px rgba(220, 38, 38, 0.3);
        }

        /* 音频播放器优化 */
        .audio-player-card {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 20px;
            margin: 16px 0;
        }

        .audio-player {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .audio-player-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #2563eb);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .audio-player-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
        }

        /* 操作按钮区域 */
        .action-section {
            padding: 24px 28px;
            background: #f8fafc;
            border-top: 1px solid var(--gray-100);
        }

        .action-buttons {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .action-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .action-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* 按钮样式优化 */
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #2563eb);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
            color: var(--gray-700);
            border: none;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, var(--gray-200), var(--gray-300));
            transform: translateY(-1px);
        }

        .ai-assist-btn {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
        }

        .ai-assist-btn:hover {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3);
        }

        /* 操作提示卡片 */
        .tips-card {
            margin-top: 24px;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 1px solid #fcd34d;
            border-radius: 12px;
            padding: 20px;
        }

        .tips-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .tips-header i {
            color: #f59e0b;
            font-size: 18px;
        }

        .tips-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .tip-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 16px;
            border: 1px solid rgba(245, 158, 11, 0.1);
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .edit-header {
                padding: 20px;
            }

            .editor-section,
            .recording-section,
            .action-section {
                padding: 20px;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 16px;
            }

            .action-left,
            .action-right {
                width: 100%;
                justify-content: center;
            }

            .tags-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .tips-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @include('components.ai-ask-modal')

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

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatRelativeTime(value) {
            if (!value) {
                return '刚刚';
            }
            const date = new Date(value.replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            const diffSec = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
            if (diffSec < 60) return '刚刚';
            if (diffSec < 3600) return Math.floor(diffSec / 60) + '分钟前';
            if (diffSec < 86400) return Math.floor(diffSec / 3600) + '小时前';
            if (diffSec < 86400 * 30) return Math.floor(diffSec / 86400) + '天前';
            return value;
        }

        function updateStatusUI(status) {
            const badge = document.getElementById('note-status-badge');
            const statusInput = document.getElementById('status_id');
            const secondaryBtn = document.getElementById('status-secondary-btn');
            const primaryBtn = document.getElementById('status-primary-btn');
            const isPublic = parseInt(status, 10) === 2;

            if (statusInput) {
                statusInput.value = isPublic ? '2' : '1';
            }

            if (badge) {
                badge.className = 'status-badge ' + (isPublic ? 'status-public' : 'status-private');
                badge.innerHTML = '<i class="fas fa-' + (isPublic ? 'globe' : 'lock') + ' mr-2"></i>' + (isPublic ? '公开' : '私密');
            }

            if (secondaryBtn) {
                secondaryBtn.innerHTML = '<i class="fas fa-lock"></i>' + (isPublic ? '设为私密' : '保持私密');
            }
            if (primaryBtn) {
                primaryBtn.innerHTML = '<i class="fas fa-globe"></i>' + (isPublic ? '保持公开' : '设为公开');
            }
        }

        function loadNote() {
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return Promise.resolve();
            }

            return apiRequest('GET', '/notes/' + noteIdFromPath, {}).then(function(response) {
                const payload = response && (response.result || response.data || null);
                if (!response || Number(response.code) !== 9999 || !payload) {
                    throw new Error((response && response.msg) ? response.msg : '加载失败');
                }

                currentNote = payload;
                const noteTitleInput = document.getElementById('note-title-input');
                if (noteTitleInput) {
                    noteTitleInput.value = currentNote.content ? decodeNoteContent(currentNote.name || '') : '';
                }

                const noteContentInput = document.getElementById('note-content');
                if (noteContentInput) {
                    noteContentInput.value = decodeNoteContent(currentNote.content || currentNote.name || '');
                }

                const noteTagsInput = document.getElementById('note-tags-input');
                if (noteTagsInput) {
                    const tagMaps = Array.isArray(currentNote.note_tag_maps) ? currentNote.note_tag_maps : [];
                    noteTagsInput.value = tagMaps.map(function(map) {
                        return map && map.tag && map.tag.name ? map.tag.name : '';
                    }).filter(Boolean).join(', ');
                    renderTagChips();
                }

                const timeNode = document.getElementById('note-updated-time');
                if (timeNode) {
                    timeNode.textContent = formatRelativeTime(currentNote.updated_at);
                }

                updateStatusUI(currentNote.status);
                if (currentNote.record_path) {
                    initExistingAudioPlayer();
                }
            }).catch(function(error) {
                showToast('加载笔记失败: ' + (error && error.message ? error.message : '网络错误'), 'error');
            });
        }

        function decodeNoteContent(value) {
            const html = String(value || '').replace(/<br\s*\/?>/ig, '\n');
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            return textarea.value;
        }

        // 全局变量
        let recorder = null;
        let easymde = null;
        let isRecording = false;
        let timerInterval = null;
        let startTime = null;
        let currentNote = null;
        const noteIdFromPath = (function() {
            const serverNoteId = Number('{{ isset($note_id) ? (int)$note_id : 0 }}' || 0);
            if (serverNoteId > 0) {
                return serverNoteId;
            }
            const parts = (window.location.pathname || '').split('/').filter(Boolean);
            for (let i = parts.length - 1; i >= 0; i--) {
                const parsed = parseInt(parts[i], 10);
                if (Number.isFinite(parsed) && parsed > 0) {
                    return parsed;
                }
            }
            return 0;
        })();

        // 初始化Markdown编辑器
        function initMarkdownEditor() {
            easymde = new EasyMDE({
                element: document.getElementById('markdown-editor'),
                spellChecker: false,
                status: false,
                autosave: {
                    enabled: false
                },
                placeholder: '开始编辑您的笔记...',
                renderingConfig: {
                    singleLineBreaks: false,
                    codeSyntaxHighlighting: true,
                },
                previewClass: ['editor-preview', 'markdown-content'],
                toolbar: [
                    'heading', 'bold', 'italic', 'strikethrough', '|',
                    'quote', 'unordered-list', 'ordered-list', '|',
                    'link', 'image', 'code', 'table', '|',
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
                minHeight: '320px',
                forceSync: true
            });

            // 加载现有内容
            const noteContent = document.getElementById('note-content').value;
            if (noteContent) {
                easymde.value(noteContent);
            }
        }

        function getCurrentTags() {
            const input = document.getElementById('note-tags-input');
            if (!input) return [];

            return input.value
                .split(/[\s,，、]+/)
                .map(function(item) { return item.trim(); })
                .filter(Boolean);
        }

        function splitTags(value) {
            return String(value || '')
                .replace(/#/g, '')
                .split(/[\s,，、;；]+/)
                .map(function(item) { return item.trim(); })
                .filter(Boolean);
        }

        function syncTagInput(tags) {
            const hidden = document.getElementById('note-tags-input');
            if (hidden) hidden.value = tags.join(', ');
        }

        function renderTagChips() {
            const selected = document.getElementById('note-tags-selected');
            if (!selected) return;

            selected.innerHTML = getCurrentTags().map(function(tag, index) {
                return '<span class="tag-chip">' + escapeHtml(tag) +
                    '<button type="button" onclick="removeTagAt(' + index + ')" title="移除标签">' +
                    '<i class="fas fa-times"></i></button></span>';
            }).join('');
        }

        function addTags(value) {
            const current = getCurrentTags();
            splitTags(value).forEach(function(tagName) {
                if (current.indexOf(tagName) === -1) {
                    current.push(tagName);
                }
            });
            syncTagInput(current);
            renderTagChips();
        }

        function addTag(tagName) {
            addTags(tagName);
            const entry = document.getElementById('note-tag-entry');
            if (entry) {
                entry.value = '';
                entry.focus();
            }
        }

        function removeTagAt(index) {
            const tags = getCurrentTags();
            tags.splice(index, 1);
            syncTagInput(tags);
            renderTagChips();
        }

        function flushTagEntry() {
            const entry = document.getElementById('note-tag-entry');
            if (entry && entry.value.trim()) {
                addTags(entry.value);
                entry.value = '';
            }
        }

        function initTagEditor() {
            const entry = document.getElementById('note-tag-entry');
            const editor = document.getElementById('note-tags-editor');
            if (!entry) return;

            renderTagChips();
            entry.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ',' || event.key === '，' || event.key === 'Tab') {
                    event.preventDefault();
                    flushTagEntry();
                } else if (event.key === 'Backspace' && !entry.value) {
                    const tags = getCurrentTags();
                    tags.pop();
                    syncTagInput(tags);
                    renderTagChips();
                }
            });
            entry.addEventListener('paste', function(event) {
                const text = (event.clipboardData || window.clipboardData).getData('text');
                if (splitTags(text).length > 1) {
                    event.preventDefault();
                    addTags(text);
                }
            });
            entry.addEventListener('blur', flushTagEntry);
            if (editor) {
                editor.addEventListener('click', function() {
                    entry.focus();
                });
            }
        }

        function getNoteDraftContent() {
            if (easymde) {
                return easymde.value();
            }

            const editor = document.getElementById('markdown-editor');
            return editor ? editor.value : '';
        }

        function generateNoteTitle() {
            const content = getNoteDraftContent();
            if (!content.trim()) {
                if (typeof showToast === 'function') {
                    showToast('请先填写笔记内容', 'info');
                } else {
                    alert('请先填写笔记内容');
                }
                return;
            }

            const editor = document.getElementById('markdown-editor');
            if (editor) {
                editor.value = content;
            }

            openAskAIModal('markdown-editor', 'note-title-input');
            setTimeout(function() {
                const query = document.getElementById('query');
                if (query) {
                    query.value = '请根据引用的笔记内容生成一个简洁标题。只输出标题，不要解释，不要加引号，20字以内。';
                    query.focus();
                }
            }, 350);
        }

        // 添加快捷内容
        function addContent(type, content) {
            if (!easymde) return;

            switch(type) {
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

        // 提交表单
        function submitProcess(status) {
            flushTagEntry();
            document.getElementById('status_id').value = status;

            const content = easymde ? easymde.value() : '';
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return;
            }

            if (!noteIdFromPath) {
                showToast('未识别到笔记ID', 'error');
                return;
            }

            apiRequest('PUT', '/notes/' + noteIdFromPath, {
                name: document.getElementById('note-title-input') ? document.getElementById('note-title-input').value : '',
                content: content,
                tags: document.getElementById('note-tags-input') ? document.getElementById('note-tags-input').value : '',
                status: status
            }).then(function(response) {
                if (response.code == 9999) {
                    showToast('笔记更新成功', 'success');
                    setTimeout(function() {
                        window.location.href = '{{ url("/notes") }}';
                    }, 300);
                } else {
                    showToast('更新失败: ' + (response.msg || '未知错误'), 'error');
                }
            }).catch(function() {
                showToast('更新失败，请检查网络连接', 'error');
            });
        }

        // 录音功能
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
            return `${mins}:${secs}`;
        }

        function startRecording() {
            if (!recorder) {
                alert('录音功能初始化失败，请刷新页面重试');
                return;
            }

            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function(stream) {
                    recorder.start();

                    document.getElementById('start-record').disabled = true;
                    document.getElementById('stop-record').disabled = false;

                    startTime = new Date();
                    timerInterval = setInterval(function() {
                        const elapsedSeconds = Math.floor((new Date() - startTime) / 1000);
                        document.getElementById('record-timer').textContent = formatTime(elapsedSeconds);
                    }, 1000);

                    isRecording = true;

                    // 显示录音状态
                    document.getElementById('record-status').innerHTML = `
                    <div style="margin-top: 12px; padding: 12px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                        <div style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: pulse 1.5s infinite;"></div>
                        <span style="color: #dc2626; font-weight: 500;">正在录音中...</span>
                        <span id="audio-timer" style="margin-left: auto; color: #dc2626; font-family: monospace;">00:00</span>
                    </div>
                `;
                })
                .catch(function(err) {
                    console.error('麦克风权限被拒绝:', err);
                    alert('需要麦克风权限才能录音。请在浏览器设置中允许麦克风访问。');
                });
        }

        function stopRecording() {
            if (!recorder || !isRecording) return;

            recorder.stop();
            isRecording = false;

            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }

            recorder.getBlob(function(blob) {
                // 添加录音标记到编辑器
                if (easymde) {
                    const timestamp = new Date().toLocaleTimeString();
                    easymde.codemirror.replaceSelection(`\n\n🎤 录音记录 ${timestamp}\n`);
                }

                // 上传音频
                uploadAudioFile(blob);
            });

            document.getElementById('start-record').disabled = false;
            document.getElementById('stop-record').disabled = true;
            document.getElementById('record-status').innerHTML = '';
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

                const headers = {'Accept': 'application/json'};
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
                    showToast('🎤 录音已保存并添加到笔记', 'success');
                });
            }).catch(function() {
                showToast('录音上传失败，请检查网络连接', 'error');
            });
        }

        // 显示提示
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                        type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
            }`;
            toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' :
                type === 'error' ? 'exclamation-circle' :
                    type === 'info' ? 'info-circle' : 'bell'} mr-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                    <i class="fas fa-times"></i>
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

        // 页面初始化
        document.addEventListener('DOMContentLoaded', function() {
            const updateForm = document.getElementById('update_note_form');
            if (updateForm) {
                updateForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const statusVal = parseInt((document.getElementById('status_id') || {}).value || '1', 10);
                    submitProcess(isNaN(statusVal) ? 1 : statusVal);
                });
            }
            if (!noteIdFromPath) {
                showToast('无效的笔记ID', 'error');
                return;
            }
            initTagEditor();

            loadNote().then(function() {
                // 初始化Markdown编辑器
                initMarkdownEditor();
            });

            // 初始化录音器
            try {
                recorder = new Recorder({
                    sampleRate: 44100,
                    bitRate: 128,
                    success: function() {
                        document.getElementById('start-record').disabled = false;
                        document.getElementById('start-record').innerHTML = '<i class="fas fa-microphone"></i>';
                    },
                    error: function(msg) {
                        console.error('录音器初始化失败:', msg);
                        document.getElementById('start-record').disabled = true;
                        document.getElementById('start-record').innerHTML = '<i class="fas fa-microphone-slash"></i>';
                        document.getElementById('start-record').title = '录音功能不可用';
                    }
                });
            } catch (error) {
                console.error('录音器初始化失败:', error);
            }

        });

        // 初始化现有音频播放器
        function initExistingAudioPlayer() {
            const audioUrl = '/api/v2/notes/' + noteIdFromPath + '/record';
            const container = document.getElementById('existing-audio-container');
            const section = document.getElementById('existing-audio-section');
            if (section) {
                section.style.display = '';
            }

            if (container) {
                container.innerHTML = `
                <div class="audio-player-card">
                    <div class="text-sm font-medium text-gray-700 mb-3">📼 已保存的录音</div>
                    <div class="audio-player">
                        <button class="audio-player-btn" onclick="playAudio(this)">
                            <i class="fas fa-play"></i>
                        </button>
                        <div style="flex: 1">
                            <div style="font-weight: 500; color: var(--gray-700);">原有录音</div>
                            <div style="font-size: 13px; color: var(--gray-500);">点击播放按钮试听</div>
                        </div>
                        <audio id="existing-audio" data-api-url="${audioUrl}" preload="metadata"></audio>
                    </div>
                </div>
            `;
            }
        }

        function playAudio(button) {
            const audio = document.getElementById('existing-audio');
            const icon = button.querySelector('i');
            const startPlay = function() {
                if (audio.paused) {
                    audio.play();
                    icon.className = 'fas fa-pause';
                    button.onclick = function() { pauseAudio(button); };

                    audio.onended = function() {
                        icon.className = 'fas fa-play';
                        button.onclick = function() { playAudio(button); };
                    };
                } else {
                    audio.pause();
                    icon.className = 'fas fa-play';
                    button.onclick = function() { playAudio(button); };
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

        function pauseAudio(button) {
            const audio = document.getElementById('existing-audio');
            const icon = button.querySelector('i');

            audio.pause();
            icon.className = 'fas fa-play';
            button.onclick = function() { playAudio(button); };
        }
    </script>

    <div class="edit-note-container">
        <!-- 成功消息 -->
        @include('common.success')

        <!-- 主要编辑卡片 -->
        <div class="edit-card">
            <!-- 页面头部（融合到卡片内） -->
            <div class="edit-header">
                <div class="header-content">
                    <div class="header-title">
                        <div class="header-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="title-text">
                            <h1>编辑笔记</h1>
                            <p>修改您的笔记内容，支持Markdown格式和录音功能</p>
                        </div>
                    </div>
                    <div class="status-badge-container">
                    <span class="status-badge status-private" id="note-status-badge">
                        <i class="fas fa-lock mr-2"></i>
                        私密
                    </span>
                    </div>
                </div>

                <!-- 快速操作 -->
                <div class="flex items-center gap-2 mt-4">
                    <a href="{{ url('/notes') }}" class="btn btn-outline">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回列表
                    </a>
                    <div class="text-sm text-gray-500 ml-4">
                        <i class="fas fa-clock mr-1"></i>
                        最后更新: <span id="note-updated-time">刚刚</span>
                    </div>
                </div>
            </div>

            <!-- 错误提示 -->
            @include('common.errors')

            <!-- 编辑表单 -->
            <form action="#" method="POST" id="update_note_form">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="PUT">

                <!-- 笔记内容编辑区域 -->
                <div class="editor-section">
                    <div class="section-label">
                        <i class="fas fa-heading"></i>
                        笔记标题
                    </div>
                    <div class="note-title-field">
                        <i class="fas fa-heading"></i>
                        <input type="text" id="note-title-input" name="name"
                               placeholder="可选，用一句话概括这条笔记">
                        <button type="button" class="note-title-ai-btn" onclick="generateNoteTitle()" title="AI生成标题">
                            <i class="fas fa-robot"></i>
                            <span>AI生成</span>
                        </button>
                    </div>

                    <div class="section-label">
                        <i class="fas fa-edit"></i>
                        笔记内容
                    </div>

                    <!-- Markdown编辑器 -->
                    <textarea id="markdown-editor" name="content" style="display: none;"></textarea>
                    <input type="hidden" id="note-content" value="">

                    <!-- 快捷标签 -->
                    <div class="quick-tags-container">
                        <div class="text-sm font-medium text-gray-700 mb-2">标签</div>
                        <input type="hidden" id="note-tags-input" name="tags" value="">
                        <div class="tag-editor mb-3" id="note-tags-editor">
                            <div id="note-tags-selected" class="contents"></div>
                            <input type="text" id="note-tag-entry" class="tag-entry-input"
                                   placeholder="输入标签后按回车">
                        </div>
                        <div class="text-sm font-medium text-gray-700 mb-2">快捷标签</div>
                        <div class="tags-grid">
                            <div class="quick-tag" onclick="addTag('每日总结')">
                                <i class="fas fa-tag"></i>
                                每日总结
                            </div>
                            <div class="quick-tag" onclick="addTag('读书笔记')">
                                <i class="fas fa-book"></i>
                                读书笔记
                            </div>
                            <div class="quick-tag" onclick="addTag('工作思考')">
                                <i class="fas fa-briefcase"></i>
                                工作思考
                            </div>
                            <div class="quick-tag" onclick="addTag('灵感闪现')">
                                <i class="fas fa-lightbulb"></i>
                                灵感闪现
                            </div>
                            <div class="quick-tag" onclick="addContent('code', '')">
                                <i class="fas fa-code"></i>
                                代码块
                            </div>
                            <div class="quick-tag" onclick="addContent('heading', '##')">
                                <i class="fas fa-heading"></i>
                                二级标题
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 现有录音 -->
                <div class="recording-section" id="existing-audio-section" style="display:none;">
                    <div id="existing-audio-container"></div>
                </div>

                <!-- 录音功能 -->
                <div class="recording-section">
                    <div class="section-label">
                        <i class="fas fa-microphone"></i>
                        添加新录音
                    </div>
                    <p class="text-sm text-gray-600 mb-4">录制音频并自动插入到笔记中</p>

                    <div class="audio-controls">
                        <button type="button" id="start-record" class="record-btn" onclick="startRecording()" disabled>
                            <i class="fas fa-microphone-slash"></i>
                        </button>
                        <button type="button" id="stop-record" class="record-btn" onclick="stopRecording()" disabled>
                            <i class="fas fa-stop"></i>
                        </button>
                        <div class="text-sm text-gray-500">
                            确保麦克风权限已开启
                        </div>
                    </div>
                    <div id="record-status"></div>
                </div>

                <!-- 隐藏字段 -->
                <input type="hidden" name="status" id="status_id" value="1">
                <input type="hidden" name="fname" id="fname" value="">

                <!-- 表单操作 -->
                <div class="action-section">
                    <div class="action-buttons">
                        <div class="action-left">
                            <button type="button" class="btn ai-assist-btn" onclick="openAskAIModal('markdown-editor', 'markdown-editor', '请帮我优化这段Markdown内容')">
                                <i class="fas fa-robot"></i>
                                AI润色
                            </button>
                        </div>

                        <div class="action-right">
                            <button type="button" class="btn btn-secondary" onclick="submitProcess(1)" id="status-secondary-btn">
                                <i class="fas fa-lock"></i>
                                保持私密
                            </button>
                            <button type="button" class="btn btn-primary" onclick="submitProcess(2)" id="status-primary-btn">
                                <i class="fas fa-globe"></i>
                                设为公开
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- 操作提示 -->
        <div class="tips-card">
            <div class="tips-header">
                <i class="fas fa-lightbulb"></i>
                <h3 class="text-sm font-medium text-gray-900">使用提示</h3>
            </div>
            <div class="tips-grid">
                <div class="tip-item">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-edit text-blue-600 text-xs"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Markdown编辑</span>
                    </div>
                    <p class="text-xs text-gray-600">支持标题、列表、代码块等格式，提升可读性</p>
                </div>
                <div class="tip-item">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-robot text-purple-600 text-xs"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900">AI助手</span>
                    </div>
                    <p class="text-xs text-gray-600">使用AI润色功能优化您的笔记内容</p>
                </div>
            </div>
        </div>
    </div>
@endsection
