@extends('layouts.app')

@section('title', '网页转RSS - 蒙太奇')
@section('description', '把任意列表页面配置为RSS订阅源，支持列表页和详情页二次提取')

@section('content')
    <div class="max-w-7xl mx-auto" id="webpageRssApp">
        <style>
            .rss-shell { --panel-border: #dbe2ea; --panel-bg: #ffffff; --soft-bg: #f8fafc; }
            .rss-panel { background: var(--panel-bg); border: 1px solid var(--panel-border); border-radius: 12px; }
            .rss-panel-head { padding: 16px 18px; border-bottom: 1px solid var(--panel-border); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
            .rss-panel-body { padding: 18px; }
            .rss-chip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--panel-border); background: #fff; border-radius: 999px; padding: 6px 10px; font-size: 12px; color: #475569; }
            .rss-chip.active { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
            .rss-step { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; }
            .rss-step span { width: 22px; height: 22px; border-radius: 999px; background: #e2e8f0; color: #334155; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
            .rss-mini-btn { border: 1px solid var(--panel-border); background: #fff; border-radius: 10px; padding: 10px 12px; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: #0f172a; }
            .rss-mini-btn:hover { border-color: #93c5fd; background: #eff6ff; }
            .rss-mini-btn.active { border-color: #60a5fa; background: #eff6ff; color: #1d4ed8; }
            .rss-tab { border: 1px solid var(--panel-border); background: #fff; color: #475569; padding: 8px 12px; border-radius: 10px; font-size: 13px; }
            .rss-tab.active { background: #f1f5f9; color: #0f172a; border-color: #94a3b8; }
            .rss-split { display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; }
            @media (max-width: 1024px) { .rss-split { grid-template-columns: 1fr; } }
        </style>

        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">网页转RSS</h1>
                        <p class="text-gray-600 mt-1">先调试规则，再保存配置。调试不需要分类，保存时才需要。</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <div class="rss-step"><span>1</span>选模板</div>
                    <div class="rss-step"><span>2</span>调规则</div>
                    <div class="rss-step"><span>3</span>看结果</div>
                    <div class="rss-step"><span>4</span>保存并生成 RSS</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url('feeds') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-2"></i>返回订阅
                </a>
                <a href="{{ url('articles') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-newspaper mr-2"></i>阅读流
                </a>
            </div>
        </div>

        <div id="webpageRssTips" class="hidden mb-5 rounded-lg border p-4 text-sm"></div>

        <div class="rss-split">
            <form id="webpageRssForm" action="javascript:void(0)" class="space-y-5">
                <div class="rss-panel">
                    <div class="rss-panel-head">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">模板和来源</div>
                            <div class="text-xs text-gray-500 mt-1">先点一个模板，能少填很多字段。</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="rss-mini-btn" id="presetGenericBtn"><i class="fas fa-layer-group"></i><span>通用列表</span></button>
                            <button type="button" class="rss-mini-btn" id="presetCardBtn"><i class="fas fa-table-cells"></i><span>卡片流</span></button>
                            <button type="button" class="rss-mini-btn" id="presetDetailBtn"><i class="fas fa-file-lines"></i><span>详情页</span></button>
                            <button type="button" class="rss-mini-btn" id="presetBlogBtn"><i class="fas fa-newspaper"></i><span>博客示例</span></button>
                        </div>
                    </div>
                    <div class="rss-panel-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">订阅名称</label>
                                <input id="name" name="name" type="text" class="input w-full" placeholder="例如：某产品博客 / 招聘动态 / 作者主页" required>
                            </div>
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">所属分类</label>
                                <select id="category_id" name="category_id" class="input w-full" required>
                                    <option value="">正在读取分类...</option>
                                </select>
                                <p class="mt-2 text-xs text-gray-500">调试时不需要选分类，保存配置时才会使用。</p>
                            </div>
                        </div>

                        <div>
                            <label for="list_url" class="block text-sm font-medium text-gray-700 mb-2">列表页地址</label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input id="list_url" name="list_url" type="url" class="input flex-1" placeholder="https://example.com/news" required>
                                <button type="button" class="btn btn-outline whitespace-nowrap" id="debugTopBtn">
                                    <i class="fas fa-eye mr-2"></i>读取预览
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="refresh_interval" class="block text-sm font-medium text-gray-700 mb-2">抓取频率</label>
                                <select id="refresh_interval" name="refresh_interval" class="input w-full">
                                    <option value="30">每30分钟</option>
                                    <option value="60" selected>每小时</option>
                                    <option value="180">每3小时</option>
                                    <option value="1440">每天</option>
                                </select>
                            </div>
                            <div>
                                <label for="encoding" class="block text-sm font-medium text-gray-700 mb-2">编码</label>
                                <select id="encoding" name="encoding" class="input w-full">
                                    <option value="auto" selected>自动识别</option>
                                    <option value="UTF-8">UTF-8</option>
                                    <option value="GBK">GBK/GB2312</option>
                                </select>
                            </div>
                            <div>
                                <label for="dedupe_key" class="block text-sm font-medium text-gray-700 mb-2">去重依据</label>
                                <select id="dedupe_key" name="dedupe_key" class="input w-full">
                                    <option value="url" selected>详情URL</option>
                                    <option value="title_published">标题 + 发布时间</option>
                                    <option value="title">标题</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rss-panel">
                    <div class="rss-panel-head">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">提取规则</div>
                            <div class="text-xs text-gray-500 mt-1">只要列表页能定位到条目，后面字段就能逐个补齐。</div>
                        </div>
                        <button type="button" class="rss-mini-btn" id="debugRuleBtn">
                            <i class="fas fa-vial"></i><span>测试规则</span>
                        </button>
                    </div>
                    <div class="rss-panel-body space-y-4">
                        <div>
                            <label for="item_selector" class="block text-sm font-medium text-gray-700 mb-2">列表项选择器</label>
                            <input id="item_selector" name="item_selector" type="text" class="input w-full font-mono text-sm" value=".post-item, article, .news-list li" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title_selector" class="block text-sm font-medium text-gray-700 mb-2">标题选择器</label>
                                <input id="title_selector" name="title_selector" type="text" class="input w-full font-mono text-sm" value="h2 a, .title a" required>
                            </div>
                            <div>
                                <label for="url_selector" class="block text-sm font-medium text-gray-700 mb-2">详情URL选择器</label>
                                <input id="url_selector" name="url_selector" type="text" class="input w-full font-mono text-sm" value="a@href" required>
                            </div>
                            <div>
                                <label for="published_selector" class="block text-sm font-medium text-gray-700 mb-2">发布时间选择器</label>
                                <input id="published_selector" name="published_selector" type="text" class="input w-full font-mono text-sm" value="time@datetime, .date">
                            </div>
                            <div>
                                <label for="image_selector" class="block text-sm font-medium text-gray-700 mb-2">封面图选择器</label>
                                <input id="image_selector" name="image_selector" type="text" class="input w-full font-mono text-sm" value="img@src">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-800">
                                    <input type="radio" name="summary_source" value="list" checked class="text-blue-600">
                                    从列表页提取摘要
                                </label>
                                <input id="summary_selector" name="summary_selector" type="text" class="input w-full font-mono text-sm mt-3" value=".summary, .excerpt, p">
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-800">
                                    <input type="radio" name="summary_source" value="detail" class="text-blue-600">
                                    从详情页提取摘要
                                </label>
                                <input id="detail_summary_selector" name="detail_summary_selector" type="text" class="input w-full font-mono text-sm mt-3" value=".article-content p">
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                            <div>
                                <div class="font-medium text-gray-900">启用详情页抓取</div>
                                <div class="text-sm text-gray-500 mt-1">用于正文、摘要和作者二次提取。</div>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input id="detail_enabled" name="detail_enabled" type="checkbox" value="1" class="sr-only peer" checked>
                                <span class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:w-5 after:h-5 after:rounded-full after:transition-all peer-checked:after:translate-x-5"></span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="content_selector" class="block text-sm font-medium text-gray-700 mb-2">正文选择器</label>
                                <textarea id="content_selector" name="content_selector" class="input w-full font-mono text-sm min-h-[96px]">.article-content, main article, #content</textarea>
                            </div>
                            <div>
                                <label for="exclude_selector" class="block text-sm font-medium text-gray-700 mb-2">排除选择器</label>
                                <textarea id="exclude_selector" name="exclude_selector" class="input w-full font-mono text-sm min-h-[96px]">.ad, .share, script, style, nav, footer</textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="author_selector" class="block text-sm font-medium text-gray-700 mb-2">作者</label>
                                <input id="author_selector" name="author_selector" type="text" class="input w-full font-mono text-sm" value=".author, [rel=author]">
                            </div>
                            <div>
                                <label for="max_content_length" class="block text-sm font-medium text-gray-700 mb-2">正文最大长度</label>
                                <input id="max_content_length" name="max_content_length" type="number" class="input w-full" value="12000">
                            </div>
                            <div>
                                <label for="failure_strategy" class="block text-sm font-medium text-gray-700 mb-2">失败策略</label>
                                <select id="failure_strategy" name="failure_strategy" class="input w-full">
                                    <option value="fallback" selected>回退到列表页摘要</option>
                                    <option value="skip">跳过该条</option>
                                    <option value="title_only">仅保留标题链接</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="button" class="btn btn-outline flex-1 justify-center" id="debugBtn">
                                <i class="fas fa-vial mr-2"></i>调试当前规则
                            </button>
                            <button type="button" class="btn btn-primary flex-1 justify-center" id="saveBtn">
                                <i class="fas fa-floppy-disk mr-2"></i>保存配置并生成 RSS
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <aside class="space-y-5">
                <div class="rss-panel sticky top-24">
                    <div class="rss-panel-head">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">AI 解析</div>
                            <div class="text-xs text-gray-500 mt-1">先选模型，再点生成规则。</div>
                        </div>
                        <a href="{{ url('llm/llmmanagement') }}" class="rss-tab active" target="_blank" rel="noreferrer">模型管理</a>
                    </div>
                    <div class="rss-panel-body space-y-4">
                        <div class="space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div>
                                <label for="ai_mode" class="block text-sm font-medium text-gray-700 mb-2">解析模式</label>
                                <select id="ai_mode" class="input w-full">
                                    <option value="list_summary" selected>列表摘要优先</option>
                                    <option value="balanced">稳妥平衡</option>
                                    <option value="detail_content">详情正文优先</option>
                                </select>
                            </div>
                            <div>
                                <label for="ai_model_id" class="block text-sm font-medium text-gray-700 mb-2">选择模型</label>
                                <select id="ai_model_id" class="input w-full">
                                    <option value="">正在加载模型...</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-outline flex-1 justify-center" id="aiAnalyzeBtn">
                                    <i class="fas fa-wand-magic-sparkles mr-2"></i>AI 生成规则
                                </button>
                            </div>
                            <div id="aiModelHint" class="text-xs text-gray-500">
                                需要先在模型管理里配置可用模型和凭据。
                            </div>
                        </div>

                        <div id="aiResultBox" class="hidden rounded-lg border border-blue-200 bg-blue-50 p-4 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-blue-900">AI 建议结果</div>
                                    <div class="text-xs text-blue-700" id="aiResultMeta"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" id="applyAiBtn">
                                    <i class="fas fa-arrow-down mr-1"></i>应用到表单
                                </button>
                            </div>
                            <div class="space-y-2 text-sm text-blue-900">
                                <div><span class="text-blue-700">置信度：</span><span id="aiConfidence">-</span></div>
                                <div><span class="text-blue-700">理由：</span><span id="aiReason">-</span></div>
                                <div><span class="text-blue-700">备注：</span><span id="aiNotes">-</span></div>
                            </div>
                            <pre id="aiResultJson" class="max-h-72 overflow-auto rounded-lg border border-blue-100 bg-white p-3 text-xs leading-5 whitespace-pre-wrap"></pre>
                        </div>

                        <div class="grid grid-cols-3 gap-2" id="debugStats">
                            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">匹配</div>
                                <div class="text-lg font-semibold text-gray-900" id="matchedCount">-</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">有效</div>
                                <div class="text-lg font-semibold text-gray-900" id="validCount">-</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">耗时</div>
                                <div class="text-lg font-semibold text-gray-900" id="elapsedMs">-</div>
                            </div>
                        </div>

                        <div id="rssUrlBox" class="hidden rounded-lg border border-green-200 bg-green-50 p-4">
                            <div class="text-sm font-semibold text-green-800 mb-2">RSS订阅地址</div>
                            <div class="flex gap-2">
                                <input id="rssUrlText" type="text" class="input flex-1 text-xs" readonly>
                                <button type="button" id="copyRssBtn" class="btn btn-sm btn-outline">复制</button>
                            </div>
                        </div>

                        <div id="previewList" class="space-y-2">
                            <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-500">
                                先点“读取预览”。
                            </div>
                        </div>

                        <div id="selectedDebug" class="hidden rounded-lg border border-gray-200 bg-white p-4 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900" id="selectedDebugTitle">条目详情</div>
                                    <div class="text-xs text-gray-500" id="selectedDebugMeta"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" id="copyDebugBtn">
                                    <i class="fas fa-copy mr-1"></i>复制JSON
                                </button>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rss-tab active" data-detail-tab="trace">字段轨迹</button>
                                <button type="button" class="rss-tab" data-detail-tab="html">原始 HTML</button>
                                <button type="button" class="rss-tab" data-detail-tab="detail">详情抓取</button>
                            </div>

                            <div data-detail-panel="trace">
                                <div class="overflow-hidden rounded-lg border border-gray-200">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 text-gray-500">
                                            <tr>
                                                <th class="px-3 py-2 text-left">字段</th>
                                                <th class="px-3 py-2 text-left">选择器</th>
                                                <th class="px-3 py-2 text-left">属性</th>
                                                <th class="px-3 py-2 text-left">值</th>
                                            </tr>
                                        </thead>
                                        <tbody id="selectedTraceBody" class="divide-y divide-gray-200 bg-white"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div data-detail-panel="html" class="hidden">
                                <pre id="selectedHtml" class="max-h-72 overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-5 whitespace-pre-wrap"></pre>
                            </div>
                            <div data-detail-panel="detail" class="hidden">
                                <pre id="selectedDetail" class="max-h-72 overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-5 whitespace-pre-wrap"></pre>
                            </div>
                        </div>

                        <div id="errorList" class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script>
        (function() {
            var form = document.getElementById('webpageRssForm');
            var tips = document.getElementById('webpageRssTips');
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var lastDebugResult = null;
            var lastAiResult = null;
            var selectedItem = null;

            var presets = {
                generic: {
                    item_selector: '.post-item, article, .news-list li',
                    title_selector: 'h2 a, .title a',
                    url_selector: 'a@href',
                    published_selector: 'time@datetime, .date',
                    image_selector: 'img@src',
                    summary_source: 'list',
                    summary_selector: '.summary, .excerpt, p',
                    detail_enabled: 1,
                    detail_summary_selector: '.article-content p',
                    content_selector: '.article-content, main article, #content',
                    exclude_selector: '.ad, .share, script, style, nav, footer',
                    author_selector: '.author, [rel=author]',
                },
                card: {
                    item_selector: '.card, .post-card, li',
                    title_selector: '.title, h3, h2',
                    url_selector: 'a@href',
                    published_selector: '.date, time@datetime',
                    image_selector: 'img@src',
                    summary_source: 'list',
                    summary_selector: '.summary, .desc, p',
                    detail_enabled: 0,
                    detail_summary_selector: '',
                    content_selector: '.content, article',
                    exclude_selector: '.ad, .share, script, style',
                    author_selector: '.author',
                },
                detail: {
                    item_selector: 'article, .article, .post',
                    title_selector: 'h1, h2, .title',
                    url_selector: 'a@href',
                    published_selector: 'time@datetime, .date',
                    image_selector: 'img@src',
                    summary_source: 'detail',
                    summary_selector: '',
                    detail_enabled: 1,
                    detail_summary_selector: '.article-content p',
                    content_selector: '.article-content, main article, #content',
                    exclude_selector: '.ad, .share, script, style, nav, footer',
                    author_selector: '.author, [rel=author]',
                },
                blog: {
                    list_url: 'https://claude.com/blog',
                    item_selector: 'div.marquee_cms_blog_list_item',
                    title_selector: 'h2, h3',
                    url_selector: 'a@href',
                    published_selector: '.u-text-style-caption, time@datetime, time',
                    image_selector: 'img@src',
                    summary_source: 'detail',
                    summary_selector: '',
                    detail_enabled: 1,
                    detail_summary_selector: '.hero_blog_description_wrap p, .u-rich-text-blog p',
                    content_selector: '.u-rich-text-blog',
                    exclude_selector: 'nav, footer, script, style, .share, .subscribe, .newsletter, .faq_section_wrap, .nav_wrap',
                    author_selector: '.hero_blog_post_details_item a[rel=author], .author',
                }
            };

            function showTips(type, message) {
                tips.className = 'mb-5 rounded-lg border p-4 text-sm ' + (type === 'error'
                    ? 'border-red-200 bg-red-50 text-red-700'
                    : 'border-green-200 bg-green-50 text-green-700');
                tips.textContent = message;
                tips.classList.remove('hidden');
            }

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function(ch) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[ch];
                });
            }

            function collectRuleData() {
                return {
                    list_url: document.getElementById('list_url').value || '',
                    item_selector: document.getElementById('item_selector').value || '',
                    title_selector: document.getElementById('title_selector').value || '',
                    url_selector: document.getElementById('url_selector').value || '',
                    published_selector: document.getElementById('published_selector').value || '',
                    image_selector: document.getElementById('image_selector').value || '',
                    summary_source: document.querySelector('input[name="summary_source"]:checked')
                        ? document.querySelector('input[name="summary_source"]:checked').value
                        : 'list',
                    summary_selector: document.getElementById('summary_selector').value || '',
                    detail_enabled: document.getElementById('detail_enabled').checked ? 1 : 0,
                    detail_summary_selector: document.getElementById('detail_summary_selector').value || '',
                    content_selector: document.getElementById('content_selector').value || '',
                    exclude_selector: document.getElementById('exclude_selector').value || '',
                    author_selector: document.getElementById('author_selector').value || '',
                    max_content_length: document.getElementById('max_content_length').value || '',
                    failure_strategy: document.getElementById('failure_strategy').value || 'fallback',
                    refresh_interval: document.getElementById('refresh_interval').value || '60',
                    dedupe_key: document.getElementById('dedupe_key').value || 'url',
                    encoding: document.getElementById('encoding').value || 'auto'
                };
            }

            function collectDebugData() {
                return collectRuleData();
            }

            function collectSaveData() {
                var data = collectRuleData();
                data.name = document.getElementById('name').value || '';
                data.category_id = document.getElementById('category_id').value || '';
                return data;
            }

            function request(url, data) {
                return fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                }).then(function(resp) {
                    return resp.json();
                }).then(function(json) {
                    if (!json || Number(json.code) !== 9999) {
                        throw new Error(json && json.msg ? json.msg : '请求失败');
                    }
                    return json.result || {};
                });
            }

            function setLoading(button, loading) {
                button.disabled = loading;
                button.classList.toggle('opacity-60', loading);
            }

            function setFieldValue(id, value) {
                var node = document.getElementById(id);
                if (!node || value === undefined || value === null) {
                    return;
                }
                if (node.type === 'checkbox') {
                    node.checked = !!value && value !== '0';
                    return;
                }
                node.value = value;
            }

            function fillPreset(key) {
                var preset = presets[key];
                if (!preset) {
                    return;
                }
                Object.keys(preset).forEach(function(name) {
                    var node = document.getElementById(name);
                    if (!node) {
                        return;
                    }
                    if (node.type === 'checkbox') {
                        node.checked = !!preset[name];
                    } else {
                        node.value = preset[name];
                    }
                });
                document.querySelectorAll('input[name="summary_source"]').forEach(function(input) {
                    input.checked = input.value === preset.summary_source;
                });
                document.querySelectorAll('#presetGenericBtn, #presetCardBtn, #presetDetailBtn, #presetBlogBtn').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                var activeMap = {
                    generic: 'presetGenericBtn',
                    card: 'presetCardBtn',
                    detail: 'presetDetailBtn',
                    blog: 'presetBlogBtn'
                };
                document.getElementById(activeMap[key]).classList.add('active');
                if (key === 'blog') {
                    document.getElementById('ai_mode').value = 'list_summary';
                }
                showTips('success', '已套用模板，先补列表页地址，再点“读取预览”。');
            }

            function renderTraceRow(label, trace) {
                trace = trace || {};
                return '<tr>'
                    + '<td class="px-3 py-2 font-medium text-gray-900">' + escapeHtml(label) + '</td>'
                    + '<td class="px-3 py-2 text-gray-600 font-mono text-xs break-all">' + escapeHtml(trace.selector || '') + '</td>'
                    + '<td class="px-3 py-2 text-gray-600 font-mono text-xs">' + escapeHtml(trace.attribute || '') + '</td>'
                    + '<td class="px-3 py-2 text-gray-700"><div class="text-xs text-gray-500">' + escapeHtml(trace.matched_selector || '') + '</div><div class="mt-1 break-all">' + escapeHtml(trace.value || '') + '</div></td>'
                    + '</tr>';
            }

            function switchDetailTab(tab) {
                document.querySelectorAll('[data-detail-tab]').forEach(function(btn) {
                    btn.classList.toggle('active', btn.getAttribute('data-detail-tab') === tab);
                });
                document.querySelectorAll('[data-detail-panel]').forEach(function(panel) {
                    panel.classList.toggle('hidden', panel.getAttribute('data-detail-panel') !== tab);
                });
            }

            function renderSelected(item, index) {
                if (!item) {
                    document.getElementById('selectedDebug').classList.add('hidden');
                    return;
                }
                selectedItem = item;
                var panel = document.getElementById('selectedDebug');
                panel.classList.remove('hidden');
                document.getElementById('selectedDebugTitle').textContent = '条目 #' + (Number(index) + 1) + ' · ' + (item.subject || '未命名');
                document.getElementById('selectedDebugMeta').textContent = (item.url || '') + ' · ' + (item.detail_status || '列表');

                var fields = (item.debug && item.debug.fields) ? item.debug.fields : {};
                var detailFields = (item.debug && item.debug.detail_fields) ? item.debug.detail_fields : {};
                var traceRows = [];
                ['title', 'url', 'published', 'image', 'summary'].forEach(function(key) {
                    if (fields[key]) {
                        traceRows.push(renderTraceRow(key, fields[key]));
                    }
                });
                if (Object.keys(detailFields).length) {
                    traceRows.push('<tr><td colspan="4" class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-500">详情页提取</td></tr>');
                    ['summary', 'content', 'author'].forEach(function(key) {
                        if (detailFields[key]) {
                            traceRows.push(renderTraceRow(key, detailFields[key]));
                        }
                    });
                }
                document.getElementById('selectedTraceBody').innerHTML = traceRows.join('') || '<tr><td colspan="4" class="px-3 py-4 text-sm text-gray-500">没有可展示的轨迹</td></tr>';
                document.getElementById('selectedHtml').textContent = (item.debug && item.debug.list_html) ? item.debug.list_html : '无原始HTML片段';
                document.getElementById('selectedDetail').textContent = (item.debug && item.debug.detail_html) ? item.debug.detail_html : '未启用详情页抓取或没有详情页片段';
                switchDetailTab('trace');
            }

            function renderDebug(result) {
                lastDebugResult = result || null;
                selectedItem = null;
                document.getElementById('matchedCount').textContent = Number(result.matched_count || 0);
                document.getElementById('validCount').textContent = Number(result.valid_count || 0);
                document.getElementById('elapsedMs').textContent = Number(result.elapsed_ms || 0) + ' ms';

                var list = document.getElementById('previewList');
                var items = Array.isArray(result.items) ? result.items : [];
                if (!items.length) {
                    list.innerHTML = '<div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-500">没有提取到有效条目，请先换模板或调列表项选择器。</div>';
                    document.getElementById('selectedDebug').classList.add('hidden');
                } else {
                    list.innerHTML = items.map(function(item, index) {
                        return '<button type="button" data-debug-index="' + index + '" class="w-full text-left rounded-lg border border-gray-200 p-3 hover:border-blue-400 hover:bg-blue-50 transition-colors">'
                            + '<div class="flex items-start justify-between gap-3">'
                            + '<div class="min-w-0">'
                            + '<div class="font-semibold text-gray-900 truncate">' + escapeHtml(item.subject || '') + '</div>'
                            + '<div class="text-xs text-gray-500 mt-1 truncate">' + escapeHtml(item.url || '') + '</div>'
                            + '</div>'
                            + '<span class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-full whitespace-nowrap">' + escapeHtml(item.detail_status || '列表') + '</span>'
                            + '</div>'
                            + '<div class="text-sm text-gray-600 mt-2 leading-6 line-clamp-2">' + escapeHtml(item.summary || item.content || '无摘要') + '</div>'
                            + '<div class="text-xs text-gray-500 mt-2">' + escapeHtml(item.published || '') + '</div>'
                            + '</button>';
                    }).join('');
                    Array.prototype.forEach.call(list.querySelectorAll('[data-debug-index]'), function(btn) {
                        btn.addEventListener('click', function() {
                            renderSelected(items[Number(btn.getAttribute('data-debug-index'))], Number(btn.getAttribute('data-debug-index')));
                        });
                    });
                    renderSelected(items[0], 0);
                }

                var errorBox = document.getElementById('errorList');
                var errors = Array.isArray(result.errors) ? result.errors : [];
                if (errors.length) {
                    errorBox.innerHTML = '<div class="font-semibold mb-2">调试错误</div>' + errors.map(function(error) {
                        return '<div class="mb-1">' + escapeHtml(error) + '</div>';
                    }).join('');
                    errorBox.classList.remove('hidden');
                } else {
                    errorBox.classList.add('hidden');
                    errorBox.innerHTML = '';
                }
            }

            function debugRules(button) {
                setLoading(button, true);
                request('/feeds/webpage-rss/debug', collectDebugData()).then(function(result) {
                    renderDebug(result);
                    showTips('success', '调试完成，结果已刷新。');
                }).catch(function(error) {
                    showTips('error', error.message);
                }).then(function() {
                    setLoading(button, false);
                });
            }

            function applyAiSuggestion(analysis) {
                if (!analysis) {
                    return;
                }
                ['item_selector', 'title_selector', 'url_selector', 'published_selector', 'image_selector', 'summary_selector', 'detail_summary_selector', 'content_selector', 'exclude_selector', 'author_selector'].forEach(function(field) {
                    if (analysis[field] !== undefined && analysis[field] !== null && analysis[field] !== '') {
                        setFieldValue(field, analysis[field]);
                    }
                });
                if (analysis.summary_source) {
                    document.querySelectorAll('input[name="summary_source"]').forEach(function(input) {
                        input.checked = input.value === analysis.summary_source;
                    });
                }
                if (analysis.detail_enabled !== undefined && analysis.detail_enabled !== null) {
                    setFieldValue('detail_enabled', Number(analysis.detail_enabled) === 1);
                }
                if (analysis.notes) {
                    showTips('success', 'AI 规则已应用到表单。' + (analysis.reason ? ' ' + analysis.reason : ''));
                }
            }

            function renderAiResult(result) {
                lastAiResult = result || null;
                var analysis = result.analysis || {};
                document.getElementById('aiResultBox').classList.remove('hidden');
                document.getElementById('aiResultMeta').textContent = (result.llm && result.llm.model_name ? result.llm.model_name : 'AI') + ' · ' + (analysis.page_title || '');
                document.getElementById('aiConfidence').textContent = (analysis.confidence !== undefined && analysis.confidence !== null ? analysis.confidence + '/100' : '-');
                document.getElementById('aiReason').textContent = analysis.reason || '-';
                document.getElementById('aiNotes').textContent = analysis.notes || '-';
                document.getElementById('aiResultJson').textContent = JSON.stringify(analysis, null, 2);
                applyAiSuggestion(analysis);
            }

            function aiAnalyze(button) {
                var modelId = document.getElementById('ai_model_id').value;
                if (!modelId) {
                    showTips('error', '请先选择一个模型。');
                    return;
                }
                var data = collectRuleData();
                data.model_id = modelId;
                data.ai_mode = document.getElementById('ai_mode').value || 'balanced';
                if (!data.list_url) {
                    showTips('error', '请先填写列表页地址。');
                    return;
                }
                setLoading(button, true);
                request('/feeds/webpage-rss/ai-analyze', data).then(function(result) {
                    renderAiResult(result);
                    showTips('success', 'AI 已生成规则，左侧表单已同步。');
                }).catch(function(error) {
                    showTips('error', error.message);
                }).then(function() {
                    setLoading(button, false);
                });
            }

            function saveRules(button) {
                setLoading(button, true);
                request('/feeds/webpage-rss/save', collectSaveData()).then(function(result) {
                    renderDebug(result.debug || {});
                    document.getElementById('rssUrlText').value = result.rss_url || '';
                    document.getElementById('rssUrlBox').classList.remove('hidden');
                    showTips('success', '配置已保存，RSS地址已生成。');
                }).catch(function(error) {
                    showTips('error', error.message);
                }).then(function() {
                    setLoading(button, false);
                });
            }

            function loadCategories() {
                fetch('/feeds/webpage-rss/categories', {
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json'}
                }).then(function(resp) {
                    return resp.json();
                }).then(function(json) {
                    var select = document.getElementById('category_id');
                    var categories = json && json.result ? json.result : [];
                    if (!categories.length) {
                        select.innerHTML = '<option value="">请先创建订阅分类</option>';
                        return;
                    }
                    select.innerHTML = categories.map(function(category) {
                        return '<option value="' + Number(category.id) + '">' + escapeHtml(category.name) + '</option>';
                    }).join('');
                }).catch(function() {
                    document.getElementById('category_id').innerHTML = '<option value="">分类读取失败</option>';
                });
            }

            function loadModels() {
                var select = document.getElementById('ai_model_id');
                fetch('/api/v2/llm/models', {
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json'}
                }).then(function(resp) {
                    return resp.json();
                }).then(function(json) {
                    var models = Array.isArray(json && json.result) ? json.result : [];
                    models = models.filter(function(model) {
                        return model && Number(model.is_active) === 1 && model.model_type === 'chat';
                    });

                    if (!models.length) {
                        select.innerHTML = '<option value="">没有可用的聊天模型</option>';
                        document.getElementById('aiModelHint').textContent = '请先到模型管理里创建可用模型和凭据。';
                        return;
                    }

                    select.innerHTML = '<option value="">选择模型</option>' + models.map(function(model) {
                        var providerName = model.provider && model.provider.name ? model.provider.name : '';
                        var label = (model.display_name || model.name || ('模型 #' + model.id)) + (providerName ? ' · ' + providerName : '');
                        return '<option value="' + Number(model.id) + '">' + escapeHtml(label) + '</option>';
                    }).join('');
                    select.value = String(models[0].id);
                    document.getElementById('aiModelHint').textContent = '当前默认选中第一个可用模型。';
                }).catch(function() {
                    select.innerHTML = '<option value="">模型读取失败</option>';
                    document.getElementById('aiModelHint').textContent = '模型列表读取失败，请检查登录状态或网络。';
                });
            }

            document.getElementById('presetGenericBtn').addEventListener('click', function() { fillPreset('generic'); });
            document.getElementById('presetCardBtn').addEventListener('click', function() { fillPreset('card'); });
            document.getElementById('presetDetailBtn').addEventListener('click', function() { fillPreset('detail'); });
            document.getElementById('presetBlogBtn').addEventListener('click', function() { fillPreset('blog'); });
            document.getElementById('debugBtn').addEventListener('click', function() { debugRules(this); });
            document.getElementById('debugTopBtn').addEventListener('click', function() { debugRules(this); });
            document.getElementById('debugRuleBtn').addEventListener('click', function() { debugRules(this); });
            document.getElementById('saveBtn').addEventListener('click', function() { saveRules(this); });
            document.getElementById('aiAnalyzeBtn').addEventListener('click', function() { aiAnalyze(this); });
            document.getElementById('applyAiBtn').addEventListener('click', function() {
                if (!lastAiResult || !lastAiResult.analysis) {
                    showTips('error', '还没有可应用的 AI 结果。');
                    return;
                }
                applyAiSuggestion(lastAiResult.analysis);
                showTips('success', 'AI 结果已重新应用。');
            });
            document.getElementById('copyRssBtn').addEventListener('click', function() {
                var input = document.getElementById('rssUrlText');
                input.select();
                document.execCommand('copy');
                showTips('success', 'RSS地址已复制。');
            });
            document.getElementById('copyDebugBtn').addEventListener('click', function() {
                if (!selectedItem) {
                    showTips('error', '请先调试并选择一个条目。');
                    return;
                }
                navigator.clipboard.writeText(JSON.stringify({
                    page: lastDebugResult && lastDebugResult.page_url ? lastDebugResult.page_url : '',
                    item: selectedItem
                }, null, 2));
                showTips('success', '条目 JSON 已复制。');
            });
            document.querySelectorAll('[data-detail-tab]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    switchDetailTab(btn.getAttribute('data-detail-tab'));
                });
            });

            loadCategories();
            loadModels();
            fillPreset('generic');
            document.getElementById('ai_mode').value = 'list_summary';
        })();
    </script>
@endsection
