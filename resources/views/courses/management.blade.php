@extends('layouts.app')

@section('title', '课程中心 - 蒙太奇')
@section('description', '浏览公开课程和您创建的课程，加入学习之旅')

@section('content')
    <div class="fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- 页面标题和操作栏 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">课程中心</h1>
                        <p class="text-gray-600 mt-1">探索优质课程，管理您创建的课程</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- 搜索框 -->
                    <div class="relative hidden sm:block">
                        <input type="text"
                               placeholder="搜索课程..."
                               class="input pl-10 pr-4 w-64"
                               id="courseSearch">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                    </div>

                    <a href="{{ url('/courses/create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>
                        创建课程
                    </a>
                </div>
            </div>

            <!-- 移动端搜索 -->
            <div class="sm:hidden mb-6">
                <div class="relative">
                    <input type="text"
                           placeholder="搜索课程..."
                           class="input pl-10 pr-4 w-full"
                           id="mobileCourseSearch">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                </div>
            </div>

            <!-- 筛选和统计 -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <!-- 统计信息 -->
                    <div class="flex items-center gap-6 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                            <span>公开课程: <span class="font-medium" id="count_public_courses">0</span></span>
                        </div>
                        @auth
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                <span>我创建的: <span class="font-medium" id="count_created_courses">0</span></span>
                            </div>
                        @endauth
                    </div>

                    <!-- 筛选选项 -->
                    <div class="flex flex-wrap gap-3">
                        <select class="input text-sm py-2 px-3" id="difficultyFilter">
                            <option value="">所有难度</option>
                            <option value="beginner">初级</option>
                            <option value="intermediate">中级</option>
                            <option value="advanced">高级</option>
                        </select>

                        <select class="input text-sm py-2 px-3" id="platformFilter">
                            <option value="">所有平台</option>
                            <option value="Coursera">Coursera</option>
                            <option value="Udemy">Udemy</option>
                            <option value="edX">edX</option>
                            <option value="B站">Bilibili</option>
                            <option value="YouTube">YouTube</option>
                            <option value="慕课网">慕课网</option>
                            <option value="极客时间">极客时间</option>
                            <option value="其他">其他平台</option>
                        </select>

                        <select class="input text-sm py-2 px-3" id="sortFilter">
                            <option value="newest">最新创建</option>
                            <option value="popular">最受欢迎</option>
                            <option value="hours_asc">时长升序</option>
                            <option value="hours_desc">时长降序</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 课程列表卡片 -->
            <div class="card card-elevated">
                <!-- 标签页导航 -->
                <div class="border-b border-gray-200">
                    <nav class="flex overflow-x-auto" id="courseTabs" role="tablist">
                        @auth
                            <button class="px-6 py-4 font-medium text-sm border-b-2 border-blue-500 text-blue-600 whitespace-nowrap"
                                    id="my-courses-tab"
                                    data-tab-target="my-courses"
                                    role="tab">
                                <i class="fas fa-user-circle mr-2"></i>
                                我创建的课程
                                <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">
                            <span id="badge_created_courses">0</span>
                        </span>
                            </button>
                        @endauth

                        <button class="px-6 py-4 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap {{ auth()->guest() ? 'border-blue-500 text-blue-600' : '' }}"
                                id="public-courses-tab"
                                data-tab-target="public-courses"
                                role="tab">
                            <i class="fas fa-globe mr-2"></i>
                            公开课程
                            <span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-800 text-xs rounded-full">
                            <span id="badge_public_courses">0</span>
                        </span>
                        </button>
                    </nav>
                </div>

                <!-- 标签页内容 -->
                <div class="p-6" id="courseTabContent">
                    <!-- 公开课程标签页 -->
                    <div class="tab-pane {{ auth()->guest() ? 'active' : 'hidden' }}"
                         id="public-courses"
                         role="tabpanel">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="public-courses-grid">
                            <div class="col-span-full text-center py-16 text-gray-500">加载课程中...</div>
                        </div>
                    </div>

                    <!-- 我创建的课程标签页 -->
                    @auth
                        <div class="tab-pane active"
                             id="my-courses"
                             role="tabpanel">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="my-courses-grid">
                                <div class="col-span-full text-center py-16 text-gray-500">加载课程中...</div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- 快速操作卡片 -->
            @auth
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="card">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-video text-blue-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">视频课程</h3>
                                    <p class="text-sm text-gray-500">创建包含视频的学习内容</p>
                                </div>
                            </div>
                            <a href="{{ url('/courses/create?type=video') }}" class="btn btn-outline w-full">
                                创建视频课程
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-alt text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">文档课程</h3>
                                    <p class="text-sm text-gray-500">基于文档的学习材料</p>
                                </div>
                            </div>
                            <a href="{{ url('/courses/create?type=document') }}" class="btn btn-outline w-full">
                                创建文档课程
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-link text-purple-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">外部课程</h3>
                                    <p class="text-sm text-gray-500">链接到其他平台课程</p>
                                </div>
                            </div>
                            <a href="{{ url('/courses/create?type=external') }}" class="btn btn-outline w-full">
                                添加外部课程
                            </a>
                        </div>
                    </div>
                </div>
            @endauth
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var CURRENT_USER_ID = Number('{{ auth()->id() ?: 0 }}');
        var courseMgmtState = {
            publicCourses: [],
            createdCourses: [],
            userCourseIds: []
        };

        function getResultData(resp) {
            if (!resp) return {};
            return resp.result || resp.data || {};
        }

        function escapeHtml(str) {
            return $('<div>').text(str || '').html();
        }

        function getPublicStatusBadge(course) {
            var status = Number(course.public_status || 2);
            if (status === 3) return '<span class="px-2.5 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">已公开</span>';
            if (status === 1) return '<span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">私有</span>';
            return '<span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">待审核</span>';
        }

        function renderPublicCourseCard(course) {
            var title = escapeHtml(course.title || '');
            var description = escapeHtml(course.description || '暂无描述');
            var cover = course.cover_image_url
                ? '<img src="' + escapeHtml(course.cover_image_url) + '" alt="' + title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">'
                : '<div class="w-full h-full flex items-center justify-center"><div class="text-center"><i class="fas fa-book-open text-gray-400 text-4xl mb-2"></i><p class="text-sm text-gray-500">' + title + '</p></div></div>';
            var isJoined = courseMgmtState.userCourseIds.indexOf(Number(course.id)) >= 0;
            var isOwner = CURRENT_USER_ID > 0 && Number(course.created_by || 0) === CURRENT_USER_ID;
            var joinAction = '';
            if (CURRENT_USER_ID > 0 && !isJoined) {
                // 未加入：创建者也可加入自己的课程进行学习
                joinAction = (isOwner ? '<span class="px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">创建的课程</span>' : '')
                    + '<button type="button" class="btn btn-success btn-sm hover:shadow-md transition-shadow join-course-btn" data-course-id="' + Number(course.id) + '">加入课程</button>';
            } else if (isJoined) {
                joinAction = '<span class="px-3 py-1.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">已加入</span>'
                    + '<a href="/courses/' + Number(course.id) + '/study" class="btn btn-success btn-sm" title="沉浸学习"><i class="fas fa-play-circle mr-1"></i>继续学习</a>';
            }
            return '<div class="course-card group" data-difficulty="' + escapeHtml(course.difficulty || '') + '" data-platform="' + escapeHtml(course.platform || '') + '" data-hours="' + Number(course.estimated_hours || 0) + '" data-created="' + escapeHtml(course.created_at || '') + '" data-enrollment="' + Number(course.enrollment_count || 0) + '">' +
                '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-blue-300 hover:shadow-lg transition-all duration-200">' +
                '<div class="relative h-48 overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200">' + cover + '</div>' +
                '<div class="p-5"><h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">' + title + '</h3><p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">' + description + '</p>' +
                '<div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4"><span class="flex items-center gap-1"><i class="fas fa-user text-xs"></i>' + escapeHtml(course.instructor || '未知讲师') + '</span><span class="flex items-center gap-1"><i class="fas fa-clock text-xs"></i>' + Number(course.estimated_hours || 0) + '小时</span><span class="flex items-center gap-1"><i class="fas fa-layer-group text-xs"></i>' + Number(course.chapters_count || 0) + '章</span></div>' +
                '<div class="flex items-center justify-between"><div class="flex items-center gap-2"><a href="/courses/' + Number(course.id) + '" class="btn btn-primary btn-sm">查看详情</a></div><div class="flex items-center gap-2">' + joinAction + '</div></div></div></div></div>';
        }

        function renderCreatedCourseCard(course) {
            var title = escapeHtml(course.title || '');
            var description = escapeHtml(course.description || '暂无描述');
            var cover = course.cover_image_url
                ? '<img src="' + escapeHtml(course.cover_image_url) + '" alt="' + title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">'
                : '<div class="w-full h-full flex items-center justify-center"><div class="text-center"><i class="fas fa-user-edit text-green-400 text-4xl mb-2"></i><p class="text-sm text-gray-500">' + title + '</p></div></div>';
            var st = Number(course.public_status || 2);
            var statusActions = '';
            if (st === 1) {
                statusActions = '<button type="button" class="btn btn-outline btn-sm" title="提交公开审核" onclick="submitCourseReview(' + Number(course.id) + ', \'request-public\')"><i class="fas fa-eye"></i></button>';
            } else if (st === 2) {
                statusActions = '<button type="button" class="btn btn-outline btn-sm text-green-600 border-green-200 hover:bg-green-50" title="审核通过并公开" onclick="submitCourseReview(' + Number(course.id) + ', \'approve\')"><i class="fas fa-check-circle"></i></button>';
            } else if (st === 3) {
                statusActions = '<button type="button" class="btn btn-outline btn-sm text-yellow-600 border-yellow-200 hover:bg-yellow-50" title="撤回公开（转为待审核）" onclick="submitCourseReview(' + Number(course.id) + ', \'unapprove\')"><i class="fas fa-eye-slash"></i></button>';
            }
            return '<div class="course-card group" data-difficulty="' + escapeHtml(course.difficulty || '') + '" data-platform="' + escapeHtml(course.platform || '') + '" data-hours="' + Number(course.estimated_hours || 0) + '" data-created="' + escapeHtml(course.created_at || '') + '" data-enrollment="' + Number(course.enrollment_count || 0) + '">' +
                '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-green-300 hover:shadow-lg transition-all duration-200">' +
                '<div class="relative h-48 overflow-hidden bg-gradient-to-br from-green-50 to-green-100">' + cover + '</div>' +
                '<div class="p-5"><h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">' + title + '</h3><p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">' + description + '</p>' +
                '<div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-4"><span class="flex items-center gap-1"><i class="fas fa-chart-line text-xs"></i>学习人数: ' + Number(course.enrollment_count || 0) + '</span><span class="flex items-center gap-1"><i class="fas fa-clock text-xs"></i>' + Number(course.estimated_hours || 0) + '小时</span><span class="flex items-center gap-1"><i class="fas fa-layer-group text-xs"></i>' + Number(course.chapters_count || 0) + '章</span>' + getPublicStatusBadge(course) + '</div>' +
                '<div class="flex items-center justify-between"><div class="flex items-center gap-2"><a href="/courses/' + Number(course.id) + '" class="btn btn-primary btn-sm">查看详情</a></div>' +
                '<div class="flex items-center gap-2">' +
                statusActions +
                '<a href="/courses/' + Number(course.id) + '/edit" class="btn btn-outline btn-sm" title="编辑课程"><i class="fas fa-edit"></i></a>' +
                '<a href="/courses/' + Number(course.id) + '/items" class="btn btn-outline btn-sm" title="完整章节管理（含测验编辑）"><i class="fas fa-list-alt"></i></a>' +
                '<button onclick="deleteCreatedCourse(' + Number(course.id) + ', \'' + title.replace(/'/g, "\\'") + '\')" class="btn btn-outline btn-sm text-red-600 border-red-200 hover:bg-red-50" title="删除课程"><i class="fas fa-trash-alt"></i></button>' +
                '</div></div></div></div></div>';
        }

        // 课程公开状态操作：request-public(提交审核) / approve(审核通过) / unapprove(撤回公开)
        function submitCourseReview(courseId, action) {
            if (!apiRequest) {
                showNotification('API客户端未初始化', 'error');
                return;
            }
            var method = 'POST';
            var url = '/courses/' + Number(courseId) + '/' + action;
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
            apiRequest(method, url, {}).then(function(resp) {
                if (resp && resp.code === 9999) {
                    showNotification(successMsg, 'success');
                    loadCourseManagementData();
                    return;
                }
                showNotification((resp && resp.msg) ? resp.msg : '操作失败', 'error');
            }).catch(function() {
                showNotification('网络错误，请稍后重试', 'error');
            });
        }

        function renderCourseManagement() {
            $('#count_public_courses').text(courseMgmtState.publicCourses.length);
            $('#count_created_courses').text(courseMgmtState.createdCourses.length);
            $('#badge_public_courses').text(courseMgmtState.publicCourses.length);
            $('#badge_created_courses').text(courseMgmtState.createdCourses.length);

            var publicEmpty = '<div class="col-span-full text-center py-16">'
                + '<div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center"><i class="fas fa-book-open text-gray-400 text-3xl"></i></div>'
                + '<h3 class="text-lg font-semibold text-gray-900 mb-2">暂无公开课程</h3>'
                + '<p class="text-gray-600 mb-8 max-w-md mx-auto">公开课程正在陆续上架，您也可以创建课程并提交公开审核！</p>'
                + '<a href="/courses/create" class="btn btn-primary"><i class="fas fa-plus-circle mr-2"></i>创建课程</a>'
                + '</div>';
            var createdEmpty = '<div class="col-span-full text-center py-16">'
                + '<div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center"><i class="fas fa-user-graduate text-gray-400 text-3xl"></i></div>'
                + '<h3 class="text-lg font-semibold text-gray-900 mb-2">您还没有创建课程</h3>'
                + '<p class="text-gray-600 mb-8 max-w-md mx-auto">把您的学习资源沉淀成课程，开始创建第一门课吧！</p>'
                + '<a href="/courses/create" class="btn btn-primary"><i class="fas fa-plus-circle mr-2"></i>创建课程</a>'
                + '</div>';
            var publicGrid = $('#public-courses-grid');
            if (publicGrid.length) {
                publicGrid.html(courseMgmtState.publicCourses.length ? courseMgmtState.publicCourses.map(renderPublicCourseCard).join('') : publicEmpty);
            }
            var createdGrid = $('#my-courses-grid');
            if (createdGrid.length) {
                createdGrid.html(courseMgmtState.createdCourses.length ? courseMgmtState.createdCourses.map(renderCreatedCourseCard).join('') : createdEmpty);
            }
        }

        function loadCourseManagementData() {
            if (!apiRequest) {
                renderCourseLoadError('API客户端未初始化，请刷新页面重试');
                return;
            }
            showCourseLoading();
            apiRequest('GET', '/courses/management', {}).then(function(resp) {
                if (!resp || resp.code !== 9999) {
                    renderCourseLoadError((resp && resp.msg) ? resp.msg : '课程数据加载失败');
                    return;
                }
                var result = getResultData(resp);
                courseMgmtState.publicCourses = Array.isArray(result.public_courses) ? result.public_courses : [];
                courseMgmtState.createdCourses = Array.isArray(result.user_created_courses) ? result.user_created_courses : [];
                courseMgmtState.userCourseIds = Array.isArray(result.user_course_ids) ? result.user_course_ids.map(function(id){ return Number(id); }) : [];
                renderCourseManagement();
            }).catch(function(err) {
                if (err && err.status === 401) {
                    renderCourseLoadError('登录状态已过期或无访问权限，请刷新页面后重试');
                } else {
                    renderCourseLoadError('网络错误，请稍后重试');
                }
            });
        }

        // 展示加载占位
        function showCourseLoading() {
            var loadingHtml = '<div class="col-span-full text-center py-16 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>加载课程中...</div>';
            var publicGrid = $('#public-courses-grid');
            if (publicGrid.length) publicGrid.html(loadingHtml);
            var createdGrid = $('#my-courses-grid');
            if (createdGrid.length) createdGrid.html(loadingHtml);
        }

        // 加载失败提示（可重试），替代原来的静默失败
        function renderCourseLoadError(msg) {
            var html = '<div class="col-span-full text-center py-16">'
                + '<div class="w-16 h-16 mx-auto mb-4 bg-red-50 rounded-full flex items-center justify-center">'
                + '<i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i></div>'
                + '<p class="text-gray-600 mb-6">' + escapeHtml(msg) + '</p>'
                + '<button type="button" class="btn btn-primary" onclick="loadCourseManagementData()">'
                + '<i class="fas fa-sync-alt mr-2"></i>重新加载</button>'
                + '</div>';
            $('#public-courses-grid').html(html);
            $('#my-courses-grid').html(html);
        }

        // 删除我创建的课程
        function deleteCreatedCourse(courseId, title) {
            if (!apiRequest) {
                showNotification('API客户端未初始化', 'error');
                return;
            }
            if (!confirm('确认删除课程「' + title + '」吗？删除后不可恢复。')) return;
            apiRequest('DELETE', '/courses/' + Number(courseId), {}).then(function(resp) {
                if (resp && resp.code === 9999) {
                    showNotification('课程已删除', 'success');
                    loadCourseManagementData();
                    return;
                }
                showNotification((resp && resp.msg) ? resp.msg : '删除失败', 'error');
            }).catch(function() {
                showNotification('网络错误，请稍后重试', 'error');
            });
        }

        $(document).ready(function() {
            loadCourseManagementData();

            // 初始化标签页切换
            initTabSwitching();

            // 初始化搜索功能
            initSearch();

            // 初始化筛选与排序
            initFiltering();

            // 加入课程走v2接口
            $(document).on('click', '.join-course-btn', function(e) {
                e.preventDefault();
                if (!apiRequest) {
                    showNotification('API客户端未初始化', 'error');
                    return;
                }
                var courseId = Number($(this).data('course-id') || 0);
                if (!courseId) {
                    showNotification('课程ID错误', 'error');
                    return;
                }
                var $btn = $(this);
                var original = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>加入中...');

                apiRequest('POST', '/courses/' + courseId + '/join', {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        loadCourseManagementData();
                        return;
                    }
                    showNotification((resp && resp.msg) ? resp.msg : '加入课程失败', 'error');
                }).catch(function() {
                    showNotification('网络错误，请稍后重试', 'error');
                }).finally(function() {
                    $btn.prop('disabled', false).html(original);
                });
            });
        });

        // 初始化标签页切换
        var IS_GUEST = {{ auth()->guest() ? 'true' : 'false' }};

        function activatePane(tabId) {
            var tabButtons = document.querySelectorAll('[data-tab-target]');
            var tabPanes = document.querySelectorAll('.tab-pane');

            tabButtons.forEach(function(btn) {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            tabPanes.forEach(function(pane) {
                pane.classList.add('hidden');
                pane.classList.remove('active');
            });

            var button = document.querySelector('[data-tab-target="' + tabId + '"]');
            if (button) {
                button.classList.add('border-blue-500', 'text-blue-600');
                button.classList.remove('border-transparent', 'text-gray-500');
            }
            var targetPane = document.getElementById(tabId);
            if (targetPane) {
                targetPane.classList.remove('hidden');
                setTimeout(function() {
                    targetPane.classList.add('active');
                }, 10);
            }
        }

        function initTabSwitching() {
            document.querySelectorAll('[data-tab-target]').forEach(function(button) {
                button.addEventListener('click', function() {
                    activatePane(button.getAttribute('data-tab-target'));
                });
            });

            // 登录默认展示"我创建的课程"，访客默认展示"公开课程"（真实布尔，避免字符串陷阱）
            activatePane(IS_GUEST ? 'public-courses' : 'my-courses');
        }

        // 切换标签页
        function switchTab(tabId) {
            var button = document.querySelector('[data-tab-target="' + tabId + '"]');
            if (button) button.click();
        }

        // 初始化搜索（桌面+移动端都绑定）
        function initSearch() {
            ['courseSearch', 'mobileCourseSearch'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', debounce(function() {
                        applyViewState();
                    }, 300));
                }
            });
        }

        // 初始化筛选与排序
        function initFiltering() {
            ['difficultyFilter', 'platformFilter', 'sortFilter'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('change', applyViewState);
            });
        }

        // 统一应用"搜索 + 难度 + 平台 + 排序"（叠加而非互相覆盖）
        function applyViewState() {
            var term = '';
            ['courseSearch', 'mobileCourseSearch'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el && el.value) term = el.value;
            });
            term = (term || '').toLowerCase().trim();

            var difficulty = document.getElementById('difficultyFilter') ? document.getElementById('difficultyFilter').value : '';
            var platform = document.getElementById('platformFilter') ? document.getElementById('platformFilter').value : '';
            var sortBy = document.getElementById('sortFilter') ? document.getElementById('sortFilter').value : '';

            var tabId = getActiveTab();
            var pane = document.getElementById(tabId);
            if (!pane) return;
            var grid = pane.querySelector('.grid');
            if (!grid) return;
            var courseCards = Array.from(grid.querySelectorAll('.course-card'));

            courseCards.forEach(function(card) {
                var titleEl = card.querySelector('h3');
                var descEl = card.querySelector('p');
                var title = titleEl ? titleEl.textContent : '';
                var desc = descEl ? descEl.textContent : '';
                var matchSearch = !term || title.toLowerCase().indexOf(term) >= 0 || desc.toLowerCase().indexOf(term) >= 0;
                var matchDifficulty = !difficulty || (card.getAttribute('data-difficulty') || '') === difficulty;
                var matchPlatform = !platform || (card.getAttribute('data-platform') || '') === platform;
                card.style.display = (matchSearch && matchDifficulty && matchPlatform) ? 'block' : 'none';
            });

            courseCards.sort(function(a, b) {
                switch(sortBy) {
                    case 'newest':
                        return new Date(b.getAttribute('data-created')) - new Date(a.getAttribute('data-created'));
                    case 'popular':
                        return (parseInt(b.getAttribute('data-enrollment')) || 0) - (parseInt(a.getAttribute('data-enrollment')) || 0);
                    case 'hours_asc':
                        return (parseInt(a.getAttribute('data-hours')) || 0) - (parseInt(b.getAttribute('data-hours')) || 0);
                    case 'hours_desc':
                        return (parseInt(b.getAttribute('data-hours')) || 0) - (parseInt(a.getAttribute('data-hours')) || 0);
                    default:
                        return 0;
                }
            });

            courseCards.forEach(function(card) {
                grid.appendChild(card);
            });
        }

        // 获取当前激活的标签页
        function getActiveTab() {
            var activeButton = document.querySelector('[data-tab-target].border-blue-500');
            return activeButton ? activeButton.getAttribute('data-tab-target') : (IS_GUEST ? 'public-courses' : 'my-courses');
        }

        // 防抖函数
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // 显示通知
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'green',
                error: 'red',
                warning: 'yellow',
                info: 'blue'
            };

            const icon = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };

            const notification = $(`
        <div class="fixed top-4 right-4 z-50 fade-in">
            <div class="card shadow-lg border-l-4 border-${colors[type]}-500">
                <div class="p-4 flex items-start gap-3">
                    <i class="fas fa-${icon[type]} text-${colors[type]}-500 text-lg"></i>
                    <p class="text-sm text-gray-800">${message}</p>
                    <button class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `);

            $('body').append(notification);

            notification.find('button').click(function() {
                notification.remove();
            });

            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    </script>

    <style>
        /* 标签页样式 */
        [data-tab-target] {
            position: relative;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        [data-tab-target]:hover {
            color: var(--gray-700);
        }

        /* 标签页内容动画 */
        .tab-pane {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .tab-pane.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* 课程卡片样式 */
        .course-card {
            transition: transform 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-4px);
        }

        /* 进度条颜色 */
        .progress-bar.bg-gradient-to-r.from-purple-500.to-purple-600 {
            background: linear-gradient(90deg, #8a6cff, #7c3aed);
        }

        /* 文本截断 */
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .grid.grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
                grid-template-columns: 1fr;
            }

            #courseTabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            [data-tab-target] {
                white-space: nowrap;
                padding: 12px 16px;
            }
        }

        /* 图片hover效果 */
        .group:hover .group-hover\:scale-105 {
            transform: scale(1.05);
        }

        /* 渐变背景 */
        .bg-gradient-to-br.from-gray-100.to-gray-200 {
            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
        }

        .bg-gradient-to-br.from-green-50.to-green-100 {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        }

        /* 状态徽章颜色 */
        .bg-blue-100 { background-color: rgba(59, 130, 246, 0.1); }
        .text-blue-800 { color: #1e40af; }
        .bg-green-100 { background-color: rgba(16, 185, 129, 0.1); }
        .text-green-800 { color: #065f46; }
        .bg-yellow-100 { background-color: rgba(245, 158, 11, 0.1); }
        .text-yellow-800 { color: #92400e; }
        .bg-red-100 { background-color: rgba(239, 68, 68, 0.1); }
        .text-red-800 { color: #991b1b; }
        .bg-gray-100 { background-color: rgba(209, 213, 219, 0.1); }
        .text-gray-600 { color: #475467; }
    </style>
@endsection