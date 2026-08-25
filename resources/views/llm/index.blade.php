@extends('layouts.app')

@section('title', 'AI 工作台 - 蒙太奇')
@section('description', '在智能体的帮助下思考、创作与整理')

@section('content')
<div class="ai-workbench" id="aiWorkbench" data-user-initial="{{ mb_substr(Auth::user()->name, 0, 1) }}">
    <aside class="ai-sidebar" id="aiSidebar" aria-label="会话导航">
        <div class="ai-sidebar-head">
            <div class="ai-brand">
                <div class="ai-brand-mark"><i class="fas fa-sparkles"></i></div>
                <div><strong>AI 工作台</strong><span>Think · Make · Remember</span></div>
            </div>
            <button class="ai-icon-btn ai-mobile-close" id="closeSidebar" title="关闭会话列表"><i class="fas fa-times"></i></button>
        </div>
        <button class="ai-new-btn" id="newChatBtn"><i class="fas fa-plus"></i><span>新对话</span><kbd>⌘K</kbd></button>
        <div class="ai-sidebar-tools">
            <label class="ai-search"><i class="fas fa-search"></i><input id="sessionSearch" type="search" placeholder="搜索会话"><kbd>/</kbd></label>
            <div class="ai-filter-row">
                <button class="ai-filter active" data-filter="all">全部</button>
                <button class="ai-filter" data-filter="pinned">固定</button>
                <button class="ai-filter" data-filter="active">最近活跃</button>
            </div>
            <select class="ai-agent-filter" id="sessionAgentFilter"><option value="">所有智能体</option></select>
        </div>
        <div class="ai-session-scroll" id="sessionList"><div class="ai-list-loading"><i class="fas fa-circle-notch fa-spin"></i> 正在加载</div></div>
        <div class="ai-sidebar-foot"><span><i class="far fa-comments"></i> <b id="sessionCount">0</b> 个会话</span><button id="clearUnpinnedBtn">清理未固定</button></div>
    </aside>
    <div class="ai-backdrop" id="sidebarBackdrop"></div>

    <main class="ai-main">
        <header class="ai-topbar">
            <button class="ai-icon-btn ai-menu-btn" id="openSidebar" title="打开会话列表"><i class="fas fa-bars"></i></button>
            <div class="ai-topbar-context"><span class="ai-live-dot"></span><span id="topbarContext">准备开始</span></div>
            <div class="ai-topbar-actions">
                <button class="ai-icon-btn" id="exportBtn" title="导出当前会话"><i class="fas fa-arrow-up-right-from-square"></i></button>
                <span class="ai-avatar" title="当前账户">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
            </div>
        </header>

        <section class="ai-conversation" id="conversationView">
            <div class="ai-empty" id="emptyState">
                <div class="ai-orbit"><span></span><i class="fas fa-sparkles"></i></div>
                <p class="ai-overline">YOUR SECOND BRAIN</p>
                <h1>今天想一起<br><em>想清楚什么？</em></h1>
                <p class="ai-empty-copy">选择一个智能体，开始一段专注的思考。答案会留在这里，也可以随时沉淀为笔记或思维导图。</p>
                <div class="ai-start-row"><select id="agentSelect" aria-label="选择智能体"><option value="">加载智能体中…</option></select><button class="ai-start-btn" id="startEmptyBtn"><i class="fas fa-arrow-right"></i></button></div>
                <div class="ai-suggestions"><button data-prompt="帮我梳理今天最重要的三件事"><i class="far fa-compass"></i> 梳理今天</button><button data-prompt="帮我把一个模糊想法变成清晰计划"><i class="far fa-lightbulb"></i> 发展想法</button><button data-prompt="帮我分析这段内容，并提炼关键结论"><i class="far fa-file-lines"></i> 提炼内容</button></div>
            </div>
            <div class="ai-chat" id="chatView" hidden>
                <div class="ai-chat-head">
                    <div class="ai-chat-agent"><div class="ai-agent-orb" id="chatAgentOrb"><i class="fas fa-sparkles"></i></div><div><div class="ai-chat-title-row"><h2 id="chatTitle">未命名会话</h2><button id="renameBtn" title="重命名"><i class="fas fa-pen"></i></button></div><div class="ai-chat-subline"><p id="chatAgentName">智能体 · 加载中</p><div class="ai-branch-nav" id="branchNav" hidden><button id="branchPrev" type="button" title="上一个分支"><i class="fas fa-chevron-left"></i></button><span id="branchLabel">原对话</span><button id="branchNext" type="button" title="下一个分支"><i class="fas fa-chevron-right"></i></button></div></div></div></div>
                    <div class="ai-chat-actions"><button class="ai-icon-btn" id="pinBtn" title="固定会话"><i class="far fa-bookmark"></i></button><button class="ai-icon-btn" id="chatMenuBtn" title="更多操作"><i class="fas fa-ellipsis"></i></button><div class="ai-menu" id="chatMenu"><button id="regenerateBtn"><i class="fas fa-rotate-right"></i> 重新生成</button><button id="clearBtn"><i class="far fa-trash-can"></i> 清空对话</button><button id="deleteBtn" class="danger"><i class="far fa-trash-can"></i> 删除会话</button></div></div>
                </div>
                <div class="ai-messages" id="messages"></div>
            </div>
            <button class="ai-jump-bottom" id="jumpBottomBtn" type="button" title="回到最新消息" aria-label="回到最新消息" hidden><i class="fas fa-arrow-down"></i></button>
        </section>

        <footer class="ai-composer-area">
            <div class="ai-composer" id="composer">
                <div class="ai-composer-top"><select id="composerAgent" aria-label="当前智能体"><option value="">选择智能体</option></select><span class="ai-composer-title">向智能体提问</span></div>
                <div class="ai-attachment-tray" id="attachmentTray" hidden></div>
                <textarea id="messageInput" rows="1" maxlength="8000" placeholder="和智能体聊聊你的想法…"></textarea>
                <input id="attachmentInput" type="file" multiple hidden accept=".txt,.md,.markdown,.csv,.json,.xml,.html,.htm,.log,.yaml,.yml,.php,.js,.jsx,.ts,.tsx,.css,.scss,.less,.py,.java,.go,.rs,.sql,.sh,.bash,.zsh,.ini,.conf,.vue,.blade,.env,.docx,.pdf">
                <div class="ai-composer-bottom">
                    <div class="ai-composer-tools">
                        <button type="button" class="ai-attach-btn" id="attachBtn" title="添加文本、代码、DOCX 或 PDF 文件" aria-label="添加附件"><i class="fas fa-paperclip"></i></button>
                        <span class="ai-hint"><span id="charCount">0</span>/8000 · Enter 发送，Shift+Enter 换行</span>
                    </div>
                    <button class="ai-send" id="sendBtn" title="发送"><i class="fas fa-arrow-up"></i></button>
                </div>
            </div>
            <p class="ai-disclaimer">AI 可能会犯错，请对重要信息进行核实。</p>
        </footer>
    </main>
</div>

<script src="/js/marked.min.js"></script>
<script src="/plugins/purify/purify.min.js"></script>
<style>
    /* 全屏工作面：脱离 max-w-7xl / py-8 容器束缚，隐藏站点 footer，让聊天区占满视口 */
    body > main.max-w-7xl{max-width:100%!important;padding:0!important}
    body > footer.mt-16{display:none!important}

    .ai-workbench{
        --ink:#0F1115;--muted:#81858C;--caption:#ADB2B8;--line:#E1E5EE;--line-soft:#EEF0F4;
        --soft:#F6F7F9;--hover:#F1F3F5;--brand:#4176E6;--brand-soft:#EDF3FE;--brand-soft-2:#E4EDFD;
        --ok:#22c55e;--warn:#e0a54b;--danger:#ef4444;
        --shadow-lv2:0 4px 12px rgba(0,0,0,.02),0 2px 8px rgba(0,0,0,.04);
        --chat-width:748px;--composer-width:calc(var(--chat-width) + 32px);--clearance:16px;
        display:flex;height:calc(100vh - 64px);min-height:560px;background:#fff;color:var(--ink);
        font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','PingFang SC','Hiragino Sans GB',sans-serif;overflow:hidden
    }
    .ai-workbench,.ai-workbench *{box-sizing:border-box}
    .ai-empty[hidden],.ai-chat[hidden]{display:none!important}

    /* ===== 会话侧栏（DSH sidebar 填充/节奏） ===== */
    .ai-sidebar{width:280px;flex:0 0 280px;background:#F9FAFB;border-right:1px solid var(--line);display:flex;flex-direction:column;z-index:20}
    .ai-sidebar-head{display:flex;align-items:center;justify-content:space-between;padding:16px 12px 10px}
    .ai-brand{display:flex;align-items:center;gap:10px;min-width:0}
    .ai-brand-mark{width:28px;height:28px;border-radius:9px;display:grid;place-items:center;flex:0 0 28px;color:#fff;background:linear-gradient(135deg,#5688f4,#3159c9);box-shadow:0 6px 14px rgba(65,118,230,.28)}
    .ai-brand strong{display:block;font-size:13px;letter-spacing:.01em;white-space:nowrap}
    .ai-brand span{display:block;margin-top:2px;color:var(--caption);font-size:9px;letter-spacing:.1em;text-transform:uppercase}
    .ai-new-btn{display:flex;align-items:center;gap:8px;margin:2px 12px 12px;width:calc(100% - 24px);padding:9px 12px;border:0;border-radius:10px;background:var(--brand);color:#fff;cursor:pointer;font-size:13px;font-weight:500;box-shadow:0 6px 16px rgba(65,118,230,.24);transition:background .15s}
    .ai-new-btn:hover{background:#3568d0}
    .ai-new-btn i{font-size:11px}
    .ai-new-btn kbd{margin-left:auto;padding:2px 6px;border-radius:5px;background:rgba(255,255,255,.18);color:#dbe8ff;font-size:10px;font-weight:400}
    .ai-sidebar-tools{padding:0 12px 12px;border-bottom:1px solid var(--line-soft)}
    .ai-search{display:flex;align-items:center;gap:7px;padding:7px 9px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--caption)}
    .ai-search:focus-within{border-color:#a9c3f5;box-shadow:0 0 0 3px var(--brand-soft)}
    .ai-search input{min-width:0;flex:1;border:0;outline:0;background:transparent;color:var(--ink);font-size:12px}
    .ai-search kbd{padding:1px 5px;border-radius:4px;background:var(--soft);font-size:10px;color:var(--caption)}
    .ai-filter-row{display:flex;gap:2px;margin:10px 0 9px}
    .ai-filter{padding:4px 9px;border:0;border-radius:7px;background:transparent;color:var(--muted);cursor:pointer;font-size:11px}
    .ai-filter.active{background:var(--brand-soft);color:var(--brand);font-weight:600}
    .ai-agent-filter{width:100%;padding:7px 9px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--muted);outline:0;font-size:12px}
    .ai-agent-filter:focus{border-color:#a9c3f5;box-shadow:0 0 0 3px var(--brand-soft)}
    .ai-session-scroll{flex:1;overflow-y:auto;padding:6px 8px}
    .ai-list-loading{padding:36px 10px;text-align:center;color:var(--caption);font-size:12px}
    .ai-list-loading i{margin-right:5px}
    .ai-session-group{margin:12px 8px 6px;color:var(--caption);font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase}
    .ai-session-item{position:relative;display:flex;align-items:center;gap:9px;width:100%;padding:8px 9px;border:0;border-radius:9px;background:transparent;text-align:left;color:var(--muted);cursor:pointer;transition:background .12s}
    .ai-session-item:hover{background:var(--hover)}
    .ai-session-item.active{background:var(--brand-soft);color:var(--brand)}
    .ai-session-item i{color:var(--caption);font-size:11px}
    .ai-session-item.active i{color:var(--brand)}
    .ai-session-text{min-width:0;flex:1}
    .ai-session-text strong{display:block;overflow:hidden;color:inherit;font-size:12px;font-weight:500;text-overflow:ellipsis;white-space:nowrap}
    .ai-session-text span{display:block;margin-top:2px;overflow:hidden;color:var(--caption);font-size:10px;text-overflow:ellipsis;white-space:nowrap}
    .ai-pin{color:#e3a63a!important}
    .ai-session-more{opacity:0;padding:3px;border:0;background:transparent;color:var(--caption);cursor:pointer;transition:opacity .12s}
    .ai-session-item:hover .ai-session-more,.ai-session-item.active .ai-session-more{opacity:1}
    .ai-sidebar-foot{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-top:1px solid var(--line-soft);color:var(--caption);font-size:10px}
    .ai-sidebar-foot b{color:var(--muted)}
    .ai-sidebar-foot button{border:0;background:transparent;color:var(--caption);cursor:pointer;font-size:10px}
    .ai-sidebar-foot button:hover{color:var(--danger)}

    /* ===== 主列 ===== */
    .ai-main{display:flex;flex:1;min-width:0;flex-direction:column;background:#fff;position:relative}
    .ai-topbar{height:48px;display:flex;align-items:center;justify-content:space-between;padding:0 18px;border-bottom:1px solid var(--line-soft);flex:0 0 48px}
    .ai-topbar-context{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:11px}
    .ai-live-dot{width:6px;height:6px;border-radius:50%;background:#3aa9ef;box-shadow:0 0 0 4px rgba(58,169,239,.14)}
    .ai-topbar-actions{display:flex;align-items:center;gap:8px}
    .ai-icon-btn{width:30px;height:30px;padding:0;border:0;border-radius:8px;background:transparent;color:var(--muted);cursor:pointer;transition:background .12s,color .12s}
    .ai-icon-btn:hover{background:var(--hover);color:var(--ink)}
    .ai-avatar{width:26px;height:26px;display:grid;place-items:center;border:0;border-radius:50%;background:var(--brand-soft-2);color:var(--brand);font-size:11px;font-weight:600}
    .ai-menu-btn,.ai-mobile-close{display:none}

    .ai-conversation{position:relative;flex:1;min-height:0;overflow:hidden}

    /* ===== 空状态 hero（DSH hero：紧凑标题栈，输入卡上浮到版面中部） ===== */
    .ai-empty{display:flex;align-items:center;flex-direction:column;justify-content:flex-start;height:100%;padding:6vh 30px 0;text-align:center;overflow-y:auto}
    .ai-orbit{position:relative;width:40px;height:40px;display:grid;place-items:center;margin-bottom:18px;border-radius:12px;color:#fff;background:linear-gradient(135deg,#5688f4,#3159c9);box-shadow:0 10px 22px rgba(65,118,230,.30)}
    .ai-orbit i{font-size:16px}
    .ai-orbit span{position:absolute;width:5px;height:5px;border-radius:50%;background:#f5b24a;top:-2px;right:-2px;border:2px solid #fff}
    .ai-overline{margin:0 0 10px;color:var(--caption);font-size:10px;font-weight:600;letter-spacing:.18em}
    .ai-empty h1{margin:0;color:var(--ink);font-size:clamp(24px,3vw,28px);font-weight:500;letter-spacing:-.02em;line-height:1.25}
    .ai-empty h1 em{color:var(--brand);font-style:normal}
    .ai-empty-copy{margin:12px 0 22px;color:var(--muted);font-size:13px;line-height:1.7}
    .ai-start-row{display:flex;align-items:center;gap:6px;width:min(320px,100%)}
    .ai-start-row select{flex:1;height:36px;padding:0 10px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--ink);outline:0;font-size:12px}
    .ai-start-row select:focus{border-color:#a9c3f5;box-shadow:0 0 0 3px var(--brand-soft)}
    .ai-start-btn{width:36px;height:36px;border:0;border-radius:9px;background:var(--brand);color:#fff;cursor:pointer}
    .ai-start-btn:hover{background:#3568d0}
    .ai-suggestions{display:flex;gap:7px;flex-wrap:wrap;justify-content:center;margin-top:20px}
    .ai-suggestions button{padding:6px 11px;border:1px solid var(--line);border-radius:16px;background:#fff;color:var(--muted);cursor:pointer;font-size:11px;transition:border-color .12s,color .12s,background .12s}
    .ai-suggestions button:hover{border-color:#a9c3f5;color:var(--brand);background:var(--brand-soft)}
    .ai-suggestions i{margin-right:4px;color:var(--brand)}

    /* ===== 聊天视图 ===== */
    .ai-chat{height:100%;display:flex;flex-direction:column}
    .ai-chat-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 20px;border-bottom:1px solid var(--line-soft);background:#fff}
    .ai-chat-agent{display:flex;align-items:center;gap:10px;min-width:0}
    .ai-agent-orb{width:30px;height:30px;display:grid;place-items:center;flex:0 0 30px;border-radius:9px;background:var(--brand-soft);color:var(--brand);font-size:12px}
    .ai-chat-title-row{display:flex;align-items:center;gap:6px;min-width:0}
    .ai-chat-title-row h2{max-width:400px;margin:0;overflow:hidden;color:var(--ink);font-size:14px;font-weight:600;text-overflow:ellipsis;white-space:nowrap}
    .ai-chat-title-row button{padding:3px;border:0;background:transparent;color:var(--caption);cursor:pointer;font-size:10px}
    .ai-chat-title-row button:hover{color:var(--brand)}
    .ai-chat-agent p{margin:3px 0 0;color:var(--caption);font-size:10px}
    .ai-chat-subline{display:flex;align-items:center;gap:8px}
    .ai-branch-nav{display:flex;align-items:center;gap:3px;padding-left:7px;border-left:1px solid var(--line);color:var(--muted);font-size:9px}
    .ai-branch-nav[hidden]{display:none}
    .ai-branch-nav button{width:18px;height:18px;padding:0;border:0;border-radius:5px;background:transparent;color:var(--muted);cursor:pointer;font-size:9px}
    .ai-branch-nav button:hover:not(:disabled){background:var(--brand-soft);color:var(--brand)}
    .ai-branch-nav button:disabled{opacity:.25;cursor:default}
    .ai-chat-actions{position:relative;display:flex;gap:2px;flex:0 0 auto}
    .ai-chat-actions .ai-icon-btn.is-pinned{color:#e3a63a}
    .ai-menu{position:absolute;z-index:30;top:36px;right:0;display:none;min-width:132px;padding:5px;border:1px solid var(--line);border-radius:10px;background:#fff;box-shadow:0 12px 28px rgba(15,17,21,.12)}
    .ai-menu.open{display:block}
    .ai-menu button{display:flex;align-items:center;gap:7px;width:100%;padding:7px 9px;border:0;border-radius:7px;background:transparent;color:var(--muted);text-align:left;cursor:pointer;font-size:11px}
    .ai-menu button:hover{background:var(--hover)}
    .ai-menu button.danger{color:var(--danger)}

    /* ===== 消息流（DSH：748px 居中列、user 右蓝气泡、AI 全宽） ===== */
    .ai-messages{flex:1;overflow-y:auto;padding:24px var(--clearance) 36px}
    .ai-messages > *{max-width:var(--chat-width);margin-left:auto;margin-right:auto}
    .ai-message{display:flex;gap:10px;margin-bottom:16px;animation:ai-in .2s ease-out}
    .ai-message.user{flex-direction:column;align-items:flex-end}
    .ai-message-avatar{width:24px;height:24px;display:grid;place-items:center;flex:0 0 24px;border-radius:8px;background:var(--soft);color:var(--muted);font-size:9px}
    .ai-message.ai .ai-message-avatar{background:var(--brand-soft);color:var(--brand)}
    .ai-message.user .ai-message-avatar{display:none}
    .ai-message-body{min-width:0;max-width:100%}
    .ai-message.user .ai-message-body{display:flex;flex-direction:column;align-items:flex-end;max-width:min(525px,82%)}
    .ai-message-meta{margin-bottom:4px;color:var(--caption);font-size:11px}
    .ai-message.user .ai-message-meta{display:none}
    .ai-bubble{font-size:16px;line-height:24px;color:var(--ink);overflow-wrap:anywhere}
    .ai-message.user .ai-bubble{padding:10px 16px;border-radius:22px;background:var(--brand-soft);color:var(--ink);white-space:pre-wrap}
    .ai-message.ai .ai-bubble{width:100%}
    .ai-bubble p:first-child{margin-top:0}
    .ai-bubble p:last-child{margin-bottom:0}
    .ai-bubble pre{position:relative;overflow:auto;padding:38px 12px 12px;border-radius:10px;background:#0F1115;color:#e6ecf4;font-size:13px;line-height:1.65}
    .ai-bubble code{font-size:.92em}
    .ai-bubble:not(pre)>code{padding:2px 5px;border-radius:5px;background:var(--soft);font-size:.88em}
    .ai-bubble table{border-collapse:collapse;max-width:100%;font-size:14px}
    .ai-bubble th,.ai-bubble td{padding:6px 10px;border:1px solid var(--line)}
    .ai-bubble blockquote{margin:8px 0;padding:4px 12px;border-left:3px solid var(--brand-200,#d3e2ff);color:var(--muted)}
    .ai-code-copy{position:absolute;top:7px;right:7px;display:flex;align-items:center;gap:5px;padding:4px 8px;border:1px solid rgba(255,255,255,.14);border-radius:6px;background:rgba(255,255,255,.08);color:#cbd5e1;cursor:pointer;font-size:10px}
    .ai-code-copy:hover{background:rgba(255,255,255,.16);color:#fff}
    .ai-message-actions{display:flex;flex-wrap:wrap;gap:10px;margin:6px 2px 0;opacity:0;transition:opacity .15s}
    .ai-message:hover .ai-message-actions{opacity:1}
    .ai-message.user .ai-message-actions{justify-content:flex-end}
    .ai-message-actions button{padding:0;border:0;background:transparent;color:var(--caption);cursor:pointer;font-size:10px}
    .ai-message-actions button:hover{color:var(--brand)}
    .ai-message-actions button.active{color:var(--brand)}
    .ai-message-attachments{display:flex;max-width:100%;justify-content:flex-end;gap:6px;flex-wrap:wrap;margin-bottom:6px}
    .ai-message-attachment{display:flex;align-items:center;min-width:0;max-width:100%;gap:6px;padding:5px 9px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--muted);font-size:11px}
    .ai-message-attachment span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .ai-message-editor{display:block;width:min(560px,70vw);min-height:100px;padding:10px;border:1px solid var(--line);border-radius:10px;outline:0;resize:vertical;color:var(--ink);background:#fff;font:14px/1.6 inherit}
    .ai-message-editor:focus{border-color:#a9c3f5;box-shadow:0 0 0 3px var(--brand-soft)}
    .ai-message-editor-actions{display:flex;justify-content:flex-end;gap:7px;margin-top:8px}
    .ai-message-editor-actions button{padding:6px 10px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--muted);cursor:pointer;font-size:11px}
    .ai-message-editor-actions button.primary{border-color:var(--brand);background:var(--brand);color:#fff}
    .ai-inline-error{display:flex;align-items:center;gap:9px;max-width:min(560px,90%);margin:12px auto 20px;padding:10px 13px;border:1px solid #f3cdd0;border-radius:10px;background:#fff7f7;color:var(--danger);font-size:12px}
    .ai-inline-error span{flex:1}
    .ai-inline-error button{padding:5px 9px;border:1px solid #e5b6b9;border-radius:6px;background:#fff;color:var(--danger);cursor:pointer;font-size:11px}

    /* 思考态：DSH turnStatus 品牌蓝 shimmer 文字 */
    .ai-message#thinking{margin-bottom:10px}
    .ai-thinking{display:inline-flex;align-items:center;gap:7px;color:var(--brand);font-size:13px;font-weight:500}
    .ai-thinking b{font-weight:500}
    .ai-thinking span{width:5px;height:5px;border-radius:50%;background:var(--brand);animation:ai-thinking 1.15s infinite ease-in-out}
    .ai-thinking span:nth-child(2){animation-delay:.15s}
    .ai-thinking span:nth-child(3){animation-delay:.3s}
    .ai-workbench.is-generating .ai-live-dot{animation:ai-pulse 1.4s infinite}
    .ai-workbench.is-stopping .ai-live-dot{background:var(--warn);box-shadow:0 0 0 4px rgba(224,165,75,.18)}

    .ai-jump-bottom{position:absolute;z-index:8;left:50%;bottom:18px;width:32px;height:32px;transform:translateX(-50%);border:1px solid var(--line);border-radius:50%;background:#fff;color:var(--muted);box-shadow:var(--shadow-lv2);cursor:pointer}
    .ai-jump-bottom:hover{color:var(--brand);border-color:#a9c3f5}
    .ai-jump-bottom[hidden]{display:none}

    /* ===== 输入区（DSH 悬浮胶囊卡） ===== */
    .ai-composer-area{padding:6px var(--clearance) 10px}
    .ai-composer{position:relative;display:flex;flex-direction:column;gap:8px;width:100%;max-width:var(--composer-width);margin:0 auto;padding:10px 12px 8px;border:1px solid var(--line);border-radius:22px;background:#fff;box-shadow:var(--shadow-lv2);transition:border-color .15s,box-shadow .15s}
    .ai-composer:focus-within{border-color:#9dbdf2;box-shadow:0 4px 14px rgba(15,17,21,.04),0 0 0 3px var(--brand-soft)}
    .ai-composer.streaming{border-color:#9dbdf2}
    .ai-composer.streaming textarea{opacity:.75}
    .ai-composer.dragging{border-color:var(--brand);box-shadow:0 0 0 4px var(--brand-soft)}
    .ai-composer.dragging:after{content:"松开即可添加文件";position:absolute;z-index:5;inset:5px;display:grid;place-items:center;border:1px dashed var(--brand);border-radius:17px;background:rgba(237,243,254,.96);color:var(--brand);font-size:13px;font-weight:500;pointer-events:none}
    .ai-composer-top{display:flex;align-items:center;gap:8px;min-height:26px}
    .ai-composer-top select{max-width:180px;padding:3px 8px;border:1px solid var(--line);border-radius:14px;background:var(--brand-soft);color:var(--brand);font-size:11px;font-weight:500;outline:0;cursor:pointer}
    .ai-composer-top select:focus{border-color:#a9c3f5}
    .ai-composer-title{color:var(--caption);font-size:10px}
    .ai-composer textarea{display:block;width:100%;min-height:52px;padding:2px 4px;border:0;outline:0;resize:none;color:var(--ink);font:16px/24px inherit}
    .ai-composer textarea::placeholder{color:var(--caption)}
    .ai-composer-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px}
    .ai-composer-tools{display:flex;align-items:center;min-width:0;gap:10px}
    .ai-attach-btn{width:26px;height:26px;flex:0 0 26px;padding:0;border:0;border-radius:7px;background:transparent;color:var(--caption);cursor:pointer}
    .ai-attach-btn:hover{background:var(--brand-soft);color:var(--brand)}
    .ai-attach-btn:disabled{opacity:.4;cursor:default}
    .ai-hint{color:var(--caption);font-size:10px;white-space:nowrap}
    .ai-send{width:30px;height:30px;flex:0 0 30px;border:0;border-radius:50%;background:var(--brand);color:#fff;cursor:pointer;box-shadow:0 4px 10px rgba(65,118,230,.26);transition:background .15s}
    .ai-send:hover{background:#3568d0}
    .ai-send:disabled{opacity:.45;cursor:default;box-shadow:none}
    .ai-disclaimer{margin:6px 0 0;color:#B7BEC9;text-align:center;font-size:10px}
    .ai-attachment-tray{display:flex;gap:7px;overflow-x:auto;padding:0 2px}
    .ai-attachment-tray[hidden]{display:none}
    .ai-attachment-chip{display:flex;align-items:center;min-width:0;max-width:230px;gap:8px;padding:6px 8px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--muted)}
    .ai-attachment-chip.uploading{opacity:.65}
    .ai-attachment-icon{width:25px;height:25px;display:grid;place-items:center;flex:0 0 25px;border-radius:7px;background:var(--brand-soft);color:var(--brand);font-size:9px;font-weight:600;text-transform:uppercase}
    .ai-attachment-copy{min-width:0;flex:1}
    .ai-attachment-copy strong{display:block;overflow:hidden;font-size:11px;font-weight:500;text-overflow:ellipsis;white-space:nowrap}
    .ai-attachment-copy span{display:block;margin-top:2px;color:var(--caption);font-size:9px}
    .ai-attachment-remove{width:20px;height:20px;padding:0;border:0;border-radius:5px;background:transparent;color:var(--caption);cursor:pointer}
    .ai-attachment-remove:hover{background:var(--hover);color:var(--danger)}

    .ai-backdrop{display:none}
    .ai-toast{position:fixed;z-index:100;right:22px;bottom:24px;padding:9px 14px;border-radius:10px;background:#1F2937;color:#fff;box-shadow:0 10px 25px rgba(15,17,21,.24);font-size:12px}
    .ai-toast.success{background:#16a34a}
    .ai-toast.error{background:var(--danger)}

    /* 空状态时输入卡上浮到版面中部（DSH hero 相位）；快捷入口隐藏，智能体选择由输入卡顶部 select 承担 */
    .ai-workbench:has(.ai-empty:not([hidden])) .ai-composer-area{position:absolute;left:0;right:0;bottom:auto;top:56%;transform:translateY(0);z-index:6}
    .ai-workbench:has(.ai-empty:not([hidden])) .ai-start-row,
    .ai-workbench:has(.ai-empty:not([hidden])) .ai-suggestions{display:none}
    .ai-workbench:has(.ai-empty:not([hidden])) .ai-conversation{padding-bottom:0}

    @keyframes ai-in{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
    @keyframes ai-thinking{0%,60%,100%{opacity:.3;transform:translateY(0)}30%{opacity:1;transform:translateY(-3px)}}
    @keyframes ai-pulse{50%{box-shadow:0 0 0 7px rgba(65,118,230,0)}}

    .ai-icon-btn:focus-visible,.ai-send:focus-visible,.ai-new-btn:focus-visible,.ai-session-item:focus-visible,.ai-suggestions button:focus-visible,.ai-start-btn:focus-visible{outline:2px solid #7ea6ef;outline-offset:2px}

    @media(max-width:760px){
        .ai-workbench{height:calc(100vh - 56px);min-height:520px}
        .ai-sidebar{position:absolute;top:0;bottom:0;left:0;width:min(88vw,310px);transform:translateX(-105%);transition:transform .22s ease;box-shadow:14px 0 35px rgba(15,17,21,.14)}
        .ai-sidebar.open{transform:none}
        .ai-backdrop{position:absolute;z-index:15;inset:0;background:rgba(15,17,21,.4)}
        .ai-backdrop.open{display:block}
        .ai-menu-btn,.ai-mobile-close{display:block}
        .ai-topbar{padding:0 12px}
        .ai-topbar-context{margin-right:auto;margin-left:6px}
        .ai-topbar-actions{gap:4px}
        .ai-empty{padding:10vh 18px 0}
        .ai-empty h1{font-size:24px}
        .desktop-only{display:none}
        .ai-empty-copy{font-size:12px}
        .ai-suggestions{max-width:320px}
        .ai-suggestions button{font-size:10px}
        .ai-chat-head{padding:11px 14px}
        .ai-chat-title-row h2{max-width:180px;font-size:13px}
        .ai-messages{padding:18px 12px 30px}
        .ai-message{font-size:15px}
        .ai-message.user .ai-message-body{max-width:90%}
        .ai-message-actions{opacity:1}
        .ai-composer-area{padding:6px 10px 10px}
        .ai-hint{font-size:8px}
        .ai-composer-top select{max-width:130px}
        .ai-disclaimer{font-size:8px}
        .ai-workbench:has(.ai-empty:not([hidden])) .ai-composer-area{top:auto;bottom:0;position:absolute}
    }
    @media(prefers-reduced-motion:reduce){.ai-message,.ai-thinking span,.ai-live-dot{animation:none!important}.ai-sidebar{transition:none!important}}
</style>

<script src="/js/llm-chat-workbench.js?v=20260717-4"></script>
@endsection