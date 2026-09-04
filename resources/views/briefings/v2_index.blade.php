@extends('layouts.app')

@section('title', '文章简报 v2 - 蒙太奇')
@section('description', '简报 v2 对比版：独立管线的生成结果，可随时与 v1 对照')

@section('content')
    <style>
        .briefing-page { max-width: 1100px; margin: 0 auto; }
        .bf-topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap:wrap; gap:12px; }
        .bf-btn { border-radius:10px; padding:9px 18px; font-weight:600; font-size:.9rem; }
        .bf-btn-new { background:linear-gradient(135deg,#059669,#10b981); color:#fff; }
        .bf-btn-new:hover { filter:brightness(1.06); }
        .bf-btn-ghost { background:#fff; color:#374151; border:1px solid #e2e8f0; }
        .bf-btn-ghost:hover { background:#f1f5f9; }
        .bf-layout { display:grid; grid-template-columns: 360px 1fr; gap:20px; }
        @media (max-width: 900px) { .bf-layout { grid-template-columns: 1fr; } }
        .bf-panel { background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 4px 14px rgba(0,0,0,.05); overflow:hidden; }
        .bf-panel-head { padding:16px 20px; border-bottom:1px solid #eef2f7; display:flex; justify-content:space-between; align-items:center; }
        .bf-panel-title { font-weight:700; color:#1e293b; font-size:1rem; }
        .bf-config-item { padding:15px 20px; border-bottom:1px solid #eef2f7; }
        .bf-config-name { font-weight:600; color:#1e293b; }
        .bf-config-meta { color:#94a3b8; font-size:.78rem; margin-top:4px; }
        .bf-config-actions { display:flex; gap:6px; margin-top:8px; }
        .bf-mini-btn { font-size:.72rem; padding:3px 9px; border-radius:6px; border:1px solid #e2e8f0; background:#fff; color:#475569; cursor:pointer; }
        .bf-mini-btn:hover { background:#f1f5f9; }
        .bf-mini-btn.primary { border-color:#10b981; color:#047857; background:#ecfdf5; }
        .bf-mini-btn.primary:hover { background:#d1fae5; }
        .bf-empty { padding:30px 20px; text-align:center; color:#94a3b8; }
        .bf-page-item { padding:13px 20px; border-bottom:1px solid #eef2f7; display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .bf-page-item:hover { background:#f8fafc; }
        .bf-page-title { font-weight:600; color:#1e293b; }
        .bf-page-sub { color:#94a3b8; font-size:.78rem; margin-top:3px; }
        .bf-page-time { color:#64748b; font-size:.8rem; white-space:nowrap; }
        .bf-page-meta { display:flex; gap:8px; align-items:center; }
        .bf-badge { font-size:.7rem; background:#eef2ff; color:#4338ca; border-radius:999px; padding:2px 8px; }
        .bf-badge-warn { font-size:.7rem; background:#fef3c7; color:#b45309; border-radius:999px; padding:2px 8px; }
        .bf-badge-ok { font-size:.7rem; background:#d1fae5; color:#047857; border-radius:999px; padding:2px 8px; }
        .text-truncate { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .generating-mask { position:fixed; inset:0; background:rgba(255,255,255,.7); z-index:50; display:flex; align-items:center; justify-content:center; }
    </style>

    <div class="briefing-page">
        <div class="bf-topbar">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-clone mr-2 text-emerald-600"></i>文章简报 v2</h1>
                <p class="text-gray-500 text-sm mt-1">对比版管线：max_tokens 4096 · 标签聚合移出 LLM · 失败自动重试 · 与 v1 同口径候选</p>
            </div>
            <div class="flex gap-3">
                <a href="/briefings" class="bf-btn bf-btn-ghost"><i class="fas fa-file-alt mr-1"></i>对比 v1 简报</a>
            </div>
        </div>

        <div class="bf-layout">
            <!-- 左：配置列表（只读复用 v1 配置） -->
            <div class="bf-panel">
                <div class="bf-panel-head">
                    <span class="bf-panel-title"><i class="fas fa-cog mr-1 text-emerald-600"></i>简报配置</span>
                    <span id="configCount" class="text-xs text-gray-400"></span>
                </div>
                <div id="configList">
                    <div class="bf-empty">加载中...</div>
                </div>
            </div>

            <!-- 右：v2 简报历史 -->
            <div class="bf-panel">
                <div class="bf-panel-head">
                    <span class="bf-panel-title"><i class="fas fa-newspaper mr-1 text-emerald-600"></i>生成的简报 v2</span>
                    <span id="pageCount" class="text-xs text-gray-400"></span>
                </div>
                <div id="pageList">
                    <div class="bf-empty">加载中...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var configs = [];
        var selectedConfigId = null;
        var generating = false;

        function esc(s) {
            if (s === null || s === undefined) return '';
            return String(s).replace(/[&<>"']/g, function(c){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
            });
        }

        function showMask(on) {
            var mask = document.getElementById('genMask');
            if (on) {
                if (!mask) {
                    mask = document.createElement('div');
                    mask.id = 'genMask';
                    mask.className = 'generating-mask';
                    mask.innerHTML = '<div class="text-center"><div class="inline-block animate-spin text-emerald-600 mb-3"><i class="fas fa-circle-notch fa-3x"></i></div><div class="text-gray-700 font-semibold">正在生成 v2 简报，请稍候（约 10~30 秒）...</div></div>';
                    document.body.appendChild(mask);
                }
                mask.style.display = 'flex';
            } else if (mask) {
                mask.style.display = 'none';
            }
        }

        function loadConfigs() {
            var wrap = document.getElementById('configList');
            wrap.innerHTML = '<div class="bf-empty">加载中...</div>';
            if (!window.taskApiFetch) { showLoadError('前端 API 尚未就绪，请刷新重试'); return; }
            window.taskApiFetch('/api/v2/briefings-v2/configs').then(function(r){ return r.json(); }).then(function(data){
                if (!data || data.code !== 9999) { showLoadError('加载配置失败：' + ((data && data.msg) || '未知错误')); return; }
                configs = (data.result && data.result.configs) ? data.result.configs : [];
                document.getElementById('configCount').textContent = configs.length + ' 个';
                renderConfigs();
                loadPages();
            }).catch(function(err){
                console.error('loadConfigs error', err);
                showLoadError('加载配置失败，点击下方重试');
            });
        }

        function showLoadError(message) {
            var wrap = document.getElementById('configList');
            if (!wrap) return;
            wrap.innerHTML = '<div class="bf-empty">' + esc(message) + '<br>'
                + '<button type="button" onclick="location.reload()" style="margin-top:10px;padding:6px 16px;border-radius:8px;background:#059669;color:#fff;font-weight:600;cursor:pointer;border:none;">刷新重试</button></div>';
        }

        function whenReady(cb, tries) {
            tries = tries || 0;
            if (typeof window.taskApiFetch === 'function') { cb(); return; }
            if (tries > 20) { showLoadError('前端 API 加载超时，请刷新重试'); return; }
            setTimeout(function(){ whenReady(cb, tries + 1); }, 300);
        }

        function renderConfigs() {
            var wrap = document.getElementById('configList');
            wrap.innerHTML = '';
            if (!configs.length) {
                wrap.innerHTML = '<div class="bf-empty">还没有简报配置，请到 <a href="/briefings/config" class="text-emerald-600 underline">v1 简报配置</a> 创建</div>';
                return;
            }
            configs.forEach(function(cfg){
                var div = document.createElement('div');
                div.className = 'bf-config-item';
                var latest = cfg.latest_v2_page;
                var v1Time = cfg.last_generated_at ? cfg.last_generated_at.replace('T',' ').substring(5,16) : '未生成';
                var v2Info = latest
                    ? ('v2 最新：' + esc(latest.title) + '（' + latest.generated_at.replace('T',' ').substring(5,16) + '）')
                    : 'v2 尚未生成，点击下方「生成 v2」开始对比';
                div.innerHTML =
                    '<div class="flex items-center gap-2">'
                    + '<i class="fas ' + (cfg.enabled ? 'fa-toggle-on text-green-500' : 'fa-toggle-off text-gray-300') + '"></i>'
                    + '<span class="bf-config-name">' + esc(cfg.name) + '</span>'
                    + (latest ? (latest.fallback ? '<span class="bf-badge-warn">兜底</span>' : '<span class="bf-badge-ok">真实LLM</span>') : '')
                    + '</div>'
                    + '<div class="bf-config-meta">前 ' + esc(cfg.pull_hours) + ' 小时 · 定时 ' + esc(cfg.schedule_time)
                    + ' · ' + (function(scope, feeds, cats){
                        if (scope === 'feeds') return feeds.length + ' 个指定订阅源';
                        if (scope === 'exclude_feeds') return '排除 ' + feeds.length + ' 个订阅源';
                        if (scope === 'by_category') return cats.length + ' 个分类';
                        return '全部订阅源';
                    })(cfg.scope, cfg.feed_ids || [], cfg.category_ids || [])
                    + ' · v1 最近 ' + v1Time + ' · v2 已生成 ' + (cfg.v2_page_count || 0) + ' 期</div>'
                    + '<div class="bf-config-meta">' + v2Info + '</div>'
                    + '<div class="bf-config-actions">'
                    + '<button class="bf-mini-btn primary gen" data-id="' + cfg.id + '"><i class="fas fa-bolt mr-1"></i>生成 v2</button>'
                    + (latest ? '<a class="bf-mini-btn" href="/briefings-v2/' + latest.id + '">查看最新</a>' : '')
                    + '</div>';
                wrap.appendChild(div);
            });
        }

        function loadPages(configId) {
            var url = '/api/v2/briefings-v2/pages';
            if (selectedConfigId) { url += '?config_id=' + selectedConfigId; }
            window.taskApiFetch(url).then(function(r){ return r.json(); }).then(function(data){
                var pages = (data.result && data.result.pages) ? data.result.pages : [];
                document.getElementById('pageCount').textContent = pages.length + ' 条';
                var wrap = document.getElementById('pageList');
                wrap.innerHTML = '';
                if (!pages.length) {
                    wrap.innerHTML = '<div class="bf-empty">还没有 v2 简报，选择配置点击「生成 v2」对比</div>';
                    return;
                }
                pages.forEach(function(p){
                    var div = document.createElement('div');
                    div.className = 'bf-page-item';
                    var tag = p.fallback
                        ? '<span class="bf-badge-warn">兜底</span>'
                        : '<span class="bf-badge-ok">真实LLM</span>';
                    div.innerHTML =
                        '<div class="min-w-0">'
                        + '<div class="flex items-center gap-2"><span class="bf-page-title text-truncate">' + esc(p.title) + '</span>' + tag + '</div>'
                        + '<div class="bf-page-sub">' + esc(p.time_window || '') + ' · 候选 ' + esc(p.candidate_count) + ' 篇</div>'
                        + '</div>'
                        + '<div class="bf-page-meta">'
                        + '<span class="bf-badge">' + esc(p.topic_count) + ' 主题</span>'
                        + '<span class="bf-page-time">' + (p.generated_at ? esc(p.generated_at.replace('T',' ').substring(5,16)) : '') + '</span>'
                        + '<a href="/briefings-v2/' + p.id + '" class="text-emerald-600 text-sm hover:underline"><i class="fas fa-eye"></i></a>'
                        + '</div>';
                    wrap.appendChild(div);
                });
            }).catch(function(err){
                console.error('loadPages error', err);
                var pl = document.getElementById('pageList');
                if (pl) pl.innerHTML = '<div class="bf-empty">加载简报历史失败，请刷新重试</div>';
            });
        }

        document.getElementById('configList').addEventListener('click', function(e){
            var btn = e.target.closest('.bf-mini-btn.gen');
            if (!btn || generating) return;
            var id = btn.getAttribute('data-id');
            generating = true;
            showMask(true);
            window.taskApiFetch('/api/v2/briefings-v2/configs/' + id + '/generate', { method: 'POST' }).then(function(r){ return r.json(); }).then(function(data){
                generating = false;
                showMask(false);
                if (data.code === 9999 && data.result && data.result.page_id) {
                    window.location.href = '/briefings-v2/' + data.result.page_id;
                } else {
                    alert('生成失败：' + (data.msg || '未知错误'));
                }
            }).catch(function(err){
                generating = false;
                showMask(false);
                alert('生成失败，请重试');
            });
        });

        document.addEventListener('DOMContentLoaded', function () { whenReady(loadConfigs); });
    })();
    </script>
@endsection