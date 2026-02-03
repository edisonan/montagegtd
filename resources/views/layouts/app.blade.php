<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '蒙太奇 - 专注效率工具')</title>
    <meta name="description" content="@yield('description', '蒙太奇是一个专注于提升个人效率的时间管理工具，提供番茄工作法、待办事项、阅读管理等核心功能。')">
    <meta name="keywords" content="蒙太奇,番茄工作法,时间管理,待办事项,GTD,RSS阅读,效率工具">

    @if(strpos($_SERVER['REQUEST_URI'],'article') !== false)
        <meta name="referrer" content="never">
    @endif

    <!-- 引入Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- 引入Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- 引入谷歌字体 -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- 引入jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 引入ECharts -->
    <script src="{{ url('/js/echarts.min.js') }}" defer></script>

    <!-- 引入自定义CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="min-h-screen">

<!-- 顶部导航 -->
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center space-x-3 hover:opacity-90 transition">
                <img src="/favicon.ico" width="36" height="36" alt="蒙太奇" class="rounded-full">
                <h1 class="text-xl font-bold text-gray-900">蒙太奇</h1>
            </a>

            <!-- 桌面端导航 -->
            <div class="hidden md:flex items-center space-x-1">
                @php
                    $menuItems = [
                        [
                            'url'=>'/',
                            'label'=>'做番茄',
                            'icon'=>'fas fa-clock',
                            'submenu' => [
                                ['url'=>'/', 'label'=>'开始专注', 'icon'=>'fas fa-play'],
                                ['url'=>'/pomos', 'label'=>'番茄列表', 'icon'=>'fas fa-list'],
                                ['url'=>'/tasks', 'label'=>'待办列表', 'icon'=>'fas fa-tasks'],
                                ['url'=>'/things', 'label'=>'事情列表', 'icon'=>'fas fa-check-circle'],
                                ['url'=>'/cals', 'label'=>'日历/订阅', 'icon'=>'fas fa-calendar'],
                                ['url'=>'/dailysummarys', 'label'=>'日报记录', 'icon'=>'fas fa-newspaper'],
                                ['url'=>'/statistics', 'label'=>'数据统计', 'icon'=>'fas fa-chart-bar']
                            ]
                        ],
                        [
                            'url'=>'/notes',
                            'label'=>'记想法',
                            'icon'=>'fas fa-lightbulb',
                            'submenu' => [
                                ['url'=>'/notes', 'label'=>'新的想法', 'icon'=>'fas fa-plus'],
                                ['url'=>'/minds', 'label'=>'思维导图', 'icon'=>'fas fa-sitemap'],
                            ]
                        ],
                        [
                            'url'=>'/articles',
                            'label'=>'去阅读',
                            'icon'=>'fas fa-book-reader',
                            'submenu' => [
                                ['url'=>'/articles', 'label'=>'最新文章', 'icon'=>'fas fa-newspaper'],
                                ['url'=>'/articles?status=read_later', 'label'=>'稍后阅读', 'icon'=>'fas fa-bookmark'],
                                ['url'=>'/articles?status=star', 'label'=>'收藏文章', 'icon'=>'fas fa-star'],
                                ['url'=>'/feeds', 'label'=>'订阅管理', 'icon'=>'fas fa-rss'],
                                ['url'=>'/feeds/explorer', 'label'=>'探索发现', 'icon'=>'fas fa-compass'],
                            ]
                        ],
                        [
                            'url'=>'/courses',
                            'label'=>'学课程',
                            'icon'=>'fas fa-graduation-cap',
                            'submenu' => [
                                ['url'=>'/courses', 'label'=>'我的学习', 'icon'=>'fas fa-book'],
                                ['url'=>'/course/management', 'label'=>'课程管理', 'icon'=>'fas fa-cog']
                            ]
                        ],
                        [
                            'url'=>'/llm/index',
                            'label'=>'AI助手',
                            'icon'=>'fas fa-robot',
                            'submenu' => [
                                ['url'=>'/llm/index', 'label'=>'AI助手', 'icon'=>'fas fa-comments'],
                                ['url'=>'/llm/llmmanagement', 'label'=>'LLM管理', 'icon'=>'fas fa-cogs'],
                                ['url'=>'/llm/agentmanagement', 'label'=>'智能体管理', 'icon'=>'fas fa-user-robot'],
                            ]
                        ],
                    ];
                @endphp

                @foreach ($menuItems as $item)
                    <div class="dropdown relative">
                        <a href="{{ url($item['url']) }}" class="nav-link group">
                            <i class="{{ $item['icon'] }} text-gray-500 text-sm mr-2"></i>
                            <span class="font-medium">{{ $item['label'] }}</span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs ml-1"></i>
                        </a>

                        <div class="dropdown-menu hidden">
                            @foreach($item['submenu'] as $subitem)
                                <a href="{{ url($subitem['url']) }}" class="dropdown-item">
                                    <i class="{{ $subitem['icon'] }} text-gray-400 text-sm"></i>
                                    <span>{{ $subitem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 右侧用户区域 -->
            <div class="flex items-center space-x-4">
                <!-- 通知 -->
                <button class="relative p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bell"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                @if(Auth::guest())
                    <!-- 登录/注册 -->
                    <a href="{{ url('/login') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="hidden sm:inline">登录</span>
                    </a>
                @else
                    <!-- 用户菜单 -->
                    <div class="dropdown relative">
                        <button class="flex items-center space-x-3 focus:outline-none">
                            <div class="w-8 h-8 bg-gradient-to-br from-gray-700 to-gray-900 rounded-full flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </button>

                        <div class="dropdown-menu hidden" style="right: 0; left: auto;">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div class="font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            </div>

                            <a href="{{ url('points') }}" class="dropdown-item">
                                <i class="fas fa-medal text-yellow-500"></i>
                                <span>积分中心</span>
                            </a>
                            <a href="{{ url('achievements') }}" class="dropdown-item">
                                <i class="fas fa-trophy text-purple-500"></i>
                                <span>成就勋章</span>
                            </a>

                            <div class="border-t border-gray-200 my-1"></div>

                            <a href="{{ url('accounts') }}" class="dropdown-item">
                                <i class="fas fa-user-cog text-gray-500"></i>
                                <span>账号管理</span>
                            </a>
                            <a href="{{ url('/personal-access-tokens') }}" class="dropdown-item">
                                <i class="fas fa-key text-gray-500"></i>
                                <span>访问令牌</span>
                            </a>
                            <a href="{{ url('settings') }}" class="dropdown-item">
                                <i class="fas fa-cog text-gray-500"></i>
                                <span>平台设置</span>
                            </a>

                            <div class="border-t border-gray-200 my-1"></div>

                            <a href="{{ url('/help/feedback') }}" class="dropdown-item">
                                <i class="fas fa-comment-dots text-blue-500"></i>
                                <span>添加反馈</span>
                            </a>
                            <a href="{{ url('/about') }}" class="dropdown-item">
                                <i class="fas fa-info-circle text-green-500"></i>
                                <span>关于我们</span>
                            </a>

                            <div class="border-t border-gray-200 my-1"></div>

                            <a href="{{ url('/logout') }}" class="dropdown-item text-red-600">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>登出</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- 移动端菜单按钮 -->
                <button id="mobileMenuButton" class="md:hidden p-2 text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- 移动端导航菜单 -->
    <div id="mobileMenu" class="md:hidden bg-white border-t border-gray-200 hidden">
        <div class="px-4 py-3">
            @foreach ($menuItems as $index => $item)
                <div class="mb-2">
                    <div class="nav-link justify-between cursor-pointer mobile-menu-item" data-index="{{ $index }}">
                        <div class="flex items-center space-x-3">
                            <i class="{{ $item['icon'] }} text-gray-500 text-sm"></i>
                            <span class="font-medium">{{ $item['label'] }}</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-xs mobile-menu-icon"></i>
                    </div>

                    <div class="ml-8 mt-1 space-y-1 hidden" id="mobileSubmenu-{{ $index }}">
                        @foreach($item['submenu'] as $subitem)
                            <a href="{{ url($subitem['url']) }}" class="nav-link py-2 mobile-submenu-link">
                                <i class="{{ $subitem['icon'] }} text-gray-400 text-sm mr-3"></i>
                                <span>{{ $subitem['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(Auth::guest())
                <div class="mt-4">
                    <a href="{{ url('/login') }}" class="btn btn-outline w-full justify-center">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>登录/注册</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</nav>

<!-- 主内容区域 -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

<!-- 底部 -->
<footer class="mt-16 border-t border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-6 md:mb-0">
                <!-- Logo位置 -->
            </div>

            <div class="flex flex-wrap justify-center gap-4 text-sm text-gray-600">
                <a href="mailto:accacc@126.com?subject=蒙太奇反馈" class="hover:text-gray-900 hover:underline">
                    <i class="fas fa-envelope mr-1"></i>联系反馈
                </a>
                <a href="/help/feedback" class="hover:text-gray-900 hover:underline">
                    <i class="fas fa-comment-alt mr-1"></i>提交反馈
                </a>
                <a href="/about" class="hover:text-gray-900 hover:underline">
                    <i class="fas fa-info-circle mr-1"></i>关于我们
                </a>
                <a href="https://gitee.com/accacc/task#%E9%AB%98%E6%95%88%E4%BD%BF%E7%94%A8montage-gtd"
                   class="hover:text-gray-900 hover:underline" target="_blank">
                    <i class="fas fa-book mr-1"></i>使用技巧
                </a>
            </div>
        </div>

        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>&copy; 2016-{{ date('Y') }} Congcong.us · 专注提升个人效率的生产力工具</p>
        </div>
    </div>
</footer>

<!-- 引入自定义JavaScript -->
<script src="{{ asset('js/app.js') }}"></script>

<!-- 百度统计 -->
<script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?d99a2953a8d7b5c51e4c84811bcbc1db";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>

@yield('scripts')
</body>
</html>