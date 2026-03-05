@extends('layouts.app')

@section('title', '课程列表 - 蒙太奇')
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
                        <p class="text-gray-600 mt-1">探索优质课程，开启学习之旅</p>
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
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                            <span>已加入: <span class="font-medium" id="count_joined_courses">0</span></span>
                        </div>
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
                            <option value="mooc">MOOC平台</option>
                            <option value="udemy">Udemy</option>
                            <option value="coursera">Coursera</option>
                            <option value="self">自建课程</option>
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

                        @auth
                            <button class="px-6 py-4 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap"
                                    id="joined-courses-tab"
                                    data-tab-target="joined-courses"
                                    role="tab">
                                <i class="fas fa-bookmark mr-2"></i>
                                已加入课程
                                <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">
                            <span id="badge_joined_courses">0</span>
                        </span>
                            </button>
                        @endauth
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
                        <div class="tab-pane active hidden"
                             id="my-courses"
                             role="tabpanel">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="my-courses-grid">
                                <div class="col-span-full text-center py-16 text-gray-500">加载课程中...</div>
                            </div>
                        </div>

                        <!-- 已加入课程标签页 -->
                        <div class="tab-pane hidden"
                             id="joined-courses"
                             role="tabpanel">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="joined-courses-grid">
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

        function renderPublicCourseCard(course) {
            var title = escapeHtml(course.title || '');
            var description = escapeHtml(course.description || '暂无描述');
            var cover = course.cover_image_url
                ? '<img src="' + escapeHtml(course.cover_image_url) + '" alt="' + title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">'
                : '<div class="w-full h-full flex items-center justify-center"><div class="text-center"><i class="fas fa-book-open text-gray-400 text-4xl mb-2"></i><p class="text-sm text-gray-500">' + title + '</p></div></div>';
            var isJoined = courseMgmtState.userCourseIds.indexOf(Number(course.id)) >= 0;
            var isOwner = CURRENT_USER_ID > 0 && Number(course.created_by || 0) === CURRENT_USER_ID;
            var joinAction = '';
            if (CURRENT_USER_ID > 0 && !isJoined && !isOwner) {
                joinAction = '<button type="button" class="btn btn-success btn-sm hover:shadow-md transition-shadow join-course-btn" data-course-id="' + Number(course.id) + '">加入课程</button>';
            } else if (isOwner) {
                joinAction = '<span class="px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">创建的课程</span>';
            } else if (isJoined) {
                joinAction = '<span class="px-3 py-1.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">已加入</span>';
            }
            return '<div class="course-card group" data-difficulty="' + escapeHtml(course.difficulty || '') + '" data-platform="' + escapeHtml(course.platform || '') + '" data-hours="' + Number(course.estimated_hours || 0) + '" data-created="' + escapeHtml(course.created_at || '') + '" data-enrollment="' + Number(course.enrollment_count || 0) + '">' +
                '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-blue-300 hover:shadow-lg transition-all duration-200">' +
                '<div class="relative h-48 overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200">' + cover + '</div>' +
                '<div class="p-5"><h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">' + title + '</h3><p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">' + description + '</p>' +
                '<div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4"><span class="flex items-center gap-1"><i class="fas fa-user text-xs"></i>' + escapeHtml(course.instructor || '未知讲师') + '</span><span class="flex items-center gap-1"><i class="fas fa-clock text-xs"></i>' + Number(course.estimated_hours || 0) + '小时</span><span class="flex items-center gap-1"><i class="fas fa-layer-group text-xs"></i>' + Number(course.chapters_count || 0) + '章</span></div>' +
                '<div class="flex items-center justify-between"><a href="/courses/' + Number(course.id) + '" class="btn btn-primary btn-sm">查看详情</a><div class="flex items-center gap-2">' + joinAction + '</div></div></div></div></div>';
        }

        function renderCreatedCourseCard(course) {
            var title = escapeHtml(course.title || '');
            var description = escapeHtml(course.description || '暂无描述');
            var cover = course.cover_image_url
                ? '<img src="' + escapeHtml(course.cover_image_url) + '" alt="' + title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">'
                : '<div class="w-full h-full flex items-center justify-center"><div class="text-center"><i class="fas fa-user-edit text-green-400 text-4xl mb-2"></i><p class="text-sm text-gray-500">' + title + '</p></div></div>';
            return '<div class="course-card group" data-difficulty="' + escapeHtml(course.difficulty || '') + '" data-platform="' + escapeHtml(course.platform || '') + '" data-hours="' + Number(course.estimated_hours || 0) + '" data-created="' + escapeHtml(course.created_at || '') + '" data-enrollment="' + Number(course.enrollment_count || 0) + '">' +
                '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-green-300 hover:shadow-lg transition-all duration-200">' +
                '<div class="relative h-48 overflow-hidden bg-gradient-to-br from-green-50 to-green-100">' + cover + '</div>' +
                '<div class="p-5"><h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">' + title + '</h3><p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">' + description + '</p>' +
                '<div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4"><span class="flex items-center gap-1"><i class="fas fa-chart-line text-xs"></i>学习人数: ' + Number(course.enrollment_count || 0) + '</span><span class="flex items-center gap-1"><i class="fas fa-clock text-xs"></i>' + Number(course.estimated_hours || 0) + '小时</span></div>' +
                '<div class="flex items-center justify-between"><a href="/courses/' + Number(course.id) + '" class="btn btn-primary btn-sm">查看详情</a><div class="flex items-center gap-2"><button onclick="showCourseStats(' + Number(course.id) + ')" class="btn btn-outline btn-sm"><i class="fas fa-chart-bar"></i></button><button onclick="manageCourse(' + Number(course.id) + ')" class="btn btn-outline btn-sm"><i class="fas fa-cog"></i></button></div></div></div></div></div>';
        }

        function renderJoinedCourseCard(course) {
            var title = escapeHtml(course.title || '');
            var progress = Number(course.user_progress || 0);
            var cover = course.cover_image_url
                ? '<img src="' + escapeHtml(course.cover_image_url) + '" alt="' + title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">'
                : '<div class="w-full h-full flex items-center justify-center"><div class="text-center"><i class="fas fa-bookmark text-purple-400 text-4xl mb-2"></i><p class="text-sm text-gray-500">' + title + '</p></div></div>';
            return '<div class="course-card group" data-difficulty="' + escapeHtml(course.difficulty || '') + '" data-platform="' + escapeHtml(course.platform || '') + '" data-hours="' + Number(course.estimated_hours || 0) + '" data-created="' + escapeHtml(course.created_at || '') + '" data-enrollment="' + Number(course.enrollment_count || 0) + '">' +
                '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-purple-300 hover:shadow-lg transition-all duration-200"><div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-50 to-purple-100">' + cover + '</div>' +
                '<div class="p-5"><h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">' + title + '</h3><div class="mb-4"><div class="flex items-center justify-between text-sm mb-1"><span class="text-gray-600">学习进度</span><span class="font-medium text-gray-900">' + progress + '%</span></div><div class="progress h-2"><div class="progress-bar bg-gradient-to-r from-purple-500 to-purple-600" style="width:' + progress + '%"></div></div></div>' +
                '<div class="flex items-center justify-between"><a href="/courses/' + Number(course.id) + '" class="btn btn-primary btn-sm">' + (progress >= 100 ? '复习课程' : '继续学习') + '</a><button onclick="showCourseProgress(' + Number(course.id) + ')" class="btn btn-outline btn-sm"><i class="fas fa-chart-line"></i></button></div></div></div></div>';
        }

        function renderCourseManagement() {
            $('#count_public_courses').text(courseMgmtState.publicCourses.length);
            $('#count_created_courses').text(courseMgmtState.createdCourses.length);
            $('#count_joined_courses').text(courseMgmtState.userCourseIds.length);
            $('#badge_public_courses').text(courseMgmtState.publicCourses.length);
            $('#badge_created_courses').text(courseMgmtState.createdCourses.length);
            $('#badge_joined_courses').text(courseMgmtState.userCourseIds.length);

            var publicGrid = $('#public-courses-grid');
            if (publicGrid.length) {
                publicGrid.html(courseMgmtState.publicCourses.length ? courseMgmtState.publicCourses.map(renderPublicCourseCard).join('') : '<div class="col-span-full text-center py-16 text-gray-500">暂无公开课程</div>');
            }
            var createdGrid = $('#my-courses-grid');
            if (createdGrid.length) {
                createdGrid.html(courseMgmtState.createdCourses.length ? courseMgmtState.createdCourses.map(renderCreatedCourseCard).join('') : '<div class="col-span-full text-center py-16 text-gray-500">您还没有创建课程</div>');
            }
            var joined = courseMgmtState.publicCourses.filter(function(c) { return courseMgmtState.userCourseIds.indexOf(Number(c.id)) >= 0; });
            var joinedGrid = $('#joined-courses-grid');
            if (joinedGrid.length) {
                joinedGrid.html(joined.length ? joined.map(renderJoinedCourseCard).join('') : '<div class="col-span-full text-center py-16 text-gray-500">您还没有加入课程</div>');
            }
        }

        function loadCourseManagementData() {
            if (!apiRequest) return;
            apiRequest('GET', '/courses/management', {}).then(function(resp) {
                if (!resp || resp.code !== 9999) return;
                var result = getResultData(resp);
                courseMgmtState.publicCourses = Array.isArray(result.public_courses) ? result.public_courses : [];
                courseMgmtState.createdCourses = Array.isArray(result.user_created_courses) ? result.user_created_courses : [];
                courseMgmtState.userCourseIds = Array.isArray(result.user_course_ids) ? result.user_course_ids.map(function(id){ return Number(id); }) : [];
                renderCourseManagement();
            }).catch(function() {});
        }

        $(document).ready(function() {
            loadCourseManagementData();

            // 初始化标签页切换
            initTabSwitching();

            // 初始化搜索功能
            initSearch();

            // 初始化筛选功能
            initFiltering();

            // 初始化排序功能
            initSorting();

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
        function initTabSwitching() {
            const tabButtons = document.querySelectorAll('[data-tab-target]');
            const tabPanes = document.querySelectorAll('.tab-pane');

            // 设置初始激活状态
            let initialTab = 'public-courses';
            if (!{{ auth()->guest() ? 'true' : 'false' }}) {
                initialTab = 'my-courses';
                document.getElementById('my-courses').classList.add('active');
                document.querySelector('[data-tab-target="my-courses"]').classList.add('border-blue-500', 'text-blue-600');
                document.querySelector('[data-tab-target="my-courses"]').classList.remove('border-transparent', 'text-gray-500');
            } else {
                document.getElementById('public-courses').classList.add('active');
                document.querySelector('[data-tab-target="public-courses"]').classList.add('border-blue-500', 'text-blue-600');
                document.querySelector('[data-tab-target="public-courses"]').classList.remove('border-transparent', 'text-gray-500');
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-tab-target');

                    // 更新按钮状态
                    tabButtons.forEach(btn => {
                        btn.classList.remove('border-blue-500', 'text-blue-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });

                    button.classList.add('border-blue-500', 'text-blue-600');
                    button.classList.remove('border-transparent', 'text-gray-500');

                    // 更新面板状态
                    tabPanes.forEach(pane => {
                        pane.classList.add('hidden');
                        pane.classList.remove('active');
                    });

                    const targetPane = document.getElementById(targetId);
                    targetPane.classList.remove('hidden');
                    setTimeout(() => {
                        targetPane.classList.add('active');
                    }, 10);
                });
            });
        }

        // 切换标签页
        function switchTab(tabId) {
            const button = document.querySelector(`[data-tab-target="${tabId}"]`);
            if (button) button.click();
        }

        // 初始化搜索功能
        function initSearch() {
            const searchInput = document.getElementById('courseSearch') || document.getElementById('mobileCourseSearch');

            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    const searchTerm = this.value.toLowerCase();
                    filterCoursesBySearch(searchTerm);
                }, 300));
            }
        }

        // 初始化筛选功能
        function initFiltering() {
            const filters = ['difficultyFilter', 'platformFilter'];

            filters.forEach(filterId => {
                const filter = document.getElementById(filterId);
                if (filter) {
                    filter.addEventListener('change', applyFilters);
                }
            });
        }

        // 初始化排序功能
        function initSorting() {
            const sortFilter = document.getElementById('sortFilter');
            if (sortFilter) {
                sortFilter.addEventListener('change', applySorting);
            }
        }

        // 应用筛选
        function applyFilters() {
            const difficulty = document.getElementById('difficultyFilter').value;
            const platform = document.getElementById('platformFilter').value;
            const currentTab = getActiveTab();

            filterCourses(currentTab, { difficulty, platform });
        }

        // 应用排序
        function applySorting() {
            const sortBy = document.getElementById('sortFilter').value;
            const currentTab = getActiveTab();

            sortCourses(currentTab, sortBy);
        }

        // 根据搜索筛选课程
        function filterCoursesBySearch(searchTerm) {
            const currentTab = getActiveTab();
            const courseCards = document.querySelectorAll(`#${currentTab} .course-card`);

            courseCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm) || searchTerm === '') {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // 综合筛选课程
        function filterCourses(tabId, filters) {
            const courseCards = document.querySelectorAll(`#${tabId} .course-card`);

            courseCards.forEach(card => {
                let shouldShow = true;

                // 难度筛选
                if (filters.difficulty) {
                    const difficulty = (card.getAttribute('data-difficulty') || '').toLowerCase();
                    if (difficulty !== filters.difficulty) {
                        shouldShow = false;
                    }
                }

                // 平台筛选
                if (filters.platform) {
                    // 这里需要根据实际的数据结构调整
                    // 假设有data-platform属性
                    const platform = card.getAttribute('data-platform') || '';
                    if (platform !== filters.platform) {
                        shouldShow = false;
                    }
                }

                card.style.display = shouldShow ? 'block' : 'none';
            });
        }

        // 排序课程
        function sortCourses(tabId, sortBy) {
            const pane = document.getElementById(tabId);
            if (!pane) return;
            const grid = pane.querySelector('.grid');
            if (!grid) return;
            const courseCards = Array.from(grid.querySelectorAll('.course-card'));

            courseCards.sort((a, b) => {
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

            // 重新排序DOM
            courseCards.forEach(card => {
                grid.appendChild(card);
            });
        }

        // 获取当前激活的标签页
        function getActiveTab() {
            const activeButton = document.querySelector('[data-tab-target].border-blue-500');
            return activeButton ? activeButton.getAttribute('data-tab-target') : 'public-courses';
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

        // 显示课程统计
        function showCourseStats(courseId) {
            showNotification('课程统计功能开发中', 'info');
        }

        // 管理课程
        function manageCourse(courseId) {
            showNotification('课程管理功能开发中', 'info');
        }

        // 显示课程进度
        function showCourseProgress(courseId) {
            showNotification('课程进度详情功能开发中', 'info');
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

        .bg-gradient-to-br.from-purple-50.to-purple-100 {
            background: linear-gradient(135deg, #faf5ff, #f3e8ff);
        }
    </style>
@endsection
