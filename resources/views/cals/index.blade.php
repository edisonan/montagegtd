@extends('layouts.app')

@section('title', '日历配置 - 蒙太奇')
@section('description', '配置您的个人日历和订阅公共日历，实现日程同步和提醒功能')

<style>
    /* 日历配置页面专用样式 */
    .calendar-config-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* 页面标题 */
    .page-title-section {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title i {
        color: #4a90e2;
    }

    /* 配置说明卡片 */
    .instruction-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 32px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .instruction-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .instruction-card-header {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        padding: 20px 32px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .instruction-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .back-link {
        color: white;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        transition: all 0.3s ease;
    }

    .back-link:hover {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    .instruction-card-body {
        padding: 32px;
    }

    .instruction-content {
        display: flex;
        gap: 32px;
        align-items: flex-start;
    }

    .calendar-illustration {
        flex-shrink: 0;
        text-align: center;
    }

    .calendar-illustration img {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .calendar-illustration img:hover {
        transform: scale(1.03);
    }

    .instruction-steps {
        flex: 1;
    }

    .instruction-steps h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }

    .step-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .step-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .step-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .step-number {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.95rem;
        margin-right: 16px;
        flex-shrink: 0;
    }

    .step-content h5 {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .step-content h5 i {
        color: #4a90e2;
        font-size: 0.9rem;
    }

    .step-content p {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
    }

    /* 日历地址卡片 */
    .calendar-url-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 32px;
    }

    .calendar-url-card-header {
        background: #f8fafc;
        padding: 20px 32px;
        border-bottom: 1px solid #e2e8f0;
    }

    .calendar-url-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .calendar-url-card-title i {
        color: #4a90e2;
    }

    .calendar-url-card-body {
        padding: 32px;
    }

    /* 个人日历地址区域 */
    .personal-calendar {
        margin-bottom: 40px;
    }

    .url-display {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
        position: relative;
    }

    .url-text {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.95rem;
        color: #475569;
        word-break: break-all;
        padding-right: 120px;
        line-height: 1.5;
    }

    .url-actions {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 8px;
    }

    .url-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        font-size: 0.875rem;
        font-weight: 500;
        background: white;
        color: #4a90e2;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .url-action-btn:hover {
        background: #4a90e2;
        color: white;
        border-color: #4a90e2;
        transform: translateY(-2px);
    }

    .url-action-btn i {
        margin-right: 6px;
        font-size: 0.9rem;
    }

    .url-hint {
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.5;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .url-hint i {
        color: #f59e0b;
        margin-top: 2px;
    }

    /* 公共日历列表 */
    .public-calendars {
        margin-top: 48px;
    }

    .public-calendars-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .calendar-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .calendar-item {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .calendar-item:hover {
        border-color: #4a90e2;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
        transform: translateY(-3px);
    }

    .calendar-theme {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .calendar-theme i {
        color: #8a6cff;
        font-size: 1.1rem;
    }

    .calendar-url {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.85rem;
        color: #475569;
        word-break: break-all;
        background: #f8fafc;
        padding: 12px;
        border-radius: 8px;
        border: 1px dashed #cbd5e1;
        margin-bottom: 16px;
        line-height: 1.4;
    }

    .calendar-actions {
        display: flex;
        gap: 8px;
    }

    .calendar-action-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        font-size: 0.85rem;
        font-weight: 500;
        background: white;
        color: #4a90e2;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .calendar-action-btn:hover {
        background: #4a90e2;
        color: white;
        border-color: #4a90e2;
    }

    .calendar-action-btn i {
        margin-right: 6px;
        font-size: 0.8rem;
    }

    /* 成功消息样式 */
    .success-message {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.5s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .success-message i {
        font-size: 1.2rem;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .calendar-config-page {
            padding: 0 16px;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .instruction-content {
            flex-direction: column;
        }

        .calendar-illustration {
            text-align: center;
            width: 100%;
        }

        .calendar-illustration img {
            width: 150px;
            height: 150px;
        }

        .instruction-card-body,
        .calendar-url-card-body {
            padding: 24px;
        }

        .url-display {
            padding: 16px;
        }

        .url-text {
            padding-right: 0;
            margin-bottom: 16px;
        }

        .url-actions {
            position: static;
            display: flex;
            justify-content: flex-end;
        }

        .calendar-list {
            grid-template-columns: 1fr;
        }

        .instruction-card-header,
        .calendar-url-card-header {
            padding: 16px 24px;
        }

        .step-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
    }

    /* 动画效果 */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.6s ease-out;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="calendar-config-page">
            <!-- 页面标题 -->
            <div class="page-title-section animate-fadeIn">
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt"></i>
                    日历配置
                </h1>
            </div>

            <!-- 成功消息 -->
            @include('common.success')

            <!-- 配置说明卡片 -->
            <div class="instruction-card animate-fadeIn">
                <div class="instruction-card-header">
                    <h3 class="instruction-card-title">
                        <i class="fas fa-info-circle"></i>
                        配置说明
                    </h3>
                    <a href="{{ url('/') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        返回首页
                    </a>
                </div>

                <div class="instruction-card-body">
                    <div class="instruction-content">
                        <div class="calendar-illustration">
                            <img src="/img/kindle.jpg" alt="日历同步示意图"
                                 title="日历同步配置 - 实现跨平台日程管理">
                        </div>

                        <div class="instruction-steps">
                            <h4>快速配置指南</h4>
                            <ul class="step-list">
                                <li class="step-item">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <h5>
                                            <i class="fas fa-mobile-alt"></i>
                                            iOS设备方案一
                                        </h5>
                                        <p>复制下方地址，在Safari浏览器中打开即可自动订阅日历</p>
                                    </div>
                                </li>

                                <li class="step-item">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <h5>
                                            <i class="fas fa-cog"></i>
                                            iOS设备方案二
                                        </h5>
                                        <p>进入"设置" → "账户与密码" → "添加账户" → "其他" → "添加已订阅的日历"，粘贴下方地址完成配置</p>
                                    </div>
                                </li>

                                <li class="step-item">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <h5>
                                            <i class="fab fa-android"></i>
                                            Android设备方案
                                        </h5>
                                        <p>使用支持订阅日历的App（如Google日历），添加订阅地址即可同步日历事件</p>
                                    </div>
                                </li>

                                <li class="step-item">
                                    <div class="step-number">4</div>
                                    <div class="step-content">
                                        <h5>
                                            <i class="fas fa-desktop"></i>
                                            电脑端方案
                                        </h5>
                                        <p>在Outlook、Google日历、iCal等日历软件中添加订阅地址，实现多端同步</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 日历地址卡片 -->
            <div class="calendar-url-card animate-fadeIn">
                <div class="calendar-url-card-header">
                    <h3 class="calendar-url-card-title">
                        <i class="fas fa-link"></i>
                        日历订阅地址
                    </h3>
                </div>

                <div class="calendar-url-card-body">
                    <!-- 个人日历地址 -->
                    <div class="personal-calendar">
                        <h4 class="calendar-url-card-title" style="font-size: 1.125rem; margin-bottom: 16px;">
                            <i class="fas fa-user-circle"></i>
                            个人日历地址
                        </h4>

                        <div class="url-display">
                            <div class="url-text" id="personalCalUrl">
                                {{ $person_cal_url }}
                            </div>
                            <div class="url-actions">
                                <button class="url-action-btn" onclick="copyToClipboard('{{ $person_cal_url }}')">
                                    <i class="fas fa-copy"></i>
                                    复制地址
                                </button>
                                <a href="{{ $person_cal_url }}" class="url-action-btn" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    测试访问
                                </a>
                            </div>
                        </div>

                        <div class="url-hint">
                            <i class="fas fa-lightbulb"></i>
                            <span>这是您的专属日历地址，包含所有个人日程和待办事项，请妥善保管</span>
                        </div>
                    </div>

                    <!-- 公共日历列表 -->
                    <div class="public-calendars">
                        <div class="public-calendars-header">
                            <h4 class="calendar-url-card-title" style="font-size: 1.125rem; margin: 0;">
                                <i class="fas fa-users"></i>
                                公共日历地址
                            </h4>
                            <span class="text-sm text-gray-600">{{ count($cals) }} 个日历</span>
                        </div>

                        @if(count($cals) > 0)
                            <div class="calendar-list">
                                @foreach ($cals as $cal)
                                    <div class="calendar-item">
                                        <h5 class="calendar-theme">
                                            <i class="fas fa-calendar-week"></i>
                                            {{ $cal['theme'] }}
                                        </h5>
                                        <div class="calendar-url">
                                            {{ $cal['url'] }}
                                        </div>
                                        <div class="calendar-actions">
                                            <button class="calendar-action-btn" onclick="copyToClipboard('{{ $cal['url'] }}')">
                                                <i class="fas fa-copy"></i>
                                                复制
                                            </button>
                                            <a href="{{ $cal['url'] }}" class="calendar-action-btn" target="_blank">
                                                <i class="fas fa-eye"></i>
                                                预览
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <i class="fas fa-calendar-times text-gray-400 text-3xl mb-4"></i>
                                <p class="text-gray-600">暂无公共日历</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 复制到剪贴板功能
            window.copyToClipboard = function(text) {
                // 创建临时textarea元素
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);

                // 选择并复制文本
                textarea.select();
                textarea.setSelectionRange(0, 99999); // 对于移动设备

                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        showNotification('日历地址已复制到剪贴板', 'success');
                    } else {
                        showNotification('复制失败，请手动复制', 'error');
                    }
                } catch (err) {
                    console.error('复制失败:', err);
                    showNotification('复制失败，请手动复制', 'error');
                }

                // 清理临时元素
                document.body.removeChild(textarea);
            };

            // 显示通知
            function showNotification(message, type = 'success') {
                const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                const notification = $(
                    '<div class="fixed top-4 right-4 z-50 max-w-sm w-full">' +
                    '<div class="' + bgColor + ' text-white p-4 rounded-lg shadow-lg flex items-center justify-between transform translate-x-full transition-transform duration-300">' +
                    '<div class="flex items-center">' +
                    '<i class="fas ' + icon + ' mr-3"></i>' +
                    '<span>' + message + '</span>' +
                    '</div>' +
                    '<button class="text-white hover:text-gray-200 ml-4" onclick="$(this).closest(\'.fixed\').remove()">' +
                    '<i class="fas fa-times"></i>' +
                    '</button>' +
                    '</div>' +
                    '</div>'
                );

                $('body').append(notification);

                // 显示通知
                setTimeout(function() {
                    notification.find('div:first').removeClass('translate-x-full');
                }, 10);

                // 3秒后自动隐藏
                setTimeout(function() {
                    notification.find('div:first').addClass('translate-x-full');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 3000);
            }

            // 卡片悬停效果
            $('.instruction-card, .calendar-item').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 按钮悬停效果
            $('.url-action-btn, .calendar-action-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 图片悬停效果
            $('.calendar-illustration img').hover(
                function() {
                    $(this).css('transform', 'scale(1.03)');
                },
                function() {
                    $(this).css('transform', 'scale(1)');
                }
            );

            // 页面加载动画
            $('.animate-fadeIn').css({
                'opacity': '0',
                'transform': 'translateY(20px)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });

            setTimeout(() => {
                $('.animate-fadeIn').css({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                });
            }, 100);
        });
    </script>
@endsection