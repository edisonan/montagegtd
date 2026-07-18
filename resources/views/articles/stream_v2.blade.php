@extends('layouts.app')

@section('title', '沉浸刷文 · 新版 - 蒙太奇')
@section('description', '抖音式单卡文章信息流，先刷摘要，再按需阅读全文')

@section('content')
<style>
    main.max-w-7xl {
        max-width: none !important;
        padding: 0 !important;
    }

    body {
        overflow: hidden;
        background: #080b12;
    }

    .v2-stream {
        --v2-accent: #ff6b4a;
        position: relative;
        width: 100%;
        height: calc(100vh - 64px);
        height: calc(100dvh - 64px);
        min-height: 560px;
        overflow: hidden;
        color: #fff;
        background: #080b12;
        user-select: none;
    }

    .v2-bg {
        position: absolute;
        inset: -24px;
        overflow: hidden;
        background:
            radial-gradient(circle at 22% 18%, rgba(255, 107, 74, .45), transparent 33%),
            radial-gradient(circle at 78% 76%, rgba(74, 119, 255, .32), transparent 36%),
            #111521;
    }

    .v2-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: blur(34px) saturate(1.12);
        transform: scale(1.12);
        opacity: 0;
        transition: opacity .28s ease;
    }

    .v2-bg.has-image img { opacity: .48; }

    .v2-bg::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(5, 7, 12, .48) 0%, rgba(5, 7, 12, .1) 34%, rgba(5, 7, 12, .82) 100%),
            radial-gradient(circle at center, transparent 0%, rgba(5, 7, 12, .24) 70%, rgba(5, 7, 12, .62) 100%);
    }

    .v2-topbar {
        position: absolute;
        z-index: 12;
        top: 0;
        left: 0;
        right: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 22px;
        pointer-events: none;
    }

    .v2-topbar > * { pointer-events: auto; }

    .v2-top-left,
    .v2-top-right {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .v2-icon-btn,
    .v2-filter-chip,
    .v2-action,
    .v2-primary-btn,
    .v2-sheet button,
    .v2-sheet select,
    .v2-sheet input {
        font: inherit;
    }

    .v2-icon-btn,
    .v2-filter-chip {
        height: 38px;
        border: 1px solid rgba(255, 255, 255, .14);
        color: #fff;
        background: rgba(12, 15, 23, .44);
        box-shadow: 0 8px 30px rgba(0, 0, 0, .14);
        backdrop-filter: blur(15px);
    }

    .v2-icon-btn {
        width: 38px;
        border-radius: 50%;
    }

    .v2-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border-radius: 999px;
        padding: 0 13px;
        font-size: 13px;
        font-weight: 650;
    }

    .v2-progress {
        position: absolute;
        z-index: 13;
        top: 0;
        left: 0;
        height: 3px;
        width: 0;
        background: linear-gradient(90deg, var(--v2-accent), #ffc857);
        box-shadow: 0 0 16px rgba(255, 107, 74, .65);
        transition: width .25s ease;
    }

    .v2-stage {
        position: relative;
        z-index: 4;
        height: 100%;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 74px 96px 28px 30px;
    }

    .v2-card {
        position: relative;
        width: min(820px, 72vw);
        height: calc(100% - 4px);
        max-height: none;
        padding: 26px 42px 28px;
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 28px;
        overflow: hidden;
        background: linear-gradient(145deg, rgba(18, 22, 32, .58), rgba(10, 12, 19, .76));
        box-shadow: 0 30px 100px rgba(0, 0, 0, .42);
        backdrop-filter: blur(22px);
        cursor: pointer;
        transform-origin: center;
    }

    .v2-preview {
        height: 100%;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .v2-card.is-read { opacity: .78; }
    .v2-card.enter-next { animation: v2EnterNext .34s cubic-bezier(.2, .82, .28, 1); }
    .v2-card.enter-prev { animation: v2EnterPrev .34s cubic-bezier(.2, .82, .28, 1); }

    @keyframes v2EnterNext {
        from { opacity: 0; transform: translateY(52px) scale(.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes v2EnterPrev {
        from { opacity: 0; transform: translateY(-52px) scale(.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .v2-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        color: rgba(255, 255, 255, .72);
        font-size: 13px;
        font-weight: 550;
    }

    .v2-feed {
        max-width: 360px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #fff;
        font-weight: 720;
    }

    .v2-dot::before {
        content: "";
        display: inline-block;
        width: 3px;
        height: 3px;
        margin: 0 2px 3px 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, .48);
    }

    .v2-title {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        margin: 14px 0 14px;
        max-width: 760px;
        font-size: clamp(30px, 4vw, 56px);
        line-height: 1.16;
        letter-spacing: -.035em;
        font-weight: 820;
        text-wrap: balance;
        text-shadow: 0 8px 30px rgba(0, 0, 0, .28);
    }

    .v2-summary {
        position: relative;
        flex: 1 1 auto;
        min-height: 180px;
        max-width: 700px;
        overflow: hidden;
        color: rgba(255, 255, 255, .84);
        font-size: clamp(16px, 1.5vw, 20px);
        line-height: 1.78;
        letter-spacing: .01em;
        pointer-events: none;
        mask-image: linear-gradient(180deg, transparent 0, #000 5%, #000 88%, transparent 100%);
        -webkit-mask-image: linear-gradient(180deg, transparent 0, #000 5%, #000 88%, transparent 100%);
    }

    .v2-summary-track {
        padding: 12px 0 38px;
        white-space: pre-wrap;
        will-change: transform;
    }

    .v2-tags {
        min-height: 24px;
        display: flex;
        gap: 8px;
        margin-top: 12px;
        overflow: hidden;
    }

    .v2-tag {
        flex: 0 0 auto;
        padding: 4px 9px;
        border-radius: 999px;
        color: rgba(255, 255, 255, .78);
        background: rgba(255, 255, 255, .1);
        font-size: 12px;
    }

    .v2-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-top: 14px;
    }

    .v2-reading-meta {
        color: rgba(255, 255, 255, .58);
        font-size: 13px;
    }

    .v2-primary-btn {
        flex: 0 0 auto;
        border: 0;
        border-radius: 999px;
        padding: 11px 18px;
        color: #11131a;
        background: #fff;
        font-size: 14px;
        font-weight: 750;
        box-shadow: 0 8px 28px rgba(0, 0, 0, .22);
    }

    .v2-inline-reader {
        height: 100%;
        min-height: 0;
        display: none;
        flex-direction: column;
        user-select: text;
    }

    .v2-card.reading-mode {
        width: min(980px, 78vw);
        padding: 0;
        cursor: default;
        background: rgba(247, 244, 238, .97);
        color: #20232a;
    }

    .v2-card.reading-mode.is-read { opacity: 1; }

    .v2-card.reading-mode .v2-preview { display: none; }
    .v2-card.reading-mode .v2-inline-reader { display: flex; }

    .v2-inline-head {
        flex: 0 0 auto;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0 18px;
        border-bottom: 1px solid rgba(31, 35, 42, .09);
        background: rgba(247, 244, 238, .94);
    }

    .v2-inline-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        color: #4d515a;
        background: transparent;
        font-size: 13px;
        font-weight: 720;
    }

    .v2-inline-actions { display: flex; align-items: center; gap: 7px; }
    .v2-inline-actions button,
    .v2-inline-actions a {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        color: #343842;
        background: rgba(31, 35, 42, .07);
        text-decoration: none;
    }

    .v2-inline-actions .active { color: #fff; background: var(--v2-accent); }

    .v2-inline-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .v2-inline-article {
        width: min(780px, calc(100% - 40px));
        margin: 0 auto;
        padding: 38px 0 80px;
    }

    .v2-inline-feed { color: #9a5f49; font-size: 13px; font-weight: 740; }
    .v2-inline-title { margin: 10px 0 13px; font-size: clamp(28px, 4vw, 48px); line-height: 1.2; letter-spacing: -.03em; font-weight: 820; }
    .v2-inline-meta { color: #83858b; font-size: 13px; }
    .v2-inline-content { margin-top: 34px; font-size: 18px; line-height: 1.9; }
    .v2-inline-content p { margin: 0 0 1.3em; }
    .v2-inline-content img { max-width: 100%; height: auto; border-radius: 12px; }
    .v2-inline-content h1,
    .v2-inline-content h2,
    .v2-inline-content h3 { margin: 1.8em 0 .7em; line-height: 1.35; font-weight: 760; }
    .v2-inline-content blockquote { margin: 1.5em 0; padding: 10px 18px; border-left: 3px solid #c5795f; color: #5c6068; background: rgba(197, 121, 95, .08); }
    .v2-inline-loading { padding: 70px 20px; color: #85878d; text-align: center; }

    .v2-stream.reading-mode .v2-rail,
    .v2-stream.reading-mode .v2-swipe-hint { opacity: 0; pointer-events: none; }

    .v2-rail {
        position: absolute;
        z-index: 10;
        right: 24px;
        top: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 17px;
        transform: translateY(-42%);
    }

    .v2-action {
        width: 54px;
        border: 0;
        color: #fff;
        background: transparent;
        text-align: center;
        font-size: 11px;
        font-weight: 650;
    }

    .v2-action .v2-action-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px;
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 50%;
        color: #fff;
        background: rgba(10, 13, 20, .46);
        box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
        backdrop-filter: blur(14px);
        font-size: 19px;
        transition: .18s ease;
    }

    .v2-action:hover .v2-action-icon { transform: scale(1.07); }
    .v2-action.active .v2-action-icon {
        border-color: transparent;
        background: var(--v2-accent);
    }

    .v2-swipe-hint {
        position: absolute;
        z-index: 9;
        left: 50%;
        bottom: 12px;
        display: flex;
        align-items: center;
        gap: 7px;
        color: rgba(255, 255, 255, .52);
        font-size: 12px;
        transform: translateX(-50%);
        animation: v2Hint 1.8s ease-in-out infinite;
    }

    @keyframes v2Hint {
        0%, 100% { transform: translate(-50%, 0); }
        50% { transform: translate(-50%, -5px); }
    }

    .v2-state {
        position: relative;
        z-index: 8;
        width: min(520px, calc(100vw - 40px));
        padding: 34px;
        border-radius: 24px;
        color: rgba(255, 255, 255, .78);
        background: rgba(15, 18, 27, .68);
        backdrop-filter: blur(18px);
        text-align: center;
    }

    .v2-state.hidden { display: none; }

    .v2-sheet-wrap,
    .v2-reader {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
    }

    .v2-sheet-wrap.active,
    .v2-reader.active { display: block; }

    .v2-sheet-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(3, 5, 9, .68);
        backdrop-filter: blur(8px);
    }

    .v2-sheet {
        position: absolute;
        left: 50%;
        bottom: 18px;
        width: min(620px, calc(100vw - 28px));
        padding: 22px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 24px;
        color: #f7f8fb;
        background: #151925;
        box-shadow: 0 30px 100px rgba(0, 0, 0, .48);
        transform: translateX(-50%);
        animation: v2SheetUp .24s ease-out;
    }

    @keyframes v2SheetUp {
        from { opacity: 0; transform: translate(-50%, 22px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }

    .v2-sheet-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .v2-sheet-title { font-size: 18px; font-weight: 760; }
    .v2-sheet-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .v2-field-full { grid-column: 1 / -1; }
    .v2-label { display: block; margin-bottom: 7px; color: #9ba3b4; font-size: 12px; }

    .v2-sheet select,
    .v2-sheet input {
        width: 100%;
        height: 42px;
        border: 1px solid #303747;
        border-radius: 12px;
        padding: 0 12px;
        outline: none;
        color: #f7f8fb;
        background: #0e1119;
    }

    .v2-sheet-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .v2-sheet-action {
        border: 1px solid #303747;
        border-radius: 999px;
        padding: 9px 17px;
        color: #fff;
        background: transparent;
        font-weight: 680;
    }

    .v2-sheet-action.primary { border-color: var(--v2-accent); background: var(--v2-accent); }

    .v2-reader {
        overflow: hidden;
        color: #20232a;
        background: #f7f4ee;
        user-select: text;
    }

    .v2-reader-head {
        position: absolute;
        z-index: 3;
        top: 0;
        left: 0;
        right: 0;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0 22px;
        border-bottom: 1px solid rgba(31, 35, 42, .09);
        background: rgba(247, 244, 238, .92);
        backdrop-filter: blur(16px);
    }

    .v2-reader-actions { display: flex; align-items: center; gap: 8px; }
    .v2-reader-head button,
    .v2-reader-head a {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        color: #343842;
        background: rgba(31, 35, 42, .07);
        text-decoration: none;
    }

    .v2-reader-head .active { color: #fff; background: var(--v2-accent); }

    .v2-reader-scroll {
        position: absolute;
        inset: 64px 0 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .v2-reader-article {
        width: min(820px, calc(100vw - 36px));
        margin: 0 auto;
        padding: 54px 0 100px;
    }

    .v2-reader-feed { color: #8a5c48; font-size: 14px; font-weight: 720; }
    .v2-reader-title { margin: 13px 0 15px; font-size: clamp(30px, 5vw, 52px); line-height: 1.18; letter-spacing: -.03em; font-weight: 820; }
    .v2-reader-meta { color: #83858b; font-size: 13px; }
    .v2-reader-content { margin-top: 42px; font-size: 18px; line-height: 1.9; }
    .v2-reader-content p { margin: 0 0 1.3em; }
    .v2-reader-content img { max-width: 100%; height: auto; border-radius: 12px; }
    .v2-reader-content h1,
    .v2-reader-content h2,
    .v2-reader-content h3 { margin: 1.8em 0 .7em; line-height: 1.35; font-weight: 760; }
    .v2-reader-content blockquote { margin: 1.5em 0; padding: 10px 18px; border-left: 3px solid #c5795f; color: #5c6068; background: rgba(197, 121, 95, .08); }
    .v2-reader-loading { padding: 80px 20px; color: #85878d; text-align: center; }

    .v2-toast {
        position: fixed;
        z-index: 160;
        left: 50%;
        bottom: 34px;
        display: none;
        max-width: calc(100vw - 40px);
        padding: 10px 16px;
        border-radius: 999px;
        color: #fff;
        background: rgba(8, 10, 15, .86);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .3);
        transform: translateX(-50%);
        font-size: 13px;
    }

    @media (max-width: 760px) {
        .v2-stream { min-height: 500px; }
        .v2-topbar { padding: 13px 12px; }
        .v2-filter-chip .v2-chip-extra { display: none; }
        .v2-stage { align-items: flex-start; padding: 66px 70px 50px 12px; }
        .v2-card {
            width: 100%;
            height: 100%;
            max-height: none;
            padding: 18px 20px 18px;
            border-radius: 22px;
        }
        .v2-title { margin: 10px 0 9px; font-size: clamp(27px, 8vw, 40px); }
        .v2-summary { min-height: 150px; font-size: 16px; line-height: 1.68; }
        .v2-tags { margin-top: 13px; }
        .v2-card-footer { align-items: flex-end; margin-top: 10px; }
        .v2-reading-meta span:first-child { display: none; }
        .v2-primary-btn { padding: 9px 14px; }
        .v2-rail { right: 8px; top: auto; bottom: 76px; transform: none; gap: 12px; }
        .v2-action { width: 54px; }
        .v2-action .v2-action-icon { width: 44px; height: 44px; }
        .v2-swipe-hint { bottom: 16px; }
        .v2-sheet-grid { grid-template-columns: 1fr; }
        .v2-field-full { grid-column: auto; }
        .v2-reader-head { padding: 0 10px; }
        .v2-reader-article { padding-top: 34px; }
        .v2-reader-content { margin-top: 30px; font-size: 17px; line-height: 1.82; }
        .v2-card.reading-mode { width: 100%; }
        .v2-inline-head { padding: 0 10px; }
        .v2-inline-article { width: calc(100% - 30px); padding-top: 28px; }
        .v2-inline-content { margin-top: 26px; font-size: 17px; line-height: 1.82; }
    }

    @media (max-width: 760px), (max-height: 700px) {
        .v2-title { -webkit-line-clamp: 2; }
        .v2-summary { min-height: 90px; }
        .v2-tags { display: none; }
    }
</style>

<section class="v2-stream" id="v2Stream">
    <div class="v2-bg" id="v2Background"><img id="v2BackgroundImage" alt=""></div>
    <div class="v2-progress" id="v2Progress"></div>

    <header class="v2-topbar">
        <div class="v2-top-left">
            <button type="button" class="v2-icon-btn" id="v2BackBtn" title="返回文章列表"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="v2-filter-chip" id="v2StatusChip"><i class="fas fa-circle text-[7px] text-orange-400"></i><span>未读</span></button>
            <button type="button" class="v2-filter-chip" id="v2TimeChip"><i class="far fa-clock"></i><span>最近6小时</span></button>
        </div>
        <div class="v2-top-right">
            <span class="v2-filter-chip"><span id="v2Position">0 / 0</span><span class="v2-chip-extra"> 篇</span></span>
            <button type="button" class="v2-icon-btn" id="v2FilterBtn" title="筛选"><i class="fas fa-sliders-h"></i></button>
        </div>
    </header>

    <div class="v2-stage" id="v2Stage">
        <article class="v2-card" id="v2Card" tabindex="0" aria-label="点击阅读全文">
            <div class="v2-preview" id="v2Preview">
                <div class="v2-meta">
                    <span class="v2-feed" id="v2Feed">加载中</span>
                    <span class="v2-dot" id="v2Category">未分类</span>
                    <span class="v2-dot" id="v2Published">-</span>
                </div>
                <h1 class="v2-title" id="v2Title">正在准备你的沉浸阅读流…</h1>
                <div class="v2-summary" id="v2Summary"><div class="v2-summary-track" id="v2SummaryTrack">每次只看一篇。上滑下一篇，下滑上一篇，点击阅读全文后可自主滚动正文。</div></div>
                <div class="v2-tags" id="v2Tags"></div>
                <div class="v2-card-footer">
                    <div class="v2-reading-meta"><span id="v2WordCount">0 字</span> · <span id="v2ReadTime">预计 1 分钟</span></div>
                    <button type="button" class="v2-primary-btn" id="v2ReadBtn">阅读全文 <i class="fas fa-arrow-right ml-1"></i></button>
                </div>
            </div>

            <div class="v2-inline-reader" id="v2InlineReader">
                <header class="v2-inline-head">
                    <button type="button" class="v2-inline-back" id="v2ReaderClose"><i class="fas fa-chevron-left"></i><span>返回沉浸流</span></button>
                    <div class="v2-inline-actions">
                        <a href="#" target="_blank" id="v2ReaderOrigin" title="查看原文"><i class="fas fa-external-link-alt"></i></a>
                        <button type="button" id="v2ReaderStar" title="收藏"><i class="far fa-star"></i></button>
                        <button type="button" id="v2ReaderLater" title="稍后阅读"><i class="far fa-clock"></i></button>
                        <button type="button" id="v2ReaderNext" title="下一篇"><i class="fas fa-forward-step"></i></button>
                    </div>
                </header>
                <div class="v2-inline-scroll" id="v2ReaderScroll">
                    <article class="v2-inline-article">
                        <div class="v2-inline-feed" id="v2ReaderFeed">-</div>
                        <h1 class="v2-inline-title" id="v2ReaderTitle">-</h1>
                        <div class="v2-inline-meta" id="v2ReaderMeta">-</div>
                        <div class="v2-inline-content" id="v2ReaderContent"><div class="v2-inline-loading"><i class="fas fa-spinner fa-spin mr-2"></i>加载全文中…</div></div>
                    </article>
                </div>
            </div>
        </article>
        <div class="v2-state hidden" id="v2EmptyState"></div>
    </div>

    <aside class="v2-rail">
        <button type="button" class="v2-action" id="v2StarBtn"><span class="v2-action-icon"><i class="far fa-star"></i></span><span>收藏</span></button>
        <button type="button" class="v2-action" id="v2LaterBtn"><span class="v2-action-icon"><i class="far fa-clock"></i></span><span>稍后读</span></button>
        <button type="button" class="v2-action" id="v2ReadStateBtn"><span class="v2-action-icon"><i class="fas fa-check"></i></span><span id="v2ReadStateText">未读</span></button>
        <button type="button" class="v2-action" id="v2NextBtn"><span class="v2-action-icon"><i class="fas fa-chevron-down"></i></span><span>下一篇</span></button>
    </aside>

    <div class="v2-swipe-hint"><i class="fas fa-chevron-up"></i><span>上滑下一篇</span></div>
</section>

<div class="v2-sheet-wrap" id="v2FilterSheet">
    <div class="v2-sheet-backdrop" id="v2FilterBackdrop"></div>
    <div class="v2-sheet">
        <div class="v2-sheet-head">
            <div class="v2-sheet-title">调整内容流</div>
            <button type="button" class="v2-icon-btn" id="v2FilterClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="v2-sheet-grid">
            <label><span class="v2-label">阅读状态</span><select id="v2StatusFilter"><option value="unread">未读</option><option value="all">全部</option><option value="read">已读</option><option value="read_later">稍后阅读</option><option value="star">收藏</option></select></label>
            <label><span class="v2-label">时间范围</span><select id="v2TimeFilter"><option value="3h">最近3小时</option><option value="6h">最近6小时</option><option value="1d">最近1天</option><option value="3d">最近3天</option><option value="7d">最近7天</option><option value="all">全部时间</option></select></label>
            <label class="v2-field-full"><span class="v2-label">订阅</span><select id="v2FeedFilter"><option value="">全部订阅</option></select></label>
            <label class="v2-field-full"><span class="v2-label">关键词</span><input type="search" id="v2KeywordFilter" placeholder="搜索标题、订阅或关键词"></label>
        </div>
        <div class="v2-sheet-actions">
            <button type="button" class="v2-sheet-action" id="v2ResetFilter">恢复默认</button>
            <button type="button" class="v2-sheet-action primary" id="v2ApplyFilter">应用筛选</button>
        </div>
    </div>
</div>

<div class="v2-toast" id="v2Toast"></div>

<script>
    $(function () {
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : function () { return Promise.reject(new Error('API客户端未初始化')); };
        var query = new URLSearchParams(window.location.search);
        var statusLabels = { unread: '未读', all: '全部', read: '已读', read_later: '稍后阅读', star: '收藏' };
        var timeLabels = { '3h': '最近3小时', '6h': '最近6小时', '1d': '最近1天', '3d': '最近3天', '7d': '最近7天', all: '全部时间' };
        var state = {
            status: query.get('status') || 'unread',
            timeRange: query.get('time_range') || '6h',
            feedId: query.get('feed_id') || '',
            keyword: query.get('keyword') || '',
            pageCount: Math.min(100, Math.max(10, Number(query.get('page_count') || 30))),
            page: Math.max(1, Number(query.get('page') || 1)),
            index: 0,
            items: [],
            feeds: [],
            pagination: null,
            loading: false,
            loadingMore: false,
            direction: 'next',
            touchStartY: 0,
            touchStartX: 0,
            touchMoving: false,
            wheelLocked: false,
            dwellTimer: null,
            readerRequestId: 0,
            readerOpen: false,
            previewScrollTimer: null,
            previewPauseUntil: 0,
            fullArticleCache: {},
            fullArticleRequests: {}
        };

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char];
            });
        }

        function showToast(message) {
            var $toast = $('#v2Toast');
            $toast.stop(true, true).text(message).fadeIn(100);
            clearTimeout($toast.data('timer'));
            $toast.data('timer', setTimeout(function () { $toast.fadeOut(160); }, 1800));
        }

        function currentItem() { return state.items[state.index] || null; }

        function articleCategory(article) {
            if (article && article.ai_profile && article.ai_profile.primary_category) return article.ai_profile.primary_category;
            return '未分类';
        }

        function articleSummary(article) {
            if (article && article.ai_profile && article.ai_profile.summary) return article.ai_profile.summary;
            return '这篇文章暂时没有摘要。点击进入全文，或继续上滑浏览下一篇。';
        }

        function formatPublished(value) {
            if (!value) return '-';
            var date = new Date(String(value).replace(/-/g, '/'));
            if (isNaN(date.getTime())) return value;
            var diff = Date.now() - date.getTime();
            if (diff >= 0 && diff < 3600000) return Math.max(1, Math.floor(diff / 60000)) + '分钟前';
            if (diff >= 0 && diff < 86400000) return Math.floor(diff / 3600000) + '小时前';
            if (diff >= 0 && diff < 604800000) return Math.floor(diff / 86400000) + '天前';
            return (date.getMonth() + 1) + '月' + date.getDate() + '日';
        }

        function syncFilterLabels() {
            $('#v2StatusChip span:last').text(statusLabels[state.status] || '未读');
            $('#v2TimeChip span').text(timeLabels[state.timeRange] || '最近6小时');
            $('#v2StatusFilter').val(state.status);
            $('#v2TimeFilter').val(state.timeRange);
            $('#v2FeedFilter').val(String(state.feedId));
            $('#v2KeywordFilter').val(state.keyword);
        }

        function renderFeeds() {
            var html = '<option value="">全部订阅</option>';
            state.feeds.forEach(function (feed) {
                html += '<option value="' + Number(feed.id) + '">' + escapeHtml(feed.feed_name || '未命名订阅') + '</option>';
            });
            $('#v2FeedFilter').html(html).val(String(state.feedId));
        }

        function syncActionButtons(item) {
            var status = item ? item.status : '';
            $('#v2StarBtn, #v2ReaderStar').toggleClass('active', status === 'star');
            $('#v2LaterBtn, #v2ReaderLater').toggleClass('active', status === 'read_later');
            $('#v2ReadStateBtn').toggleClass('active', status === 'read');
            $('#v2ReadStateText').text(status === 'read' ? '已读' : '未读');
        }

        function stopPreviewScroll() {
            if (state.previewScrollTimer) {
                clearInterval(state.previewScrollTimer);
                state.previewScrollTimer = null;
            }
        }

        function startPreviewScroll() {
            stopPreviewScroll();
            if (state.readerOpen) return;
            var viewport = document.getElementById('v2Summary');
            if (!viewport || viewport.scrollHeight <= viewport.clientHeight + 8) return;
            viewport.scrollTop = 0;
            state.previewPauseUntil = Date.now() + 1400;
            state.previewScrollTimer = setInterval(function () {
                if (state.readerOpen || Date.now() < state.previewPauseUntil) return;
                var maxTop = viewport.scrollHeight - viewport.clientHeight;
                if (viewport.scrollTop >= maxTop - 2) {
                    viewport.scrollTop = 0;
                    state.previewPauseUntil = Date.now() + 1300;
                    return;
                }
                viewport.scrollTop += 1;
            }, 44);
        }

        function fullArticleKey(item) {
            return item && item.id ? String(item.id) : '';
        }

        function loadFullArticle(item) {
            var key = fullArticleKey(item);
            if (!key || !item.article) return Promise.reject(new Error('文章信息不完整'));
            if (state.fullArticleCache[key]) return Promise.resolve(state.fullArticleCache[key]);
            if (state.fullArticleRequests[key]) return state.fullArticleRequests[key];

            state.fullArticleRequests[key] = apiRequest('GET', '/articles/' + item.article.id + '/reader-view', {
                article_sub_id: item.id
            }).then(function (resp) {
                delete state.fullArticleRequests[key];
                if (!(resp && resp.code === 9999 && resp.result && resp.result.article)) {
                    throw new Error(resp && resp.msg ? resp.msg : '全文加载失败');
                }
                state.fullArticleCache[key] = resp.result.article;
                return resp.result.article;
            }).catch(function (error) {
                delete state.fullArticleRequests[key];
                throw error;
            });
            return state.fullArticleRequests[key];
        }

        function loadPreviewText(item) {
            var itemId = Number(item.id);
            loadFullArticle(item).then(function (full) {
                var active = currentItem();
                if (!active || Number(active.id) !== itemId) return;
                var text = String(full.plain_text || articleSummary(item.article)).replace(/\s+/g, ' ').trim();
                if (text.length > 12000) text = text.slice(0, 12000) + '…';
                $('#v2SummaryTrack').text(text || articleSummary(item.article));
                setTimeout(startPreviewScroll, 60);
            }).catch(function () {
                setTimeout(startPreviewScroll, 60);
            });
        }

        function startDwellTimer(item) {
            clearTimeout(state.dwellTimer);
            if (!item || item.status !== 'unread') return;
            var itemId = Number(item.id);
            state.dwellTimer = setTimeout(function () {
                var active = currentItem();
                if (!active || Number(active.id) !== itemId || active.status !== 'unread' || state.readerOpen) return;
                updateStatus('read', { silent: true, item: active });
            }, 3000);
        }

        function renderCurrent() {
            clearTimeout(state.dwellTimer);
            stopPreviewScroll();
            var item = currentItem();
            if (!item || !item.article) {
                $('#v2Card').hide();
                $('#v2EmptyState').removeClass('hidden').html('<div class="text-xl font-bold mb-2">这里暂时没有文章</div><div class="mb-5">可以扩大时间范围，或者切换到全部文章。</div><button type="button" class="v2-primary-btn" id="v2ShowAllTime">查看全部时间</button>');
                $('.v2-rail, .v2-swipe-hint').hide();
                $('#v2Position').text('0 / 0');
                $('#v2Progress').css('width', '0');
                return;
            }

            $('#v2EmptyState').addClass('hidden').empty();
            $('#v2Card').show();
            $('.v2-rail, .v2-swipe-hint').show();
            var article = item.article;
            var profile = article.ai_profile || {};
            var tags = Array.isArray(profile.tags) ? profile.tags.slice(0, 4) : [];
            var tagsHtml = tags.map(function (tag) { return '<span class="v2-tag"># ' + escapeHtml(tag) + '</span>'; }).join('');
            var feedName = article.feed && article.feed.feed_name ? article.feed.feed_name : '未知订阅';
            var imageUrl = article.image_url || '';

            $('#v2Feed').text(feedName);
            $('#v2Category').text(articleCategory(article));
            $('#v2Published').text(formatPublished(article.published));
            $('#v2Title').text(article.subject || '无标题');
            $('#v2Summary').scrollTop(0);
            $('#v2SummaryTrack').text(articleSummary(article));
            $('#v2Tags').html(tagsHtml);
            $('#v2WordCount').text(Number(article.word_count || 0).toLocaleString() + ' 字');
            $('#v2ReadTime').text('预计 ' + Math.max(1, Number(article.estimated_read_minutes || 1)) + ' 分钟');
            $('#v2Position').text((state.index + 1) + ' / ' + state.items.length);
            $('#v2Progress').css('width', (((state.index + 1) / Math.max(1, state.items.length)) * 100).toFixed(1) + '%');
            $('#v2Card').toggleClass('is-read', item.status === 'read').removeClass('enter-next enter-prev');
            void document.getElementById('v2Card').offsetWidth;
            $('#v2Card').addClass(state.direction === 'prev' ? 'enter-prev' : 'enter-next');
            syncActionButtons(item);

            if (imageUrl) {
                $('#v2BackgroundImage').attr('src', imageUrl);
                $('#v2Background').addClass('has-image');
            } else {
                $('#v2BackgroundImage').attr('src', '');
                $('#v2Background').removeClass('has-image');
            }

            setTimeout(startPreviewScroll, 60);
            loadPreviewText(item);
            startDwellTimer(item);
            if (state.index >= state.items.length - 4) loadMore();
        }

        function loadArticles(reset) {
            if (state.loading) return;
            state.loading = true;
            clearTimeout(state.dwellTimer);
            stopPreviewScroll();
            if (reset) {
                if (state.readerOpen) closeReader();
                state.page = 1;
                state.index = 0;
                state.items = [];
                $('#v2Title').text('正在准备你的沉浸阅读流…');
                $('#v2SummaryTrack').text('正在加载当前文章文本…');
            }
            apiRequest('GET', '/articles', {
                status: state.status,
                time_range: state.timeRange,
                feed_id: state.feedId,
                keyword: state.keyword,
                page_count: state.pageCount,
                page: state.page,
                mode: 'simple'
            }).then(function (resp) {
                state.loading = false;
                if (!(resp && resp.code === 9999 && resp.result)) throw new Error(resp && resp.msg ? resp.msg : '加载文章失败');
                state.items = Array.isArray(resp.result.articles) ? resp.result.articles : [];
                state.feeds = Array.isArray(resp.result.feeds) ? resp.result.feeds : [];
                state.pagination = resp.result.pagination || null;
                renderFeeds();
                var requestedId = Number(query.get('article_sub_id') || 0);
                if (requestedId) {
                    var found = state.items.findIndex(function (item) { return Number(item.id) === requestedId; });
                    if (found >= 0) state.index = found;
                }
                renderCurrent();
            }).catch(function (error) {
                state.loading = false;
                $('#v2Card').hide();
                $('.v2-rail, .v2-swipe-hint').hide();
                $('#v2EmptyState').removeClass('hidden').html('<div class="text-xl font-bold mb-2">内容流加载失败</div><div>' + escapeHtml(error && error.message ? error.message : '请稍后重试') + '</div>');
            });
        }

        function loadMore() {
            if (state.loadingMore || !state.pagination || !state.pagination.has_more_pages) return;
            state.loadingMore = true;
            var nextPage = Number(state.pagination.current_page || state.page) + 1;
            apiRequest('GET', '/articles', {
                status: state.status,
                time_range: state.timeRange,
                feed_id: state.feedId,
                keyword: state.keyword,
                page_count: state.pageCount,
                page: nextPage,
                mode: 'simple'
            }).then(function (resp) {
                state.loadingMore = false;
                if (!(resp && resp.code === 9999 && resp.result)) return;
                var extra = Array.isArray(resp.result.articles) ? resp.result.articles : [];
                state.items = state.items.concat(extra);
                state.pagination = resp.result.pagination || state.pagination;
                state.page = nextPage;
                $('#v2Position').text((state.index + 1) + ' / ' + state.items.length);
            }).catch(function () { state.loadingMore = false; });
        }

        function move(direction) {
            if (state.readerOpen || $('#v2FilterSheet').hasClass('active')) return;
            var target = state.index + direction;
            if (target < 0) { showToast('已经是第一篇'); return; }
            if (target >= state.items.length) {
                if (state.pagination && state.pagination.has_more_pages) {
                    loadMore();
                    showToast('正在加载更多文章');
                } else {
                    showToast('已经刷完当前内容流');
                }
                return;
            }
            state.direction = direction > 0 ? 'next' : 'prev';
            state.index = target;
            renderCurrent();
        }

        function updateStatus(nextStatus, options) {
            var settings = options || {};
            var item = settings.item || currentItem();
            if (!item) return $.Deferred().reject().promise();
            return apiRequest('POST', '/articles/status/' + item.id, { status: nextStatus }).then(function (resp) {
                if (!(resp && resp.code === 9999)) throw new Error(resp && resp.msg ? resp.msg : '状态更新失败');
                item.status = nextStatus;
                $('#v2Card').toggleClass('is-read', nextStatus === 'read');
                syncActionButtons(item);
                if (!settings.silent) showToast(nextStatus === 'star' ? '已收藏' : (nextStatus === 'read_later' ? '已加入稍后阅读' : (nextStatus === 'unread' ? '已恢复未读' : '已标记已读')));
            }).catch(function () { if (!settings.silent) showToast('状态更新失败，请稍后重试'); });
        }

        function openReader() {
            var item = currentItem();
            if (!item || !item.article || state.readerOpen) return;
            clearTimeout(state.dwellTimer);
            stopPreviewScroll();
            var article = item.article;
            var requestId = ++state.readerRequestId;
            state.readerOpen = true;
            $('#v2Stream, #v2Card').addClass('reading-mode');
            $('#v2ReaderFeed').text(article.feed && article.feed.feed_name ? article.feed.feed_name : '未知订阅');
            $('#v2ReaderTitle').text(article.subject || '无标题');
            $('#v2ReaderMeta').text(formatPublished(article.published) + ' · ' + Number(article.word_count || 0).toLocaleString() + ' 字 · 预计 ' + Math.max(1, Number(article.estimated_read_minutes || 1)) + ' 分钟');
            $('#v2ReaderOrigin').attr('href', article.url || '#');
            $('#v2ReaderContent').html('<div class="v2-inline-loading"><i class="fas fa-spinner fa-spin mr-2"></i>加载全文中…</div>');
            $('#v2ReaderScroll').scrollTop(0);
            if (item.status === 'unread') updateStatus('read', { silent: true, item: item });
            syncActionButtons(item);

            loadFullArticle(item).then(function (full) {
                if (requestId !== state.readerRequestId) return;
                $('#v2ReaderContent').html(full.formatted_content || full.content || '<p>暂无正文，请点击右上角原文链接查看。</p>');
            }).catch(function (error) {
                if (requestId !== state.readerRequestId) return;
                $('#v2ReaderContent').html('<div class="v2-inline-loading">' + escapeHtml(error && error.message ? error.message : '全文加载失败') + '</div>');
            });
        }

        function closeReader() {
            if (!state.readerOpen) return;
            state.readerRequestId += 1;
            state.readerOpen = false;
            $('#v2Stream, #v2Card').removeClass('reading-mode');
            $('#v2ReaderScroll').scrollTop(0);
            setTimeout(startPreviewScroll, 80);
        }

        function openFilters() { syncFilterLabels(); $('#v2FilterSheet').addClass('active'); }
        function closeFilters() { $('#v2FilterSheet').removeClass('active'); }

        $('#v2BackBtn').on('click', function () { window.location.href = '/articles'; });
        $('#v2FilterBtn, #v2StatusChip, #v2TimeChip').on('click', openFilters);
        $('#v2FilterClose, #v2FilterBackdrop').on('click', closeFilters);
        $('#v2ResetFilter').on('click', function () {
            $('#v2StatusFilter').val('unread');
            $('#v2TimeFilter').val('6h');
            $('#v2FeedFilter').val('');
            $('#v2KeywordFilter').val('');
        });
        $('#v2ApplyFilter').on('click', function () {
            state.status = $('#v2StatusFilter').val() || 'unread';
            state.timeRange = $('#v2TimeFilter').val() || '6h';
            state.feedId = $('#v2FeedFilter').val() || '';
            state.keyword = $.trim($('#v2KeywordFilter').val() || '');
            syncFilterLabels();
            closeFilters();
            loadArticles(true);
        });

        $(document).on('click', '#v2ShowAllTime', function () { state.timeRange = 'all'; syncFilterLabels(); loadArticles(true); });
        $('#v2Card').on('click', function (event) {
            if (state.touchMoving) {
                state.touchMoving = false;
                return;
            }
            event.stopPropagation();
            openReader();
        });
        $('#v2ReadBtn').on('click', function (event) { event.stopPropagation(); openReader(); });
        $('#v2StarBtn, #v2ReaderStar').on('click', function (event) { event.stopPropagation(); updateStatus('star'); });
        $('#v2LaterBtn, #v2ReaderLater').on('click', function (event) { event.stopPropagation(); updateStatus('read_later'); });
        $('#v2ReadStateBtn').on('click', function (event) {
            event.stopPropagation();
            var item = currentItem();
            updateStatus(item && item.status === 'read' ? 'unread' : 'read');
        });
        $('#v2NextBtn').on('click', function (event) { event.stopPropagation(); move(1); });
        $('#v2ReaderClose').on('click', function (event) { event.stopPropagation(); closeReader(); });
        $('#v2ReaderNext').on('click', function (event) { event.stopPropagation(); closeReader(); move(1); });

        $('#v2Stage').on('touchstart', function (event) {
            var touch = event.originalEvent.touches && event.originalEvent.touches[0];
            if (!touch) return;
            state.touchStartY = touch.clientY;
            state.touchStartX = touch.clientX;
            state.touchMoving = false;
        }).on('touchmove', function (event) {
            var touch = event.originalEvent.touches && event.originalEvent.touches[0];
            if (!touch) return;
            if (Math.abs(touch.clientY - state.touchStartY) > 12) state.touchMoving = true;
        }).on('touchend', function (event) {
            var touch = event.originalEvent.changedTouches && event.originalEvent.changedTouches[0];
            if (!touch) return;
            var diffY = touch.clientY - state.touchStartY;
            var diffX = touch.clientX - state.touchStartX;
            if (Math.abs(diffY) < 58 || Math.abs(diffY) <= Math.abs(diffX)) return;
            move(diffY < 0 ? 1 : -1);
        });

        $('#v2Stream').on('wheel', function (event) {
            if (state.wheelLocked || Math.abs(event.originalEvent.deltaY) < 18) return;
            state.wheelLocked = true;
            move(event.originalEvent.deltaY > 0 ? 1 : -1);
            setTimeout(function () { state.wheelLocked = false; }, 620);
        });

        $(document).on('keydown', function (event) {
            if (event.target && /input|textarea|select/i.test(event.target.tagName)) return;
            if (event.key === 'Escape') {
                if (state.readerOpen) closeReader();
                else closeFilters();
                return;
            }
            if (state.readerOpen) return;
            if (event.key === 'ArrowDown' || event.key === 'PageDown') { event.preventDefault(); move(1); }
            if (event.key === 'ArrowUp' || event.key === 'PageUp') { event.preventDefault(); move(-1); }
            if (event.key === 'Enter') openReader();
        });

        syncFilterLabels();
        loadArticles(true);
    });
</script>
@endsection
