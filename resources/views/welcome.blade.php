@extends('layouts.app')

@section('title', '蒙太奇 - 知而善用，行稳致远')
@section('description', '利用番茄工作法结合待办列表来高效完成每一件事，实时统计，笔记记录，RSS阅读，思维导图，订阅推送到kindle来帮助你记录更多想法，希望它可以帮你更多')

@section('content')
<style>
    /* 首页专用样式 */
    .homepage-hero {
        background-image:
                linear-gradient(135deg, rgba(59, 130, 246, 0.85), rgba(139, 92, 246, 0.85)),
                url('/img/index_background2.jpg');
        background-size: cover;
        background-position: center;
        background-blend-mode: normal; /* 或直接删除这一行 */
        border-radius: 24px;
        margin: 40px auto;
        padding: 80px 40px;
        position: relative;
        overflow: hidden;
        max-width: 1200px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .homepage-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    }

    .hero-content {
        position: relative;
        z-index: 1;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 24px;
        line-height: 1.2;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .hero-subtitle {
        font-size: 1.5rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 48px;
        line-height: 1.6;
        text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
    }

    .hero-description {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 60px;
        line-height: 1.7;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .hero-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 40px;
        font-size: 1.125rem;
        font-weight: 600;
        background: white;
        color: #4a90e2;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
    }

    .hero-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(255, 255, 255, 0.4);
        color: #4a90e2;
    }

    .hero-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 40px;
        font-size: 1.125rem;
        font-weight: 600;
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .hero-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        transform: translateY(-3px);
        color: white;
    }

    /* 核心功能展示 */
    .core-features {
        max-width: 1200px;
        margin: 80px auto;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #4a90e2, #8a6cff);
        border-radius: 2px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 32px;
    }

    .feature-card {
        background: white;
        border-radius: 16px;
        padding: 40px 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-align: center;
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
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        color: white;
        font-size: 2rem;
        transition: transform 0.3s ease;
    }

    .feature-card:hover .feature-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .feature-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .feature-description {
        color: #64748b;
        line-height: 1.6;
        font-size: 1rem;
        margin-bottom: 24px;
    }

    .feature-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #4a90e2;
        font-weight: 500;
        text-decoration: none;
        font-size: 0.95rem;
        transition: gap 0.3s ease;
    }

    .feature-link:hover {
        color: #8a6cff;
        gap: 12px;
    }

    /* 数据统计展示 */
    .stats-section {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 24px;
        padding: 80px 40px;
        margin: 80px auto;
        max-width: 1200px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        margin-top: 60px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        color: #4a90e2;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label {
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
    }

    /* CTA区域 */
    .cta-section {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        border-radius: 24px;
        padding: 80px 40px;
        margin: 80px auto;
        max-width: 1200px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        opacity: 0.5;
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }

    .cta-description {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 48px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
        position: relative;
        z-index: 1;
    }

    .cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 18px 48px;
        font-size: 1.25rem;
        font-weight: 600;
        background: white;
        color: #4a90e2;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 15px 30px rgba(255, 255, 255, 0.3);
        position: relative;
        z-index: 1;
    }

    .cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(255, 255, 255, 0.4);
        color: #4a90e2;
    }

    /* 优势特点 */
    .advantages-section {
        max-width: 1200px;
        margin: 80px auto;
    }

    .advantages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 32px;
    }

    .advantage-item {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        padding: 32px;
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .advantage-item:hover {
        border-color: #4a90e2;
        box-shadow: 0 15px 30px rgba(59, 130, 246, 0.1);
        transform: translateY(-5px);
    }

    .advantage-icon {
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

    .advantage-content h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .advantage-content p {
        color: #64748b;
        line-height: 1.6;
        font-size: 1rem;
    }

    /* 动画效果 */
    @keyframes floatIn {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-floatIn {
        animation: floatIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .animate-fadeIn {
        animation: fadeIn 1s ease-out;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .homepage-hero {
            margin: 20px 16px;
            padding: 60px 24px;
            border-radius: 16px;
        }

        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
        }

        .hero-actions {
            flex-direction: column;
            align-items: center;
        }

        .hero-btn-primary, .hero-btn-secondary {
            width: 100%;
            max-width: 300px;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 40px;
        }

        .feature-card {
            padding: 32px 24px;
        }

        .features-grid, .advantages-grid {
            gap: 24px;
        }

        .stats-section, .cta-section {
            padding: 60px 24px;
            margin: 60px 16px;
        }

        .cta-title {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .advantage-item {
            padding: 24px;
        }
    }

    @media (max-width: 480px) {
        .hero-title {
            font-size: 1.75rem;
        }

        .hero-subtitle {
            font-size: 1.125rem;
        }

        .section-title {
            font-size: 1.75rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            font-size: 1.75rem;
        }
    }
</style>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 首页英雄区域 -->
        <div class="homepage-hero animate-floatIn">
            <div class="hero-content">
                <h1 class="hero-title">蒙太奇 - 知而善用，行稳致远</h1>
                <p class="hero-subtitle">不止于GTD，陪你做好每件事，在这里定格更多精彩瞬间</p>
                <p class="hero-description">
                    利用番茄工作法结合待办列表来高效完成每一件事，实时统计，笔记记录，RSS阅读，思维导图，订阅推送到kindle来帮助你记录更多想法
                </p>

                <div class="hero-actions">
                    <a href="{{ url('/login') }}" class="hero-btn-primary">
                        <i class="fas fa-play-circle mr-3"></i>
                        现在就开始吧
                    </a>
                    <a href="#features" class="hero-btn-secondary">
                        <i class="fas fa-info-circle mr-3"></i>
                        了解更多功能
                    </a>
                </div>

                <div class="text-white text-sm">
                    <i class="fas fa-users mr-2"></i>
                    已有超过 10,000+ 用户使用蒙太奇提升效率
                </div>
            </div>
        </div>

        <!-- 核心功能展示 -->
        <section id="features" class="core-features animate-fadeIn">
            <h2 class="section-title">核心功能</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">做番茄</h3>
                    <p class="feature-description">
                        科学的番茄工作法，25分钟专注工作，5分钟休息，结合待办列表高效完成每项任务。
                    </p>
                    <a href="{{ url('/') }}" class="feature-link">
                        立即体验
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="feature-title">记想法</h3>
                    <p class="feature-description">
                        快速捕捉灵感，支持文字、图片、语音录入，浏览器插件一键保存网页内容。
                    </p>
                    <a href="{{ url('/notes') }}" class="feature-link">
                        开始记录
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3 class="feature-title">去阅读</h3>
                    <p class="feature-description">
                        订阅优质博客和媒体，自动推送到Kindle，构建个人知识库，享受沉浸式阅读。
                    </p>
                    <a href="{{ url('/articles') }}" class="feature-link">
                        开始阅读
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="feature-title">绘导图</h3>
                    <p class="feature-description">
                        可视化思维工具，发散思维，结构化思考，激发创意，整理复杂信息。
                    </p>
                    <a href="{{ url('/minds') }}" class="feature-link">
                        绘制导图
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- 优势特点 -->
{{--        <section class="advantages-section animate-fadeIn">--}}
{{--            <h2 class="section-title">为什么选择蒙太奇？</h2>--}}
{{--            <div class="advantages-grid">--}}
{{--                <div class="advantage-item">--}}
{{--                    <div class="advantage-icon">--}}
{{--                        <i class="fas fa-bolt"></i>--}}
{{--                    </div>--}}
{{--                    <div class="advantage-content">--}}
{{--                        <h3>效率倍增</h3>--}}
{{--                        <p>科学的番茄工作法+智能待办管理，让您的工作效率提升300%以上。</p>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="advantage-item">--}}
{{--                    <div class="advantage-icon">--}}
{{--                        <i class="fas fa-sync-alt"></i>--}}
{{--                    </div>--}}
{{--                    <div class="advantage-content">--}}
{{--                        <h3>全平台同步</h3>--}}
{{--                        <p>Web端 + 移动端 + 浏览器插件 + Kindle推送，数据实时同步，随时随地使用。</p>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="advantage-item">--}}
{{--                    <div class="advantage-icon">--}}
{{--                        <i class="fas fa-shield-alt"></i>--}}
{{--                    </div>--}}
{{--                    <div class="advantage-content">--}}
{{--                        <h3>数据安全</h3>--}}
{{--                        <p>端到端加密，私有化部署可选，您的数据完全由您掌控。</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </section>--}}

{{--        <!-- 数据统计展示 -->--}}
{{--        <section class="stats-section animate-fadeIn">--}}
{{--            <h2 class="section-title" style="color: #1e293b;">用户成就</h2>--}}
{{--            <div class="stats-grid">--}}
{{--                <div class="stat-item">--}}
{{--                    <div class="stat-number">10,000+</div>--}}
{{--                    <div class="stat-label">活跃用户</div>--}}
{{--                </div>--}}
{{--                <div class="stat-item">--}}
{{--                    <div class="stat-number">2.5M+</div>--}}
{{--                    <div class="stat-label">完成任务</div>--}}
{{--                </div>--}}
{{--                <div class="stat-item">--}}
{{--                    <div class="stat-number">500K+</div>--}}
{{--                    <div class="stat-label">记录想法</div>--}}
{{--                </div>--}}
{{--                <div class="stat-item">--}}
{{--                    <div class="stat-number">100K+</div>--}}
{{--                    <div class="stat-label">思维导图</div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </section>--}}

        <!-- CTA区域 -->
        <section class="cta-section animate-floatIn">
            <h2 class="cta-title">立即开始您的高效之旅</h2>
            <p class="cta-description">
                蒙太奇不仅仅是一个工具，更是您个人效率提升的伙伴。从今天开始，让每一分钟都创造价值。
            </p>
            <a href="{{ url('/login') }}" class="cta-btn">
                <i class="fas fa-rocket mr-3"></i>
                免费开始使用
            </a>
            <p style="color: rgba(255, 255, 255, 0.8); margin-top: 24px; font-size: 0.95rem;">
                <i class="fas fa-check-circle mr-2"></i>
                无需信用卡，永久免费基础版
            </p>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            // 滚动动画
            function animateOnScroll() {
                $('.animate-fadeIn').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).css('opacity', '1');
                    }
                });
            }

            // 初始隐藏动画元素
            $('.animate-fadeIn').css({
                'opacity': '0',
                'transition': 'opacity 0.8s ease'
            });

            // 初始检查
            setTimeout(animateOnScroll, 100);

            // 滚动时检查
            $(window).scroll(function() {
                animateOnScroll();
            });

            // 功能卡片悬停效果
            $('.feature-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-10px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 优势项悬停效果
            $('.advantage-item').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 按钮悬停效果
            $('.hero-btn-primary, .hero-btn-secondary, .cta-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 功能链接悬停效果
            $('.feature-link').hover(
                function() {
                    $(this).css('gap', '12px');
                },
                function() {
                    $(this).css('gap', '8px');
                }
            );

            // 滚动到功能区域
            $('a[href="#features"]').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('#features').offset().top - 80
                }, 800);
            });

            // 平滑滚动
            $('a[href^="#"]').on('click', function(e) {
                if ($(this).attr('href') !== '#') {
                    e.preventDefault();
                    var target = $(this).attr('href');
                    if ($(target).length) {
                        $('html, body').animate({
                            scrollTop: $(target).offset().top - 80
                        }, 800);
                    }
                }
            });

            // 数字动画效果
            function animateNumbers() {
                $('.stat-number').each(function() {
                    var $this = $(this);
                    var originalText = $this.text();
                    var number = parseInt(originalText.replace(/[^0-9]/g, ''));

                    if (!isNaN(number) && $this.data('animated') !== true) {
                        $this.data('animated', true);

                        // 移除原有内容，准备动画
                        $this.text('0');

                        // 数字增长动画
                        var counter = 0;
                        var increment = Math.ceil(number / 50); // 分50步完成
                        var timer = setInterval(function() {
                            counter += increment;
                            if (counter >= number) {
                                counter = number;
                                clearInterval(timer);
                            }
                            $this.text(formatNumber(counter) + originalText.replace(/[0-9]/g, ''));
                        }, 30);
                    }
                });
            }

            // 数字格式化
            function formatNumber(num) {
                if (num >= 1000000) {
                    return (num / 1000000).toFixed(1) + 'M';
                } else if (num >= 1000) {
                    return (num / 1000).toFixed(1) + 'K';
                }
                return num.toString();
            }

            // 当统计区域进入视口时触发数字动画
            function checkStatsVisibility() {
                var statsTop = $('.stats-section').offset().top;
                var statsBottom = statsTop + $('.stats-section').outerHeight();
                var viewportTop = $(window).scrollTop();
                var viewportBottom = viewportTop + $(window).height();

                if (statsBottom > viewportTop && statsTop < viewportBottom) {
                    animateNumbers();
                }
            }

            // 初始检查
            setTimeout(checkStatsVisibility, 500);

            // 滚动时检查
            $(window).scroll(function() {
                checkStatsVisibility();
            });
        });
    </script>
@endsection