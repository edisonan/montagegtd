@extends('layouts.app')

@section('title', '课程管理 - 蒙太奇')
@section('description', '管理课程的基础信息、章节、测试与制品')

@section('content')
@include('components.course-item-modal')
@include('components.course-quiz-editor-modal')

@php $courseId = isset($courseId) ? (int)$courseId : 0; @endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     data-course-id="{{ $courseId }}"
     id="courseManageRoot">
    <!-- 面包屑 + 标题 -->
    <div class="mb-6">
        <nav class="flex items-center text-sm text-gray-600 mb-4">
            <a href="{{ url('/') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                <i class="fas fa-home mr-1"></i>首页
            </a>
            <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
            <a href="{{ url('/course/management') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                课程中心
            </a>
            <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
            <span class="text-gray-900 font-medium" id="mgBreadcrumbTitle">课程管理</span>
        </nav>

        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center mr-4 shadow-sm">
                    <i class="fas fa-cog text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900" id="mgCourseTitle">课程管理</h1>
                    <p class="text-gray-600 mt-1 text-sm">基础信息 · 章节管理 · 测试管理 · 制品管理</p>
                </div>
            </div>
            <div class="flex flex-wrap space-x-3">
                <a href="{{ url('/course/management') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>返回课程中心
                </a>
                <a href="/courses/{{ $courseId }}" id="mgViewLink" class="btn btn-outline">
                    <i class="fas fa-eye mr-2"></i>查看课程
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <!-- Tab 导航 -->
        <div class="flex border-b border-gray-200 overflow-x-auto mg-tabs" role="tablist">
            <button type="button" class="manage-tab" data-mg-tab="basic" role="tab" aria-selected="false">
                <i class="fas fa-info-circle mr-2"></i>基础信息
            </button>
            <button type="button" class="manage-tab" data-mg-tab="chapters" role="tab" aria-selected="false">
                <i class="fas fa-list-ul mr-2"></i>章节管理
            </button>
            <button type="button" class="manage-tab" data-mg-tab="tests" role="tab" aria-selected="false">
                <i class="fas fa-question-circle mr-2"></i>测试管理
            </button>
            <button type="button" class="manage-tab" data-mg-tab="artifacts" role="tab" aria-selected="false">
                <i class="fas fa-box-archive mr-2"></i>制品管理
            </button>
        </div>

        <!-- ============ 基础信息 ============ -->
        <div class="manage-panel hidden" data-mg-panel="basic" id="mgPanel-basic">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-primary-color mr-2"></i>课程基本信息
                </h2>
                <p class="text-sm text-gray-600 mt-1">修改课程的核心信息，保存后即时生效</p>
            </div>
            <div class="p-6" id="mgBasicInfoWrap">
                @include('courses._basic-info-form', ['editCourseId' => $courseId])
                <div class="text-center text-gray-400 py-6" id="mgBasicInfoLoading"><i class="fas fa-spinner fa-spin mr-2"></i>加载课程信息中...</div>
            </div>
        </div>

        <!-- ============ 章节管理 ============ -->
        <div class="manage-panel hidden" data-mg-panel="chapters" id="mgPanel-chapters">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-list-ul text-primary-color mr-2"></i>章节管理
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">维护课程章节结构与内容，支持多级章节</p>
                </div>
                <button type="button" class="btn btn-primary" id="mgAddChapterBtn">
                    <i class="fas fa-plus mr-2"></i>添加章节
                </button>
            </div>
            <div class="p-6">
                <div id="mgChapterLoading" class="text-muted mb-3">加载章节中...</div>
                <div id="mgChapterTree"></div>
            </div>
        </div>

        <!-- ============ 测试管理 ============ -->
        <div class="manage-panel hidden" data-mg-panel="tests" id="mgPanel-tests">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-question-circle text-primary-color mr-2"></i>测试管理
                </h2>
                <p class="text-sm text-gray-600 mt-1">以章节为主线：勾选章节生成测试，支持一键生成全部章节测试</p>
            </div>
            <div class="p-6">
                <!-- 工具栏 -->
                <div class="flex flex-wrap items-center gap-3 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="checkbox" id="mgTestSelectAll" class="mr-2 mg-select-all" data-scope="tests">全选
                    </label>
                    <div class="flex items-center gap-1 text-sm text-gray-700">
                        题目数
                        <select id="mgQuizQuestionCount" class="input text-sm py-1 px-2" style="width:auto">
                            <option value="3">3 题</option>
                            <option value="5" selected>5 题</option>
                            <option value="8">8 题</option>
                            <option value="10">10 题</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1 text-sm text-gray-700">
                        及格分
                        <input type="number" id="mgQuizPassingScore" class="input text-sm py-1 px-2" style="width:84px" value="70" min="0" max="100">
                        %
                    </div>
                    <div class="flex-1"></div>
                    <button type="button" class="btn btn-primary" id="mgTestGenSelectedBtn">
                        <i class="fas fa-magic mr-2"></i>生成选中章节测试
                    </button>
                    <button type="button" class="btn btn-outline" id="mgTestGenAllBtn">
                        <i class="fas fa-bolt mr-2"></i>一键生成全部测试
                    </button>
                </div>
                <p class="text-xs text-gray-400 mb-3">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    由 AI 根据章节内容自动出题；已存在的测试会被覆盖，生成需要一点时间，请勿关闭页面。
                </p>

                <!-- 生成进度 -->
                <div id="mgTestGenProgress" class="hidden mb-4"></div>

                <!-- 章节列表 -->
                <div id="mgTestChapterList"></div>
                <div id="mgTestChapterLoading" class="text-muted">加载测试状态中...</div>
            </div>
        </div>

        <!-- ============ 制品管理 ============ -->
        <div class="manage-panel hidden" data-mg-panel="artifacts" id="mgPanel-artifacts">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-box-archive text-primary-color mr-2"></i>制品管理
                </h2>
                <p class="text-sm text-gray-600 mt-1">以章节为主线：勾选多个章节，同时生成该类型的 AI 制品</p>
            </div>
            <div class="p-6">
                <!-- 工具栏 -->
                <div class="flex flex-wrap items-center gap-3 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="checkbox" id="mgArtSelectAll" class="mr-2 mg-select-all" data-scope="artifacts">全选
                    </label>
                    <div class="flex items-center gap-1 text-sm text-gray-700">
                        制品类型
                        <select id="mgArtifactType" class="input text-sm py-1 px-2" style="width:auto">
                            <option value="key_points">关键信息</option>
                            <option value="visual_reading">可视化界面</option>
                            <option value="mind_map">思维导图</option>
                            <option value="ai_ppt">AIPPT</option>
                        </select>
                    </div>
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="checkbox" id="mgArtForce" class="mr-2">强制重新生成
                    </label>
                    <div class="flex-1"></div>
                    <button type="button" class="btn btn-primary" id="mgArtGenSelectedBtn">
                        <i class="fas fa-magic mr-2"></i>生成选中章节制品
                    </button>
                    <button type="button" class="btn btn-outline" id="mgArtGenAllBtn">
                        <i class="fas fa-bolt mr-2"></i>一键生成全部章节制品
                    </button>
                </div>
                <p class="text-xs text-gray-400 mb-3">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    未勾选「强制重新生成」时，已成功生成的制品会被复用；生成需要一点时间，支持多个章节同时生成，请勿关闭页面。
                </p>

                <!-- 生成进度 -->
                <div id="mgArtGenProgress" class="hidden mb-4"></div>

                <!-- 章节列表 -->
                <div id="mgArtChapterList"></div>
                <div id="mgArtChapterLoading" class="text-muted">加载制品状态中...</div>
            </div>
        </div>
    </div>
</div>

<style>
    .manage-tab {
        display: inline-flex;
        align-items: center;
        padding: 14px 24px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        transition: all .2s ease;
        white-space: nowrap;
        background: transparent;
        cursor: pointer;
    }
    .manage-tab:hover { color: #374151; }
    .manage-tab.active { color: #2563eb; border-bottom-color: #2563eb; background: #eff6ff; }
    .mg-chapter-row { transition: background .15s ease; }
    .mg-chapter-row:hover { background: #f9fafb; }
    .mg-art-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; padding: 2px 8px; border-radius: 9999px; }
    .mg-progress-row { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; font-size: 13px; }
    .mg-progress-wrap { max-height: 320px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px; background: #fff; }
</style>

<script>
(function () {
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

    var root = document.getElementById('courseManageRoot');
    var COURSE_ID = root ? Number(root.getAttribute('data-course-id') || 0) : 0;
    if (!COURSE_ID) {
        // 兜底：从路径解析 /courses/{id}/edit
        var parts = window.location.pathname.split('/').filter(Boolean);
        for (var i = 0; i < parts.length; i++) {
            if (parts[i] === 'courses' && parts[i + 1]) { COURSE_ID = Number(parts[i + 1] || 0); break; }
        }
    }

    var ARTIFACT_TYPES = [
        { key: 'key_points', label: '关键信息', icon: 'fa-lightbulb' },
        { key: 'visual_reading', label: '可视化界面', icon: 'fa-book-open' },
        { key: 'mind_map', label: '思维导图', icon: 'fa-diagram-project' },
        { key: 'ai_ppt', label: 'AIPPT', icon: 'fa-file-powerpoint' }
    ];

    var mgState = {
        course: null,
        structure: [],
        quizStatus: {},   // item_id => bool
        quizErrors: {},   // item_id => 最近一次生成测试的失败理由
        artifactMap: {},  // item_id => { type => status }
        artifactErrors: {}, // item_id => { type => error_message }
        flatItems: []     // [{id,title,item_type,depth,parent_id}]
    };

    function escapeHtml(text) {
        return String(text == null ? '' : text).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }

    function iconByType(type) {
        if (type === 'video') return 'fa-video-camera';
        if (type === 'quiz') return 'fa-question-circle';
        if (type === 'assignment') return 'fa-file-text';
        if (type === 'reading') return 'fa-book';
        if (type === 'chapter' || type === 'module') return 'fa-folder';
        return 'fa-file';
    }

    function mgToast(message, type) {
        type = type || 'info';
        var colors = {
            success: 'bg-green-500', error: 'bg-red-500', warning: 'bg-yellow-500', info: 'bg-blue-500'
        };
        var icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        var toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 z-[10001] px-5 py-3 rounded-lg shadow-lg text-white flex items-center space-x-3 ' + (colors[type] || colors.info);
        toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + escapeHtml(message) + '</span>';
        document.body.appendChild(toast);
        setTimeout(function () {
            if (toast.parentNode) toast.remove();
        }, 3600);
    }

    // ---------------- 数据加载 ----------------
    function loadAllData() {
        if (!apiRequest || !COURSE_ID) return;
        document.getElementById('mgChapterLoading').textContent = '加载章节中...';
        document.getElementById('mgTestChapterLoading').textContent = '加载测试状态中...';
        document.getElementById('mgArtChapterLoading').textContent = '加载制品状态中...';

        return Promise.all([
            apiRequest('GET', '/courses/' + COURSE_ID, {}),
            apiRequest('GET', '/course-items/structure/' + COURSE_ID, {}),
            apiRequest('GET', '/courses/' + COURSE_ID + '/quiz-status', {}),
            apiRequest('GET', '/courses/' + COURSE_ID + '/artifact-status', {})
        ]).then(function (results) {
            var courseResp = results[0];
            var structResp = results[1];
            if (!courseResp || courseResp.code !== 9999 || !courseResp.result || !courseResp.result.course) {
                throw new Error((courseResp && courseResp.msg) || '课程加载失败');
            }
            mgState.course = courseResp.result.course;
            // 结构接口返回 result: {result: [...]}（历史双重包裹），兼容两种形状
            var structResult = (structResp && structResp.code === 9999 && structResp.result) ? structResp.result : {};
            mgState.structure = Array.isArray(structResult)
                ? structResult
                : (Array.isArray(structResult.result) ? structResult.result : []);

            var quizResp = results[2];
            mgState.quizStatus = (quizResp && quizResp.code === 9999 && quizResp.result && quizResp.result.quiz_status) ? quizResp.result.quiz_status : {};
            mgState.quizErrors = (quizResp && quizResp.code === 9999 && quizResp.result && quizResp.result.quiz_errors) ? quizResp.result.quiz_errors : {};
            var artResp = results[3];
            mgState.artifactMap = (artResp && artResp.code === 9999 && artResp.result && artResp.result.artifact_map) ? artResp.result.artifact_map : {};
            mgState.artifactErrors = (artResp && artResp.code === 9999 && artResp.result && artResp.result.artifact_errors) ? artResp.result.artifact_errors : {};

            mgState.flatItems = flattenItems(mgState.structure, 0);

            renderHeader();
            loadBasicInfo();
            renderChapterTree();
            renderTestList();
            renderArtifactList();

            document.getElementById('mgChapterLoading').style.display = 'none';
            document.getElementById('mgTestChapterLoading').style.display = 'none';
            document.getElementById('mgArtChapterLoading').style.display = 'none';
        }).catch(function (err) {
            var msg = (err && err.message) ? err.message : '加载失败，请稍后重试';
            document.getElementById('mgChapterLoading').textContent = msg;
            document.getElementById('mgTestChapterLoading').textContent = msg;
            document.getElementById('mgArtChapterLoading').textContent = msg;
        });
    }

    function flattenItems(items, depth) {
        var out = [];
        (items || []).forEach(function (item) {
            out.push({ id: Number(item.id || 0), title: item.title || '', item_type: item.item_type || 'chapter', depth: depth, children: !!item.children });
            if (Array.isArray(item.children) && item.children.length) {
                out = out.concat(flattenItems(item.children, depth + 1));
            }
        });
        return out;
    }

    function renderHeader() {
        var c = mgState.course || {};
        document.title = (c.title || '课程') + ' - 课程管理 - 蒙太奇';
        document.getElementById('mgCourseTitle').textContent = (c.title || '课程') + ' 管理';
        document.getElementById('mgBreadcrumbTitle').textContent = (c.title || '课程') + ' - 管理';
        document.getElementById('mgViewLink').setAttribute('href', '/courses/' + Number(c.id || COURSE_ID));
    }

    // ---------------- 基础信息 Tab ----------------
    function loadBasicInfo() {
        if (!apiRequest || !mgState.course) return;
        if (document.getElementById('mgBasicInfoLoading')) {
            document.getElementById('mgBasicInfoLoading').style.display = 'none';
        }
        var form = document.getElementById('createCourseForm');
        if (!form) return;
        if (form.dataset.mgBound) return;
        form.dataset.mgBound = '1';
        initBasicForm(mgState.course);
    }

    function initBasicForm(c) {
        document.getElementById('title').value = c.title || '';
        document.getElementById('instructor').value = c.instructor || '';
        document.getElementById('platform').value = c.platform || '';
        document.getElementById('estimated_hours').value = (c.estimated_hours !== null && c.estimated_hours !== undefined) ? c.estimated_hours : '';
        document.getElementById('public_url').value = c.public_url || '';
        document.getElementById('cover_image_url').value = c.cover_image_url || '';
        document.getElementById('tags').value = Array.isArray(c.tags) ? c.tags.join(', ') : (c.tags || '');
        document.getElementById('description').value = c.description || '';

        var diff = document.querySelector('input[name="difficulty"][value="' + (c.difficulty || 'beginner') + '"]');
        if (diff) diff.checked = true;
        var pub = document.querySelector('input[name="public_status"][value="' + Number(c.public_status || 2) + '"]');
        if (pub) pub.checked = true;

        updateCharCount(document.getElementById('title'), 'titleCharCount', 100);
        updateCharCount(document.getElementById('tags'), 'tagsCharCount', 100);
        updateCharCount(document.getElementById('description'), 'descCharCount', 500);

        document.getElementById('title').addEventListener('input', function () { updateCharCount(this, 'titleCharCount', 100); });
        document.getElementById('tags').addEventListener('input', function () { updateCharCount(this, 'tagsCharCount', 100); });
        document.getElementById('description').addEventListener('input', function () { updateCharCount(this, 'descCharCount', 500); });

        var coverUrl = (c.cover_image_url || '').trim();
        if (coverUrl && isValidImageUrl(coverUrl)) {
            document.getElementById('previewImage').src = coverUrl;
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        document.getElementById('cover_image_url').addEventListener('input', function () {
            var url = this.value.trim();
            if (isValidImageUrl(url)) {
                document.getElementById('previewImage').src = url;
                document.getElementById('imagePreview').classList.remove('hidden');
            } else {
                document.getElementById('imagePreview').classList.add('hidden');
            }
        });

        var form = document.getElementById('createCourseForm');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = document.getElementById('submitBtn');
            if (!submitBtn) return;
            var original = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

            var payload = {
                title: document.getElementById('title').value.trim(),
                instructor: document.getElementById('instructor').value.trim(),
                platform: document.getElementById('platform').value,
                difficulty: (document.querySelector('input[name="difficulty"]:checked') || {}).value || 'intermediate',
                estimated_hours: document.getElementById('estimated_hours').value,
                public_url: document.getElementById('public_url').value.trim(),
                cover_image_url: document.getElementById('cover_image_url').value.trim(),
                description: document.getElementById('description').value.trim(),
                public_status: Number((document.querySelector('input[name="public_status"]:checked') || {}).value || 2),
                tags: document.getElementById('tags').value.split(',').map(function (t) { return t.trim(); }).filter(Boolean)
            };
            apiRequest('PUT', '/courses/' + COURSE_ID, payload).then(function (resp) {
                if (resp && resp.code === 9999) {
                    mgToast(resp.msg || '课程已保存', 'success');
                    loadAllData();
                    return;
                }
                mgToast((resp && resp.msg) ? resp.msg : '课程保存失败', 'error');
            }).catch(function () {
                mgToast('网络错误，请稍后重试', 'error');
            }).finally(function () {
                submitBtn.innerHTML = original;
                submitBtn.disabled = false;
            });
        });
    }

    function updateCharCount(input, counterId, maxLength) {
        var counter = document.getElementById(counterId);
        if (!counter) return;
        counter.textContent = input.value.length + '/' + maxLength;
        counter.classList.remove('text-gray-400', 'text-yellow-600', 'text-red-600');
        if (input.value.length > maxLength) counter.classList.add('text-red-600');
        else if (input.value.length > maxLength * 0.9) counter.classList.add('text-yellow-600');
        else counter.classList.add('text-gray-400');
    }

    function addTag(tag) {
        var tagsInput = document.getElementById('tags');
        if (!tagsInput) return;
        var current = tagsInput.value.trim();
        var arr = current ? current.split(',').map(function (t) { return t.trim(); }) : [];
        if (arr.indexOf(tag) === -1) {
            arr.push(tag);
            tagsInput.value = arr.join(', ');
            updateCharCount(tagsInput, 'tagsCharCount', 100);
        }
    }

    function isValidImageUrl(url) {
        if (!url) return false;
        try { new URL(url); } catch (e) { return false; }
        return /\.(jpg|jpeg|png|gif|webp|svg|bmp)(\?.*)?$/i.test(url);
    }

    // ---------------- 章节管理 Tab ----------------
    function renderChapterTree() {
        var box = document.getElementById('mgChapterTree');
        if (!mgState.flatItems.length) {
            box.innerHTML = '<div class="text-center text-gray-400 py-10"><i class="fas fa-inbox text-3xl mb-3" style="display:block"></i><p>暂无章节，点击右上角「添加章节」开始构建课程</p></div>';
            return;
        }
        box.innerHTML = '<div class="space-y-2">' + renderChapterNodes(mgState.structure, false) + '</div>';
    }

    function renderChapterNodes(items, child) {
        var html = '';
        (items || []).forEach(function (item) {
            var id = Number(item.id || 0);
            var sub = (Array.isArray(item.children) && item.children.length)
                ? '<div class="ml-8 mt-2 space-y-2">' + renderChapterNodes(item.children, true) + '</div>' : '';
            html += ''
                + '<div class="border border-gray-200 rounded-lg p-3 bg-white">'
                + '<div class="flex items-center justify-between flex-wrap gap-2">'
                + '<div class="flex items-center gap-2 min-w-0">'
                + '<i class="fas ' + iconByType(item.item_type) + ' text-gray-400"></i>'
                + '<span class="font-medium text-gray-900 truncate">' + escapeHtml(item.title || '') + '</span>'
                + '<span class="badge badge-secondary">' + escapeHtml(String(item.item_type || '')) + '</span>'
                + '</div>'
                + '<div class="flex items-center gap-1 flex-wrap">'
                + '<button type="button" class="btn btn-sm btn-outline" onclick="mgEditChapter(' + id + ')"><i class="fas fa-edit mr-1"></i>编辑</button>'
                + '<button type="button" class="btn btn-sm btn-outline" onclick="mgAddChildChapter(' + id + ')"><i class="fas fa-plus mr-1"></i>子章节</button>'
                + '<button type="button" class="btn btn-sm" style="background:#8b5cf6;color:#fff" onclick="openQuizEditorModal(' + id + ', \'' + escapeHtml(item.title || '').replace(/'/g, "\\'") + '\')"><i class="fas fa-question-circle mr-1"></i>测试</button>'
                + '<button type="button" class="btn btn-sm btn-danger" onclick="mgDeleteChapter(' + id + ', \'' + escapeHtml(item.title || '').replace(/'/g, "\\'") + '\')"><i class="fas fa-trash-alt mr-1"></i>删除</button>'
                + '</div></div>'
                + ((item.description && !child) ? '<div class="mt-2 text-xs text-gray-500">' + escapeHtml(String(item.description).slice(0, 120)) + '</div>' : '')
                + sub
                + '</div>';
        });
        return html;
    }

    function mgEditChapter(id) {
        if (!apiRequest) { mgToast('API客户端未初始化', 'warning'); return; }
        apiRequest('GET', '/course-items/' + id, {}).then(function (resp) {
            if (resp && resp.code === 9999 && resp.result && resp.result.course_item) {
                openCourseItemModal(COURSE_ID, resp.result.course_item);
                return;
            }
            mgToast((resp && resp.msg) ? resp.msg : '获取章节失败', 'error');
        }).catch(function () { mgToast('网络错误，请稍后重试', 'error'); });
    }

    function mgAddChildChapter(id) {
        openCourseItemModal(COURSE_ID, null, id);
    }

    function mgDeleteChapter(id, title) {
        if (typeof Swal === 'undefined') {
            if (!confirm('确定删除章节「' + title + '」？')) return;
            doDeleteChapter(id);
            return;
        }
        Swal.fire({
            title: '确定删除这个章节吗？',
            text: '删除后不可恢复，其子章节也将无法通过课程查看',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '删除',
            cancelButtonText: '取消',
            confirmButtonColor: '#dc2626'
        }).then(function (result) {
            if (result.isConfirmed) doDeleteChapter(id);
        });
    }

    function doDeleteChapter(id) {
        apiRequest('DELETE', '/course-items/' + id, {}).then(function (resp) {
            if (resp && resp.code === 9999) {
                mgToast(resp.msg || '删除成功', 'success');
                loadAllData();
                return;
            }
            mgToast((resp && resp.msg) ? resp.msg : '删除失败', 'error');
        }).catch(function () { mgToast('网络错误，请稍后重试', 'error'); });
    }

    // ---------------- 测试管理 Tab ----------------
    function renderTestList() {
        var box = document.getElementById('mgTestChapterList');
        if (!mgState.flatItems.length) {
            box.innerHTML = '<div class="text-center text-gray-400 py-10"><p>暂无章节，请先在「章节管理」中添加章节</p></div>';
            return;
        }
        var html = '<div class="border border-gray-200 rounded-lg divide-y divide-gray-100">';
        mgState.flatItems.forEach(function (item, idx) {
            var hasQuiz = !!mgState.quizStatus[item.id];
            var quizErr = (mgState.quizErrors && mgState.quizErrors[item.id]) || '';
            var quizErrHtml = quizErr
                ? '<div class="w-full ml-7 mt-1"><div class="text-xs text-red-600"><i class="fas fa-times-circle mr-1"></i>上次生成失败：' + escapeHtml(quizErr) + '</div></div>'
                : '';
            html += ''
                + '<div class="mg-chapter-row flex items-center gap-3 px-4 py-3 flex-wrap">'
                + '<input type="checkbox" class="mg-test-check" data-id="' + item.id + '" data-idx="' + idx + '">'
                + '<div style="width:' + (item.depth * 20) + 'px" class="shrink-0"></div>'
                + '<i class="fas ' + iconByType(item.item_type) + ' text-gray-400"></i>'
                + '<span class="font-medium text-gray-900 truncate flex-1 min-w-0">' + escapeHtml(item.title || '') + '</span>'
                + (hasQuiz
                    ? '<span class="mg-art-badge bg-green-100 text-green-700"><i class="fas fa-check-circle"></i>已有测试</span>'
                    : '<span class="mg-art-badge bg-gray-100 text-gray-500"><i class="fas fa-minus-circle"></i>暂无测试</span>')
                + '<div class="flex items-center gap-1">'
                + '<button type="button" class="btn btn-sm btn-outline" onclick="mgEditQuiz(' + item.id + ', \'' + escapeHtml(item.title || '').replace(/'/g, "\\'") + '\')"><i class="fas fa-edit mr-1"></i>编辑测验</button>'
                + '<button type="button" class="btn btn-sm btn-primary" onclick="mgGenOneQuiz(' + item.id + ', \'' + escapeHtml(item.title || '').replace(/'/g, "\\'") + '\')"><i class="fas fa-magic mr-1"></i>生成测试</button>'
                + '</div>'
                + quizErrHtml
                + '</div>';
        });
        box.innerHTML = html + '</div>';
    }

    function mgEditQuiz(itemId, title) {
        openQuizEditorModal(itemId, title);
    }

    function collectChecked(selector) {
        var ids = [];
        document.querySelectorAll(selector).forEach(function (el) {
            if (el.checked) ids.push(Number(el.getAttribute('data-id')));
        });
        return ids;
    }

    function currentQuizSettings() {
        var qcount = Math.max(3, Math.min(20, Number(document.getElementById('mgQuizQuestionCount').value || 5)));
        var pass = Math.max(1, Math.min(100, Number(document.getElementById('mgQuizPassingScore').value || 70)));
        return { question_count: qcount, passing_score: pass };
    }

    function mgGenOneQuiz(itemId, title) {
        var hasQuiz = !!mgState.quizStatus[itemId];
        var doGen = function () { runQuizGeneration([itemId], [title]); };
        if (hasQuiz) {
            if (typeof Swal === 'undefined') {
                if (!confirm('该章节已有测试，继续将覆盖，确认生成吗？')) return;
                doGen(); return;
            }
            Swal.fire({
                title: '章节已有测试',
                text: '生成后将以 AI 新题覆盖现有测试，继续吗？',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '覆盖生成',
                cancelButtonText: '取消'
            }).then(function (r) { if (r.isConfirmed) doGen(); });
            return;
        }
        doGen();
    }

    function mgRunTests(ids) {
        if (!ids.length) { mgToast('请先勾选要生成测试的章节', 'warning'); return; }
        var existing = ids.filter(function (id) { return mgState.quizStatus[id]; });
        var doGen = function () { runQuizGeneration(ids, titlesOf(ids)); };
        if (existing.length) {
            if (typeof Swal === 'undefined') {
                if (!confirm(existing.length + ' 个章节已有测试，继续将覆盖，确认生成吗？')) return;
                doGen(); return;
            }
            Swal.fire({
                title: '部分章节已有测试',
                text: '有 ' + existing.length + ' 个章节已有测试，生成后将覆盖，继续吗？',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '覆盖生成',
                cancelButtonText: '取消'
            }).then(function (r) { if (r.isConfirmed) doGen(); });
            return;
        }
        doGen();
    }

    function runQuizGeneration(ids, titles) {
        var settings = currentQuizSettings();
        var progressWrap = document.getElementById('mgTestGenProgress');
        progressWrap.classList.remove('hidden');
        progressWrap.innerHTML = '<div class="mg-progress-wrap"><div class="mb-2 text-sm font-semibold text-gray-700"><i class="fas fa-magic mr-1"></i>正在为 ' + ids.length + ' 个章节生成测试（并发 3）...</div></div>';
        var listEl = progressWrap.querySelector('.mg-progress-wrap');
        var rows = [];
        ids.forEach(function (id, i) {
            var row = document.createElement('div');
            row.className = 'mg-progress-row bg-gray-50';
            row.innerHTML = '<i class="fas fa-circle-notch fa-spin text-blue-500"></i>'
                + '<span class="truncate flex-1">' + escapeHtml((titles && titles[i]) || '章节 #' + id) + '</span>'
                + '<span class="text-xs text-gray-400 status-text">等待中</span>';
            listEl.appendChild(row);
            rows.push(row);
        });

        window.__mgGenBusy = true;

        runConcurrent(ids, function (id, i) {
            var statusEl = rows[i].querySelector('.status-text');
            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-500"></i><span class="text-xs text-gray-500">生成中...</span>';
            return apiRequest('POST', '/courses/' + COURSE_ID + '/quizzes/generate', {
                item_ids: [id],
                question_count: settings.question_count,
                passing_score: settings.passing_score
            });
        }, 3, function (i, result) {
            var first = (result && result.result && result.result.results && result.result.results[0]) || {};
            var ok = result && result.code === 9999 && first.success;
            var title = (titles && titles[i]) || '章节 #' + ids[i];
            if (ok) {
                rows[i].innerHTML = '<i class="fas fa-check-circle text-green-500"></i><span class="truncate flex-1">' + escapeHtml(title) + '</span><span class="text-xs text-green-600 whitespace-nowrap">成功</span>';
            } else {
                var msg = first.error || (result && result.msg) || '生成失败';
                rows[i].innerHTML = '<div class="w-full">'
                    + '<div class="flex items-center gap-2"><i class="fas fa-times-circle text-red-500"></i>'
                    + '<span class="truncate flex-1">' + escapeHtml(title) + '</span>'
                    + '<span class="text-xs text-red-500 whitespace-nowrap">失败</span></div>'
                    + '<div class="text-xs text-red-600 mt-1 pl-6 break-all">' + escapeHtml(msg) + '</div>'
                    + '</div>';
            }
        }).then(function (results) {
            window.__mgGenBusy = false;
            var okCount = results.filter(function (r) { return r && r.code === 9999; }).length;
            var row = document.createElement('div');
            row.className = 'mt-3 text-sm font-semibold ' + (okCount === ids.length ? 'text-green-600' : 'text-amber-600');
            row.innerHTML = '生成完成：成功 ' + okCount + ' / ' + ids.length;
            listEl.appendChild(row);
            mgToast((okCount === ids.length ? '全部章节测试生成成功' : '生成完成，成功 ' + okCount + ' / ' + ids.length), okCount === ids.length ? 'success' : 'warning');
            // 刷新测试状态
            apiRequest('GET', '/courses/' + COURSE_ID + '/quiz-status', {}).then(function (resp) {
                if (resp && resp.code === 9999 && resp.result && resp.result.quiz_status) {
                    mgState.quizStatus = resp.result.quiz_status;
                    mgState.quizErrors = (resp.result.quiz_errors || {});
                    renderTestList();
                }
            });
        });
    }

    // ---------------- 制品管理 Tab ----------------
    function renderArtifactList() {
        var box = document.getElementById('mgArtChapterList');
        if (!mgState.flatItems.length) {
            box.innerHTML = '<div class="text-center text-gray-400 py-10"><p>暂无章节，请先在「章节管理」中添加章节</p></div>';
            return;
        }
        var html = '<div class="border border-gray-200 rounded-lg divide-y divide-gray-100">';
        mgState.flatItems.forEach(function (item, idx) {
            var art = mgState.artifactMap[item.id] || {};
            var errMap = (mgState.artifactErrors && mgState.artifactErrors[item.id]) || {};
            var badges = '';
            var failLines = [];
            ARTIFACT_TYPES.forEach(function (t) {
                var st = art[t.key];
                if (!st) return;
                var tip = st;
                if (st === 'failed') {
                    tip = errMap[t.key] || '生成失败';
                    failLines.push(t.label + '：' + tip);
                }
                var color = st === 'success' ? 'bg-green-100 text-green-700' : (st === 'failed' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700');
                var statusText = st === 'success' ? '' : (st === 'failed' ? '（失败）' : '（' + st + '）');
                badges += '<span class="mg-art-badge ' + color + '" title="' + escapeHtml(tip) + '"><i class="fas ' + t.icon + '"></i>' + t.label + statusText + '</span>';
            });
            var typeBtns = '';
            ARTIFACT_TYPES.forEach(function (t) {
                var has = art[t.key] === 'success';
                typeBtns += '<button type="button" class="btn btn-sm ' + (has ? 'btn-outline' : 'btn-outline') + '" onclick="mgGenOneArtifact(' + item.id + ', \'' + t.key + '\', \'' + escapeHtml(item.title || '').replace(/'/g, "\\'") + '\')" title="' + escapeHtml(t.label) + '"><i class="fas ' + t.icon + ' mr-1"></i>' + (has ? '重做' : '生成') + '</button>';
            });
            var failHtml = failLines.length
                ? '<div class="w-full ml-7 mt-1">' + failLines.map(function (m) {
                    return '<div class="text-xs text-red-600"><i class="fas fa-times-circle mr-1"></i>' + escapeHtml(m) + '</div>';
                }).join('') + '</div>'
                : '';
            html += ''
                + '<div class="mg-chapter-row flex items-center gap-3 px-4 py-3 flex-wrap">'
                + '<input type="checkbox" class="mg-art-check" data-id="' + item.id + '" data-idx="' + idx + '">'
                + '<div style="width:' + (item.depth * 20) + 'px" class="shrink-0"></div>'
                + '<i class="fas ' + iconByType(item.item_type) + ' text-gray-400"></i>'
                + '<span class="font-medium text-gray-900 truncate" style="max-width:220px">' + escapeHtml(item.title || '') + '</span>'
                + '<div class="flex flex-wrap items-center gap-1">' + badges + '</div>'
                + '<div class="flex items-center gap-1 flex-wrap">' + typeBtns + '</div>'
                + failHtml
                + '</div>';
        });
        box.innerHTML = html + '</div>';
    }

    function currentArtifactType() {
        return document.getElementById('mgArtifactType').value || 'key_points';
    }

    function mgRunArtifacts(ids) {
        if (!ids.length) { mgToast('请先勾选要生成制品的章节', 'warning'); return; }
        var type = currentArtifactType();
        var force = document.getElementById('mgArtForce').checked ? 1 : 0;
        var typeLabel = (ARTIFACT_TYPES.filter(function (t) { return t.key === type; })[0] || {}).label || type;
        runArtifactGeneration(ids, type, force, typeLabel, titlesOf(ids));
    }

    function mgGenOneArtifact(itemId, type, title) {
        var force = (mgState.artifactMap[itemId] || {})[type] === 'success' ? 1 : 0;
        var typeLabel = (ARTIFACT_TYPES.filter(function (t) { return t.key === type; })[0] || {}).label || type;
        runArtifactGeneration([itemId], type, force, typeLabel, [title]);
    }

    function titlesOf(ids) {
        var map = {};
        mgState.flatItems.forEach(function (i) { map[i.id] = i.title; });
        return ids.map(function (id) { return map[id] || ('章节 #' + id); });
    }

    function runArtifactGeneration(ids, type, force, typeLabel, titles) {
        titles = titles || [];
        var progressWrap = document.getElementById('mgArtGenProgress');
        progressWrap.classList.remove('hidden');
        progressWrap.innerHTML = '<div class="mg-progress-wrap"><div class="mb-2 text-sm font-semibold text-gray-700"><i class="fas fa-box-archive mr-1"></i>正在为 ' + ids.length + ' 个章节生成「' + escapeHtml(typeLabel) + '」制品（并发 3）...</div></div>';
        var listEl = progressWrap.querySelector('.mg-progress-wrap');
        var rows = [];
        ids.forEach(function (id, i) {
            var row = document.createElement('div');
            row.className = 'mg-progress-row bg-gray-50';
            row.innerHTML = '<i class="fas fa-circle-notch fa-spin text-blue-500"></i>'
                + '<span class="truncate flex-1">' + escapeHtml(titles[i] || ('章节 #' + id)) + '</span>'
                + '<span class="text-xs text-gray-400 status-text">等待中</span>';
            listEl.appendChild(row);
            rows.push(row);
        });

        window.__mgGenBusy = true;

        runConcurrent(ids, function (id, i) {
            var statusEl = rows[i].querySelector('.status-text');
            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-500"></i><span class="text-xs text-gray-500">生成中...</span>';
            return apiRequest('POST', '/artifacts/generate', {
                related_type: 'course_item',
                related_id: id,
                artifact_type: type,
                force: force
            });
        }, 3, function (i, result) {
            var artifact = (result && result.result && result.result.artifact) || {};
            var ok = result && result.code === 9999 && artifact.status === 'success';
            var title = titles[i] || ('章节 #' + ids[i]);
            if (ok) {
                rows[i].innerHTML = '<i class="fas fa-check-circle text-green-500"></i><span class="truncate flex-1">' + escapeHtml(title) + '</span><span class="text-xs text-green-600 whitespace-nowrap">成功</span>';
            } else {
                var msg = artifact.error_message || (result && result.msg) || '生成失败';
                rows[i].innerHTML = '<div class="w-full">'
                    + '<div class="flex items-center gap-2"><i class="fas fa-times-circle text-red-500"></i>'
                    + '<span class="truncate flex-1">' + escapeHtml(title) + '</span>'
                    + '<span class="text-xs text-red-500 whitespace-nowrap">失败</span></div>'
                    + '<div class="text-xs text-red-600 mt-1 pl-6 break-all">' + escapeHtml(msg) + '</div>'
                    + '</div>';
            }
        }).then(function (results) {
            window.__mgGenBusy = false;
            var okCount = results.filter(function (r) { return r && r.code === 9999 && r.result && r.result.artifact && r.result.artifact.status === 'success'; }).length;
            var row = document.createElement('div');
            row.className = 'mt-3 text-sm font-semibold ' + (okCount === ids.length ? 'text-green-600' : 'text-amber-600');
            row.innerHTML = '生成完成：成功 ' + okCount + ' / ' + ids.length;
            listEl.appendChild(row);
            mgToast((okCount === ids.length ? '制品生成成功' : '生成完成，成功 ' + okCount + ' / ' + ids.length), okCount === ids.length ? 'success' : 'warning');
            // 刷新制品状态
            apiRequest('GET', '/courses/' + COURSE_ID + '/artifact-status', {}).then(function (resp) {
                if (resp && resp.code === 9999 && resp.result && resp.result.artifact_map) {
                    mgState.artifactMap = resp.result.artifact_map;
                    mgState.artifactErrors = (resp.result.artifact_errors || {});
                    renderArtifactList();
                }
            });
        });
    }

    // ---------------- 并发任务队列 ----------------
    function runConcurrent(items, worker, concurrency, onProgress) {
        return new Promise(function (resolve) {
            var results = new Array(items.length);
            var index = 0, done = 0;
            function next() {
                if (done >= items.length) { resolve(results); return; }
                var i = index++;
                if (i >= items.length) return;
                worker(items[i], i).then(function (r) {
                    results[i] = r;
                    done++;
                    if (onProgress) onProgress(i, r);
                    next();
                }).catch(function (e) {
                    results[i] = { code: 0, msg: (e && e.message) ? e.message : '网络错误' };
                    done++;
                    if (onProgress) onProgress(i, results[i]);
                    next();
                });
            }
            var n = Math.max(1, Math.min(concurrency || 3, items.length));
            for (var c = 0; c < n; c++) next();
        });
    }

    // ---------------- Tab 切换 ----------------
    function switchTab(tab) {
        var valid = ['basic', 'chapters', 'tests', 'artifacts'];
        if (valid.indexOf(tab) === -1) tab = 'basic';
        document.querySelectorAll('.manage-tab').forEach(function (btn) {
            var active = btn.getAttribute('data-mg-tab') === tab;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        document.querySelectorAll('.manage-panel').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.getAttribute('data-mg-panel') !== tab);
        });
        if (tab === 'basic') loadBasicInfo();
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            history.replaceState(null, '', url.toString());
        } catch (e) {}
        return tab;
    }

    // ---------------- 事件绑定 ----------------
    document.addEventListener('DOMContentLoaded', function () {
        if (!apiRequest || !COURSE_ID) {
            document.getElementById('mgChapterLoading').textContent = 'API客户端未初始化或课程ID错误';
            return;
        }

        // 组件钩子：章节保存 / 测验保存后刷新全部数据
        window.refreshCourseStructure = loadAllData;
        window.refreshQuizStatus = function () {
            apiRequest('GET', '/courses/' + COURSE_ID + '/quiz-status', {}).then(function (resp) {
                if (resp && resp.code === 9999 && resp.result && resp.result.quiz_status) {
                    mgState.quizStatus = resp.result.quiz_status;
                    mgState.quizErrors = (resp.result.quiz_errors || {});
                    renderTestList();
                }
            });
        };

        document.querySelectorAll('.manage-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (window.__mgGenBusy) {
                    mgToast('正在生成中，请稍候…', 'warning');
                    return;
                }
                switchTab(btn.getAttribute('data-mg-tab'));
            });
        });

        // 初始 Tab
        var initialTab = 'basic';
        try {
            var params = new URLSearchParams(window.location.search);
            if (params.get('tab')) initialTab = params.get('tab');
        } catch (e) {}
        switchTab(initialTab);

        // 章节管理：添加章节
        document.getElementById('mgAddChapterBtn').addEventListener('click', function () {
            openCourseItemModal(COURSE_ID);
        });

        // 测试管理
        document.getElementById('mgTestSelectAll').addEventListener('change', function () {
            document.querySelectorAll('.mg-test-check').forEach(function (el) { el.checked = this.checked; }, this);
        });
        document.getElementById('mgTestGenSelectedBtn').addEventListener('click', function () {
            mgRunTests(collectChecked('.mg-test-check'));
        });
        document.getElementById('mgTestGenAllBtn').addEventListener('click', function () {
            mgRunTests(mgState.flatItems.map(function (i) { return i.id; }));
        });

        // 制品管理
        document.getElementById('mgArtSelectAll').addEventListener('change', function () {
            document.querySelectorAll('.mg-art-check').forEach(function (el) { el.checked = this.checked; }, this);
        });
        document.getElementById('mgArtGenSelectedBtn').addEventListener('click', function () {
            mgRunArtifacts(collectChecked('.mg-art-check'));
        });
        document.getElementById('mgArtGenAllBtn').addEventListener('click', function () {
            mgRunArtifacts(mgState.flatItems.map(function (i) { return i.id; }));
        });

        // 供全局 inline onclick 使用
        window.mgEditChapter = mgEditChapter;
        window.mgAddChildChapter = mgAddChildChapter;
        window.mgDeleteChapter = mgDeleteChapter;
        window.mgEditQuiz = mgEditQuiz;
        window.mgGenOneQuiz = mgGenOneQuiz;
        window.mgGenOneArtifact = mgGenOneArtifact;

        loadAllData();
    });
})();
</script>
@endsection