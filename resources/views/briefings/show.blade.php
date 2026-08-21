@extends('layouts.app')

@section('title', '文章简报详情 - 蒙太奇')
@section('description', '查看文章简报详情')

@section('content')
    <style>
        .bf-show { max-width: 900px; margin: 0 auto; }
        .bf-hero { background:linear-gradient(135deg,#4f46e5,#7c3aed); border-radius:16px; padding:28px; color:#fff; box-shadow:0 8px 24px rgba(79,70,229,.25); }
        .bf-hero h1 { font-size:1.5rem; font-weight:700; }
        .bf-hero .meta { display:flex; flex-wrap:wrap; gap:16px; margin-top:14px; }
        .bf-hero .meta-item { background:rgba(255,255,255,.14); border-radius:999px; padding:5px 14px; font-size:.8rem; }
        .bf-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 4px 14px rgba(0,0,0,.05); padding:22px 24px; margin-top:20px; }
        .bf-section-title { font-weight:700; color:#1e293b; font-size:1.05rem; display:flex; align-items:center; gap:8px; margin-bottom:14px; }
        .bf-section-title .dot { width:8px; height:8px; border-radius:50%; background:#6366f1; }
        .bf-hotline { background:#eef2ff; border-radius:10px; padding:14px 18px; color:#3730a3; line-height:1.8; }
        .bf-article-item { border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:10px; display:flex; align-items:flex-start; gap:10px; }
        .bf-article-body { flex:1; min-width:0; }
        .bf-article-title { font-weight:600; color:#1e293b; }
        .bf-article-sum { color:#64748b; font-size:.85rem; margin-top:4px; line-height:1.5; }
        .bf-article-meta { color:#94a3b8; font-size:.75rem; margin-top:5px; display:flex; gap:10px; align-items:center; }
        .bf-actions { display:flex; gap:6px; flex-shrink:0; }
        .bf-action { width:32px; height:32px; border-radius:8px; border:1px solid #e2e8f0; background:#fff; color:#94a3b8; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; }
        .bf-action:hover { background:#f1f5f9; color:#475569; }
        .bf-action.active-read { background:#dcfce7; color:#16a34a; border-color:#86efac; }
        .bf-action.active-star { background:#fef3c7; color:#d97706; border-color:#fcd34d; }
        .bf-action.active-later { background:#dbeafe; color:#2563eb; border-color:#93c5fd; }
        .bf-link { color:#6366f1; font-size:.8rem; text-decoration:none; }
        .bf-link:hover { text-decoration:underline; }
        .bf-tagwrap { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
        .bf-tag { border-radius:999px; padding:6px 14px; font-size:.82rem; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#475569; transition:all .15s; }
        .bf-tag:hover { border-color:#6366f1; color:#4338ca; }
        .bf-tag.active { background:#6366f1; color:#fff; border-color:#6366f1; }
        .bf-actions-label { font-size:.68rem; color:#94a3b8; }
        .bf-empty { color:#94a3b8; text-align:center; padding:20px 0; }
    </style>

    <div class="bf-show">
        <div class="flex items-center justify-between mb-4">
            <a href="/briefings" class="text-sm text-indigo-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i>返回简报列表</a>
            <button id="refreshBtn" class="text-sm text-indigo-600 hover:underline"><i class="fas fa-sync-alt mr-1"></i>刷新</button>
        </div>

        <div id="briefingRoot"><div class="text-gray-500 text-center py-10">加载中...</div></div>
    </div>

    <script>
    (function() {
        var pageId = {{ $pageId ?? 0 }};
        var statusBusy = false;

        function esc(s) {
            if (s === null || s === undefined) return '';
            return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; });
        }

        function actionIcons(article, subId) {
            var read = article && article.read;
            var star = article && article.starred;
            var later = article && article.read_later;
            return '<div class="bf-actions">'
                + '<button type="button" class="bf-action bf-set-read ' + (read?'active-read':'') + '" data-sub="' + subId + '" data-aid="' + (article?article.article_id:'') + '" title="已读"><i class="fas fa-check"></i></button>'
                + '<button type="button" class="bf-action bf-set-star ' + (star?'active-star':'') + '" data-sub="' + subId + '" data-aid="' + (article?article.article_id:'') + '" title="加星"><i class="fas fa-star"></i></button>'
                + '<button type="button" class="bf-action bf-set-later ' + (later?'active-later':'') + '" data-sub="' + subId + '" data-aid="' + (article?article.article_id:'') + '" title="稍后阅读"><i class="far fa-clock"></i></button>'
                + '</div>';
        }

        function renderArticleItem(item, isTrend) {
            var article = item.article || null;
            var title = item.title || (article ? article.subject : '');
            var summary = item.summary || (article ? article.summary : '');
            var subId = article ? article.article_sub_id : 0;
            var feedName = article ? article.feed_name : '';
            var pub = article && article.published ? String(article.published).replace('T',' ').substring(0,16) : '';
            var link = article && article.url ? article.url : '#';
            return '<div class="bf-article-item">'
                + '<div class="bf-article-body">'
                + '<div class="bf-article-title">' + esc(title) + '</div>'
                + (summary ? '<div class="bf-article-sum">' + esc(summary) + '</div>' : '')
                + '<div class="bf-article-meta">'
                + (feedName ? '<span><i class="fas fa-rss mr-1"></i>' + esc(feedName) + '</span>' : '')
                + (pub ? '<span>' + esc(pub) + '</span>' : '')
                + '<a class="bf-link" href="' + esc(link) + '" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i>原文</a>'
                + '</div>'
                + '</div>'
                + actionIcons(article, subId)
                + '</div>';
        }

        // 今日趋势：主标题展示 AI 汇总的趋势内容，文章作为佐证（链接 + 状态图标）
        function renderTrendItem(item) {
            var article = item.article || null;
            var title = (item && item.title) ? item.title : (article ? article.subject : '');
            var summary = (item && item.summary) ? item.summary : (article ? article.summary : '');
            var subId = article ? article.article_sub_id : 0;
            var feedName = article ? article.feed_name : '';
            var link = article && article.url ? article.url : '#';
            return '<div class="bf-article-item" style="flex-direction:column;align-items:stretch;">'
                + '<div class="bf-article-body">'
                + '<div class="bf-article-sum" style="color:#1e293b;font-size:.95rem;line-height:1.6;">'
                + '<i class="fas fa-arrow-trend-up mr-1" style="color:#10b981"></i>' + esc(title)
                + (summary ? '<div style="color:#64748b;font-size:.85rem;margin-top:6px;">' + esc(summary) + '</div>' : '')
                + '</div>'
                + '<div class="bf-article-meta" style="margin-top:8px;border-top:1px dashed #e2e8f0;padding-top:8px;">'
                + '<span style="color:#94a3b8;"><i class="fas fa-paperclip mr-1"></i>佐证</span>'
                + (feedName ? '<span><i class="fas fa-rss mr-1"></i>' + esc(feedName) + '</span>' : '')
                + '<a class="bf-link" href="' + esc(link) + '" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i>原文</a>'
                + '</div>'
                + '</div>'
                + actionIcons(article, subId)
                + '</div>';
        }

        function renderTagArticles(group) {
            var html = '<div class="bf-card" style="margin-top:12px">';
            html += '<div class="bf-section-title"><span class="dot" style="background:#f59e0b"></span>标签「' + esc(group.tag) + '」下的文章（' + group.articles.length + ' 篇）</div>';
            if (!group.articles.length) { html += '<div class="bf-empty">该标签下暂无可展示文章</div>'; }
            group.articles.forEach(function(article){
                var subId = article.article_sub_id;
                var sum = article.summary || '';
                var pub = article.published ? String(article.published).replace('T',' ').substring(0,16) : '';
                html += '<div class="bf-article-item"><div class="bf-article-body">'
                    + '<div class="bf-article-title">' + esc(article.subject) + '</div>'
                    + (sum ? '<div class="bf-article-sum">' + esc(sum) + '</div>' : '')
                    + '<div class="bf-article-meta">'
                    + (article.feed_name ? '<span><i class="fas fa-rss mr-1"></i>' + esc(article.feed_name) + '</span>' : '')
                    + (pub ? '<span>' + esc(pub) + '</span>' : '')
                    + '<a class="bf-link" href="' + esc(article.url || '#') + '" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i>原文</a>'
                    + '</div></div>'
                    + actionIcons(article, subId)
                    + '</div>';
            });
            html += '</div>';
            return html;
        }

        // 全部标签：合并所有标签下的文章并按文章去重
        function renderAllTagArticles(tags) {
            var seen = {};
            var articles = [];
            tags.forEach(function(g){
                (g.articles || []).forEach(function(a){
                    if (a && !seen[a.article_id]) {
                        seen[a.article_id] = true;
                        articles.push(a);
                    }
                });
            });
            var html = '<div class="bf-card" style="margin-top:12px">';
            html += '<div class="bf-section-title"><span class="dot" style="background:#f59e0b"></span>全部标签下的文章（' + articles.length + ' 篇）</div>';
            if (!articles.length) { html += '<div class="bf-empty">暂无可展示文章</div>'; }
            articles.forEach(function(article){
                var subId = article.article_sub_id;
                var sum = article.summary || '';
                var pub = article.published ? String(article.published).replace('T',' ').substring(0,16) : '';
                var tagText = (article.tags && article.tags.length) ? article.tags.join('、') : '';
                html += '<div class="bf-article-item"><div class="bf-article-body">'
                    + '<div class="bf-article-title">' + esc(article.subject) + '</div>'
                    + (sum ? '<div class="bf-article-sum">' + esc(sum) + '</div>' : '')
                    + '<div class="bf-article-meta">'
                    + (tagText ? '<span style="color:#8b5cf6;"><i class="fas fa-tags mr-1"></i>' + esc(tagText) + '</span>' : '')
                    + (article.feed_name ? '<span><i class="fas fa-rss mr-1"></i>' + esc(article.feed_name) + '</span>' : '')
                    + (pub ? '<span>' + esc(pub) + '</span>' : '')
                    + '<a class="bf-link" href="' + esc(article.url || '#') + '" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i>原文</a>'
                    + '</div></div>'
                    + actionIcons(article, subId)
                    + '</div>';
            });
            html += '</div>';
            return html;
        }

        function render(data) {
            var page = data.page;
            var hot = page.hot_topics || [];
            var trends = page.trends || [];
            var signals = page.signals || [];
            var tags = page.tag_aggregation || [];

            var html = '<div class="bf-hero">'
                + '<h1>' + esc(page.title) + '</h1>'
                + '<div class="meta">'
                + '<span class="meta-item"><i class="fas fa-hashtag mr-1"></i>主题数量：' + esc(page.topic_count) + '</span>'
                + '<span class="meta-item"><i class="fas fa-clock mr-1"></i>时间窗口：' + esc(page.time_window || '') + '</span>'
                + (page.model_name ? '<span class="meta-item"><i class="fas fa-robot mr-1"></i>模型：' + esc(page.model_name) + '</span>' : '')
                + (page.config_name ? '<span class="meta-item"><i class="fas fa-cog mr-1"></i>' + esc(page.config_name) + '</span>' : '')
                + '</div>'
                + '</div>';

            // 热点内容
            html += '<div class="bf-card">'
                + '<div class="bf-section-title"><span class="dot"></span>本次主要包含热点内容</div>';
            if (hot.length) {
                html += '<div class="bf-hotline"><i class="fas fa-fire mr-1" style="color:#ef4444"></i>' + hot.map(esc).join('；') + '</div>';
            } else {
                html += '<div class="bf-empty">暂无热点内容</div>';
            }
            html += '</div>';

            // 今日趋势
            html += '<div class="bf-card">'
                + '<div class="bf-section-title"><span class="dot" style="background:#10b981"></span>今日趋势' + (trends.length ? '（' + trends.length + ' 条）' : '') + '</div>';
            if (!trends.length) { html += '<div class="bf-empty">暂无趋势条目</div>'; }
            trends.forEach(function(item){ html += renderTrendItem(item); });
            html += '</div>';

            // 待观察信号
            html += '<div class="bf-card">'
                + '<div class="bf-section-title"><span class="dot" style="background:#f59e0b"></span>待观察信号</div>';
            if (!signals.length) { html += '<div class="bf-empty">暂无待观察信号</div>'; }
            signals.forEach(function(item){ html += renderArticleItem(item, false); });
            html += '</div>';

            // 标签聚合
            html += '<div class="bf-card">'
                + '<div class="bf-section-title"><span class="dot" style="background:#8b5cf6"></span>标签聚合</div>';
            if (!tags.length) {
                html += '<div class="bf-empty">暂无标签聚合</div>';
            } else {
                // 「全部」默认选中
                var totalCount = 0;
                tags.forEach(function(g){ totalCount += (g.articles || []).length; });
                html += '<div class="bf-tagwrap">';
                html += '<span class="bf-tag active" data-tag-idx="-1"><i class="fas fa-th-large mr-1"></i>全部 <span class="bf-actions-label">(去重)</span></span>';
                tags.forEach(function(g, idx){
                    html += '<span class="bf-tag" data-tag-idx="' + idx + '">' + esc(g.tag) + ' <span class="bf-actions-label">(' + g.count + ')</span></span>';
                });
                html += '</div>';
                // 默认展示全部标签下的文章
                html += '<div id="tagArticlesContainer">' + renderAllTagArticles(tags) + '</div>';
            }
            html += '</div>';

            return html;
        }

        function load() {
            var root = document.getElementById('briefingRoot');
            root.innerHTML = '<div class="text-gray-500 text-center py-10">加载中...</div>';
            window.taskApiFetch('/api/v2/briefings/pages/' + pageId).then(function(r){ return r.json(); }).then(function(data){
                if (data.code !== 9999 || !data.result || !data.result.page) { root.innerHTML = '<div class="bf-empty">简报不存在或已删除</div>'; return; }
                window.__briefingTags = (data.result.page.tag_aggregation || []).slice();
                root.innerHTML = render(data.result);
                bindTagEvents(root);
            }).catch(function(){ root.innerHTML = '<div class="bf-empty">加载失败，请刷新重试</div>'; });
        }

        function bindTagEvents(root) {
            var tags = Array.prototype.slice.call(root.querySelectorAll('.bf-tag'));
            tags.forEach(function(tag){
                tag.addEventListener('click', function(){
                    var idx = parseInt(tag.getAttribute('data-tag-idx'), 10);
                    var container = document.getElementById('tagArticlesContainer');
                    if (container) container.remove();
                    tags.forEach(function(t){ t.classList.remove('active'); });
                    tag.classList.add('active');
                    var groups = window.__briefingTags || [];
                    var tagwrap = tag.parentNode;
                    var holder = document.createElement('div');
                    holder.id = 'tagArticlesContainer';
                    if (idx === -1) {
                        // 全部标签
                        holder.innerHTML = renderAllTagArticles(groups);
                    } else if (groups[idx]) {
                        holder.innerHTML = renderTagArticles(groups[idx]);
                    } else {
                        holder.innerHTML = '<div class="bf-card" style="margin-top:12px"><div class="bf-empty">暂无可展示文章</div></div>';
                    }
                    tagwrap.insertAdjacentElement('afterend', holder);
                });
            });
        }

        // 状态切换
        document.addEventListener('click', function(e){
            var btn = e.target.closest('.bf-set-read, .bf-set-star, .bf-set-later');
            if (!btn || statusBusy) return;
            var subId = btn.getAttribute('data-sub');
            if (!subId) { alert('该文章暂无订阅记录'); return; }
            var kind = btn.classList.contains('bf-set-read') ? 'read' : (btn.classList.contains('bf-set-star') ? 'star' : 'read_later');
            var active = btn.classList.contains('active-read') || btn.classList.contains('active-star') || btn.classList.contains('active-later');
            var nextStatus = active ? (kind === 'star' ? 'read' : 'unread') : kind;
            statusBusy = true;
            window.taskApiFetch('/api/v2/articles/status/' + subId, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({status: nextStatus})
            }).then(function(r){ return r.json(); }).then(function(data){
                statusBusy = false;
                if (data.code === 9999) {
                    var siblings = btn.parentNode.querySelectorAll('.bf-action');
                    siblings.forEach(function(s){ s.classList.remove('active-read','active-star','active-later'); });
                    if (!active) btn.classList.add(kind === 'read' ? 'active-read' : (kind === 'star' ? 'active-star' : 'active-later'));
                } else {
                    alert('设置失败：' + (data.msg || '请重试'));
                }
            }).catch(function(){ statusBusy = false; alert('设置失败，请重试'); });
        });

        document.getElementById('refreshBtn').addEventListener('click', load);

        document.addEventListener('DOMContentLoaded', function () { load(); });
    })();
    </script>
@endsection
