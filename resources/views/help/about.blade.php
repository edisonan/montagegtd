@extends('layouts.app')

@section('title', '关于我们 - 蒙太奇')

<style>
    /* 关于页面专用样式 */
    .about-page {
        max-width: 1200px;
    }

    /* 页头区域 */
    .about-header {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }

    .about-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .about-subtitle {
        font-size: 1.25rem;
        color: #64748b;
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* 蒙太奇解释卡片 */
    .montage-explanation {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
        border-radius: 20px;
        padding: 48px;
        margin: 60px 0;
        position: relative;
        overflow: hidden;
    }

    .montage-explanation::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4a90e2, #8a6cff);
    }

    .explanation-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 24px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .explanation-content {
        font-size: 1.125rem;
        line-height: 1.8;
        color: #475569;
        max-width: 900px;
        margin: 0 auto;
    }

    .quote-block {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin: 32px 0;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #4a90e2;
        position: relative;
    }

    .quote-block::before {
        content: '"';
        position: absolute;
        top: 20px;
        left: 20px;
        font-size: 4rem;
        color: rgba(59, 130, 246, 0.1);
        font-family: Georgia, serif;
        line-height: 1;
    }

    .quote-text {
        font-style: italic;
        color: #475569;
        font-size: 1.125rem;
        line-height: 1.7;
        position: relative;
        z-index: 1;
    }

    /* 核心价值 */
    .core-values {
        margin: 60px 0;
    }

    .values-title {
        font-size: 2rem;
        font-weight: 600;
        color: #1e293b;
        text-align: center;
        margin-bottom: 48px;
        position: relative;
    }

    .values-title::after {
        content: '';
        position: absolute;
        bottom: -16px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #4a90e2, #8a6cff);
        border-radius: 2px;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 32px;
    }

    .value-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-align: center;
    }

    .value-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .value-icon {
        width: 72px;
        height: 72px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        color: #4a90e2;
        font-size: 1.75rem;
    }

    .value-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .value-description {
        color: #64748b;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* 技术栈展示 */
    .tech-stack {
        background: #f8fafc;
        border-radius: 20px;
        padding: 48px;
        margin: 60px 0;
    }

    .tech-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 32px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .tech-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        max-width: 800px;
        margin: 0 auto;
    }

    .tech-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.3s ease;
    }

    .tech-item:hover {
        border-color: #4a90e2;
        transform: translateX(5px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
    }

    .tech-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a90e2;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .tech-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .tech-info p {
        color: #64748b;
        font-size: 0.875rem;
        margin: 0;
    }

    /* 功能特色展示 */
    .features-showcase {
        margin: 60px 0;
    }

    .features-title {
        font-size: 2rem;
        font-weight: 600;
        color: #1e293b;
        text-align: center;
        margin-bottom: 48px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 32px;
    }

    .feature-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4a90e2, #8a6cff);
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .feature-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }

    .feature-icon-wrapper {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a90e2;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .feature-description {
        color: #64748b;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* 行动卡片 */
    .action-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 32px;
        margin: 60px 0;
    }

    .action-card {
        background: white;
        border-radius: 16px;
        padding: 40px 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: all 0.3s ease;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .action-icon {
        font-size: 3rem;
        color: #4a90e2;
        margin-bottom: 24px;
        display: block;
    }

    .action-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 28px;
        font-size: 1rem;
        font-weight: 600;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.3s ease;
        margin-top: 20px;
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        color: white;
    }

    /* 鸣谢区域 */
    .acknowledgement {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05));
        border-radius: 20px;
        padding: 48px;
        margin: 60px 0;
        text-align: center;
    }

    .acknowledgement-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 32px;
    }

    .jb-logo {
        max-width: 200px;
        height: auto;
        margin-bottom: 24px;
        filter: grayscale(100%);
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .jb-logo:hover {
        filter: grayscale(0%);
        opacity: 1;
    }

    /* 联系区域 */
    .contact-section {
        text-align: center;
        margin-top: 80px;
        padding-top: 60px;
        border-top: 1px solid #e2e8f0;
    }

    .contact-message {
        font-size: 1.125rem;
        color: #64748b;
        margin-bottom: 32px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .contact-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 36px;
        font-size: 1.125rem;
        font-weight: 600;
        background: white;
        color: #4a90e2;
        border: 2px solid #4a90e2;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .contact-btn:hover {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
    }

    /* 动画效果 */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.8s ease-out;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .about-header {
            margin-bottom: 40px;
        }

        .about-title {
            font-size: 2rem;
        }

        .about-subtitle {
            font-size: 1.125rem;
            padding: 0 16px;
        }

        .montage-explanation {
            padding: 32px 24px;
            margin: 40px 0;
            border-radius: 16px;
        }

        .quote-block {
            padding: 24px;
            margin: 24px 0;
        }

        .quote-text {
            font-size: 1rem;
        }

        .values-title, .features-title {
            font-size: 1.75rem;
        }

        .values-grid, .features-grid, .action-cards {
            gap: 24px;
        }

        .tech-stack {
            padding: 32px 24px;
            margin: 40px 0;
            border-radius: 16px;
        }

        .tech-grid {
            grid-template-columns: 1fr;
        }

        .feature-card, .value-card, .action-card {
            padding: 24px;
        }

        .acknowledgement {
            padding: 32px 24px;
            margin: 40px 0;
            border-radius: 16px;
        }

        .contact-section {
            margin-top: 60px;
            padding-top: 40px;
        }
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 about-page">
        <!-- 页头区域 -->
        <div class="about-header animate-fadeInUp">
            <h1 class="about-title">关于蒙太奇</h1>
            <p class="about-subtitle">
                不止于GTD的知识管理系统，将番茄工作法、任务管理、笔记记录、RSS阅读、思维导图融为一体
            </p>
        </div>

        <!-- 蒙太奇解释区域 -->
        <div class="montage-explanation animate-fadeInUp">
            <h2 class="explanation-title">
                <i class="fas fa-film"></i>
                为什么叫"蒙太奇"？
            </h2>

            <div class="explanation-content">
                <p>
                    "蒙太奇"是一个电影术语，指通过对众多镜头进行不同方式的剪辑，从而展现出各异的立意。即便在疫情期间，不少人断言电影将会走向消亡，但实际上电影或许是最具生命力、最难消亡的产业之一。
                </p>

                <div class="quote-block">
                    <p class="quote-text">
                        人类在现实中往往显得渺小脆弱，而电影为我们提供了一个能够主宰想象空间的契机。
                    </p>
                </div>

                <p>
                    那么这个平台呢？它期望记录下我们这些小人物在平凡日常与非凡时刻的点点滴滴，如同经历一场"浮生一日"。在这里，你既是导演，掌控着情节走向；也是编剧，书写着故事内容；更是演员，演绎着自己的人生。
                </p>

                <div class="quote-block">
                    <p class="quote-text">
                        衷心希望大家所记录的每一件事、每一段成长历程，最终能够如同精心剪辑的蒙太奇镜头一般，共同拼凑出一部专属于我们生活的宏大电影。
                    </p>
                </div>

                <p style="text-align: center; margin-top: 32px; font-weight: 500; color: #4a90e2;">
                    <i class="fas fa-quote-left mr-2"></i>
                    记录生活点滴，编织知识网络
                    <i class="fas fa-quote-right ml-2"></i>
                </p>
            </div>
        </div>

        <!-- 核心价值 -->
        <div class="core-values animate-fadeInUp">
            <h2 class="values-title">我们的核心价值</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="value-title">效率提升</h3>
                    <p class="value-description">
                        通过科学的番茄工作法和智能任务管理，帮助用户专注核心工作，提升时间利用效率300%以上。
                    </p>
                </div>

                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="value-title">知识管理</h3>
                    <p class="value-description">
                        提供从信息收集、整理、思考到输出的完整知识工作流，构建个人知识体系。
                    </p>
                </div>

                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="value-title">以人为本</h3>
                    <p class="value-description">
                        尊重用户隐私和数据安全，提供简洁优雅的用户体验，关注每一个细节的完善。
                    </p>
                </div>
            </div>
        </div>

        <!-- 技术栈展示 -->
        <div class="tech-stack animate-fadeInUp">
            <h2 class="tech-title">
                <i class="fas fa-code"></i>
                技术栈
            </h2>
            <div class="tech-grid">
                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="fab fa-php"></i>
                    </div>
                    <div class="tech-info">
                        <h4>PHP >= 7.0.0</h4>
                        <p>高性能后端编程语言</p>
                    </div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="fab fa-laravel"></i>
                    </div>
                    <div class="tech-info">
                        <h4>Laravel 5.5.*</h4>
                        <p>优雅的PHP Web开发框架</p>
                    </div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="tech-info">
                        <h4>MySQL >= 5.5.*</h4>
                        <p>稳定可靠的关系型数据库</p>
                    </div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="fab fa-js-square"></i>
                    </div>
                    <div class="tech-info">
                        <h4>Bootstrap + jQuery</h4>
                        <p>简洁优雅的前端交互</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 功能特色展示 -->
        <div class="features-showcase animate-fadeInUp">
            <h2 class="features-title">核心功能特色</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="feature-title">番茄工作法</h3>
                    </div>
                    <p class="feature-description">
                        支持番茄钟自定义设置，找到个人最高效的时间段。完成番茄钟后，可直接关联待办事项，形成完整工作闭环。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3 class="feature-title">智能任务管理</h3>
                    </div>
                    <p class="feature-description">
                        待办事项支持提醒功能、deadline预警、四象限优先级管理，帮助您高效安排工作，确保重要任务不遗漏。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3 class="feature-title">想法记录</h3>
                    </div>
                    <p class="feature-description">
                        支持文字、图片、语音多种记录方式，提供标签分类、公开/私密发布选项，浏览器插件一键保存网页内容。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <h3 class="feature-title">RSS阅读管理</h3>
                    </div>
                    <p class="feature-description">
                        订阅优质博客和媒体源，支持拖动排序、稍后阅读、收藏加星，支持自动推送到Kindle设备。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3 class="feature-title">思维导图</h3>
                    </div>
                    <p class="feature-description">
                        可视化思维工具，支持快捷键操作、无限画布扩展，可导出为图片或PDF，助力结构化思考。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3 class="feature-title">数据统计分析</h3>
                    </div>
                    <p class="feature-description">
                        提供阅读、番茄钟、想法等维度的饼图与柱状图统计，帮助您清晰了解时间分配和使用习惯。
                    </p>
                </div>
            </div>
        </div>

        <!-- 行动卡片 -->
        <div class="action-cards animate-fadeInUp">
            <div class="action-card">
                <i class="fas fa-rocket action-icon"></i>
                <h3 class="action-title">立即体验</h3>
                <p class="feature-description">感受蒙太奇带来的高效工作体验</p>
                <a href="https://task.congcong.us" target="_blank" class="action-btn">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    访问演示站点
                </a>
            </div>

            <div class="action-card">
                <i class="fas fa-code action-icon"></i>
                <h3 class="action-title">开源项目</h3>
                <p class="feature-description">查看项目源码，参与社区贡献</p>
                <a href="https://gitee.com/accacc/task" target="_blank" class="action-btn">
                    <i class="fab fa-gitee mr-2"></i>
                    查看源码
                </a>
            </div>

            <div class="action-card">
                <i class="fas fa-book action-icon"></i>
                <h3 class="action-title">使用指南</h3>
                <p class="feature-description">了解蒙太奇的高级功能和技巧</p>
                <a href="https://gitee.com/accacc/task#%E9%AB%98%E6%95%88%E4%BD%BF%E7%94%A8montage-gtd"
                   target="_blank" class="action-btn">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    学习使用技巧
                </a>
            </div>
        </div>

        <!-- 鸣谢区域 -->
        <div class="acknowledgement animate-fadeInUp">
            <h2 class="acknowledgement-title">
                <i class="fas fa-hands-helping"></i>
                特别鸣谢
            </h2>
            <img src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg"
                 alt="JetBrains"
                 class="jb-logo">
            <p style="color: #64748b; font-size: 1.125rem; line-height: 1.6; max-width: 600px; margin: 0 auto;">
                感谢 <strong>JetBrains Open Source Support</strong> 计划的支持，为开源项目提供专业的开发工具，
                帮助我们构建更好的产品体验。
            </p>
        </div>

        <!-- 联系区域 -->
        <div class="contact-section animate-fadeInUp">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">期待您的反馈</h3>
            <p class="contact-message">
                蒙太奇始终在不断完善和进化中。如果您有任何建议、想法或问题，欢迎随时联系我们。
                您的反馈将帮助我们做得更好。
            </p>
            <a href="mailto:accacc@126.com?subject=关于蒙太奇的反馈" class="contact-btn">
                <i class="fas fa-envelope mr-2"></i>
                联系我们
            </a>
            <p style="color: #94a3b8; margin-top: 24px; font-size: 0.95rem;">
                <i class="fas fa-copyright mr-1"></i>
                2016-{{ date('Y') }} 蒙太奇 - 专注提升个人效率的生产力工具
            </p>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 滚动动画
            function animateOnScroll() {
                $('.animate-fadeInUp').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).css('opacity', '1');
                        $(this).css('transform', 'translateY(0)');
                    }
                });
            }

            // 初始隐藏动画元素
            $('.animate-fadeInUp').css({
                'opacity': '0',
                'transform': 'translateY(30px)',
                'transition': 'opacity 0.8s ease, transform 0.8s ease'
            });

            // 初始检查
            setTimeout(animateOnScroll, 100);

            // 滚动时检查
            $(window).scroll(function() {
                animateOnScroll();
            });

            // 卡片悬停效果
            $('.value-card, .feature-card, .action-card').hover(
                function() {
                    $(this).css('transform', $(this).hasClass('feature-card') ? 'translateY(-8px)' : 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 技术项悬停效果
            $('.tech-item').hover(
                function() {
                    $(this).css('transform', 'translateX(5px)');
                },
                function() {
                    $(this).css('transform', 'translateX(0)');
                }
            );

            // 按钮悬停效果
            $('.action-btn, .contact-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // JetBrains logo悬停效果
            $('.jb-logo').hover(
                function() {
                    $(this).css('filter', 'grayscale(0%)');
                    $(this).css('opacity', '1');
                },
                function() {
                    $(this).css('filter', 'grayscale(100%)');
                    $(this).css('opacity', '0.8');
                }
            );

            // 引用块鼠标悬停效果
            $('.quote-block').hover(
                function() {
                    $(this).css('box-shadow', '0 12px 28px rgba(0, 0, 0, 0.1)');
                    $(this).css('border-left-color', '#8a6cff');
                },
                function() {
                    $(this).css('box-shadow', '0 8px 20px rgba(0, 0, 0, 0.05)');
                    $(this).css('border-left-color', '#4a90e2');
                }
            );
        });
    </script>
@endsection