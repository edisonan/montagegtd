@extends('layouts.app')

@section('title', '做番茄 - 蒙太奇')
@section('description', '蒙太奇做番茄这里通过番茄工作法合理的安排工作与休息，极大提高你的工作效率，另外这里有完善的待办管理，你可以定义优先级，还可以设置deadline、设置提醒时间，让你每个任务都不落下，每个任务都顺利完成！')

@section('content')
<style>
    /* 特色页面专用样式 */
    .feature-page {
        max-width: 1200px;
    }

    /* 特色卡片 */
    .feature-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .feature-card-header {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        padding: 32px 40px;
        color: white;
    }

    .feature-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .feature-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-top: 8px;
    }

    .feature-card-body {
        padding: 40px;
    }

    /* 介绍内容样式 */
    .intro-section {
        margin-bottom: 32px;
    }

    .intro-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .intro-title i {
        color: #4a90e2;
    }

    .intro-content {
        font-size: 1.125rem;
        line-height: 1.7;
        color: #475569;
        margin-bottom: 24px;
    }

    .highlight-text {
        background: linear-gradient(120deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
        border-left: 4px solid #4a90e2;
        padding: 20px 24px;
        border-radius: 8px;
        margin: 24px 0;
    }

    /* 功能列表 */
    .feature-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin: 32px 0;
    }

    .feature-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        background: white;
        border-color: #4a90e2;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
    }

    .feature-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: white;
        font-size: 1.5rem;
    }

    .feature-item-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .feature-item-desc {
        color: #64748b;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* 图片展示 */
    .image-showcase {
        margin: 40px 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .image-showcase img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }

    .image-showcase:hover img {
        transform: scale(1.02);
    }

    /* 行动按钮 */
    .action-section {
        text-align: center;
        margin-top: 48px;
        padding-top: 40px;
        border-top: 1px solid #e2e8f0;
    }

    .primary-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 40px;
        font-size: 1.125rem;
        font-weight: 600;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
    }

    .primary-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .primary-action-btn i {
        margin-right: 12px;
        font-size: 1.2rem;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .feature-card {
            border-radius: 12px;
            margin: 0 -16px;
        }

        .feature-card-header {
            padding: 24px;
        }

        .feature-title {
            font-size: 1.5rem;
        }

        .feature-card-body {
            padding: 24px;
        }

        .feature-list {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .feature-item {
            padding: 20px;
        }

        .intro-content {
            font-size: 1rem;
        }

        .primary-action-btn {
            width: 100%;
            padding: 14px 24px;
        }

        .highlight-text {
            padding: 16px;
        }
    }

    /* 动画效果 */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out;
    }
</style>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 feature-page">
        <!-- 特色卡片 -->
        <div class="feature-card animate-fadeInUp">
            <!-- 卡片头部 -->
            <div class="feature-card-header">
                <h1 class="feature-title">
                    <i class="fas fa-clock"></i>
                    做番茄
                </h1>
                <p class="feature-subtitle">
                    科学的时间管理，高效的工作流程
                </p>
            </div>

            <!-- 卡片内容 -->
            <div class="feature-card-body">
                <!-- 介绍部分 -->
                <div class="intro-section">
                    <h2 class="intro-title">
                        <i class="fas fa-lightbulb"></i>
                        什么是番茄工作法？
                    </h2>
                    <div class="intro-content">
                        番茄工作法是一种简单易行的时间管理方法，它将工作时间划分为25分钟的专注时段（称为"番茄钟"），每个番茄钟后短暂休息5分钟。每完成4个番茄钟后，进行一次较长的休息（15-30分钟）。这种工作-休息的循环有助于保持专注、提高效率、减少疲劳。
                    </div>

                    <div class="highlight-text">
                        <strong>核心优势：</strong> 通过固定的时间间隔，帮助您进入深度工作状态，同时保证充足的休息，避免倦怠。
                    </div>
                </div>

                <!-- 功能特色 -->
                <div class="feature-section">
                    <h2 class="intro-title">
                        <i class="fas fa-star"></i>
                        蒙太奇番茄特色
                    </h2>

                    <div class="feature-list">
                        <div class="feature-item animate-fadeInUp" style="animation-delay: 0.1s;">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h3 class="feature-item-title">智能待办管理</h3>
                            <p class="feature-item-desc">
                                完善的任务管理系统，支持优先级设置、deadline提醒、重复任务等功能，确保每个任务都得到妥善处理。
                            </p>
                        </div>

                        <div class="feature-item animate-fadeInUp" style="animation-delay: 0.2s;">
                            <div class="feature-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h3 class="feature-item-title">智能提醒系统</h3>
                            <p class="feature-item-desc">
                                支持多种提醒方式，包括页面通知、声音提示等，确保您不会错过任何重要的任务和时间安排。
                            </p>
                        </div>

                        <div class="feature-item animate-fadeInUp" style="animation-delay: 0.3s;">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3 class="feature-item-title">数据分析统计</h3>
                            <p class="feature-item-desc">
                                详细的工作时长统计、任务完成情况分析，帮助您了解自己的工作模式，不断优化时间管理策略。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 图片展示 -->
                <div class="image-section">
                    <h2 class="intro-title">
                        <i class="fas fa-eye"></i>
                        界面预览
                    </h2>
                    <div class="image-showcase">
                        <img src="/img/pomo.png" alt="蒙太奇番茄工作法界面"
                             title="蒙太奇做番茄界面 - 简洁高效的时间管理工具">
                    </div>
                </div>

                <!-- 核心价值 -->
                <div class="value-section">
                    <h2 class="intro-title">
                        <i class="fas fa-bullseye"></i>
                        核心价值
                    </h2>
                    <div class="intro-content">
                        蒙太奇做番茄不仅是一个简单的时间追踪工具，更是一套完整的个人效率提升系统。通过科学的番茄工作法，结合智能的任务管理和数据分析，帮助您：
                    </div>

                    <ul style="list-style: none; padding-left: 0; margin: 20px 0;">
                        <li style="display: flex; align-items: flex-start; margin-bottom: 16px; padding-left: 12px;">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1" style="flex-shrink: 0;"></i>
                            <span><strong>提高专注力：</strong> 25分钟专注，5分钟休息的科学循环</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; margin-bottom: 16px; padding-left: 12px;">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1" style="flex-shrink: 0;"></i>
                            <span><strong>减少拖延：</strong> 清晰的任务分解和deadline设定</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; margin-bottom: 16px; padding-left: 12px;">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1" style="flex-shrink: 0;"></i>
                            <span><strong>平衡工作生活：</strong> 合理的休息安排，避免过度劳累</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; margin-bottom: 16px; padding-left: 12px;">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1" style="flex-shrink: 0;"></i>
                            <span><strong>持续改进：</strong> 通过数据分析，优化您的时间管理习惯</span>
                        </li>
                    </ul>
                </div>

                <!-- 行动召唤 -->
                <div class="action-section">
                    <a href="{{ url('/index') }}" class="primary-action-btn">
                        <i class="fas fa-play-circle"></i>
                        立即开始体验
                    </a>
                    <p style="color: #64748b; margin-top: 16px; font-size: 0.95rem;">
                        已有超过10,000+用户通过蒙太奇提升了工作效率
                    </p>
                </div>
            </div>
        </div>

        <!-- 快速导航 -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ url('/tasks') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tasks text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">待办管理</h3>
                <p class="text-gray-600 text-sm">管理您的任务清单，设置优先级和提醒</p>
            </a>

            <a href="{{ url('/pomos') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">番茄记录</h3>
                <p class="text-gray-600 text-sm">查看历史番茄记录，分析工作效率</p>
            </a>

            <a href="{{ url('/statistics') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-bar text-green-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">数据统计</h3>
                <p class="text-gray-600 text-sm">详细的工作数据分析，助您持续改进</p>
            </a>
        </div>
    </div>

    <script>
        // 页面加载动画
        $(document).ready(function() {
            // 为特色项添加滚动动画
            function animateOnScroll() {
                $('.feature-item').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).addClass('animate-fadeInUp');
                    }
                });
            }

            // 初始检查
            animateOnScroll();

            // 滚动时检查
            $(window).scroll(function() {
                animateOnScroll();
            });

            // 特色卡片悬停效果增强
            $('.feature-item').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 主按钮悬停效果
            $('.primary-action-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 快速导航卡片效果
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