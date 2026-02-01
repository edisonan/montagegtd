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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ url('/js/echarts.min.js') }}" defer></script>


    <style>
        :root {
            /* 调整为更柔和的蓝色和紫色 */
            --primary-color: #4a90e2;    /* 柔和的蓝色 */
            --secondary-color: #8a6cff;  /* 柔和的紫色 */
            /* 原色对比：#4a90e2, #8a6cff */

            --success-color: #27ae60;    /* 稍微柔和的绿色 */
            --warning-color: #f39c12;    /* 稍微柔和的橙色 */
            --danger-color: #e74c3c;     /* 稍微柔和的红色 */

            /* 灰度系统保持不变 */
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* 蒙太奇设计系统 - 简洁专业版 */

        /* 1. 卡片组件规范 */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card-elevated {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* 2. 按钮组件规范 */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 8px;
        }

        /*.btn-primary {*/
        /*    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));*/
        /*    color: white;*/
        /*}*/
        /* 主按钮渐变调整为柔和版 */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }



        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
        }

        /*.btn-outline {*/
        /*    background: transparent;*/
        /*    color: var(--primary-color);*/
        /*    border: 2px solid var(--primary-color);*/
        /*}*/
        /* 轮廓按钮调整为柔和版 */
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: rgba(74, 144, 226, 0.05);
        }

        /*.btn-outline:hover {*/
        /*    background: rgba(59, 130, 246, 0.1);*/
        /*}*/

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* 3. 输入框规范 */
        .input {
            padding: 10px 14px;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            background: white;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* 4. 标签/徽章规范 */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-primary {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        /* 5. 进度条规范 */
        .progress {
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
        }

        /*.progress-bar {*/
        /*    height: 100%;*/
        /*    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));*/
        /*    border-radius: 3px;*/
        /*    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);*/
        /*}*/

        /* 进度条渐变调整为柔和版 */
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        /* 6. 表格规范 */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--gray-50);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
        }

        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-200);
        }

        /* 7. 列表规范 */
        .list {
            list-style: none;
            padding: 0;
        }

        .list-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-200);
            transition: background 0.2s ease;
        }

        .list-item:hover {
            background: var(--gray-50);
        }

        /* 8. 导航链接规范 */

        a {
            color: var(--gray-700);
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: var(--gray-100);
            color: var(--gray-800);
        }

        /*.nav-link.active {*/
        /*    background: rgba(59, 130, 246, 0.1);*/
        /*    color: var(--primary-color);*/
        /*}*/

        /* 导航链接保持原有样式，但调整激活状态颜色 */
        .nav-link.active {
            background: rgba(74, 144, 226, 0.08); /* 更柔和的蓝色背景 */
            color: var(--primary-color);
        }

        /* 9. 下拉菜单规范 */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            min-width: 200px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: var(--gray-700);
            text-decoration: none;
            transition: background 0.2s ease;
            gap: 10px;
        }

        .dropdown-item:hover {
            background: var(--gray-50);
        }

        /* 10. 模态框规范 */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        /* 11. 动画规范 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* 12. 自定义滚动条 */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) var(--gray-100);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }

        /* 13. 响应式断点 */
        @media (max-width: 768px) {
            .hidden-mobile {
                display: none !important;
            }
        }
    </style>
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
                                ['url'=>'/notes', 'label'=>'新想法', 'icon'=>'fas fa-plus'],
                                ['url'=>'/notes', 'label'=>'想法列表', 'icon'=>'fas fa-list'],
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
                            'url'=>'/minds',
                            'label'=>'绘导图',
                            'icon'=>'fas fa-project-diagram',
                            'submenu' => [
                                ['url'=>'/minds', 'label'=>'思维导图', 'icon'=>'fas fa-sitemap'],
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
            @foreach ($menuItems as $item)
                <div class="mb-2">
                    <a href="{{ url($item['url']) }}" class="nav-link justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="{{ $item['icon'] }} text-gray-500 text-sm"></i>
                            <span class="font-medium">{{ $item['label'] }}</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    </a>

                    <div class="ml-8 mt-1 space-y-1 hidden" id="mobileSubmenu-{{ $loop->index }}">
                        @foreach($item['submenu'] as $subitem)
                            <a href="{{ url($subitem['url']) }}" class="nav-link py-2">
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

<script>
    // 下拉菜单功能
    document.addEventListener('DOMContentLoaded', function() {
        // 桌面端下拉菜单
        const dropdowns = document.querySelectorAll('.dropdown');

        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('a, button');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (button && menu) {
                // 鼠标悬停显示菜单
                dropdown.addEventListener('mouseenter', () => {
                    menu.classList.remove('hidden');
                    setTimeout(() => {
                        menu.classList.add('show');
                    }, 10);
                });

                dropdown.addEventListener('mouseleave', () => {
                    menu.classList.remove('show');
                    setTimeout(() => {
                        menu.classList.add('hidden');
                    }, 200);
                });

                // 点击按钮跳转
                if (button.tagName === 'A') {
                    button.addEventListener('click', (e) => {
                        if (window.innerWidth >= 768) { // 只在桌面端跳转
                            e.preventDefault();
                            e.stopPropagation();
                            window.location.href = button.getAttribute('href');
                        }
                    });
                }
            }
        });

        // 移动端菜单切换
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // 移动端子菜单切换
            const mobileNavLinks = mobileMenu.querySelectorAll('.nav-link');
            mobileNavLinks.forEach((link, index) => {
                link.addEventListener('click', (e) => {
                    if (link.querySelector('.fa-chevron-right')) {
                        e.preventDefault();
                        const submenuId = 'mobileSubmenu-' + index;
                        const submenu = document.getElementById(submenuId);
                        if (submenu) {
                            submenu.classList.toggle('hidden');

                            const icon = link.querySelector('.fa-chevron-right');
                            if (icon) {
                                icon.classList.toggle('rotate-90');
                            }
                        }
                    }
                });
            });
        }

        // 点击其他地方关闭所有下拉菜单
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                    setTimeout(() => {
                        menu.classList.add('hidden');
                    }, 200);
                });
            }
        });

        // ESC键关闭下拉菜单
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                    setTimeout(() => {
                        menu.classList.add('hidden');
                    }, 200);
                });
            }
        });

        // CSRF令牌设置
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
</script>

<!-- 主内容区域 -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

<!-- 底部 -->
<footer class="mt-16 border-t border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-6 md:mb-0">
{{--                <div class="flex items-center space-x-3">--}}
{{--                    <img src="/favicon.ico" width="32" height="32" alt="蒙太奇" class="rounded-full">--}}
{{--                    <span class="text-lg font-bold text-gray-900">蒙太奇</span>--}}
{{--                </div>--}}
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