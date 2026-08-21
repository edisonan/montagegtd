@extends('layouts.app')

@section('title', '简报配置 - 蒙太奇')
@section('description', '配置文章简报的拉取时间范围、定时获取时间、文章范围等参数')

@section('content')
    <style>
        .briefing-config-page { max-width: 820px; margin: 0 auto; }
        .bf-card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 14px rgba(0,0,0,.05); padding: 28px; }
        .bf-field { margin-bottom: 22px; }
        .bf-label { display: block; font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: .92rem; }
        .bf-hint { color: #94a3b8; font-size: .78rem; margin-top: 5px; }
        .bf-input { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: .9rem; }
        .bf-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .bf-radio-group { display: flex; gap: 16px; }
        .bf-radio { flex: 1; border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px 16px; cursor: pointer; transition: all .15s; }
        .bf-radio.selected { border-color: #6366f1; background: #eef2ff; box-shadow: 0 0 0 2px rgba(99,102,241,.15); }
        .bf-radio-title { font-weight: 600; color: #1e293b; }
        .bf-radio-sub { color: #94a3b8; font-size: .78rem; margin-top: 4px; }
        .bf-chip { display: inline-flex; align-items: center; gap: 6px; background: #eef2ff; color: #4338ca; border-radius: 999px; padding: 5px 12px; font-size: .8rem; margin: 0 6px 6px 0; }
        .bf-chip .x { cursor: pointer; font-weight: 700; }
        .bf-btn-primary { background: linear-gradient(135deg,#4f46e5,#6366f1); color:#fff; border-radius: 8px; padding: 9px 20px; font-weight: 600; }
        .bf-btn-primary:hover { filter: brightness(1.05); }
        .bf-btn-secondary { background:#f1f5f9; color:#334155; border-radius:8px; padding:9px 20px; font-weight:600; }
        .feed-option { display:inline-flex; align-items:center; gap:6px; border:1px solid #e2e8f0; border-radius:8px; padding:6px 10px; margin:0 6px 6px 0; cursor:pointer; background:#fff; font-size:.82rem; }
        .feed-option.selected { border-color:#6366f1; background:#eef2ff; color:#4338ca; }
    </style>

    <div class="briefing-config-page">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isset($configId) && $configId ? '编辑简报配置' : '新建简报配置' }}</h1>
                <p class="text-gray-500 text-sm mt-1">配置拉取时间范围、定时获取与文章范围</p>
            </div>
            <a href="/briefings" class="text-sm text-indigo-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i>返回简报</a>
        </div>

        <div class="bf-card">
            <form id="briefingConfigForm">
                <input type="hidden" id="config_id" value="{{ isset($configId) ? $configId : '' }}">

                <div class="bf-field">
                    <label class="bf-label">配置名称</label>
                    <input type="text" id="name" class="bf-input" placeholder="例如：早间技术简报" maxlength="120">
                </div>

                <div class="bf-field">
                    <label class="bf-label">拉取时间范围（前 x 小时）</label>
                    <div class="flex items-center gap-3">
                        <input type="number" id="pull_hours" class="bf-input" min="1" max="24" value="6" style="max-width:150px;">
                        <span class="text-gray-500 text-sm">小时（跨度不能大于 24 小时）</span>
                    </div>
                    <div class="bf-hint">简报将聚合最近 x 小时内发布的文章。</div>
                </div>

                <div class="bf-field">
                    <label class="bf-label">定时获取时间</label>
                    <input type="time" id="schedule_time" class="bf-input" value="08:00" style="max-width:160px;">
                    <div class="bf-hint">到达设定时间后自动生成简报（北京时间）。</div>
                </div>

                <div class="bf-field">
                    <label class="bf-label">拉取文章范围</label>
                    <div class="bf-radio-group" style="flex-wrap:wrap;">
                        <div class="bf-radio selected" data-scope="all" id="scopeAll" style="flex:1 1 45%;">
                            <div class="bf-radio-title">全部订阅源</div>
                            <div class="bf-radio-sub">聚合用户所有订阅的文章</div>
                        </div>
                        <div class="bf-radio" data-scope="feeds" id="scopeFeeds" style="flex:1 1 45%;">
                            <div class="bf-radio-title">指定多个订阅源</div>
                            <div class="bf-radio-sub">仅从选中的订阅源拉取</div>
                        </div>
                        <div class="bf-radio" data-scope="exclude_feeds" id="scopeExcludeFeeds" style="flex:1 1 45%;">
                            <div class="bf-radio-title">排除多个订阅源</div>
                            <div class="bf-radio-sub">排除选中的订阅源，其余拉取</div>
                        </div>
                        <div class="bf-radio" data-scope="by_category" id="scopeByCategory" style="flex:1 1 45%;">
                            <div class="bf-radio-title">按订阅源分类</div>
                            <div class="bf-radio-sub">按订阅源分类维度拉取</div>
                        </div>
                    </div>
                    <input type="hidden" id="scope" value="all">
                </div>

                <div class="bf-field" id="feedPickerWrap" style="display:none;">
                    <label class="bf-label"><span id="feedPickerLabel">选择订阅源</span>（按分类展示，可多选）</label>
                    <div id="feedOptions"></div>
                    <div id="feedLoading" class="text-gray-400 text-sm">加载订阅源中...</div>
                    <input type="hidden" id="feed_ids" value="">
                </div>

                <div class="bf-field" id="categoryPickerWrap" style="display:none;">
                    <label class="bf-label">选择订阅源分类（可多选）</label>
                    <div id="categoryOptions"></div>
                    <div id="categoryLoading" class="text-gray-400 text-sm">加载分类中...</div>
                    <input type="hidden" id="category_ids" value="">
                </div>

                <div class="bf-field">
                    <label class="bf-label">附加补充内容</label>
                    <textarea id="supplement" class="bf-input" rows="4" placeholder="可选：填写你想让简报侧重的内容，例如「重点关注 AI 与芯片相关动态」"></textarea>
                </div>

                <div class="bf-field flex items-center gap-3">
                    <input type="checkbox" id="enabled" checked class="w-4 h-4">
                    <label for="enabled" class="text-gray-700 text-sm">启用该配置（参与定时自动生成）</label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bf-btn-primary">保存配置</button>
                    <button type="button" id="generateBtn" class="bf-btn-secondary">保存并立即生成</button>
                    <span id="formMsg" class="text-sm text-gray-500"></span>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        var configId = document.getElementById('config_id').value;
        var selectedFeedIds = [];
        var selectedCategoryIds = [];
        var allFeeds = [];
        var allCategories = [];
        var currentScope = 'all';

        function setScope(scope) {
            currentScope = scope;
            document.getElementById('scope').value = scope;
            document.getElementById('scopeAll').classList.toggle('selected', scope === 'all');
            document.getElementById('scopeFeeds').classList.toggle('selected', scope === 'feeds');
            document.getElementById('scopeExcludeFeeds').classList.toggle('selected', scope === 'exclude_feeds');
            document.getElementById('scopeByCategory').classList.toggle('selected', scope === 'by_category');

            var showFeeds = (scope === 'feeds' || scope === 'exclude_feeds');
            document.getElementById('feedPickerWrap').style.display = showFeeds ? 'block' : 'none';
            document.getElementById('categoryPickerWrap').style.display = scope === 'by_category' ? 'block' : 'none';
            document.getElementById('feedPickerLabel').textContent = scope === 'exclude_feeds' ? '选择要排除的订阅源' : '选择订阅源';
        }

        document.getElementById('scopeAll').addEventListener('click', function(){ setScope('all'); });
        document.getElementById('scopeFeeds').addEventListener('click', function(){ setScope('feeds'); if (allFeeds.length === 0) { loadFeeds(); } });
        document.getElementById('scopeExcludeFeeds').addEventListener('click', function(){ setScope('exclude_feeds'); if (allFeeds.length === 0) { loadFeeds(); } });
        document.getElementById('scopeByCategory').addEventListener('click', function(){ setScope('by_category'); if (allCategories.length === 0) { loadFeeds(); } });

        function loadFeeds() {
            var feedLoading = document.getElementById('feedLoading');
            var catLoading = document.getElementById('categoryLoading');
            feedLoading.style.display = 'block';
            catLoading.style.display = 'block';
            return window.taskApiFetch('/api/v2/feeds').then(function(resp){
                return resp.json();
            }).then(function(data){
                feedLoading.style.display = 'none';
                catLoading.style.display = 'none';
                var list = (data && data.result && data.result.feed_subs) ? data.result.feed_subs : [];
                var cats = (data && data.result && data.result.categorys) ? data.result.categorys : [];
                allFeeds = list;
                allCategories = cats;
                renderFeedOptions();
                renderCategoryOptions();
            }).catch(function(){
                feedLoading.textContent = '加载订阅源失败';
                catLoading.textContent = '加载分类失败';
            });
        }

        function feedLabel(fs) {
            var feed = fs.feed || {};
            var name = fs.feed_name || feed.feed_name || feed.title || '';
            return String(name || ('订阅源' + (feed.id || ''))).trim();
        }

        function feedCategoryId(fs) {
            var c = fs.category || {};
            var id = fs.category_id || c.id || 0;
            return parseInt(id, 10) || 0;
        }

        function renderFeedOptions() {
            var wrap = document.getElementById('feedOptions');
            wrap.innerHTML = '';

            // 按分类分组展示订阅源
            var groups = {};
            var order = [];
            allFeeds.forEach(function(fs){
                var catId = feedCategoryId(fs);
                var catName = (fs.category && fs.category.name) || ('分类' + catId);
                if (!groups[catId]) { groups[catId] = { name: catName, feeds: [] }; order.push(catId); }
                groups[catId].feeds.push(fs);
            });

            order.forEach(function(catId){
                var g = groups[catId];
                var groupEl = document.createElement('div');
                groupEl.style.marginBottom = '12px';
                var head = document.createElement('div');
                head.style.cssText = 'font-weight:600;color:#64748b;font-size:.78rem;margin-bottom:6px;';
                head.textContent = g.name;
                groupEl.appendChild(head);

                var chips = document.createElement('div');
                chips.className = 'flex flex-wrap';
                g.feeds.forEach(function(fs){
                    var feed = fs.feed || {};
                    var id = feed.id;
                    if (!id) return;
                    var el = document.createElement('span');
                    el.className = 'feed-option';
                    el.textContent = feedLabel(fs);
                    if (selectedFeedIds.indexOf(id) >= 0) el.classList.add('selected');
                    el.addEventListener('click', function(){
                        var idx = selectedFeedIds.indexOf(id);
                        if (idx >= 0) { selectedFeedIds.splice(idx, 1); } else { selectedFeedIds.push(id); }
                        el.classList.toggle('selected');
                        document.getElementById('feed_ids').value = selectedFeedIds.join(',');
                    });
                    chips.appendChild(el);
                });
                groupEl.appendChild(chips);
                wrap.appendChild(groupEl);
            });
        }

        function renderCategoryOptions() {
            var wrap = document.getElementById('categoryOptions');
            wrap.innerHTML = '';
            allCategories.forEach(function(cat){
                var id = cat.id || cat.category_id;
                if (!id) return;
                var el = document.createElement('span');
                el.className = 'feed-option';
                el.textContent = cat.name || ('分类' + id);
                if (selectedCategoryIds.indexOf(id) >= 0) el.classList.add('selected');
                el.addEventListener('click', function(){
                    var idx = selectedCategoryIds.indexOf(id);
                    if (idx >= 0) { selectedCategoryIds.splice(idx, 1); } else { selectedCategoryIds.push(id); }
                    el.classList.toggle('selected');
                    document.getElementById('category_ids').value = selectedCategoryIds.join(',');
                });
                wrap.appendChild(el);
            });
        }

        function collectFeedIds() {
            var v = document.getElementById('feed_ids').value;
            return v ? v.split(',').map(function(s){ return parseInt(s,10); }).filter(function(n){ return n > 0; }) : [];
        }

        function collectCategoryIds() {
            var v = document.getElementById('category_ids').value;
            return v ? v.split(',').map(function(s){ return parseInt(s,10); }).filter(function(n){ return n > 0; }) : [];
        }

        function submitAndMaybeGenerate(alsoGenerate) {
            var msg = document.getElementById('formMsg');
            msg.textContent = '保存中...';
            var payload = {
                name: document.getElementById('name').value || '默认简报',
                pull_hours: parseInt(document.getElementById('pull_hours').value, 10) || 6,
                schedule_time: document.getElementById('schedule_time').value || '08:00',
                scope: currentScope,
                feed_ids: (currentScope === 'feeds' || currentScope === 'exclude_feeds') ? collectFeedIds() : [],
                category_ids: currentScope === 'by_category' ? collectCategoryIds() : [],
                supplement: document.getElementById('supplement').value || '',
                enabled: document.getElementById('enabled').checked,
            };

            var url = '/api/v2/briefings/configs';
            var options = {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload),
            };

            // 编辑场景：走同样的 POST（服务端按 id 复用）
            if (configId) {
                payload.id = parseInt(configId, 10);
                options.body = JSON.stringify(payload);
            }

            window.taskApiFetch(url, options).then(function(resp){
                return resp.json().then(function(data){ return { ok: resp.ok, data: data }; });
            }).then(function(res){
                if (!res.data || res.data.code !== 9999) {
                    msg.textContent = (res.data && res.data.msg) ? res.data.msg : '保存失败';
                    return;
                }
                var saved = res.data.result.config || {};
                msg.textContent = '保存成功';
                if (alsoGenerate && saved.id) {
                    msg.textContent = '保存成功，生成中...';
                    return window.taskApiFetch('/api/v2/briefings/configs/' + saved.id + '/generate', { method: 'POST' })
                        .then(function(r){ return r.json(); })
                        .then(function(g){
                            if (g.code === 9999 && g.result && g.result.page_id) {
                                window.location.href = '/briefings/' + g.result.page_id;
                            } else {
                                msg.textContent = '生成失败：' + (g.msg || '未知错误');
                            }
                        });
                } else if (saved.id) {
                    setTimeout(function(){ window.location.href = '/briefings'; }, 600);
                }
            }).catch(function(){
                msg.textContent = '保存失败，请重试';
            });
        }

        document.getElementById('briefingConfigForm').addEventListener('submit', function(e){
            e.preventDefault();
            submitAndMaybeGenerate(false);
        });

        document.getElementById('generateBtn').addEventListener('click', function(){
            submitAndMaybeGenerate(true);
        });

        // 编辑：加载已有配置（等 DOM 与 taskApiFetch 就绪）
        document.addEventListener('DOMContentLoaded', function () {
        if (configId) {
            window.taskApiFetch('/api/v2/briefings/configs').then(function(r){ return r.json(); }).then(function(data){
                var configs = (data.result && data.result.configs) ? data.result.configs : [];
                var cfg = null;
                for (var i=0;i<configs.length;i++){ if (String(configs[i].id) === String(configId)) { cfg = configs[i]; break; } }
                if (!cfg) return;
                document.getElementById('name').value = cfg.name || '';
                document.getElementById('pull_hours').value = cfg.pull_hours || 6;
                if (cfg.schedule_time) document.getElementById('schedule_time').value = cfg.schedule_time;
                if (cfg.supplement) document.getElementById('supplement').value = cfg.supplement;
                document.getElementById('enabled').checked = !!cfg.enabled;
                setScope(['feeds','exclude_feeds','by_category'].indexOf(cfg.scope) >= 0 ? cfg.scope : 'all');
                if (cfg.scope === 'feeds' || cfg.scope === 'exclude_feeds') {
                    loadFeeds().then(function(){ selectedFeedIds = (cfg.feed_ids || []).slice(); renderFeedOptions(); document.getElementById('feed_ids').value = selectedFeedIds.join(','); });
                } else if (cfg.scope === 'by_category') {
                    loadFeeds().then(function(){ selectedCategoryIds = (cfg.category_ids || []).slice(); renderCategoryOptions(); document.getElementById('category_ids').value = selectedCategoryIds.join(','); });
                }
            });
        }
        }); // close DOMContentLoaded
    })();
    </script>
@endsection
