@extends('layouts.app')

@section('title', '笔记管理 - 蒙太奇')
@section('description', '管理和编辑您的笔记')

@section('content')
    @include('components.ai-ask-modal')

    <link href="https://unpkg.com/easymde/dist/easymde.min.css" rel="stylesheet">
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <script src="/js/marked.min.js"></script>
    <script src="/plugins/purify/purify.min.js"></script>

    <style>
        .notes-workspace {
            height: calc(100vh - 96px);
            min-height: 680px;
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            border: 1px solid var(--gray-200);
            background: white;
            overflow: hidden;
            min-width: 0;
        }

        .notes-sidebar {
            border-right: 1px solid var(--gray-200);
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
        }

        .notes-sidebar-header {
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            background: white;
        }

        .notes-sidebar-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .notes-sidebar-title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: white;
            color: var(--gray-700);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .icon-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(74, 144, 226, 0.08);
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 13px;
        }

        .search-box input {
            width: 100%;
            height: 38px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0 12px 0 34px;
            outline: 0;
            font-size: 14px;
            color: var(--gray-800);
            background: #f8fafc;
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.12);
        }

        .notes-list {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 8px;
            overscroll-behavior: contain;
        }

        .note-list-item {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            border-radius: 8px;
            padding: 12px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .note-list-item:hover {
            background: white;
            border-color: var(--gray-200);
        }

        .note-list-item.active {
            background: white;
            border-color: rgba(74, 144, 226, 0.35);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
        }

        .note-list-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.35;
            margin-bottom: 6px;
        }

        .note-list-title span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .note-list-preview {
            color: var(--gray-500);
            font-size: 13px;
            line-height: 1.45;
            height: 38px;
            overflow: hidden;
        }

        .note-list-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
            color: var(--gray-400);
            font-size: 12px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            flex: 0 0 auto;
            cursor: help;
        }

        .status-dot.public {
            background: #22c55e;
        }

        .notes-sidebar-footer {
            padding: 10px 12px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: white;
        }

        .pager-btn {
            height: 32px;
            padding: 0 10px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: white;
            color: var(--gray-700);
            font-size: 13px;
            cursor: pointer;
        }

        .pager-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .note-editor {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
        }

        .editor-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 60px;
            padding: 12px 18px;
            border-bottom: 1px solid var(--gray-200);
            background: white;
        }

        .editor-state {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1;
            color: var(--gray-500);
            font-size: 13px;
        }

        .editor-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: white;
            color: var(--gray-700);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(74, 144, 226, 0.08);
        }

        .btn-primary {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            color: white;
            background: #2563eb;
        }

        .btn-danger:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.08);
        }

        /* 导出菜单 */
        .btn.export-group {
            position: relative;
        }

        .btn.export-group .export-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            min-width: 170px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            padding: 6px;
            z-index: 300;
        }

        .btn.export-group:hover .export-menu,
        .btn.export-group.open .export-menu,
        .btn.export-group:focus-within .export-menu {
            display: block;
        }

        .btn.export-group .export-menu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .btn.export-group .export-menu-item i {
            width: 16px;
            text-align: center;
            color: var(--primary-color);
            font-size: 14px;
        }

        .btn.export-group .export-menu-item:hover {
            background: var(--gray-50);
            color: var(--primary-color);
        }

        .editor-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 22px;
            overscroll-behavior: contain;
        }

        .title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .note-title-field {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 14px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            transition: all 0.2s ease;
        }

        .note-title-field:focus-within {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.12);
        }

        .note-title-field i {
            color: var(--gray-400);
            font-size: 14px;
            flex: 0 0 auto;
        }

        .note-title-field input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--gray-900);
            font-size: 18px;
            font-weight: 700;
            line-height: 1.4;
            padding: 10px 0;
        }

        .note-title-ai-btn {
            flex: 0 0 auto;
            min-height: 32px;
            padding: 0 10px;
            border: 0;
            border-radius: 7px;
            background: rgba(102, 126, 234, 0.1);
            color: #5b5fc7;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .note-title-ai-btn:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .field-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--gray-700);
            margin: 16px 0 8px;
        }

        .tag-editor {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            padding: 8px 10px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: white;
        }

        .tag-editor:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.12);
        }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            border: 1px solid rgba(74, 144, 226, 0.25);
            background: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
            font-size: 13px;
            font-weight: 600;
        }

        .tag-chip button {
            border: 0;
            background: transparent;
            color: inherit;
            cursor: pointer;
            padding: 0;
        }

        #note-tags-selected {
            display: contents;
        }

        .tag-entry-input {
            flex: 1;
            min-width: 150px;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--gray-700);
            padding: 4px 2px;
        }

        .quick-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .quick-tag {
            border: 1px solid var(--gray-200);
            background: #f8fafc;
            border-radius: 999px;
            padding: 5px 10px;
            color: var(--gray-600);
            font-size: 12px;
            cursor: pointer;
        }

        .quick-tag:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(74, 144, 226, 0.08);
        }

        .status-toggle {
            display: inline-flex;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafc;
        }

        .status-toggle button {
            min-height: 34px;
            padding: 0 12px;
            border: 0;
            background: transparent;
            color: var(--gray-600);
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
        }

        .status-toggle button.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }

        .editor-toolbar {
            border: 1px solid var(--gray-200) !important;
            border-bottom: none !important;
            border-radius: 8px 8px 0 0;
            background: #f8fafc !important;
        }

        .CodeMirror {
            border: 1px solid var(--gray-200) !important;
            border-radius: 0 0 8px 8px;
            height: calc(100vh - 390px) !important;
            min-height: 360px;
            font-size: 14px;
            line-height: 1.6;
        }

        .CodeMirror-scroll {
            min-height: 360px;
        }

        .empty-state {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            text-align: center;
            padding: 24px;
        }

        @media (max-width: 900px) {
            .notes-workspace {
                height: auto;
                min-height: 0;
                grid-template-columns: 1fr;
            }

            .notes-sidebar {
                height: 360px;
                border-right: 0;
                border-bottom: 1px solid var(--gray-200);
            }

            .CodeMirror {
                height: 420px !important;
            }

            .editor-topbar,
            .title-row {
                align-items: stretch;
                flex-direction: column;
            }

            .editor-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="notes-workspace">
            <aside class="notes-sidebar">
                <div class="notes-sidebar-header">
                    <div class="notes-sidebar-title">
                        <h1>笔记</h1>
                        <button type="button" class="icon-btn" onclick="createNewNote()" title="新建笔记">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="search" id="note-search-input" placeholder="搜索标题或正文">
                    </div>
                </div>
                <div class="notes-list" id="notes-list"></div>
                <div class="notes-sidebar-footer">
                    <button type="button" class="pager-btn" id="notes-prev-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="notes-page-label" class="text-sm text-gray-500">1 / 1</span>
                    <button type="button" class="pager-btn" id="notes-next-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </aside>

            <main class="note-editor">
                <div class="editor-topbar">
                    <div class="editor-state" id="editor-state">
                        <div class="note-title-field">
                            <i class="fas fa-heading"></i>
                            <input type="text" id="note-title-input" placeholder="可选，用一句话概括这条笔记">
                            <button type="button" class="note-title-ai-btn" onclick="generateNoteTitle()">
                                <i class="fas fa-robot"></i>
                                <span>AI生成</span>
                            </button>
                        </div>
                    </div>
                    <div class="editor-actions">
                        <div class="status-toggle">
                            <button type="button" id="status-private-btn" class="active" onclick="setNoteStatus(1)">
                                <i class="fas fa-lock"></i> 私密
                            </button>
                            <button type="button" id="status-public-btn" onclick="setNoteStatus(2)">
                                <i class="fas fa-globe"></i> 公开
                            </button>
                        </div>
                        <button type="button" class="btn" onclick="createNewNote()">
                            <i class="fas fa-file"></i> 新建
                        </button>
                        <button type="button" class="btn btn-danger" id="delete-note-btn" onclick="deleteCurrentNote()" style="display:none;">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                        <button type="button" class="btn" id="share-link-btn" onclick="copyShareLink()" style="display:none;" title="复制公开分享链接">
                            <i class="fas fa-link"></i> 分享链接
                        </button>
                        <div class="btn export-group" id="export-group-btn" title="导出笔记">
                            <i class="fas fa-download"></i> 导出
                            <div class="export-menu">
                                <div class="export-menu-item" onclick="exportCurrentNote('md')"><i class="fab fa-markdown"></i>导出 Markdown</div>
                                <div class="export-menu-item" onclick="exportCurrentNote('html')"><i class="fas fa-code"></i>导出 HTML</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveCurrentNote()">
                            <i class="fas fa-save"></i> 保存
                        </button>
                    </div>
                </div>

                <div class="editor-body">
                    <div class="field-label">正文</div>
                    <textarea id="markdown-editor"></textarea>

                    <div class="field-label">标签</div>
                    <input type="hidden" id="note-tags-input" value="">
                    <div class="tag-editor" id="note-tags-editor">
                        <div id="note-tags-selected"></div>
                        <input type="text" id="note-tag-entry" class="tag-entry-input" placeholder="输入标签后按回车">
                    </div>
                    <div class="quick-tags">
                        <button type="button" class="quick-tag" onclick="addTag('每日小计划')">每日小计划</button>
                        <button type="button" class="quick-tag" onclick="addTag('读书笔记')">读书笔记</button>
                        <button type="button" class="quick-tag" onclick="addTag('工作思考')">工作思考</button>
                        <button type="button" class="quick-tag" onclick="addTag('灵感闪现')">灵感闪现</button>
                        <button type="button" class="quick-tag" onclick="addTag('会议记录')">会议记录</button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const CURRENT_USER_ID = Number('{{ Auth::id() }}' || 0);
        let apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        let easymde = null;
        let currentNoteId = null;
        let currentStatus = 1;
        let notesLoading = false;
        let notesState = {
            items: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 0
            }
        };

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function decodeNoteContent(value) {
            const html = String(value || '').replace(/<br\s*\/?>/ig, '\n');
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            return textarea.value;
        }

        function stripHtml(value) {
            const div = document.createElement('div');
            div.innerHTML = String(value || '').replace(/<br\s*\/?>/ig, '\n');
            return (div.textContent || div.innerText || '').trim();
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

        function getNoteTitle(note) {
            if (note.content && note.name) return stripHtml(note.name);
            const content = stripHtml(note.content || note.name || '');
            return content ? content.slice(0, 28) : '未命名笔记';
        }

        function getNoteContent(note) {
            return note.content ? decodeNoteContent(note.content) : decodeNoteContent(note.name || '');
        }

        function initMarkdownEditor() {
            easymde = new EasyMDE({
                element: document.getElementById('markdown-editor'),
                spellChecker: false,
                status: false,
                autosave: {
                    enabled: false
                },
                placeholder: '开始记录...',
                renderingConfig: {
                    singleLineBreaks: false,
                    codeSyntaxHighlighting: true,
                },
                previewClass: ['editor-preview', 'markdown-content'],
                toolbar: [
                    'heading', 'bold', 'italic', 'strikethrough', '|',
                    'quote', 'unordered-list', 'ordered-list', '|',
                    'link', 'image', 'code', 'table', '|',
                    'preview', 'side-by-side', 'fullscreen'
                ],
                sideBySideFullscreen: false,
                minHeight: '420px',
                forceSync: true
            });
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
            document.getElementById('note-tags-input').value = tags.join(', ');
        }

        function renderTagChips() {
            const selected = document.getElementById('note-tags-selected');
            selected.innerHTML = getCurrentTags().map(function(tag, index) {
                return '<span class="tag-chip">' + escapeHtml(tag) +
                    '<button type="button" onclick="removeTagAt(' + index + ')" title="移除标签">' +
                    '<i class="fas fa-times"></i></button></span>';
            }).join('');
        }

        function addTags(value) {
            const current = getCurrentTags();
            splitTags(value).forEach(function(tagName) {
                if (current.indexOf(tagName) === -1) current.push(tagName);
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
            editor.addEventListener('click', function() {
                entry.focus();
            });
        }

        function setNoteStatus(status) {
            currentStatus = Number(status) === 2 ? 2 : 1;
            document.getElementById('status-private-btn').classList.toggle('active', currentStatus === 1);
            document.getElementById('status-public-btn').classList.toggle('active', currentStatus === 2);
            updateShareBtn();
        }

        // 更新分享链接按钮的显隐：已保存且状态为公开时显示
        function updateShareBtn() {
            const btn = document.getElementById('share-link-btn');
            if (btn) {
                btn.style.display = (currentNoteId && currentStatus === 2) ? '' : 'none';
            }
        }

        // 生成并复制公开分享链接（后端生成随机码 + 随机访问密码，链接里带 key）
        function copyShareLink() {
            if (!currentNoteId) {
                showToast('请先保存笔记，再复制分享链接', 'warning');
                return;
            }
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
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

            function done(copied) {
                if (copied) {
                    showToast('分享链接已复制（含访问密码）', 'success');
                } else {
                    showToast('复制失败，请手动复制', 'error');
                }
            }

            function copyUrl(url) {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText(url).then(function() {
                        done(true);
                    }).catch(function() {
                        done(fallbackCopy(url));
                    });
                    return;
                }
                done(fallbackCopy(url));
            }

            apiRequest('POST', '/notes/' + currentNoteId + '/share').then(function(response) {
                const payload = response && (response.result || response.data);
                if (!response || Number(response.code) !== 9999 || !payload || !payload.url) {
                    throw new Error((response && response.msg) ? response.msg : '生成分享链接失败');
                }
                copyUrl(String(payload.url));
            }).catch(function(error) {
                showToast('生成分享链接失败: ' + (error && error.message ? error.message : '网络错误'), 'error');
            });
        }

        function setEditorState(text, iconClass) {
            const editorState = document.getElementById('editor-state');
            if (editorState) {
                editorState.dataset.stateText = text || '';
                editorState.title = text || '';
            }
        }

        function createNewNote() {
            currentNoteId = null;
            document.getElementById('note-title-input').value = '';
            document.getElementById('note-tags-input').value = '';
            document.getElementById('note-tag-entry').value = '';
            renderTagChips();
            setNoteStatus(1);
            if (easymde) easymde.value('');
            document.getElementById('delete-note-btn').style.display = 'none';
            setEditorState('新建笔记', 'fas fa-circle text-gray-300');
            renderNotesList();
        }

        function openNote(noteId) {
            const note = notesState.items.find(function(item) {
                return Number(item.id) === Number(noteId);
            });
            if (!note) return;

            currentNoteId = Number(note.id);
            document.getElementById('note-title-input').value = note.content ? decodeNoteContent(note.name || '') : '';
            document.getElementById('note-tags-input').value = (Array.isArray(note.note_tag_maps) ? note.note_tag_maps : []).map(function(map) {
                return map && map.tag && map.tag.name ? map.tag.name : '';
            }).filter(Boolean).join(', ');
            document.getElementById('note-tag-entry').value = '';
            renderTagChips();
            setNoteStatus(Number(note.status || 1));
            if (easymde) easymde.value(getNoteContent(note));
            document.getElementById('delete-note-btn').style.display = '';
            setEditorState('最后更新 ' + formatRelativeTime(note.updated_at), 'fas fa-circle text-green-400');
            renderNotesList();
        }

        function renderNotesList() {
            const list = document.getElementById('notes-list');
            if (notesLoading) {
                list.innerHTML = '<div class="empty-state"><div><i class="fas fa-spinner fa-spin text-3xl mb-3"></i><div>加载中...</div></div></div>';
                return;
            }
            const items = notesState.items.filter(function(note) {
                return Number(note.user_id || 0) === CURRENT_USER_ID;
            });

            if (!items.length) {
                list.innerHTML = '<div class="empty-state"><div><i class="fas fa-book-open text-3xl mb-3"></i><div>还没有笔记</div></div></div>';
                return;
            }

            list.innerHTML = items.map(function(note) {
                const active = Number(note.id) === Number(currentNoteId);
                const title = getNoteTitle(note);
                const preview = stripHtml(note.content || note.name || '');
                const isPublic = Number(note.status || 1) === 2;
                const statusLabel = isPublic ? '公开' : '私密';
                return '<button type="button" class="note-list-item ' + (active ? 'active' : '') + '" onclick="openNote(' + Number(note.id) + ')">' +
                    '<div class="note-list-title"><span class="status-dot ' + (isPublic ? 'public' : '') + '" title="' + statusLabel + '" aria-label="' + statusLabel + '"></span><i class="fas fa-file-alt text-gray-400"></i><span>' + escapeHtml(title) + '</span></div>' +
                    '<div class="note-list-preview">' + escapeHtml(preview || '空笔记') + '</div>' +
                    '<div class="note-list-meta"><span>' + escapeHtml(formatRelativeTime(note.updated_at || note.created_at)) + '</span></div>' +
                    '</button>';
            }).join('');
        }

        function updatePaginationUI() {
            const pagination = notesState.pagination || {};
            const current = Number(pagination.current_page || 1);
            const last = Number(pagination.last_page || 1);
            document.getElementById('notes-page-label').textContent = current + ' / ' + last;
            document.getElementById('notes-prev-btn').disabled = current <= 1;
            document.getElementById('notes-next-btn').disabled = current >= last;
        }

        function loadNotes(page) {
            if (!apiRequest) {
                showToast('API客户端未初始化', 'error');
                return Promise.resolve();
            }

            const keyword = document.getElementById('note-search-input').value || '';
            notesLoading = true;
            renderNotesList();
            return apiRequest('GET', '/notes', {
                keyword: keyword,
                page: page || 1
            }).then(function(response) {
                const payloadRoot = response && (response.result || response.data);
                if (!response || Number(response.code) !== 9999 || !payloadRoot) {
                    throw new Error((response && response.msg) ? response.msg : '加载失败');
                }

                const notesPayload = payloadRoot.notes || {};
                notesState.items = Array.isArray(notesPayload.data) ? notesPayload.data : [];
                notesState.pagination = {
                    current_page: Number(notesPayload.current_page || 1),
                    last_page: Number(notesPayload.last_page || 1),
                    total: Number(notesPayload.total || notesState.items.length)
                };
                notesLoading = false;
                renderNotesList();
                updatePaginationUI();
            }).catch(function(error) {
                notesLoading = false;
                renderNotesList();
                showToast('笔记加载失败: ' + (error && error.message ? error.message : '网络错误'), 'error');
            });
        }

        function saveCurrentNote() {
            flushTagEntry();
            const title = document.getElementById('note-title-input').value || '';
            const content = easymde ? easymde.value() : '';

            if (!title.trim() && !content.trim()) {
                showToast('请填写标题或正文', 'warning');
                return;
            }

            const payload = {
                name: title,
                content: content,
                tags: document.getElementById('note-tags-input').value || '',
                status: currentStatus
            };
            const method = currentNoteId ? 'PUT' : 'POST';
            const url = currentNoteId ? '/notes/' + currentNoteId : '/notes';

            let savedNoteId = currentNoteId;
            apiRequest(method, url, payload).then(function(response) {
                if (!response || Number(response.code) !== 9999) {
                    throw new Error((response && response.msg) ? response.msg : '保存失败');
                }

                const result = response.result || response.data || null;
                if (result && result.id) {
                    savedNoteId = Number(result.id);
                }
                showToast('笔记已保存', 'success');
                const page = method === 'POST' ? 1 : Number((notesState.pagination || {}).current_page || 1);
                return loadNotes(page);
            }).then(function() {
                if (savedNoteId) {
                    currentNoteId = savedNoteId;
                    openNote(savedNoteId);
                } else if (notesState.items.length) {
                    openNote(notesState.items[0].id);
                }
            }).catch(function(error) {
                showToast('保存失败: ' + (error && error.message ? error.message : '网络错误'), 'error');
            });
        }

        function buildSafeFilename(name) {
            const cleaned = String(name || '')
                .replace(/[\\/:*?"<>|\r\n\t]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
            return cleaned || '笔记';
        }

        function downloadTextFile(filename, content, mime) {
            const blob = new Blob([content], { type: mime + ';charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
        }

        function exportHtmlDocument(title, bodyHtml) {
            return '<!DOCTYPE html>\n' +
                '<html lang="zh-CN">\n' +
                '<head>\n' +
                '  <meta charset="utf-8">\n' +
                '  <meta name="viewport" content="width=device-width, initial-scale=1.0">\n' +
                '  <title>' + escapeHtml(title) + '</title>\n' +
                '  <style>\n' +
                '    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; line-height: 1.8; color: #1f2937; max-width: 860px; margin: 0 auto; padding: 40px 24px; }\n' +
                '    h1, h2, h3, h4 { color: #111827; margin-top: 1.5em; margin-bottom: 0.5em; font-weight: 600; line-height: 1.3; }\n' +
                '    h1 { font-size: 1.875rem; } h2 { font-size: 1.5rem; } h3 { font-size: 1.25rem; }\n' +
                '    p { margin: 1em 0; } ul, ol { padding-left: 1.5em; margin: 1em 0; } li { margin: 0.5em 0; }\n' +
                '    blockquote { border-left: 4px solid #4a90e2; margin: 1.5em 0; padding: 0.5em 1em; background: #f9fafb; border-radius: 0 8px 8px 0; color: #4b5563; }\n' +
                '    code { background: #f3f4f6; color: #1f2937; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.9em; }\n' +
                '    pre { background: #111827; color: #f3f4f6; padding: 1.25em; border-radius: 8px; overflow-x: auto; margin: 1.5em 0; }\n' +
                '    pre code { background: transparent; color: inherit; padding: 0; }\n' +
                '    table { width: 100%; border-collapse: collapse; margin: 1.5em 0; }\n' +
                '    th, td { padding: 0.75em 1em; border: 1px solid #e5e7eb; text-align: left; }\n' +
                '    th { background: #f9fafb; font-weight: 600; }\n' +
                '    img { max-width: 100%; height: auto; border-radius: 8px; }\n' +
                '  </style>\n' +
                '</head>\n' +
                '<body>\n' +
                '  <h1>' + escapeHtml(title) + '</h1>\n' +
                '  <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 1.5em 0;">\n' +
                '  ' + bodyHtml + '\n' +
                '</body>\n' +
                '</html>\n';
        }

        function renderExportMarkdownHtml(markdown) {
            let html;
            try {
                if (window.marked && typeof window.marked.parse === 'function') {
                    html = window.marked.parse(markdown);
                } else if (window.marked && typeof window.marked === 'function') {
                    html = window.marked(markdown);
                } else {
                    html = escapeHtml(markdown).replace(/\n/g, '<br>');
                }
                if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
                    html = window.DOMPurify.sanitize(html);
                }
            } catch (e) {
                html = escapeHtml(markdown).replace(/\n/g, '<br>');
            }
            return html;
        }

        // 导出当前正在编辑的笔记（markdown 或 html）
        function exportCurrentNote(format) {
            const title = (document.getElementById('note-title-input') || {}).value || '';
            const markdown = easymde ? easymde.value() : '';

            if (!title.trim() && !markdown.trim()) {
                showToast('当前笔记为空，无法导出', 'warning');
                return;
            }

            const filename = buildSafeFilename(title || markdown.slice(0, 28));
            const timeSuffix = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);

            if (format === 'md') {
                downloadTextFile(filename + '.md', markdown, 'text/markdown');
            } else if (format === 'html') {
                const bodyHtml = renderExportMarkdownHtml(markdown);
                const html = exportHtmlDocument(filename + ' - ' + timeSuffix, bodyHtml);
                downloadTextFile(filename + '.html', html, 'text/html');
            } else {
                showToast('不支持的导出格式', 'error');
                return;
            }

            showToast('笔记已导出为 ' + String(format).toUpperCase(), 'success');
        }

        // 点击页面其他位置时关闭导出菜单
        document.addEventListener('click', function(event) {
            if (!event.target.closest || !event.target.closest('.export-group')) {
                document.querySelectorAll('.btn.export-group.open').forEach(function(el) {
                    el.classList.remove('open');
                });
            }
        });

        function deleteCurrentNote() {
            if (!currentNoteId) return;
            if (!confirm('确认删除这条笔记吗？')) return;

            apiRequest('DELETE', '/notes/' + currentNoteId, {}).then(function(response) {
                if (!response || Number(response.code) !== 9999) {
                    throw new Error((response && response.msg) ? response.msg : '删除失败');
                }

                showToast('笔记已删除', 'success');
                createNewNote();
                return loadNotes(Number((notesState.pagination || {}).current_page || 1));
            }).catch(function(error) {
                showToast('删除失败: ' + (error && error.message ? error.message : '网络错误'), 'error');
            });
        }

        function generateNoteTitle() {
            const content = easymde ? easymde.value() : '';
            if (!content.trim()) {
                showToast('请先填写笔记内容', 'info');
                return;
            }

            document.getElementById('markdown-editor').value = content;
            openAskAIModal('markdown-editor', 'note-title-input');
            setTimeout(function() {
                const query = document.getElementById('query');
                if (query) {
                    query.value = '请根据引用的笔记内容生成一个简洁标题。只输出标题，不要解释，不要加引号，20字以内。';
                    query.focus();
                }
            }, 350);
        }

        function showToast(message, type) {
            type = type || 'info';
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm';
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            toast.classList.add(colors[type] || colors.info);
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(function() {
                if (toast.parentNode) toast.remove();
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            initMarkdownEditor();
            initTagEditor();
            createNewNote();
            loadNotes(1);

            let searchTimer = null;
            document.getElementById('note-search-input').addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    loadNotes(1);
                }, 250);
            });
            document.getElementById('notes-prev-btn').addEventListener('click', function() {
                const current = Number((notesState.pagination || {}).current_page || 1);
                if (current > 1) loadNotes(current - 1);
            });
            document.getElementById('notes-next-btn').addEventListener('click', function() {
                const current = Number((notesState.pagination || {}).current_page || 1);
                const last = Number((notesState.pagination || {}).last_page || 1);
                if (current < last) loadNotes(current + 1);
            });
        });
    </script>
@endsection
