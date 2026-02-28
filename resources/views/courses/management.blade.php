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
                            <span>公开课程: <span class="font-medium">{{ count($public_courses ?? []) }}</span></span>
                        </div>
                        @auth
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                <span>我创建的: <span class="font-medium">{{ count($user_created_courses ?? []) }}</span></span>
                            </div>
                        @endauth
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                            <span>已加入: <span class="font-medium">{{ count($user_course_ids ?? []) }}</span></span>
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
                            {{ count($user_created_courses ?? []) }}
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
                            {{ count($public_courses ?? []) }}
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
                            {{ count($user_course_ids ?? []) }}
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @forelse($public_courses as $course)
                                <div class="course-card group">
                                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-blue-300 hover:shadow-lg transition-all duration-200">
                                        <!-- 课程封面 -->
                                        <div class="relative h-48 overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200">
                                            @if($course->cover_image_url)
                                                <img src="{{ $course->cover_image_url }}"
                                                     alt="{{ $course->title }}"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <div class="text-center">
                                                        <i class="fas fa-book-open text-gray-400 text-4xl mb-2"></i>
                                                        <p class="text-sm text-gray-500">{{ $course->title }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- 难度标签 -->
                                            <div class="absolute top-3 right-3">
                                                @php
                                                    $difficultyConfig = [
                                                        'beginner' => ['color' => 'green', 'text' => '初级'],
                                                        'intermediate' => ['color' => 'yellow', 'text' => '中级'],
                                                        'advanced' => ['color' => 'red', 'text' => '高级'],
                                                    ];
                                                    $config = $difficultyConfig[$course->difficulty] ?? ['color' => 'gray', 'text' => '未知'];
                                                @endphp
                                                <span class="px-3 py-1 bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 text-xs font-semibold rounded-full">
                                            {{ $config['text'] }}
                                        </span>
                                            </div>

                                            <!-- 平台标识 -->
                                            @if($course->platform)
                                                <div class="absolute top-3 left-3">
                                        <span class="px-2 py-1 bg-gray-900/70 text-white text-xs rounded">
                                            {{ $course->platform }}
                                        </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- 课程内容 -->
                                        <div class="p-5">
                                            <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">
                                                {{ $course->title }}
                                            </h3>

                                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">
                                                {{ $course->description ?: '暂无描述' }}
                                            </p>

                                            <!-- 课程元数据 -->
                                            <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-user text-xs"></i>
                                            {{ $course->instructor ?: '未知讲师' }}
                                        </span>
                                                <span class="flex items-center gap-1">
                                            <i class="fas fa-clock text-xs"></i>
                                            {{ $course->estimated_hours ?: 0 }}小时
                                        </span>
                                                <span class="flex items-center gap-1">
                                            <i class="fas fa-layer-group text-xs"></i>
                                            {{ $course->chapters_count ?? 0 }}章
                                        </span>
                                            </div>

                                            <!-- 操作按钮 -->
                                            <div class="flex items-center justify-between">
                                                <a href="{{ url('/courses/' . $course->id) }}"
                                                   class="btn btn-primary btn-sm">
                                                    查看详情
                                                </a>

                                                <div class="flex items-center gap-2">
                                                    @if(!auth()->guest() && !in_array($course->id, $user_course_ids ?? []))
                                                        <form action="{{ url('/courses/' . $course->id . '/join') }}"
                                                              method="POST"
                                                              class="inline">
                                                            {!! csrf_field() !!}
                                                            <button type="submit"
                                                                    class="btn btn-success btn-sm hover:shadow-md transition-shadow">
                                                                加入课程
                                                            </button>
                                                        </form>
                                                    @elseif(auth()->id() == $course->created_by)
                                                        <span class="px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                创建的课程
                                            </span>
                                                    @elseif(in_array($course->id, $user_course_ids ?? []))
                                                        <span class="px-3 py-1.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                已加入
                                            </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <!-- 空状态 -->
                                <div class="col-span-full">
                                    <div class="text-center py-16">
                                        <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-graduation-cap text-gray-400 text-3xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-3">暂无公开课程</h3>
                                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                                            暂时还没有公开课程，您可以创建第一个课程
                                        </p>
                                        @auth
                                            <a href="{{ url('/courses/create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                创建课程
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- 我创建的课程标签页 -->
                    @auth
                        <div class="tab-pane active hidden"
                             id="my-courses"
                             role="tabpanel">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @forelse($user_created_courses as $course)
                                    <div class="course-card group">
                                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-green-300 hover:shadow-lg transition-all duration-200">
                                            <!-- 课程封面 -->
                                            <div class="relative h-48 overflow-hidden bg-gradient-to-br from-green-50 to-green-100">
                                                @if($course->cover_image_url)
                                                    <img src="{{ $course->cover_image_url }}"
                                                         alt="{{ $course->title }}"
                                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <div class="text-center">
                                                            <i class="fas fa-user-edit text-green-400 text-4xl mb-2"></i>
                                                            <p class="text-sm text-gray-500">{{ $course->title }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- 状态标签 -->
                                                <div class="absolute top-3 right-3">
                                                    @php
                                                        $statusConfig = [
                                                            1 => ['color' => 'gray', 'icon' => 'fa-lock', 'text' => '私有'],
                                                            2 => ['color' => 'yellow', 'icon' => 'fa-clock', 'text' => '待审核'],
                                                            3 => ['color' => 'green', 'icon' => 'fa-check-circle', 'text' => '已审核'],
                                                        ];
                                                        $config = $statusConfig[$course->public_status] ?? ['color' => 'gray', 'icon' => 'fa-question-circle', 'text' => '未知'];
                                                    @endphp
                                                    <span class="px-3 py-1 bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 text-xs font-semibold rounded-full flex items-center gap-1">
                                            <i class="fas {{ $config['icon'] }} text-xs"></i>
                                            {{ $config['text'] }}
                                        </span>
                                                </div>

                                                <!-- 编辑按钮 -->
                                                <div class="absolute bottom-3 right-3">
                                                    <a href="{{ url('/courses/' . $course->id . '/edit') }}"
                                                       class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-shadow"
                                                       title="编辑课程">
                                                        <i class="fas fa-edit text-gray-600 text-sm"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- 课程内容 -->
                                            <div class="p-5">
                                                <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">
                                                    {{ $course->title }}
                                                </h3>

                                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">
                                                    {{ $course->description ?: '暂无描述' }}
                                                </p>

                                                <!-- 课程元数据 -->
                                                <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-chart-line text-xs"></i>
                                            学习人数: {{ $course->enrollment_count ?? 0 }}
                                        </span>
                                                    <span class="flex items-center gap-1">
                                            <i class="fas fa-clock text-xs"></i>
                                            {{ $course->estimated_hours ?: 0 }}小时
                                        </span>
                                                    <span class="flex items-center gap-1">
                                            <i class="fas fa-calendar text-xs"></i>
                                            {{ $course->created_at->format('Y-m-d') }}
                                        </span>
                                                </div>

                                                <!-- 操作按钮 -->
                                                <div class="flex items-center justify-between">
                                                    <a href="{{ url('/courses/' . $course->id) }}"
                                                       class="btn btn-primary btn-sm">
                                                        查看详情
                                                    </a>

                                                    <div class="flex items-center gap-2">
                                                        <button onclick="showCourseStats({{ $course->id }})"
                                                                class="btn btn-outline btn-sm">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </button>
                                                        <button onclick="manageCourse({{ $course->id }})"
                                                                class="btn btn-outline btn-sm">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <!-- 空状态 -->
                                    <div class="col-span-full">
                                        <div class="text-center py-16">
                                            <div class="w-20 h-20 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user-edit text-green-400 text-3xl"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-3">您还没有创建课程</h3>
                                            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                                                创建您的第一个课程，分享知识和经验
                                            </p>
                                            <a href="{{ url('/courses/create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                创建课程
                                            </a>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- 已加入课程标签页 -->
                        <div class="tab-pane hidden"
                             id="joined-courses"
                             role="tabpanel">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @php
                                    $joinedCourses = collect($public_courses)->filter(function($course) use ($user_course_ids) {
                                        return in_array($course->id, $user_course_ids ?? []);
                                    });
                                @endphp

                                @forelse($joinedCourses as $course)
                                    <div class="course-card group">
                                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-purple-300 hover:shadow-lg transition-all duration-200">
                                            <!-- 课程封面 -->
                                            <div class="relative h-48 overflow-hidden bg-gradient-to-br from-purple-50 to-purple-100">
                                                @if($course->cover_image_url)
                                                    <img src="{{ $course->cover_image_url }}"
                                                         alt="{{ $course->title }}"
                                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <div class="text-center">
                                                            <i class="fas fa-bookmark text-purple-400 text-4xl mb-2"></i>
                                                            <p class="text-sm text-gray-500">{{ $course->title }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- 进度标签 -->
                                                <div class="absolute top-3 right-3">
                                                    @php
                                                        $progress = $course->user_progress ?? 0;
                                                        $progressColor = $progress >= 80 ? 'green' : ($progress >= 50 ? 'blue' : 'yellow');
                                                    @endphp
                                                    <span class="px-3 py-1 bg-{{ $progressColor }}-100 text-{{ $progressColor }}-800 text-xs font-semibold rounded-full">
                                            {{ $progress }}%
                                        </span>
                                                </div>
                                            </div>

                                            <!-- 课程内容 -->
                                            <div class="p-5">
                                                <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-1">
                                                    {{ $course->title }}
                                                </h3>

                                                <!-- 进度条 -->
                                                <div class="mb-4">
                                                    <div class="flex items-center justify-between text-sm mb-1">
                                                        <span class="text-gray-600">学习进度</span>
                                                        <span class="font-medium text-gray-900">{{ $progress }}%</span>
                                                    </div>
                                                    <div class="progress h-2">
                                                        <div class="progress-bar bg-gradient-to-r from-purple-500 to-purple-600"
                                                             style="width: {{ $progress }}%"></div>
                                                    </div>
                                                </div>

                                                <!-- 课程元数据 -->
                                                <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-user text-xs"></i>
                                            {{ $course->instructor ?: '未知讲师' }}
                                        </span>
                                                    <span class="flex items-center gap-1">
                                            <i class="fas fa-clock text-xs"></i>
                                            已学 {{ $course->study_minutes ?? 0 }}分钟
                                        </span>
                                                    <span class="flex items-center gap-1">
                                            <i class="fas fa-calendar text-xs"></i>
                                            上次学习: {{ $course->last_studied_at ? $course->last_studied_at->format('m-d') : '未开始' }}
                                        </span>
                                                </div>

                                                <!-- 操作按钮 -->
                                                <div class="flex items-center justify-between">
                                                    <a href="{{ url('/courses/' . $course->id) }}"
                                                       class="btn btn-primary btn-sm">
                                                        {{ $progress >= 100 ? '复习课程' : '继续学习' }}
                                                    </a>

                                                    <button onclick="showCourseProgress({{ $course->id }})"
                                                            class="btn btn-outline btn-sm">
                                                        <i class="fas fa-chart-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <!-- 空状态 -->
                                    <div class="col-span-full">
                                        <div class="text-center py-16">
                                            <div class="w-20 h-20 mx-auto mb-6 bg-purple-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-bookmark text-purple-400 text-3xl"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-3">您还没有加入课程</h3>
                                            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                                                浏览公开课程，选择您感兴趣的加入学习
                                            </p>
                                            <button onclick="switchTab('public-courses')" class="btn btn-primary">
                                                <i class="fas fa-compass mr-2"></i>
                                                浏览课程
                                            </button>
                                        </div>
                                    </div>
                                @endforelse
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
        $(document).ready(function() {
            // 初始化标签页切换
            initTabSwitching();

            // 初始化搜索功能
            initSearch();

            // 初始化筛选功能
            initFiltering();

            // 初始化排序功能
            initSorting();
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
                    const difficultyBadge = card.querySelector('[class*="bg-"]').textContent;
                    if (!difficultyBadge.includes(filters.difficulty)) {
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
            const container = document.getElementById(tabId);
            const courseCards = Array.from(container.querySelectorAll('.course-card'));

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
                container.appendChild(card);
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