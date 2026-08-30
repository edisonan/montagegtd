/**
 * ai-ppt.js —— AI PPT 幻灯片渲染器（无依赖、自包含）
 *
 * 用法：
 *   window.renderAiPpt(container, deck[, opts])
 *   - container: 元素 or 元素 id
 *   - deck: 制品 content 解析后的 JSON，可传 {format:"ppt", data:{...}} 或直接传 {title, subtitle, slides}
 *   - opts: { startIndex: 0 }
 *
 * 渲染封面（title/subtitle）+ 内容页（title/bullets），支持左右翻页、进度条、
 * 键盘 ←/→ 翻页、点击侧边翻页；所有文本全部转义，绝不对 HTML 原样输出。
 */
(function (global) {
    'use strict';

    var STYLE_ID = 'ai-ppt-style';
    var KEY_LEFT = 37;
    var KEY_RIGHT = 39;
    var liveDisposals = [];

    function disposeAll() {
        liveDisposals.forEach(function (fn) {
            try { fn(); } catch (e) {}
        });
        liveDisposals = [];
    }

    function injectStyle() {
        if (document.getElementById(STYLE_ID)) { return; }
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = [
            '.aippt-wrap { width: 100%; --aippt-accent: #6366f1; --aippt-accent-2: #0ea5e9; --aippt-ink: #0f172a; --aippt-sub: #64748b; --aippt-line: #e2e8f0; --aippt-bg: #ffffff; }',
            '.aippt-deck { position: relative; width: 100%; aspect-ratio: 16 / 9; min-height: 240px; max-height: 460px; background: var(--aippt-bg); border: 1px solid var(--aippt-line); border-radius: 14px; overflow: hidden; box-shadow: 0 10px 30px rgba(15,23,42,.08); }',
            '.aippt-slide { position: absolute; inset: 0; display: flex; flex-direction: column; padding: clamp(18px, 5%, 44px); box-sizing: border-box; opacity: 0; visibility: hidden; transform: translateX(24px); transition: opacity .28s ease, transform .28s ease, visibility .28s; overflow: auto; }',
            '.aippt-slide.aippt-active { opacity: 1; visibility: visible; transform: translateX(0); }',
            '.aippt-slide.aippt-prev { transform: translateX(-24px); }',
            '.aippt-cover { justify-content: center; align-items: center; text-align: center; background: linear-gradient(135deg, #eef2ff 0%, #f0f9ff 55%, #ecfeff 100%); }',
            '.aippt-cover-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: #6366f1; background: rgba(99,102,241,.12); border: 1px solid rgba(99,102,241,.25); padding: 4px 12px; border-radius: 999px; margin-bottom: 18px; }',
            '.aippt-cover-title { font-size: clamp(20px, 5vw, 40px); font-weight: 800; line-height: 1.25; color: var(--aippt-ink); margin: 0 0 12px; word-break: break-word; }',
            '.aippt-cover-sub { font-size: clamp(12px, 2.2vw, 17px); color: var(--aippt-sub); line-height: 1.6; max-width: 80%; margin: 0 auto; word-break: break-word; }',
            '.aippt-cover-hint { margin-top: 26px; font-size: 11px; color: #94a3b8; }',
            '.aippt-cover-hint i { margin: 0 4px; }',
            '.aippt-body-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; margin-bottom: 14px; }',
            '.aippt-body-title { font-size: clamp(17px, 3.4vw, 27px); font-weight: 750; line-height: 1.3; color: var(--aippt-ink); margin: 0; word-break: break-word; }',
            '.aippt-body-num { flex-shrink: 0; font-size: clamp(11px, 1.8vw, 14px); font-weight: 700; color: #6366f1; background: #eef2ff; border-radius: 8px; padding: 4px 10px; }',
            '.aippt-bullets { list-style: none; margin: 0; padding: 0; flex: 1; display: flex; flex-direction: column; gap: clamp(8px, 1.6vw, 14px); justify-content: center; }',
            '.aippt-bullets li { position: relative; padding-left: clamp(20px, 3vw, 30px); font-size: clamp(13px, 2.4vw, 18px); line-height: 1.6; color: #334155; word-break: break-word; }',
            '.aippt-bullets li::before { content: ""; position: absolute; left: 0; top: .62em; width: 9px; height: 9px; border-radius: 3px; background: linear-gradient(135deg, var(--aippt-accent), var(--aippt-accent-2)); }',
            '.aippt-foot { display: flex; align-items: center; gap: 10px; margin-top: 10px; }',
            '.aippt-nav { border: 1px solid var(--aippt-line); background: #fff; color: #334155; width: 30px; height: 30px; border-radius: 8px; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease; }',
            '.aippt-nav:hover:not(:disabled) { border-color: #6366f1; color: #6366f1; }',
            '.aippt-nav:disabled { opacity: .35; cursor: not-allowed; }',
            '.aippt-track { flex: 1; height: 4px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }',
            '.aippt-track-bar { height: 100%; width: 0; background: linear-gradient(90deg, var(--aippt-accent), var(--aippt-accent-2)); border-radius: 999px; transition: width .25s ease; }',
            '.aippt-count { font-size: 12px; color: var(--aippt-sub); white-space: nowrap; font-variant-numeric: tabular-nums; }',
            '.aippt-empty { display: flex; align-items: center; justify-content: center; min-height: 140px; color: #94a3b8; font-size: 13px; border: 1px dashed #e2e8f0; border-radius: 12px; background: #f8fafc; }'
        ].join('\n');
        document.head.appendChild(style);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function parseDeck(deck) {
        var d = deck;
        if (d && typeof d === 'object') {
            if (d.format === 'ppt' && d.data) { d = d.data; }
            else if (d.data && !d.slides) { d = d.data; }
        }
        if (!d || typeof d !== 'object') { d = {}; }
        var slides = Array.isArray(d.slides) ? d.slides : [];
        var normalized = [];
        for (var i = 0; i < slides.length; i++) {
            var s = slides[i];
            if (!s || typeof s !== 'object') { continue; }
            var title = String(s.title == null ? '' : s.title).trim();
            var bullets = Array.isArray(s.bullets)
                ? s.bullets.map(function (b) { return String(b == null ? '' : b).trim(); }).filter(function (b) { return b !== ''; })
                : [];
            if (title === '' || bullets.length === 0) { continue; }
            normalized.push({ title: title, bullets: bullets });
        }
        return {
            title: String(d.title == null ? '' : d.title).trim(),
            subtitle: String(d.subtitle == null ? '' : d.subtitle).trim(),
            slides: normalized
        };
    }

    function renderAiPpt(container, deck, opts) {
        injectStyle();
        opts = opts || {};

        var el = typeof container === 'string' ? document.getElementById(container) : container;
        if (!el) { return; }

        var data = parseDeck(deck);
        if (data.slides.length === 0) {
            el.innerHTML = '<div class="aippt-empty">幻灯片数据为空或格式无效</div>';
            return;
        }

        var total = data.slides.length + 1; // +1 封面
        var current = Math.max(0, Math.min(total - 1, Number(opts.startIndex) || 0));

        var wrap = document.createElement('div');
        wrap.className = 'aippt-wrap';
        wrap.innerHTML = [
            '<div class="aippt-deck" data-role="deck">',
            '<div class="aippt-slide aippt-cover" data-cover="1">',
            '<span class="aippt-cover-badge"><i class="fas fa-wand-magic-sparkles"></i>AI 生成演示稿</span>',
            '<h3 class="aippt-cover-title"></h3>',
            '<p class="aippt-cover-sub"></p>',
            '<div class="aippt-cover-hint"><i class="fas fa-arrow-left"></i>方向键或点击按钮翻页<i class="fas fa-arrow-right"></i></div>',
            '</div>',
            '<div class="aippt-body" style="display:none;">',
            '<div class="aippt-body-head"><h3 class="aippt-body-title"></h3><span class="aippt-body-num"></span></div>',
            '<ul class="aippt-bullets"></ul>',
            '</div>',
            '</div>',
            '<div class="aippt-foot">',
            '<button type="button" class="aippt-nav" data-nav="prev" title="上一页"><i class="fas fa-chevron-left"></i></button>',
            '<div class="aippt-track"><div class="aippt-track-bar"></div></div>',
            '<span class="aippt-count"></span>',
            '<button type="button" class="aippt-nav" data-nav="next" title="下一页"><i class="fas fa-chevron-right"></i></button>',
            '</div>'
        ].join('');
        el.innerHTML = '';
        el.appendChild(wrap);

        var deckEl = wrap.querySelector('[data-role="deck"]');
        var coverEl = wrap.querySelector('[data-cover="1"]');
        var bodyEl = wrap.querySelector('.aippt-body');
        var coverTitle = coverEl.querySelector('.aippt-cover-title');
        var coverSub = coverEl.querySelector('.aippt-cover-sub');
        var bodyTitle = bodyEl.querySelector('.aippt-body-title');
        var bodyNum = bodyEl.querySelector('.aippt-body-num');
        var bulletsEl = bodyEl.querySelector('.aippt-bullets');
        var prevBtn = wrap.querySelector('[data-nav="prev"]');
        var nextBtn = wrap.querySelector('[data-nav="next"]');
        var bar = wrap.querySelector('.aippt-track-bar');
        var countEl = wrap.querySelector('.aippt-count');

        coverTitle.textContent = data.title !== '' ? data.title : 'AI 演示文稿';
        coverSub.textContent = data.subtitle !== '' ? data.subtitle : 'AI 基于原文自动生成的演示文稿';

        function renderBullets(bullets) {
            bulletsEl.innerHTML = '';
            bullets.forEach(function (b) {
                var li = document.createElement('li');
                li.textContent = b;
                bulletsEl.appendChild(li);
            });
        }

        function show(index, direction) {
            current = Math.max(0, Math.min(total - 1, index));
            var slideEls = deckEl.children;
            for (var i = 0; i < slideEls.length; i++) {
                slideEls[i].classList.remove('aippt-active', 'aippt-prev');
            }
            if (current === 0) {
                coverEl.style.display = '';
                bodyEl.style.display = 'none';
                coverEl.classList.add('aippt-active');
            } else {
                coverEl.style.display = 'none';
                bodyEl.style.display = '';
                var slide = data.slides[current - 1];
                bodyTitle.textContent = slide.title;
                bodyNum.textContent = (current) + ' / ' + (total - 1);
                renderBullets(slide.bullets);
                bodyEl.classList.add('aippt-active');
            }
            prevBtn.disabled = current === 0;
            nextBtn.disabled = current === total - 1;
            bar.style.width = ((current + 1) / total * 100).toFixed(1) + '%';
            countEl.textContent = (current + 1) + ' / ' + total;
        }

        function step(delta) {
            show(current + delta, delta > 0 ? 1 : -1);
        }

        prevBtn.addEventListener('click', function () { step(-1); });
        nextBtn.addEventListener('click', function () { step(1); });
        deckEl.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('button')) { return; }
            var rect = deckEl.getBoundingClientRect();
            if (!rect.width) { return; }
            step((e.clientX - rect.left) > rect.width / 2 ? 1 : -1);
        });

        var keyHandler = function (e) {
            if (e.keyCode === KEY_LEFT) { step(-1); e.preventDefault(); }
            else if (e.keyCode === KEY_RIGHT) { step(1); e.preventDefault(); }
        };
        document.addEventListener('keydown', keyHandler);

        // 销毁时解绑键盘事件（页面上同时只保留一个活动的 PPT 实例）
        disposeAll();
        var cleanup = function () {
            document.removeEventListener('keydown', keyHandler);
            var idx = liveDisposals.indexOf(cleanup);
            if (idx !== -1) { liveDisposals.splice(idx, 1); }
        };
        liveDisposals.push(cleanup);
        if (el.__aipptCleanup) { el.__aipptCleanup = null; }

        show(current, 0);
    }

    global.renderAiPpt = renderAiPpt;
    global.__aipptDisposeAll = disposeAll;
})(window);