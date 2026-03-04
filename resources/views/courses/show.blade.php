@extends('layouts.app')

@section('title', $course->title . ' - 蒙太奇课程')
@section('description', $course->description ?: '蒙太奇在线课程学习平台')



@section('content')

    <style>
        /* 课程详情页面专用样式 */
        .course-detail-page {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* 课程头部 */
        .course-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }

        .course-title-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .course-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
            flex: 1;
            min-width: 300px;
        }

        .course-status {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge.private {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.2);
        }

        .status-badge.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #b45309;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }

        .status-badge.public {
            background: rgba(34, 197, 94, 0.1);
            color: #047857;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .course-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        /* 课程封面 */
        .course-cover {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 32px;
            transition: transform 0.3s ease;
        }

        .course-cover:hover {
            transform: scale(1.01);
        }

        .no-cover {
            height: 200px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 1.125rem;
        }

        /* 课程信息卡片 */
        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: #f8fafc;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: white;
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            background: #f1f5f9;
            color: #3b82f6;
            border-color: #3b82f6;
        }

        .card-body {
            padding: 24px;
        }

        /* 课程详情表格 */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            padding: 12px 0;
            font-weight: 600;
            color: #334155;
            width: 120px;
            vertical-align: top;
        }

        .info-value {
            padding: 12px 0;
            color: #475569;
        }

        /* 难度徽章 */
        .difficulty-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .difficulty-beginner {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .difficulty-intermediate {
            background: rgba(245, 158, 11, 0.1);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .difficulty-advanced {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* 标签样式 */
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .tag-item {
            padding: 4px 12px;
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* 课程结构 */
        .course-structure-card {
            margin-bottom: 40px;
        }

        .structure-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .chapter-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .chapter-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
        }

        .chapter-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chapter-header:hover {
            background: #f1f5f9;
        }

        .chapter-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chapter-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .lesson-list {
            padding: 0;
            margin: 0;
            list-style: none;
            display: none;
        }

        .lesson-list.expanded {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .lesson-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.3s ease;
        }

        .lesson-item:last-child {
            border-bottom: none;
        }

        .lesson-item:hover {
            background: #f8fafc;
        }

        .lesson-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .lesson-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }

        .lesson-icon.video {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
        }

        .lesson-icon.quiz {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .lesson-icon.assignment {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .lesson-icon.reading {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .lesson-icon.folder {
            background: linear-gradient(135deg, #64748b, #94a3b8);
        }

        .lesson-text {
            flex: 1;
        }

        .lesson-name {
            font-weight: 500;
            color: #334155;
            margin-bottom: 4px;
        }

        .lesson-duration {
            font-size: 0.875rem;
            color: #64748b;
        }

        .lesson-type {
            padding: 4px 12px;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .no-structure {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        /* 讨论区侧边栏 */
        .discussion-sidebar {
            position: sticky;
            top: 100px;
        }

        .discussion-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .discussion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .coming-soon {
            text-align: center;
            padding: 40px 20px;
        }

        .coming-soon-icon {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 16px;
            display: block;
        }

        /* 按钮样式优化 */
        .btn-course {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 10px;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-course-primary {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            border: none;
        }

        .btn-course-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-course-success {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            border: none;
        }

        .btn-course-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-course-secondary {
            background: white;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-course-secondary:hover {
            background: #f8fafc;
            color: #3b82f6;
            border-color: #3b82f6;
            transform: translateY(-3px);
        }

        .btn-course:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* 动画效果 */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .course-detail-page {
                padding: 0 16px;
            }

            .course-header {
                padding: 24px;
                border-radius: 16px;
            }

            .course-title {
                font-size: 1.75rem;
            }

            .course-title-section {
                flex-direction: column;
                align-items: stretch;
            }

            .course-actions {
                flex-direction: column;
            }

            .btn-course {
                width: 100%;
                justify-content: center;
            }

            .course-info-grid {
                grid-template-columns: 1fr;
            }

            .info-card {
                margin-bottom: 20px;
            }

            .chapter-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .chapter-meta {
                width: 100%;
                justify-content: space-between;
            }

            .lesson-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .lesson-type {
                align-self: flex-start;
            }
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 course-detail-page">
        <!-- 课程头部 -->
        <div class="course-header animate-fadeIn">
            <div class="course-title-section">
                <h1 class="course-title">{{ $course->title }}</h1>
                <div class="course-status">
                <span class="status-badge {{
                    $course->public_status == 1 ? 'private' :
                    ($course->public_status == 2 ? 'pending' : 'public')
                }}">
                    @if($course->public_status == 1)
                        <i class="fas fa-lock"></i> 私有
                    @elseif($course->public_status == 2)
                        <i class="fas fa-clock"></i> 待审核
                    @elseif($course->public_status == 3)
                        <i class="fas fa-check-circle"></i> 已审核
                    @else
                        <i class="fas fa-question-circle"></i> 未知状态
                    @endif
                </span>
                </div>
            </div>

            <div class="course-actions">
                @if(auth()->id() == $course->created_by)
                    <a href="{{ url('/admin/courses/' . $course->id . '/edit') }}" class="btn btn-course btn-course-primary">
                        <i class="fas fa-edit mr-2"></i>
                        编辑课程
                    </a>
                    <a href="{{ url('/courses/' . $course->id . '/items') }}" class="btn btn-course btn-course-primary">
                        <i class="fas fa-cog mr-2"></i>
                        管理章节
                    </a>
                    <a href="{{ url('/courses') }}" class="btn btn-course btn-course-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回课程列表
                    </a>
                @elseif(!auth()->guest() && !$is_joined)
                    <form action="{{ url('/api/v2/courses/' . $course->id . '/join') }}" method="POST" class="d-inline">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-course btn-course-success">
                            <i class="fas fa-user-plus mr-2"></i>
                            加入课程
                        </button>
                    </form>
                @elseif(!auth()->guest() && $is_joined)
                    <button class="btn btn-course btn-course-success" disabled>
                        <i class="fas fa-check-circle mr-2"></i>
                        已加入课程
                    </button>
                    <a href="{{ url('/study') }}" class="btn btn-course btn-course-primary">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        前往学习中心
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 主内容区域 -->
            <div class="lg:col-span-2">
                <!-- 课程封面 -->
                @if($course->cover_image_url)
                    <img src="{{ $course->cover_image_url }}" class="course-cover" alt="{{ $course->title }}">
                @else
                    <div class="no-cover">
                        <i class="fas fa-book-open mr-2"></i>
                        暂无课程封面
                    </div>
                @endif

                <!-- 课程信息网格 -->
                <div class="course-info-grid">
                    <!-- 基本信息卡片 -->
                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i>
                                课程信息
                            </h3>
                            <button class="toggle-btn" data-toggle="infoBasic">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body" id="infoBasic">
                            <table class="info-table">
                                <tr class="info-row">
                                    <td class="info-label">讲师</td>
                                    <td class="info-value">{{ $course->instructor ?: '未知' }}</td>
                                </tr>
                                <tr class="info-row">
                                    <td class="info-label">平台</td>
                                    <td class="info-value">{{ $course->platform ?: '未知' }}</td>
                                </tr>
                                <tr class="info-row">
                                    <td class="info-label">难度</td>
                                    <td class="info-value">
                                        @switch($course->difficulty)
                                            @case('beginner')
                                                <span class="difficulty-badge difficulty-beginner">
                                                <i class="fas fa-seedling mr-1"></i> 初级
                                            </span>
                                                @break
                                            @case('intermediate')
                                                <span class="difficulty-badge difficulty-intermediate">
                                                <i class="fas fa-tree mr-1"></i> 中级
                                            </span>
                                                @break
                                            @case('advanced')
                                                <span class="difficulty-badge difficulty-advanced">
                                                <i class="fas fa-mountain mr-1"></i> 高级
                                            </span>
                                                @break
                                            @default
                                                <span class="difficulty-badge">
                                                <i class="fas fa-question-circle mr-1"></i> 未知
                                            </span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr class="info-row">
                                    <td class="info-label">预计时长</td>
                                    <td class="info-value">
                                        <i class="far fa-clock text-gray-400 mr-2"></i>
                                        {{ $course->estimated_hours ?: 0 }} 小时
                                    </td>
                                </tr>
                                @if($course->tags && is_array($course->tags))
                                    <tr class="info-row">
                                        <td class="info-label">标签</td>
                                        <td class="info-value">
                                            <div class="tag-list">
                                                @foreach($course->tags as $tag)
                                                    <span class="tag-item">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- 课程描述卡片 -->
                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-alt"></i>
                                课程描述
                            </h3>
                            <button class="toggle-btn" data-toggle="infoDescription">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body" id="infoDescription">
                            <div class="text-gray-600 leading-relaxed">
                                @if($course->description)
                                    {!! nl2br(e($course->description)) !!}
                                @else
                                    <div class="text-center text-gray-400 py-8">
                                        <i class="fas fa-file-alt text-3xl mb-4"></i>
                                        <p>暂无课程描述</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 课程结构卡片 -->
                <div class="info-card course-structure-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sitemap"></i>
                            课程结构
                        </h3>
                        <button class="toggle-btn" data-toggle="courseStructure">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="card-body" id="courseStructure">
                        @if($structure && count($structure) > 0)
                            <div class="structure-list">
                                @foreach($structure as $item)
                                    <div class="chapter-item" data-chapter-id="{{ $item->id }}">
                                        <div class="chapter-header" onclick="toggleChapter({{ $item->id }})">
                                            <div>
                                                <h4 class="chapter-title">
                                                    <i class="fas fa-folder text-yellow-500"></i>
                                                    {{ $item->title }}
                                                </h4>
                                                @if($item->description)
                                                    <p class="text-gray-500 text-sm mt-1">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                            <div class="chapter-meta">
                                            <span class="text-gray-500 text-sm">
                                                {{ count($item->children ?? []) }} 个课时
                                                @if($item->duration)
                                                    · {{ $item->duration }} 分钟
                                                @endif
                                            </span>
                                                <i class="fas fa-chevron-down text-gray-400 transition-transform" id="icon-{{ $item->id }}"></i>
                                            </div>
                                        </div>

                                        @if($item->children && count($item->children) > 0)
                                            <ul class="lesson-list" id="lessons-{{ $item->id }}">
                                                @foreach($item->children as $child)
                                                    <li class="lesson-item">
                                                        <div class="lesson-content">
                                                            <div class="lesson-icon {{ $child->item_type }}">
                                                                @if($child->item_type == 'video')
                                                                    <i class="fas fa-play"></i>
                                                                @elseif($child->item_type == 'quiz')
                                                                    <i class="fas fa-question-circle"></i>
                                                                @elseif($child->item_type == 'assignment')
                                                                    <i class="fas fa-file-alt"></i>
                                                                @elseif($child->item_type == 'reading')
                                                                    <i class="fas fa-book"></i>
                                                                @else
                                                                    <i class="fas fa-file"></i>
                                                                @endif
                                                            </div>
                                                            <div class="lesson-text">
                                                                <div class="lesson-name">{{ $child->title }}</div>
                                                                @if($child->duration)
                                                                    <div class="lesson-duration">
                                                                        <i class="far fa-clock mr-1"></i>
                                                                        {{ $child->duration }} 分钟
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <span class="lesson-type">
                                                        {{ ucfirst($child->item_type) }}
                                                    </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-structure">
                                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                <h4 class="text-gray-600 mb-2">暂无课程内容</h4>
                                <p class="text-gray-400 text-sm">课程管理员尚未添加章节内容</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 侧边栏 -->
            <div class="lg:col-span-1">
                <div class="discussion-sidebar">
                    <div class="discussion-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-comments"></i>
                                课程讨论
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="coming-soon">
                                <i class="fas fa-tools coming-soon-icon"></i>
                                <h4 class="text-gray-600 mb-2">功能即将上线</h4>
                                <p class="text-gray-400 text-sm">课程讨论功能正在开发中，敬请期待</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        $(document).ready(function() {
            // 卡片折叠功能
            $('.toggle-btn').click(function() {
                var target = $(this).data('toggle');
                var $target = $('#' + target);
                var $icon = $(this).find('i');

                $target.slideToggle(300);
                $icon.toggleClass('fa-chevron-down fa-chevron-up');

                // 卡片悬停效果
                $(this).closest('.info-card').toggleClass('expanded');
            });

            // 初始展开课程结构
            $('#courseStructure').show();
            $('[data-toggle="courseStructure"]').find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');

            // 卡片悬停效果
            $('.info-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    if (!$(this).hasClass('expanded')) {
                        $(this).css('transform', 'translateY(0)');
                    }
                }
            );

            // 按钮悬停效果
            $('.btn-course').hover(
                function() {
                    if (!$(this).prop('disabled')) {
                        $(this).css('transform', 'translateY(-3px)');
                    }
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 加入课程走v2接口
            $('form[action*="/courses/"][action$="/join"]').on('submit', function(e) {
                e.preventDefault();
                if (!apiRequest) {
                    alert('API客户端未初始化');
                    return;
                }
                var action = $(this).attr('action');
                var match = action.match(/\/courses\/(\d+)\/join$/);
                if (!match) {
                    this.submit();
                    return;
                }
                var courseId = match[1];
                var $btn = $(this).find('button[type="submit"]');
                var original = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>加入中...');

                apiRequest('POST', '/courses/' + courseId + '/join', {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        window.location.reload();
                        return;
                    }
                    alert((resp && resp.msg) ? resp.msg : '加入课程失败');
                }).catch(function() {
                    alert('网络错误，请稍后重试');
                }).finally(function() {
                    $btn.prop('disabled', false).html(original);
                });
            });
        });

        // 章节展开/收起功能
        function toggleChapter(chapterId) {
            var $lessonList = $('#lessons-' + chapterId);
            var $icon = $('#icon-' + chapterId);

            if ($lessonList.length) {
                $lessonList.toggleClass('expanded');
                $icon.toggleClass('fa-chevron-down fa-chevron-up');

                if ($lessonList.hasClass('expanded')) {
                    $icon.css('transform', 'rotate(180deg)');
                } else {
                    $icon.css('transform', 'rotate(0deg)');
                }
            }
        }

        // 初始展开第一个章节
        $(document).ready(function() {
            if ($('.chapter-item').length > 0) {
                var firstChapter = $('.chapter-item').first().data('chapter-id');
                if (firstChapter) {
                    toggleChapter(firstChapter);
                }
            }
        });
    </script>
@endsection
