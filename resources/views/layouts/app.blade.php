<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title','蒙太奇 - 知而善用，行稳致远')</title>
    <meta name="description" content="@yield('description')">
    <meta name="keywords" content="蒙太奇,番茄工作法,待办事项,推送到kindle,RSS阅读,知乎日报订阅">
    @if(strpos($_SERVER['REQUEST_URI'],'article') !== false)
        <meta name="referrer" content="never">
    @endif

    <!-- Fonts -->
    <link href="/fonts/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet" type="text/css">
    <link href="/css/woff.css" rel="stylesheet" type="text/css">
    {{--<link rel="stylesheet" href="/css/bootstrap-icons.css">--}}

    <!-- Bootstrap -->
    <link rel="stylesheet" href="/css/bootstrap.min.css" crossorigin="anonymous">

    <style>
        body {
            font-family: 'Noto Sans', 'Helvetica Neue', Arial, sans-serif;
            color: #4a4a4a;
            background-color: #fdfdfd;
        }
        a { color: inherit; text-decoration: none; transition: color 0.2s; }
        a:hover { color: #429c4e; }

        .navbar { background: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .navbar-brand img { border-radius: 50%; transition: transform 0.2s; }
        .navbar-brand img:hover { transform: scale(1.1); }

        .navbar-nav .nav-link {
            font-weight: 500;
            margin-right: 0.8rem;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .navbar-nav .nav-link:hover { background: rgba(66, 156, 78, 0.1); color: #429c4e; }
        
        .menu-item {
            cursor: pointer;
        }

        .navbar-nav .dropdown-menu { 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            min-width: 220px;
            border: 1px solid #eee;
        }
        
        .dropdown-menu .dropdown-header {
            color: #4a4a4a;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #eee;
            background-color: #f9f9f9;
            border-radius: 8px 8px 0 0;
        }
        
        .dropdown-menu .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            border-radius: 4px;
            margin: 2px 8px;
        }
        
        .dropdown-menu .dropdown-item:hover {
            background-color: #f0f8ff;
            transform: translateX(5px);
        }
        
        /* 悬停下拉菜单样式 */
        .dropdown-hover:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-hover .dropdown-menu {
            margin-top: 0;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        
        .dropdown-submenu {
            position: relative;
        }
        
        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -6px;
            margin-left: -1px;
            border-top-left-radius: 0;
        }
        
        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
            transform: none;
        }

        footer.footer {
            padding: 15px 0;
            font-size: 0.875rem;
            color: #888;
            background: #fafafa;
            border-top: 1px solid #eaeaea;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, #4CA1D7, #0F959D);
            border: none;
            color: #fff;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .container { max-width: 1140px; }
    </style>
</head>

<body id="app-layout">

<div class="container mb-3">
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="/favicon.ico" width="36" height="36" alt="蒙太奇">
            <span class="ml-2" style="color:#429c4e;font-weight:600;font-size:1.2rem;">蒙太奇</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                @php
                    $menuItems = [
                        [
                            'url'=>'/', 
                            'label'=>'做番茄', 
                            'color'=>'#584029',
                            'submenu' => [
                                ['url'=>'/', 'label'=>'番茄/待办'],
                                ['url'=>'/pomos', 'label'=>'番茄列表'],
                                ['url'=>'/tasks', 'label'=>'待办列表'],
                                ['url'=>'/things', 'label'=>'事情列表'],
                                ['url'=>'/cals', 'label'=>'日历/订阅'],
                                ['url'=>'/dailysummarys', 'label'=>'日报记录'],
                                ['url'=>'/statistics', 'label'=>'数据统计']
                            ]
                        ],
                        [
                            'url'=>'/notes', 
                            'label'=>'记想法', 
                            'color'=>'#4CA1D7',
                            'submenu' => [
                                ['url'=>'/notes', 'label'=>'新建想法'],
                                ['url'=>'/notes', 'label'=>'查看想法'],
//                                ['url'=>'/tags', 'label'=>'标签管理']
                            ]
                        ],
                        [
                            'url'=>'/articles', 
                            'label'=>'去阅读', 
                            'color'=>'#F7AA55',
                            'submenu' => [
                                ['url'=>'/articles', 'label'=>'最新文章'],
                                ['url'=>'/articles?status=later', 'label'=>'稍后阅读'],
                                ['url'=>'/articles?status=star', 'label'=>'收藏文章'],
                                ['url'=>'/feeds', 'label'=>'订阅管理'],
                                ['url'=>'/feeds/explorer', 'label'=>'探索发现'],
                            ]
                        ],
                        [
                            'url'=>'/minds', 
                            'label'=>'绘导图', 
                            'color'=>'#0F959D',
                            'submenu' => [
                                ['url'=>'/minds', 'label'=>'思维导图'],
                            ]
                        ],
                        [
                            'url'=>'/course-management', 
                            'label'=>'学课程', 
                            'color'=>'#7B68EE',
                            'submenu' => [
                                ['url'=>'/course-management', 'label'=>'我的课程'],
                                ['url'=>'/courses', 'label'=>'课程管理']
                            ]
                        ],
                        [
                            'url'=>'/llm-management',
                            'label'=>'AI助手',
                            'color'=>'#E85205',
                            'submenu' => [
                                ['url'=>'/llm-management', 'label'=>'LLM管理'],
                            ]
                        ],
//                        [
//                            'url'=>'help/feedback',
//                            'label'=>'添加反馈',
//                            'color'=>'#E85205',
//                            'submenu' => [
//                                ['url'=>'help/feedback', 'label'=>'提交反馈'],
//                                ['url'=>'https://gitee.com/accacc/task#%E9%AB%98%E6%95%88%E4%BD%BF%E7%94%A8montage-gtd', 'label'=>'高效使用'],
//                                ['url'=>'/about', 'label'=>'关于我们']
//                            ]
//                        ]
                    ];
                @endphp
                @foreach ($menuItems as $item)
                    <li class="nav-item dropdown dropdown-hover">
                        <a class="nav-link menu-item" href="{{ url($item['url']) }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color:{{$item['color']}}">
                            {{ $item['label'] }}
{{--                            @if($item['url'] == '/articles' && !Auth::guest())<sup>推荐</sup>@endif--}}
                        </a>
                        <div class="dropdown-menu" style="border: 1px solid {{$item['color']}}40;">
                            @foreach($item['submenu'] as $subitem)
                                <a class="dropdown-item d-flex align-items-center" href="{{ url($subitem['url']) }}" style="color: {{$item['color']}};">
                                    <i class="fa fa-angle-right text-muted mr-2" style="font-size: 0.8em; color: {{$item['color']}};"></i>
                                    <span>{{ $subitem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                @endforeach

                @if(Auth::guest())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/login') }}" style="color:#9BD6C5">登录/注册</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span style="color:#9BD6C5">{{ Auth::user()->name }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ url('points') }}">积分中心</a>
                            <a class="dropdown-item" href="{{ url('achievements') }}">成就勋章</a>

                            <a class="dropdown-item" href="{{ url('accounts') }}">账号管理</a>
                            <a class="dropdown-item" href="{{ url('/personal-access-tokens') }}">访问令牌</a>

                            <a class="dropdown-item" href="{{ url('settings') }}">平台设置</a>

                            <a class="dropdown-item" href="{{ url('/help/feedback') }}">添加反馈</a>
                            <a class="dropdown-item" href="{{ url('/about') }}">关于我们</a>


                            <a class="dropdown-item" href="{{ url('/logout') }}"><i class="fa fa-sign-out"></i> 登出</a>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</div>


<!-- =================== JS 加载（严格顺序） =================== -->
<script src="/js/jquery.min.js"></script>
<script src="/js/popper.min.js"></script>
@yield('content')
<script src="/js/bootstrap.min.js"></script>

<!-- 悬停下拉菜单功能 -->
<script>
$(document).ready(function(){
    // 使下拉菜单在悬停时触发
    $('.dropdown-hover').hover(
        function() {
            $(this).find('.dropdown-menu').first().addClass('show');
            $(this).find('.menu-item').attr('aria-expanded', 'true');
        }, 
        function() {
            $(this).find('.dropdown-menu').first().removeClass('show');
            $(this).find('.menu-item').attr('aria-expanded', 'false');
        }
    );
    
    // 子菜单悬停功能
    $('.dropdown-submenu').hover(
        function() {
            $(this).find('.dropdown-menu').first().addClass('show');
        }, 
        function() {
            $(this).find('.dropdown-menu').first().removeClass('show');
        }
    );
    
    // 为主菜单项添加特殊处理，使其点击时跳转，悬停时显示下拉
    $('.menu-item').off('click').on('click', function(e) {
        // 阻止默认的下拉行为
        e.preventDefault();
        e.stopPropagation();
        // 直接跳转到链接地址
        window.location.href = $(this).attr('href');
    });
    
    // 防止菜单项的键盘事件触发下拉
    $('.menu-item').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            window.location.href = $(this).attr('href');
        }
    });
});
</script>



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

<footer class="footer text-center">
    <p>&copy;2016 Congcong.us&nbsp;
        <a href="mailto:accacc@126.com?subject=MontageGTD反馈">遇到问题?联系我~</a>&nbsp;
        <a href="https://gitee.com/accacc/task#%E9%AB%98%E6%95%88%E4%BD%BF%E7%94%A8montage-gtd">高效使用技巧</a>
    </p>
</footer>

</body>
</html>