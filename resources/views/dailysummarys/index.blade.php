@extends('layouts.app')

@section('title', '日报列表 - 蒙太奇')
@section('description', '查看和管理您的每日工作总结与生活记录')

<style>
    /* 日报页面专用样式 */
    .daily-summary-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* 页面标题区域 */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title i {
        color: #4a90e2;
    }

    .page-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* 日报卡片 */
    .summary-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 24px;
        position: relative;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .summary-card-header {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
    }

    .summary-date {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
    }

    .summary-date i {
        color: #4a90e2;
    }

    .summary-actions {
        display: flex;
        gap: 8px;
    }

    .summary-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: white;
        color: #64748b;
        border: 1px solid #cbd5e1;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .summary-action-btn:hover {
        background: #f8fafc;
        color: #4a90e2;
        border-color: #4a90e2;
        transform: translateY(-2px);
    }

    .summary-action-btn.delete:hover {
        color: #ef4444;
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.05);
    }

    .summary-card-body {
        padding: 24px;
    }

    /* 内容区域 */
    .content-section {
        margin-bottom: 24px;
    }

    .content-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }

    .section-title i {
        color: #4a90e2;
        font-size: 0.9rem;
    }

    .section-title.work i {
        color: #10b981;
    }

    .section-title.life i {
        color: #8a6cff;
    }

    .section-content {
        color: #475569;
        line-height: 1.7;
        font-size: 0.95rem;
        padding-left: 24px;
        position: relative;
        white-space: pre-line;
        word-wrap: break-word;
    }

    .section-content::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #e2e8f0;
        border-radius: 1.5px;
    }

    .content-section.work .section-content::before {
        background: #10b981;
    }

    .content-section.life .section-content::before {
        background: #8a6cff;
    }

    /* 空状态 */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state img {
        max-width: 180px;
        height: auto;
        margin-bottom: 24px;
        opacity: 0.8;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .empty-state-text {
        color: #64748b;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 24px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state-action {
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
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    }

    .empty-state-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .empty-state-action i {
        margin-right: 10px;
    }

    /* 分页样式 */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 40px;
        padding-top: 32px;
        border-top: 1px solid #e2e8f0;
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

    /* 错误消息样式 */
    .error-message {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid rgba(239, 68, 68, 0.2);
        margin-bottom: 32px;
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

    /* 响应式调整 */
    @media (max-width: 768px) {
        .daily-summary-page {
            padding: 0 16px;
        }

        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .page-actions {
            justify-content: center;
        }

        .summary-card {
            border-radius: 12px;
        }

        .summary-card-header {
            padding: 16px 20px;
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .summary-actions {
            align-self: flex-end;
        }

        .summary-card-body {
            padding: 20px;
        }

        .section-content {
            padding-left: 16px;
        }

        .empty-state {
            padding: 40px 16px;
        }

        .empty-state img {
            max-width: 140px;
        }
    }

    /* 日期格式化 */
    .date-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(59, 130, 246, 0.1);
        color: #4a90e2;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* 标签样式 */
    .summary-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
    }

    .summary-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="daily-summary-page">
            <!-- 页面标题区域 -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-newspaper"></i>
                    日报列表
                </h1>
                <div class="page-actions">
                    <a href="{{ url('/dailycreate') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        新的日总结
                    </a>
                    <a href="{{ url('/index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回首页
                    </a>
                </div>
            </div>

            <!-- 成功消息 -->
            @include('common.success')

            <!-- 错误消息 -->
            @include('common.errors')

            @if (count($dailysummarys) > 0)
                <!-- 日报列表 -->
                <div id="dailySummaryList">
                    @foreach ($dailysummarys as $dailysummary)
                        <div class="summary-card animate-fadeInUp" id="summary-{{ $dailysummary->id }}">
                            <!-- 日报头部 -->
                            <div class="summary-card-header">
                                <div class="summary-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>{{ $dailysummary->summary_date }}</span>
                                    <span class="date-badge">
                                    <i class="far fa-clock"></i>
                                    {{ \Carbon\Carbon::parse($dailysummary->created_at)->format('H:i') }}
                                </span>
                                </div>
                                <div class="summary-actions">
                                    <a href="{{ url('dailysummary/'.$dailysummary->id) }}" class="summary-action-btn" title="编辑日报">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="summary-action-btn delete delete_dailysummary"
                                            dailysummary_value="{{ $dailysummary->id }}"
                                            title="删除日报">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- 日报内容 -->
                            <div class="summary-card-body">
                                <!-- 工作总结 -->
                                @if(!empty($dailysummary->work_content))
                                    <div class="content-section work">
                                        <h3 class="section-title work">
                                            <i class="fas fa-briefcase"></i>
                                            工作总结
                                        </h3>
                                        <div class="section-content">
                                            {{ $dailysummary->work_content }}
                                        </div>
                                    </div>
                                @endif

                                <!-- 生活总结 -->
                                @if(!empty($dailysummary->life_content))
                                    <div class="content-section life">
                                        <h3 class="section-title life">
                                            <i class="fas fa-heart"></i>
                                            生活总结
                                        </h3>
                                        <div class="section-content">
                                            {{ $dailysummary->life_content }}
                                        </div>
                                    </div>
                                @endif

                                <!-- 标签区域（如果有额外字段可以展示） -->
                                @if($dailysummary->created_at != $dailysummary->updated_at)
                                    <div class="summary-tags">
                                    <span class="summary-tag">
                                        <i class="fas fa-history"></i>
                                        最后更新: {{ \Carbon\Carbon::parse($dailysummary->updated_at)->diffForHumans() }}
                                    </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 分页 -->
                <div class="pagination-wrapper">
                    {!! $dailysummarys->links() !!}
                </div>
            @else
                <!-- 空状态 -->
                <div class="empty-state animate-fadeInUp">
                    <img src="/img/new/love.png" alt="暂无日报" class="mx-auto">
                    <h3 class="empty-state-title">今天发生了点什么呢？</h3>
                    <p class="empty-state-text">
                        记录下今天的收获与感悟，让每一天都有迹可循。<br>
                        无论是工作中的成就，还是生活中的小确幸，都值得被记住。
                    </p>
                    <a href="{{ url('/dailycreate') }}" class="empty-state-action">
                        <i class="fas fa-pen-alt"></i>
                        开始记录日报
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        $(document).ready(function() {
            // 删除功能
            $(".delete_dailysummary").click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                const summary_value = $(this).attr("dailysummary_value");
                if (!confirm("确认要删除此日报吗？")) {
                    return false;
                }

                const $card = $('#summary-' + summary_value);
                if (!apiRequest) {
                    showNotification('API客户端未初始化', 'error');
                    return false;
                }

                apiRequest('DELETE', '/daily-summaries/' + summary_value, {}).then(function(result_arr) {
                    if (!result_arr || result_arr.code != 9999) {
                        showNotification('删除失败，请稍后再试', 'error');
                        return;
                    }

                    // 添加删除动画
                    $card.css({
                        'transform': 'scale(0.95)',
                        'opacity': '0.5'
                    });

                    setTimeout(() => {
                        $card.css({
                            'transform': 'translateX(100%)',
                            'opacity': '0'
                        });

                        setTimeout(() => {
                            $card.remove();
                            showNotification('日报删除成功', 'success');

                            // 如果没有日报了，显示空状态
                            if ($('.summary-card').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }, 100);
                }).catch(function() {
                    showNotification('网络错误，删除失败', 'error');
                });
            });

            // 卡片悬停效果
            $('.summary-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 按钮悬停效果
            $('.summary-action-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 页面加载动画
            $('.animate-fadeInUp').css({
                'opacity': '0',
                'transform': 'translateY(20px)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });

            setTimeout(() => {
                $('.animate-fadeInUp').css({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                });
            }, 100);

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
                setTimeout(() => {
                    notification.find('div:first').removeClass('translate-x-full');
                }, 10);

                // 3秒后自动隐藏
                setTimeout(() => {
                    notification.find('div:first').addClass('translate-x-full');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>
@endsection
