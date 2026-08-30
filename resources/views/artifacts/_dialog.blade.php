{{-- 制品弹窗组件：openArtifactDialog({relatedType, relatedId, artifactType}) --}}
<div class="artifact-dialog-mask" id="artifactDialogMask" style="display:none;">
    <div class="artifact-dialog">
        <div class="artifact-dialog-head">
            <div>
                <div class="artifact-dialog-title" id="artifactDialogTitle">制品</div>
                <div class="artifact-dialog-sub" id="artifactDialogSub"></div>
            </div>
            <button type="button" class="artifact-dialog-close" id="artifactDialogClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="artifact-dialog-body" id="artifactDialogBody">
            <div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在查询…</div>
        </div>
    </div>
</div>

{{-- 重新生成补充信息弹窗（已生成过制品时，二次生成先让用户补充信息再生成） --}}
<div class="artifact-gen-mask" id="artifactGenMask" style="display:none;">
    <div class="artifact-gen-box">
        <div class="artifact-gen-head">
            <div>
                <div class="artifact-gen-title" id="artifactGenTitle">重新生成</div>
                <div class="artifact-gen-sub" id="artifactGenSub"></div>
            </div>
            <button type="button" class="artifact-gen-close" id="artifactGenClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="artifact-gen-body">
            <label class="artifact-gen-label" for="artifactGenTextarea">补充信息（可选）</label>
            <textarea class="artifact-gen-textarea" id="artifactGenTextarea" rows="5" placeholder="补充你对生成结果的额外要求，例如：更侧重数据分析部分、换一种更简洁的风格、增加某类信息…"></textarea>
            <div class="artifact-gen-tip"><i class="fas fa-circle-info mr-1"></i>不填写则直接按当前内容重新生成；补充的信息会作为额外要求随原内容一起交给 AI，重新生成后旧版本会自动保留在下方历史列表中。</div>
            <div class="artifact-gen-error" id="artifactGenError" style="display:none;"></div>
        </div>
        <div class="artifact-gen-foot">
            <button type="button" class="artifact-gen-cancel" id="artifactGenCancel">取消</button>
            <button type="button" class="artifact-gen-confirm" id="artifactGenConfirm"><i class="fas fa-wand-magic-sparkles"></i> 确认重新生成</button>
        </div>
    </div>
</div>

<style>
    .artifact-dialog-mask { position: fixed; inset: 0; z-index: 120; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(15,23,42,.5); }
    .artifact-dialog { width: min(560px, 100%); max-height: min(80vh, 640px); display: flex; flex-direction: column; border-radius: 14px; background: #fff; box-shadow: 0 24px 80px rgba(15,23,42,.3); overflow: hidden; }
    .artifact-dialog-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #e5e7eb; }
    .artifact-dialog-title { font-size: 17px; font-weight: 750; color: #0f172a; }
    .artifact-dialog-sub { margin-top: 4px; font-size: 12px; color: #64748b; }
    .artifact-dialog-close { border: 0; background: transparent; color: #64748b; cursor: pointer; font-size: 17px; }
    .artifact-dialog-body { overflow-y: auto; padding: 20px; min-height: 160px; }
    .artifact-dialog-loading { display: flex; align-items: center; justify-content: center; gap: 8px; color: #64748b; padding: 40px 0; }
    .artifact-dialog-empty { text-align: center; color: #64748b; padding: 30px 0; }
    .artifact-dialog-empty .icon { font-size: 28px; margin-bottom: 10px; color: #cbd5e1; }
    .artifact-dialog-empty p { margin: 4px 0; }
    .artifact-dialog-generate-btn { display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; padding: 9px 18px; border: 0; border-radius: 9px; background: #0284c7; color: #fff; font-size: 14px; cursor: pointer; }
    .artifact-dialog-generate-btn:hover { background: #0369a1; }
    .artifact-dialog-generate-btn:disabled { opacity: .6; cursor: not-allowed; }
    .artifact-dialog-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 10px; }
    .artifact-dialog-item .info { min-width: 0; }
    .artifact-dialog-item .name { font-size: 14px; font-weight: 600; color: #0f172a; }
    .artifact-dialog-item .meta { margin-top: 2px; font-size: 11px; color: #94a3b8; }
    .artifact-dialog-item .ops { display: flex; gap: 6px; flex-shrink: 0; }
    .artifact-dialog-item .ops a, .artifact-dialog-item .ops button { padding: 5px 10px; border-radius: 7px; font-size: 12px; border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; text-decoration: none; }
    .artifact-dialog-item .ops a:hover, .artifact-dialog-item .ops button:hover { border-color: #38bdf8; color: #0284c7; }
    .artifact-dialog-error { padding: 12px 14px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; margin-bottom: 10px; }
    .dlg-mindmap { width: 100%; height: 420px; margin: 8px 0 4px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .dlg-mindmap-loading { display: flex; align-items: center; justify-content: center; height: 100%; color: #64748b; gap: 8px; }
    .dlg-mindmap jmnode { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; font-size: 13px; padding: 6px 10px; border-radius: 8px; }
    .dlg-mindmap jmnode.selected { background: #6366f1; color: #fff; border-color: #6366f1; }
    .dlg-mindmap jmexpander { color: #6366f1; }
    .dlg-tip { text-align: center; font-size: 11px; color: #94a3b8; margin: 6px 0 4px; }
    .dlg-fill-btn { padding: 5px 10px; border-radius: 7px; font-size: 12px; border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; text-decoration: none; }
    .dlg-fill-btn:hover { border-color: #38bdf8; color: #0284c7; }
    .dlg-html-content { margin-top: 10px; }
    .ai-key-points { font-size: 14px; line-height: 1.8; color: #1e293b; }
    .ai-key-points h2 { font-size: 16px; font-weight: 700; color: #0f172a; margin: 14px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    .ai-key-points h3 { font-size: 15px; font-weight: 600; color: #0f172a; margin: 12px 0 6px; }
    .ai-key-points ul, .ai-key-points ol { padding-left: 20px; margin: 6px 0; }
    .ai-key-points li { margin: 5px 0; }
    .ai-key-points strong { color: #0f172a; font-weight: 700; }
    .ai-key-points p { margin: 6px 0; }
    .ai-key-points blockquote { border-left: 3px solid #94a3b8; padding-left: 12px; color: #475569; margin: 8px 0; }
    .ai-key-points code { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-size: 13px; color: #dc2626; }

    /* 重新生成补充信息弹窗 */
    .artifact-gen-mask { position: fixed; inset: 0; z-index: 130; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(15,23,42,.45); }
    .artifact-gen-box { width: min(520px, 100%); display: flex; flex-direction: column; border-radius: 14px; background: #fff; box-shadow: 0 24px 80px rgba(15,23,42,.35); overflow: hidden; }
    .artifact-gen-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #e5e7eb; }
    .artifact-gen-title { font-size: 16px; font-weight: 750; color: #0f172a; }
    .artifact-gen-sub { margin-top: 4px; font-size: 12px; color: #64748b; }
    .artifact-gen-close { border: 0; background: transparent; color: #64748b; cursor: pointer; font-size: 17px; }
    .artifact-gen-body { padding: 16px 20px; }
    .artifact-gen-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .artifact-gen-textarea { width: 100%; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px; font-size: 13px; line-height: 1.7; color: #0f172a; resize: vertical; min-height: 90px; outline: none; }
    .artifact-gen-textarea:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,.15); }
    .artifact-gen-tip { margin-top: 8px; font-size: 12px; color: #94a3b8; line-height: 1.6; }
    .artifact-gen-error { margin-top: 10px; padding: 10px 12px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; }
    .artifact-gen-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 1px solid #e5e7eb; }
    .artifact-gen-cancel { padding: 8px 16px; border-radius: 9px; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }
    .artifact-gen-cancel:hover { border-color: #94a3b8; }
    .artifact-gen-confirm { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9px; border: 0; background: #0284c7; color: #fff; font-size: 13px; cursor: pointer; }
    .artifact-gen-confirm:hover { background: #0369a1; }
    .artifact-gen-confirm:disabled { opacity: .6; cursor: not-allowed; }

    /* 弹窗最下方：历史版本列表 */
    .artifact-dialog-history { margin-top: 16px; border-top: 1px dashed #e2e8f0; padding-top: 12px; }
    .artifact-dialog-history .history-head { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
    .artifact-dialog-history .history-empty { font-size: 12px; color: #94a3b8; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 10px; padding: 10px 12px; }
    .artifact-dialog-history .history-list { display: flex; flex-direction: column; gap: 8px; }
    .artifact-dialog-history .history-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
    .artifact-dialog-history .history-item.active { border-color: #38bdf8; background: #f0f9ff; }
    .artifact-dialog-history .history-info { min-width: 0; display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
    .artifact-dialog-history .history-version { font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; border-radius: 6px; padding: 2px 8px; }
    .artifact-dialog-history .history-status { font-size: 11px; border-radius: 6px; padding: 2px 8px; font-weight: 600; }
    .artifact-dialog-history .history-status.success { background: #dcfce7; color: #15803d; }
    .artifact-dialog-history .history-status.failed { background: #fee2e2; color: #b91c1c; }
    .artifact-dialog-history .history-meta { font-size: 11px; color: #94a3b8; }
    .artifact-dialog-history .history-prompt { width: 100%; font-size: 11px; color: #64748b; }

    /* 查看历史版本时的提示条 */
    .artifact-dialog-history-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 12px; border-radius: 10px; background: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; font-size: 12px; margin-bottom: 12px; }
</style>

<script>
    (function () {
        var TYPE_LABELS = {
            visual_reading: '可视化阅读',
            mind_map: '思维导图',
            key_points: 'AI 关键信息',
            ai_ppt: 'AIPPT',
            briefing_latest: '最新简报',
            briefing_followed: '关注简报'
        };

        var AIPPT_SCRIPT = '/js/ai-ppt.js';

        // 弹窗状态：当前制品(最新成功) + 历史版本
        var state = {
            relatedType: null,
            relatedId: null,
            artifactType: null,
            label: '',
            latest: null,
            versions: [],
            viewingVersion: null
        };

        function escapeHtml(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        // markdown 渲染：优先用全局 marked，回退为纯文本
        function renderMarkdown(md) {
            if (window.marked && typeof window.marked.parse === 'function') {
                return window.marked.parse(String(md));
            }
            if (window.marked) {
                return window.marked(String(md));
            }
            return '<pre>' + escapeHtml(md) + '</pre>';
        }

        function getAccessToken() {
            try {
                if (window.TaskApiClient && typeof window.TaskApiClient.getAccessToken === 'function') {
                    return window.TaskApiClient.getAccessToken() || '';
                }
            } catch (e) {}
            return '';
        }

        function requestJson(url, options) {
            options = options || {};
            var headers = Object.assign({}, options.headers || {});
            headers['Accept'] = 'application/json';
            headers['X-Requested-With'] = 'XMLHttpRequest';
            headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
            if (options.body) {
                headers['Content-Type'] = 'application/json';
            }
            // 与页面其他 API 一致：附加 access token（session 登录时由客户端注入）
            var accessToken = getAccessToken();
            if (accessToken && !headers['Authorization']) {
                headers['Authorization'] = 'Bearer ' + accessToken;
            }
            return fetch(url, Object.assign({ headers: headers, credentials: 'same-origin' }, options)).then(function (res) {
                return res.json();
            });
        }

        // ================= 打开 / 查询 / 渲染主体 =================

        function openArtifactDialog(opts) {
            state.relatedType = opts.relatedType;
            state.relatedId = opts.relatedId;
            state.artifactType = opts.artifactType;
            state.label = TYPE_LABELS[opts.artifactType] || opts.artifactType;
            state.latest = null;
            state.versions = [];
            state.viewingVersion = null;

            var mask = document.getElementById('artifactDialogMask');
            var body = document.getElementById('artifactDialogBody');
            document.getElementById('artifactDialogTitle').textContent = state.label;
            document.getElementById('artifactDialogSub').textContent = state.relatedType + ' #' + state.relatedId + ' · ' + 'AI 二次产出';
            body.innerHTML = '<div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在查询…</div>';
            mask.style.display = 'flex';

            requestJson('/api/v2/artifacts?related_type=' + encodeURIComponent(state.relatedType) + '&related_id=' + state.relatedId + '&artifact_type=' + encodeURIComponent(state.artifactType))
                .then(function (data) {
                    if (!data || Number(data.code) !== 9999) {
                        throw new Error(data && data.msg ? data.msg : '查询失败');
                    }
                    var artifacts = (data.result && data.result.artifacts) || [];
                    var mine = artifacts.filter(function (a) { return a.artifact_type === state.artifactType; });
                    var success = mine.filter(function (a) { return a.status === 'success'; });
                    var failed = mine.filter(function (a) { return a.status !== 'success'; });
                    state.latest = success.length > 0 ? success[0] : null;
                    renderBody(failed);
                })
                .catch(function (err) {
                    body.innerHTML = '<div class="artifact-dialog-empty"><div class="icon"><i class="fas fa-circle-exclamation"></i></div><p>查询失败：' + escapeHtml(err.message || err) + '</p></div>';
                });
        }

        function refreshDialog() {
            openArtifactDialog({
                relatedType: state.relatedType,
                relatedId: state.relatedId,
                artifactType: state.artifactType
            });
        }

        function renderBody(failedList) {
            var body = document.getElementById('artifactDialogBody');
            failedList = failedList || [];
            var failedHtml = '';
            failedList.forEach(function (a) {
                failedHtml += '<div class="artifact-dialog-error">' + state.label + '生成失败：' + escapeHtml(a.error_message || '未知原因') + '</div>';
            });

            if (!state.latest) {
                // 未生成 → 空状态 + 生成按钮（直接生成，不弹补充窗）
                var html = '';
                html += '<div class="artifact-dialog-empty">';
                html += '<div class="icon"><i class="fas fa-wand-magic-sparkles"></i></div>';
                html += '<p>当前还没有「' + state.label + '」制品</p>';
                html += '<p style="font-size:12px;color:#94a3b8;">点击下方按钮，让 AI 基于原文生成一个</p>';
                html += '</div>';
                html += failedHtml;
                html += '<div style="text-align:center;">';
                html += '<button type="button" class="artifact-dialog-generate-btn" data-gen="1"><i class="fas fa-wand-magic-sparkles"></i> 生成' + state.label + '</button>';
                html += '</div>';
                body.innerHTML = html;

                var genBtn = body.querySelector('[data-gen="1"]');
                if (genBtn) {
                    genBtn.addEventListener('click', function () {
                        genBtn.disabled = true;
                        genBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 生成中，请稍候…';
                        body.insertAdjacentHTML('beforeend', '<div class="artifact-dialog-loading" id="artifactDialogGenerating"><i class="fas fa-spinner fa-spin"></i> AI 正在生成' + state.label + '，可能需要 1-2 分钟…</div>');
                        generateArtifact('', function () {
                            refreshDialog();
                        }, function (msg) {
                            var loading = document.getElementById('artifactDialogGenerating');
                            if (loading) { loading.remove(); }
                            genBtn.disabled = false;
                            genBtn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> 生成' + state.label;
                            body.insertAdjacentHTML('beforeend', '<div class="artifact-dialog-error">生成失败：' + escapeHtml(msg) + '</div>');
                        });
                    });
                }
                return;
            }

            // 已生成 → 上（重新生成行，不变）＋ 中（当前内容展示，不变）＋ 下（历史版本列表，新增）
            var latest = state.latest;
            html = '';
            html += '<div class="artifact-dialog-item" style="background:#f0f9ff;border-color:#bae6fd;">';
            html += '<div class="info"><div class="name">已生成「' + state.label + '」</div>';
            html += '<div class="meta">' + (latest.model_name ? escapeHtml(latest.model_name) : '') + (latest.generated_at ? ' · ' + escapeHtml(latest.generated_at) : '') + '</div></div>';
            html += '<div class="ops"><button type="button" data-gen="1"><i class="fas fa-rotate"></i>重新生成</button>';
            html += '<a href="/artifacts/' + latest.id + '" target="_blank" class="dlg-fill-btn"><i class="fas fa-expand mr-1"></i>独立页</a></div>';
            html += '</div>';

            html += failedHtml;
            html += '<div id="artifactContentArea"><div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在加载…</div></div>';
            html += '<div id="artifactHistoryArea"></div>';

            body.innerHTML = html;

            var genBtn = body.querySelector('[data-gen="1"]');
            if (genBtn) {
                genBtn.addEventListener('click', function () {
                    openRegenerateModal();
                });
            }

            renderCurrentContent();
            loadVersions();
        }

        // ================= 中间内容区：当前版本 / 历史版本 =================

        function renderCurrentContent() {
            var area = document.getElementById('artifactContentArea');
            if (!area || !state.latest) return;
            state.viewingVersion = null;
            var a = state.latest;

            if (state.artifactType === 'mind_map') {
                var nodeTree = parseNodeTreeContent(a.content);
                if (nodeTree) {
                    area.innerHTML = '<div class="dlg-mindmap" id="artifactDialogMindmap"><div class="dlg-mindmap-loading"><i class="fas fa-spinner fa-spin"></i> 加载思维导图…</div></div>'
                        + '<div class="dlg-tip">满屏看不全？点上方「独立页」查看完整导图。</div>';
                    renderMindmapInDialog(nodeTree, a.name);
                } else {
                    area.innerHTML = '<div class="artifact-dialog-error">思维导图数据无法解析</div>';
                }
                return;
            }

            // 关键信息 / 可视化阅读：调 show 接口拉完整内容内嵌展示
            area.innerHTML = '<div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在加载内容…</div>';
            requestJson('/api/v2/artifacts/' + a.id)
                .then(function (data) {
                    var el = document.getElementById('artifactContentArea');
                    if (!el) return;
                    var full = data && data.result && data.result.artifact;
                    if (full && full.content) {
                        if (state.artifactType === 'key_points') {
                            el.innerHTML = '<div class="ai-key-points max-w-none rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">' + renderMarkdown(full.content) + '</div>';
                        } else if (state.artifactType === 'ai_ppt') {
                            el.innerHTML = '';
                            renderAiPptInDialog(el, full.content);
                        } else {
                            el.innerHTML = '<div class="ai-render-content max-w-none rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">' + full.content + '</div>'
                                + '<div class="dlg-tip">如弹窗内展示不完整，可点上方「独立页」查看全屏版本。</div>';
                        }
                    } else {
                        el.innerHTML = '<div class="artifact-dialog-error">加载内容失败</div>';
                    }
                })
                .catch(function () {
                    var el = document.getElementById('artifactContentArea');
                    if (el) el.innerHTML = '<div class="artifact-dialog-error">加载内容失败</div>';
                });
        }

        function renderVersionIntoArea(v) {
            var area = document.getElementById('artifactContentArea');
            if (!area) return;
            var bar = '<div class="artifact-dialog-history-bar"><span><i class="fas fa-clock-rotate-left mr-1"></i>正在查看 历史版本 v' + v.version + (v.generated_at ? '（' + escapeHtml(v.generated_at) + '）' : '') + '</span>'
                + '<button type="button" class="dlg-fill-btn" id="backToCurrentVersion"><i class="fas fa-rotate-left mr-1"></i>返回当前版本</button></div>';

            if (state.artifactType === 'mind_map') {
                var nodeTree = parseNodeTreeContent(v.content);
                if (nodeTree) {
                    area.innerHTML = bar + '<div class="dlg-mindmap" id="artifactDialogMindmap"><div class="dlg-mindmap-loading"><i class="fas fa-spinner fa-spin"></i> 加载思维导图…</div></div>';
                    renderMindmapInDialog(nodeTree, (state.latest && state.latest.name) || '思维导图');
                } else {
                    area.innerHTML = bar + '<div class="artifact-dialog-error">该历史版本思维导图数据无法解析</div>';
                }
            } else if (state.artifactType === 'key_points') {
                area.innerHTML = bar + '<div class="ai-key-points max-w-none rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">' + renderMarkdown(v.content) + '</div>';
            } else if (state.artifactType === 'ai_ppt') {
                area.innerHTML = bar;
                renderAiPptInDialog(area, v.content);
            } else {
                area.innerHTML = bar + '<div class="ai-render-content max-w-none rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">' + (v.content ? v.content : '<p class="text-slate-400" style="color:#94a3b8;">该历史版本没有内容</p>') + '</div>';
            }

            var backBtn = document.getElementById('backToCurrentVersion');
            if (backBtn) {
                backBtn.addEventListener('click', function () {
                    renderCurrentContent();
                    highlightHistory(null);
                });
            }
            highlightHistory(v.version);
        }

        // AIPPT 内容渲染：解析 JSON 幻灯片并（按需加载 ai-ppt.js 后）渲染进容器
        function renderAiPptInDialog(container, content) {
            var parsed = null;
            try {
                parsed = JSON.parse(content);
            } catch (e) { parsed = null; }
            if (!parsed) {
                container.innerHTML = '<div class="artifact-dialog-error">AIPPT 数据解析失败</div>';
                return;
            }
            var mount = function () {
                var box = document.createElement('div');
                box.className = 'aippt-mount';
                container.appendChild(box);
                window.renderAiPpt(box, parsed, {});
            };
            if (typeof window.renderAiPpt === 'function') {
                mount();
                return;
            }
            loadScript(AIPPT_SCRIPT, function () {
                if (typeof window.renderAiPpt === 'function') {
                    mount();
                } else {
                    container.innerHTML = '<div class="artifact-dialog-error">AIPPT 渲染器加载失败</div>';
                }
            });
        }

        // 解析制品 content 里的 node_tree data
        function parseNodeTreeContent(content) {
            if (content == null || content === '') return null;
            try {
                var decoded = typeof content === 'string' ? JSON.parse(content) : content;
                if (!decoded) return null;
                return decoded.data || decoded;
            } catch (e) { return null; }
        }

        // ================= 历史版本列表（弹窗最下方） =================

        function loadVersions() {
            var area = document.getElementById('artifactHistoryArea');
            if (!area || !state.latest) return;
            requestJson('/api/v2/artifacts/' + state.latest.id + '/versions')
                .then(function (data) {
                    if (!data || Number(data.code) !== 9999) {
                        throw new Error(data && data.msg ? data.msg : '查询失败');
                    }
                    state.versions = (data.result && data.result.versions) || [];
                    renderVersionList();
                })
                .catch(function () {
                    // 历史列表加载失败不阻塞主内容
                    var a2 = document.getElementById('artifactHistoryArea');
                    if (a2) a2.innerHTML = '';
                });
        }

        function renderVersionList() {
            var area = document.getElementById('artifactHistoryArea');
            if (!area) return;
            var html = '<div class="artifact-dialog-history">';
            html += '<div class="history-head"><i class="fas fa-clock-rotate-left"></i>历史版本（' + state.versions.length + '）</div>';
            if (state.versions.length === 0) {
                html += '<div class="history-empty">暂无历史记录：重新生成后，旧版本会自动保留在下方列表中，可随时查看。</div>';
            } else {
                html += '<div class="history-list">' + buildVersionItems() + '</div>';
            }
            html += '</div>';
            area.innerHTML = html;
            bindVersionButtons();
        }

        function buildVersionItems() {
            var html = '';
            state.versions.forEach(function (v) {
                var active = state.viewingVersion === v.version;
                html += '<div class="history-item' + (active ? ' active' : '') + '">';
                html += '<div class="history-info">';
                html += '<span class="history-version">v' + v.version + '</span>';
                html += '<span class="history-status ' + escapeHtml(v.status) + '">' + (v.status === 'success' ? '成功' : '失败') + '</span>';
                html += '<span class="history-meta">' + escapeHtml(v.model_name || '') + (v.generated_at ? ' · ' + escapeHtml(v.generated_at) : '') + '</span>';
                if (v.custom_prompt) {
                    html += '<div class="history-prompt">补充要求：「' + escapeHtml(v.custom_prompt) + '」</div>';
                }
                html += '</div>';
                html += '<div class="history-ops"><button type="button" class="dlg-fill-btn" data-view-version="' + v.version + '"><i class="fas fa-eye mr-1"></i>查看</button></div>';
                html += '</div>';
            });
            return html;
        }

        function bindVersionButtons() {
            var area = document.getElementById('artifactHistoryArea');
            if (!area) return;
            Array.prototype.forEach.call(area.querySelectorAll('[data-view-version]'), function (btn) {
                btn.addEventListener('click', function () {
                    viewVersion(Number(btn.getAttribute('data-view-version')));
                });
            });
        }

        function highlightHistory(version) {
            var area = document.getElementById('artifactHistoryArea');
            if (!area) return;
            var items = area.querySelectorAll('.history-item');
            for (var i = 0; i < items.length; i++) {
                var b = items[i].querySelector('[data-view-version]');
                var n = b ? Number(b.getAttribute('data-view-version')) : null;
                if (version !== null && n === version) items[i].classList.add('active');
                else items[i].classList.remove('active');
            }
        }

        function viewVersion(version) {
            var area = document.getElementById('artifactContentArea');
            if (!area || !state.latest) return;
            area.innerHTML = '<div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在加载历史版本…</div>';
            requestJson('/api/v2/artifacts/' + state.latest.id + '/versions/' + version)
                .then(function (data) {
                    if (!data || Number(data.code) !== 9999) {
                        throw new Error(data && data.msg ? data.msg : '加载失败');
                    }
                    var v = data.result && data.result.version;
                    if (!v) throw new Error('版本不存在');
                    renderVersionIntoArea(v);
                })
                .catch(function (err) {
                    var el = document.getElementById('artifactContentArea');
                    if (el) el.innerHTML = '<div class="artifact-dialog-error">加载历史版本失败：' + escapeHtml(err.message || err) + '</div>';
                    renderCurrentContent();
                    highlightHistory(null);
                });
        }

        // ================= 重新生成：补充信息弹窗 =================

        function openRegenerateModal() {
            var mask = document.getElementById('artifactGenMask');
            if (!mask) return;
            var title = document.getElementById('artifactGenTitle');
            var sub = document.getElementById('artifactGenSub');
            var err = document.getElementById('artifactGenError');
            var textarea = document.getElementById('artifactGenTextarea');
            if (title) { title.textContent = '重新生成' + state.label; }
            if (sub) { sub.textContent = '已生成过「' + state.label + '」，重新生成后旧版本会自动保留在下方历史列表中'; }
            if (err) { err.style.display = 'none'; err.textContent = ''; }
            if (textarea) { textarea.value = ''; }
            mask.style.display = 'flex';
            if (textarea) { setTimeout(function () { textarea.focus(); }, 40); }
        }

        function closeRegenerateModal() {
            var mask = document.getElementById('artifactGenMask');
            if (mask) { mask.style.display = 'none'; }
            var err = document.getElementById('artifactGenError');
            if (err) { err.style.display = 'none'; }
            var confirmBtn = document.getElementById('artifactGenConfirm');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> 确认重新生成';
            }
        }

        function confirmRegenerate() {
            var confirmBtn = document.getElementById('artifactGenConfirm');
            var err = document.getElementById('artifactGenError');
            var textarea = document.getElementById('artifactGenTextarea');
            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 正在重新生成，可能需 1-2 分钟…';
            }
            if (err) { err.style.display = 'none'; err.textContent = ''; }
            var customPrompt = textarea ? textarea.value : '';

            generateArtifact(customPrompt, function () {
                closeRegenerateModal();
                refreshDialog();
            }, function (msg) {
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> 确认重新生成';
                }
                if (err) {
                    err.style.display = 'block';
                    err.textContent = '重新生成失败：' + msg;
                }
            });
        }

        // ================= 生成请求 =================

        function generateArtifact(customPrompt, onSuccess, onError) {
            requestJson('/api/v2/artifacts/generate', {
                method: 'POST',
                body: JSON.stringify({
                    related_type: state.relatedType,
                    related_id: state.relatedId,
                    artifact_type: state.artifactType,
                    force: 1,
                    custom_prompt: customPrompt || ''
                })
            }).then(function (data) {
                if (!data || Number(data.code) !== 9999) {
                    throw new Error(data && data.msg ? data.msg : '生成失败');
                }
                var artifact = data.result && data.result.artifact;
                if (!artifact || artifact.status !== 'success') {
                    throw new Error((artifact && artifact.error_message) || 'AI 生成失败，请稍后重试');
                }
                if (onSuccess) { onSuccess(artifact); }
            }).catch(function (err) {
                if (onError) { onError(err.message || err); }
            });
        }

        // ================= 思维导图内嵌渲染 =================

        function renderMindmapInDialog(nodeTree, name) {
            // 确保 jsmind.css 已加载（缺 css 会显示空白/乱排）
            loadCss('/css/jsmind.css');
            var containerId = 'artifactDialogMindmap';
            var container = document.getElementById(containerId);
            if (!container) return;
            if (typeof jsMind === 'undefined') {
                // 动态加载 jsmind.js（依赖 jquery）
                loadScript('/js/jsmind.js', function () {
                    var c = document.getElementById(containerId);
                    if (c && typeof jsMind !== 'undefined') doRenderMindmap(c, nodeTree, name);
                    else if (c) c.innerHTML = '<div class="artifact-dialog-error">jsMind 加载失败</div>';
                });
                return;
            }
            doRenderMindmap(container, nodeTree, name);
        }

        function loadCss(href) {
            var links = document.querySelectorAll('link[rel="stylesheet"]');
            for (var i = 0; i < links.length; i++) {
                if (links[i].href && links[i].href.indexOf(basename(href)) !== -1) return;
            }
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = href;
            document.head.appendChild(l);
        }

        function basename(path) {
            var parts = String(path).split('/');
            return parts[parts.length - 1];
        }

        function doRenderMindmap(container, nodeTree, name) {
            if (!nodeTree || !nodeTree.topic) return;
            var options = {
                container: container.id,
                editable: false,
                theme: 'primary',
                mode: 'full',
                support_html: true,
                view: { hmargin: 80, vmargin: 40, line_width: 2, line_color: '#cbd5e1' },
                layout: { hspace: 60, vspace: 34, pspace: 22 }
            };
            var cid = container.id;
            // 延迟到容器布局就绪再渲染，避免 canvas 尺寸为 0
            setTimeout(function () {
                var el = document.getElementById(cid);
                if (!el) return;
                try {
                    el.innerHTML = '';
                    var jm = new jsMind(options);
                    jm.show({
                        meta: { name: name || '思维导图', author: 'MontageGTD AI', version: '1.0' },
                        format: 'node_tree',
                        data: nodeTree
                    });
                } catch (e) {
                    var errEl = document.getElementById(cid);
                    if (errEl) errEl.innerHTML = '<div class="artifact-dialog-error">思维导图渲染失败：' + escapeHtml(e && e.message ? e.message : String(e)) + '</div>';
                }
            }, 50);
        }

        function loadScript(src, cb) {
            var s = document.createElement('script');
            s.src = src;
            s.onload = cb;
            document.head.appendChild(s);
        }

        document.addEventListener('DOMContentLoaded', function () {
            var disposeAiPpt = function () {
                if (window.__aipptDisposeAll) { window.__aipptDisposeAll(); }
            };
            var mask = document.getElementById('artifactDialogMask');
            var close = document.getElementById('artifactDialogClose');
            if (mask && close) {
                close.addEventListener('click', function () { mask.style.display = 'none'; disposeAiPpt(); });
                mask.addEventListener('click', function (e) { if (e.target === mask) { mask.style.display = 'none'; disposeAiPpt(); } });
            }

            var genMask = document.getElementById('artifactGenMask');
            var genClose = document.getElementById('artifactGenClose');
            var genCancel = document.getElementById('artifactGenCancel');
            var genConfirm = document.getElementById('artifactGenConfirm');
            if (genMask) {
                if (genClose) { genClose.addEventListener('click', closeRegenerateModal); }
                if (genCancel) { genCancel.addEventListener('click', closeRegenerateModal); }
                genMask.addEventListener('click', function (e) { if (e.target === genMask) { closeRegenerateModal(); } });
                if (genConfirm) { genConfirm.addEventListener('click', confirmRegenerate); }
            }
        });

        window.openArtifactDialog = openArtifactDialog;
    })();
</script>