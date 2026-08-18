<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '蒙太奇 - 专注效率工具')</title>
    <meta name="description" content="@yield('description', '蒙太奇是一个专注于提升个人效率的时间管理工具，提供专注工作法、待办事项、阅读管理等核心功能。')">
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

    <!-- 引入jQuery（本地优先，CDN不可用时自动回退，避免网络问题导致全站JS失效） -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');</script>

    <!-- 引入SweetAlert2（本地优先，删除/确认弹窗依赖） -->
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script>window.Swal || document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');</script>

    <!-- 提前加载API客户端，避免内容区内联脚本执行时未初始化 -->
    <script src="{{ asset('js/hybrid-api-client.js') }}"></script>

    <!-- 引入ECharts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js" defer></script>

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

            <!-- 桌面端导航 - 现在在右侧 -->
            <div class="hidden md:flex items-center">
                <!-- 将菜单项和用户区域放在同一个容器中 -->
                <div class="flex items-center space-x-1">
                    @php
                        $menuItems = [
                            [
                                'url'=>'/',
                                'label'=>'专注',
                                'icon'=>'fas fa-clock',
                                'submenu' => [
                                    ['url'=>'/', 'label'=>'开始专注', 'icon'=>'fas fa-play'],
//                                    ['url'=>'/focuss', 'label'=>'专注列表', 'icon'=>'fas fa-list'],
                                    ['url'=>'/tasks', 'label'=>'待办列表', 'icon'=>'fas fa-tasks'],
                                    ['url'=>'/journals', 'label'=>'手账列表', 'icon'=>'fas fa-check-circle']
                                ]
                            ],
                            [
                                'url'=>'/notes',
                                'label'=>'想法',
                                'icon'=>'fas fa-lightbulb',
                                'submenu' => [
                                    ['url'=>'/notes', 'label'=>'新的想法', 'icon'=>'fas fa-plus'],
                                    ['url'=>'/notes/manage', 'label'=>'笔记管理', 'icon'=>'fas fa-book'],
                                    ['url'=>'/minds', 'label'=>'思维导图', 'icon'=>'fas fa-sitemap'],
                                ]
                            ],
                            [
                                'url'=>'/articles',
                                'label'=>'阅读',
                                'icon'=>'fas fa-book-reader',
                                'submenu' => [
                                    ['url'=>'/articles', 'label'=>'最新文章', 'icon'=>'fas fa-newspaper'],
                                    ['url'=>'/articles/explorer', 'label'=>'探索阅读', 'icon'=>'fas fa-columns'],
                                    ['url'=>'/articles/workbench', 'label'=>'文章总览', 'icon'=>'fas fa-sliders-h'],
                                    ['url'=>'/articles?status=read_later', 'label'=>'稍后阅读', 'icon'=>'fas fa-bookmark'],
                                    ['url'=>'/articles?status=star', 'label'=>'收藏文章', 'icon'=>'fas fa-star'],
                                    ['url'=>'/feeds', 'label'=>'订阅管理', 'icon'=>'fas fa-rss'],
                                    ['url'=>'/feeds/explorer', 'label'=>'探索发现', 'icon'=>'fas fa-compass'],
                                ]
                            ],
                            [
                                'url'=>'/study',
                                'label'=>'学习',
                                'icon'=>'fas fa-graduation-cap',
                                'submenu' => [
                                    ['url'=>'/study', 'label'=>'学习计划', 'icon'=>'fas fa-calendar-alt'],
                                    ['url'=>'/courses', 'label'=>'我的学习', 'icon'=>'fas fa-book'],
                                    ['url'=>'/course/management', 'label'=>'课程管理', 'icon'=>'fas fa-cog']
                                ]
                            ],
                            [
                                'url'=>'/llm/index',
                                'label'=>'助手',
                                'icon'=>'fas fa-comments',
                                'submenu' => [
                                    ['url'=>'/llm/index', 'label'=>'智能助手', 'icon'=>'fas fa-comments'],
                                    ['url'=>'/llm/llmmanagement', 'label'=>'模型管理', 'icon'=>'fas fa-cogs'],
                                    ['url'=>'/llm/agentmanagement', 'label'=>'智能体管理', 'icon'=>'fas fa-robot'],
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
                <div class="flex items-center space-x-4 ml-6">
                    <!-- 通知 -->
                    <div class="dropdown relative" id="userNotificationDropdown">
                        <button id="userNotificationButton" class="relative p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 focus:outline-none">
                            <i class="fas fa-bell"></i>
                            <span id="userNotificationBadge" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] leading-[18px] text-center hidden">0</span>
                        </button>
                        <div id="userNotificationMenu" class="dropdown-menu hidden" style="right: 0; left: auto; min-width: 320px;">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-900">站内通知</div>
                                <button id="markAllNotificationsReadBtn" type="button" class="text-xs text-[#00b894] hover:underline">全部已读</button>
                            </div>
                            <div id="userNotificationList" class="max-h-80 overflow-y-auto">
                                <div class="px-4 py-6 text-sm text-gray-500 text-center">暂无通知</div>
                            </div>
                        </div>
                    </div>

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

                                <a href="{{ url('cals') }}" class="dropdown-item">
                                    <i class="fas fa-medal text-yellow-500"></i>
                                    <span>日历/订阅</span>
                                </a>
                                <a href="{{ url('dailysummarys') }}" class="dropdown-item">
                                    <i class="fas fa-store text-emerald-500"></i>
                                    <span>日报记录</span>
                                </a>
                                <a href="{{ url('satistics') }}" class="dropdown-item">
                                    <i class="fas fa-trophy text-purple-500"></i>
                                    <span>数据统计</span>
                                </a>

                                <div class="border-t border-gray-200 my-1"></div>

                                <a href="{{ url('points') }}" class="dropdown-item">
                                    <i class="fas fa-medal text-yellow-500"></i>
                                    <span>积分中心</span>
                                </a>
                                <a href="{{ url('point-mall') }}" class="dropdown-item">
                                    <i class="fas fa-store text-emerald-500"></i>
                                    <span>积分商城</span>
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

                                <a href="{{ url('/logout') }}" class="dropdown-item text-red-600" id="logoutLink">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>登出</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 移动端菜单按钮 -->
            <button id="mobileMenuButton" class="md:hidden p-2 text-gray-600 hover:text-gray-900">
                <i class="fas fa-bars text-xl"></i>
            </button>
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
@if(!Auth::guest())
<script>
    window.__TASK_ALLOW_LEGACY_FALLBACK__ = false;
    window.__TASK_FORCE_API__ = true;
    window.__taskBootstrapAccessToken = function() {
        if (typeof fetch !== 'function') {
            console.warn('task bootstrap skipped: fetch is unavailable');
            window.__TASK_FORCE_API__ = false;
            return Promise.resolve(null);
        }
        var tokenNode = document.head.querySelector('meta[name="csrf-token"]');
        var csrfToken = tokenNode ? tokenNode.content : '';

        return fetch('/api/v2/auth/bootstrap-session', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }).then(function(resp) {
            return resp.json().then(function(data) {
                if (!resp.ok || !data || !data.result || !data.result.access_token || !data.result.refresh_token) {
                    throw new Error('token bootstrap failed');
                }
                if (window.TaskApiClient && typeof window.TaskApiClient.setTokenPair === 'function') {
                    window.TaskApiClient.setTokenPair(data.result);
                }
                return data.result;
            });
        }).catch(function(err) {
            console.error('token bootstrap error', err);
            window.__TASK_FORCE_API__ = false;
            return null;
        });
    };

    try {
        window.__taskTokenBootstrapPromise = window.__taskBootstrapAccessToken();
    } catch (bootstrapErr) {
        console.error('token bootstrap init error', bootstrapErr);
        window.__TASK_FORCE_API__ = false;
        window.__taskTokenBootstrapPromise = Promise.resolve(null);
    }

    window.taskApiFetch = function(url, options) {
        if (typeof fetch !== 'function') {
            return Promise.reject(new Error('fetch is unavailable in this browser'));
        }
        var opts = options ? Object.assign({}, options) : {};
        var originalHeaders = (opts.headers && typeof opts.headers === 'object') ? opts.headers : {};
        var baseHeaders = Object.assign({}, originalHeaders);

        if (!baseHeaders['X-Requested-With']) {
            baseHeaders['X-Requested-With'] = 'XMLHttpRequest';
        }
        if (!baseHeaders['Accept']) {
            baseHeaders['Accept'] = 'application/json';
        }

        var csrfNode = document.head.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfNode ? csrfNode.content : '';
        if (csrfToken && !baseHeaders['X-CSRF-TOKEN']) {
            baseHeaders['X-CSRF-TOKEN'] = csrfToken;
        }

        if (!opts.credentials) {
            opts.credentials = 'same-origin';
        }

        function getAccessToken() {
            try {
                if (window.TaskApiClient && typeof window.TaskApiClient.getAccessToken === 'function') {
                    return window.TaskApiClient.getAccessToken() || '';
                }
            } catch (e) {}
            return '';
        }

        function getRefreshToken() {
            try {
                if (window.TaskApiClient && typeof window.TaskApiClient.getRefreshToken === 'function') {
                    return window.TaskApiClient.getRefreshToken() || '';
                }
            } catch (e) {}
            return '';
        }

        function buildRequestHeaders() {
            var headers = Object.assign({}, baseHeaders);
            var accessToken = getAccessToken();
            if (accessToken && !headers['Authorization']) {
                headers['Authorization'] = 'Bearer ' + accessToken;
            }
            return headers;
        }

        function doFetch() {
            var fetchOpts = Object.assign({}, opts);
            fetchOpts.headers = buildRequestHeaders();
            return fetch(url, fetchOpts);
        }

        function refreshAccessToken() {
            var refreshToken = getRefreshToken();
            if (!refreshToken) {
                return Promise.resolve(false);
            }

            return fetch('/api/v2/auth/refresh', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ refresh_token: refreshToken })
            }).then(function(resp) {
                return resp.json().then(function(data) {
                    if (!resp.ok || !data || !data.result || !data.result.access_token || !data.result.refresh_token) {
                        return false;
                    }
                    if (window.TaskApiClient && typeof window.TaskApiClient.setTokenPair === 'function') {
                        window.TaskApiClient.setTokenPair(data.result);
                    }
                    return true;
                }).catch(function() {
                    return false;
                });
            }).catch(function() {
                return false;
            });
        }

        var bootstrapPromise = (window.__taskTokenBootstrapPromise && typeof window.__taskTokenBootstrapPromise.then === 'function')
            ? window.__taskTokenBootstrapPromise
            : Promise.resolve();

        return bootstrapPromise
            .catch(function() {})
            .then(function() {
                if (getAccessToken()) {
                    return true;
                }
                if (typeof window.__taskBootstrapAccessToken === 'function') {
                    return window.__taskBootstrapAccessToken().then(function(result) {
                        return !!result;
                    }).catch(function() {
                        return false;
                    });
                }
                return false;
            })
            .then(function() {
                return doFetch();
            })
            .then(function(resp) {
                var needTokenRetry = resp && resp.status === 401 && /^\/api\/v2\//.test(url || '');
                if (!needTokenRetry) {
                    return resp;
                }

                return refreshAccessToken().then(function(refreshed) {
                    if (refreshed) {
                        return doFetch();
                    }
                    if (typeof window.__taskBootstrapAccessToken === 'function') {
                        return window.__taskBootstrapAccessToken().then(function(result) {
                            if (result) {
                                return doFetch();
                            }
                            return resp;
                        }).catch(function() {
                            return resp;
                        });
                    }
                    return resp;
                });
            })
            .catch(function(err) {
                if (err && err.message) {
                    console.error('taskApiFetch error:', err.message);
                } else {
                    console.error('taskApiFetch error:', err);
                }
                throw err;
            });
    };

    (function() {
        window.addEventListener('focus', function() {
            var accessToken = '';
            try {
                accessToken = (window.TaskApiClient && typeof window.TaskApiClient.getAccessToken === 'function')
                    ? (window.TaskApiClient.getAccessToken() || '')
                    : '';
            } catch (e) {
                accessToken = '';
            }
            if (!accessToken && typeof window.__taskBootstrapAccessToken === 'function') {
                window.__taskTokenBootstrapPromise = window.__taskBootstrapAccessToken();
            }
        });
    })();

    (function() {
        var badgeEl = document.getElementById('userNotificationBadge');
        var listEl = document.getElementById('userNotificationList');
        var markAllBtn = document.getElementById('markAllNotificationsReadBtn');
        if (!badgeEl || !listEl) {
            return;
        }

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function updateBadge(unreadCount) {
            var count = Number(unreadCount || 0);
            if (count > 0) {
                badgeEl.textContent = count > 99 ? '99+' : String(count);
                badgeEl.classList.remove('hidden');
            } else {
                badgeEl.textContent = '0';
                badgeEl.classList.add('hidden');
            }
        }

        function renderList(items) {
            if (!Array.isArray(items) || items.length === 0) {
                listEl.innerHTML = '<div class="px-4 py-6 text-sm text-gray-500 text-center">暂无通知</div>';
                return;
            }

            listEl.innerHTML = items.map(function(item) {
                var isRead = !!item.read_at;
                var readClass = isRead ? 'bg-white' : 'bg-emerald-50';
                var readDot = isRead ? '' : '<span class="w-2 h-2 bg-emerald-500 rounded-full inline-block mr-2"></span>';
                var createdAt = item.created_at ? String(item.created_at).replace('T', ' ').slice(0, 19) : '';
                var title = escapeHtml(item.title || '系统通知');
                var content = escapeHtml(item.content || '');

                return ''
                    + '<button type="button" class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition '
                    + readClass
                    + '" data-notification-id="' + Number(item.id || 0) + '" data-read="' + (isRead ? '1' : '0') + '">'
                    + '<div class="text-sm text-gray-900 font-medium flex items-center">' + readDot + title + '</div>'
                    + '<div class="text-xs text-gray-600 mt-1">' + content + '</div>'
                    + '<div class="text-[11px] text-gray-400 mt-2">' + escapeHtml(createdAt) + '</div>'
                    + '</button>';
            }).join('');
        }

        function loadNotifications() {
            return window.taskApiFetch('/api/v2/notifications?limit=20', {
                method: 'GET'
            }).then(function(resp) {
                return resp.json().then(function(data) {
                    if (!resp.ok || !data || !data.result) {
                        return;
                    }
                    renderList(data.result.list || []);
                    updateBadge(data.result.unread_count || 0);
                });
            }).catch(function() {});
        }

        listEl.addEventListener('click', function(e) {
            var item = e.target.closest('[data-notification-id]');
            if (!item) {
                return;
            }
            var isRead = item.getAttribute('data-read') === '1';
            if (isRead) {
                return;
            }
            var id = Number(item.getAttribute('data-notification-id') || 0);
            if (!id) {
                return;
            }

            window.taskApiFetch('/api/v2/notifications/' + id + '/read', {
                method: 'POST'
            }).then(function() {
                return loadNotifications();
            }).catch(function() {});
        });

        if (markAllBtn) {
            markAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.taskApiFetch('/api/v2/notifications/read-all', {
                    method: 'POST'
                }).then(function() {
                    return loadNotifications();
                }).catch(function() {});
            });
        }

        loadNotifications();
        window.setInterval(loadNotifications, 30000);
    })();

    (function() {
        var logoutLink = document.getElementById('logoutLink');
        if (!logoutLink) {
            return;
        }

        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            var webLogoutUrl = logoutLink.getAttribute('href') || '/logout';

            var doWebLogout = function() {
                window.location.href = webLogoutUrl;
            };

            if (!window.TaskApiClient || typeof window.TaskApiClient.logout !== 'function') {
                doWebLogout();
                return;
            }

            window.TaskApiClient.logout()
                .catch(function() {
                    if (typeof window.TaskApiClient.clearTokenPair === 'function') {
                        window.TaskApiClient.clearTokenPair();
                    }
                })
                .finally(function() {
                    doWebLogout();
                });
        });
    })();
</script>
@endif

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
