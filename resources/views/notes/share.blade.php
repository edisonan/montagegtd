<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $note && !$locked ? (trim($note->name) !== '' ? e($note->name) : '分享的笔记') : '分享的笔记' }} - 蒙太奇</title>
    <meta name="description" content="蒙太奇用户分享的公开笔记">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .wrap { max-width: 1080px; margin: 0 auto; padding: 48px 20px 64px; }
        .brand {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            color: #9ca3af; font-size: 14px; margin-bottom: 24px; text-decoration: none;
        }
        .brand i { color: #4a90e2; }
        .card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 40px 48px;
        }
        .card.not-found, .card.locked { text-align: center; padding: 64px 36px; }
        .not-found .icon, .locked .icon { font-size: 48px; color: #d1d5db; margin-bottom: 16px; }
        .locked .icon { color: #f59e0b; }
        .not-found h1, .locked h1 { font-size: 20px; color: #374151; margin-bottom: 10px; }
        .not-found p, .locked p { font-size: 14px; color: #9ca3af; }
        .lock-form { margin-top: 24px; }
        .lock-form input[type="password"] {
            width: 260px; max-width: 100%; padding: 10px 14px;
            border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;
            outline: none; transition: border-color .15s;
        }
        .lock-form input[type="password"]:focus { border-color: #4a90e2; }
        .lock-form .btn {
            display: inline-block; margin-left: 8px; padding: 10px 18px;
            background: #4a90e2; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; cursor: pointer;
        }
        .lock-form .btn:hover { background: #3b82d6; }
        .wrong-tip { color: #ef4444; font-size: 13px; margin-top: 10px; }
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
        .public-badge {
            margin-left: auto;
            font-size: 12px; color: #047857; background: #d1fae5;
            padding: 4px 10px; border-radius: 999px; white-space: nowrap;
        }
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
        .footer { text-align: center; margin-top: 24px; font-size: 13px; color: #b0b8c4; }
        .footer a { color: #9ca3af; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <a class="brand" href="{{ url('/') }}">
            <i class="fas fa-bolt"></i> 蒙太奇
        </a>

        @if(! $note)
            <div class="card not-found">
                <div class="icon"><i class="fas fa-lock"></i></div>
                <h1>笔记未公开或不存在</h1>
                <p>该笔记可能已被删除，或者作者尚未将其设为公开分享。</p>
            </div>
        @elseif($locked)
            <div class="card locked">
                <div class="icon"><i class="fas fa-shield-alt"></i></div>
                <h1>该笔记受密码保护</h1>
                <p>请输入作者提供的访问密码后查看内容。</p>
                <form class="lock-form" method="get" action="{{ url('/notes/share/' . $note->share_token) }}">
                    <input type="password" name="key" placeholder="访问密码" autocomplete="off" required autofocus>
                    <button type="submit" class="btn">查看笔记</button>
                </form>
                @if($wrong)
                    <div class="wrong-tip"><i class="fas fa-exclamation-circle"></i> 密码不正确，请重试</div>
                @endif
            </div>
        @else
            <div class="card">
                <div class="meta">
                    <div class="avatar" style="background: linear-gradient(135deg, {{ $note->user && $note->user->color_1 ? e($note->user->color_1) : '#4a90e2' }}, {{ $note->user && $note->user->color_2 ? e($note->user->color_2) : '#8a6cff' }})">{{ $note->user && trim($note->user->name) !== '' ? e(mb_substr($note->user->name, 0, 1)) : '用' }}</div>
                    <div>
                        <div class="author">{{ $note->user ? e($note->user->name) : '用户' }}</div>
                        <div class="time"><i class="far fa-clock"></i> {{ date('Y-m-d H:i', strtotime($note->created_at)) }}</div>
                    </div>
                    <span class="public-badge"><i class="fas fa-globe"></i> 公开笔记</span>
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
            </div>
            <div class="footer">公开分享自 <a href="{{ url('/notes') }}">蒙太奇 · 记录想法</a></div>
        @endif
    </div>

    @if($note && ! $locked)
    <script>
        window.__NOTE_CONTENT__ = {!! json_encode($note->content, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};

        function decodeNoteContent(value) {
            const html = String(value || '').replace(/<br\s*\/?>/ig, '\n');
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            return textarea.value;
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
            const markdown = decodeNoteContent(value);
            const html = (window.marked && typeof window.marked.parse === 'function')
                ? window.marked.parse(markdown)
                : escapeHtml(markdown).replace(/\n/g, '<br>');
            if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
                return window.DOMPurify.sanitize(html);
            }
            return html;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('note-content');
            if (el) {
                el.innerHTML = renderMarkdownContent(window.__NOTE_CONTENT__ || '');
            }
        });
    </script>
    @endif
</body>
</html>