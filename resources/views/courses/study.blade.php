@extends('layouts.app')

@php $hideAppShell = true; @endphp

@section('title', '沉浸学习 - 蒙太奇')
@section('description', '蒙太奇课程沉浸学习模式')

@section('content')
    <style>
        /* ===== 沉浸学习页：全屏应用式布局（隐藏主站导航/页脚） ===== */
        main.max-w-7xl {
            max-width: none !important;
            padding: 0 !important;
        }

        body {
            overflow: hidden;
            background: #f8fafc;
        }

        .study-app {
            position: relative;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }

        /* ---------- 顶栏 ---------- */
        .st-topbar {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 10px;
            height: 54px;
            padding: 0 14px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 40;
        }

        .st-icon-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            color: #475569;
            border: 1px solid transparent;
            cursor: pointer;
            background: transparent;
            font-size: 14px;
            flex-shrink: 0;
        }

        .st-icon-btn:hover { background: #f1f5f9; color: #1d4ed8; }

    .st-drawer-toggle { display: none; }

        .st-title {
            flex: 1;
            min-width: 0;
            font-weight: 600;
            color: #1e293b;
            font-size: 15px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .st-progress-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12.5px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .st-progress-chip .st-progress-bar {
            width: 72px;
            height: 5px;
            border-radius: 999px;
            background: #dbeafe;
            overflow: hidden;
        }

        .st-progress-chip .st-progress-bar i {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width .3s ease;
        }

        /* ---------- 主体 ---------- */
        .st-body {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            position: relative;
        }

        /* 左侧章节抽屉 */
        .st-drawer {
            flex: 0 0 300px;
            width: 300px;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
            padding: 12px 10px 32px;
            z-index: 35;
        }

        .st-drawer-title {
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: .06em;
            padding: 4px 10px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .st-tree { list-style: none; margin: 0; padding: 0; }
        .st-tree ul { list-style: none; padding-left: 14px; margin: 0; }
        .st-tree-children { display: none; }
        .st-tree-children.open { display: block; }

        .st-node {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            margin: 1px 0;
            border-radius: 9px;
            cursor: pointer;
            color: #475569;
            font-size: 13.5px;
            line-height: 1.35;
            user-select: none;
        }

        .st-node:hover { background: #f1f5f9; }
        .st-node.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        .st-node.done .st-label { color: #94a3b8; text-decoration: line-through; }

        .st-caret { width: 14px; flex-shrink: 0; display: inline-flex; justify-content: center; color: #94a3b8; font-size: 10px; }
        .st-node-icon { flex-shrink: 0; font-size: 12.5px; width: 16px; text-align: center; }
        .st-node.done .st-node-icon { color: #10b981; }
        .st-label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .st-dur { flex-shrink: 0; font-size: 11px; color: #94a3b8; }

        /* 右侧阅读区 */
        .st-main {
            flex: 1 1 auto;
            min-width: 0;
            overflow-y: auto;
            background: #f8fafc;
        }

        .st-reading {
            max-width: 820px;
            margin: 0 auto;
            padding: 40px 28px 120px;
        }

        .st-item-title {
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 10px;
        }

        .st-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }

        .type-label {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .type-label.module { background: rgba(100, 116, 139, .12); color: #475569; }
        .type-label.chapter { background: rgba(59, 130, 246, .12); color: #3b82f6; }
        .type-label.video { background: rgba(59, 130, 246, .12); color: #3b82f6; }
        .type-label.quiz { background: rgba(16, 185, 129, .12); color: #059669; }
        .type-label.assignment { background: rgba(139, 92, 246, .12); color: #8b5cf6; }
        .type-label.reading { background: rgba(245, 158, 11, .12); color: #b45309; }

        .st-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 12px;
        }

        .st-desc {
            color: #475569;
            line-height: 1.8;
            white-space: pre-wrap;
            word-break: break-word;
            margin-bottom: 18px;
        }

        .st-external {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .st-external .txt { font-size: 13.5px; color: #1e40af; }
        .st-external .txt i { margin-right: 6px; }

        /* Markdown 正文 */
        .md-content {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 26px 30px;
            line-height: 1.9;
            color: #334155;
            font-size: 15.5px;
            overflow-wrap: break-word;
        }

        .md-content h1, .md-content h2, .md-content h3, .md-content h4 { font-weight: 700; color: #1e293b; margin: 16px 0 10px; }
        .md-content h1 { font-size: 1.35rem; }
        .md-content h2 { font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        .md-content h3 { font-size: 1.05rem; }
        .md-content h4 { font-size: 1rem; }
        .md-content p { margin: 10px 0; }
        .md-content ul, .md-content ol { padding-left: 24px; margin: 10px 0; }
        .md-content li { margin: 5px 0; }
        .md-content a { color: #2563eb; text-decoration: underline; }
        .md-content strong { font-weight: 700; color: #1e293b; }
        .md-content blockquote { border-left: 3px solid #cbd5e1; padding-left: 14px; color: #64748b; margin: 12px 0; }
        .md-content code { background: #e2e8f0; padding: 1px 6px; border-radius: 5px; font-size: 13.5px; color: #dc2626; }
        .md-content pre { background: #0f172a; color: #e2e8f0; padding: 14px 18px; border-radius: 10px; overflow-x: auto; margin: 12px 0; }
        .md-content pre code { background: transparent; color: inherit; padding: 0; font-size: 13.5px; }
        .md-content table { border-collapse: collapse; margin: 12px 0; width: 100%; }
        .md-content th, .md-content td { border: 1px solid #e2e8f0; padding: 6px 10px; font-size: 13.5px; }
        .md-content th { background: #f8fafc; font-weight: 600; }
        .md-content img { max-width: 100%; border-radius: 8px; }
        .md-content hr { border: 0; border-top: 1px solid #e2e8f0; margin: 18px 0; }

        /* Markdown 正文：按标题折叠/展开 */
        .md-content .md-section > .md-head-toggle {
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .md-content .md-head-toggle:hover { color: #1d4ed8; }
        .md-content .md-head-toggle:hover .md-toggle-icon { color: #3b82f6; }

        .md-toggle-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 10px;
            border-radius: 5px;
            transition: transform .15s ease;
        }

        .md-section.collapsed > .md-head-toggle .md-toggle-icon { transform: rotate(90deg); }
        .md-section.collapsed > *:not(:first-child) { display: none !important; }

        .st-md-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .st-btn-xs { padding: 4px 10px; font-size: 12px; border-radius: 8px; }

        /* 容器章节 → 子章节目录 */
        .st-chapter-list { margin: 6px 0 18px; }
        .st-chapter-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: border-color .15s ease;
        }
        .st-chapter-row:hover { border-color: #93c5fd; }
        .st-chapter-row.done .txt { color: #94a3b8; text-decoration: line-through; }
        .st-chapter-row .txt { flex: 1; min-width: 0; font-size: 14px; color: #334155; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* 操作区 */
        .st-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }

        .st-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all .18s ease;
        }
        .st-btn-primary { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: #fff; }
        .st-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(59, 130, 246, .25); }
        .st-btn-success { background: linear-gradient(135deg, #10b981, #34d399); color: #fff; }
        .st-btn-success:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16, 185, 129, .25); }
        .st-btn-ghost { background: #fff; color: #475569; border-color: #cbd5e1; }
        .st-btn-ghost:hover { background: #f8fafc; color: #1d4ed8; border-color: #93c5fd; }
        .st-btn:disabled { opacity: .55; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

        /* 上/下一节导航 */
        .st-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 34px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .st-nav .spacer { flex: 1; }
        .st-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
        }
        .st-nav-btn:hover:not(:disabled) { border-color: #93c5fd; color: #1d4ed8; }
        .st-nav-btn:disabled { opacity: .4; cursor: not-allowed; }

        /* 测验弹窗 */
        .quiz-modal { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; background: rgba(15, 23, 42, .55); padding: 20px; }
        .quiz-modal.show { display: flex; }
        .quiz-modal-card { width: min(720px, 100%); max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 16px; padding: 24px; }

        /* 待复习提醒条 */
        .st-review-strip { display: none; padding: 10px 18px; border-bottom: 1px solid #fde68a; background: #fffbeb; }
        .st-review-strip.show { display: block; }
        .st-review-inner { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .st-review-label { font-size: 12.5px; font-weight: 700; color: #b45309; white-space: nowrap; }
        .st-review-chip { cursor: pointer; }
        .quiz-question { border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        .quiz-option { display: block; padding: 8px 10px; border-radius: 8px; margin-top: 6px; background: #f8fafc; }
        .quiz-result { border-radius: 10px; padding: 14px; margin-top: 16px; background: #eff6ff; }

        /* 空态/错误/未加入 */
        .st-empty {
            max-width: 460px;
            margin: 12vh auto 0;
            text-align: center;
            color: #64748b;
            padding: 0 20px;
        }
        .st-empty i { font-size: 44px; color: #cbd5e1; margin-bottom: 16px; display: block; }
        .st-empty h3 { font-size: 17px; font-weight: 600; color: #334155; margin-bottom: 8px; }
        .st-empty p { font-size: 13.5px; line-height: 1.8; margin-bottom: 20px; }

        /* toast */
        .st-toast {
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            bottom: 36px;
            z-index: 10000;
            background: #111827;
            color: #fff;
            font-size: 13.5px;
            padding: 10px 18px;
            border-radius: 999px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .22);
            max-width: 82vw;
            opacity: 0;
            transition: opacity .2s ease;
            pointer-events: none;
        }
        .st-toast.show { opacity: 1; }

        /* 移动端抽屉 */
        @media (max-width: 900px) {
            .st-drawer-toggle { display: inline-flex !important; }
            .st-drawer {
                position: fixed;
                top: 54px;
                bottom: 0;
                left: 0;
                width: 300px;
                transform: translateX(-102%);
                transition: transform .22s ease;
                box-shadow: 12px 0 32px rgba(15, 23, 42, .12);
            }
            .st-drawer.open { transform: translateX(0); }
            .st-drawer-mask {
                position: fixed;
                inset: 54px 0 0 0;
                background: rgba(15, 23, 42, .35);
                z-index: 34;
                display: none;
            }
            .st-drawer-mask.show { display: block; }
            .st-reading { padding: 26px 18px 110px; }
            .st-item-title { font-size: 21px; }
        }
    </style>

    @include('artifacts._dialog')

    <script src="{{ asset('js/marked.min.js') }}"></script>

    <div class="study-app" id="studyApp">
        <!-- 顶栏 -->
        <div class="st-topbar">
            <a href="/courses/{{ $id ?? 0 }}" class="st-icon-btn" title="返回课程详情页">
                <i class="fas fa-arrow-left"></i>
            </a>
            <button type="button" class="st-icon-btn st-drawer-toggle" id="stDrawerToggle" title="章节目录">
                <i class="fas fa-list-ul"></i>
            </button>
            <div class="st-title" id="stCourseTitle">沉浸学习</div>
            <div class="st-progress-chip" id="stProgressChip" title="学习进度">
                <i class="fas fa-check-circle"></i>
                <span id="stProgressText">0/0</span>
                <span class="st-progress-bar"><i id="stProgressFill" style="width:0%"></i></span>
            </div>
        </div>

        <!-- 主体 -->
        <div class="st-body">
            <div class="st-drawer-mask" id="stDrawerMask"></div>
            <aside class="st-drawer" id="stDrawer">
                <div class="st-drawer-title">
                    <span>课程结构</span>
                    <a href="/courses" class="text-xs text-gray-400 hover:text-blue-600 transition"><i class="fas fa-th-large mr-1"></i>我的课程</a>
                </div>
                <div id="stTreeBox">
                    <div class="text-center text-gray-400 py-10 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>加载中...</div>
                </div>
            </aside>

            <div class="st-main" id="stMain">
                <div class="st-review-strip" id="stReviewStrip"></div>
                <div class="st-reading" id="stReading">
                    <div class="st-empty">
                        <i class="fas fa-book-open"></i>
                        <h3>正在准备沉浸学习…</h3>
                        <p>加载课程内容中</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 测验弹窗 -->
        <div id="quizModal" class="quiz-modal">
            <div class="quiz-modal-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="quizModalTitle" class="text-xl font-semibold text-gray-900">章节小测试</h3>
                    <div class="flex items-center gap-3">
                        <button type="button" id="quizHistoryBtn" class="st-btn st-btn-ghost st-btn-xs hidden"><i class="fas fa-history mr-1"></i>作答历史</button>
                        <button type="button" id="closeQuizBtn" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
                    </div>
                </div>
                <div id="quizLoading" class="text-gray-500 py-6 text-center">加载测试中...</div>
                <form id="quizForm" class="hidden"></form>
                <div id="quizResult" class="hidden"></div>
                <div id="quizHistory" class="hidden"></div>
            </div>
        </div>

        <div class="st-toast" id="stToast"></div>
    </div>

    <script>
        (function () {
            'use strict';

            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;
            var CURRENT_USER_ID = Number('{{ auth()->id() ?: 0 }}');
            var COURSE_ID = (function () {
                var m = (window.location.pathname || '').match(/\/courses\/(\d+)(?:\/study)?\/?$/);
                return m ? Number(m[1]) : 0;
            })();
            var DEEP_ITEM_ID = (function () {
                var m = (window.location.search || '').match(/[?&]item=(\d+)/);
                return m ? Number(m[1]) : null;
            })();

            var DATA = null;
            var flat = [];          // DFS 顺序的全量节点 [{item, leaf, depth}]
            var leaves = [];        // 可完成的叶子节点（DFS 顺序）
            var currentIndex = -1;

            function escapeHtml(str) {
                return $('<div>').text(str || '').html();
            }

            function toast(msg) {
                var $t = $('#stToast');
                $t.text(msg).addClass('show');
                clearTimeout(toast._t);
                toast._t = setTimeout(function () { $t.removeClass('show'); }, 2200);
            }

            function itemIcon(type) {
                if (type === 'video') return 'fas fa-play text-blue-500';
                if (type === 'quiz') return 'fas fa-question-circle text-green-600';
                if (type === 'assignment') return 'fas fa-file-alt text-purple-500';
                if (type === 'reading') return 'fas fa-book text-amber-500';
                return 'fas fa-file text-blue-400';
            }

            function typeLabelHtml(type) {
                var text = String(type || '');
                var map = { module: '模块', chapter: '章节', video: '视频', quiz: '测验', assignment: '作业', reading: '阅读' };
                return '<span class="type-label ' + escapeHtml(text) + '">' + escapeHtml(map[text] || text.toUpperCase()) + '</span>';
            }

            function isContainer(item) {
                return (Array.isArray(item.children) && item.children.length > 0)
                    || item.item_type === 'module'
                    || item.item_type === 'chapter';
            }

            // ---------- 结构树：扁平化 + 渲染 ----------
            function flattenItems(items, depth, out) {
                (items || []).forEach(function (item) {
                    var leaf = !isContainer(item);
                    out.push({ item: item, leaf: leaf, depth: depth || 0 });
                    if (Array.isArray(item.children) && item.children.length) {
                        flattenItems(item.children, (depth || 0) + 1, out);
                    }
                });
                return out;
            }

            function renderTree(nodes, depth, openSet) {
                var html = '';
                (nodes || []).forEach(function (item) {
                    var children = Array.isArray(item.children) ? item.children : [];
                    var isC = isContainer(item);
                    var idx = flat.findIndex(function (f) { return Number(f.item.id) === Number(item.id); });
                    var cls = 'st-node' + (idx === currentIndex ? ' active' : '') + (item.is_completed ? ' done' : '');
                    var icon = isC
                        ? '<i class="fas fa-folder text-yellow-500 st-node-icon"></i>'
                        : '<i class="' + itemIcon(item.item_type) + ' st-node-icon"></i>';
                    var caret = isC ? '<span class="st-caret"><i class="fas fa-chevron-right" style="transition:transform .15s ease"></i></span>' : '<span class="st-caret"></span>';
                    // 当前章节所在层级的祖先容器自动展开，保证"学到哪就看到哪"
                    var isOpen = isC && (idx === currentIndex || (openSet && openSet[Number(item.id)]));
                    html += '<li>'
                        + '<div class="' + cls + '" data-id="' + Number(item.id) + '" data-container="' + (isC ? '1' : '0') + '" style="padding-left:' + (10 + (depth || 0) * 16) + 'px">'
                        + caret + icon
                        + '<span class="st-label">' + escapeHtml(item.title || '未命名') + '</span>'
                        + (item.duration ? '<span class="st-dur">' + Number(item.duration) + '′</span>' : '')
                        + '</div>'
                        + (children.length ? '<ul class="st-tree-children' + (isOpen ? ' open' : '') + '">' + renderTree(children, (depth || 0) + 1, openSet) + '</ul>' : '')
                        + '</li>';
                });
                return html;
            }

            // 计算当前章节的全部祖先容器 id（用于树自动展开定位）
            function computeAncestors(items, targetId) {
                var result = {};
                var stack = [];
                var walk = function (nodes) {
                    for (var i = 0; i < (nodes || []).length; i++) {
                        var n = nodes[i];
                        if (Number(n.id) === Number(targetId)) {
                            stack.forEach(function (id) { result[id] = true; });
                            return true;
                        }
                        if (Array.isArray(n.children) && n.children.length) {
                            if (isContainer(n)) stack.push(Number(n.id));
                            var found = walk(n.children);
                            if (found) return true;
                            if (isContainer(n)) stack.pop();
                        }
                    }
                    return false;
                };
                walk(items || []);
                return result;
            }

            function renderDrawer() {
                var openSet = currentIndex >= 0 && flat[currentIndex]
                    ? computeAncestors(DATA.structure, Number(flat[currentIndex].item.id))
                    : {};
                $('#stTreeBox').html('<ul class="st-tree">' + renderTree(DATA.structure, 0, openSet) + '</ul>');
                // 抽屉滚动定位到当前章节
                var $active = $('#stTreeBox .st-node.active').first();
                if ($active.length) {
                    var el = $active[0];
                    try { el.scrollIntoView({ block: 'nearest' }); } catch (e) { el.scrollIntoView(); }
                }
            }

            // ---------- 进度 ----------
            function renderProgress() {
                var done = leaves.filter(function (f) { return f.item.is_completed; }).length;
                var total = leaves.length;
                var pct = total > 0 ? Math.round(done * 100 / total) : 0;
                $('#stProgressText').text(done + '/' + total + ' · ' + pct + '%');
                $('#stProgressFill').css('width', Math.round(pct) + '%');
                return { done: done, total: total, pct: pct };
            }

            function renderProgressForCourse() {
                var total = leaves.length;
                var done = leaves.filter(function (f) { return f.item.is_completed; }).length;
                var pct = total > 0 ? Math.round(done * 100 / total) : 0;
                return { done: done, total: total, pct: pct };
            }

            // ---------- 章节正文渲染 ----------
            function renderReading(idx) {
                if (!flat.length) {
                    $('#stReading').html(
                        '<div class="st-empty"><i class="fas fa-inbox"></i>'
                        + '<h3>课程还没有内容</h3><p>管理员尚未添加章节，先去课程详情页看看吧。</p>'
                        + '<a href="/courses/' + Number(COURSE_ID) + '" class="st-btn st-btn-primary">返回课程详情</a></div>'
                    );
                    return;
                }
                var f = flat[idx];
                var item = f.item;
                var isC = !f.leaf;
                currentIndex = idx;
                renderDrawer();

                var title = '<h1 class="st-item-title">' + escapeHtml(item.title || '未命名章节') + '</h1>';
                var meta = '<div class="st-meta">' + typeLabelHtml(item.item_type);
                if (item.duration) meta += '<span class="st-meta-chip"><i class="far fa-clock"></i>' + Number(item.duration) + ' 分钟</span>';
                if (item.order_index) meta += '<span class="st-meta-chip"><i class="fas fa-hashtag"></i>排序 ' + Number(item.order_index) + '</span>';
                meta += '</div>';

                var body = '';
                if (isC) {
                    // 容器：描述 + 子章节目录
                    body += '<div class="st-desc">' + (item.description ? String(escapeHtml(item.description)).replace(/\n/g, '<br>') : '<span class="text-gray-400">（无描述）</span>') + '</div>';
                    if (Array.isArray(item.children) && item.children.length) {
                        body += '<h4 style="font-size:15px;font-weight:700;color:#334155;margin:18px 0 12px"><i class="fas fa-list-ol text-blue-500 mr-2"></i>目录</h4>';
                        body += '<div class="st-chapter-list">';
                        item.children.forEach(function (child) {
                            body += '<div class="st-chapter-row' + (child.is_completed ? ' done' : '') + '" data-id="' + Number(child.id) + '">'
                                + '<i class="' + (child.is_completed ? 'fas fa-check-circle text-green-500' : (isContainer(child) ? 'fas fa-folder text-yellow-500' : itemIcon(child.item_type))) + '" style="flex-shrink:0"></i>'
                                + '<span class="txt">' + escapeHtml(child.title || '') + '</span>'
                                + '<i class="fas fa-chevron-right text-gray-300" style="flex-shrink:0;font-size:11px"></i>'
                                + '</div>';
                        });
                        body += '</div>';
                    }
                } else {
                    // 叶子
                    if (item.description) body += '<div class="st-desc">' + String(escapeHtml(item.description)).replace(/\n/g, '<br>') + '</div>';
                    if (item.external_url) {
                        body += '<div class="st-external"><span class="txt"><i class="fas fa-external-link-alt"></i>外部学习资源</span>'
                            + '<a href="' + escapeHtml(item.external_url) + '" target="_blank" rel="noopener" class="st-btn st-btn-primary"><i class="fas fa-external-link-alt mr-1"></i>打开链接</a></div>';
                    }
                    if (item.content && String(item.content).trim()) {
                        body += '<div class="st-md-toolbar" id="stMdToolbar" style="display:none">'
                            + '<span class="text-xs text-gray-400"><i class="fas fa-list-ol mr-1"></i>按标题折叠</span>'
                            + '<button type="button" class="st-btn st-btn-ghost st-btn-xs" id="stMdExpandAll"><i class="fas fa-expand-arrows-alt mr-1"></i>全部展开</button>'
                            + '<button type="button" class="st-btn st-btn-ghost st-btn-xs" id="stMdCollapseAll"><i class="fas fa-compress-arrows-alt mr-1"></i>全部折叠</button>'
                            + '</div>'
                            + '<div class="md-content">' + escapeHtml(item.content) + '</div>';
                    }
                    if (!item.content && !item.external_url && !item.description) {
                        body += '<div class="text-center text-gray-400 py-10"><i class="fas fa-folder-open text-3xl mb-3 text-gray-300"></i><p>该章节暂无内容</p></div>';
                    }
                }

                // 操作区
                var actions = '';
                if (!isC) {
                    actions += '<div class="st-actions">';
                    if (item.is_completed) {
                        actions += '<button type="button" class="st-btn st-btn-success" disabled><i class="fas fa-check-circle mr-1"></i>已完成</button>';
                    } else {
                        actions += '<button type="button" class="st-btn st-btn-success" id="stCompleteBtn"><i class="fas fa-check mr-1"></i>标记完成</button>';
                    }
                    actions += '<button type="button" class="st-btn st-btn-ghost st-quiz-btn"><i class="fas fa-question-circle mr-1"></i>小测试</button>';
                    actions += '</div>';
                    // AI 制品：把本节内容加入制品库（与详情页一致的能力）
                    if (typeof window.openArtifactDialog === 'function') {
                        actions += '<div class="st-actions">'
                            + '<span class="text-xs text-gray-400 self-center mr-1"><i class="fas fa-wand-magic-sparkles text-purple-500 mr-1"></i>AI 制品</span>'
                            + '<button type="button" class="st-btn st-btn-ghost" onclick="window.stArtifact(\'visual_reading\')"><i class="fas fa-book-open mr-1"></i>可视化界面</button>'
                            + '<button type="button" class="st-btn st-btn-ghost" onclick="window.stArtifact(\'mind_map\')"><i class="fas fa-diagram-project mr-1"></i>思维导图</button>'
                            + '<button type="button" class="st-btn st-btn-ghost" onclick="window.stArtifact(\'key_points\')"><i class="fas fa-lightbulb mr-1"></i>关键信息</button>'
                            + '<button type="button" class="st-btn st-btn-ghost" onclick="window.stArtifact(\'ai_ppt\')"><i class="fas fa-file-powerpoint mr-1"></i>AIPPT</button>'
                            + '</div>';
                    }
                }

                // 上/下一节
                var nav = '<div class="st-nav">'
                    + '<button type="button" class="st-nav-btn" id="stPrevBtn"' + (idx <= 0 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i>上一节</button>'
                    + '<span class="spacer"></span>'
                    + '<span class="text-xs text-gray-400">' + (idx + 1) + ' / ' + flat.length + '</span>'
                    + '<button type="button" class="st-nav-btn" id="stNextBtn"' + (idx >= flat.length - 1 ? ' disabled' : '') + '>下一节<i class="fas fa-chevron-right"></i></button>'
                    + '</div>';

                $('#stReading').html(title + meta + body + actions + nav);
                renderMarkdown();
                if (!isC) {
                    bindLeavesActions(item);
                }
                bindChapterRows();
                bindNavButtons(idx);
                document.title = (item.title || '章节') + ' · ' + (DATA.course.title || '课程') + ' - 沉浸学习';
                if (window.innerWidth <= 900) closeDrawer();
            }

            // markdown 渲染（复用 marked + 轻量清理）
            function renderMarkdown() {
                var blocks = document.querySelectorAll('#stReading .md-content');
                blocks.forEach(function (el) {
                    var raw = String(el.textContent || '');
                    if (!raw.trim()) return;
                    var rendered = null;
                    try {
                        if (window.marked && typeof window.marked.parse === 'function') {
                            rendered = window.marked.parse(raw, { gfm: true, breaks: true });
                        } else if (window.marked) {
                            rendered = window.marked(raw);
                        }
                    } catch (e) { return; }
                    if (!rendered) return;
                    var tmp = document.createElement('div');
                    tmp.innerHTML = rendered;
                    tmp.querySelectorAll('script, iframe, object, embed, link, meta, style').forEach(function (n) { n.remove(); });
                    tmp.querySelectorAll('*').forEach(function (n) {
                        Array.prototype.slice.call(n.attributes).forEach(function (attr) {
                            if (/^on/i.test(attr.name)) n.removeAttribute(attr.name);
                        });
                    });
                    el.innerHTML = tmp.innerHTML;
                    enableHeadingFold(el);
                });
            }

            // 按标题折叠/展开：把 H1~H4 及其后续内容按层级包成 .md-section，
            // 点击标题即可折叠/展开该小节（嵌套层级、CSS 一收全收）
            function enableHeadingFold(content) {
                var $toolbar = $('#stMdToolbar');
                var headings = content.querySelectorAll('h1, h2, h3, h4');
                if (!headings.length) {
                    if ($toolbar.length) $toolbar.hide();
                    return;
                }
                if ($toolbar.length) $toolbar.show();

                // 1) 按标题层级重组 DOM：h1 小节内嵌套 h2 小节…
                var children = Array.prototype.slice.call(content.children);
                var stack = []; // {el, level}
                children.forEach(function (node) {
                    var m = node.tagName ? node.tagName.match(/^H([1-4])$/) : null;
                    if (m) {
                        var level = parseInt(m[1], 10);
                        while (stack.length && stack[stack.length - 1].level >= level) {
                            stack.pop();
                        }
                        var icon = document.createElement('span');
                        icon.className = 'md-toggle-icon';
                        icon.innerHTML = '<i class="fas fa-chevron-right"></i>';
                        node.insertBefore(icon, node.firstChild);
                        node.classList.add('md-head-toggle');
                        var sec = document.createElement('div');
                        sec.className = 'md-section';
                        sec.appendChild(node);
                        var parent = stack.length ? stack[stack.length - 1].el : content;
                        parent.appendChild(sec);
                        stack.push({ el: sec, level: level });
                    } else {
                        if (stack.length) {
                            stack[stack.length - 1].el.appendChild(node);
                        }
                        // 首个标题之前的内容留在根层
                    }
                });

                // 2) 点击标题折叠/展开
                content.querySelectorAll('.md-head-toggle').forEach(function (h) {
                    h.addEventListener('click', function () {
                        h.closest('.md-section').classList.toggle('collapsed');
                    });
                });

                // 3) 全部展开 / 全部折叠
                $('#stMdExpandAll').off('click').on('click', function () {
                    content.querySelectorAll('.md-section').forEach(function (s) { s.classList.remove('collapsed'); });
                });
                $('#stMdCollapseAll').off('click').on('click', function () {
                    content.querySelectorAll('.md-section').forEach(function (s) { s.classList.add('collapsed'); });
                });
            }

            function bindLeavesActions(item) {
                // 标记完成
                var $btn = $('#stCompleteBtn');
                if ($btn.length) {
                    $btn.on('click', function () {
                        var $b = $(this);
                        if ($b.hasClass('done') || $b.attr('disabled')) return;
                        if (!apiRequest) { toast('API 客户端未初始化'); return; }
                        $b.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>处理中');
                        apiRequest('POST', '/course-items/' + Number(item.id) + '/complete', {}).then(function (resp) {
                            if (resp && resp.code === 9999) {
                                item.is_completed = true;
                                renderProgress();
                                toast('已完成「' + (item.title || '本节') + '」');
                                var p = renderProgressForCourse();
                                if (p.total > 0 && p.done >= p.total) {
                                    toast('🎉 恭喜！课程已全部完成');
                                    renderReading(currentIndex);
                                    return;
                                }
                                // 自动前进到下一节未完成
                                var nxt = leaves.find(function (f) { return !f.item.is_completed; });
                                if (nxt) {
                                    var ni = flat.findIndex(function (f) { return f.item.id === nxt.item.id; });
                                    if (ni >= 0) renderReading(ni);
                                } else {
                                    renderReading(currentIndex);
                                }
                                return;
                            }
                            toast((resp && resp.msg) ? resp.msg : '标记完成失败');
                            $b.prop('disabled', false).html('<i class="fas fa-check mr-1"></i>标记完成');
                        }).catch(function () {
                            toast('网络错误，请稍后重试');
                            $b.prop('disabled', false).html('<i class="fas fa-check mr-1"></i>标记完成');
                        });
                    });
                }
            }

            function bindChapterRows() {
                $('#stReading .st-chapter-row').off('click').on('click', function () {
                    var id = Number($(this).data('id') || 0);
                    var idx = flat.findIndex(function (f) { return Number(f.item.id) === id; });
                    if (idx >= 0) renderReading(idx);
                });
            }

            function bindNavButtons(idx) {
                $('#stPrevBtn').off('click').on('click', function () {
                    if (idx > 0) renderReading(idx - 1);
                });
                $('#stNextBtn').off('click').on('click', function () {
                    if (idx < flat.length - 1) renderReading(idx + 1);
                });
            }

            // ---------- 抽屉 ----------
            function openDrawer() {
                $('#stDrawer').addClass('open');
                $('#stDrawerMask').addClass('show');
            }

            function closeDrawer() {
                $('#stDrawer').removeClass('open');
                $('#stDrawerMask').removeClass('show');
            }

            // ---------- 测验 ----------
            // 弹窗状态缓存（返回测验时保留已填表单 / 已出结果）
            var quizState = { itemId: 0, formHtml: '', resultHtml: '', questions: null, view: 'form' };

            function openQuiz(itemId) {
                if (!apiRequest || !itemId) return;
                quizState = { itemId: itemId, formHtml: '', resultHtml: '', questions: null, view: 'form' };
                $('#quizModalTitle').text('章节小测试');
                $('#quizModal').addClass('show');
                $('#quizLoading').removeClass('hidden').text('加载测试中...');
                $('#quizForm').addClass('hidden').empty();
                $('#quizResult').addClass('hidden').empty();
                $('#quizHistory').addClass('hidden').empty();
                $('#quizHistoryBtn').addClass('hidden');
                $('#quizForm').data('item-id', itemId);
                apiRequest('GET', '/course-items/' + itemId + '/quiz', {}).then(function (resp) {
                    if (!resp || resp.code !== 9999 || !resp.result || !resp.result.quiz) throw new Error((resp && resp.msg) || '该章节暂无测试');
                    var quiz = resp.result.quiz;
                    var html = '<p class="text-sm text-gray-500 mb-4">通过分数：' + Number(quiz.passing_score || 70) + '%</p>';
                    (quiz.questions || []).forEach(function (question, index) {
                        var multiple = question.question_type === 'multiple';
                        html += '<div class="quiz-question"><div class="font-medium text-gray-900">' + (index + 1) + '. ' + escapeHtml(question.question || '') + '</div>';
                        (question.options || []).forEach(function (option) {
                            html += '<label class="quiz-option"><input type="' + (multiple ? 'checkbox' : 'radio') + '" name="quiz-answer-' + Number(question.id) + '" value="' + escapeHtml(option.option_key || '') + '" class="mr-2">' + escapeHtml(option.option_key || '') + '. ' + escapeHtml(option.content || '') + '</label>';
                        });
                        html += '</div>';
                    });
                    html += '<button type="submit" class="st-btn st-btn-primary">提交测试</button>';
                    quizState.formHtml = html;
                    quizState.questions = quiz.questions || [];
                    $('#quizForm').html(html).removeClass('hidden');
                    $('#quizLoading').addClass('hidden');
                    $('#quizHistoryBtn').removeClass('hidden');
                }).catch(function (err) {
                    renderQuizEmpty(itemId, (err && err.message) ? err.message : '该章节还没有配置小测试');
                });
            }

            // 章节没有测试时的友好空态（不再是一片空白/报错文本）
            function renderQuizEmpty(itemId, msg) {
                var isOwner = DATA && DATA.course && !!DATA.course.is_owner;
                var html = '<div class="py-10 text-center">'
                    + '<i class="fas fa-question-circle text-4xl text-gray-300 mb-3" style="display:block"></i>'
                    + '<p class="text-gray-600 mb-1">' + escapeHtml(msg || '该章节还没有配置小测试') + '</p>'
                    + '<p class="text-xs text-gray-400 mb-5">可以稍后再来看看，或联系课程创建者补充测验。</p>'
                    + (isOwner ? '<a href="/courses/' + COURSE_ID + '/items" class="st-btn st-btn-primary st-btn-sm"><i class="fas fa-cog mr-1"></i>去配置测验</a> ' : '')
                    + '<button type="button" class="st-btn st-btn-ghost st-btn-sm" onclick="closeQuiz()">关闭</button>'
                    + '</div>';
                $('#quizLoading').html(html).removeClass('hidden');
                $('#quizForm').addClass('hidden').empty();
                $('#quizResult').addClass('hidden').empty();
                $('#quizHistory').addClass('hidden').empty();
                $('#quizHistoryBtn').addClass('hidden');
            }

            function showQuizView(itemId) {
                $('#quizHistory').addClass('hidden').empty();
                if (itemId !== quizState.itemId) { openQuiz(itemId); return; }
                $('#quizHistoryBtn').removeClass('hidden');
                if (quizState.view === 'result' && quizState.resultHtml) {
                    $('#quizForm').addClass('hidden');
                    $('#quizResult').html(quizState.resultHtml).removeClass('hidden');
                } else if (quizState.formHtml) {
                    $('#quizResult').addClass('hidden').empty();
                    $('#quizForm').removeClass('hidden');
                } else {
                    openQuiz(itemId);
                }
            }

            function openQuizHistory(itemId) {
                if (!apiRequest || !itemId) return;
                $('#quizLoading').addClass('hidden').empty();
                $('#quizForm').addClass('hidden');
                $('#quizResult').addClass('hidden').empty();
                $('#quizHistory').removeClass('hidden').html(
                    '<div class="text-center text-gray-400 py-8"><i class="fas fa-spinner fa-spin mr-2"></i>加载作答记录...</div>'
                );
                apiRequest('GET', '/course-items/' + itemId + '/quiz/attempts', {}).then(function (resp) {
                    var attempts = (resp && resp.code === 9999 && resp.result && Array.isArray(resp.result.attempts)) ? resp.result.attempts : [];
                    if (!attempts.length) {
                        $('#quizHistory').html(
                            '<div class="text-center text-gray-400 py-10"><i class="fas fa-inbox text-3xl mb-3" style="display:block"></i>'
                            + '<p>还没有作答记录</p>'
                            + '<button type="button" class="st-btn st-btn-primary st-btn-sm mt-4" onclick="showQuizView(' + itemId + ')">返回测验</button></div>'
                        );
                        return;
                    }
                    var qMap = {};
                    if (quizState.itemId === itemId && Array.isArray(quizState.questions)) {
                        quizState.questions.forEach(function (q) { qMap[Number(q.id)] = q; });
                    }
                    var html = '<div class="flex items-center justify-between mb-3">'
                        + '<span class="text-sm font-semibold text-gray-700"><i class="fas fa-history mr-1"></i>共 ' + attempts.length + ' 次作答</span>'
                        + '<button type="button" class="st-btn st-btn-ghost st-btn-sm" onclick="showQuizView(' + itemId + ')">返回测验</button>'
                        + '</div>';
                    attempts.forEach(function (a, index) {
                        var passed = !!a.passed;
                        var isLast = index === 0;
                        html += '<div class="quiz-result" style="background:' + (passed ? '#ecfdf5' : '#fef2f2') + '">'
                            + '<div class="flex items-center justify-between flex-wrap gap-1">'
                            + '<span class="font-semibold ' + (passed ? 'text-green-700' : 'text-red-700') + '">' + (isLast ? '最近一次 · ' : '') + (passed ? '通过' : '未通过') + ' · ' + Number(a.score || 0) + '%</span>'
                            + '<span class="text-xs text-gray-400">' + escapeHtml(a.completed_at ? String(a.completed_at).replace('T', ' ').substring(0, 16) : '') + '</span>'
                            + '</div>'
                            + '<div class="text-xs text-gray-500 mt-1">答对 ' + Number(a.correct_count || 0) + ' / ' + Number(a.total_count || 0) + ' 题</div>';
                        (a.answers || []).forEach(function (row, qIdx) {
                            var qt = qMap[Number(row.question_id)];
                            html += '<div class="text-sm mt-2" style="border-top:1px dashed #e2e8f0;padding-top:8px">'
                                + '<div class="text-gray-700">' + (qt ? escapeHtml(qt.question) : ('第 ' + (qIdx + 1) + ' 题')) + '</div>'
                                + '<div class="text-xs mt-1 text-gray-600">你的答案：' + escapeHtml((row.submitted && row.submitted.length ? row.submitted : ['（未作答）']).join('、'))
                                + (row.correct ? ' <span class="text-green-600">✓ 正确</span>' : ' <span class="text-red-600">✗ 错误，正确答案：' + escapeHtml((row.correct_options || []).join('、')) + '</span>') + '</div>'
                                + (row.explanation ? '<div class="text-xs text-gray-500 mt-1">解析：' + escapeHtml(row.explanation) + '</div>' : '')
                                + '</div>';
                        });
                        html += '</div>';
                    });
                    $('#quizHistory').html(html);
                }).catch(function () {
                    $('#quizHistory').html(
                        '<div class="text-center text-gray-400 py-10"><p>作答历史加载失败</p>'
                        + '<button type="button" class="st-btn st-btn-ghost st-btn-sm mt-4" onclick="showQuizView(' + itemId + ')">返回测验</button></div>'
                    );
                });
            }

            // 待复习提醒条：拉取本课程到期的复习项
            function loadStudyReviews() {
                var $s = $('#stReviewStrip');
                $s.removeClass('show').empty();
                if (!CURRENT_USER_ID || !apiRequest || !DATA || !DATA.is_joined) return;
                apiRequest('GET', '/reviews/course-items', {}).then(function (resp) {
                    var all = (resp && resp.code === 9999 && resp.result && Array.isArray(resp.result.reviews)) ? resp.result.reviews : [];
                    var reviews = all.filter(function (r) {
                        return r && r.course_item && Number(r.course_item.course_id) === Number(COURSE_ID);
                    });
                    if (!reviews.length) return;
                    reviews.sort(function (a, b) {
                        return String(a.next_review_at || '9999-99-99').localeCompare(String(b.next_review_at || '9999-99-99'));
                    });
                    var chips = reviews.map(function (r) {
                        var item = r.course_item || {};
                        return '<button type="button" class="st-btn st-btn-ghost st-btn-xs st-review-chip" data-item-id="' + Number(item.id) + '" title="上次 ' + (r.last_score === null ? '—' : (Number(r.last_score) + '%')) + ' · 到期 ' + (r.next_review_at ? escapeHtml(String(r.next_review_at).replace('T', ' ').substring(0, 10)) : '') + '">'
                            + escapeHtml(item.title || '未命名章节') + '</button>';
                    }).join('');
                    $s.html('<div class="st-review-inner"><span class="st-review-label"><i class="fas fa-sync-alt mr-1"></i>待复习</span>' + chips + '</div>').addClass('show');
                    $('#stReviewStrip .st-review-chip').off('click').on('click', function () {
                        var id = Number($(this).data('item-id') || 0);
                        var idx = flat.findIndex(function (f) { return Number(f.item.id) === id; });
                        if (idx >= 0) renderReading(idx);
                        openQuiz(id);
                    });
                }).catch(function () { $s.removeClass('show').empty(); });
            }

            function submitQuiz() {
                var itemId = Number($('#quizForm').data('item-id') || 0);
                var answers = {};
                $('#quizForm input:checked').each(function () {
                    var questionId = $(this).attr('name').replace('quiz-answer-', '');
                    if (!answers[questionId]) answers[questionId] = [];
                    answers[questionId].push($(this).val());
                });
                apiRequest('POST', '/course-items/' + itemId + '/quiz/attempts', { answers: answers }).then(function (resp) {
                    if (!resp || resp.code !== 9999) throw new Error((resp && resp.msg) || '提交失败');
                    var result = resp.result || {};
                    quizState.view = 'result';
                    var html = '<div class="quiz-result"><div class="font-semibold ' + (result.passed ? 'text-green-700' : 'text-red-700') + '">' + (result.passed ? '测试通过' : '需要复习') + '：' + Number(result.score || 0) + '%</div>';
                    (result.results || []).forEach(function (row, index) {
                        html += '<div class="text-sm mt-2">第 ' + (index + 1) + ' 题：' + (row.correct ? '<span class="text-green-600">正确</span>' : '<span class="text-red-600">错误，正确答案：' + escapeHtml((row.correct_options || []).join(', ')) + '</span>') + (row.explanation ? '<div class="text-gray-500">解析：' + escapeHtml(row.explanation) + '</div>' : '') + '</div>';
                    });
                    html += '</div><div class="mt-4 flex flex-wrap gap-2">'
                        + '<button type="button" class="st-btn st-btn-ghost" onclick="showQuizView(' + itemId + ')">返回测验</button>'
                        + '<button type="button" class="st-btn st-btn-ghost" onclick="openQuizHistory(' + itemId + ')">查看作答历史</button>'
                        + '<button type="button" class="st-btn st-btn-primary" onclick="closeQuiz()">关闭</button>'
                        + '</div>';
                    $('#quizForm').addClass('hidden');
                    $('#quizResult').html(html).removeClass('hidden');
                    $('#quizHistoryBtn').removeClass('hidden');
                    // 测验及格 = 该课时完成（与服务端聚合规则一致），本地同步进度
                    if (result.passed && currentIndex >= 0) {
                        var cur = flat[currentIndex];
                        if (cur && !cur.item.is_completed) {
                            cur.item.is_completed = true;
                            renderProgress();
                        }
                    }
                    loadStudyReviews();
                }).catch(function (err) {
                    $('#quizResult').html('<div class="quiz-result text-red-600">' + escapeHtml(err && err.message ? err.message : '提交失败') + '</div>').removeClass('hidden');
                });
            }

            function closeQuiz() {
                $('#quizModal').removeClass('show');
                $('#quizResult').addClass('hidden').empty();
                $('#quizHistory').addClass('hidden').empty();
                // 测验及格已把本节标记为完成：关闭弹窗后刷新按钮态
                if (currentIndex >= 0 && flat[currentIndex] && flat[currentIndex].item.is_completed) {
                    var $btn = $('#stCompleteBtn');
                    if ($btn.length && !$btn.attr('disabled')) renderReading(currentIndex);
                }
            }

            // 供页面内联 onclick 使用（closeQuiz 在 IIFE 内部）
            window.closeQuiz = closeQuiz;
            window.showQuizView = showQuizView;
            window.openQuizHistory = openQuizHistory;

            // 生成 AI 制品（复用制品库对话框，把当前章节加入制品库）
            window.stArtifact = function (type) {
                var f = flat[currentIndex];
                if (!f || typeof window.openArtifactDialog !== 'function') return;
                window.openArtifactDialog({
                    relatedType: 'course_item',
                    relatedId: Number(f.item.id),
                    artifactType: String(type || 'key_points')
                });
            };

            // ---------- 数据加载 ----------
            function renderNotJoined() {
                $('#stReading').html(
                    '<div class="st-empty"><i class="fas fa-user-plus"></i>'
                    + '<h3>加入课程后才能沉浸学习</h3><p>加入后系统会记录你的逐节进度，并自动从下一节未学内容继续。</p>'
                    + '<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">'
                    + '<button type="button" class="st-btn st-btn-success" id="stJoinBtn"><i class="fas fa-user-plus mr-1"></i>加入课程</button>'
                    + '<a href="/courses/' + Number(COURSE_ID) + '" class="st-btn st-btn-ghost">返回详情</a>'
                    + '</div></div>'
                );
                $('#stJoinBtn').off('click').on('click', function () {
                    var $b = $(this);
                    $b.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>加入中...');
                    apiRequest('POST', '/courses/' + Number(COURSE_ID) + '/join', {}).then(function (resp) {
                        if (resp && resp.code === 9999) { loadStudy(); return; }
                        toast((resp && resp.msg) ? resp.msg : '加入失败');
                        $b.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i>加入课程');
                    }).catch(function () {
                        toast('网络错误，请稍后重试');
                        $b.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i>加入课程');
                    });
                });
            }

            function renderError(msg) {
                $('#stReading').html(
                    '<div class="st-empty"><i class="fas fa-exclamation-triangle"></i>'
                    + '<h3>加载失败</h3><p>' + escapeHtml(msg || '课程加载失败，请稍后重试') + '</p>'
                    + '<a href="/courses" class="st-btn st-btn-primary">返回我的课程</a></div>'
                );
            }

            function loadStudy() {
                if (!apiRequest) { renderError('API 客户端未初始化'); return; }
                if (!COURSE_ID) { renderError('课程 ID 无效'); return; }

                apiRequest('GET', '/courses/' + COURSE_ID, {}).then(function (resp) {
                    if (!resp || resp.code !== 9999) throw new Error((resp && resp.msg) || '课程不存在或有权限限制');
                    var result = resp.result || {};
                    var course = result.course || {};
                    DATA = {
                        course: course,
                        structure: Array.isArray(result.structure) ? result.structure : [],
                        is_joined: !!result.is_joined,
                        user_course: result.user_course || null
                    };

                    $('#stCourseTitle').text(course.title || '沉浸学习');
                    document.title = (course.title || '课程') + ' - 沉浸学习';

                    if (!DATA.is_joined) { renderNotJoined(); return; }

                    flat = flattenItems(DATA.structure, 0, []);
                    leaves = flat.filter(function (f) { return f.leaf; });
                    renderProgress();

                    // 定位：?item= 深链 > 下一节未学 > 第一个可学
                    var targetIdx = -1;
                    if (DEEP_ITEM_ID) {
                        targetIdx = flat.findIndex(function (f) { return Number(f.item.id) === DEEP_ITEM_ID; });
                    }
                    if (targetIdx < 0) {
                        var next = leaves.find(function (f) { return !f.item.is_completed; });
                        if (next) {
                            targetIdx = flat.findIndex(function (f) { return f.item.id === next.item.id; });
                        }
                    }
                    if (targetIdx < 0 && flat.length) targetIdx = 0;

                    if (targetIdx < 0) { renderReading(-1); } else { renderReading(targetIdx); }

                    loadStudyReviews();
                }).catch(function (err) {
                    renderError(err && err.message ? err.message : '课程加载失败，请稍后重试');
                });
            }

            // ---------- 初始化 ----------
            $(document).ready(function () {
                loadStudy();

                // 抽屉开合
                $('#stDrawerToggle').on('click', function () {
                    if ($('#stDrawer').hasClass('open')) closeDrawer(); else openDrawer();
                });
                $('#stDrawerMask').on('click', closeDrawer);

                // 树节点点击（事件委托，重渲染后仍有效）
                $(document).on('click', '#stTreeBox .st-node', function (e) {
                    e.preventDefault();
                    var id = Number($(this).data('id') || 0);
                    var isC = $(this).data('container') === '1';
                    if (isC) {
                        var $children = $(this).next('.st-tree-children');
                        $children.toggleClass('open');
                        $(this).find('.st-caret i').css('transform', $children.hasClass('open') ? 'rotate(90deg)' : '');
                    }
                    var idx = flat.findIndex(function (f) { return Number(f.item.id) === id; });
                    if (idx >= 0) renderReading(idx);
                });

                // 测验
                $('#closeQuizBtn').on('click', closeQuiz);
                $('#quizHistoryBtn').on('click', function () { openQuizHistory(quizState.itemId); });
                $('#quizModal').on('click', function (e) { if (e.target === this) closeQuiz(); });
                $(document).on('click', '.st-quiz-btn', function () {
                    var f = flat[currentIndex];
                    if (f && !f.leaf) return;
                    openQuiz(f ? Number(f.item.id) : 0);
                });
                $('#quizForm').on('submit', function (e) { e.preventDefault(); submitQuiz(); });

                // 键盘导航
                $(document).on('keydown', function (e) {
                    if (e.key === 'Escape') {
                        if ($('#quizModal').hasClass('show')) { closeQuiz(); return; }
                        closeDrawer();
                        return;
                    }
                    if ($('#quizModal').hasClass('show')) return;
                    if (e.key === 'ArrowRight' && currentIndex >= 0 && currentIndex < flat.length - 1) {
                        e.preventDefault(); renderReading(currentIndex + 1);
                    } else if (e.key === 'ArrowLeft' && currentIndex > 0) {
                        e.preventDefault(); renderReading(currentIndex - 1);
                    }
                });
            });
        })();
    </script>
@endsection