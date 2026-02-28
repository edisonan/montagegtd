@extends('layouts.app')

@section('title', '记想法 - 蒙太奇')
@section('description', '蒙太奇记想法这里支持通过插件快速分享chrome等浏览器所浏览的网站、图片与文字，同时可以你可以实时去记录你的想法，其更支持语音录入极大方便你的学习生活')

<style>
    /* 记想法页面专用样式 */
    .notes-feature-page {
        max-width: 1200px;
    }

    /* 特色卡片 */
    .notes-feature-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .notes-feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .notes-feature-card-header {
        background: linear-gradient(135deg, #8a6cff, #4a90e2);
        padding: 32px 40px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .notes-feature-card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .notes-feature-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .notes-feature-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-top: 8px;
        position: relative;
        z-index: 1;
    }

    .notes-feature-card-body {
        padding: 40px;
    }

    /* 特色功能介绍 */
    .notes-features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin: 40px 0;
    }

    .notes-feature-block {
        background: #f8fafc;
        border-radius: 12px;
        padding: 28px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .notes-feature-block:hover {
        background: white;
        border-color: #8a6cff;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.1);
        transform: translateY(-5px);
    }

    .notes-feature-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #8a6cff, #4a90e2);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: white;
        font-size: 1.5rem;
    }

    .notes-feature-block-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .notes-feature-block-desc {
        color: #64748b;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    /* 使用场景 */
    .use-cases {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(59, 130, 246, 0.05));
        border-radius: 16px;
        padding: 32px;
        margin: 40px 0;
    }

    .use-cases-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 24px;
        text-align: center;
    }

    .use-cases-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .use-case-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        transition: transform 0.3s ease;
    }

    .use-case-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    }

    .use-case-icon {
        width: 40px;
        height: 40px;
        background: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: #8a6cff;
        font-size: 1.2rem;
    }

    .use-case-text {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* 浏览器插件展示 */
    .browser-extensions {
        margin: 40px 0;
    }

    .extensions-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .browser-icons {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .browser-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .browser-icon:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        color: #4a90e2;
    }

    /* 图片展示区 */
    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin: 40px 0;
    }

    .gallery-item {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
    }

    .gallery-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }

    .gallery-caption {
        padding: 16px;
        background: white;
        text-align: center;
        color: #64748b;
        font-size: 0.9rem;
    }

    /* 语音录入特色 */
    .voice-feature {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
        border-radius: 16px;
        padding: 32px;
        margin: 40px 0;
        text-align: center;
    }

    .voice-icon {
        font-size: 3rem;
        color: #10b981;
        margin-bottom: 20px;
    }

    .voice-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .voice-desc {
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 600px;
        margin: 0 auto;
    }

    /* 行动按钮 */
    .notes-action-section {
        text-align: center;
        margin-top: 48px;
        padding-top: 40px;
        border-top: 1px solid #e2e8f0;
    }

    .notes-primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 40px;
        font-size: 1.125rem;
        font-weight: 600;
        background: linear-gradient(135deg, #8a6cff, #4a90e2);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
    }

    .notes-primary-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(139, 92, 246, 0.4);
        color: white;
    }

    .notes-primary-btn i {
        margin-right: 12px;
        font-size: 1.2rem;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .notes-feature-card {
            border-radius: 12px;
            margin: 0 -16px;
        }

        .notes-feature-card-header {
            padding: 24px;
        }

        .notes-feature-title {
            font-size: 1.5rem;
        }

        .notes-feature-card-body {
            padding: 24px;
        }

        .notes-features-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .notes-feature-block {
            padding: 20px;
        }

        .use-cases {
            padding: 24px;
        }

        .use-cases-grid {
            grid-template-columns: 1fr;
        }

        .image-gallery {
            grid-template-columns: 1fr;
        }

        .notes-primary-btn {
            width: 100%;
            padding: 14px 24px;
        }
    }

    /* 动画效果 */
    @keyframes floatIn {
        0% {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .animate-floatIn {
        animation: floatIn 0.6s ease-out;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 notes-feature-page">
        <!-- 特色卡片 -->
        <div class="notes-feature-card animate-floatIn">
            <!-- 卡片头部 -->
            <div class="notes-feature-card-header">
                <h1 class="notes-feature-title">
                    <i class="fas fa-lightbulb"></i>
                    记想法
                </h1>
                <p class="notes-feature-subtitle">
                    捕捉灵感，记录思考，构建你的知识体系
                </p>
            </div>

            <!-- 卡片内容 -->
            <div class="notes-feature-card-body">
                <!-- 介绍段落 -->
                <div class="intro-content" style="font-size: 1.125rem; line-height: 1.8; color: #475569;">
                    <b>记想法</b>是蒙太奇的核心功能之一，旨在帮助您随时随地捕捉灵感、记录思考。无论是网页浏览时的发现，还是生活中的灵感闪现，都能快速记录并整理，构建属于您个人的知识体系。
                </div>

                <!-- 特色功能网格 -->
                <div class="notes-features-grid">
                    <div class="notes-feature-block animate-floatIn" style="animation-delay: 0.1s;">
                        <div class="notes-feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="notes-feature-block-title">网页快速分享</h3>
                        <p class="notes-feature-block-desc">
                            通过浏览器插件，一键保存网页内容、图片和文字，支持Chrome、Edge等主流浏览器，让知识收集变得极其便捷。
                        </p>
                    </div>

                    <div class="notes-feature-block animate-floatIn" style="animation-delay: 0.2s;">
                        <div class="notes-feature-icon">
                            <i class="fas fa-microphone-alt"></i>
                        </div>
                        <h3 class="notes-feature-block-title">智能语音录入</h3>
                        <p class="notes-feature-block-desc">
                            支持语音转文字功能，无论是会议记录、灵感闪现还是日常想法，动动嘴就能快速记录，解放您的双手。
                        </p>
                    </div>

                    <div class="notes-feature-block animate-floatIn" style="animation-delay: 0.3s;">
                        <div class="notes-feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3 class="notes-feature-block-title">实时同步记录</h3>
                        <p class="notes-feature-block-desc">
                            随时随地记录您的想法，所有内容云端同步，在电脑、手机、平板上都能无缝访问和编辑。
                        </p>
                    </div>
                </div>

                <!-- 语音录入特色区 -->
                <div class="voice-feature animate-floatIn" style="animation-delay: 0.4s;">
                    <div class="voice-icon">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <h2 class="voice-title">语音录入，极速记录</h2>
                    <p class="voice-desc">
                        在忙碌的生活中，掏出手机说话比打字更方便。蒙太奇的语音识别技术能准确识别您的语音，自动转换为文字记录，让记录想法变得前所未有的轻松。
                    </p>
                </div>

                <!-- 使用场景 -->
                <div class="use-cases animate-floatIn" style="animation-delay: 0.5s;">
                    <h2 class="use-cases-title">适用场景</h2>
                    <div class="use-cases-grid">
                        <div class="use-case-item">
                            <div class="use-case-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <p class="use-case-text">
                                <strong>学习研究：</strong>整理文献资料，记录学习心得，构建知识框架
                            </p>
                        </div>
                        <div class="use-case-item">
                            <div class="use-case-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <p class="use-case-text">
                                <strong>工作记录：</strong>会议纪要，项目想法，待办事项，工作日志
                            </p>
                        </div>
                        <div class="use-case-item">
                            <div class="use-case-icon">
                                <i class="fas fa-pen-fancy"></i>
                            </div>
                            <p class="use-case-text">
                                <strong>创作灵感：</strong>写作素材，设计想法，创意收集，灵感日记
                            </p>
                        </div>
                        <div class="use-case-item">
                            <div class="use-case-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <p class="use-case-text">
                                <strong>个人生活：</strong>旅行记录，读书笔记，生活感悟，计划安排
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 浏览器插件支持 -->
                <div class="browser-extensions animate-floatIn" style="animation-delay: 0.6s;">
                    <h2 class="extensions-title">
                        <i class="fas fa-puzzle-piece"></i>
                        浏览器插件支持
                    </h2>
                    <p style="color: #475569; margin-bottom: 20px; line-height: 1.7;">
                        安装蒙太奇浏览器插件后，您可以在任何网页上快速保存内容：
                    </p>
                    <div class="browser-icons">
                        <div class="browser-icon" title="Chrome浏览器">
                            <i class="fab fa-chrome"></i>
                        </div>
                        <div class="browser-icon" title="Edge浏览器">
                            <i class="fab fa-edge"></i>
                        </div>
                        <div class="browser-icon" title="Firefox浏览器">
                            <i class="fab fa-firefox"></i>
                        </div>
                        <div class="browser-icon" title="Safari浏览器">
                            <i class="fab fa-safari"></i>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border-radius: 12px; padding: 20px; margin-top: 20px;">
                        <p style="color: #475569; font-size: 0.95rem; line-height: 1.6;">
                            <i class="fas fa-mouse-pointer text-purple-500 mr-2"></i>
                            <strong>使用方法：</strong> 在网页上选中文字或图片 → 点击插件图标 → 自动保存到蒙太奇想法库 → 随时随地查看和编辑
                        </p>
                    </div>
                </div>

                <!-- 图片展示 -->
                <div class="image-gallery animate-floatIn" style="animation-delay: 0.7s;">
                    <div class="gallery-item">
                        <img src="/img/note.png" alt="蒙太奇记想法界面"
                             title="蒙太奇记想法主界面 - 简洁高效的知识管理">
                        <div class="gallery-caption">主界面 - 清爽的笔记管理</div>
                    </div>
                    <div class="gallery-item">
                        <img src="/img/note.png" alt="笔记编辑界面"
                             title="丰富的编辑功能 - 支持文字、图片、语音">
                        <div class="gallery-caption">编辑界面 - 支持多种内容类型</div>
                    </div>
                </div>

                <!-- 核心价值 -->
                <div class="mt-10 p-6 bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-bullseye text-purple-600"></i>
                        为什么选择蒙太奇记想法？
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span><strong>多端同步：</strong>Web端 + 浏览器插件 + 移动端，随时随地记录</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span><strong>智能识别：</strong>语音转文字、网页内容智能提取</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span><strong>高效整理：</strong>标签分类、全文搜索、快速定位</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span><strong>安全可靠：</strong>数据加密存储，保护您的个人隐私</span>
                        </li>
                    </ul>
                </div>

                <!-- 行动召唤 -->
                <div class="notes-action-section">
                    <a href="{{ url('/notes') }}" class="notes-primary-btn">
                        <i class="fas fa-edit"></i>
                        开始记录想法
                    </a>
                    <p style="color: #64748b; margin-top: 20px; font-size: 0.95rem;">
                        立即体验高效的灵感捕捉和知识管理
                    </p>
                </div>
            </div>
        </div>

        <!-- 相关功能导航 -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ url('/articles') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-reader text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">去阅读</h3>
                <p class="text-gray-600 text-sm">阅读管理，收藏文章，构建个人知识库</p>
            </a>

            <a href="{{ url('/minds') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">绘导图</h3>
                <p class="text-gray-600 text-sm">思维导图，梳理思路，建立知识连接</p>
            </a>

            <a href="{{ url('/tasks') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tasks text-green-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">待办管理</h3>
                <p class="text-gray-600 text-sm">任务管理，设置提醒，高效完成任务</p>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 滚动动画
            function animateOnScroll() {
                $('.animate-floatIn').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).css('opacity', '1');
                        $(this).css('transform', 'translateY(0) scale(1)');
                    }
                });
            }

            // 初始隐藏动画元素
            $('.animate-floatIn').css({
                'opacity': '0',
                'transform': 'translateY(30px) scale(0.95)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });

            // 初始检查
            animateOnScroll();

            // 滚动时检查
            $(window).scroll(function() {
                animateOnScroll();
            });

            // 特色块悬停效果
            $('.notes-feature-block').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 使用场景悬停
            $('.use-case-item').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 浏览器图标悬停
            $('.browser-icon').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 画廊项目悬停
            $('.gallery-item').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 主按钮悬停
            $('.notes-primary-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
    </script>
@endsection