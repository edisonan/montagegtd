'use strict';

const fs = require('fs');
const http = require('http');
const path = require('path');

const root = path.resolve(__dirname, '..');
const blade = fs.readFileSync(path.join(root, 'resources/views/llm/index.blade.php'), 'utf8');
const start = blade.indexOf("@section('content')") + "@section('content')".length;
const end = blade.lastIndexOf('@endsection');
const content = blade.slice(start, end).replace(/\{\{[\s\S]*?\}\}/g, '安');

const mock = `
<script>
window.taskApiFetch = async function(url, options) {
    options = options || {};
    var json = function(data, status) { return new Response(JSON.stringify(data), {status: status || 200, headers: {'Content-Type':'application/json'}}); };
    if (url === '/api/v2/llm/agents') return json({success:true,result:{agents:[{id:1,name:'通用智能体',builtin_slug:'builtin_common'},{id:2,name:'深度研究助手'}]}});
    if (url === '/api/v2/llm/attachments' && options.method === 'POST') return json({success:true,data:{id:88,name:'product-brief.md',extension:'md',size:2048,status:'ready'}});
    if (url === '/api/v2/llm/sessions' && (!options.method || options.method === 'GET')) return json({success:true,data:[
        {id:42,title:'设计一个媲美 ChatGPT 的聊天体验',agent_id:1,agent_name:'通用智能体',is_pinned:true,last_message_at:'2026-07-17 13:55:00',updated_at:'2026-07-17 13:55:00'},
        {id:43,title:'设计一个媲美 ChatGPT 的聊天体验 · 分支 1',agent_id:1,agent_name:'通用智能体',parent_session_id:42,branch_order:1,is_pinned:false,last_message_at:'2026-07-17 13:56:00',updated_at:'2026-07-17 13:56:00'},
        {id:41,title:'本周产品计划与优先级',agent_id:2,agent_name:'深度研究助手',is_pinned:false,last_message_at:'2026-07-16 18:20:00',updated_at:'2026-07-16 18:20:00'},
        {id:40,title:'Laravel 流式响应排查',agent_id:1,agent_name:'通用智能体',is_pinned:false,last_message_at:'2026-07-10 09:10:00',updated_at:'2026-07-10 09:10:00'}
    ]});
    if (url === '/api/v2/llm/sessions/42') return json({success:true,data:{id:42,title:'设计一个媲美 ChatGPT 的聊天体验',agent_id:1,agent_name:'通用智能体',is_pinned:true,branch_navigation:[{id:42,title:'设计一个媲美 ChatGPT 的聊天体验',is_original:true,branch_order:0},{id:43,title:'设计一个媲美 ChatGPT 的聊天体验 · 分支 1',is_original:false,branch_order:1}],messages:[
        {role:'user',conversation_id:501,content:'请根据附件，给我一份聊天产品的核心体验清单。',attachments:[{id:91,name:'chat-ux-requirements.md',extension:'md',size:18342,status:'ready'}]},
        {role:'assistant',conversation_id:501,content:'当然。一个真正好用的聊天产品，核心不是堆功能，而是让每次交流都**连续、可信、可恢复**。\\n\\n### 首要能力\\n\\n1. 流式回答与随时停止\\n2. 完整的多轮上下文\\n3. 编辑消息并从任意位置创建新分支\\n4. 代码、表格与 Markdown 的清晰呈现\\n\\n\`\`\`javascript\\nconst experience = { fast: true, calm: true, recoverable: true };\\n\`\`\`'},
        {role:'user',conversation_id:502,content:'界面上应该保持什么样的气质？'},
        {role:'assistant',conversation_id:502,feedback:1,content:'保持安静、克制和高信息密度。让内容成为主角，操作在需要时出现；桌面端充分利用宽度，手机端把会话列表收进抽屉，并始终让输入框触手可及。'}
    ]}});
    return json({success:true,data:{}});
};
var fixtureAttempts = 0;
var fixtureTimer = setInterval(function(){
    fixtureAttempts++;
    var item=document.querySelector('[data-session-id="42"]');
    if(item){
        item.click();
        clearInterval(fixtureTimer);
        setTimeout(function(){
            var ids=['aiWorkbench','conversationView','emptyState','chatView','composer'];
            document.body.dataset.metrics=ids.map(function(id){var el=document.getElementById(id);var r=el&&el.getBoundingClientRect();return id+':'+(r?Math.round(r.left)+','+Math.round(r.width):'none');}).join('|');
            var userRow=document.querySelector('.ai-message.user'),userBody=userRow&&userRow.querySelector('.ai-message-body'),userBubble=userRow&&userRow.querySelector('.ai-bubble');
            document.body.dataset.messageMetrics=[document.documentElement.scrollWidth,window.innerWidth,userRow&&userRow.getBoundingClientRect().width,userBody&&userBody.getBoundingClientRect().width,userBubble&&userBubble.getBoundingClientRect().width,userBubble&&userBubble.scrollWidth].join('|');
            var mode=new URLSearchParams(location.search).get('mode');
            if(mode==='edit'){
                var edit=document.querySelector('.ai-message.user [data-message-action="edit"]');
                if(edit)edit.click();
            }
            if(mode==='drawer'){
                var menu=document.getElementById('openSidebar');
                if(menu)menu.click();
            }
            if(mode==='scroll'){
                var messages=document.getElementById('messages');
                if(messages){messages.scrollTop=0;messages.dispatchEvent(new Event('scroll'));}
            }
            if(mode==='upload'){
                var input=document.getElementById('attachmentInput'),transfer=new DataTransfer();
                transfer.items.add(new File(['# Product brief'], 'product-brief.md', {type:'text/markdown'}));
                input.files=transfer.files;input.dispatchEvent(new Event('change'));
            }
        },300);
    }
    if(fixtureAttempts>50)clearInterval(fixtureTimer);
},100);
</script>`;

const html = `<!doctype html><html><head><meta charset="utf-8"><meta name="csrf-token" content="fixture"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/css/app.css"><style>html,body{margin:0;height:100%;background:#fff}.navbar,.footer{display:none!important}</style></head><body>${mock}${content}</body></html>`;

const server = http.createServer((request, response) => {
    if (request.url === '/' || request.url.startsWith('/fixture')) {
        response.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
        response.end(html);
        return;
    }
    const cleanPath = decodeURIComponent(request.url.split('?')[0]).replace(/^\/+/, '');
    const file = path.resolve(root, 'public', cleanPath);
    if (!file.startsWith(path.resolve(root, 'public')) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) {
        response.writeHead(404); response.end('not found'); return;
    }
    const extensions = {'.js':'application/javascript','.css':'text/css','.woff':'font/woff','.woff2':'font/woff2','.ttf':'font/ttf'};
    response.writeHead(200, {'Content-Type': extensions[path.extname(file)] || 'application/octet-stream'});
    fs.createReadStream(file).pipe(response);
});

server.listen(4178, '127.0.0.1', () => process.stdout.write('fixture ready http://127.0.0.1:4178\n'));
