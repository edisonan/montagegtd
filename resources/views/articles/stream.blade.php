@extends('layouts.app')

@section('title', '沉浸刷文 - 蒙太奇')
@section('description', '像刷短视频一样阅读文章，支持自动滚动、浏览器朗读和背景音乐')

@section('content')
<style>
    .stream-shell {
        min-height: calc(100vh - 64px);
        background:
            radial-gradient(circle at top left, rgba(255, 122, 89, 0.28), transparent 28%),
            radial-gradient(circle at top right, rgba(0, 184, 148, 0.18), transparent 24%),
            linear-gradient(180deg, #f4efe6 0%, #f6f7fb 42%, #eef3f8 100%);
        color: #102033;
    }

    .stream-layout {
        max-width: 1320px;
        margin: 0 auto;
        padding: 20px 16px 32px;
    }

    .stream-stage {
        position: relative;
        min-height: calc(100vh - 92px);
        border-radius: 28px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 30px 90px rgba(16, 32, 51, 0.12);
        backdrop-filter: blur(18px);
    }

    .stream-stage.chrome-hidden .stream-topbar,
    .stream-stage.chrome-hidden .stream-progress-wrap,
    .stream-stage.chrome-hidden .stream-side-rail {
        opacity: 0;
        pointer-events: none;
    }

    .stream-background {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(135deg, rgba(255,255,255,0.88), rgba(245,248,252,0.76)),
            var(--stream-bg, linear-gradient(135deg, #fdf2e9, #edf4fb));
        transform: scale(1.02);
    }

    .stream-main {
        position: relative;
        z-index: 1;
        height: calc(100vh - 92px);
        display: grid;
        grid-template-rows: auto auto 1fr auto;
    }

    .stream-main::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(255,255,255,0.16), transparent 14%, transparent 86%, rgba(255,255,255,0.12));
        opacity: 0.7;
    }

    .stream-topbar {
        padding: 6px 10px 4px;
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: center;
        transition: opacity 0.2s ease;
    }

    .stream-badges,
    .stream-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .stream-pill,
    .stream-action-btn {
        border: 0;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .stream-pill {
        background: transparent;
        color: #5b6978;
        padding: 0;
        font-size: 11px;
        font-weight: 500;
        border-radius: 0;
    }

    .stream-badges {
        gap: 8px;
    }

    .stream-badges .stream-pill::after {
        content: "/";
        margin-left: 8px;
        color: #d2d8df;
    }

    .stream-badges .stream-pill:last-child::after {
        display: none;
    }

    .stream-action-btn {
        background: transparent;
        color: #223243;
        box-shadow: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .stream-action-btn.icon-only {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .stream-action-btn:hover,
    .stream-action-btn.active {
        transform: translateY(-1px);
        background: rgba(16, 32, 51, 0.08);
        color: #102033;
        box-shadow: none;
    }

    .stream-progress-wrap {
        padding: 0 8px 2px;
        transition: opacity 0.2s ease;
    }

    .stream-progress-meta {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: #9aa6b2;
        margin-bottom: 2px;
        opacity: 0.7;
    }

    .stream-progress-track {
        height: 6px;
        width: 100%;
        border-radius: 999px;
        background: rgba(16, 32, 51, 0.08);
        overflow: hidden;
    }

    .stream-progress-bar {
        height: 100%;
        width: 0;
        border-radius: 999px;
        background: linear-gradient(90deg, #ff7a59, #ffb347);
        transition: width 0.16s ease-out;
    }

    .stream-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 44px;
        gap: 6px;
        min-height: 0;
        padding: 0 4px 8px 10px;
    }

    .stream-article-wrap {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-height: 0;
    }

    .stream-article-head {
        padding-right: 4px;
    }

    .stream-feed {
        font-size: 11px;
        color: #95a1ad;
        margin-bottom: 4px;
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .stream-title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(18px, 2.2vw, 24px);
        line-height: 1.35;
        letter-spacing: -0.01em;
        color: #13233a;
        margin: 0;
        font-weight: 700;
    }

    .stream-content-panel {
        min-height: 0;
        border-radius: 16px;
        background: rgba(255,255,255,0.86);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 12px 36px rgba(16, 32, 51, 0.06);
        border: 1px solid rgba(255,255,255,0.72);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .stream-content-panel.animating-next {
        animation: streamSlideNext 320ms cubic-bezier(0.22, 0.8, 0.24, 1);
    }

    .stream-content-panel.animating-prev {
        animation: streamSlidePrev 320ms cubic-bezier(0.22, 0.8, 0.24, 1);
    }

    .stream-content-scroll {
        min-height: 0;
        overflow-y: auto;
        padding: 12px 14px 18px;
        scroll-behavior: smooth;
    }

    .stream-content-scroll.raw-mode {
        font-size: 16px;
        line-height: 1.78;
        color: #293847;
    }

    .stream-content-scroll h2,
    .stream-content-scroll h3 {
        color: #102033;
        line-height: 1.3;
        margin: 1.6em 0 0.8em;
        font-weight: 700;
    }

    .stream-content-scroll h2 {
        font-size: 1.35rem;
    }

    .stream-content-scroll h3 {
        font-size: 1.08rem;
    }

    .stream-content-scroll p {
        margin-bottom: 0.95em;
    }

    .stream-content-scroll ul,
    .stream-content-scroll ol {
        padding-left: 1.4em;
        margin: 1em 0 1.2em;
    }

    .stream-content-scroll blockquote {
        margin: 1.1em 0;
        padding: 10px 12px;
        border-left: 4px solid #ff7a59;
        background: #fff8f2;
        border-radius: 0 12px 12px 0;
        color: #5f4b3d;
    }

    .stream-content-scroll img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 0.9em 0;
    }

    .stream-side-rail {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: center;
        padding-top: 8px;
        transition: opacity 0.2s ease;
        justify-self: end;
        margin-right: 2px;
    }

    .stream-rail-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 0;
        background: transparent;
        color: #223243;
        box-shadow: none;
        font-size: 13px;
        transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .stream-rail-btn:hover,
    .stream-rail-btn.active {
        background: rgba(16, 32, 51, 0.08);
        color: #102033;
        transform: translateY(-2px);
    }

    .stream-rail-btn.status-active {
        background: rgba(255, 122, 89, 0.16);
        color: #d9480f;
    }

    .stream-rail-btn.status-active:hover {
        background: rgba(255, 122, 89, 0.22);
        color: #c2410c;
    }

    .panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #13233a;
        margin-bottom: 10px;
    }

    .stream-music-card,
    .stream-outline-card,
    .stream-tip-card {
        border-radius: 22px;
        padding: 16px;
        background: rgba(248, 250, 252, 0.84);
        border: 1px solid rgba(16, 32, 51, 0.06);
    }

    .stream-music-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 230px;
        overflow-y: auto;
    }

    .stream-music-item {
        width: 100%;
        border: 0;
        border-radius: 16px;
        padding: 12px;
        text-align: left;
        background: #fff;
        color: #223243;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 8px 22px rgba(16, 32, 51, 0.05);
    }

    .stream-music-item.active {
        background: #102033;
        color: #fff;
    }

    .stream-music-item:hover {
        transform: translateY(-1px);
    }

    .stream-outline-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-left: 18px;
        color: #445466;
    }

    .stream-slider {
        width: 100%;
    }

    .stream-loading,
    .stream-empty {
        height: calc(100vh - 170px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #5c6978;
        font-size: 15px;
    }

    .stream-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 70;
        background: #102033;
        color: #fff;
        padding: 12px 16px;
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(16, 32, 51, 0.22);
        font-size: 13px;
        display: none;
    }

    .stream-peek {
        position: absolute;
        left: 16px;
        right: 56px;
        bottom: 14px;
        z-index: 4;
        pointer-events: none;
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 0.18s ease, transform 0.18s ease;
    }

    .stream-peek.active {
        opacity: 1;
        transform: translateY(0);
    }

    .stream-peek-card {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: 100%;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(16, 32, 51, 0.86);
        color: #f8fafc;
        font-size: 12px;
        line-height: 1.3;
        box-shadow: 0 14px 34px rgba(16, 32, 51, 0.18);
        backdrop-filter: blur(8px);
    }

    @keyframes streamSlideNext {
        from {
            opacity: 0.25;
            transform: translateX(32px) scale(0.992);
        }
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    @keyframes streamSlidePrev {
        from {
            opacity: 0.25;
            transform: translateX(-32px) scale(0.992);
        }
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    .stream-help {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .stream-help-btn {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        border: 0;
        background: transparent;
        color: #223243;
        box-shadow: none;
        font-size: 11px;
    }

    .stream-help-pop {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 280px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(16, 32, 51, 0.95);
        color: #f8fafc;
        font-size: 13px;
        line-height: 1.7;
        box-shadow: 0 20px 46px rgba(16, 32, 51, 0.25);
        display: none;
    }

    .stream-help-pop.active {
        display: block;
    }

    .stream-stage:hover .stream-progress-meta,
    .stream-stage:hover .stream-feed,
    .stream-stage:hover .stream-badges .stream-pill {
        opacity: 1;
        color: #607080;
    }

    .stream-stage:hover .stream-badges .stream-pill::after {
        color: #b8c2cc;
    }

    :fullscreen .stream-shell,
    :-webkit-full-screen .stream-shell {
        background: #eef3f8;
    }

    body.stream-pseudo-fullscreen {
        overflow: hidden;
        background: #eef3f8;
    }

    :fullscreen .stream-stage,
    :-webkit-full-screen .stream-stage,
    body.stream-pseudo-fullscreen .stream-stage {
        min-height: 100vh;
        border-radius: 0;
        border: 0;
        box-shadow: none;
        backdrop-filter: none;
    }

    :fullscreen .stream-main,
    :-webkit-full-screen .stream-main,
    body.stream-pseudo-fullscreen .stream-main {
        height: 100vh;
    }

    :fullscreen .stream-topbar,
    :-webkit-full-screen .stream-topbar,
    body.stream-pseudo-fullscreen .stream-topbar {
        display: none;
    }

    :fullscreen .stream-progress-wrap,
    :-webkit-full-screen .stream-progress-wrap,
    body.stream-pseudo-fullscreen .stream-progress-wrap {
        display: none;
    }

    :fullscreen .stream-body,
    :-webkit-full-screen .stream-body,
    body.stream-pseudo-fullscreen .stream-body {
        padding: 0;
        height: 100vh;
        grid-template-columns: minmax(0, 1fr) 40px;
        gap: 4px;
    }

    :fullscreen .stream-content-panel,
    :-webkit-full-screen .stream-content-panel,
    body.stream-pseudo-fullscreen .stream-content-panel {
        border-radius: 0;
        border-left: 0;
        border-bottom: 0;
        border-top: 0;
        box-shadow: none;
        background: rgba(255,255,255,0.94);
    }

    :fullscreen .stream-article-head,
    :-webkit-full-screen .stream-article-head,
    body.stream-pseudo-fullscreen .stream-article-head,
    :fullscreen .stream-peek,
    :-webkit-full-screen .stream-peek,
    body.stream-pseudo-fullscreen .stream-peek {
        display: none;
    }

    :fullscreen .stream-article-wrap,
    :-webkit-full-screen .stream-article-wrap,
    body.stream-pseudo-fullscreen .stream-article-wrap {
        gap: 0;
    }

    :fullscreen .stream-side-rail,
    :-webkit-full-screen .stream-side-rail,
    body.stream-pseudo-fullscreen .stream-side-rail {
        display: flex;
        opacity: 0.2;
        transition: opacity 0.2s ease;
        margin-right: 0;
        padding-top: 10px;
    }

    :fullscreen .stream-side-rail:hover,
    :-webkit-full-screen .stream-side-rail:hover,
    body.stream-pseudo-fullscreen .stream-side-rail:hover {
        opacity: 1;
    }

    :fullscreen .stream-content-scroll,
    :-webkit-full-screen .stream-content-scroll,
    body.stream-pseudo-fullscreen .stream-content-scroll {
        padding: 18px 20px 28px;
    }

    :fullscreen .stream-content-scroll.raw-mode,
    :-webkit-full-screen .stream-content-scroll.raw-mode,
    body.stream-pseudo-fullscreen .stream-content-scroll.raw-mode {
        font-size: 17px;
        line-height: 1.82;
    }

    .stream-modal {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: none;
    }

    .stream-modal.active {
        display: block;
    }

    .stream-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(16, 32, 51, 0.42);
        backdrop-filter: blur(6px);
    }

    .stream-modal-panel {
        position: relative;
        z-index: 1;
        max-width: 540px;
        margin: 48px auto 0;
        background: rgba(255,255,255,0.94);
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,0.72);
        box-shadow: 0 28px 80px rgba(16, 32, 51, 0.18);
        padding: 22px;
    }

    @media (max-width: 768px) {
        .stream-layout {
            padding: 12px 0 18px;
            gap: 12px;
        }

        .stream-stage {
            border-radius: 0;
        }

        .stream-main {
            height: calc(100vh - 64px);
        }

        .stream-topbar {
            padding: 4px 6px 3px;
        }

        .stream-progress-wrap {
            padding: 0 4px 2px;
        }

        .stream-badges {
            gap: 6px;
        }

        .stream-badges .stream-pill::after {
            margin-left: 6px;
        }

        .stream-body {
            padding: 0 2px 6px 6px;
            grid-template-columns: minmax(0, 1fr) 36px;
        }

        .stream-title {
            font-size: 18px;
        }

        .stream-content-scroll {
            padding: 10px 10px 14px;
            font-size: 14px;
        }

        .stream-content-scroll.raw-mode {
            font-size: 14px;
            line-height: 1.72;
        }

        .stream-content-scroll h2 {
            font-size: 1.15rem;
        }

        .stream-content-scroll h3 {
            font-size: 1rem;
        }

        .stream-rail-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            font-size: 12px;
        }

        .stream-modal-panel {
            margin: 18px 12px 0;
            max-width: none;
        }

        .stream-action-btn.icon-only,
        .stream-help-btn {
            width: 30px;
            height: 30px;
            font-size: 12px;
        }

        :fullscreen .stream-content-scroll,
        :-webkit-full-screen .stream-content-scroll {
            padding: 12px 12px 20px;
        }

        :fullscreen .stream-content-scroll.raw-mode,
        :-webkit-full-screen .stream-content-scroll.raw-mode {
            font-size: 15px;
            line-height: 1.78;
        }

        :fullscreen .stream-body,
        :-webkit-full-screen .stream-body {
            grid-template-columns: minmax(0, 1fr) 42px;
            gap: 2px;
        }
    }
</style>

<div class="stream-shell">
    <div class="stream-layout">
        <section class="stream-stage" id="streamStage">
            <div class="stream-background" id="streamBackground"></div>
            <div class="stream-main" id="streamMain">
                <div class="stream-topbar">
                    <div class="stream-badges">
                        <span class="stream-pill" id="streamFeedName">加载中</span>
                        <span class="stream-pill" id="streamPosition">0 / 0</span>
                        <span class="stream-pill" id="streamReadTime">0 分钟</span>
                    </div>
                    <div class="stream-actions">
                        <div class="stream-help">
                            <button type="button" class="stream-help-btn" id="streamHelpBtn" title="查看说明">
                                <i class="fas fa-question"></i>
                            </button>
                            <div class="stream-help-pop" id="streamHelpPop">
                                左右滑动切换上一篇/下一篇，避免竖向阅读正文时误切页。
                                <br>手动滚动正文会暂停自动滚动。
                                <br>点击音乐按钮可打开阅读设置和背景音乐列表。
                            </div>
                        </div>
                        <button type="button" class="stream-action-btn icon-only" id="backToListBtn" title="返回列表">
                            <i class="fas fa-list"></i>
                        </button>
                        <button type="button" class="stream-action-btn icon-only" id="fullscreenBtn" title="全屏">
                            <i class="fas fa-expand"></i>
                        </button>
                        <a href="#" target="_blank" class="stream-action-btn icon-only" id="originLinkTop" title="原文">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>

                <div class="stream-progress-wrap">
                    <div class="stream-progress-meta">
                        <span id="streamPublishTime">-</span>
                        <span id="streamProgressText">阅读进度 0%</span>
                    </div>
                    <div class="stream-progress-track">
                        <div class="stream-progress-bar" id="streamProgressBar"></div>
                    </div>
                </div>

                <div class="stream-body" id="streamBody">
                    <div class="stream-article-wrap">
                        <div class="stream-article-head">
                            <div class="stream-feed">
                                <span id="streamFeedHost">-</span>
                                <span id="streamArticleStatus">未读</span>
                            </div>
                            <h1 class="stream-title" id="streamTitle">加载文章中...</h1>
                        </div>

                        <div class="stream-content-panel" id="streamContentPanel">
                            <div class="stream-content-scroll raw-mode" id="streamContentScroll">
                                <div class="stream-loading"><i class="fas fa-spinner fa-spin mr-2"></i>加载中...</div>
                            </div>
                        </div>
                    </div>

                    <div class="stream-side-rail">
                        <button type="button" class="stream-rail-btn" id="prevArticleBtn" title="上一篇">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="nextArticleBtn" title="下一篇">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="autoScrollBtn" title="自动滚动">
                            <i class="fas fa-forward"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="speechBtn" title="浏览器朗读">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="musicSettingsBtn" title="音乐设置">
                            <i class="fas fa-music"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="markReadBtn" title="标记已读">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="starBtn" title="收藏">
                            <i class="far fa-star"></i>
                        </button>
                        <button type="button" class="stream-rail-btn" id="readLaterBtn" title="稍后阅读">
                            <i class="far fa-clock"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<div class="stream-peek" id="streamPeek">
    <div class="stream-peek-card">
        <i class="fas fa-arrow-right" id="streamPeekIcon"></i>
        <span id="streamPeekText">下一篇预览</span>
    </div>
</div>

<div class="stream-modal" id="musicModal">
    <div class="stream-modal-backdrop" id="musicModalBackdrop"></div>
    <div class="stream-modal-panel">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="panel-title mb-1">阅读设置</div>
                <div class="text-sm text-gray-600" id="playlistSourceText">正在加载歌单...</div>
            </div>
            <button type="button" class="stream-action-btn" id="musicModalCloseBtn">
                <i class="fas fa-times mr-1"></i>关闭
            </button>
        </div>
        <div class="stream-outline-card mb-4">
            <div class="panel-title">自动滚动</div>
            <div class="mb-2 text-sm text-gray-600">速度：<span id="scrollSpeedText">中</span></div>
            <input type="range" min="1" max="3" value="2" class="stream-slider" id="scrollSpeedRange">
        </div>
        <div class="stream-outline-card mb-4">
            <div class="panel-title">自动已读</div>
            <div class="mb-2 text-sm text-gray-600">切下一篇前，当前文章阅读进度达到：<span id="readThresholdText">20%</span></div>
            <input type="range" min="0" max="100" step="10" value="20" class="stream-slider" id="readThresholdRange">
        </div>
        <div class="stream-music-card">
            <div class="flex items-center gap-2 mb-3">
                <button type="button" class="stream-action-btn" id="bgmToggleBtn"><i class="fas fa-play mr-1"></i>播放</button>
                <button type="button" class="stream-action-btn" id="bgmNextBtn"><i class="fas fa-forward-step mr-1"></i>下一首</button>
            </div>
            <input type="range" min="0" max="100" value="28" class="stream-slider mb-3" id="bgmVolume">
            <audio id="bgmAudio" preload="none"></audio>
            <div class="stream-music-list" id="playlistList"></div>
        </div>
    </div>
</div>

<div class="stream-toast" id="streamToast"></div>

<script>
    $(function () {
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : function () { return Promise.reject(new Error('API客户端未初始化')); };

        var query = new URLSearchParams(window.location.search);
        var state = {
            status: query.get('status') || 'unread',
            feedId: query.get('feed_id') || '',
            pageCount: Number(query.get('page_count') || 10),
            page: Number(query.get('page') || 1),
            currentIndex: 0,
            pendingDirection: 'next',
            chromeHidden: false,
            pseudoFullscreen: false,
            currentProgressRatio: 0,
            readThresholdRatio: Number(localStorage.getItem('articleStreamReadThresholdRatio') || 0.2),
            articleSubs: [],
            pagination: null,
            loadingNextPage: false,
            currentReaderData: null,
            preloadedReaderMap: {},
            autoScrollTimer: null,
            scrollSpeed: Number(localStorage.getItem('articleStreamScrollSpeed') || 2),
            speechState: {
                utterance: null,
                active: false
            },
            playlist: [],
            currentTrackIndex: 0,
            playlistSource: 'fallback_demo',
            touchStartX: 0,
            touchStartY: 0,
            touchMoved: false
        };

        var speedMap = {
            1: { label: '慢', step: 0.4 },
            2: { label: '中', step: 0.8 },
            3: { label: '快', step: 1.4 }
        };

        function showToast(message) {
            var $toast = $('#streamToast');
            $toast.stop(true, true).text(message).fadeIn(120);
            clearTimeout($toast.data('timer'));
            $toast.data('timer', setTimeout(function () {
                $toast.fadeOut(180);
            }, 2200));
        }

        function showPeek(text, direction) {
            var $peek = $('#streamPeek');
            $('#streamPeekText').text(text || '');
            $('#streamPeekIcon').attr('class', direction === 'prev' ? 'fas fa-arrow-left' : 'fas fa-arrow-right');
            $peek.addClass('active');
            clearTimeout($peek.data('timer'));
            $peek.data('timer', setTimeout(function () {
                $peek.removeClass('active');
            }, 1200));
        }

        function setChromeHidden(hidden) {
            state.chromeHidden = !!hidden;
            $('#streamStage').toggleClass('chrome-hidden', state.chromeHidden);
        }

        function getBackListUrl() {
            var params = new URLSearchParams();
            params.set('status', state.status);
            if (state.feedId) params.set('feed_id', state.feedId);
            if (state.pageCount) params.set('page_count', String(state.pageCount));
            if (state.page) params.set('page', String(state.page));
            return '/articles?' + params.toString();
        }

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
            });
        }

        function getFeedGradient(article) {
            var palette = [
                'linear-gradient(135deg, rgba(255,122,89,0.24), rgba(255,192,120,0.16))',
                'linear-gradient(135deg, rgba(0,184,148,0.18), rgba(129,236,236,0.12))',
                'linear-gradient(135deg, rgba(108,92,231,0.16), rgba(162,155,254,0.12))',
                'linear-gradient(135deg, rgba(9,132,227,0.18), rgba(116,185,255,0.12))'
            ];
            var feedId = article && article.feed && article.feed.id ? Number(article.feed.id) : 0;
            return palette[Math.abs(feedId) % palette.length];
        }

        function buildArticleStatusText(status) {
            if (status === 'read') return '已读';
            if (status === 'star') return '已收藏';
            if (status === 'read_later') return '稍后阅读';
            return '未读';
        }

        function buildPlaylistItemHtml(track, index) {
            var activeClass = index === state.currentTrackIndex ? ' active' : '';
            return ''
                + '<button type="button" class="stream-music-item' + activeClass + '" data-track-index="' + index + '">'
                + '<div class="font-semibold">' + escapeHtml(track.title || '未命名曲目') + '</div>'
                + '<div class="text-xs opacity-80 mt-1">' + escapeHtml(track.artist || track.source_type || '未知来源') + '</div>'
                + '</button>';
        }

        function renderPlaylist() {
            if (state.playlistSource === 'bgm_tracks') {
                $('#playlistSourceText').text('当前使用后台配置歌单，可人工筛选 Pixabay 等免版税曲目。');
            } else if (state.playlistSource === 'fallback_demo') {
                $('#playlistSourceText').text('当前使用内置 Demo 歌单。数据库没有启用曲目时会自动兜底。');
            } else {
                $('#playlistSourceText').text('当前使用外部音乐源。');
            }

            if (!state.playlist.length) {
                $('#playlistList').html('<div class="text-sm text-gray-500">暂无可播放曲目，请稍后重试。</div>');
                return;
            }

            var html = '';
            state.playlist.forEach(function (track, index) {
                html += buildPlaylistItemHtml(track, index);
            });
            $('#playlistList').html(html);
        }

        function applyCurrentTrack(autoplay) {
            var track = state.playlist[state.currentTrackIndex];
            var audio = $('#bgmAudio')[0];
            if (!track || !track.audio_url) {
                showToast('当前曲目没有可播放地址');
                return;
            }

            audio.src = track.audio_url;
            audio.volume = Number($('#bgmVolume').val() || 28) / 100;
            renderPlaylist();

            if (autoplay) {
                audio.play().then(function () {
                    $('#bgmToggleBtn').html('<i class="fas fa-pause mr-1"></i>暂停').addClass('active');
                }).catch(function () {
                    showToast('浏览器拦截了自动播放，请手动点击播放');
                });
            }
        }

        function loadPlaylist() {
            apiRequest('GET', '/music/hot-playlist', {}).then(function (resp) {
                if (resp && resp.code === 9999 && resp.result) {
                    state.playlist = Array.isArray(resp.result.playlist) ? resp.result.playlist : [];
                    state.playlistSource = resp.result.source || 'fallback_demo';
                    renderPlaylist();
                    if (state.playlist.length) {
                        applyCurrentTrack(false);
                    }
                    return;
                }
                throw new Error(resp && resp.msg ? resp.msg : '加载歌单失败');
            }).catch(function () {
                state.playlist = [];
                renderPlaylist();
            });
        }

        function syncStatusButtons(articleSub) {
            $('#markReadBtn').toggleClass('active', articleSub.status === 'read').toggleClass('status-active', articleSub.status === 'read');
            $('#starBtn').toggleClass('active', articleSub.status === 'star').toggleClass('status-active', articleSub.status === 'star');
            $('#readLaterBtn').toggleClass('active', articleSub.status === 'read_later').toggleClass('status-active', articleSub.status === 'read_later');
            $('#streamArticleStatus').text(buildArticleStatusText(articleSub.status));
        }

        function renderContent() {
            var readerData = state.currentReaderData;
            if (!readerData || !readerData.article_sub || !readerData.article) {
                $('#streamContentScroll').html('<div class="stream-empty">没有可展示的文章</div>');
                return;
            }

            var article = readerData.article;
            var $content = $('#streamContentScroll');
            $content.removeClass('ai-mode').addClass('raw-mode');
            $content.html(article.formatted_content || '<p>暂无正文</p>');

            updateProgress();
        }

        function playContentTransition() {
            var $panel = $('#streamContentPanel');
            var animationClass = state.pendingDirection === 'prev' ? 'animating-prev' : 'animating-next';
            $panel.removeClass('animating-next animating-prev');
            // Force reflow so repeated same-direction animations still fire.
            void $panel[0].offsetWidth;
            $panel.addClass(animationClass);
            setTimeout(function () {
                $panel.removeClass('animating-next animating-prev');
            }, 260);
        }

        function updateProgress() {
            var $scroll = $('#streamContentScroll');
            var max = $scroll[0].scrollHeight - $scroll.innerHeight();
            var current = $scroll.scrollTop();
            var ratio = max > 0 ? Math.min(1, Math.max(0, current / max)) : 0;
            state.currentProgressRatio = ratio;
            $('#streamProgressBar').css('width', (ratio * 100).toFixed(0) + '%');
            $('#streamProgressText').text('阅读进度 ' + Math.round(ratio * 100) + '%');
        }

        function stopAutoScroll(silent) {
            if (state.autoScrollTimer) {
                clearInterval(state.autoScrollTimer);
                state.autoScrollTimer = null;
            }
            $('#autoScrollBtn').removeClass('active');
            if (!silent) {
                showToast('自动滚动已暂停');
            }
        }

        function startAutoScroll() {
            stopAutoScroll(true);
            var step = speedMap[state.scrollSpeed] ? speedMap[state.scrollSpeed].step : 0.8;
            state.autoScrollTimer = setInterval(function () {
                var $scroll = $('#streamContentScroll');
                var nextTop = $scroll.scrollTop() + step;
                var maxTop = $scroll[0].scrollHeight - $scroll.innerHeight();
                if (nextTop >= maxTop) {
                    $scroll.scrollTop(maxTop);
                    updateProgress();
                    stopAutoScroll(true);
                    showToast('已经到达文末，可继续下滑切换下一篇');
                    return;
                }
                $scroll.scrollTop(nextTop);
                updateProgress();
            }, 16);
            $('#autoScrollBtn').addClass('active');
            showToast('自动滚动已开启');
        }

        function toggleSpeech() {
            if (state.speechState.active) {
                window.speechSynthesis.cancel();
                state.speechState.active = false;
                $('#speechBtn').removeClass('active');
                showToast('朗读已停止');
                return;
            }

            var readerData = state.currentReaderData;
            if (!readerData || !readerData.article) return;
            var article = readerData.article;
            var sourceText = article.plain_text;

            sourceText = String(sourceText || '').replace(/\s+/g, ' ').trim().slice(0, 12000);
            if (!sourceText) {
                showToast('当前没有可朗读的内容');
                return;
            }

            var utterance = new SpeechSynthesisUtterance(sourceText);
            utterance.lang = 'zh-CN';
            utterance.rate = 1;
            utterance.pitch = 1;
            utterance.onend = utterance.onerror = function () {
                state.speechState.active = false;
                $('#speechBtn').removeClass('active');
            };
            state.speechState.utterance = utterance;
            state.speechState.active = true;
            $('#speechBtn').addClass('active');

            var audio = $('#bgmAudio')[0];
            if (!audio.paused) {
                audio.volume = Math.max(0.08, Number($('#bgmVolume').val() || 28) / 200);
            }

            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(utterance);
            showToast('浏览器朗读已开始');
        }

        function syncScrollSpeed() {
            localStorage.setItem('articleStreamScrollSpeed', String(state.scrollSpeed));
            $('#scrollSpeedText').text(speedMap[state.scrollSpeed] ? speedMap[state.scrollSpeed].label : '中');
            var speedLabel = speedMap[state.scrollSpeed] ? speedMap[state.scrollSpeed].label : '中';
            $('#autoScrollBtn').attr('title', '自动滚动（' + speedLabel + '）');
        }

        function syncReadThreshold() {
            var percent = Math.round(state.readThresholdRatio * 100);
            localStorage.setItem('articleStreamReadThresholdRatio', String(state.readThresholdRatio));
            $('#readThresholdRange').val(percent);
            $('#readThresholdText').text(percent + '%');
        }

        function syncFullscreenButton() {
            var active = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || state.pseudoFullscreen);
            $('#fullscreenBtn')
                .toggleClass('active', active)
                .attr('title', active ? '退出全屏' : '全屏')
                .html(active
                    ? '<i class="fas fa-compress"></i>'
                    : '<i class="fas fa-expand"></i>');
        }

        function setPseudoFullscreen(active) {
            state.pseudoFullscreen = !!active;
            $('body').toggleClass('stream-pseudo-fullscreen', state.pseudoFullscreen);
            syncFullscreenButton();
            if (state.pseudoFullscreen) {
                $('#streamContentScroll').scrollTop(0);
                updateProgress();
                setChromeHidden(true);
            } else {
                setChromeHidden(false);
            }
        }

        function toggleFullscreen() {
            var activeElement = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
            if (state.pseudoFullscreen && !activeElement) {
                setPseudoFullscreen(false);
                return;
            }
            if (!activeElement) {
                var target = document.getElementById('streamStage') || document.documentElement;
                var requestMethod = target.requestFullscreen
                    || target.webkitRequestFullscreen
                    || target.webkitRequestFullScreen
                    || target.mozRequestFullScreen
                    || target.msRequestFullscreen;

                if (!requestMethod) {
                    setPseudoFullscreen(true);
                    return;
                }

                try {
                    var result = requestMethod.call(target);
                    if (result && typeof result.catch === 'function') {
                        result.catch(function () {
                            setPseudoFullscreen(true);
                        });
                    }
                } catch (error) {
                    setPseudoFullscreen(true);
                }
                return;
            }

            var exitMethod = document.exitFullscreen
                || document.webkitExitFullscreen
                || document.webkitCancelFullScreen
                || document.mozCancelFullScreen
                || document.msExitFullscreen;

            if (!exitMethod) {
                showToast('当前浏览器不支持退出全屏');
                return;
            }

            try {
                var exitResult = exitMethod.call(document);
                if (exitResult && typeof exitResult.catch === 'function') {
                    exitResult.catch(function () {
                        showToast('退出全屏失败');
                    });
                }
            } catch (error) {
                showToast('退出全屏失败');
            }
        }

        function renderArticleMeta() {
            var readerData = state.currentReaderData;
            if (!readerData) return;

            var article = readerData.article;
            var articleSub = readerData.article_sub;
            var feedName = article.feed && article.feed.feed_name ? article.feed.feed_name : '未知来源';
            var feedHost = article.feed && article.feed.url ? article.feed.url : '#';
            var hostText = '-';

            try {
                hostText = new URL(feedHost).host;
            } catch (e) {}

            $('#streamBackground').css('--stream-bg', getFeedGradient(article));
            $('#streamFeedName').text(feedName);
            $('#streamPosition').text((state.currentIndex + 1) + ' / ' + state.articleSubs.length);
            $('#streamReadTime').text((article.estimated_read_minutes || 1) + ' 分钟');
            $('#streamPublishTime').text(article.published || '-');
            $('#streamFeedHost').text(hostText);
            $('#streamTitle').text(article.subject || '无标题');
            $('#originLinkTop').attr('href', article.url || '#');
            syncStatusButtons(articleSub);
            renderContent();
        }

        function getReaderCacheKey(articleSub) {
            if (!articleSub || !articleSub.article || !articleSub.id) {
                return '';
            }
            return String(articleSub.id) + ':' + String(articleSub.article.id);
        }

        function savePreloadedReader(articleSub, data) {
            var key = getReaderCacheKey(articleSub);
            if (!key) return;
            state.preloadedReaderMap[key] = data;
        }

        function takePreloadedReader(articleSub) {
            var key = getReaderCacheKey(articleSub);
            if (!key || !state.preloadedReaderMap[key]) {
                return null;
            }
            var data = state.preloadedReaderMap[key];
            delete state.preloadedReaderMap[key];
            return data;
        }

        function preloadReaderView(articleSub) {
            var key = getReaderCacheKey(articleSub);
            if (!key || state.preloadedReaderMap[key]) {
                return;
            }

            apiRequest('GET', '/articles/' + articleSub.article.id + '/reader-view', {
                article_sub_id: articleSub.id
            }).then(function (resp) {
                if (resp && resp.code === 9999 && resp.result) {
                    savePreloadedReader(articleSub, resp.result);
                }
            }).catch(function () {
                // Ignore preload failures; foreground load will retry.
            });
        }

        function preloadAdjacentArticles() {
            var prevArticleSub = state.articleSubs[state.currentIndex - 1] || null;
            var nextArticleSub = state.articleSubs[state.currentIndex + 1] || null;
            if (prevArticleSub && prevArticleSub.article) {
                preloadReaderView(prevArticleSub);
            }
            if (nextArticleSub && nextArticleSub.article) {
                preloadReaderView(nextArticleSub);
            } else if (state.pagination && state.pagination.has_more_pages) {
                maybeLoadNextPage().then(function () {
                    var lazyNext = state.articleSubs[state.currentIndex + 1] || null;
                    if (lazyNext && lazyNext.article) {
                        preloadReaderView(lazyNext);
                    }
                });
            }
        }

        function loadReaderView(articleSub) {
            stopAutoScroll(true);
            window.speechSynthesis.cancel();
            state.speechState.active = false;
            $('#speechBtn').removeClass('active');
            state.currentProgressRatio = 0;

            var preloadedReader = takePreloadedReader(articleSub);
            if (preloadedReader) {
                state.currentReaderData = preloadedReader;
                renderArticleMeta();
                $('#streamContentScroll').scrollTop(0);
                updateProgress();
                playContentTransition();
                preloadAdjacentArticles();
                return;
            }

            $('#streamContentScroll').html('<div class="stream-loading"><i class="fas fa-spinner fa-spin mr-2"></i>加载文章中...</div>');
            apiRequest('GET', '/articles/' + articleSub.article.id + '/reader-view', {
                article_sub_id: articleSub.id
            }).then(function (resp) {
                if (!(resp && resp.code === 9999 && resp.result)) {
                    throw new Error(resp && resp.msg ? resp.msg : '加载阅读页失败');
                }
                state.currentReaderData = resp.result;
                renderArticleMeta();
                $('#streamContentScroll').scrollTop(0);
                updateProgress();
                playContentTransition();
                preloadAdjacentArticles();
            }).catch(function (error) {
                $('#streamContentScroll').html('<div class="stream-empty">' + escapeHtml(error && error.message ? error.message : '加载失败') + '</div>');
            });
        }

        function renderCurrentArticle() {
            if (!state.articleSubs.length) {
                $('#streamContentScroll').html('<div class="stream-empty">当前筛选条件下没有文章</div>');
                return;
            }
            var current = state.articleSubs[state.currentIndex];
            loadReaderView(current);
        }

        function maybeLoadNextPage() {
            if (!state.pagination || !state.pagination.has_more_pages || state.loadingNextPage) {
                return $.Deferred().resolve().promise();
            }
            state.loadingNextPage = true;
            return apiRequest('GET', '/articles', {
                status: state.status,
                feed_id: state.feedId,
                page_count: state.pageCount,
                page: Number(state.pagination.current_page || 1) + 1
            }).then(function (resp) {
                state.loadingNextPage = false;
                if (resp && resp.code === 9999 && resp.result) {
                    var extra = Array.isArray(resp.result.articles) ? resp.result.articles : [];
                    state.articleSubs = state.articleSubs.concat(extra);
                    state.pagination = resp.result.pagination || state.pagination;
                }
            }).catch(function () {
                state.loadingNextPage = false;
            });
        }

        function changeArticle(nextIndex) {
            var movingForward = nextIndex > state.currentIndex;
            state.pendingDirection = movingForward ? 'next' : 'prev';
            if (movingForward) {
                markCurrentArticleAsReadSilently();
            }

            if (nextIndex < 0) {
                showToast('已经是第一篇');
                return;
            }

            if (nextIndex >= state.articleSubs.length) {
                maybeLoadNextPage().then(function () {
                    if (nextIndex >= state.articleSubs.length) {
                        showToast('已经没有更多文章了');
                        return;
                    }
                    var nextAfterLoad = state.articleSubs[nextIndex];
                    if (nextAfterLoad && nextAfterLoad.article) {
                        showPeek((movingForward ? '下一篇：' : '上一篇：') + (nextAfterLoad.article.subject || '无标题'), state.pendingDirection);
                    }
                    state.currentIndex = nextIndex;
                    renderCurrentArticle();
                });
                return;
            }

            var targetSub = state.articleSubs[nextIndex];
            if (targetSub && targetSub.article) {
                showPeek((movingForward ? '下一篇：' : '上一篇：') + (targetSub.article.subject || '无标题'), state.pendingDirection);
            }
            state.currentIndex = nextIndex;
            renderCurrentArticle();
        }

        function updateArticleStatus(nextStatus, options) {
            var settings = options || {};
            var articleSub = settings.articleSub || (state.currentReaderData ? state.currentReaderData.article_sub : null);
            if (!articleSub) return $.Deferred().reject().promise();

            return apiRequest('POST', '/articles/status/' + articleSub.id, {
                status: nextStatus
            }).then(function (resp) {
                if (!(resp && resp.code === 9999)) {
                    throw new Error(resp && resp.msg ? resp.msg : '更新状态失败');
                }
                articleSub.status = nextStatus;
                for (var i = 0; i < state.articleSubs.length; i++) {
                    if (state.articleSubs[i] && Number(state.articleSubs[i].id) === Number(articleSub.id)) {
                        state.articleSubs[i].status = nextStatus;
                        break;
                    }
                }
                if (state.currentReaderData && state.currentReaderData.article_sub && Number(state.currentReaderData.article_sub.id) === Number(articleSub.id)) {
                    state.currentReaderData.article_sub.status = nextStatus;
                    syncStatusButtons(state.currentReaderData.article_sub);
                }
                if (!settings.silent) {
                    showToast('文章状态已更新');
                }
            }).catch(function () {
                if (!settings.silent) {
                    showToast('状态更新失败，请稍后重试');
                }
            });
        }

        function markCurrentArticleAsReadSilently() {
            var readerData = state.currentReaderData;
            if (!readerData || !readerData.article_sub) {
                return;
            }
            if (readerData.article_sub.status !== 'unread') {
                return;
            }
            if (state.currentProgressRatio < state.readThresholdRatio) {
                return;
            }
            updateArticleStatus('read', {
                silent: true,
                articleSub: readerData.article_sub
            });
        }

        function loadArticles() {
            apiRequest('GET', '/articles', {
                status: state.status,
                feed_id: state.feedId,
                page_count: state.pageCount,
                page: state.page
            }).then(function (resp) {
                if (!(resp && resp.code === 9999 && resp.result)) {
                    throw new Error(resp && resp.msg ? resp.msg : '加载文章失败');
                }
                state.articleSubs = Array.isArray(resp.result.articles) ? resp.result.articles : [];
                state.pagination = resp.result.pagination || null;

                var requestedSubId = Number(query.get('article_sub_id') || 0);
                if (requestedSubId) {
                    var foundIndex = state.articleSubs.findIndex(function (item) { return Number(item.id) === requestedSubId; });
                    if (foundIndex >= 0) {
                        state.currentIndex = foundIndex;
                    }
                }

                renderCurrentArticle();
            }).catch(function (error) {
                $('#streamContentScroll').html('<div class="stream-empty">' + escapeHtml(error && error.message ? error.message : '加载失败') + '</div>');
            });
        }

        $('#backToListBtn').on('click', function (event) {
            event.stopPropagation();
            window.location.href = getBackListUrl();
        });

        $('#fullscreenBtn').on('click', function () {
            toggleFullscreen();
        });

        $('#streamHelpBtn').on('click', function (event) {
            event.stopPropagation();
            $('#streamHelpPop').toggleClass('active');
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.stream-help').length) {
                $('#streamHelpPop').removeClass('active');
            }
        });

        $('#prevArticleBtn').on('click', function (event) {
            event.stopPropagation();
            changeArticle(state.currentIndex - 1);
        });

        $('#nextArticleBtn').on('click', function (event) {
            event.stopPropagation();
            changeArticle(state.currentIndex + 1);
        });

        $('#autoScrollBtn').on('click', function (event) {
            event.stopPropagation();
            if (state.autoScrollTimer) {
                stopAutoScroll(false);
                return;
            }
            startAutoScroll();
        });

        $('#speechBtn').on('click', function (event) {
            event.stopPropagation();
            toggleSpeech();
        });

        $('#markReadBtn').on('click', function (event) {
            event.stopPropagation();
            updateArticleStatus('read');
        });

        $('#starBtn').on('click', function (event) {
            event.stopPropagation();
            updateArticleStatus('star');
        });

        $('#readLaterBtn').on('click', function (event) {
            event.stopPropagation();
            updateArticleStatus('read_later');
        });

        $('#scrollSpeedRange').val(state.scrollSpeed).on('input change', function () {
            state.scrollSpeed = Number($(this).val() || 2);
            syncScrollSpeed();
            if (state.autoScrollTimer) {
                startAutoScroll();
            }
        });

        $('#readThresholdRange').on('input change', function () {
            state.readThresholdRatio = Math.max(0, Math.min(1, Number($(this).val() || 20) / 100));
            syncReadThreshold();
        });

        $('#streamContentScroll').on('scroll', function () {
            updateProgress();
        }).on('wheel touchmove', function () {
            if (state.autoScrollTimer) {
                stopAutoScroll(true);
            }
        });

        $(document).on('click', '.stream-music-item', function (event) {
            event.stopPropagation();
            state.currentTrackIndex = Number($(this).data('track-index') || 0);
            applyCurrentTrack(true);
        });

        function openMusicModal() {
            $('#musicModal').addClass('active');
        }

        function closeMusicModal() {
            $('#musicModal').removeClass('active');
        }

        $('#musicSettingsBtn').on('click', function (event) {
            event.stopPropagation();
            openMusicModal();
        });

        $('#musicModalBackdrop, #musicModalCloseBtn').on('click', function () {
            closeMusicModal();
        });

        $('#bgmToggleBtn').on('click', function (event) {
            event.stopPropagation();
            var audio = $('#bgmAudio')[0];
            if (!audio.src && state.playlist.length) {
                applyCurrentTrack(true);
                return;
            }
            if (audio.paused) {
                audio.play().then(function () {
                    $('#bgmToggleBtn').html('<i class="fas fa-pause mr-1"></i>暂停').addClass('active');
                }).catch(function () {
                    showToast('浏览器拦截了播放，请再试一次');
                });
            } else {
                audio.pause();
                $('#bgmToggleBtn').html('<i class="fas fa-play mr-1"></i>播放').removeClass('active');
            }
        });

        $('#bgmNextBtn').on('click', function (event) {
            event.stopPropagation();
            if (!state.playlist.length) {
                showToast('当前没有可播放曲目');
                return;
            }
            state.currentTrackIndex = (state.currentTrackIndex + 1) % state.playlist.length;
            applyCurrentTrack(true);
        });

        $('#bgmVolume').on('input change', function () {
            var audio = $('#bgmAudio')[0];
            audio.volume = Number($(this).val() || 28) / 100;
        });

        $('#bgmAudio').on('ended', function () {
            if (!state.playlist.length) return;
            state.currentTrackIndex = (state.currentTrackIndex + 1) % state.playlist.length;
            applyCurrentTrack(true);
        });

        $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function () {
            var activeElement = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
            syncFullscreenButton();
            if (activeElement) {
                $('#streamContentScroll').scrollTop(0);
                updateProgress();
                setChromeHidden(true);
            } else {
                if (!state.pseudoFullscreen) {
                    setChromeHidden(false);
                }
            }
        });

        $(document).on('keydown', function (event) {
            if (event.target && /input|textarea/i.test(event.target.tagName)) {
                return;
            }
            if (event.key === 'ArrowRight' || event.key === 'PageDown') {
                event.preventDefault();
                changeArticle(state.currentIndex + 1);
            } else if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
                event.preventDefault();
                changeArticle(state.currentIndex - 1);
            } else if (event.key === ' ') {
                event.preventDefault();
                if (state.autoScrollTimer) stopAutoScroll(false);
                else startAutoScroll();
            }
        });

        $('#streamMain').on('touchstart', function (event) {
            if (event.originalEvent.touches && event.originalEvent.touches[0]) {
                state.touchStartX = event.originalEvent.touches[0].clientX;
                state.touchStartY = event.originalEvent.touches[0].clientY;
                state.touchMoved = false;
            }
        }).on('touchmove', function (event) {
            if (!(event.originalEvent.touches && event.originalEvent.touches[0])) return;
            var moveX = event.originalEvent.touches[0].clientX;
            var moveY = event.originalEvent.touches[0].clientY;
            if (Math.abs(moveX - state.touchStartX) > 10 || Math.abs(moveY - state.touchStartY) > 10) {
                state.touchMoved = true;
            }
        }).on('touchend', function (event) {
            if (!(event.originalEvent.changedTouches && event.originalEvent.changedTouches[0])) return;
            var endX = event.originalEvent.changedTouches[0].clientX;
            var endY = event.originalEvent.changedTouches[0].clientY;
            var diffX = endX - state.touchStartX;
            var diff = endY - state.touchStartY;
            if (Math.abs(diffX) < 80 || Math.abs(diffX) <= Math.abs(diff)) return;
            if (diffX < 0) {
                changeArticle(state.currentIndex + 1);
            } else {
                changeArticle(state.currentIndex - 1);
            }
        });

        $('#streamContentPanel').on('click', function (event) {
            if ($(event.target).closest('a, button, input, textarea, select, label').length) {
                return;
            }
            closeMusicModal();
            setChromeHidden(!state.chromeHidden);
        });

        syncScrollSpeed();
        syncReadThreshold();
        syncFullscreenButton();
        loadPlaylist();
        loadArticles();
    });
</script>
@endsection
