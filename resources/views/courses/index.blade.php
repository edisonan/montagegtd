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
                        <p class="text-gray-600 mt-1">管理您的学习计划，跟踪学习进度</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('/courses') }}" class="btn btn-primary">
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
                                <p class="text-xl font-bold text-gray-900">{{ count($user_courses ?? []) }}</p>
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
                                <p class="text-xl font-bold text-gray-900">
                                    {{ collect($user_courses ?? [])->where('status', 'completed')->count() }}
                                </p>
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
                                <p class="text-xl font-bold text-gray-900">
                                    {{ collect($user_courses ?? [])->where('status', 'active')->count() }}
                                </p>
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
                                <p class="text-xl font-bold text-gray-900">
                                    @php
                                        $totalProgress = collect($user_courses ?? [])->sum('progress_percent');
                                        $courseCount = count($user_courses ?? []);
                                        $avgProgress = $courseCount > 0 ? round($totalProgress / $courseCount) : 0;
                                    @endphp
                                    {{ $avgProgress }}%
                                </p>
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
                    @forelse($user_courses ?? [] as $userCourse)
                        <div class="mb-6 last:mb-0 course-card"
                             data-status="{{ $userCourse->status }}"
                             data-progress="{{ $userCourse->progress_percent }}"
                             data-name="{{ strtolower($userCourse->title) }}">
                            <div class="flex flex-col lg:flex-row gap-6 p-5 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                <!-- 课程封面 -->
                                <div class="lg:w-1/4">
                                    <div class="aspect-video rounded-lg overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        @if($userCourse->course->cover_image)
                                            <img src="{{ $userCourse->course->cover_image }}"
                                                 alt="{{ $userCourse->title }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="text-center p-4">
                                                <i class="fas fa-book-open text-gray-400 text-3xl mb-2"></i>
                                                <p class="text-xs text-gray-500">{{ $userCourse->title }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- 课程信息 -->
                                <div class="lg:w-2/4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <a href="{{ url('/courses/' . $userCourse->course->id) }}"
                                                   class="hover:text-blue-600 transition-colors">
                                                    {{ $userCourse->title }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-500 line-clamp-2">
                                                {{ $userCourse->course->description ?? '暂无描述' }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col items-end gap-2">
                                            <!-- 课程状态 -->
                                            @php
                                                $statusConfig = [
                                                    'planned' => ['color' => 'gray', 'icon' => 'fa-clock', 'text' => '计划中'],
                                                    'active' => ['color' => 'blue', 'icon' => 'fa-play-circle', 'text' => '学习中'],
                                                    'completed' => ['color' => 'green', 'icon' => 'fa-check-circle', 'text' => '已完成'],
                                                    'paused' => ['color' => 'yellow', 'icon' => 'fa-pause-circle', 'text' => '暂停'],
                                                    'dropped' => ['color' => 'red', 'icon' => 'fa-times-circle', 'text' => '已放弃'],
                                                ];
                                                $config = $statusConfig[$userCourse->status] ?? $statusConfig['planned'];
                                            @endphp
                                            <span class="px-3 py-1 bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 text-xs font-semibold rounded-full flex items-center gap-1">
                                        <i class="fas {{ $config['icon'] }} text-xs"></i>
                                        {{ $config['text'] }}
                                    </span>

                                            <!-- 课程进度 -->
                                            <div class="text-right">
                                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                                    <span>进度</span>
                                                    <span class="font-semibold text-gray-900">{{ $userCourse->progress_percent }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 进度条 -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between text-sm mb-1">
                                            <span class="text-gray-600">学习进度</span>
                                            <span class="font-medium text-gray-900">{{ $userCourse->progress_percent }}%</span>
                                        </div>
                                        <div class="progress h-2">
                                            <div class="progress-bar bg-gradient-to-r from-blue-500 to-purple-600"
                                                 style="width: {{ $userCourse->progress_percent }}%"></div>
                                        </div>
                                    </div>

                                    <!-- 课程元数据 -->
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-layer-group text-xs"></i>
                                    章节: {{ $userCourse->course->chapters_count ?? 0 }}
                                </span>
                                        <span class="flex items-center gap-1">
                                    <i class="fas fa-clock text-xs"></i>
                                    时长: {{ $userCourse->course->duration ?? '未设置' }}
                                </span>
                                        <span class="flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-xs"></i>
                                    最后学习: {{ $userCourse->last_studied_at ? $userCourse->last_studied_at->format('m-d') : '未开始' }}
                                </span>
                                    </div>
                                </div>

                                <!-- 课程操作 -->
                                <div class="lg:w-1/4 flex flex-col gap-3">
                                    <a href="{{ url('/courses/' . $userCourse->course->id) }}"
                                       class="btn btn-primary w-full justify-center">
                                        <i class="fas fa-play-circle mr-2"></i>
                                        {{ $userCourse->status === 'completed' ? '复习课程' : '继续学习' }}
                                    </a>

                                    <div class="flex gap-2">
                                        <button onclick="showCourseSettings({{ $userCourse->id }})"
                                                class="btn btn-outline flex-1">
                                            <i class="fas fa-cog"></i>
                                        </button>

                                        <button onclick="showProgressDetails({{ $userCourse->id }})"
                                                class="btn btn-outline flex-1">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>

                                        <button onclick="toggleCourseStatus({{ $userCourse->id }})"
                                                class="btn btn-outline flex-1">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>

                                    @if($userCourse->status === 'active')
                                        <div class="text-center">
                                            <small class="text-gray-500">
                                                <i class="fas fa-hourglass-half mr-1"></i>
                                                已学习 {{ $userCourse->study_minutes ?? 0 }} 分钟
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- 空状态 -->
                        <div class="text-center py-16">
                            <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-book-open text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">暂无课程</h3>
                            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                                您还没有加入任何课程，开始您的学习之旅吧！
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="{{ url('/courses') }}" class="btn btn-primary">
                                    <i class="fas fa-compass mr-2"></i>
                                    浏览课程
                                </a>
                                <a href="{{ url('/courses/explore') }}" class="btn btn-outline">
                                    <i class="fas fa-search mr-2"></i>
                                    探索推荐
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 学习统计和推荐 -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- 学习统计 -->
                <div class="lg:col-span-2">
                    <div class="card">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">学习统计</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">学习时间分布</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <div class="flex items-center justify-between text-sm mb-1">
                                                <span class="text-gray-600">本周</span>
                                                <span class="font-medium text-gray-900">4.5小时</span>
                                            </div>
                                            <div class="progress h-2">
                                                <div class="progress-bar" style="width: 60%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between text-sm mb-1">
                                                <span class="text-gray-600">本月</span>
                                                <span class="font-medium text-gray-900">18小时</span>
                                            </div>
                                            <div class="progress h-2">
                                                <div class="progress-bar" style="width: 75%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between text-sm mb-1">
                                                <span class="text-gray-600">累计</span>
                                                <span class="font-medium text-gray-900">125小时</span>
                                            </div>
                                            <div class="progress h-2">
                                                <div class="progress-bar" style="width: 90%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">学习习惯</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">平均每日学习</span>
                                            <span class="font-medium text-gray-900">45分钟</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">最佳学习时段</span>
                                            <span class="font-medium text-gray-900">19:00-21:00</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">专注度得分</span>
                                            <span class="font-medium text-gray-900">8.2/10</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">课程完成率</span>
                                            <span class="font-medium text-gray-900">68%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-1">学习建议</h4>
                                        <p class="text-sm text-gray-600">尝试在最佳学习时段集中学习，提高效率</p>
                                    </div>
                                    <button onclick="showStudyTips()" class="text-sm text-blue-600 hover:text-blue-800">
                                        查看更多
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 课程推荐 -->
                <div>
                    <div class="card">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">为您推荐</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-code text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-1">Python数据分析</h5>
                                        <p class="text-xs text-gray-500 mb-2">适合有Python基础的学习者</p>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">数据分析</span>
                                            <span class="text-xs text-gray-500">8小时课程</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-brain text-green-600"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-1">高效思维导图</h5>
                                        <p class="text-xs text-gray-500 mb-2">提升思考和组织能力</p>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">效率提升</span>
                                            <span class="text-xs text-gray-500">5小时课程</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-chart-line text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-1">时间管理大师</h5>
                                        <p class="text-xs text-gray-500 mb-2">GTD与番茄工作法实践</p>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">时间管理</span>
                                            <span class="text-xs text-gray-500">6小时课程</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <a href="{{ url('/courses/explore') }}" class="btn btn-outline w-full">
                                    <i class="fas fa-compass mr-2"></i>
                                    探索更多课程
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 课程设置模态框 -->
    <div id="course_settings_modal" class="modal">
        <div class="modal-content max-w-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">课程设置</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('course_settings_modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4" id="courseSettingsContent">
                <!-- 内容由JS动态加载 -->
            </div>
        </div>
    </div>

    <!-- 进度详情模态框 -->
    <div id="progress_modal" class="modal">
        <div class="modal-content max-w-2xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">学习进度详情</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('progress_modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="progressModalContent">
                <!-- 内容由JS动态加载 -->
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // 初始化筛选器
            const sortSelect = $('#sortCourses');
            const filterSelect = $('#filterStatus');

            sortSelect.change(function() {
                sortCourses($(this).val());
            });

            filterSelect.change(function() {
                filterCourses($(this).val());
            });

            // 初始化工具提示
            initTooltips();
        });

        // 排序课程
        function sortCourses(sortType) {
            const $cards = $('.course-card');
            const cardsArray = $cards.toArray();

            cardsArray.sort((a, b) => {
                const $a = $(a);
                const $b = $(b);

                switch(sortType) {
                    case 'progress_desc':
                        return parseFloat($b.data('progress')) - parseFloat($a.data('progress'));
                    case 'progress_asc':
                        return parseFloat($a.data('progress')) - parseFloat($b.data('progress'));
                    case 'name':
                        return $a.data('name').localeCompare($b.data('name'));
                    default: // recent
                        return 0; // 保持原顺序
                }
            });

            // 重新排序DOM
            const $container = $('.course-card').first().parent();
            cardsArray.forEach(card => {
                $container.append(card);
            });

            showNotification('已按' + sortSelect.find('option:selected').text() + '排序');
        }

        // 筛选课程
        function filterCourses(status) {
            const $cards = $('.course-card');

            $cards.each(function() {
                const $card = $(this);
                const cardStatus = $card.data('status');

                if (status === 'all' || cardStatus === status) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });

            const visibleCount = $cards.filter(':visible').length;
            if (visibleCount === 0) {
                showNotification('没有符合条件的课程', 'info');
            } else {
                showNotification(`显示了 ${visibleCount} 个课程`, 'success');
            }
        }

        // 显示课程设置
        function showCourseSettings(courseId) {
            // 这里可以添加AJAX请求加载设置
            showNotification('课程设置功能开发中', 'info');

            /*
            $.ajax({
                url: `/user-courses/${courseId}/settings`,
                type: 'GET',
                success: function(data) {
                    $('#courseSettingsContent').html(data);
                    $('#course_settings_modal').addClass('show');
                }
            });
            */
        }

        // 显示进度详情
        function showProgressDetails(courseId) {
            // 这里可以添加AJAX请求加载进度详情
            showNotification('进度详情功能开发中', 'info');

            /*
            $.ajax({
                url: `/user-courses/${courseId}/progress`,
                type: 'GET',
                success: function(data) {
                    $('#progressModalContent').html(data);
                    $('#progress_modal').addClass('show');
                }
            });
            */
        }

        // 切换课程状态
        function toggleCourseStatus(courseId) {
            if (!confirm('确定要更改课程状态吗？')) return;

            // 这里可以添加AJAX请求更改状态
            showNotification('状态更改功能开发中', 'info');

            /*
            $.ajax({
                url: `/user-courses/${courseId}/toggle-status`,
                type: 'POST',
                data: {_token: '{{ csrf_token() }}'},
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        }
    });
    */
        }

        // 显示学习建议
        function showStudyTips() {
            showNotification('学习建议功能开发中', 'info');
        }

        // 关闭模态框
        function closeModal(modalId) {
            $('#' + modalId).removeClass('show');
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

        // 初始化工具提示
        function initTooltips() {
            $('[title]').tooltip({
                placement: 'top',
                trigger: 'hover'
            });
        }

        // 搜索课程
        function searchCourses() {
            const searchTerm = $('#courseSearch').val().toLowerCase();
            const $cards = $('.course-card');

            $cards.each(function() {
                const $card = $(this);
                const courseName = $card.data('name');

                if (courseName.includes(searchTerm) || searchTerm === '') {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        }
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