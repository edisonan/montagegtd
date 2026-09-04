@extends('layouts.app')

@section('title', '课程详情 - 蒙太奇课程')
@section('description', '蒙太奇在线课程学习平台')



@section('content')

    <style>
        /* 课程详情页面专用样式 */
        .course-detail-page {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* 顶部信息面板：封面 + 标题 + 描述 + 信息合并 */
        .course-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
            border: 1px solid rgba(59, 130, 246, 0.12);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 24px;
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

        .course-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
        }

        .course-desc {
            color: #475569;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* 封面 */
        .course-cover {
            width: 100%;
            height: 240px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .no-cover {
            height: 240px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 1.125rem;
        }

        /* 信息chip */
        .course-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            font-size: 13px;
            color: #475569;
        }

        .course-chip i {
            color: #8b5cf6;
            font-size: 12px;
        }

        .tag-item {
            padding: 4px 12px;
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* 状态徽章 */
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

        /* 按钮样式 */
        .btn-course {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 10px;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-course-sm {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 8px;
        }

        .btn-course-primary {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            border: none;
        }

        .btn-course-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-course-success {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            border: none;
        }

        .btn-course-success:hover {
            transform: translateY(-2px);
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
            transform: translateY(-2px);
        }

        .btn-course-danger {
            background: white;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .btn-course-danger:hover {
            background: #fef2f2;
            border-color: #dc2626;
            transform: translateY(-2px);
        }

        .btn-course:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-course a { text-decoration: none; }

        /* 左侧课程结构树 */
        .tree-box { max-height: calc(100vh - 160px); overflow-y: auto; }
        .tree-list { list-style: none; margin: 0; padding: 0; }
        .tree-list ul { list-style: none; }
        .tree-children {
            display: none;
            margin-left: 12px;
            padding-left: 10px;
            border-left: 1px dashed #e2e8f0;
        }
        .tree-children.open { display: block; }

        .tree-node {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: #475569;
            font-size: 14px;
            transition: background 0.15s ease;
            user-select: none;
        }
        .tree-node:hover { background: #f1f5f9; }
        .tree-node.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        .tree-node.active .tree-label { color: #1d4ed8; }
        .tree-caret {
            width: 14px;
            display: inline-flex;
            justify-content: center;
            color: #94a3b8;
            flex-shrink: 0;
        }
        .tree-caret i { transition: transform 0.2s ease; font-size: 11px; }
        .tree-container.open > .tree-caret i { transform: rotate(90deg); }
        .tree-node-icon { flex-shrink: 0; font-size: 13px; width: 16px; text-align: center; }
        .tree-label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tree-count {
            flex-shrink: 0;
            font-size: 11px;
            color: #94a3b8;
            background: #f1f5f9;
            border-radius: 999px;
            padding: 1px 8px;
        }
        .tree-duration {
            flex-shrink: 0;
            font-size: 11px;
            color: #94a3b8;
        }

        /* 右侧章节详情 */
        .detail-card { overflow: hidden; }
        .detail-header {
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 12px;
        }

        /* 测验弹窗 */
        .quiz-modal { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; background: rgba(15, 23, 42, .55); padding: 20px; }
        .quiz-modal.show { display: flex; }
        .quiz-modal-card { width: min(720px, 100%); max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 16px; padding: 24px; }
        .quiz-question { border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        .quiz-option { display: block; padding: 8px 10px; border-radius: 8px; margin-top: 6px; background: #f8fafc; }
        .quiz-result { border-radius: 10px; padding: 14px; margin-top: 16px; background: #eff6ff; }

        /* 动画 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        /* 章节类型小标记 */
        .type-label {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .type-label.module { background: rgba(100, 116, 139, 0.12); color: #475569; }
        .type-label.chapter { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .type-label.video { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .type-label.quiz { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .type-label.assignment { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
        .type-label.reading { background: rgba(245, 158, 11, 0.12); color: #b45309; }

        /* Markdown 正文渲染 */
        .md-content { line-height: 1.8; color: #334155; overflow-wrap: break-word; }
        .md-content h1, .md-content h2, .md-content h3, .md-content h4 { font-weight: 700; color: #1e293b; margin: 14px 0 8px; }
        .md-content h1 { font-size: 1.25rem; }
        .md-content h2 { font-size: 1.125rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .md-content h3 { font-size: 1rem; }
        .md-content h4 { font-size: 0.95rem; }
        .md-content p { margin: 8px 0; }
        .md-content ul, .md-content ol { padding-left: 22px; margin: 8px 0; }
        .md-content li { margin: 4px 0; }
        .md-content a { color: #2563eb; text-decoration: underline; }
        .md-content strong { font-weight: 700; color: #1e293b; }
        .md-content blockquote { border-left: 3px solid #cbd5e1; padding-left: 12px; color: #64748b; margin: 10px 0; }
        .md-content code { background: #e2e8f0; padding: 1px 6px; border-radius: 5px; font-size: 13px; color: #dc2626; }
        .md-content pre { background: #0f172a; color: #e2e8f0; padding: 12px 16px; border-radius: 10px; overflow-x: auto; margin: 10px 0; }
        .md-content pre code { background: transparent; color: inherit; padding: 0; font-size: 13px; }
        .md-content table { border-collapse: collapse; margin: 10px 0; width: 100%; }
        .md-content th, .md-content td { border: 1px solid #e2e8f0; padding: 6px 10px; font-size: 13px; }
        .md-content th { background: #f8fafc; font-weight: 600; }
        .md-content img { max-width: 100%; border-radius: 8px; }
        .md-content hr { border: 0; border-top: 1px solid #e2e8f0; margin: 16px 0; }

        /* 响应式 */
        @media (max-width: 768px) {
            .course-detail-page { padding: 0 16px; }
            .course-header { padding: 20px; border-radius: 16px; }
            .course-title { font-size: 1.5rem; }
            .course-actions .btn-course { width: auto; }
            .tree-box { max-height: 320px; }
        }
    </style>

    @include('components.course-item-modal')
    @include('artifacts._dialog')

    <script src="{{ asset('js/marked.min.js') }}"></script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 course-detail-page">
        <!-- 顶部信息面板：封面 + 标题 + 描述 + 信息合并 -->
        <div class="course-header animate-fadeIn">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- 封面 -->
                <div class="shrink-0 w-full lg:w-80" id="course_cover_box">
                    <div class="no-cover">
                        <i class="fas fa-book-open mr-2"></i>暂无课程封面
                    </div>
                </div>

                <!-- 标题/状态/描述/信息/操作 -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <h1 class="course-title" id="course_title_text">课程详情</h1>
                        <div class="flex items-center gap-2 flex-wrap" id="course_status_box"></div>
                    </div>

                    <p class="course-desc mt-4" id="course_desc_box"></p>

                    <div class="mt-4 flex flex-wrap gap-2" id="course_chips_box"></div>
                    <div class="mt-3 flex flex-wrap gap-2" id="course_tags_box"></div>

                    <div class="mt-6 flex flex-wrap gap-3" id="course_actions_box"></div>
                </div>
            </div>
        </div>

        <!-- 课程结构：左2（章节树） + 右8（章节详情） -->
        <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <!-- 左：课程结构树 -->
            <div class="lg:col-span-2">
                <div class="card p-4 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-sitemap text-blue-500"></i>
                            课程结构
                        </h3>
                        <div class="flex items-center gap-2">
                            <a href="javascript:void(0)" id="fullManageLink" class="hidden text-gray-400 hover:text-blue-600 transition" title="课程管理（基础信息 / 章节 / 测试 / 制品）" style="display:none">
                                <i class="fas fa-cog"></i>
                            </a>
                            <button type="button" id="btnAddTopItem" class="hidden btn-course btn-course-primary btn-course-sm" style="display:none">
                                <i class="fas fa-plus mr-1"></i>添加章节
                            </button>
                        </div>
                    </div>
                    <div class="tree-box" id="course_structure_box">
                        <div class="text-center text-gray-400 py-8 text-sm">
                            <i class="fas fa-spinner fa-spin mr-2"></i>加载中...
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右：章节详情 -->
            <div class="lg:col-span-8">
                <!-- 待复习（已加入课程且有到期复习项时显示） -->
                <div class="card p-4 mb-6 hidden" id="reviewPanel">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-sync-alt text-amber-500"></i>待复习
                        </h3>
                        <span class="text-xs text-gray-400" id="reviewPanelCount"></span>
                    </div>
                    <div class="space-y-2" id="reviewPanelList"></div>
                </div>
                <div class="card detail-card">
                    <div class="detail-header px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-900 text-lg truncate" id="item_detail_title">课程概览</h3>
                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-2 flex-wrap" id="item_detail_subtitle"></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2" id="item_action_box"></div>
                    </div>
                    <div class="p-5" id="item_detail_body">
                        <div class="text-center text-gray-400 py-10">
                            <i class="fas fa-book-open text-4xl mb-4 text-gray-300"></i>
                            <p>点击左侧章节查看详情</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="quizModal" class="quiz-modal">
        <div class="quiz-modal-card">
            <div class="flex items-center justify-between mb-4">
                <h3 id="quizModalTitle" class="text-xl font-semibold text-gray-900">章节小测试</h3>
                <div class="flex items-center gap-3">
                    <button type="button" id="quizHistoryBtn" class="btn-course btn-course-secondary btn-course-sm hidden"><i class="fas fa-history mr-1"></i>作答历史</button>
                    <button type="button" id="closeQuizBtn" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
                </div>
            </div>
            <div id="quizLoading" class="text-gray-500 py-6 text-center">加载测试中...</div>
            <form id="quizForm" class="hidden"></form>
            <div id="quizResult" class="hidden"></div>
            <div id="quizHistory" class="hidden"></div>
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var CURRENT_USER_ID = Number('{{ auth()->id() ?: 0 }}');
        var COURSE_DETAIL_ID = (function() {
            var parts = (window.location.pathname || '').split('/').filter(Boolean);
            var last = parts.length ? parts[parts.length - 1] : '';
            var parsed = parseInt(last, 10);
            return Number.isFinite(parsed) ? parsed : 0;
        })();
        var COURSE_DETAIL_DATA = null;
        var IS_OWNER = false;
        var SELECTED_ITEM_ID = (function() {
            var m = (window.location.search || '').match(/[?&]item=(\d+)/);
            return m ? Number(m[1]) : null;
        })();

        // 保存后刷新整棵树（供 course-item-modal 组件调用）
        window.refreshCourseStructure = function() { loadCourseDetail(); };

        function escapeHtml(str) {
            return $('<div>').text(str || '').html();
        }

        function getResultData(resp) {
            if (!resp) return {};
            return resp.result || resp.data || {};
        }

        function getStatusBadgeHtml(publicStatus) {
            if (Number(publicStatus) === 1) return '<span class="status-badge private"><i class="fas fa-lock"></i> 私有</span>';
            if (Number(publicStatus) === 2) return '<span class="status-badge pending"><i class="fas fa-clock"></i> 待审核</span>';
            if (Number(publicStatus) === 3) return '<span class="status-badge public"><i class="fas fa-check-circle"></i> 已公开</span>';
            return '<span class="status-badge"><i class="fas fa-question-circle"></i> 未知状态</span>';
        }

        function getDifficultyHtml(difficulty) {
            if (difficulty === 'beginner') return '<span class="difficulty-badge difficulty-beginner"><i class="fas fa-seedling mr-1"></i> 初级</span>';
            if (difficulty === 'intermediate') return '<span class="difficulty-badge difficulty-intermediate"><i class="fas fa-tree mr-1"></i> 中级</span>';
            if (difficulty === 'advanced') return '<span class="difficulty-badge difficulty-advanced"><i class="fas fa-mountain mr-1"></i> 高级</span>';
            return '';
        }

        function itemIcon(type) {
            if (type === 'video') return 'fa-play';
            if (type === 'quiz') return 'fa-question-circle';
            if (type === 'assignment') return 'fa-file-alt';
            if (type === 'reading') return 'fa-book';
            return 'fa-file';
        }

        function typeLabelHtml(type) {
            var text = String(type || '');
            var map = { module: '模块', chapter: '章节', video: '视频', quiz: '测验', assignment: '作业', reading: '阅读' };
            return '<span class="type-label ' + escapeHtml(text) + '">' + escapeHtml(map[text] || text.toUpperCase()) + '</span>';
        }

        // ---------- 顶部信息面板 ----------
        function renderCourseInfo(course) {
            $('#course_title_text').text(course.title || '课程详情');
            $('#course_status_box').html(
                (getStatusBadgeHtml(course.public_status) || '') +
                getDifficultyHtml(course.difficulty)
            );
            document.title = (course.title || '课程详情') + ' - 蒙太奇课程';

            if (course.cover_image_url) {
                $('#course_cover_box').html('<img src="' + escapeHtml(course.cover_image_url) + '" class="course-cover" alt="' + escapeHtml(course.title || 'course') + '">');
            } else {
                $('#course_cover_box').html('<div class="no-cover"><i class="fas fa-book-open mr-2"></i>暂无课程封面</div>');
            }

            if (course.description) {
                $('#course_desc_box').html(String(escapeHtml(course.description)).replace(/\n/g, '<br>'));
            } else {
                $('#course_desc_box').html('<span class="text-gray-400">暂无课程描述</span>');
            }

            var chips = '';
            if (course.instructor) chips += '<span class="course-chip"><i class="fas fa-user-tie"></i>' + escapeHtml(course.instructor) + '</span>';
            if (course.platform) chips += '<span class="course-chip"><i class="fas fa-globe"></i>' + escapeHtml(course.platform) + '</span>';
            chips += '<span class="course-chip"><i class="far fa-clock"></i>' + Number(course.estimated_hours || 0) + ' 小时</span>';
            chips += '<span class="course-chip"><i class="fas fa-layer-group"></i>' + Number(course.chapters_count || 0) + ' 章</span>';
            chips += '<span class="course-chip"><i class="fas fa-users"></i>' + Number(course.enrollment_count || 0) + ' 人学习</span>';
            $('#course_chips_box').html(chips);

            var tagsHtml = '';
            if (Array.isArray(course.tags) && course.tags.length) {
                tagsHtml = course.tags.map(function(tag){ return '<span class="tag-item">' + escapeHtml(tag) + '</span>'; }).join('');
            }
            $('#course_tags_box').html(tagsHtml);
        }

        // ---------- 顶部操作按钮 ----------
        function renderCourseActions(course, isJoined, isOwner) {
            var box = $('#course_actions_box');
            if (!box.length) return;
            var html = '';
            if (isOwner) {
                html += '<a href="/courses/' + Number(course.id) + '/manage" class="btn-course btn-course-secondary"><i class="fas fa-cog mr-2"></i>管理课程</a>';
                var st = Number(course.public_status || 1);
                if (st === 1) {
                    html += '<button type="button" onclick="submitCourseReview(' + Number(course.id) + ', \'request-public\')" class="btn-course btn-course-primary"><i class="fas fa-eye mr-2"></i>提交公开审核</button>';
                } else if (st === 2) {
                    html += '<button type="button" onclick="submitCourseReview(' + Number(course.id) + ', \'approve\')" class="btn-course btn-course-success"><i class="fas fa-check-circle mr-2"></i>审核通过并公开</button>';
                } else if (st === 3) {
                    html += '<button type="button" onclick="submitCourseReview(' + Number(course.id) + ', \'unapprove\')" class="btn-course btn-course-secondary"><i class="fas fa-eye-slash mr-2"></i>撤回公开</button>';
                }
                // 创建者同样可以加入自己的课程进行学习
                if (!isJoined) {
                    html += '<button type="button" class="btn-course btn-course-success join-course-btn" data-course-id="' + Number(course.id) + '"><i class="fas fa-user-plus mr-2"></i>加入课程</button>';
                } else {
                    html += '<span class="btn-course btn-course-success" disabled><i class="fas fa-check-circle mr-2"></i>已加入</span>';
                    html += '<a href="/courses/' + Number(course.id) + '/study" class="btn-course btn-course-primary"><i class="fas fa-graduation-cap mr-2"></i>继续学习</a>';
                }
            } else if (CURRENT_USER_ID > 0 && !isJoined) {
                html += '<button type="button" class="btn-course btn-course-success join-course-btn" data-course-id="' + Number(course.id) + '"><i class="fas fa-user-plus mr-2"></i>加入课程</button>';
            } else if (CURRENT_USER_ID > 0 && isJoined) {
                html += '<span class="btn-course btn-course-success" disabled><i class="fas fa-check-circle mr-2"></i>已加入</span>';
                html += '<a href="/courses/' + Number(course.id) + '/study" class="btn-course btn-course-primary"><i class="fas fa-graduation-cap mr-2"></i>继续学习</a>';
            }
            html += '<a href="/course/management" class="btn-course btn-course-secondary"><i class="fas fa-arrow-left mr-2"></i>返回课程中心</a>';
            box.html(html);

            // 左侧结构区的管理按钮（仅课程所有者可见）
            $('#btnAddTopItem').toggle(isOwner).css('display', isOwner ? '' : 'none');
            var manageLink = $('#fullManageLink');
            manageLink.toggle(isOwner).css('display', isOwner ? '' : 'none');
            if (isOwner) {
                manageLink.attr('href', '/courses/' + Number(course.id) + '/manage');
            }
        }

        // 课程公开状态操作：request-public / approve / unapprove
        function submitCourseReview(courseId, action) {
            if (!apiRequest) {
                alert('API客户端未初始化');
                return;
            }
            var tip = '确认执行此操作吗？';
            var successMsg = action === 'approve' ? '课程已公开' : (action === 'unapprove' ? '已撤回公开' : '已提交公开审核');
            if (action === 'approve') {
                tip = '确认将该课程审核通过并公开吗？';
            } else if (action === 'unapprove') {
                tip = '确认撤回公开吗？撤回后课程转为待审核状态。';
            } else {
                tip = '确认提交公开审核吗？审核通过后所有用户可见。';
            }
            if (!confirm(tip)) return;
            apiRequest('POST', '/courses/' + Number(courseId) + '/' + action, {}).then(function(resp) {
                if (resp && resp.code === 9999) {
                    alert(successMsg);
                    loadCourseDetail();
                    return;
                }
                alert((resp && resp.msg) ? resp.msg : '操作失败');
            }).catch(function() {
                alert('网络错误，请稍后重试');
            });
        }

        // ---------- 左侧课程结构树 ----------
        function renderCourseStructure(structure) {
            var box = $('#course_structure_box');
            if (!Array.isArray(structure) || !structure.length) {
                box.html('<div class="text-center text-gray-400 py-8 text-sm"><i class="fas fa-inbox text-3xl mb-3 text-gray-300"></i><p>暂无课程内容</p>' + (IS_OWNER ? '<p class="mt-2 text-xs">点击右上角「添加章节」开始构建课程</p>' : '<p class="mt-2 text-xs">课程管理员尚未添加章节内容</p>') + '</div>');
                return;
            }
            box.html('<ul class="tree-list">' + renderTreeNodes(structure) + '</ul>');
        }

        function renderTreeNodes(items) {
            var html = '';
            items.forEach(function(item) {
                var children = Array.isArray(item.children) ? item.children : [];
                var isContainer = children.length > 0 || item.item_type === 'module' || item.item_type === 'chapter';
                var nodeId = Number(item.id || 0);
                var iconClass = isContainer ? 'fas fa-folder text-yellow-500' : 'fas ' + itemIcon(item.item_type) + ' text-blue-500';
                if (isContainer) {
                    html += '<li class="tree-item">'
                        + '<div class="tree-node tree-container" data-item-id="' + nodeId + '">'
                        + '<span class="tree-caret"><i class="fas fa-chevron-right"></i></span>'
                        + '<i class="' + iconClass + ' tree-node-icon"></i>'
                        + '<span class="tree-label">' + escapeHtml(item.title || '') + '</span>'
                        + (children.length ? '<span class="tree-count">' + children.length + '</span>' : '')
                        + '</div>'
                        + (children.length ? '<ul class="tree-children">' + renderTreeNodes(children) + '</ul>' : '')
                        + '</li>';
                } else {
                    html += '<li class="tree-item">'
                        + '<div class="tree-node tree-leaf" data-item-id="' + nodeId + '">'
                        + '<i class="' + iconClass + ' tree-node-icon"></i>'
                        + '<span class="tree-label">' + escapeHtml(item.title || '') + '</span>'
                        + (item.duration ? '<span class="tree-duration">' + Number(item.duration) + '′</span>' : '')
                        + '</div></li>';
                }
            });
            return html;
        }

        function openTreeNode(treePath) {
            var $node = $('.tree-node[data-item-id="' + treePath + '"]').first();
            var $children = $node.next('.tree-children');
            $children.toggleClass('open');
            $node.toggleClass('open');
        }

        function selectCourseItem(id) {
            if (!COURSE_DETAIL_DATA || !Array.isArray(COURSE_DETAIL_DATA.structure)) return;
            var item = findItemInTree(COURSE_DETAIL_DATA.structure, id);
            if (!item) return;
            SELECTED_ITEM_ID = Number(id);
            $('.tree-node').removeClass('active');
            var $node = $('.tree-node[data-item-id="' + Number(id) + '"]').first();
            if ($node.length) $node.addClass('active');
            // 展开选中节点的祖先链（重渲染后保持可见）
            expandAncestorsOf(COURSE_DETAIL_DATA.structure, Number(id));
            renderItemDetail(item);
        }

        // 递归展开包含目标节点的祖先容器
        function expandAncestorsOf(items, id) {
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (Number(item.id) === Number(id)) return true;
                if (Array.isArray(item.children) && item.children.length) {
                    var found = expandAncestorsOf(item.children, id);
                    if (found) {
                        var $node = $('.tree-node[data-item-id="' + Number(item.id) + '"]').first();
                        $node.addClass('open');
                        $node.next('.tree-children').addClass('open');
                        return true;
                    }
                }
            }
            return false;
        }

        // ---------- 右侧章节详情 ----------
        function renderItemOverview() {
            var course = COURSE_DETAIL_DATA ? COURSE_DETAIL_DATA.course : {};
            $('#item_detail_title').text(course.title || '课程概览');
            $('#item_detail_subtitle').html('');
            $('#item_action_box').html('');
            var html = '<div class="text-center text-gray-400 py-10">'
                + '<i class="fas fa-book-open text-4xl mb-4 text-gray-300"></i>'
                + '<p class="text-gray-500">点击左侧章节树查看章节详情</p>'
                + '</div>';
            $('#item_detail_body').html(html);
        }

        function renderItemDetail(item) {
            var container = (item.children && item.children.length > 0) || item.item_type === 'module' || item.item_type === 'chapter';
            $('#item_detail_title').text(item.title || '未命名章节');
            var subtitle = typeLabelHtml(item.item_type);
            if (item.duration) subtitle += '<span class="meta-chip"><i class="far fa-clock"></i>' + Number(item.duration) + ' 分钟</span>';
            if (item.item_type !== 'module') subtitle += '<span class="meta-chip"><i class="fas fa-hashtag"></i>排序 ' + Number(item.order_index || 0) + '</span>';
            $('#item_detail_subtitle').html(subtitle);

            // 顶部操作按钮
            var actions = '';
            if (IS_OWNER) {
                actions += '<button type="button" class="btn-course btn-course-secondary btn-course-sm" onclick="openEditItem(' + Number(item.id) + ')"><i class="fas fa-edit mr-1"></i>编辑</button>';
                if (container) {
                    actions += '<button type="button" class="btn-course btn-course-primary btn-course-sm" onclick="openCourseItemModal(COURSE_DETAIL_ID, null, ' + Number(item.id) + ')"><i class="fas fa-plus mr-1"></i>添加子章节</button>';
                }
                actions += '<button type="button" class="btn-course btn-course-danger btn-course-sm" onclick="deleteCourseItem(' + Number(item.id) + ', \'' + escapeHtml(item.title || '').replace(/'/g, "\\'") + '\')"><i class="fas fa-trash-alt mr-1"></i>删除</button>';
            }
            if (COURSE_DETAIL_DATA && COURSE_DETAIL_DATA.is_joined && !container) {
                actions += '<button type="button" class="btn-course btn-course-success btn-course-sm quiz-btn" data-course-item-id="' + Number(item.id) + '"><i class="fas fa-question-circle mr-1"></i>小测试</button>';
                actions += '<button type="button" class="btn-course btn-course-secondary btn-course-sm complete-course-item-btn" data-course-item-id="' + Number(item.id) + '"><i class="fas fa-check mr-1"></i>标记完成</button>';
            }
            $('#item_action_box').html(actions);

            // 详情正文
            var html = '';
            if (item.description) {
                html += '<div class="mb-5"><h4 class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-align-left text-gray-400 mr-2"></i>章节描述</h4>'
                    + '<p class="text-sm text-gray-600 leading-relaxed">' + String(escapeHtml(item.description)).replace(/\n/g, '<br>') + '</p></div>';
            }
            if (item.external_url) {
                html += '<div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl flex flex-wrap items-center justify-between gap-3">'
                    + '<div class="text-sm text-gray-700"><i class="fas fa-link text-blue-500 mr-2"></i>外部学习资源</div>'
                    + '<a href="' + escapeHtml(item.external_url) + '" target="_blank" rel="noopener" class="btn-course btn-course-primary btn-course-sm"><i class="fas fa-external-link-alt mr-1"></i>打开链接</a>'
                    + '</div>';
            }
            if (item.content && String(item.content).trim()) {
                html += '<div><h4 class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-file-alt text-gray-400 mr-2"></i>章节正文（Markdown）</h4>'
                    + '<div class="md-content text-sm bg-gray-50 border border-gray-200 rounded-xl p-4">' + escapeHtml(item.content) + '</div></div>';
            }
            if (!html) {
                html = '<div class="text-center text-gray-400 py-10"><i class="fas fa-folder-open text-3xl mb-3 text-gray-300"></i><p>该章节暂无内容</p></div>';
            }

            // 章节正文 markdown 渲染（marked + 轻量清理）
            $('#item_detail_body').html(html);
            renderMarkdownBlocks();

            // AI 制品区（登录用户可见）：把课程文章内容加入制品库，支持可视化界面/思维导图/关键信息
            if (CURRENT_USER_ID > 0) {
                var artifactHtml = '<div class="mt-6 border-t border-gray-200 pt-4">'
                    + '<div class="flex items-center justify-between mb-2"><h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-wand-magic-sparkles text-purple-500"></i>AI 制品</h4></div>'
                    + '<p class="text-xs text-gray-500 mb-2">把本章内容加入制品库，生成可视化阅读 / 思维导图 / 关键信息 / AIPPT</p>'
                    + '<div class="flex flex-wrap gap-2">'
                    + '<button type="button" class="btn-course btn-course-primary btn-course-sm course-artifact-btn" data-artifact-type="visual_reading"><i class="fas fa-book-open mr-1"></i>可视化界面</button>'
                    + '<button type="button" class="btn-course btn-course-primary btn-course-sm course-artifact-btn" data-artifact-type="mind_map"><i class="fas fa-diagram-project mr-1"></i>思维导图</button>'
                    + '<button type="button" class="btn-course btn-course-primary btn-course-sm course-artifact-btn" data-artifact-type="key_points"><i class="fas fa-lightbulb mr-1"></i>关键信息</button>'
                    + '<button type="button" class="btn-course btn-course-primary btn-course-sm course-artifact-btn" data-artifact-type="ai_ppt"><i class="fas fa-file-powerpoint mr-1"></i>AIPPT</button>'
                    + '<a href="/artifacts" class="btn-course btn-course-secondary btn-course-sm"><i class="fas fa-box-archive mr-1"></i>制品库</a>'
                    + '</div></div>';
                $('#item_detail_body').append(artifactHtml);
            }
        }

        // 渲染 markdown（复用 marked，做轻量安全清理）
        function renderMarkdownBlocks() {
            var blocks = document.querySelectorAll('#item_detail_body .md-content');
            blocks.forEach(function (el) {
                var raw = String(el.textContent || '');
                if (!raw.trim()) return;
                var rendered = null;
                try {
                    if (window.marked && typeof window.marked.parse === 'function') {
                        rendered = window.marked.parse(raw, { gfm: true, breaks: true });
                    } else if (window.marked) {
                        rendered = window.marked(raw);
                    }
                } catch (e) { return; }
                if (!rendered) return;
                var tmp = document.createElement('div');
                tmp.innerHTML = rendered;
                tmp.querySelectorAll('script, iframe, object, embed, link, meta, style').forEach(function (n) { n.remove(); });
                tmp.querySelectorAll('*').forEach(function (n) {
                    Array.prototype.slice.call(n.attributes).forEach(function (attr) {
                        if (/^on/i.test(attr.name)) n.removeAttribute(attr.name);
                    });
                });
                el.innerHTML = tmp.innerHTML;
            });
        }

        function findItemInTree(items, id) {
            for (var i = 0; i < items.length; i++) {
                if (Number(items[i].id) === Number(id)) return items[i];
                if (Array.isArray(items[i].children)) {
                    var found = findItemInTree(items[i].children, id);
                    if (found) return found;
                }
            }
            return null;
        }

        // ---------- 所有者操作 ----------
        function openEditItem(id) {
            if (!apiRequest) return;
            apiRequest('GET', '/course-items/' + Number(id), {}).then(function(resp) {
                if (resp && resp.code === 9999 && resp.result && resp.result.course_item) {
                    openCourseItemModal(COURSE_DETAIL_ID, resp.result.course_item);
                    return;
                }
                alert((resp && resp.msg) ? resp.msg : '获取章节信息失败');
            }).catch(function() {
                alert('网络错误，请稍后重试');
            });
        }

        function deleteCourseItem(id, title) {
            if (!apiRequest) return;
            Swal.fire({
                title: '确定要删除这个章节吗？',
                text: '「' + title + '」删除后不可恢复',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '删除',
                cancelButtonText: '取消',
                confirmButtonColor: '#dc2626'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                apiRequest('DELETE', '/course-items/' + Number(id), {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        Swal.fire('已删除', resp.msg || '删除成功', 'success').then(function() {
                            SELECTED_ITEM_ID = null;
                            loadCourseDetail();
                        });
                        return;
                    }
                    Swal.fire('删除失败', (resp && resp.msg) ? resp.msg : '未知错误', 'error');
                }).catch(function() {
                    Swal.fire('删除失败', '网络错误，请稍后重试', 'error');
                });
            });
        }

        // ---------- 数据加载 ----------
        function loadCourseDetail() {
            if (!apiRequest || !COURSE_DETAIL_ID) return;
            apiRequest('GET', '/courses/' + COURSE_DETAIL_ID, {}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    throw new Error((resp && resp.msg) ? resp.msg : '加载失败');
                }
                var result = getResultData(resp);
                var course = result.course || {};
                var structure = Array.isArray(result.structure) ? result.structure : [];
                var isJoined = !!result.is_joined;
                COURSE_DETAIL_DATA = { course: course, structure: structure, is_joined: isJoined };
                IS_OWNER = !!course.is_owner;

                renderCourseInfo(course);
                renderCourseActions(course, isJoined, IS_OWNER);
                renderCourseStructure(structure);
                if (isJoined) loadCourseReviews();

                if (SELECTED_ITEM_ID && findItemInTree(structure, SELECTED_ITEM_ID)) {
                    selectCourseItem(SELECTED_ITEM_ID);
                    return;
                }
                // 默认选中第一个可展示的节点
                var first = findFirstSelectable(structure);
                if (first) {
                    selectCourseItem(Number(first.id));
                } else {
                    renderItemOverview();
                }
            }).catch(function() {
                alert('课程加载失败，请稍后重试');
            });
        }

        function findFirstSelectable(items) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].item_type === 'module' && Array.isArray(items[i].children) && items[i].children.length) {
                    var inner = findFirstSelectable(items[i].children);
                    if (inner) return inner;
                    continue;
                }
                return items[i];
            }
            return null;
        }

        $(document).ready(function() {
            loadCourseDetail();

            // 章节树：点击容器展开/收起并选中；点击叶子直接选中
            $(document).on('click', '.tree-node', function(e) {
                e.preventDefault();
                var id = Number($(this).data('item-id') || 0);
                if (!id) return;
                if ($(this).hasClass('tree-container')) {
                    openTreeNode(id);
                }
                if (findItemInTree(COURSE_DETAIL_DATA ? COURSE_DETAIL_DATA.structure : [], id)) {
                    selectCourseItem(id);
                }
            });

            // 添加顶级章节
            $('#btnAddTopItem').on('click', function() {
                openCourseItemModal(COURSE_DETAIL_ID);
            });

            // 加入课程
            $(document).on('click', '.join-course-btn', function(e) {
                e.preventDefault();
                if (!apiRequest) { alert('API客户端未初始化'); return; }
                var courseId = Number($(this).data('course-id') || 0);
                var $btn = $(this);
                var original = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>加入中...');
                apiRequest('POST', '/courses/' + courseId + '/join', {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        loadCourseDetail();
                        return;
                    }
                    alert((resp && resp.msg) ? resp.msg : '加入课程失败');
                }).catch(function() {
                    alert('网络错误，请稍后重试');
                }).finally(function() {
                    $btn.prop('disabled', false).html(original);
                });
            });

            // 标记课时完成
            $(document).on('click', '.complete-course-item-btn', function(e) {
                e.preventDefault();
                if (!apiRequest) { alert('API客户端未初始化'); return; }
                var $btn = $(this);
                if ($btn.hasClass('done')) return;
                var itemId = Number($btn.data('course-item-id') || 0);
                var original = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>处理中');
                apiRequest('POST', '/course-items/' + itemId + '/complete', {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        $btn.addClass('done').html('<i class="fas fa-check-circle mr-1"></i>已完成').prop('disabled', true);
                        return;
                    }
                    alert((resp && resp.msg) ? resp.msg : '标记完成失败');
                    $btn.prop('disabled', false).html(original);
                }).catch(function() {
                    alert('网络错误，请稍后重试');
                    $btn.prop('disabled', false).html(original);
                });
            });

            // 测验弹窗
            $('#closeQuizBtn').on('click', closeQuiz);
            $('#quizHistoryBtn').on('click', function() { openQuizHistory(quizState.itemId); });
            $('#quizModal').on('click', function(e) { if (e.target === this) closeQuiz(); });
            $(document).on('click', '.quiz-btn', function(e) {
                e.preventDefault();
                openQuiz(Number($(this).data('course-item-id') || 0));
            });
            $('#quizForm').on('submit', function(e) { e.preventDefault(); submitQuiz(); });

            // 生成 AI 制品（把课程章节内容加入制品库）
            $(document).on('click', '.course-artifact-btn', function(e) {
                e.preventDefault();
                if (!SELECTED_ITEM_ID || typeof window.openArtifactDialog !== 'function') return;
                window.openArtifactDialog({
                    relatedType: 'course_item',
                    relatedId: SELECTED_ITEM_ID,
                    artifactType: String($(this).data('artifact-type') || 'key_points')
                });
            });
        });

        function closeQuiz() {
            $('#quizModal').removeClass('show');
            $('#quizResult').addClass('hidden').empty();
            $('#quizHistory').addClass('hidden').empty();
        }

        // 测验弹窗状态缓存（返回测验时保留已填表单 / 已出结果）
        var quizState = { itemId: 0, formHtml: '', resultHtml: '', questions: null, view: 'form' };

        function openQuiz(itemId) {
            if (!apiRequest || !itemId) return;
            quizState = { itemId: itemId, formHtml: '', resultHtml: '', questions: null, view: 'form' };
            $('#quizModalTitle').text('章节小测试');
            $('#quizModal').addClass('show');
            $('#quizLoading').removeClass('hidden').text('加载测试中...');
            $('#quizForm').addClass('hidden').empty();
            $('#quizResult').addClass('hidden').empty();
            $('#quizHistory').addClass('hidden').empty();
            $('#quizHistoryBtn').addClass('hidden');
            $('#quizForm').data('item-id', itemId);
            apiRequest('GET', '/course-items/' + itemId + '/quiz', {}).then(function(resp) {
                if (!resp || resp.code !== 9999 || !resp.result || !resp.result.quiz) throw new Error((resp && resp.msg) || '该章节暂无测试');
                var quiz = resp.result.quiz;
                var html = '<p class="text-sm text-gray-500 mb-4">通过分数：' + Number(quiz.passing_score || 70) + '%</p>';
                (quiz.questions || []).forEach(function(question, index) {
                    var multiple = question.question_type === 'multiple';
                    html += '<div class="quiz-question"><div class="font-medium text-gray-900">' + (index + 1) + '. ' + escapeHtml(question.question || '') + '</div>';
                    (question.options || []).forEach(function(option) {
                        html += '<label class="quiz-option"><input type="' + (multiple ? 'checkbox' : 'radio') + '" name="quiz-answer-' + Number(question.id) + '" value="' + escapeHtml(option.option_key || '') + '" class="mr-2">' + escapeHtml(option.option_key || '') + '. ' + escapeHtml(option.content || '') + '</label>';
                    });
                    html += '</div>';
                });
                html += '<button type="submit" class="btn-course btn-course-primary">提交测试</button>';
                quizState.formHtml = html;
                quizState.questions = quiz.questions || [];
                $('#quizForm').html(html).removeClass('hidden');
                $('#quizLoading').addClass('hidden');
                $('#quizHistoryBtn').removeClass('hidden');
            }).catch(function(err) {
                renderQuizEmpty(itemId, (err && err.message) ? err.message : '该章节还没有配置小测试');
            });
        }

        // 章节没有测试时的友好空态（不再是一片空白/报错文本）
        function renderQuizEmpty(itemId, msg) {
            var html = '<div class="py-10 text-center">'
                + '<i class="fas fa-question-circle text-4xl text-gray-300 mb-3" style="display:block"></i>'
                + '<p class="text-gray-600 mb-1">' + escapeHtml(msg || '该章节还没有配置小测试') + '</p>'
                + '<p class="text-xs text-gray-400 mb-5">可以稍后再来看看，或联系课程创建者补充测验。</p>'
                + (IS_OWNER ? '<a href="/courses/' + COURSE_DETAIL_ID + '/manage?tab=tests" class="btn-course btn-course-primary btn-course-sm"><i class="fas fa-cog mr-1"></i>去配置测验</a> ' : '')
                + '<button type="button" class="btn-course btn-course-secondary btn-course-sm" onclick="closeQuiz()">关闭</button>'
                + '</div>';
            $('#quizLoading').html(html).removeClass('hidden');
            $('#quizForm').addClass('hidden').empty();
            $('#quizResult').addClass('hidden').empty();
            $('#quizHistory').addClass('hidden').empty();
            $('#quizHistoryBtn').addClass('hidden');
        }

        function showQuizView(itemId) {
            $('#quizHistory').addClass('hidden').empty();
            if (itemId !== quizState.itemId) { openQuiz(itemId); return; }
            $('#quizHistoryBtn').removeClass('hidden');
            if (quizState.view === 'result' && quizState.resultHtml) {
                $('#quizForm').addClass('hidden');
                $('#quizResult').html(quizState.resultHtml).removeClass('hidden');
            } else if (quizState.formHtml) {
                $('#quizResult').addClass('hidden').empty();
                $('#quizForm').removeClass('hidden');
            } else {
                openQuiz(itemId);
            }
        }

        function openQuizHistory(itemId) {
            if (!apiRequest || !itemId) return;
            $('#quizLoading').addClass('hidden').empty();
            $('#quizForm').addClass('hidden');
            $('#quizResult').addClass('hidden').empty();
            $('#quizHistory').removeClass('hidden').html(
                '<div class="text-center text-gray-400 py-8"><i class="fas fa-spinner fa-spin mr-2"></i>加载作答记录...</div>'
            );
            apiRequest('GET', '/course-items/' + itemId + '/quiz/attempts', {}).then(function(resp) {
                var attempts = (resp && resp.code === 9999 && resp.result && Array.isArray(resp.result.attempts)) ? resp.result.attempts : [];
                if (!attempts.length) {
                    $('#quizHistory').html(
                        '<div class="text-center text-gray-400 py-10"><i class="fas fa-inbox text-3xl mb-3" style="display:block"></i>'
                        + '<p>还没有作答记录</p>'
                        + '<button type="button" class="btn-course btn-course-primary btn-course-sm mt-4" onclick="showQuizView(' + itemId + ')">返回测验</button></div>'
                    );
                    return;
                }
                var qMap = {};
                if (quizState.itemId === itemId && Array.isArray(quizState.questions)) {
                    quizState.questions.forEach(function(q) { qMap[Number(q.id)] = q; });
                }
                var html = '<div class="flex items-center justify-between mb-3">'
                    + '<span class="text-sm font-semibold text-gray-700"><i class="fas fa-history mr-1"></i>共 ' + attempts.length + ' 次作答</span>'
                    + '<button type="button" class="btn-course btn-course-secondary btn-course-sm" onclick="showQuizView(' + itemId + ')">返回测验</button>'
                    + '</div>';
                attempts.forEach(function(a, index) {
                    var passed = !!a.passed;
                    var isLast = index === 0;
                    html += '<div class="quiz-result" style="background:' + (passed ? '#ecfdf5' : '#fef2f2') + '">'
                        + '<div class="flex items-center justify-between flex-wrap gap-1">'
                        + '<span class="font-semibold ' + (passed ? 'text-green-700' : 'text-red-700') + '">' + (isLast ? '最近一次 · ' : '') + (passed ? '通过' : '未通过') + ' · ' + Number(a.score || 0) + '%</span>'
                        + '<span class="text-xs text-gray-400">' + escapeHtml(a.completed_at ? String(a.completed_at).replace('T', ' ').substring(0, 16) : '') + '</span>'
                        + '</div>'
                        + '<div class="text-xs text-gray-500 mt-1">答对 ' + Number(a.correct_count || 0) + ' / ' + Number(a.total_count || 0) + ' 题</div>';
                    (a.answers || []).forEach(function(row, qIdx) {
                        var qt = qMap[Number(row.question_id)];
                        html += '<div class="text-sm mt-2" style="border-top:1px dashed #e2e8f0;padding-top:8px">'
                            + '<div class="text-gray-700">' + (qt ? escapeHtml(qt.question) : ('第 ' + (qIdx + 1) + ' 题')) + '</div>'
                            + '<div class="text-xs mt-1 text-gray-600">你的答案：' + escapeHtml((row.submitted && row.submitted.length ? row.submitted : ['（未作答）']).join('、'))
                            + (row.correct ? ' <span class="text-green-600">✓ 正确</span>' : ' <span class="text-red-600">✗ 错误，正确答案：' + escapeHtml((row.correct_options || []).join('、')) + '</span>') + '</div>'
                            + (row.explanation ? '<div class="text-xs text-gray-500 mt-1">解析：' + escapeHtml(row.explanation) + '</div>' : '')
                            + '</div>';
                    });
                    html += '</div>';
                });
                $('#quizHistory').html(html);
            }).catch(function() {
                $('#quizHistory').html(
                    '<div class="text-center text-gray-400 py-10"><p>作答历史加载失败</p>'
                    + '<button type="button" class="btn-course btn-course-secondary btn-course-sm mt-4" onclick="showQuizView(' + itemId + ')">返回测验</button></div>'
                );
            });
        }

        // 待复习：拉取当前课程到期的复习项
        function loadCourseReviews() {
            $('#reviewPanel').addClass('hidden');
            if (!CURRENT_USER_ID || !apiRequest || !COURSE_DETAIL_DATA || !COURSE_DETAIL_DATA.is_joined) return;
            apiRequest('GET', '/reviews/course-items', {}).then(function(resp) {
                var all = (resp && resp.code === 9999 && resp.result && Array.isArray(resp.result.reviews)) ? resp.result.reviews : [];
                var reviews = all.filter(function(r) {
                    return r && r.course_item && Number(r.course_item.course_id) === Number(COURSE_DETAIL_ID);
                });
                if (!reviews.length) return;
                reviews.sort(function(a, b) {
                    return String(a.next_review_at || '9999-99-99').localeCompare(String(b.next_review_at || '9999-99-99'));
                });
                var html = '';
                reviews.forEach(function(r) {
                    var item = r.course_item || {};
                    html += '<div class="flex items-center justify-between gap-2 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2">'
                        + '<div class="min-w-0">'
                        + '<div class="text-sm font-medium text-gray-800 truncate">' + escapeHtml(item.title || '未命名章节') + '</div>'
                        + '<div class="text-xs text-gray-500 mt-0.5">上次 ' + (r.last_score === null ? '—' : (Number(r.last_score) + '%')) + ' · 复习 ' + Number(r.review_count || 0) + ' 次'
                        + (r.next_review_at ? ' · 到期 ' + escapeHtml(String(r.next_review_at).replace('T', ' ').substring(0, 10)) : '') + '</div>'
                        + '</div>'
                        + '<div class="flex gap-2 shrink-0">'
                        + '<button type="button" class="btn-course btn-course-primary btn-course-sm quiz-btn" data-course-item-id="' + Number(item.id) + '"><i class="fas fa-pen mr-1"></i>去测验</button>'
                        + '<a href="/courses/' + COURSE_DETAIL_ID + '/study?item=' + Number(item.id) + '" class="btn-course btn-course-secondary btn-course-sm"><i class="fas fa-book mr-1"></i>去学习</a>'
                        + '</div></div>';
                });
                $('#reviewPanelList').html(html);
                $('#reviewPanelCount').text(reviews.length + ' 个章节');
                $('#reviewPanel').removeClass('hidden');
            }).catch(function() { $('#reviewPanel').addClass('hidden'); });
        }

        function submitQuiz() {
            var itemId = Number($('#quizForm').data('item-id') || 0);
            var answers = {};
            $('#quizForm input:checked').each(function() {
                var questionId = $(this).attr('name').replace('quiz-answer-', '');
                if (!answers[questionId]) answers[questionId] = [];
                answers[questionId].push($(this).val());
            });
            apiRequest('POST', '/course-items/' + itemId + '/quiz/attempts', {answers: answers}).then(function(resp) {
                if (!resp || resp.code !== 9999) throw new Error((resp && resp.msg) || '提交失败');
                var result = resp.result || {};
                quizState.view = 'result';
                var html = '<div class="quiz-result"><div class="font-semibold ' + (result.passed ? 'text-green-700' : 'text-red-700') + '">' + (result.passed ? '测试通过' : '需要复习') + '：' + Number(result.score || 0) + '%</div>';
                (result.results || []).forEach(function(row, index) {
                    html += '<div class="text-sm mt-2">第 ' + (index + 1) + ' 题：' + (row.correct ? '<span class="text-green-600">正确</span>' : '<span class="text-red-600">错误，正确答案：' + escapeHtml((row.correct_options || []).join(', ')) + '</span>') + (row.explanation ? '<div class="text-gray-500">解析：' + escapeHtml(row.explanation) + '</div>' : '') + '</div>';
                });
                html += '</div><div class="mt-4 flex flex-wrap gap-2">'
                    + '<button type="button" class="btn-course btn-course-secondary" onclick="showQuizView(' + itemId + ')">返回测验</button>'
                    + '<button type="button" class="btn-course btn-course-secondary" onclick="openQuizHistory(' + itemId + ')">查看作答历史</button>'
                    + '<button type="button" class="btn-course btn-course-primary" onclick="closeQuiz()">关闭</button>'
                    + '</div>';
                $('#quizForm').addClass('hidden');
                $('#quizResult').html(html).removeClass('hidden');
                $('#quizHistoryBtn').removeClass('hidden');
                loadCourseReviews();
            }).catch(function(err) {
                $('#quizResult').html('<div class="quiz-result text-red-600">' + escapeHtml(err && err.message ? err.message : '提交失败') + '</div>').removeClass('hidden');
            });
        }
    </script>
@endsection