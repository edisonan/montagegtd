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
</style>

<script>
    (function () {
        var TYPE_LABELS = {
            visual_reading: '可视化阅读',
            mind_map: '思维导图',
            briefing_latest: '最新简报',
            briefing_followed: '关注简报'
        };

        function escapeHtml(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
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

        function openArtifactDialog(opts) {
            var relatedType = opts.relatedType;
            var relatedId = opts.relatedId;
            var artifactType = opts.artifactType;
            var label = TYPE_LABELS[artifactType] || artifactType;

            var mask = document.getElementById('artifactDialogMask');
            var body = document.getElementById('artifactDialogBody');
            document.getElementById('artifactDialogTitle').textContent = label;
            document.getElementById('artifactDialogSub').textContent = relatedType + ' #' + relatedId + ' · ' + 'AI 二次产出';
            body.innerHTML = '<div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在查询…</div>';
            mask.style.display = 'flex';

            requestJson('/api/v2/artifacts?related_type=' + encodeURIComponent(relatedType) + '&related_id=' + relatedId + '&artifact_type=' + encodeURIComponent(artifactType))
                .then(function (data) {
                    if (!data || Number(data.code) !== 9999) {
                        throw new Error(data && data.msg ? data.msg : '查询失败');
                    }
                    var artifacts = (data.result && data.result.artifacts) || [];
                    var mine = artifacts.filter(function (a) { return a.artifact_type === artifactType; });
                    render(relatedType, relatedId, artifactType, label, mine);
                })
                .catch(function (err) {
                    body.innerHTML = '<div class="artifact-dialog-empty"><div class="icon"><i class="fas fa-circle-exclamation"></i></div><p>查询失败：' + escapeHtml(err.message || err) + '</p></div>';
                });
        }

        function render(relatedType, relatedId, artifactType, label, artifacts) {
            var body = document.getElementById('artifactDialogBody');
            var success = artifacts.filter(function (a) { return a.status === 'success'; });
            var failed = artifacts.filter(function (a) { return a.status !== 'success'; });
            var html = '';

            if (success.length === 0) {
                html += '<div class="artifact-dialog-empty">';
                html += '<div class="icon"><i class="fas fa-wand-magic-sparkles"></i></div>';
                html += '<p>当前还没有「' + label + '」制品</p>';
                html += '<p style="font-size:12px;color:#94a3b8;">点击下方按钮，让 AI 基于原文生成一个</p>';
                html += '</div>';
                html += '<div style="text-align:center;">';
                html += '<button type="button" class="artifact-dialog-generate-btn" data-gen="1"><i class="fas fa-wand-magic-sparkles"></i> 生成' + label + '</button>';
                html += '</div>';
            } else {
                // 标题行：已有 + 重新生成
                html += '<div class="artifact-dialog-item" style="background:#f0f9ff;border-color:#bae6fd;">';
                html += '<div class="info"><div class="name">已生成「' + label + '」</div>';
                html += '<div class="meta">' + (success[0].model_name ? escapeHtml(success[0].model_name) : '') + (success[0].generated_at ? ' · ' + escapeHtml(success[0].generated_at) : '') + '</div></div>';
                var viewBtn = '';
                success.forEach(function (a) {
                    viewBtn += '<a href="/artifacts/' + a.id + '" target="_blank" class="dlg-fill-btn"><i class="fas fa-expand mr-1"></i>独立页</a>';
                });
                html += '<div class="ops"><button type="button" data-gen="1"><i class="fas fa-rotate"></i>重新生成</button>' + viewBtn + '</div>';
                html += '</div>';

                // mind_map：弹窗内直接内嵌 jsMind 展示
                if (artifactType === 'mind_map') {
                    var mm = success[0];
                    var nodeTree = parseNodeTree(mm);
                    if (nodeTree) {
                        html += '<div id="artifactDialogMindmap" class="dlg-mindmap"><div class="dlg-mindmap-loading"><i class="fas fa-spinner fa-spin"></i> 加载思维导图…</div></div>';
                        html += '<div class="dlg-tip">满屏看不全？点上方「独立页」查看完整导图。</div>';
                    } else {
                        html += '<div class="artifact-dialog-error">思维导图数据无法解析</div>';
                    }
                } else {
                    // visual_reading / 其他：弹窗内直接展示，独立页保留
                    success.forEach(function (a) {
                        html += '<div id="dlgVisualContent" class="dlg-html-content"><div class="artifact-dialog-loading"><i class="fas fa-spinner fa-spin"></i> 正在加载可视化内容…</div></div>';
                        html += '<div class="dlg-tip">如弹窗内展示不完整，可点上方「独立页」查看全屏版本。</div>';
                    });
                }

                if (failed.length > 0) {
                    failed.forEach(function (a) {
                        html += '<div class="artifact-dialog-error">' + label + '生成失败：' + escapeHtml(a.error_message || '未知原因') + '</div>';
                    });
                }
            }

            body.innerHTML = html;

            var genBtn = body.querySelector('[data-gen="1"]');
            if (genBtn) {
                genBtn.addEventListener('click', function () {
                    generate(relatedType, relatedId, artifactType, label);
                });
            }

            // visual_reading 内嵌展示：调 show 接口拉完整内容
            if (artifactType !== 'mind_map' && success.length > 0) {
                var vr = success[0];
                var contentEl = document.getElementById('dlgVisualContent');
                if (contentEl) {
                    requestJson('/api/v2/artifacts/' + vr.id)
                        .then(function (data) {
                            var el = document.getElementById('dlgVisualContent');
                            if (!el) return;
                            var full = data && data.result && data.result.artifact;
                            if (full && full.content) {
                                el.innerHTML = '<div class="ai-render-content max-w-none rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">' + full.content + '</div>';
                            } else {
                                el.innerHTML = '<div class="artifact-dialog-error">加载可视化内容失败</div>';
                            }
                        })
                        .catch(function () {
                            var el = document.getElementById('dlgVisualContent');
                            if (el) el.innerHTML = '<div class="artifact-dialog-error">加载可视化内容失败</div>';
                        });
                }
            }

            // mind_map 内嵌导图渲染
            if (artifactType === 'mind_map' && success.length > 0) {
                var mm = success[0];
                var nodeTree = parseNodeTree(mm);
                if (nodeTree) {
                    renderMindmapInDialog(nodeTree, mm.name);
                }
            }
        }

        // 解析制品 content 里的 node_tree data
        function parseNodeTree(artifact) {
            if (!artifact || !artifact.content) return null;
            try {
                var decoded = typeof artifact.content === 'string' ? JSON.parse(artifact.content) : artifact.content;
                if (!decoded) return null;
                return decoded.data || decoded;
            } catch (e) { return null; }
        }

        // 在弹窗内渲染 jsMind
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

        function generate(relatedType, relatedId, artifactType, label) {
            var body = document.getElementById('artifactDialogBody');
            var generateBtn = body.querySelector('[data-gen="1"]');
            if (generateBtn) { generateBtn.disabled = true; generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 生成中，请稍候…'; }
            body.insertAdjacentHTML('beforeend', '<div class="artifact-dialog-loading" id="artifactDialogGenerating"><i class="fas fa-spinner fa-spin"></i> AI 正在生成' + label + '，可能需要 1-2 分钟…</div>');

            requestJson('/api/v2/artifacts/generate', {
                method: 'POST',
                body: JSON.stringify({ related_type: relatedType, related_id: relatedId, artifact_type: artifactType, force: 1 })
            }).then(function (data) {
                if (!data || Number(data.code) !== 9999) {
                    throw new Error(data && data.msg ? data.msg : '生成失败');
                }
                var artifact = data.result && data.result.artifact;
                if (!artifact || artifact.status !== 'success') {
                    throw new Error((artifact && artifact.error_message) || 'AI 生成失败，请稍后重试');
                }
                // 重新查询展示
                openArtifactDialog({ relatedType: relatedType, relatedId: relatedId, artifactType: artifactType });
            }).catch(function (err) {
                var loading = document.getElementById('artifactDialogGenerating');
                if (loading) { loading.remove(); }
                if (generateBtn) { generateBtn.disabled = false; generateBtn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> 重新生成'; }
                body.insertAdjacentHTML('beforeend', '<div class="artifact-dialog-error">生成失败：' + escapeHtml(err.message || err) + '</div>');
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var mask = document.getElementById('artifactDialogMask');
            var close = document.getElementById('artifactDialogClose');
            if (!mask || !close) { return; }
            close.addEventListener('click', function () { mask.style.display = 'none'; });
            mask.addEventListener('click', function (e) { if (e.target === mask) { mask.style.display = 'none'; } });
        });

        window.openArtifactDialog = openArtifactDialog;
    })();
</script>