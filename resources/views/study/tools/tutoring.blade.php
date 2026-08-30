@extends('layouts.app')

@section('title', '远程辅导 - 蒙太奇')

@section('content')
    <style>
        .tt-shell { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 10px; height: calc(100vh - 140px); min-height: 520px; }
        .tt-topbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 8px 12px; border: 1px solid #e4e7ec; border-radius: 14px; background: #fff; }
        .tt-body { display: flex; gap: 10px; flex: 1; min-height: 0; }
        .tt-content-panel { flex: 1; min-width: 0; display: flex; flex-direction: column; border: 1px solid #e4e7ec; border-radius: 14px; background: #fff; overflow: hidden; }
        .tt-toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 8px 10px; border-bottom: 1px solid #eef0f3; background: #fafbfc; }
        .tt-video-panel { width: 320px; flex: 0 0 320px; display: flex; flex-direction: column; gap: 8px; min-height: 0; }
        .tt-stage-wrap { position: relative; flex: 1; min-height: 0; overflow: hidden; background: #f3f4f6; }
        #ttStage { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; overflow: auto; }
        #ttStage .content-iframe, #ttStage .content-object, #ttStage .content-img { width: 100%; height: 100%; border: 0; }
        #ttStage .content-img { object-fit: contain; }
        #ttStage .content-text { width: 100%; height: 100%; overflow: auto; padding: 24px; font-size: 15px; line-height: 1.8; color: #111827; background: #fff; font-family: -apple-system, "PingFang SC", "Microsoft YaHei", sans-serif; white-space: pre-wrap; word-break: break-word; }
        #ttStage .tt-empty { text-align: center; color: #98a2b3; padding: 40px 20px; }
        #ttStage .tt-empty i { font-size: 40px; display: block; margin-bottom: 12px; }
        #ttStage .tt-empty-desc { font-size: 13px; line-height: 2; }
        #ttWbCanvas { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 20; cursor: crosshair; touch-action: none; display: none; }
        #ttWbCanvas.on { display: block; }
        .tt-stage-caption { position: absolute; left: 10px; top: 10px; z-index: 25; max-width: 70%; font-size: 11px; color: #475467; background: rgba(255,255,255,.92); border: 1px solid #e4e7ec; border-radius: 8px; padding: 4px 8px; display: flex; align-items: center; gap: 6px; }
        @media (max-width: 900px) {
            .tt-shell { height: auto; }
            .tt-body { flex-direction: column; align-items: stretch; }
            .tt-content-panel { flex: 1 1 auto; min-height: 62vh; }
            .tt-video-panel { width: 100%; flex: none; }
            .tt-video-remote { min-height: 200px; }
        }
        .tt-btn { display: inline-flex; align-items: center; gap: 6px; border-radius: 10px; border: 1px solid #d0d5dd; background: #fff; color: #344054; font-size: 13px; padding: 7px 12px; cursor: pointer; }
        .tt-btn:hover { border-color: #98a2b3; }
        .tt-btn.active { border-color: #1e3a8a; color: #1e3a8a; background: #eef4ff; }
        .tt-btn.primary { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .tt-btn.danger { color: #dc2626; border-color: #fca5a5; }
        .tt-btn:disabled { opacity: .5; cursor: not-allowed; }
        .tt-input { border: 1px solid #d0d5dd; border-radius: 10px; font-size: 13px; padding: 7px 10px; outline: none; }
        .tt-input:focus { border-color: #1e3a8a; }
        .tt-input.error { border-color: #dc2626; background: #fef2f2; }
        .tt-select { border: 1px solid #d0d5dd; border-radius: 8px; font-size: 12px; padding: 5px 6px; background: #fff; }
        .tt-room-card { border: 1px solid #e4e7ec; border-radius: 14px; background: #fff; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
        .tt-room-title { font-size: 13px; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 6px; }
        .tt-video-card { flex: 1; min-height: 0; display: flex; flex-direction: column; gap: 8px; }
        .tt-video-remote { position: relative; flex: 1; min-height: 120px; border-radius: 12px; background: #0f172a; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .tt-video-local { position: relative; width: 120px; height: 84px; border-radius: 10px; background: #1f2937; overflow: hidden; border: 2px solid rgba(255,255,255,.7); }
        #ttRemoteVideo, #ttLocalVideo { width: 100%; height: 100%; object-fit: cover; }
        .tt-video-placeholder { color: #64748b; font-size: 12px; text-align: center; }
        .tt-status { font-size: 12px; padding: 3px 10px; border-radius: 999px; background: #f3f4f6; color: #6b7280; }
        .tt-status.ok { background: #d1fae5; color: #065f46; }
        .tt-status.warn { background: #fef3c7; color: #92400e; }
        .tt-status.err { background: #fee2e2; color: #991b1b; }
        .tt-toast { position: fixed; left: 50%; transform: translateX(-50%); bottom: 34px; z-index: 9999; background: #111827; color: #fff; font-size: 13px; padding: 9px 16px; border-radius: 999px; box-shadow: 0 8px 24px rgba(0,0,0,.25); max-width: 82vw; opacity: 0; transition: opacity .2s; pointer-events: none; }
        .tt-toast.show { opacity: 1; }
        .tt-color-dot { width: 20px; height: 20px; border-radius: 999px; border: 2px solid #fff; box-shadow: 0 0 0 1px #d0d5dd; cursor: pointer; }
        .tt-color-dot.active { box-shadow: 0 0 0 2px #1e3a8a; }
        .tt-sep { width: 1px; height: 22px; background: #e4e7ec; margin: 0 2px; }
    </style>

    <div class="tt-shell">
        <!-- 顶栏 -->
        <div class="tt-topbar">
            <a href="{{ url('/study/tools') }}" class="tt-btn" title="返回学习工具"><i class="fas fa-arrow-left"></i>学习工具</a>
            <span class="font-semibold text-gray-900"><i class="fas fa-chalkboard-teacher text-[#1e3a8a] mr-1"></i>远程辅导</span>
            <span class="tt-status" id="ttConnStatus">未连接</span>
            <span class="tt-status" id="ttPeerStatus" style="display:none;">无对方</span>
            <div class="flex-1"></div>
            <span class="text-xs text-gray-400" id="ttRoomLabel"></span>
        </div>

        <div class="tt-body">
            <!-- 左侧：主内容区 -->
            <div class="tt-content-panel">
                <div class="tt-toolbar" id="ttToolbar">
                    <button type="button" class="tt-btn" id="ttOpenFileBtn" title="打开本地文件（可同时打开多个）"><i class="fas fa-folder-open"></i>打开文件</button>
                    <input type="file" id="ttFileInput" multiple hidden
                           accept="image/*,.pdf,.html,.htm,.txt,.md,.docx,.xls,.xlsx,.pptx" />
                    <input type="text" id="ttUrlInput" class="tt-input" style="width: 280px; max-width: 40vw;" placeholder="粘贴课程/计划/网页 URL（课程支持 ?item= 定位章节，计划支持 ?plan=）" />
                    <button type="button" class="tt-btn" id="ttUrlOpenBtn"><i class="fas fa-link"></i>打开</button>
                    <span class="tt-sep"></span>
                    <button type="button" class="tt-btn" id="ttQuickCourses" title="打开我的课程"><i class="fas fa-book"></i>我的课程</button>
                    <button type="button" class="tt-btn" id="ttQuickPlans" title="打开学习计划"><i class="fas fa-calendar-alt"></i>学习计划</button>
                    <span class="tt-sep"></span>
                    <span class="text-xs text-gray-400">缩放</span>
                    <button type="button" class="tt-btn" id="ttZoomInBtn" title="放大（房主控制）"><i class="fas fa-search-plus"></i></button>
                    <button type="button" class="tt-btn" id="ttZoomOutBtn" title="缩小（房主控制）"><i class="fas fa-search-minus"></i></button>
                    <button type="button" class="tt-btn" id="ttZoomResetBtn" title="重置缩放"><i class="fas fa-expand"></i></button>
                    <span class="tt-sep"></span>
                    <button type="button" class="tt-btn" id="ttWbToggle" title="切换标注画笔模式（双方实时同步）"><i class="fas fa-pen"></i>标注模式</button>
                    <span id="ttWbTools" style="display:none; align-items:center; gap:6px;">
                        <span class="tt-color-dot active" data-color="#ff3b5c" style="background:#ff3b5c;"></span>
                        <span class="tt-color-dot" data-color="#000000" style="background:#000000;"></span>
                        <span class="tt-color-dot" data-color="#1e3a8a" style="background:#1e3a8a;"></span>
                        <span class="tt-color-dot" data-color="#16a34a" style="background:#16a34a;"></span>
                        <span class="tt-color-dot" data-color="#d97706" style="background:#d97706;"></span>
                        <input type="range" id="ttWbWidth" min="1" max="24" value="4" title="笔宽" style="width: 72px;" />
                        <button type="button" class="tt-btn" id="ttWbEraser" title="橡皮擦"><i class="fas fa-eraser"></i></button>
                        <button type="button" class="tt-btn" id="ttWbUndo" title="撤销"><i class="fas fa-undo"></i></button>
                        <button type="button" class="tt-btn danger" id="ttWbClear" title="清空批注"><i class="fas fa-trash-alt"></i></button>
                    </span>
                    <button type="button" class="tt-btn" id="ttOpenNewTab" style="display:none;" title="在新标签打开当前内容"><i class="fas fa-external-link-alt"></i>新标签打开</button>
                    <div class="flex-1"></div>
                    <span class="text-xs text-gray-400" id="ttContentName"></span>
                    <button type="button" class="tt-btn" id="ttBackToHost" style="display:none;" title="回到房主展示的画面"><i class="fas fa-arrow-right"></i>回到房主画面</button>
                    <span class="tt-status ok" id="ttMirrorHostBadge" style="display:none;"><i class="fas fa-camera mr-1"></i>镜像开·房主</span>
                    <span class="tt-status warn" id="ttMirrorBadge" style="display:none;"><i class="fas fa-eye mr-1"></i>房主控制展示</span>
                </div>
                <div class="tt-stage-wrap">
                    <div id="ttStage">
                        <div class="tt-empty" id="ttEmpty">
                            <i class="fas fa-chalkboard"></i>
                            <div class="tt-empty-desc">
                                通过 <b>上方的 URL 框</b> 打开学习内容（支持课程章节、计划详情、网页），<br>
                                或 <b>打开本地文件</b>（图片 / PDF / HTML / TXT / docx / xlsx / pptx）。<br>
                                打开 <b>标注模式</b> 后，双方可实时同步画笔批注。
                            </div>
                        </div>
                    </div>
                    <canvas id="ttWbCanvas"></canvas>
                    <div class="tt-stage-caption" id="ttCaption" style="display:none;"></div>
                </div>
            </div>

            <!-- 右侧：视频区 -->
            <div class="tt-video-panel">
                <div class="tt-room-card" id="ttRoomPanel">
                    <div class="tt-room-title"><i class="fas fa-door-open"></i>辅导房间</div>
                    <div class="flex gap-2">
                        <button type="button" class="tt-btn primary" id="ttCreateRoomBtn"><i class="fas fa-plus"></i>创建房间</button>
                        <input type="text" id="ttJoinCodeInput" class="tt-input" style="width: 110px; text-transform: uppercase;" placeholder="房号" maxlength="8" />
                        <button type="button" class="tt-btn" id="ttJoinRoomBtn">加入</button>
                    </div>
                    <div id="ttRoomInfo" style="display:none; flex-direction: column; gap: 6px;">
                        <div class="text-sm">房间号：<b id="ttRoomCodeText" class="text-[#1e3a8a]"></b>
                            <button type="button" class="tt-btn" style="padding:2px 8px;" id="ttCopyLinkBtn" title="复制邀请链接"><i class="fas fa-copy"></i>复制邀请链接</button>
                        </div>
                        <div class="text-xs text-gray-500 break-all" id="ttInviteUrl"></div>
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" class="tt-btn" id="ttReconnectBtn" title="重新协商连接"><i class="fas fa-sync"></i>重新连接</button>
                            <button type="button" class="tt-btn" id="ttQrBtn" title="二维码邀请"><i class="fas fa-qrcode"></i>二维码</button>
                            <button type="button" class="tt-btn" id="ttManualSdpToggle" title="手动 SDP 兜底"><i class="fas fa-clipboard"></i>手动SDP</button>
                        </div>
                        <div id="ttQrWrap" style="display:none; text-align:center; padding:8px 0;">
                            <div id="ttQrCode"></div>
                            <div class="text-xs text-gray-500 mt-2">扫码加入辅导房间</div>
                            <button type="button" class="tt-btn mt-2" id="ttQrClose">关闭</button>
                        </div>
                        <div id="ttManualSdp" style="display:none; flex-direction: column; gap: 6px;">
                            <div class="text-xs text-gray-500 leading-5">自动协商失败的兜底：A 点「生成 offer」复制发 B；B 粘贴到下方点「应用 offer 并生成 answer」复制回 A；A 粘贴点「应用 answer」。ICE 仍会自动经房间转发。</div>
                            <textarea id="ttSdpIn" class="tt-input" rows="3" placeholder="在此粘贴对方发来的 SDP（JSON 或 v=0 开头文本）"></textarea>
                            <div class="flex gap-2 flex-wrap">
                                <button type="button" class="tt-btn primary" id="ttGenOfferBtn"><i class="fas fa-file-export"></i>生成 offer</button>
                                <button type="button" class="tt-btn" id="ttApplyOfferBtn"><i class="fas fa-file-import"></i>应用 offer 并生成 answer</button>
                                <button type="button" class="tt-btn" id="ttApplyAnswerBtn"><i class="fas fa-check"></i>应用 answer</button>
                            </div>
                            <textarea id="ttSdpOut" class="tt-input" rows="3" readonly placeholder="生成的 SDP 将自动填入此处"></textarea>
                            <button type="button" class="tt-btn" id="ttCopySdpBtn"><i class="fas fa-copy"></i>复制上方 SDP</button>
                        </div>
                        <button type="button" class="tt-btn danger" id="ttLeaveRoomBtn"><i class="fas fa-sign-out-alt"></i>离开房间</button>
                    </div>
                </div>

                <div class="tt-video-card">
                    <div class="tt-room-title"><i class="fas fa-video"></i>视频辅导</div>
                    <div class="tt-video-remote">
                        <video id="ttRemoteVideo" autoplay playsinline style="display:none;"></video>
                        <div class="tt-video-placeholder" id="ttRemotePh"><i class="fas fa-user-friends text-2xl mb-2"></i><br>等待对方加入...</div>
                    </div>
                    <div class="flex items-center gap-2 justify-between">
                        <div class="tt-video-local">
                            <video id="ttLocalVideo" autoplay playsinline muted style="display:none;"></video>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button type="button" class="tt-btn" id="ttMicBtn" title="麦克风"><i class="fas fa-microphone"></i></button>
                            <button type="button" class="tt-btn" id="ttCamBtn" title="摄像头"><i class="fas fa-video"></i></button>
                        </div>
                        <button type="button" class="tt-btn danger" id="ttHangupBtn" title="挂断"><i class="fas fa-phone-slash"></i></button>
                    </div>
                </div>

                <div class="tt-room-card">
                    <div class="text-xs text-gray-500 leading-5">
                        <b>使用说明</b>：创建房间后把<b>邀请链接</b>发给对方；对方打开链接即自动加入并建立视频。左侧内容（课程/计划/文件）会同步给双方；打开标注模式后双方的画笔实时同步。
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tt-toast" id="ttToast"></div>

    <script src="{{ asset('js/qrcode.js') }}"></script>
    <script>
    (function() {
        'use strict';

        // ====================== 基础工具 ======================
        var $ = function(id) { return document.getElementById(id); };
        var ICE_SERVERS = {!! json_encode((array)config('study_tools.ice_servers', array(array('urls' => 'stun:stun.l.google.com:19302')))) !!};
        var TURN = {!! json_encode((array)config('study_tools.turn', array())) !!};
        if (TURN && TURN.urls) {
            var turnServer = { urls: TURN.urls };
            if (TURN.username) turnServer.username = TURN.username;
            if (TURN.credential) turnServer.credential = TURN.credential;
            ICE_SERVERS = ICE_SERVERS.concat([turnServer]);
        }
        var CURRENT_USER_ID = Number('{{ auth()->id() ?: 0 }}');
        var LOGICAL_W = 1280, LOGICAL_H = 800;

        function uuid() {
            return 'xxxxxx'.replace(/x/g, function() { return (Math.random() * 16 | 0).toString(16); });
        }

        var toastTimer = null;
        function toast(msg, type) {
            var el = $('ttToast');
            el.textContent = msg;
            el.className = 'tt-toast show' + (type === 'err' ? ' bg-red-600' : '');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(function() { el.classList.remove('show'); }, 2600);
        }

        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function api(base, opts) {
            if (!window.taskApiFetch) {
                return Promise.reject(new Error('网络组件未就绪，请稍候重试'));
            }
            return window.taskApiFetch(base, Object.assign({ method: 'GET' }, opts || {}))
                .then(function(resp) {
                    return resp.json().then(function(data) {
                        if (!resp.ok || !data) throw new Error('请求失败');
                        if (Number(data.code) !== 9999) {
                            var e = new Error(data.msg || '操作失败');
                            e.code = data.code;
                            throw e;
                        }
                        return data.result || {};
                    });
                });
        }

        function parseQuery() {
            var q = {};
            (window.location.search || '').replace(/[?&]([^=&#]+)=([^&#]*)/g, function(m, k, v) {
                q[decodeURIComponent(k)] = decodeURIComponent(v);
                return m;
            });
            return q;
        }

        // ====================== 状态 ======================
        var state = {
            peerId: 'p' + uuid(),
            room: null,
            role: 'host',            // 房间创建者=host
            joined: false,
            pollTimer: null,
            pollSince: 0,
            pingTimer: null,
            lastPeers: 0,
            rtc: {
                pc: null,
                dc: null,
                dcOpen: false,
                stream: null,
                wantVideo: true,
                wantAudio: true,
                connected: false,
                startedOnce: false,
                negotiating: false
            },
            content: { kind: null, src: null, name: null },
            wb: {
                on: false,
                color: '#ff3b5c',
                width: 4,
                eraser: false,
                drawing: false,
                curStroke: null,
                strokes: [],          // {id, tool, color, size, points:[[x,y]...], sender}
                seq: 0
            },
            file: {
                incoming: null,       // {meta, chunks:[], received}
                outgoing: null,       // {meta, offset, chunks}
                chunkSize: 16384
            },
            outgoingFiles: []         // DC 未开时暂存
        };

        // ====================== Viewer：URL 识别与渲染 ======================
        function classifyUrl(raw) {
            var u;
            try { u = new URL(raw, window.location.origin); } catch (e) { return null; }
            if (u.protocol !== 'http:' && u.protocol !== 'https:') return null;
            if (u.origin === window.location.origin) {
                var m = u.pathname.match(/^\/courses\/(\d+)/);
                if (m) return { kind: 'app-course', src: u.toString(), label: '课程 #' + m[1] };
                if (u.pathname === '/study' || u.pathname.indexOf('/study/') === 0) {
                    var p = (u.search.match(/[?&]plan=(\d+)/) || [])[1];
                    return { kind: 'app-study', src: u.toString(), label: p ? '学习计划 #' + p : '学习计划' };
                }
                return { kind: 'app-other', src: u.toString(), label: '应用页面' };
            }
            return { kind: 'external', src: u.toString(), label: '外部网页' };
        }

        function setCaption(text, showNewTab, newTabUrl) {
            var c = $('ttCaption');
            if (!text) { c.style.display = 'none'; c.innerHTML = ''; return; }
            c.innerHTML = '<i class="fas fa-file-alt"></i>' + escapeHtml(text);
            c.style.display = 'flex';
            var btn = $('ttOpenNewTab');
            if (showNewTab && newTabUrl) {
                btn.style.display = 'inline-flex';
                btn.onclick = function() { window.open(newTabUrl, '_blank'); };
            } else {
                btn.style.display = 'none';
            }
        }

        function clearStage() {
            var stage = $('ttStage');
            while (stage.firstChild) stage.removeChild(stage.firstChild);
            var ph = document.createElement('div');
            ph.className = 'tt-empty';
            ph.id = 'ttEmpty';
            ph.innerHTML = '<i class="fas fa-chalkboard"></i><div class="tt-empty-desc">主内容区为空，请打开内容</div>';
            stage.appendChild(ph);
        }

        function attachEl(el) {
            var stage = $('ttStage');
            while (stage.firstChild) stage.removeChild(stage.firstChild);
            stage.appendChild(el);
        }

        function renderUrl(kind, src, label, opts) {
            opts = opts || {};
            var prev = state.content;
            state.content = { kind: kind, src: src, name: label || src, seq: (state.content.seq || 0) + 1, zoom: 1 };
            $('ttContentName').textContent = '';
            var sameOriginApp = (kind === 'app-course' || kind === 'app-study' || kind === 'app-other');
            var frame = null;
            if (sameOriginApp && opts.keepFrame && prev && prev.kind === kind && prev.iframe && prev.iframe.contentWindow) {
                // 同源 iframe 复用仅导航（避免重建闪烁，并保持房主导航连续同步）
                frame = prev.iframe;
                try { frame.contentWindow.location.href = src; } catch (e) { frame.src = src; }
            } else {
                frame = document.createElement('iframe');
                frame.className = 'content-iframe';
                frame.src = src;
                frame.setAttribute('allow', 'camera; microphone; autoplay');
                attachEl(frame);
            }
            if (sameOriginApp) {
                // 同源页面：加载完成后注入交互监控（悬停/点击镜像）
                frame.addEventListener('load', function() { injectMirrorMonitor(frame); });
            }
            state.content.iframe = frame;
            startHostNavWatch(frame, sameOriginApp);
            startScrollWatch('iframe');
            if (sameOriginApp) {
                setCaption(label || src, false, null);
            } else {
                setCaption(label || src, true, src);
            }
            layoutCanvas();
        }

        // 房主镜像：滚动位置同步（按内容可滚高度的比例，适配左右不同视口）
        var scrollWatchTimer = null;
        var scrollWatchKey = 0;
        var lastSentRatio = -1;
        var scrollRelayPending = null;
        var scrollRelayTimer = null;
        function startScrollWatch(what) {
            if (scrollWatchTimer) { clearInterval(scrollWatchTimer); scrollWatchTimer = null; }
            scrollWatchKey = state.content.seq;
            lastSentRatio = -1;
            if (state.role !== 'host') return;
            scrollWatchTimer = setInterval(function() {
                if (!state.joined || state.role !== 'host' || !state.content || state.content.seq !== scrollWatchKey) {
                    if (scrollWatchTimer) { clearInterval(scrollWatchTimer); scrollWatchTimer = null; }
                    return;
                }
                var r = readScrollRatio();
                if (r === null) return;
                if (Math.abs(r - lastSentRatio) < 0.008) return;
                lastSentRatio = r;
                var msg = { seq: state.content.seq, r: r };
                if (state.rtc.dcOpen) {
                    try { state.rtc.dc.send(JSON.stringify({ t: 'scroll', d: msg })); return; } catch (e) {}
                }
                // 中继节流：300ms 内只发最新一条
                scrollRelayPending = msg;
                if (!scrollRelayTimer) {
                    scrollRelayTimer = setTimeout(function() {
                        scrollRelayTimer = null;
                        if (scrollRelayPending && state.room) {
                            var m = scrollRelayPending;
                            scrollRelayPending = null;
                            api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ type: 'scroll', peer_id: state.peerId, payload: m })
                            }).catch(function() {});
                        }
                    }, 300);
                }
            }, 180);
        }

        function readScrollRatio() {
            var c = state.content;
            if (!c) return null;
            if (c.iframe && c.iframe.contentWindow) {
                try {
                    var win = c.iframe.contentWindow;
                    var sc = win.document.scrollingElement || win.document.documentElement;
                    var max = sc.scrollHeight - win.innerHeight;
                    if (max <= 0) return 0;
                    return Math.round(win.scrollY / max * 10000) / 10000;
                } catch (e) { return null; } // 跨域外部页面无法读，不同步滚动
            }
            if (c.textEl) {
                var el = c.textEl;
                var mx = el.scrollHeight - el.clientHeight;
                if (mx <= 0) return 0;
                return Math.round(el.scrollTop / mx * 10000) / 10000;
            }
            return null;
        }

        function applyScroll(msg) {
            if (!msg || !state.content) return;
            if (Number(msg.seq) !== Number(state.content.seq)) return; // 内容已切换，忽略旧滚动
            var r = Number(msg.r);
            if (isNaN(r)) return;
            var c = state.content;
            if (c.iframe && c.iframe.contentWindow) {
                try {
                    var win = c.iframe.contentWindow;
                    var sc = win.document.scrollingElement || win.document.documentElement;
                    var max = sc.scrollHeight - win.innerHeight;
                    win.scrollTo(0, max > 0 ? Math.round(r * max) : 0);
                } catch (e) {}
            } else if (c.textEl) {
                var mx = c.textEl.scrollHeight - c.textEl.clientHeight;
                c.textEl.scrollTop = mx > 0 ? Math.round(r * mx) : 0;
            }
        }

        // ============ 同源页面内交互镜像（悬停/点击高亮） ============
        var MIRROR_MONITOR_JS = '(' + function() {
            if (window.__ttMirror) return; window.__ttMirror = true;
            var doc = document;
            function elPath(el) {
                var path = [];
                while (el && el !== doc.body && el.nodeType === 1) {
                    var parent = el.parentNode, idx = 0;
                    if (parent) {
                        var kids = parent.children;
                        for (var i = 0; i < kids.length; i++) { if (kids[i] === el) { idx = i; break; } }
                    }
                    path.unshift(idx);
                    el = parent;
                }
                return path;
            }
            function elFromPath(path) {
                var el = doc.body;
                for (var i = 0; i < path.length; i++) {
                    if (el && el.children && el.children[path[i]]) el = el.children[path[i]]; else return null;
                }
                return el;
            }
            var hl = null;
            function highlight(path, keep) {
                if (hl) { hl.remove(); hl = null; }
                if (!path) return;
                var el = elFromPath(path);
                if (!el) return;
                hl = doc.createElement('div');
                hl.style.cssText = 'position:absolute;pointer-events:none;z-index:99999;border:2px solid #ff3b5c;border-radius:4px;box-shadow:0 0 0 2px rgba(255,59,92,.25);transition:all .15s;';
                var r = el.getBoundingClientRect();
                hl.style.left = (r.left + (window.scrollX || window.pageXOffset)) + 'px';
                hl.style.top = (r.top + (window.scrollY || window.pageYOffset)) + 'px';
                hl.style.width = r.width + 'px';
                hl.style.height = r.height + 'px';
                hl.style.background = 'rgba(255,59,92,.08)';
                doc.body.appendChild(hl);
                if (!keep) {
                    setTimeout(function() { if (hl) { hl.remove(); hl = null; } }, 1600);
                }
            }
            function clean() {
                if (hl) { hl.remove(); hl = null; }
            }
            doc.addEventListener('mouseover', function(e) {
                var el = e.target || e.toElement;
                if (!el || el === doc.body || el === doc.documentElement) return;
                try { window.parent.postMessage({ tt: 'ttmirror', ev: 'hover', path: elPath(el) }, '*'); } catch (err) {}
            });
            doc.addEventListener('click', function(e) {
                var el = e.target;
                if (!el) return;
                try { window.parent.postMessage({ tt: 'ttmirror', ev: 'click', path: elPath(el) }, '*'); } catch (err) {}
            }, true);
            window.addEventListener('message', function(ev) {
                var m = ev.data;
                if (!m || m.tt !== 'tthost') return;
                if (m.cmd === 'hover') highlight(m.path, true);
                else if (m.cmd === 'click') highlight(m.path, false);
                else if (m.cmd === 'clear') clean();
            });
        } + ')();';

        function injectMirrorMonitor(frame) {
            try {
                var fdoc = frame.contentDocument;
                if (!fdoc || !fdoc.head) return;
                var sc = fdoc.createElement('script');
                sc.text = MIRROR_MONITOR_JS;
                fdoc.head.appendChild(sc);
            } catch (e) {}
        }

        // 子 iframe → 父页面事件
        window.addEventListener('message', function(ev) {
            var m = ev.data;
            if (!m || m.tt !== 'ttmirror') return;
            if (!state.content || !state.content.iframe) return;
            if (ev.source !== state.content.iframe.contentWindow) return;
            if (state.role !== 'host') return; // 仅房主的交互镜像给访客
            if (m.ev === 'hover') {
                sendSync('interact', { ev: 'hover', path: m.path });
            } else if (m.ev === 'click') {
                sendSync('interact', { ev: 'click', path: m.path });
            }
        });

        // 访客收到房主交互 → 应用到自己 iframe
        function applyInteract(msg) {
            if (!msg || !state.content || !state.content.iframe) return;
            var fw = state.content.iframe.contentWindow;
            if (!fw) return;
            try {
                fw.postMessage({ tt: 'tthost', cmd: msg.ev === 'click' ? 'click' : 'hover', path: msg.path }, '*');
            } catch (e) {}
        }

        // ============ 图片缩放同步（房主控制） ============
        function applyZoom(z) {
            state.content.zoom = z || 1;
            var img = state.content.imgEl;
            if (img) {
                img.style.transform = 'scale(' + state.content.zoom + ')';
                img.style.transformOrigin = 'center center';
            }
        }
        function zoomBy(delta) {
            if (!state.content) return;
            if (state.role !== 'host') { toast('缩放由房主控制', 'err'); return; }
            var z = Math.min(3, Math.max(0.5, (state.content.zoom || 1) * delta));
            applyZoom(z);
            sendSync('zoom', { z: z });
        }

        // 房主镜像：同源页面内部导航（课程章节跳转 / 计划切换等 URL 变化）自动同步给访客
        var navWatchTimer = null;
        var navWatchLastUrl = null;
        function startHostNavWatch(frame, sameOriginApp) {
            if (navWatchTimer) { clearInterval(navWatchTimer); navWatchTimer = null; }
            navWatchLastUrl = state.content.src;
            if (!sameOriginApp || state.role !== 'host') return;
            navWatchTimer = setInterval(function() {
                if (!state.content || !state.content.iframe || state.role !== 'host') {
                    if (navWatchTimer) { clearInterval(navWatchTimer); navWatchTimer = null; }
                    return;
                }
                var fr = state.content.iframe;
                var href = null;
                try { href = fr.contentWindow.location.href; } catch (e) { return; }
                if (href && href !== navWatchLastUrl) {
                    navWatchLastUrl = href;
                    sendSync('content_url', { url: href, nav: true });
                }
            }, 700);
        }

        function renderImageBlob(blob, name) {
            var url = URL.createObjectURL(blob);
            var img = document.createElement('img');
            img.className = 'content-img';
            img.src = url;
            img.alt = name || '';
            if (state.content) state.content.imgEl = img;
            if (state.content && state.content.zoom) img.style.transform = 'scale(' + state.content.zoom + ')';
            setCaption(name, false, null);
            attachEl(img);
        }

        function renderPdfBlob(blob, name) {
            var url = URL.createObjectURL(blob);
            var obj = document.createElement('object');
            obj.className = 'content-object';
            obj.type = 'application/pdf';
            obj.data = url;
            obj.innerHTML = '<div class="tt-empty"><i class="fas fa-file-pdf"></i><div>浏览器无法直接预览 PDF，请下载查看</div></div>';
            setCaption(name, true, url);
            attachEl(obj);
        }

        function renderText(text, name) {
            var pre = document.createElement('div');
            pre.className = 'content-text';
            pre.textContent = text;
            if (state.content) state.content = Object.assign(state.content, { seq: (state.content.seq || 0) + 1, textEl: pre });
            else state.content = { kind: 'text', seq: 1, textEl: pre, name: name };
            startScrollWatch('text');
            setCaption(name, false, null);
            attachEl(pre);
        }

        function renderHtmlSrcdoc(htmlText, name) {
            var frame = document.createElement('iframe');
            frame.className = 'content-iframe';
            frame.setAttribute('sandbox', 'allow-scripts allow-popups allow-forms');
            frame.srcdoc = htmlText;
            setCaption(name + '（隔离渲染）', false, null);
            attachEl(frame);
        }

        // Office 渲染：在 sandbox iframe 内加载 vendored 库渲染
        var OFFICE_LIBS = {
            docx: { libs: ['/vendor_local/jszip.min.js', '/vendor_local/docx-preview.min.js'], render: function(fwin, fdoc, buf) {
                return fwin.docx.renderAsync(buf, fdoc.body, null, { breakPages: true });
            } },
            xlsx: { libs: ['/vendor_local/xlsx.full.min.js'], render: function(fwin, fdoc, buf) {
                var wb = fwin.XLSX.read(new Uint8Array(buf), { type: 'array' });
                var first = wb.SheetNames.length ? wb.SheetNames[0] : null;
                if (!first) throw new Error('空表格');
                fdoc.body.innerHTML = fwin.XLSX.utils.sheet_to_html(wb.Sheets[first]);
                fdoc.body.firstChild && (fdoc.body.firstChild.style.width = '100%');
            } },
            pptx: { libs: ['/vendor_local/jszip.min.js', '/vendor_local/pptx-preview.umd.js'], render: function(fwin, fdoc, buf) {
                if (!fwin.pptxPreview || typeof fwin.pptxPreview.init !== 'function') throw new Error('pptx 渲染器未找到');
                var renderer = fwin.pptxPreview.init(fdoc.body, buf);
                var p = renderer.render();
                if (p && typeof p.then === 'function') return p;
                return Promise.resolve();
            } }
        };

        function renderOfficeBuffer(ext, arrayBuffer, name) {
            var spec = OFFICE_LIBS[ext];
            return new Promise(function(resolve, reject) {
                if (!spec) { reject(new Error('不支持的格式')); return; }
                var frame = document.createElement('iframe');
                frame.className = 'content-iframe';
                frame.setAttribute('sandbox', 'allow-scripts');
                var baseHtml = '<!doctype html><html><head><meta charset="utf-8">'
                    + '<style>body{margin:0;padding:18px;font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;color:#111827;box-sizing:border-box;}'
                    + 'table{border-collapse:collapse;width:100%}td,th{border:1px solid #d1d5db;padding:5px 8px;font-size:13px;}'
                    + 'img{max-width:100%}</style></head><body></body></html>';
                frame.srcdoc = baseHtml;
                var done = false;
                frame.addEventListener('load', function() {
                    if (done) return;
                    var fwin = frame.contentWindow;
                    var fdoc = frame.contentDocument;
                    var chain = Promise.resolve();
                    (spec.libs || []).forEach(function(libPath) {
                        chain = chain.then(function() {
                            return new Promise(function(okScript, failScript) {
                                var s = fdoc.createElement('script');
                                s.src = window.location.origin + libPath;
                                s.onload = function() { okScript(); };
                                s.onerror = function() { failScript(new Error('渲染库未加载：' + libPath)); };
                                fdoc.head.appendChild(s);
                            });
                        });
                    });
                    chain.then(function() {
                        try {
                            spec.render(fwin, fdoc, arrayBuffer).then(function() {
                                done = true; resolve();
                            }, function(err) {
                                done = true;
                                appendFrameError(fdoc, err);
                                resolve();
                            });
                        } catch (err) {
                            done = true;
                            appendFrameError(fdoc, err);
                            resolve();
                        }
                    }).catch(function(err) {
                        done = true;
                        appendFrameError(fdoc, err);
                        resolve();
                    });
                });
                setCaption((name || '文档') + '（隔离渲染）', false, null);
                attachEl(frame);
                layoutCanvas();
            });
        }

        function appendFrameError(fdoc, err) {
            if (!fdoc || !fdoc.body) return;
            fdoc.body.insertAdjacentHTML('beforeend',
                '<div style="color:#991b1b;font-size:13px;margin-top:14px">渲染失败：' + escapeHtml(String(err && err.message || err)) + '</div>');
        }

        var IMAGE_EXT = { png: true, jpg: true, jpeg: true, gif: true, webp: true, svg: true, bmp: true };

        function extOf(name) {
            var m = /\.([A-Za-z0-9]+)$/.exec(name || '');
            return m ? m[1].toLowerCase() : '';
        }

        function renderLocalFile(file) {
            var ext = extOf(file.name);
            var mine;
            if (IMAGE_EXT[ext]) mine = 'image';
            else if (ext === 'pdf') mine = 'pdf';
            else if (ext === 'html' || ext === 'htm') mine = 'html';
            else if (ext === 'txt' || ext === 'md') mine = 'text';
            else if (ext === 'docx') mine = 'office-docx';
            else if (ext === 'xls' || ext === 'xlsx') mine = 'office-xlsx';
            else if (ext === 'pptx') mine = 'office-pptx';
            else if (ext === 'doc' || ext === 'ppt') {
                toast('老版 ' + ext.toUpperCase() + ' 无法直接预览，请另存为 .docx / .pptx 后打开', 'err');
                return;
            } else {
                toast('暂不支持该文件类型：.' + ext, 'err');
                return;
            }
            state.content = { kind: 'file', src: null, name: file.name, seq: (state.content.seq || 0) + 1, zoom: 1, fileMeta: { name: file.name, type: file.type || '', ext: ext }, fileSmall: null, fileSmallType: '' };
            $('ttContentName').textContent = file.name;

            function doRender(buf) {
                // 房主缓存文件字节（≤8MB），供后加入/重试时拉取
                if (state.role === 'host' && buf.byteLength <= 8 * 1024 * 1024) {
                    state.content.fileBuffer = buf;
                } else {
                    state.content.fileBuffer = null;
                }
                if (mine === 'image' && buf.byteLength > 1536 * 1024 && state.role === 'host') {
                    // 大图：本地显示原图，同步用降采样小图（快且必达）
                    downscaleImage(file, buf, state.content);
                }
                if (mine === 'image') renderImageBlob(new Blob([buf], { type: file.type || 'application/octet-stream' }), file.name);
                else if (mine === 'pdf') renderPdfBlob(new Blob([buf], { type: 'application/pdf' }), file.name);
                else if (mine === 'html') renderHtmlSrcdoc(new TextDecoder().decode(buf), file.name);
                else if (mine === 'text') renderText(new TextDecoder().decode(buf), file.name);
                else if (mine === 'office-docx') renderOfficeBuffer('docx', buf, file.name);
                else if (mine === 'office-xlsx') renderOfficeBuffer('xlsx', buf, file.name);
                else if (mine === 'office-pptx') renderOfficeBuffer('pptx', buf, file.name);
                else toast('暂不支持该文件类型', 'err');
            }

            file.arrayBuffer().then(doRender).catch(function(err) {
                toast('读取文件失败：' + (err && err.message || err), 'err');
            });
        }

        // 大图降采样（供中继/DC 快速同步；原图仍本地展示）
        function downscaleImage(file, buf, content) {
            var blob = new Blob([buf], { type: file.type || 'image/jpeg' });
            var url = URL.createObjectURL(blob);
            var img = new Image();
            img.onload = function() {
                var w = img.naturalWidth, h = img.naturalHeight;
                var scale = Math.min(1, 1280 / Math.max(w, h));
                if (scale >= 1) { URL.revokeObjectURL(url); return; }
                var canvas = document.createElement('canvas');
                canvas.width = Math.round(w * scale);
                canvas.height = Math.round(h * scale);
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function(out) {
                    URL.revokeObjectURL(url);
                    if (!out || !content || !content.fileMeta) return;
                    out.arrayBuffer().then(function(small) {
                        if (small.byteLength < (content.fileBuffer ? content.fileBuffer.byteLength : 1)) {
                            content.fileSmall = small;
                            content.fileSmallType = out.type || 'image/jpeg';
                        }
                    }).catch(function() {});
                }, 'image/jpeg', 0.85);
            };
            img.onerror = function() { URL.revokeObjectURL(url); };
            img.src = url;
        }

        // 准备发送的文件：大图用降采样版（小、快、必达）
        function getSendFile(file) {
            if (state.content && state.content.fileSmall && state.content.fileMeta && state.content.fileMeta.name === file.name) {
                var small = state.content.fileSmall;
                return {
                    name: file.name,
                    size: small.byteLength,
                    type: state.content.fileSmallType || 'image/jpeg',
                    arrayBuffer: function() { return Promise.resolve(small); }
                };
            }
            return file;
        }

        // 把文件字节发给对端（访问端不缓存 blob，仅传输）
        function sendBufferToPeer(name, meta, buf) {
            if (!state.room) return;
            var fileLike = { name: name, size: buf.byteLength, type: (meta && meta.type) || '', arrayBuffer: function() { return Promise.resolve(buf); } };
            sendFileToPeer(fileLike);
        }

        // 房主重放当前内容（URL 或本地文件）给对端
        function replayContentToPeer() {
            if (state.role !== 'host' || !state.joined || !state.content) return;
            if (state.content.kind === 'file') {
                if (state.content.fileBuffer && state.content.fileMeta) {
                    sendBufferToPeer(state.content.fileMeta.name, state.content.fileMeta, state.content.fileBuffer);
                }
            } else if (state.content.src) {
                sendSync('content_url', { url: state.content.src });
            }
        }

        // ====================== 白板 ======================
        function canvasCtx() {
            return $('ttWbCanvas').getContext('2d');
        }

        function layoutCanvas() {
            var stage = $('ttStage');
            var canvas = $('ttWbCanvas');
            var rect = stage.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            var dpr = window.devicePixelRatio || 1;
            canvas.width = Math.round(rect.width * dpr);
            canvas.height = Math.round(rect.height * dpr);
            canvas.style.width = rect.width + 'px';
            canvas.style.height = rect.height + 'px';
            canvasCtx().setTransform(dpr, 0, 0, dpr, 0, 0);
            redrawWb();
        }

        function wbPoint(clientX, clientY) {
            var canvas = $('ttWbCanvas');
            var rect = canvas.getBoundingClientRect();
            var x = (clientX - rect.left) / rect.width * LOGICAL_W;
            var y = (clientY - rect.top) / rect.height * LOGICAL_H;
            return [Math.round(x), Math.round(y)];
        }

        function redrawWb() {
            var canvas = $('ttWbCanvas');
            var ctx = canvasCtx();
            var dpr = window.devicePixelRatio || 1;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            var rect = canvas.getBoundingClientRect();
            var scaleX = rect.width / LOGICAL_W;
            var scaleY = rect.height / LOGICAL_H;
            ctx.save();
            ctx.scale(scaleX, scaleY);
            state.wb.strokes.forEach(function(s) {
                ctx.beginPath();
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                if (s.tool === 'eraser') { ctx.globalCompositeOperation = 'destination-out'; ctx.strokeStyle = 'rgba(0,0,0,1)'; }
                else { ctx.globalCompositeOperation = 'source-over'; ctx.strokeStyle = s.color; ctx.globalAlpha = s.sender && s.sender !== state.peerId ? 0.75 : 1; }
                ctx.lineWidth = s.size;
                s.points.forEach(function(pt, i) {
                    if (i === 0) ctx.moveTo(pt[0], pt[1]);
                    else ctx.lineTo(pt[0], pt[1]);
                });
                ctx.stroke();
            });
            ctx.restore();
            ctx.globalAlpha = 1;
        }

        function setWb(on) {
            state.wb.on = !!on;
            $('ttWbToggle').classList.toggle('active', state.wb.on);
            $('ttWbCanvas').classList.toggle('on', state.wb.on);
            $('ttWbTools').style.display = state.wb.on ? 'inline-flex' : 'none';
            if (state.wb.on) layoutCanvas();
        }

        function strokeSeq() { return ++state.wb.seq; }

        function applyRemoteStroke(s) {
            if (!s || !s.id || !s.points) return;
            // 去掉重复
            for (var i = 0; i < state.wb.strokes.length; i++) {
                if (state.wb.strokes[i].id === s.id) return;
            }
            state.wb.strokes.push(s);
            redrawWb();
        }

        function applyRemoteUndo(strokeId) {
            var idx = -1;
            for (var i = state.wb.strokes.length - 1; i >= 0; i--) {
                if (state.wb.strokes[i].id === strokeId) { idx = i; break; }
            }
            if (idx >= 0) { state.wb.strokes.splice(idx, 1); redrawWb(); }
        }

        function applyRemoteClear() {
            state.wb.strokes = [];
            redrawWb();
        }

        // ====================== 同步：DC 优先，中继兜底 ======================
        function isContentOwner() {
            // 镜像主导：未进房单人使用无广播；进房后仅房主的内容操作广播
            return !state.joined || state.role === 'host';
        }

        function sendSync(type, payload) {
            var msg = { t: type, d: payload };
            if (state.rtc.dcOpen && state.rtc.dc && state.rtc.dc.readyState === 'open') {
                try { state.rtc.dc.send(JSON.stringify(msg)); return; } catch (e) {}
            }
            if (state.room) {
                api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: type, peer_id: state.peerId, payload: payload })
                }).catch(function() {});
            }
        }

        function handleSyncMessage(m) {
            // m: {t:type, d:payload}
            if (!m || !m.t) return;
            switch (m.t) {
                case 'wb':
                    applyRemoteStroke(m.d);
                    break;
                case 'wb_clear':
                    applyRemoteClear();
                    break;
                case 'wb_undo':
                    applyRemoteUndo(m.d && m.d.strokeId);
                    break;
                case 'content_url':
                    var cls = classifyUrl(m.d && m.d.url);
                    if (cls) renderUrl(cls.kind, cls.src, cls.label);
                    break;
                case 'scroll':
                    applyScroll(m.d);
                    break;
                case 'interact':
                    applyInteract(m.d);
                    break;
                case 'zoom':
                    if (state.content) applyZoom(Number((m.d && m.d.z) || 1));
                    break;
                case 'file_ack':
                    if (m.d && m.d.name) delete relayAckPending[m.d.name];
                    break;
                case 'file_meta':
                case 'file_chunk':
                case 'file_end':
                    handleIncomingFile(m);
                    break;
            }
        }

        // 文件传输：有数据通道走 DC（快、大文件）；否则经服务端中继（分批发送 + ack 重试，不依赖连接）
        var relayFileQueue = [];
        var relayFileTimer = null;
        var relayAckPending = {};   // name -> {retries}
        var relayLastTickAt = 0;

        function enqueueRelayFile(name, ext, type, buf) {
            var u8 = new Uint8Array(buf);
            relayFileQueue.push({ k: 'meta', name: name, size: u8.byteLength, type: type, ext: ext });
            var CHUNK = 30000;
            var seq = 0;
            for (var off = 0; off < u8.byteLength; off += CHUNK) {
                relayFileQueue.push({ k: 'chunk', seq: seq++, b64: btoaBin(u8.slice(off, Math.min(off + CHUNK, u8.byteLength))) });
            }
            relayFileQueue.push({ k: 'end', name: name });
            if (!relayAckPending[name]) relayAckPending[name] = { retries: 0 };
            flushRelayQueue();
        }

        function flushRelayQueue() {
            if (relayFileTimer) return;
            relayFileTimer = setInterval(function() {
                var sent = 0;
                while (relayFileQueue.length && sent < 60) {
                    var item = relayFileQueue.shift();
                    if (state.room) {
                        api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ type: 'file', peer_id: state.peerId, payload: item })
                        }).catch(function() {});
                    }
                    sent++;
                }
                if (!relayFileQueue.length) {
                    clearInterval(relayFileTimer);
                    relayFileTimer = null;
                }
            }, 2000);
        }

        // 中继文件送达确认与重试（每 8s 检查，最多重试 3 次；重试优先用降采样小图）
        setInterval(function() {
            if (state.role !== 'host' || !state.joined) return;
            Object.keys(relayAckPending).forEach(function(name) {
                var p = relayAckPending[name];
                // 该文件还有批次正在发送或刚发过，等待下一轮
                var busy = relayFileQueue.some(function(it) { return it.k === 'meta' && it.name === name; });
                if (busy) return;
                if (p.retries >= 3) {
                    delete relayAckPending[name];
                    toast('文件未能送达对方：' + name, 'err');
                    return;
                }
                var payloadBuf = null;
                if (state.content && state.content.fileMeta && state.content.fileMeta.name === name) {
                    // 优先重发小图，其次原图
                    if (state.content.fileSmall) payloadBuf = state.content.fileSmall;
                    else if (state.content.fileBuffer) payloadBuf = state.content.fileBuffer;
                }
                if (payloadBuf) {
                    p.retries++;
                    var fileLike = {
                        name: name,
                        size: payloadBuf.byteLength,
                        type: state.content.fileSmallType || state.content.fileMeta.type || '',
                        arrayBuffer: function() { return Promise.resolve(payloadBuf); }
                    };
                    sendFileToPeer(fileLike);
                } else {
                    delete relayAckPending[name];
                }
            });
        }, 8000);

        function sendFileToPeer(file) {
            // DC 就绪：直接走数据通道（大文件、低延迟）
            if (state.rtc.dcOpen && state.rtc.dc && state.rtc.dc.readyState === 'open') {
                sendFileOverDc(file);
                return;
            }
            if (!state.joined || !state.room) {
                toast('请先进入房间再发送文件', 'err');
                return;
            }
            if (file.size > 8 * 1024 * 1024) {
                state.outgoingFiles.push(file);
                toast('文件较大（>8MB），连接建立后自动发送：' + file.name, '');
                return;
            }
            file.arrayBuffer().then(function(buf) {
                enqueueRelayFile(file.name, extOf(file.name), file.type || '', buf);
                toast('正在同步文件：' + file.name, '');
            }).catch(function(err) { toast('发送失败：' + (err && err.message || err), 'err'); });
        }

        function sendRelay(type, payload) {
            api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, peer_id: state.peerId, payload: payload })
            }).catch(function() {});
        }

        function btoaBin(u8) {
            var bin = '';
            var STEP = 8000;
            for (var i = 0; i < u8.length; i += STEP) {
                bin += String.fromCharCode.apply(null, u8.subarray(i, Math.min(i + STEP, u8.length)));
            }
            return btoa(bin);
        }

        function atobBin(b64) {
            var bin = atob(b64);
            var u8 = new Uint8Array(bin.length);
            for (var i = 0; i < bin.length; i++) u8[i] = bin.charCodeAt(i);
            return u8;
        }

        function sendFileOverDc(file) {
            if (!state.rtc.dcOpen) {
                toast('对方尚未建立数据通道，文件仅在本地显示', 'err');
                return;
            }
            // 登记 ack：即使 DC 中途断开，ack 巡检也会走中继兜底重发（文件最终必达）
            relayAckPending[file.name] = { retries: 0 };
            var meta = { name: file.name, size: file.size, type: file.type || '', ext: extOf(file.name) };
            var p = file.arrayBuffer().then(function(buf) {
                var metaMsg = { t: 'file_meta', d: { name: meta.name, size: buf.byteLength, type: meta.type, ext: meta.ext } };
                state.rtc.dc.send(JSON.stringify(metaMsg));
                var offset = 0;
                while (offset < buf.byteLength) {
                    var end = Math.min(offset + state.file.chunkSize, buf.byteLength);
                    state.rtc.dc.send(buf.slice(offset, end));
                    offset = end;
                }
                state.rtc.dc.send(JSON.stringify({ t: 'file_end' }));
                toast('已发送文件：' + meta.name, '');
            });
            p.catch(function(err) { toast('发送失败：' + (err && err.message || err), 'err'); });
        }

        // 渲染收到的文件内容（DC 与中继共用）
        function renderReceivedFile(name, type, total) {
            state.content = { kind: 'file', src: null, name: name, seq: (state.content.seq || 0) + 1, zoom: 1 };
            var ext = extOf(name);
            if (IMAGE_EXT[ext]) {
                renderImageBlob(new Blob([total], { type: type || 'image/*' }), name);
            } else if (ext === 'pdf') {
                renderPdfBlob(new Blob([total], { type: 'application/pdf' }), name);
            } else if (ext === 'html' || ext === 'htm') {
                renderHtmlSrcdoc(new TextDecoder().decode(total), name);
            } else if (ext === 'txt' || ext === 'md') {
                renderText(new TextDecoder().decode(total), name);
            } else if (ext === 'docx') {
                renderOfficeBuffer('docx', total.buffer, name);
            } else if (ext === 'xls' || ext === 'xlsx') {
                renderOfficeBuffer('xlsx', total.buffer, name);
            } else if (ext === 'pptx') {
                renderOfficeBuffer('pptx', total.buffer, name);
            } else if (ext === 'doc' || ext === 'ppt') {
                toast('对方发送了 ' + ext.toUpperCase() + '，请另存为 .docx/.pptx', 'err');
            } else {
                toast('收到不支持的文件类型：.' + ext, 'err');
            }
            // 送达确认（房主收到后停止重试）
            if (state.joined) {
                sendSync('file_ack', { name: name });
            }
        }

        // 中继文件接收（消息类型 file）
        var relayIncoming = null;
        function handleIncomingRelayFile(payload) {
            var d = payload || {};
            if (d.k === 'meta') {
                relayIncoming = { meta: d, chunks: [], offset: 0 };
            } else if (d.k === 'chunk') {
                if (!relayIncoming) return;
                relayIncoming.chunks.push(atobBin(d.b64));
                relayIncoming.offset += relayIncoming.chunks[relayIncoming.chunks.length - 1].byteLength;
            } else if (d.k === 'end') {
                var inc = relayIncoming;
                relayIncoming = null;
                if (!inc) return;
                var total = new Uint8Array(inc.offset || 0);
                var off = 0;
                inc.chunks.forEach(function(c) { total.set(c, off); off += c.byteLength; });
                renderReceivedFile(inc.meta.name, inc.meta.type || '', total);
            }
        }

        function handleIncomingFile(m) {
            var d = m.d || {};
            if (m.t === 'file_meta') {
                state.file.incoming = { meta: d, chunks: [], received: 0 };
                toast('正在接收文件：' + d.name, '');
            } else if (m.t === 'file_chunk') {
                if (!state.file.incoming) return;
                state.file.incoming.chunks.push(d.buf);
                state.file.incoming.received += d.buf.byteLength || 0;
            } else if (m.t === 'file_end') {
                var inc = state.file.incoming;
                if (!inc) return;
                var total = new Uint8Array(inc.received || 0);
                var offset = 0;
                inc.chunks.forEach(function(c) {
                    total.set(new Uint8Array(c), offset);
                    offset += c.byteLength;
                });
                state.file.incoming = null;
                renderReceivedFile(inc.meta.name, inc.meta.type || '', total);
            }
        }

        // ====================== WebRTC ======================
        function rtcConfig() {
            return { iceServers: ICE_SERVERS };
        }

        function openMedia() {
            var constraints = {
                video: state.rtc.wantVideo,
                audio: state.rtc.wantAudio
            };
            if (!state.rtc.wantVideo && !state.rtc.wantAudio) return Promise.resolve(null);
            return navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
                state.rtc.stream = stream;
                var loc = $('ttLocalVideo');
                var ph = $('ttRemotePh');
                loc.srcObject = stream;
                loc.style.display = 'block';
                return stream;
            }).catch(function(err) {
                toast('无法获取麦克风/摄像头：' + (err && err.name || ''), 'err');
                return null;
            });
        }

        function ensurePc(withDataChannel) {
            if (state.rtc.pc) return state.rtc.pc;
            var pc = new RTCPeerConnection(rtcConfig());
            state.rtc.pc = pc;

            if (state.rtc.stream) {
                state.rtc.stream.getTracks().forEach(function(track) {
                    pc.addTrack(track, state.rtc.stream);
                });
            }

            pc.ontrack = function(ev) {
                var rv = $('ttRemoteVideo');
                rv.srcObject = ev.streams[0] || ev.streams.length ? ev.streams[0] : rv.srcObject;
                rv.style.display = 'block';
                $('ttRemotePh').style.display = 'none';
            };

            pc.onicecandidate = function(ev) {
                if (ev.candidate) {
                    queueIceCandidate({
                        candidate: ev.candidate.candidate,
                        sdpMid: ev.candidate.sdpMid,
                        sdpMLineIndex: ev.candidate.sdpMLineIndex
                    });
                }
            };

            pc.oniceconnectionstatechange = function() {
                var st = pc.iceConnectionState;
                var el = $('ttConnStatus');
                if (st === 'connected' || st === 'completed') {
                    state.rtc.connected = true;
                    el.textContent = '已连接';
                    el.className = 'tt-status ok';
                } else if (st === 'failed' || st === 'disconnected') {
                    state.rtc.connected = false;
                    el.textContent = '视频直连失败（白板走中继）';
                    el.className = 'tt-status warn';
                }
            };

            // 数据通道：由发起方（offerer）创建；应答方通过 ondatachannel 接收
            pc.ondatachannel = function(ev) { wireDataChannel(ev); };
            if (withDataChannel) {
                var dc = pc.createDataChannel('sync', { ordered: true });
                wireDataChannel({ channel: dc });
            }
            return pc;
        }

        function wireDataChannel(ev) {
            var dc = ev.channel;
            if (!dc) return;
            state.rtc.dc = dc;
            state.rtc.dc.binaryType = 'arraybuffer';
            dc.onopen = function() {
                state.rtc.dcOpen = true;
                // 数据通道就绪后补发暂存文件（仅房主发送）+ 房主重放当前内容（保证镜像）
                if (state.role === 'host') {
                    state.outgoingFiles.forEach(function(f) { sendFileOverDc(f); });
                    state.outgoingFiles = [];
                    replayContentToPeer();
                }
            };
            dc.onclose = function() { state.rtc.dcOpen = false; };
            dc.onmessage = function(ev2) {
                if (typeof ev2.data === 'string') {
                    try { handleSyncMessage(JSON.parse(ev2.data)); } catch (e) {}
                } else {
                    handleIncomingFile({ t: 'file_chunk', d: { buf: ev2.data } });
                }
            };
        }

        // ICE：远端描述尚未就绪前先排队，就绪后统一注入，避免加候选报错
        var pendingIce = [];
        // ICE 发送批量合并（300ms 攒一批，减少请求数避免触发限流）
        var outboxIce = [];
        var outboxIceTimer = null;
        function queueIceCandidate(candidate) {
            if (!state.room) return;
            outboxIce.push(candidate);
            if (outboxIceTimer) return;
            outboxIceTimer = setTimeout(function() {
                outboxIceTimer = null;
                var batch = outboxIce;
                outboxIce = [];
                if (!state.room || !batch.length) return;
                api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'ice', peer_id: state.peerId, payload: { candidates: batch } })
                }).catch(function() {});
            }, 300);
        }
        function pcAddIce(candidate) {
            if (!state.rtc.pc) return;
            if (!state.rtc.pc.remoteDescription || state.rtc.pc.signalingState !== 'stable') {
                pendingIce.push(candidate);
                return;
            }
            flushIce();
            state.rtc.pc.addIceCandidate(candidate).catch(function(err) {
                console.warn('addIceCandidate ignored:', err && err.message);
            });
        }
        function flushIce() {
            if (!state.rtc.pc || !state.rtc.pc.remoteDescription) return;
            var list = pendingIce;
            pendingIce = [];
            list.forEach(function(c) {
                state.rtc.pc.addIceCandidate(c).catch(function() {});
            });
        }

        function errName(err) {
            if (!err) return String(err);
            var name = err.name || '';
            var msg = err.message || err.toString && err.toString() || '';
            return (name ? name + ': ' : '') + msg;
        }

        // ============ 自动协商（信令轮询通道） ============
        function hostStartNegotiation() {
            if (state.rtc.negotiating || !state.joined || !state.room) return;
            state.rtc.negotiating = true;
            var pc = ensurePc(true);
            pc.createOffer().then(function(offer) {
                return pc.setLocalDescription(offer);
            }).then(function() {
                flushIce();
                return api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        type: 'offer',
                        peer_id: state.peerId,
                        payload: { sdp: { type: pc.localDescription.type, sdp: String(pc.localDescription.sdp || '') } }
                    })
                });
            }).then(function() {
                state.rtc.negotiating = false;
            }).catch(function(err) {
                state.rtc.negotiating = false;
                toast('创建连接失败：' + errName(err), 'err');
            });
        }

        // 标准化远端 SDP（兼容对象/字符串/异常嵌套），并剥离 UTF-8 BOM
        function normalizeSdp(x) {
            if (!x) return null;
            var sdpStr = null;
            var type = null;
            if (typeof x === 'string') {
                var trimmed = x.trim();
                if (trimmed.indexOf('{') === 0) {
                    try {
                        var parsed = JSON.parse(trimmed);
                        if (parsed && parsed.sdp) { x = parsed; }
                    } catch (e) {}
                }
            }
            if (typeof x === 'object') {
                if (typeof x.sdp === 'string') {
                    sdpStr = x.sdp;
                    type = x.type || 'offer';
                } else if (x.sdp && typeof x.sdp.sdp === 'string') {
                    sdpStr = x.sdp.sdp;
                    type = x.sdp.type || x.type || 'offer';
                }
            }
            if (sdpStr === null) return null;
            // 去掉 BOM 与首行前空白
            sdpStr = String(sdpStr).replace(/^\uFEFF/, '').trim();
            if (sdpStr.charAt(0) !== 'v') {
                // 尝试从 v= 处截断（防止前边粘连垃圾数据）
                var vIdx = sdpStr.indexOf('\nv=');
                if (vIdx >= 0) sdpStr = sdpStr.slice(vIdx + 1);
            }
            if (sdpStr.indexOf('v=0') !== 0) return null;
            // 保证以换行结尾（部分解析器要求末行有换行）
            if (!/\r?\n$/.test(sdpStr)) sdpStr += '\r\n';
            return { type: (type === 'answer' ? 'answer' : 'offer'), sdp: sdpStr };
        }

        function sdpDiag(sdp) {
            return 'len=' + (sdp && sdp.length) + ' nl=' + String(sdp || '').split('\n').length
                + ' head=' + JSON.stringify(String(sdp || '').slice(0, 24));
        }

        function guestHandleOffer(offer) {
            if (state.rtc.negotiating) return;
            state.rtc.negotiating = true;
            var pc = ensurePc(false);
            var sdp = normalizeSdp((offer && offer.sdp) ? offer.sdp : offer);
            if (!sdp) {
                state.rtc.negotiating = false;
                toast('offer 内容异常：' + sdpDiag((offer && offer.sdp) ? offer.sdp : offer), 'err');
                return;
            }
            pc.setRemoteDescription(sdp).then(function() {
                return pc.createAnswer();
            }).then(function(answer) {
                return pc.setLocalDescription(answer);
            }).then(function() {
                flushIce();
                return api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        type: 'answer',
                        peer_id: state.peerId,
                        payload: { sdp: { type: pc.localDescription.type, sdp: String(pc.localDescription.sdp || '') } }
                    })
                });
            }).then(function() {
                state.rtc.negotiating = false;
            }).catch(function(err) {
                state.rtc.negotiating = false;
                toast('应答失败：' + errName(err) + ' | ' + sdpDiag(sdp.sdp), 'err');
            });
        }

        function hostHandleAnswer(answer) {
            if (!state.rtc.pc) return;
            var sdp = normalizeSdp((answer && answer.sdp) ? answer.sdp : answer);
            if (!sdp) {
                toast('answer 内容异常：' + sdpDiag((answer && answer.sdp) ? answer.sdp : answer), 'err');
                return;
            }
            state.rtc.pc.setRemoteDescription(sdp).then(function() {
                flushIce();
            }).catch(function(err) {
                toast('设置远端描述失败：' + errName(err) + ' | ' + sdpDiag(sdp.sdp), 'err');
            });
        }

        function hangup() {
            resetRtc();
            if (state.rtc.stream) {
                state.rtc.stream.getTracks().forEach(function(t) { t.stop(); });
                state.rtc.stream = null;
            }
            state.rtc.connected = false;
            state.rtc.startedOnce = false;
            $('ttLocalVideo').style.display = 'none';
            var el = $('ttConnStatus');
            el.textContent = state.joined ? '房间内' : '未连接';
            el.className = 'tt-status';
        }

        // 复位 RTC（不关媒体流）
        function resetRtc() {
            if (state.rtc.dc) { try { state.rtc.dc.close(); } catch (e) {} }
            if (state.rtc.pc) { try { state.rtc.pc.close(); } catch (e) {} }
            state.rtc.pc = null;
            state.rtc.dc = null;
            state.rtc.dcOpen = false;
            state.rtc.negotiating = false;
            pendingIce = [];
            $('ttRemoteVideo').style.display = 'none';
            $('ttRemotePh').style.display = '';
        }

        function updateStatus(text, cls) {
            var el = $('ttConnStatus');
            el.textContent = text;
            el.className = 'tt-status' + (cls ? ' ' + cls : '');
        }

        // ====================== 房间 ======================
        function startPolling() {
            if (state.pollTimer) return;
            state.pollTimer = setInterval(pollOnce, 3000);
        }

        // 加入后连接自检：8s 未建立连接则请求重协商（前 3 次明确提示，之后 15s 低频持续重试不再刷屏）
        var reconnectAttempts = 0;
        var reconnectWarned = false;
        function scheduleConnectivityCheck() {
            if (!state.joined) return;
            reconnectAttempts++;
            if (reconnectAttempts > 3) {
                // 持续低频重试直到连接成功或离开
                setTimeout(function() {
                    if (!state.joined || state.rtc.connected) return;
                    if ((state.lastPeers || 0) >= 1) {
                        api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ type: 'renegotiate', peer_id: state.peerId, payload: {} })
                        }).catch(function() {});
                    }
                    scheduleConnectivityCheck();
                }, 15000);
                return;
            }
            setTimeout(function() {
                if (!state.joined || state.rtc.connected) return;
                if ((state.lastPeers || 0) < 1) {
                    scheduleConnectivityCheck();
                    return;
                }
                api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'renegotiate', peer_id: state.peerId, payload: {} })
                }).catch(function() {});
                if (state.role === 'host') {
                    state.rtc.startedOnce = false;
                    resetRtc();
                    hostStartNegotiation();
                }
                updateStatus('正在重新尝试连接...', 'warn');
                scheduleConnectivityCheck();
            }, 8000);
        }

        function stopPolling() {
            if (state.pollTimer) { clearInterval(state.pollTimer); state.pollTimer = null; }
            if (state.pingTimer) { clearInterval(state.pingTimer); state.pingTimer = null; }
        }

        function pollOnce() {
            if (!state.room) return;
            api('/api/v2/study/tools/rooms/' + state.room.code + '/messages?since=' + state.pollSince + '&peer_id=' + encodeURIComponent(state.peerId))
                .then(function(data) {
                    state.pollSince = Number(data.since || state.pollSince);
                    var peers = $('ttPeerStatus');
                    var cnt = Number(data.peers || 0);
                    state.lastPeers = cnt;
                    if (cnt > 0) {
                        peers.textContent = '在线 ' + cnt;
                        peers.className = 'tt-status ok';
                        peers.style.display = '';
                    } else {
                        peers.textContent = '无对方';
                        peers.className = 'tt-status';
                        peers.style.display = '';
                    }
                    (data.list || []).forEach(function(m) {
                        if (m.peer_id === state.peerId) return; // 自己的消息
                        handleRoomMessage(m);
                    });
                })
                .catch(function(err) {
                    if (err && err.code === 1001) {
                        toast('房间已关闭或过期，已离开', 'err');
                        leaveRoom(true);
                    }
                });
        }

        function handleRoomMessage(m) {
            var payload = m.payload;
            switch (m.type) {
                case 'join':
                    updateStatus('房间内，等待/已连接', '');
                    reconnectAttempts = 0;
                    // 对方（重新）加入时：房主重放当前内容（URL 或本地文件），保证房主镜像
                    if (state.role === 'host') {
                        replayContentToPeer();
                    }
                    // 房主在有对方后发起 offer（仅一次）
                    if (state.role === 'host') {
                        if (!state.rtc.startedOnce) { state.rtc.startedOnce = true; hostStartNegotiation(); }
                    }
                    break;
                case 'sync_req':
                    // 访客请求回到房主画面：房主重放当前内容
                    if (state.role === 'host') replayContentToPeer();
                    break;
                case 'offer':
                    if (state.role === 'student') guestHandleOffer(payload);
                    break;
                case 'answer':
                    if (state.role === 'host') hostHandleAnswer(payload);
                    break;
                case 'ice':
                    var cands = (payload && payload.candidates) ? payload.candidates
                        : ((payload && payload.candidate) ? [payload.candidate] : []);
                    cands.forEach(function(c) { pcAddIce(c); });
                    break;
                case 'file':
                    handleIncomingRelayFile(payload);
                    break;
                case 'renegotiate':
                    if (state.role === 'host') {
                        state.rtc.startedOnce = false;
                        resetRtc();
                        hostStartNegotiation();
                    } else {
                        resetRtc();
                    }
                    break;
                case 'leave':
                    toast('对方已离开');
                    updateStatus('对方已离开', 'warn');
                    break;
                case 'wb': applyRemoteStroke(payload); break;
                case 'wb_clear': applyRemoteClear(); break;
                case 'wb_undo': applyRemoteUndo(payload && payload.strokeId); break;
                case 'content_url':
                    var cls = classifyUrl(payload && payload.url);
                    if (cls) renderUrl(cls.kind, cls.src, cls.label, { keepFrame: true });
                    break;
                case 'scroll':
                    applyScroll(payload);
                    break;
                case 'file_ack':
                    if (payload && payload.name) delete relayAckPending[payload.name];
                    break;
                case 'interact':
                    applyInteract(payload);
                    break;
                case 'zoom':
                    if (state.content) applyZoom(Number((payload && payload.z) || 1));
                    break;
                case 'ping':
                    break;
            }
        }

        function enterRoom(code, role) {
            return api('/api/v2/study/tools/rooms/' + code + '/join', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ role: role })
            }).then(function(data) {
                state.room = { code: code, room_id: data.room_id, expires_at: data.expires_at };
                state.role = role;
                state.joined = true;
                state.rtc.startedOnce = false;
                reconnectAttempts = 0;
                $('ttRoomInfo').style.display = 'flex';
                $('ttRoomCodeText').textContent = code;
                $('ttInviteUrl').textContent = window.location.origin + '/study/tools/tutoring?room=' + code;
                $('ttRoomLabel').textContent = '房间 ' + code;
                $('ttCreateRoomBtn').disabled = true;
                $('ttJoinRoomBtn').disabled = true;
                $('ttJoinCodeInput').disabled = true;
                updateStatus('房间内', 'ok');
                $('ttMirrorHostBadge').style.display = (role === 'host') ? 'inline-flex' : 'none';
                $('ttMirrorBadge').style.display = (role === 'student') ? 'inline-flex' : 'none';
                $('ttBackToHost').style.display = (role === 'student') ? 'inline-flex' : 'none';
                toast(role === 'host' ? '房间已创建：' + code : '已加入房间：' + code, '');
                // 发送 join + 开启媒体 + 轮询
                return api('/api/v2/study/tools/rooms/' + code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'join', peer_id: state.peerId, payload: {} })
                }).then(function() {
                    state.pollSince = 0;
                    startPolling();
                    state.pingTimer = setInterval(function() {
                        if (!state.room) return;
                        api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ type: 'ping', peer_id: state.peerId, payload: {} })
                        }).catch(function() {});
                    }, 120000);
                    // 房主重进房间时若房内已有对方，直接发起协商（覆盖“宿主刷新页面后 guest 等待 offer 死锁”）
                    if (state.role === 'host' && Number(data.peer_count || 0) > 0) {
                        setTimeout(function() {
                            if (state.joined && state.role === 'host' && !state.rtc.startedOnce) {
                                state.rtc.startedOnce = true;
                                hostStartNegotiation();
                            }
                        }, 700);
                    }
                    // 双方：加入后未连接则自动请求重新协商（最多重试3次）
                    scheduleConnectivityCheck();
                    return openMedia();
                }).then(function() {
                    // 房主：若已加载过内容，进房后重放给对端（房主镜像）
                    if (state.role === 'host' && state.content && (state.content.src || state.content.fileBuffer)) {
                        replayContentToPeer();
                    }
                });
            });
        }

        function leaveRoom(silent) {
            if (state.room && !silent) {
                api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'leave', peer_id: state.peerId, payload: {} })
                }).catch(function() {});
            }
            stopPolling();
            hangup();
            if (navWatchTimer) { clearInterval(navWatchTimer); navWatchTimer = null; }
            if (scrollWatchTimer) { clearInterval(scrollWatchTimer); scrollWatchTimer = null; }
            state.room = null;
            state.joined = false;
            state.rtc.startedOnce = false;
            $('ttRoomInfo').style.display = 'none';
            $('ttRoomCodeText').textContent = '';
            $('ttInviteUrl').textContent = '';
            $('ttRoomLabel').textContent = '';
            $('ttCreateRoomBtn').disabled = false;
            $('ttJoinRoomBtn').disabled = false;
            $('ttJoinCodeInput').disabled = false;
            updateStatus(state.joined ? '房间内' : '未连接');
            $('ttPeerStatus').style.display = 'none';
            $('ttMirrorBadge').style.display = 'none';
            $('ttMirrorHostBadge').style.display = 'none';
            $('ttBackToHost').style.display = 'none';
        }

        // ====================== 事件绑定 ======================
        $('ttOpenFileBtn').addEventListener('click', function() { $('ttFileInput').click(); });
        $('ttFileInput').addEventListener('change', function() {
            var files = Array.prototype.slice.call(this.files || []);
            if (!files.length) return;
            files.forEach(function(f) { renderLocalFile(f); });
            // 仅房主同步文件给对端（房主镜像）；大图自动用降采样小图，DC 未开时走中继
            if (state.role === 'host') {
                files.forEach(function(f) { sendFileToPeer(getSendFile(f)); });
            } else if (state.joined) {
                toast('内容展示由房主控制，文件仅本地预览', '');
            }
            this.value = '';
        });

        $('ttUrlOpenBtn').addEventListener('click', openUrlInput);
        $('ttUrlInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') openUrlInput(); });

        function openUrlInput() {
            var raw = ($('ttUrlInput').value || '').trim();
            if (!raw) { toast('请输入 URL', 'err'); return; }
            if (!/^https?:\/\//i.test(raw)) raw = 'https://' + raw;
            var cls = classifyUrl(raw);
            if (!cls) { toast('仅支持 http/https 地址', 'err'); return; }
            renderUrl(cls.kind, cls.src, cls.label);
            if (isContentOwner()) {
                sendSync('content_url', { url: cls.src });
            } else {
                toast('内容展示由房主控制，此仅为你的本地预览', '');
            }
        }

        $('ttWbToggle').addEventListener('click', function() { setWb(!state.wb.on); });

        // 缩放（房主控制，双向同步）
        $('ttZoomInBtn').addEventListener('click', function() { zoomBy(1.25); });
        $('ttZoomOutBtn').addEventListener('click', function() { zoomBy(0.8); });
        $('ttZoomResetBtn').addEventListener('click', function() {
            if (state.role !== 'host') { toast('缩放由房主控制', 'err'); return; }
            applyZoom(1);
            sendSync('zoom', { z: 1 });
        });

        var colorDots = document.querySelectorAll('.tt-color-dot');
        colorDots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                colorDots.forEach(function(d) { d.classList.remove('active'); });
                dot.classList.add('active');
                state.wb.color = dot.getAttribute('data-color') || '#ff3b5c';
            });
        });

        $('ttWbWidth').addEventListener('input', function() { state.wb.width = Number(this.value || 4); });

        $('ttWbEraser').addEventListener('click', function() {
            state.wb.eraser = !state.wb.eraser;
            this.classList.toggle('active', state.wb.eraser);
        });

        $('ttWbUndo').addEventListener('click', function() {
            if (!state.wb.strokes.length) return;
            var last = state.wb.strokes[state.wb.strokes.length - 1];
            state.wb.strokes.pop();
            redrawWb();
            sendSync('wb_undo', { strokeId: last.id });
        });

        $('ttWbClear').addEventListener('click', function() {
            state.wb.strokes = [];
            redrawWb();
            sendSync('wb_clear', {});
        });

        var canvas = $('ttWbCanvas');
        canvas.addEventListener('pointerdown', function(e) {
            if (!state.wb.on) return;
            e.preventDefault();
            canvas.setPointerCapture(e.pointerId);
            var id = 's' + state.peerId.slice(0, 4) + '-' + strokeSeq();
            state.wb.curStroke = {
                id: id,
                tool: state.wb.eraser ? 'eraser' : 'pen',
                color: state.wb.color,
                size: state.wb.width,
                points: [wbPoint(e.clientX, e.clientY)],
                sender: state.peerId
            };
            state.wb.drawing = true;
            state.wb.strokes.push(state.wb.curStroke);
            redrawWb();
        });
        canvas.addEventListener('pointermove', function(e) {
            if (!state.wb.on || !state.wb.drawing || !state.wb.curStroke) return;
            e.preventDefault();
            state.wb.curStroke.points.push(wbPoint(e.clientX, e.clientY));
            redrawWb();
        });
        function endStroke(e) {
            if (!state.wb.drawing || !state.wb.curStroke) return;
            state.wb.drawing = false;
            var stroke = state.wb.curStroke;
            state.wb.curStroke = null;
            sendSync('wb', stroke);
        }
        canvas.addEventListener('pointerup', endStroke);
        canvas.addEventListener('pointercancel', endStroke);

        // 房间
        $('ttCreateRoomBtn').addEventListener('click', function() {
            api('/api/v2/study/tools/rooms', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ kind: 'tutoring' })
            }).then(function(data) {
                return enterRoom(data.code, 'host');
            }).then(function() {
                var link = window.location.origin + '/study/tools/tutoring?room=' + state.room.code;
                toast('房间已创建：' + state.room.code);
                copyText(link);
            }).catch(function(err) { toast(err && err.message || '创建失败', 'err'); });
        });

        $('ttJoinRoomBtn').addEventListener('click', function() {
            var code = ($('ttJoinCodeInput').value || '').trim().toUpperCase();
            if (!code) { toast('请输入房号', 'err'); $('ttJoinCodeInput').classList.add('error'); return; }
            enterRoom(code, 'student').catch(function(err) {
                toast(err && err.message || '加入失败', 'err');
                $('ttJoinCodeInput').classList.add('error');
                setTimeout(function() { $('ttJoinCodeInput').classList.remove('error'); }, 2500);
            });
        });

        $('ttCopyLinkBtn').addEventListener('click', function() {
            if (!state.room) return;
            copyText(window.location.origin + '/study/tools/tutoring?room=' + state.room.code);
        });

        // 访客：请求房主重放当前画面
        $('ttBackToHost').addEventListener('click', function() {
            if (!state.room) { toast('请先进入房间', 'err'); return; }
            sendSync('sync_req', {});
            toast('已请求房主重放画面', '');
        });

        // ===== 快捷打开 =====
        function openAppUrl(u) {
            var cls = classifyUrl(u);
            if (!cls) { toast('地址无效', 'err'); return; }
            renderUrl(cls.kind, cls.src, cls.label);
            if (isContentOwner()) {
                sendSync('content_url', { url: cls.src });
            } else {
                toast('内容展示由房主控制，此仅为你的本地预览', '');
            }
        }
        $('ttQuickCourses').addEventListener('click', function() { openAppUrl('/courses'); });
        $('ttQuickPlans').addEventListener('click', function() { openAppUrl('/study'); });

        // ===== 重新连接 =====
        $('ttReconnectBtn').addEventListener('click', function() {
            if (!state.room) { toast('请先进入房间', 'err'); return; }
            resetRtc();
            api('/api/v2/study/tools/rooms/' + state.room.code + '/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'renegotiate', peer_id: state.peerId, payload: {} })
            }).catch(function() {});
            if (state.role === 'host') hostStartNegotiation();
            toast('已请求重新连接', '');
        });

        // ===== 二维码分享 =====
        $('ttQrBtn').addEventListener('click', function() {
            if (!state.room) { toast('请先创建/加入房间', 'err'); return; }
            var wrap = $('ttQrWrap');
            var isOpen = wrap.style.display === 'block';
            $('ttManualSdp').style.display = 'none';
            if (isOpen) { wrap.style.display = 'none'; return; }
            wrap.style.display = 'block';
            var node = $('ttQrCode');
            node.innerHTML = '';
            if (window.QRCode) {
                new QRCode(node, {
                    text: window.location.origin + '/study/tools/tutoring?room=' + state.room.code,
                    width: 180,
                    height: 180,
                    correctLevel: QRCode.CorrectLevel.M
                });
            } else {
                node.innerHTML = '<div class="text-xs text-gray-500">二维码组件未加载</div>';
            }
        });
        $('ttQrClose').addEventListener('click', function() { $('ttQrWrap').style.display = 'none'; });

        // ===== 手动 SDP 兜底 =====
        $('ttManualSdpToggle').addEventListener('click', function() {
            var panel = $('ttManualSdp');
            var open = panel.style.display === 'flex';
            $('ttQrWrap').style.display = 'none';
            panel.style.display = open ? 'none' : 'flex';
        });

        function parseSdpInput() {
            var v = ($('ttSdpIn').value || '').trim();
            if (!v) { toast('请先粘贴 SDP', 'err'); return null; }
            try {
                var obj = JSON.parse(v);
                if (obj && obj.type && obj.sdp) return obj;
            } catch (e) {}
            if (v.indexOf('v=0') === 0) return { type: 'offer', sdp: v };
            toast('SDP 格式无法识别', 'err');
            return null;
        }

        $('ttGenOfferBtn').addEventListener('click', function() {
            var pc = ensurePc(true);
            pc.createOffer().then(function(o) { return pc.setLocalDescription(o); })
                .then(function() {
                    $('ttSdpOut').value = JSON.stringify(pc.localDescription);
                    toast('请复制上方 SDP 发给对方', '');
                })
                .catch(function(err) { toast('生成失败：' + errName(err), 'err'); });
        });

        $('ttApplyOfferBtn').addEventListener('click', function() {
            var sdp = parseSdpInput();
            if (!sdp) return;
            var pc = ensurePc(false);
            pc.setRemoteDescription(sdp)
                .then(function() { return pc.createAnswer(); })
                .then(function(a) { return pc.setLocalDescription(a); })
                .then(function() {
                    flushIce();
                    $('ttSdpOut').value = JSON.stringify(pc.localDescription);
                    toast('请将上方 answer 复制回对方', '');
                })
                .catch(function(err) { toast('应用 offer 失败：' + errName(err), 'err'); });
        });

        $('ttApplyAnswerBtn').addEventListener('click', function() {
            var sdp = parseSdpInput();
            if (!sdp || !state.rtc.pc) { toast('请先生成 offer 或等待协商', 'err'); return; }
            state.rtc.pc.setRemoteDescription(sdp)
                .then(function() { flushIce(); toast('已设置 answer，等待建立连接...', ''); })
                .catch(function(err) { toast('应用 answer 失败：' + errName(err), 'err'); });
        });

        $('ttCopySdpBtn').addEventListener('click', function() {
            copyText($('ttSdpOut').value || $('ttSdpIn').value);
        });

        $('ttLeaveRoomBtn').addEventListener('click', function() { leaveRoom(false); });
        $('ttHangupBtn').addEventListener('click', function() { hangup(); });

        function copyText(text) {
            function fallback() {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); toast('已复制邀请链接'); } catch (e) { toast(text); }
                document.body.removeChild(ta);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() { toast('已复制邀请链接'); }, fallback);
            } else fallback();
        }

        $('ttMicBtn').addEventListener('click', function() {
            if (!state.rtc.stream) return;
            var enabled = !state.rtc.stream.getAudioTracks().some(function(t) { return t.enabled; });
            state.rtc.stream.getAudioTracks().forEach(function(t) { t.enabled = enabled; });
            this.classList.toggle('active', enabled);
        });

        $('ttCamBtn').addEventListener('click', function() {
            if (!state.rtc.stream) return;
            var enabled = !state.rtc.stream.getVideoTracks().some(function(t) { return t.enabled; });
            state.rtc.stream.getVideoTracks().forEach(function(t) { t.enabled = enabled; });
            this.classList.toggle('active', enabled);
        });

        window.addEventListener('resize', function() { layoutCanvas(); });
        window.addEventListener('beforeunload', function() {
            if (state.room) {
                try {
                    navigator.sendBeacon && navigator.sendBeacon(
                        '/api/v2/study/tools/rooms/' + state.room.code + '/messages',
                        new Blob([JSON.stringify({ type: 'leave', peer_id: state.peerId, payload: {} })], { type: 'application/json' })
                    );
                } catch (e) {}
            }
            stopPolling();
            hangup();
        });

        // ====================== 初始化：?url= ?room= ======================
        layoutCanvas();
        var query = parseQuery();

        if (query.url) {
            var cls = classifyUrl(query.url);
            if (cls) renderUrl(cls.kind, cls.src, cls.label);
        }

        if (query.room) {
            var autoCode = String(query.room).toUpperCase();
            // 1) 无论自动加入结果如何，先回填房间号（用户可随时手动点「加入」重试）
            $('ttJoinCodeInput').value = autoCode;
            // 2) 布局底部的 taskApiFetch 晚于本页脚本定义，等待其就绪后再自动加入
            (function tryAutoJoin() {
                if (!window.taskApiFetch) { setTimeout(tryAutoJoin, 250); return; }
                enterRoom(autoCode, 'student').then(function() {
                    // 已加入：enterRoom 内会禁用输入框并展开房间面板
                }).catch(function(err) {
                    var msg = (err && err.message) || '房间无效';
                    toast('自动加入失败：' + msg, 'err');
                    updateStatus('自动加入失败，请点击「加入」重试', 'err');
                    // 保持输入框可编辑并引导手动加入
                    $('ttJoinCodeInput').value = autoCode;
                    $('ttJoinCodeInput').classList.remove('error');
                    $('ttCreateRoomBtn').classList.remove('primary');
                    $('ttJoinRoomBtn').classList.add('primary');
                });
            })();
        }
    })();
    </script>
@endsection