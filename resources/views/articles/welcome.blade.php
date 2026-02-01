@extends('layouts.app')

@section('title', '去阅读 - 蒙太奇')
@section('description', '蒙太奇去阅读这里支持你订阅各大个人博客、科技媒体，甚至都可以定时发送您订阅的文章到kindle阅读器，每天回家打开kindle即可享受阅读好时光！')

<style>
    /* 阅读页面专用样式 */
    .reading-feature-page {
        max-width: 1200px;
    }

    /* 特色卡片 */
    .reading-feature-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .reading-feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .reading-feature-card-header {
        background: linear-gradient(135deg, #10b981, #4a90e2);
        padding: 32px 40px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .reading-feature-card-header::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -30%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        opacity: 0.5;
    }

    .reading-feature-card-header::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: -20%;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        opacity: 0.3;
    }

    .reading-feature-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .reading-feature-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-top: 8px;
        position: relative;
        z-index: 1;
    }

    .reading-feature-card-body {
        padding: 40px;
    }

    /* Kindle特色区 */
    .kindle-feature {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 16px;
        padding: 40px;
        margin: 40px 0;
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .kindle-feature::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981, #4a90e2);
    }

    .kindle-content {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 40px;
        align-items: center;
    }

    .kindle-icon-wrapper {
        text-align: center;
    }

    .kindle-icon {
        font-size: 4rem;
        color: #555;
        margin-bottom: 16px;
        display: block;
    }

    .kindle-text {
        font-size: 1rem;
        color: #64748b;
        max-width: 400px;
        margin: 0 auto;
    }

    .kindle-description {
        flex: 1;
    }

    .kindle-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .kindle-benefits {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-top: 20px;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .benefit-icon {
        width: 32px;
        height: 32px;
        background: rgba(16, 185, 129, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #10b981;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .benefit-text {
        color: #475569;
        font-size: 0.95rem;
    }

    /* 订阅源展示 */
    .feeds-showcase {
        margin: 40px 0;
    }

    .feeds-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .feeds-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }

    .feed-category {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .feed-category:hover {
        border-color: #10b981;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.1);
        transform: translateY(-3px);
    }

    .feed-category-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feed-category-title i {
        color: #10b981;
    }

    .feed-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .feed-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        color: #64748b;
        font-size: 0.95rem;
    }

    .feed-item:last-child {
        border-bottom: none;
    }

    .feed-item i {
        color: #94a3b8;
        margin-right: 10px;
        font-size: 0.875rem;
    }

    /* 工作流程 */
    .workflow-section {
        margin: 40px 0;
    }

    .workflow-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 32px;
        text-align: center;
    }

    .workflow-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        max-width: 800px;
        margin: 0 auto;
    }

    .workflow-steps::before {
        content: '';
        position: absolute;
        top: 32px;
        left: 40px;
        right: 40px;
        height: 2px;
        background: #e2e8f0;
        z-index: 1;
    }

    .workflow-step {
        text-align: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }

    .step-number {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #10b981, #4a90e2);
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0 auto 16px;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
    }

    .step-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .step-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.5;
        max-width: 200px;
        margin: 0 auto;
    }

    /* 阅读管理特色 */
    .reading-management {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(16, 185, 129, 0.05));
        border-radius: 16px;
        padding: 32px;
        margin: 40px 0;
    }

    .management-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }

    .management-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .management-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a90e2;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
    }

    .management-content h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .management-content p {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* 图片展示区 */
    .reading-image-section {
        margin: 40px 0;
        text-align: center;
    }

    .reading-image-container {
        max-width: 900px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        transition: transform 0.3s ease;
    }

    .reading-image-container:hover {
        transform: translateY(-5px);
    }

    .reading-image-container img {
        width: 100%;
        height: auto;
        display: block;
    }

    .image-caption {
        padding: 16px;
        background: white;
        color: #64748b;
        font-size: 0.95rem;
    }

    /* 行动按钮 */
    .reading-action-section {
        text-align: center;
        margin-top: 48px;
        padding-top: 40px;
        border-top: 1px solid #e2e8f0;
    }

    .reading-primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 40px;
        font-size: 1.125rem;
        font-weight: 600;
        background: linear-gradient(135deg, #10b981, #4a90e2);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    }

    .reading-primary-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .reading-primary-btn i {
        margin-right: 12px;
        font-size: 1.2rem;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .reading-feature-card {
            border-radius: 12px;
            margin: 0 -16px;
        }

        .reading-feature-card-header {
            padding: 24px;
        }

        .reading-feature-title {
            font-size: 1.5rem;
        }

        .reading-feature-card-body {
            padding: 24px;
        }

        .kindle-feature {
            padding: 24px;
        }

        .kindle-content {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .kindle-benefits {
            grid-template-columns: 1fr;
        }

        .feeds-grid {
            grid-template-columns: 1fr;
        }

        .workflow-steps {
            flex-direction: column;
            gap: 32px;
        }

        .workflow-steps::before {
            display: none;
        }

        .step-number {
            width: 56px;
            height: 56px;
        }

        .management-grid {
            grid-template-columns: 1fr;
        }

        .reading-primary-btn {
            width: 100%;
            padding: 14px 24px;
        }
    }

    /* 动画效果 */
    @keyframes slideInFromLeft {
        0% {
            opacity: 0;
            transform: translateX(-50px);
        }
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slideInLeft {
        animation: slideInFromLeft 0.6s ease-out;
    }

    @keyframes slideInFromRight {
        0% {
            opacity: 0;
            transform: translateX(50px);
        }
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slideInRight {
        animation: slideInFromRight 0.6s ease-out;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 reading-feature-page">
        <!-- 特色卡片 -->
        <div class="reading-feature-card animate-slideInLeft">
            <!-- 卡片头部 -->
            <div class="reading-feature-card-header">
                <h1 class="reading-feature-title">
                    <i class="fas fa-book-reader"></i>
                    去阅读
                </h1>
                <p class="reading-feature-subtitle">
                    构建个人知识库，享受沉浸式阅读体验
                </p>
            </div>

            <!-- 卡片内容 -->
            <div class="reading-feature-card-body">
                <!-- 介绍段落 -->
                <div class="intro-content" style="font-size: 1.125rem; line-height: 1.8; color: #475569; text-align: center; max-width: 800px; margin: 0 auto 40px;">
                    <b>去阅读</b>是蒙太奇的阅读管理功能，帮助您高效订阅和管理优质内容源，构建个人知识体系。从技术博客到科技媒体，从深度分析到行业动态，一切尽在掌握。
                </div>

                <!-- Kindle特色区 -->
                <div class="kindle-feature animate-slideInRight">
                    <div class="kindle-content">
                        <div class="kindle-icon-wrapper">
                            <i class="fas fa-tablet-alt kindle-icon"></i>
                            <p class="kindle-text">Kindle友好体验</p>
                        </div>
                        <div class="kindle-description">
                            <h2 class="kindle-title">无缝对接Kindle，享受纸书般的阅读体验</h2>
                            <p style="color: #64748b; line-height: 1.7; margin-bottom: 20px;">
                                蒙太奇支持定时将您订阅的文章自动发送到Kindle阅读器。每天回家打开Kindle，即可享受纯净、无干扰的阅读时光，就像阅读纸质书籍一样舒适。
                            </p>

                            <div class="kindle-benefits">
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <span class="benefit-text">定时自动推送</span>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <span class="benefit-text">护眼墨水屏阅读</span>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-wifi"></i>
                                    </div>
                                    <span class="benefit-text">无线自动同步</span>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-bookmark"></i>
                                    </div>
                                    <span class="benefit-text">离线随时阅读</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 订阅源展示 -->
                <div class="feeds-showcase animate-slideInLeft">
                    <h2 class="feeds-title">
                        <i class="fas fa-rss"></i>
                        丰富的订阅源
                    </h2>
                    <div class="feeds-grid">
                        <div class="feed-category">
                            <h3 class="feed-category-title">
                                <i class="fas fa-code"></i>
                                技术博客
                            </h3>
                            <ul class="feed-list">
                                <li class="feed-item"><i class="fas fa-blog"></i> 个人技术博客</li>
                                <li class="feed-item"><i class="fas fa-laptop-code"></i> 开源项目动态</li>
                                <li class="feed-item"><i class="fas fa-tools"></i> 开发工具分享</li>
                                <li class="feed-item"><i class="fas fa-server"></i> 架构设计思考</li>
                            </ul>
                        </div>

                        <div class="feed-category">
                            <h3 class="feed-category-title">
                                <i class="fas fa-newspaper"></i>
                                科技媒体
                            </h3>
                            <ul class="feed-list">
                                <li class="feed-item"><i class="fas fa-mobile-alt"></i> 科技行业新闻</li>
                                <li class="feed-item"><i class="fas fa-chart-line"></i> 市场趋势分析</li>
                                <li class="feed-item"><i class="fas fa-lightbulb"></i> 创新产品评测</li>
                                <li class="feed-item"><i class="fas fa-users"></i> 大佬观点分享</li>
                            </ul>
                        </div>

                        <div class="feed-category">
                            <h3 class="feed-category-title">
                                <i class="fas fa-book"></i>
                                深度阅读
                            </h3>
                            <ul class="feed-list">
                                <li class="feed-item"><i class="fas fa-search"></i> 行业深度研究</li>
                                <li class="feed-item"><i class="fas fa-graduation-cap"></i> 专业知识分享</li>
                                <li class="feed-item"><i class="fas fa-history"></i> 历史技术回顾</li>
                                <li class="feed-item"><i class="fas fa-rocket"></i> 前沿技术展望</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 工作流程 -->
                <div class="workflow-section">
                    <h2 class="workflow-title">简单三步，开启高效阅读</h2>
                    <div class="workflow-steps">
                        <div class="workflow-step">
                            <div class="step-number">1</div>
                            <h3 class="step-title">订阅内容源</h3>
                            <p class="step-description">添加您感兴趣的博客、媒体RSS源</p>
                        </div>
                        <div class="workflow-step">
                            <div class="step-number">2</div>
                            <h3 class="step-title">配置Kindle</h3>
                            <p class="step-description">设置您的Kindle邮箱和发送时间</p>
                        </div>
                        <div class="workflow-step">
                            <div class="step-number">3</div>
                            <h3 class="step-title">享受阅读</h3>
                            <p class="step-description">每天自动接收，专注沉浸式阅读</p>
                        </div>
                    </div>
                </div>

                <!-- 阅读管理特色 -->
                <div class="reading-management animate-slideInRight">
                    <h2 class="feeds-title" style="margin-top: 0;">
                        <i class="fas fa-star"></i>
                        智能阅读管理
                    </h2>
                    <div class="management-grid">
                        <div class="management-item">
                            <div class="management-icon">
                                <i class="fas fa-bookmark"></i>
                            </div>
                            <div class="management-content">
                                <h3>稍后阅读</h3>
                                <p>遇到好文章没时间看？一键收藏到稍后阅读，空闲时再细细品味。</p>
                            </div>
                        </div>
                        <div class="management-item">
                            <div class="management-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="management-content">
                                <h3>智能分类</h3>
                                <p>自动为文章添加标签，按主题分类，建立您的个人知识体系。</p>
                            </div>
                        </div>
                        <div class="management-item">
                            <div class="management-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="management-content">
                                <h3>全文搜索</h3>
                                <p>强大的搜索功能，快速找到您阅读过的任何内容。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 图片展示 -->
                <div class="reading-image-section animate-slideInLeft">
                    <div class="reading-image-container">
                        <img src="/img/read.png" alt="蒙太奇去阅读界面"
                             title="蒙太奇去阅读界面 - 智能阅读管理平台">
                        <div class="image-caption">阅读管理界面 - 简洁高效的内容订阅与管理</div>
                    </div>
                </div>

                <!-- 核心价值 -->
                <div class="mt-10 p-6 bg-gradient-to-r from-green-50 to-blue-50 rounded-2xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-award text-green-600"></i>
                        为什么选择蒙太奇去阅读？
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span><strong>Kindle集成：</strong>唯一支持自动推送至Kindle的阅读管理工具</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>海量源支持：</strong>支持RSS、Atom等标准，兼容几乎所有内容源</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>智能整理：</strong>自动分类、标签、去重，保持阅读列表整洁</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>专注阅读：</strong>无广告、无干扰的纯净阅读环境</span>
                        </li>
                    </ul>
                </div>

                <!-- 行动召唤 -->
                <div class="reading-action-section">
                    <a href="{{ url('/articles') }}" class="reading-primary-btn">
                        <i class="fas fa-book-open"></i>
                        开始订阅阅读
                    </a>
                    <p style="color: #64748b; margin-top: 20px; font-size: 0.95rem;">
                        立即体验高效的知识获取和沉浸式阅读
                    </p>
                </div>
            </div>
        </div>

        <!-- 相关功能导航 -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ url('/feeds') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-rss text-green-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">订阅管理</h3>
                <p class="text-gray-600 text-sm">管理订阅源，发现优质内容，定制个人阅读</p>
            </a>

            <a href="{{ url('/feeds/explorer') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-compass text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">探索发现</h3>
                <p class="text-gray-600 text-sm">发现新的优质博客和媒体源，不断拓展知识边界</p>
            </a>

            <a href="{{ url('/notes') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lightbulb text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">记想法</h3>
                <p class="text-gray-600 text-sm">记录阅读心得，整理思考，构建知识体系</p>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 滚动动画
            function animateOnScroll() {
                $('.animate-slideInLeft').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).css('opacity', '1');
                        $(this).css('transform', 'translateX(0)');
                    }
                });

                $('.animate-slideInRight').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).css('opacity', '1');
                        $(this).css('transform', 'translateX(0)');
                    }
                });
            }

            // 初始隐藏动画元素
            $('.animate-slideInLeft').css({
                'opacity': '0',
                'transform': 'translateX(-50px)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });

            $('.animate-slideInRight').css({
                'opacity': '0',
                'transform': 'translateX(50px)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });

            // 初始检查
            setTimeout(animateOnScroll, 100);

            // 滚动时检查
            $(window).scroll(function() {
                animateOnScroll();
            });

            // 特色卡片悬停
            $('.reading-feature-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 订阅源分类悬停
            $('.feed-category').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 管理项悬停
            $('.management-item').hover(
                function() {
                    $(this).find('.management-icon').css('transform', 'scale(1.1)');
                },
                function() {
                    $(this).find('.management-icon').css('transform', 'scale(1)');
                }
            );

            // 工作步骤悬停
            $('.workflow-step').hover(
                function() {
                    $(this).find('.step-number').css('transform', 'scale(1.1)');
                },
                function() {
                    $(this).find('.step-number').css('transform', 'scale(1)');
                }
            );

            // 图片容器悬停
            $('.reading-image-container').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 主按钮悬停
            $('.reading-primary-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 相关功能卡片悬停
            $('.card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                    $(this).removeClass('shadow');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                    $(this).addClass('shadow');
                }
            );
        });
    </script>
@endsection