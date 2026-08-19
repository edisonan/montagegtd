<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $note ? (trim($note->name) !== '' ? e($note->name) : '笔记详情') : '笔记详情' }} - 蒙太奇</title>
    <meta name="description" content="蒙太奇笔记详情">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/hybrid-api-client.js"></script>
    <script src="/js/marked.min.js"></script>
    <script src="/plugins/purify/purify.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: #f5f6f8;
            color: #1f2937;
            min-height: 100vh;
            line-height: 1.7;
        }
        .wrap { max-width: 1080px; margin: 0 auto; padding: 40px 20px 64px; }
        .brand {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            color: #9ca3af; font-size: 14px; margin-bottom: 20px;
        }
        .brand .brand-link { display: flex; align-items: center; gap: 8px; color: #9ca3af; text-decoration: none; }
        .brand i { color: #4a90e2; }
        .brand .go-back {
            display: inline-flex; align-items: center; gap: 6px;
            color: #4b5563; text-decoration: none; font-size: 13px; font-weight: 500;
            padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
            background: #fff; transition: all .15s;
        }
        .brand .go-back:hover { color: #4a90e2; border-color: #4a90e2; }
        .card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 40px 48px;
        }
        .meta {
            display: flex; align-items: center; gap: 12px;
            padding-bottom: 18px; margin-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }
        .avatar {
            width: 42px; height: 42px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 18px; font-weight: 600; flex-shrink: 0;
        }
        .author { font-size: 15px; font-weight: 600; color: #111827; }
        .time { font-size: 13px; color: #9ca3af; margin-top: 2px; }
        .status-badge {
            margin-left: auto;
            font-size: 12px; padding: 4px 10px; border-radius: 999px; white-space: nowrap;
        }
        .status-badge.public { color: #047857; background: #d1fae5; }
        .status-badge.private { color: #b45309; background: #fef3c7; }
        .title { font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 14px; }
        .tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
        .tag {
            font-size: 12px; color: #4a90e2; background: #eaf2fe;
            padding: 3px 10px; border-radius: 999px; text-decoration: none;
        }
        .content { font-size: 15px; color: #374151; word-break: break-word; }
        .content h1, .content h2, .content h3, .content h4 { margin: 18px 0 10px; color: #111827; }
        .content h1 { font-size: 22px; } .content h2 { font-size: 19px; } .content h3 { font-size: 17px; }
        .content p { margin: 10px 0; }
        .content ul, .content ol { margin: 10px 0 10px 22px; }
        .content blockquote {
            border-left: 4px solid #4a90e2; background: #f8fafc;
            padding: 10px 14px; margin: 12px 0; color: #6b7280; border-radius: 0 8px 8px 0;
        }
        .content code {
            background: #f3f4f6; border-radius: 4px; padding: 2px 6px;
            font-family: 'SF Mono', Consolas, monospace; font-size: 13px;
        }
        .content pre {
            background: #1e293b; color: #e2e8f0; border-radius: 8px;
            padding: 14px 16px; overflow-x: auto; margin: 12px 0;
        }
        .content pre code { background: transparent; color: inherit; padding: 0; }
        .content a { color: #4a90e2; }
        .content img { max-width: 100%; border-radius: 8px; margin: 12px 0; }
        .content table { border-collapse: collapse; margin: 12px 0; }
        .content th, .content td { border: 1px solid #e5e7eb; padding: 6px 12px; }
        .note-image { max-width: 100%; border-radius: 10px; margin-top: 18px; }

        /* 作者操作区 */
        .actions {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            margin-top: 24px; padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        .action-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 500;
            border: 1px solid #e5e7eb; background: #fff; color: #4b5563;
            cursor: pointer; text-decoration: none; transition: all .15s;
        }
        .action-btn:hover { color: #4a90e2; border-color: #4a90e2; }
        .action-btn.primary { background: #4a90e2; border-color: #4a90e2; color: #fff; }
        .action-btn.primary:hover { background: #3b82d6; }
        .action-btn.danger:hover { color: #ef4444; border-color: #ef4444; }

        /* 导出下拉 */
        .export-group { position: relative; }
        .export-menu {
            display: none; position: absolute; top: calc(100% + 6px); left: 0;
            min-width: 172px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12); padding: 6px; z-index: 300;
        }
        .export-group:hover .export-menu, .export-group.open .export-menu, .export-group:focus-within .export-menu { display: block; }
        .export-menu-item {
            display: flex; align-items: center; gap: 8px;
            padding: 9px 12px; border-radius: 6px; font-size: 13px; font-weight: 500;
            color: #4b5563; cursor: pointer; white-space: nowrap; transition: all .15s;
        }
        .export-menu-item i { width: 16px; text-align: center; color: #4a90e2; font-size: 14px; }
        .export-menu-item:hover { background: #f9fafb; color: #4a90e2; }

        .footer { text-align: center; margin-top: 24px; font-size: 13px; color: #b0b8c4; }
        .footer a { color: #9ca3af; text-decoration: none; }
        .toast {
            position: fixed; top: 20px; right: 20px; padding: 12px 20px;
            border-radius: 8px; color: #fff; font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15); z-index: 10000;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">
            <a class="brand-link" href="{{ url('/notes') }}">
                <i class="fas fa-bolt"></i> 蒙太奇
            </a>
            <a class="go-back" href="{{ url('/notes') }}">
                <i class="fas fa-arrow-left"></i> 返回笔记列表
            </a>
        </div>

        <div class="card">
            <div class="meta">
                <div class="avatar" style="background: linear-gradient(135deg, {{ $note->user && $note->user->color_1 ? e($note->user->color_1) : '#4a90e2' }}, {{ $note->user && $note->user->color_2 ? e($note->user->color_2) : '#8a6cff' }})">{{ $note->user && trim($note->user->name) !== '' ? e(mb_substr($note->user->name, 0, 1)) : '用' }}</div>
                <div>
                    <div class="author">{{ $note->user ? e($note->user->name) : '用户' }}</div>
                    <div class="time"><i class="far fa-clock"></i> {{ date('Y-m-d H:i', strtotime($note->created_at)) }}</div>
                </div>
                @if($isPublic)
                    <span class="status-badge public"><i class="fas fa-globe"></i> 公开笔记</span>
                @else
                    <span class="status-badge private"><i class="fas fa-lock"></i> 私密笔记</span>
                @endif
            </div>

            @if(trim($note->name) !== '')
                <h1 class="title">{{ e($note->name) }}</h1>
            @endif

            @if($note->noteTagMaps && count($note->noteTagMaps) > 0)
                <div class="tags">
                    @foreach($note->noteTagMaps as $tagMap)
                        @if($tagMap->tag && trim($tagMap->tag->name) !== '')
                            <span class="tag">#{{ e($tagMap->tag->name) }}</span>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="content" id="note-content">
                <noscript>该笔记内容需要启用 JavaScript 渲染。</noscript>
            </div>

            @if($note->image_path)
                <a href="{{ e($note->image_path) }}" target="_blank">
                    <img class="note-image" src="{{ e($note->image_path) }}" alt="笔记图片">
                </a>
            @endif

            @if($canEdit)
                <div class="actions">
                    <button type="button" class="action-btn js-note-artifact" data-note-id="{{ $note->id }}" data-artifact-type="visual_reading">
                        <i class="fas fa-book-open"></i> 可视化
                    </button>
                    <button type="button" class="action-btn js-note-artifact" data-note-id="{{ $note->id }}" data-artifact-type="mind_map">
                        <i class="fas fa-diagram-project"></i> 思维导图
                    </button>
                    <button type="button" class="action-btn js-note-artifact" data-note-id="{{ $note->id }}" data-artifact-type="key_points">
                        <i class="fas fa-list-check"></i> 关键信息
                    </button>
                    <a class="action-btn primary" href="{{ url('/notes/' . $note->id . '/edit') }}">
                        <i class="fas fa-edit"></i> 编辑
                    </a>
                    <div class="action-btn export-group" title="导出笔记">
                        <i class="fas fa-download"></i> 导出
                        <div class="export-menu">
                            <div class="export-menu-item" onclick="exportNote('md')"><i class="fab fa-markdown"></i>导出 Markdown</div>
                            <div class="export-menu-item" onclick="exportNote('html')"><i class="fas fa-code"></i>导出 HTML</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="footer">Powered by <a href="{{ url('/notes') }}">蒙太奇 · 记录想法</a></div>
    </div>

    <script>
        window.__NOTE_CONTENT__ = {!! json_encode($note->content, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
        window.__NOTE_NAME__ = {!! json_encode($note->name, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};

        function showToast(message, type) {
            type = type || 'info';
            const colors = { success: '#10b981', error: '#ef4444', info: '#4a90e2', warning: '#f59e0b' };
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = colors[type] || colors.info;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 3000);
        }

        function decodeNoteContent(value) {
            const html = String(value || '').replace(/<br\s*\/?>/ig, '\n');
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            return textarea.value;
        }

        function stripLeadingH1(value) {
            // 去掉内容开头的一级标题（# ...），避免与笔记标题重复显示成两个大标题
            return String(value || '').replace(/^\s*#[ \t]+[^\n]*\n?/, '').replace(/^\n+/, '');
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderMarkdownContent(value) {
            const markdown = stripLeadingH1(decodeNoteContent(value));
            const html = (window.marked && typeof window.marked.parse === 'function')
                ? window.marked.parse(markdown)
                : escapeHtml(markdown).replace(/\n/g, '<br>');
            if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
                return window.DOMPurify.sanitize(html);
            }
            return html;
        }

        // 导出：markdown 或 html
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

        function exportNote(format) {
            const markdown = decodeNoteContent(window.__NOTE_CONTENT__ || '');
            const title = window.__NOTE_NAME__ || '笔记';
            const filename = buildSafeFilename(title);
            const timeSuffix = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);

            if (format === 'md') {
                downloadTextFile(filename + '.md', markdown, 'text/markdown');
            } else if (format === 'html') {
                const bodyHtml = renderMarkdownContent(window.__NOTE_CONTENT__ || '');
                const html = exportHtmlDocument(filename + ' - ' + timeSuffix, bodyHtml);
                downloadTextFile(filename + '.html', html, 'text/html');
            } else {
                showToast('不支持的导出格式', 'error');
                return;
            }
            showToast('笔记已导出为 ' + String(format).toUpperCase(), 'success');
        }

        document.addEventListener('click', function(event) {
            if (!event.target.closest || !event.target.closest('.export-group')) {
                document.querySelectorAll('.export-group.open').forEach(function(el) {
                    el.classList.remove('open');
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('note-content');
            if (el) {
                el.innerHTML = renderMarkdownContent(window.__NOTE_CONTENT__ || '');
            }
        });
    </script>

    @include('artifacts._dialog')

    <script>
        // 笔记可视化/思维导图/关键信息 → 制品库弹窗
        document.querySelectorAll('.js-note-artifact').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var noteId = btn.getAttribute('data-note-id');
                var artifactType = btn.getAttribute('data-artifact-type');
                if (window.openArtifactDialog && noteId) {
                    window.openArtifactDialog({ relatedType: 'note', relatedId: noteId, artifactType: artifactType });
                } else {
                    alert('制品弹窗未初始化');
                }
            });
        });
    </script>
</body>
</html>
