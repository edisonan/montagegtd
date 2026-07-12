@extends('layouts.app')

@section('title', '探索版思维导图 - 蒙太奇')
@section('description', '以更清晰、沉浸的方式探索思维导图')

@section('content')
<style>
    :root { --explore-ink: #172033; --explore-muted: #8791a5; --explore-line: #dbe2ec; --explore-blue: #1677ff; }
    .mind-explore-page { min-height: calc(100vh - 64px); padding: 28px 24px 36px; background: #f7f9fc; color: var(--explore-ink); }
    .mind-explore-shell { max-width: 1480px; margin: 0 auto; }
    .mind-explore-head { display:flex; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:22px; }
    .mind-explore-eyebrow { display:flex; align-items:center; gap:8px; color:var(--explore-blue); font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
    .mind-explore-title { margin:7px 0 4px; font-size:clamp(25px, 3vw, 38px); line-height:1.12; font-weight:700; letter-spacing:-.035em; }
    .mind-explore-subtitle { margin:0; color:var(--explore-muted); font-size:14px; }
    .mind-explore-actions { display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
    .mind-explore-segment { display:flex; padding:4px; border:1px solid #e1e6ef; border-radius:12px; background:#fff; box-shadow:0 2px 10px rgba(26,40,68,.04); }
    .mind-explore-segment a { padding:8px 12px; border-radius:8px; color:#7c879b; font-size:13px; text-decoration:none; }
    .mind-explore-segment a.active { background:#eef5ff; color:var(--explore-blue); font-weight:600; }
    .mind-explore-btn { border:1px solid #e1e6ef; border-radius:10px; padding:9px 13px; background:#fff; color:#536078; cursor:pointer; font-size:13px; box-shadow:0 2px 10px rgba(26,40,68,.04); transition:.2s ease; }
    .mind-explore-btn:hover { border-color:#b9c9e4; color:var(--explore-blue); transform:translateY(-1px); }
    .mind-explore-card { position:relative; overflow:hidden; min-height:650px; border:1px solid #e5eaf2; border-radius:24px; background:rgba(255,255,255,.88); box-shadow:0 18px 55px rgba(38,55,84,.09); }
    .mind-explore-card:before { content:""; position:absolute; width:420px; height:420px; right:-160px; top:-220px; border-radius:50%; background:#eaf3ff; pointer-events:none; }
    .mind-explore-meta { position:absolute; z-index:2; top:20px; left:24px; display:flex; gap:8px; }
    .mind-explore-pill { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; background:rgba(247,250,255,.86); color:#8590a3; font-size:12px; backdrop-filter:blur(10px); }
    .mind-explore-canvas { position:absolute; inset:0; overflow:hidden; cursor:grab; }
    .mind-explore-canvas.is-panning { cursor:grabbing; }
    .mind-explore-svg { width:100%; height:100%; min-width:100%; min-height:650px; }
    .mind-explore-node { cursor:pointer; }
    .mind-explore-node rect { fill:#fff; stroke:#e1e7f0; stroke-width:1.2; filter:drop-shadow(0 5px 10px rgba(38,55,84,.07)); transition:.2s ease; }
    .mind-explore-node:hover rect, .mind-explore-node.selected rect { stroke:#8bbcff; filter:drop-shadow(0 8px 17px rgba(22,119,255,.18)); }
    .mind-explore-node.selected rect { fill:#f7fbff; stroke-width:1.8; }
    .mind-explore-node.root rect { fill:#172033; stroke:#172033; }
    .mind-explore-node .node-title { fill:#202b40; font:600 16px -apple-system,BlinkMacSystemFont,"SF Pro Display","PingFang SC",sans-serif; }
    .mind-explore-node.root .node-title { fill:#fff; font-size:20px; }
    .mind-explore-node .node-index { fill:#a3afc2; font:600 10px -apple-system,BlinkMacSystemFont,"SF Pro Text",sans-serif; }
    .mind-explore-node.root .node-index { fill:#9fb4d5; }
    .mind-explore-node .node-dot { fill:#65a8ff; }
    .mind-explore-node.root .node-dot { fill:#6ee7cf; }
    .mind-explore-link { fill:none; stroke:var(--explore-line); stroke-width:1.7; stroke-linecap:round; }
    .mind-explore-controls { position:absolute; z-index:3; right:20px; bottom:20px; display:flex; gap:6px; padding:5px; border:1px solid #e4e9f1; border-radius:12px; background:rgba(255,255,255,.82); box-shadow:0 5px 18px rgba(38,55,84,.08); backdrop-filter:blur(12px); }
    .mind-explore-controls button { width:32px; height:32px; border:0; border-radius:8px; color:#64728b; background:transparent; cursor:pointer; }
    .mind-explore-controls button:hover { background:#eef5ff; color:var(--explore-blue); }
    .mind-explore-inspector { position:absolute; z-index:4; top:20px; right:20px; width:252px; padding:18px; border:1px solid rgba(225,231,240,.9); border-radius:16px; background:rgba(255,255,255,.84); box-shadow:0 12px 32px rgba(38,55,84,.1); backdrop-filter:blur(18px); }
    .mind-explore-inspector small { color:#99a4b7; font-size:11px; }
    .mind-explore-inspector h3 { margin:5px 0 9px; font-size:16px; line-height:1.35; }
    .mind-explore-inspector p { max-height:120px; overflow:auto; margin:0; color:#748097; font-size:12px; line-height:1.7; white-space:pre-wrap; }
    .mind-explore-empty { display:flex; height:650px; align-items:center; justify-content:center; color:#8b96aa; font-size:14px; }
    @media(max-width:768px) { .mind-explore-page{padding:20px 14px}.mind-explore-head{display:block}.mind-explore-actions{margin-top:16px}.mind-explore-card,.mind-explore-svg,.mind-explore-empty{min-height:560px}.mind-explore-inspector{top:auto; right:12px; bottom:70px; left:12px; width:auto;}.mind-explore-meta{top:14px;left:14px} }
</style>

<div class="mind-explore-page">
    <div class="mind-explore-shell">
        <div class="mind-explore-head">
            <div>
                <div class="mind-explore-eyebrow"><i class="fas fa-sparkles"></i> Explore view</div>
                <h1 class="mind-explore-title" id="explore_title">思维导图</h1>
                <p class="mind-explore-subtitle">把复杂关系留在画布上，把注意力留给正在探索的想法。</p>
            </div>
            <div class="mind-explore-actions">
                <div class="mind-explore-segment"><a href="#" id="explore_edit_link">编辑版</a><a href="#" class="active">探索版</a></div>
                <button class="mind-explore-btn" id="explore_fit_btn"><i class="fas fa-crosshairs mr-1"></i> 适应画布</button>
                <a href="{{ url('/minds') }}" class="mind-explore-btn"><i class="fas fa-arrow-left mr-1"></i> 返回</a>
            </div>
        </div>
        <div class="mind-explore-card">
            <div class="mind-explore-meta"><span class="mind-explore-pill"><i class="fas fa-circle-nodes"></i><span id="explore_count">0</span> 个节点</span><span class="mind-explore-pill" id="explore_status">正在加载</span></div>
            <div class="mind-explore-canvas" id="explore_canvas"><div class="mind-explore-empty" id="explore_loading">正在整理思维脉络…</div></div>
            <div class="mind-explore-inspector" id="explore_inspector"><small>当前焦点</small><h3 id="explore_node_title">选择一个节点</h3><p id="explore_node_content">点击节点查看备注，拖动画布可以自由浏览。</p></div>
            <div class="mind-explore-controls"><button id="explore_zoom_out" title="缩小"><i class="fas fa-minus"></i></button><button id="explore_zoom_reset" title="重置"><i class="fas fa-expand"></i></button><button id="explore_zoom_in" title="放大"><i class="fas fa-plus"></i></button></div>
        </div>
    </div>
</div>

<script>
(function () {
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function' ? window.TaskApiBridge.requestWithFallback : null;
    var state = { id: '', tree: null, nodes: [], scale: 1, tx: 0, ty: 0, svg: null, dragging: false, startX: 0, startY: 0, startTx: 0, startTy: 0 };
    function resultOf(r) { return r && (r.result || r.data) || {}; }
    function escapeHtml(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }
    function resolveId() { var p = location.pathname.split('/').filter(Boolean); var mindIndex = p.indexOf('mind'); return mindIndex >= 0 && p[mindIndex + 1] ? p[mindIndex + 1] : ''; }
    function flatten(node, depth, parent, out) { if (!node) return; node.depth = depth; node.parent = parent; out.push(node); (node.children || []).forEach(function (child) { flatten(child, depth + 1, node, out); }); }
    function textLines(text, max) { var s = String(text || '未命名节点'), lines = []; while (s.length > max) { lines.push(s.slice(0, max)); s = s.slice(max); } lines.push(s); return lines.slice(0, 2); }
    function layout(tree) {
        var nodes = [], leaves = 0, maxDepth = 0;
        flatten(tree, 0, null, nodes); nodes.forEach(function(n){ if (!(n.children || []).length) leaves++; maxDepth=Math.max(maxDepth,n.depth); });
        var gap = 60, width = Math.max(960, (leaves + 1) * gap + 220), height = Math.max(560, leaves * 60 + 90), cursor = 0;
        function place(n) { var children=n.children||[]; if (!children.length) { n.x=140+n.depth*220; n.y=60+cursor*60; cursor++; return; } children.forEach(place); n.x=140+n.depth*220; n.y=(children[0].y+children[children.length-1].y)/2; }
        place(tree); var top=tree.y-height/2+height/2; nodes.forEach(function(n){n.y += 28;}); return {nodes:nodes,width:Math.max(width, (maxDepth+1)*220+240),height:Math.max(height, cursor*60+76)};
    }
    function render(tree) {
        var l=layout(tree), html='<svg class="mind-explore-svg" viewBox="0 0 '+l.width+' '+l.height+'" preserveAspectRatio="xMidYMid meet"><g id="explore_transform">';
        l.nodes.forEach(function(n){(n.children||[]).forEach(function(c){html+='<path class="mind-explore-link" d="M '+(n.x+154)+' '+n.y+' C '+(n.x+210)+' '+n.y+', '+(c.x-56)+' '+c.y+', '+(c.x-10)+' '+c.y+'"/>';});});
        l.nodes.forEach(function(n){var lines=textLines(n.topic, n.depth===0?16:21), h=lines.length>1?74:62, w=n.depth===0?224:202, cls='mind-explore-node '+(n.depth===0?'root ':'')+'node-'+n.id; html+='<g class="'+cls+'" data-node-id="'+escapeHtml(n.id)+'" transform="translate('+(n.x-w/2)+' '+(n.y-h/2)+')"><rect width="'+w+'" height="'+h+'" rx="'+(n.depth===0?17:13)+'"></rect><circle class="node-dot" cx="18" cy="'+(h/2)+'" r="4"></circle><text class="node-index" x="30" y="18">'+(n.depth===0?'FOCUS':'0'+n.depth)+'</text>'; lines.forEach(function(line,i){html+='<text class="node-title" x="30" y="'+(i?49:40)+'">'+escapeHtml(line)+'</text>';}); html+='</g>';}); html+='</g></svg>'; $('#explore_canvas').html(html); state.nodes=l.nodes; state.svg=document.getElementById('explore_transform'); $('#explore_count').text(l.nodes.length); $('#explore_status').text('已准备好探索'); bindNodes(); fit(); }
    function bindNodes(){ document.querySelectorAll('.mind-explore-node').forEach(function(el){el.addEventListener('click',function(e){e.stopPropagation(); document.querySelectorAll('.mind-explore-node').forEach(function(n){n.classList.remove('selected')}); el.classList.add('selected'); var n=state.nodes.find(function(x){return String(x.id)===String(el.getAttribute('data-node-id'))}); if(n){$('#explore_node_title').text(n.topic||'未命名节点'); $('#explore_node_content').text(n.content||'这个节点还没有备注。');}});}); }
    function applyTransform(){ if(state.svg) state.svg.setAttribute('transform','translate('+state.tx+' '+state.ty+') scale('+state.scale+')'); }
    function fit(){ if(!state.svg)return; state.scale=1; state.tx=0; state.ty=0; applyTransform(); }
    function load(){ state.id=resolveId(); if(!apiRequest||!state.id){$('#explore_loading').text('无法加载思维导图');return;} apiRequest('GET','/minds/'+state.id,{}).then(function(r){var m=resultOf(r).mind||{}; $('#explore_title').text(m.name||'思维导图'); document.title=(m.name||'思维导图')+' - 探索版思维导图 - 蒙太奇'; $('#explore_edit_link').attr('href','/mind/'+state.id); return apiRequest('GET','/minds/'+state.id+'/jsmind',{});}).then(function(r){var raw=resultOf(r).jsmind_datas; if(!raw)throw new Error('empty'); render(JSON.parse(raw).data);}).catch(function(){ $('#explore_loading').text('导图加载失败，请返回重试'); $('#explore_status').text('加载失败'); }); }
    $('#explore_zoom_in').on('click',function(){state.scale=Math.min(1.8,state.scale+.12);applyTransform();}); $('#explore_zoom_out').on('click',function(){state.scale=Math.max(.55,state.scale-.12);applyTransform();}); $('#explore_zoom_reset,#explore_fit_btn').on('click',fit);
    $('#explore_canvas').on('mousedown',function(e){ if(e.target.closest('.mind-explore-node'))return; state.dragging=true; state.startX=e.clientX;state.startY=e.clientY;state.startTx=state.tx;state.startTy=state.ty;$(this).addClass('is-panning'); }).on('mousemove',function(e){if(!state.dragging)return;state.tx=state.startTx+e.clientX-state.startX;state.ty=state.startTy+e.clientY-state.startY;applyTransform();}).on('mouseup mouseleave',function(){state.dragging=false;$(this).removeClass('is-panning');});
    $(load);
}());
</script>
@endsection
