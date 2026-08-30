@extends('layouts.app')

@section('title', '我的课程 - 蒙太奇')
@section('description', '查看和管理您的课程学习进度')

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
                        <h1 class="text-2xl font-bold text-gray-900">我的课程</h1>
                        <p class="text-gray-600 mt-1">跟踪学习进度，点击课程卡片继续学习</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('/course/management') }}" class="btn btn-primary">
                        <i class="fas fa-compass mr-2"></i>
                        浏览课程
                    </a>
                    <a href="{{ url('/courses/create') }}" class="btn btn-outline">
                        <i class="fas fa-plus-circle mr-2"></i>
                        创建课程
                    </a>
                </div>
            </div>

            <!-- 快速数据概览 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">总课程数</p>
                                <p id="courseCountValue" class="text-xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">已完成</p>
                                <p id="courseCompletedValue" class="text-xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">学习中</p>
                                <p id="courseActiveValue" class="text-xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">平均进度</p>
                                <p id="courseAvgProgressValue" class="text-xl font-bold text-gray-900">0%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 我的课程列表 -->
            <div class="card card-elevated">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">我的学习课程</h2>
                            <p class="text-sm text-gray-500 mt-1">点击课程卡片继续学习</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- 排序筛选 -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">排序:</label>
                                <select class="input text-sm py-1 px-2" id="sortCourses">
                                    <option value="recent">最近学习</option>
                                    <option value="progress_desc">进度降序</option>
                                    <option value="progress_asc">进度升序</option>
                                    <option value="name">名称排序</option>
                                </select>
                            </div>

                            <!-- 状态筛选 -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">状态:</label>
                                <select class="input text-sm py-1 px-2" id="filterStatus">
                                    <option value="all">全部状态</option>
                                    <option value="active">学习中</option>
                                    <option value="completed">已完成</option>
                                    <option value="planned">计划中</option>
                                    <option value="paused">暂停</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div id="courseListContainer">
                        <div class="text-center py-16 text-gray-500">加载课程中...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var allCourses = [];

        function escapeHtml(str) {
            return $('<div>').text(str || '').html();
        }

        function getStatusConfig(status) {
            var map = {
                planned: { color: 'gray', icon: 'fa-clock', text: '计划中' },
                active: { color: 'blue', icon: 'fa-play-circle', text: '学习中' },
                completed: { color: 'green', icon: 'fa-check-circle', text: '已完成' },
                paused: { color: 'yellow', icon: 'fa-pause-circle', text: '暂停' },
                dropped: { color: 'red', icon: 'fa-times-circle', text: '已放弃' }
            };
            return map[status] || map.planned;
        }

        function updateSummary(courses) {
            var total = courses.length;
            var completed = courses.filter(function(c) { return c.status === 'completed'; }).length;
            var active = courses.filter(function(c) { return c.status === 'active'; }).length;
            var avg = total > 0
                ? Math.round(courses.reduce(function(sum, c) { return sum + Number(c.progress_percent || 0); }, 0) / total)
                : 0;

            $('#courseCountValue').text(total);
            $('#courseCompletedValue').text(completed);
            $('#courseActiveValue').text(active);
            $('#courseAvgProgressValue').text(avg + '%');
        }

        function sortedAndFilteredCourses() {
            var status = $('#filterStatus').val();
            var sortType = $('#sortCourses').val();

            var filtered = allCourses.filter(function(item) {
                return status === 'all' || item.status === status;
            });

            filtered.sort(function(a, b) {
                if (sortType === 'progress_desc') {
                    return Number(b.progress_percent || 0) - Number(a.progress_percent || 0);
                }
                if (sortType === 'progress_asc') {
                    return Number(a.progress_percent || 0) - Number(b.progress_percent || 0);
                }
                if (sortType === 'name') {
                    return String(a.title || '').localeCompare(String(b.title || ''));
                }
                var aTime = a.last_studied_at ? new Date(a.last_studied_at).getTime() : 0;
                var bTime = b.last_studied_at ? new Date(b.last_studied_at).getTime() : 0;
                return bTime - aTime;
            });
            return filtered;
        }

        function renderCourseList() {
            var list = sortedAndFilteredCourses();
            var container = $('#courseListContainer');

            if (!list.length) {
                container.html(
                    '<div class="text-center py-16">'
                    + '<div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">'
                    + '<i class="fas fa-book-open text-gray-400 text-3xl"></i></div>'
                    + '<h3 class="text-lg font-semibold text-gray-900 mb-3">暂无课程</h3>'
                    + '<p class="text-gray-600 mb-8 max-w-md mx-auto">您还没有加入任何课程，前往课程中心开始学习吧！</p>'
                    + '<a href="/course/management" class="btn btn-primary"><i class="fas fa-compass mr-2"></i>浏览课程</a>'
                    + '</div>'
                );
                return;
            }

            container.html(list.map(function(item) {
                var status = getStatusConfig(item.status);
                var course = item.course || {};
                var progress = Number(item.progress_percent || 0);
                var detailUrl = '/courses/' + (course.id || item.course_id || '');
                var studyUrl = detailUrl + '/study';
                var title = escapeHtml(item.title || course.title || '未命名课程');
                var description = escapeHtml(course.description || '暂无描述');
                var cover = course.cover_image_url ? '<img src="' + escapeHtml(course.cover_image_url) + '" alt="' + title + '" class="w-full h-full object-cover">' : '<div class="text-center p-4"><i class="fas fa-book-open text-gray-400 text-3xl mb-2"></i><p class="text-xs text-gray-500">' + title + '</p></div>';
                var lastStudied = item.last_studied_at ? String(item.last_studied_at).slice(5, 10) : '未开始';
                var hours = Number(course.estimated_hours || 0) > 0 ? Number(course.estimated_hours) + ' 小时' : '未设置';

                return ''
                    + '<div class="mb-6 last:mb-0 course-card">'
                    + '<div class="flex flex-col lg:flex-row gap-6 p-5 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all duration-200">'
                    + '<div class="lg:w-1/4"><div class="aspect-video rounded-lg overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">' + cover + '</div></div>'
                    + '<div class="lg:w-2/4">'
                    + '<div class="flex items-start justify-between mb-3"><div><h3 class="text-lg font-semibold text-gray-900 mb-1"><a href="' + detailUrl + '" class="hover:text-blue-600 transition-colors">' + title + '</a></h3><p class="text-sm text-gray-500 line-clamp-2">' + description + '</p></div>'
                    + '<div class="flex flex-col items-end gap-2"><span class="px-3 py-1 bg-' + status.color + '-100 text-' + status.color + '-800 text-xs font-semibold rounded-full flex items-center gap-1"><i class="fas ' + status.icon + ' text-xs"></i>' + status.text + '</span></div></div>'
                    + '<div class="mb-4"><div class="flex items-center justify-between text-sm mb-1"><span class="text-gray-600">学习进度</span><span class="font-medium text-gray-900">' + progress + '%</span></div><div class="progress h-2"><div class="progress-bar bg-gradient-to-r from-blue-500 to-purple-600" style="width:' + progress + '%"></div></div></div>'
                    + '<div class="flex flex-wrap gap-4 text-sm text-gray-500"><span class="flex items-center gap-1"><i class="fas fa-layer-group text-xs"></i>章节: ' + Number(course.chapters_count || 0) + '</span><span class="flex items-center gap-1"><i class="fas fa-clock text-xs"></i>时长: ' + hours + '</span><span class="flex items-center gap-1"><i class="fas fa-calendar-alt text-xs"></i>最后学习: ' + lastStudied + '</span></div>'
                    + '</div>'
                    + '<div class="lg:w-1/4 flex flex-col gap-3"><a href="' + studyUrl + '" class="btn btn-primary w-full justify-center"><i class="fas fa-play-circle mr-2"></i>' + (item.status === 'completed' ? '复习课程' : '继续学习') + '</a>'
                    + '<button type="button" class="btn btn-outline w-full justify-center update-status-btn" data-id="' + Number(item.id || 0) + '" data-status="' + escapeHtml(item.status || 'planned') + '"><i class="fas fa-exchange-alt mr-2"></i>更新状态</button>'
                    + '</div></div></div>';
            }).join(''));
        }

        function loadCourses() {
            if (!apiRequest) {
                $('#courseListContainer').html('<div class="text-center py-16 text-gray-500">API客户端未初始化</div>');
                return;
            }
            apiRequest('GET', '/courses', {}).then(function(resp) {
                if (resp.code !== 9999 || !resp.result) {
                    throw new Error(resp.msg || '加载课程失败');
                }
                allCourses = Array.isArray(resp.result.user_courses) ? resp.result.user_courses : [];
                updateSummary(allCourses);
                renderCourseList();
            }).catch(function(err) {
                console.error('load courses failed:', err);
                $('#courseListContainer').html('<div class="text-center py-16 text-gray-500">课程加载失败，请稍后重试</div>');
            });
        }

        $(document).ready(function() {
            $('#sortCourses').change(function() {
                renderCourseList();
            });
            $('#filterStatus').change(function() {
                renderCourseList();
            });
            loadCourses();

            // 更新学习状态（暂停/完成/放弃/恢复）
            $(document).on('click', '.update-status-btn', function() {
                var id = Number($(this).data('id') || 0);
                if (!id || !apiRequest) {
                    Swal.fire('提示', '操作不可用，请刷新页面重试', 'info');
                    return;
                }
                var current = String($(this).data('status') || 'planned');
                var statusMap = {
                    planned: '计划中',
                    active: '学习中',
                    completed: '已完成',
                    paused: '暂停',
                    dropped: '已放弃'
                };
                var optsHtml = Object.keys(statusMap).map(function(k) {
                    return '<option value="' + k + '"' + (k === current ? ' selected' : '') + '>' + statusMap[k] + '</option>';
                }).join('');

                Swal.fire({
                    title: '更新学习状态',
                    html: '<select id="enrollmentStatusSelect" class="swal2-select" style="width:100%">' + optsHtml + '</select>',
                    showCancelButton: true,
                    confirmButtonText: '保存',
                    cancelButtonText: '取消',
                    preConfirm: function() {
                        return document.getElementById('enrollmentStatusSelect').value;
                    }
                }).then(function(result) {
                    if (!result.value) return;
                    apiRequest('PUT', '/course-enrollments/' + id, { status: result.value }).then(function(resp) {
                        if (resp && resp.code === 9999) {
                            Swal.fire('已更新', resp.msg || '学习状态已更新', 'success').then(function() {
                                loadCourses();
                            });
                            return;
                        }
                        Swal.fire('操作失败', (resp && resp.msg) ? resp.msg : '请稍后重试', 'error');
                    }).catch(function() {
                        Swal.fire('网络错误', '请稍后重试', 'error');
                    });
                });
            });
        });
    </script>

    <style>
        /* 课程卡片样式 */
        .course-card {
            transition: all 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-2px);
        }

        /* 进度条颜色 */
        .progress-bar.bg-gradient-to-r.from-blue-500.to-purple-600 {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
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
        .text-gray-800 { color: #374151; }

        /* 文本截断 */
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* 响应式调整 */
        @media (max-width: 1024px) {
            .course-card .flex-col.lg\:flex-row {
                flex-direction: column;
            }

            .course-card .lg\:w-1\/4,
            .course-card .lg\:w-2\/4,
            .course-card .lg\:w-1\/4 {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .text-xl {
                font-size: 1.25rem;
            }

            .grid.grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection