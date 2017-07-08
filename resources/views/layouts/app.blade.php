<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title','Montage GTD - 高效你的生活')</title>
    <meta name="description" content="@yield('description')">
    <meta name="keywords" content="Montage GTD,番茄工作法,待办事项,推送到kindle,RSS阅读,知乎日报订阅">

    <!-- Fonts -->
    <link href="//cdn.bootcss.com/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.css.network/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>
    
    <link href="{{'css/screen.min.css'}}" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>
</head>

<body id="app-layout">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">
                		<img src="/favicon.ico" width="30px" style="display: -webkit-inline-box;border-radius:25px;">
                    	Montage GTD
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guest())
                    <li><a href="{{ url('/pomo/welcome') }}">做番茄</a></li>
                    <li><a href="{{ url('/note/welcome') }}">记想法</a></li>
                    <li><a href="{{ url('/read/welcome') }}">去阅读</a></li>
                    <li><a href="{{ url('/minds/welcome') }}">思维导图</a></li>
                    @else
                    <li><a href="{{ url('/') }}">做番茄</a></li>
                    <li><a href="{{ url('/notes') }}">记想法</a></li>
                    <li><a href="{{ url('/articles') }}">去阅读</a></li>
                    <li><a href="{{ url('/minds') }}">思维导图</a></li>
                    @endif
                    
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">登录</a></li>
                        <li><a href="{{ url('/register') }}">注册</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                    			<li><a href="{{ url('statistics') }}">统计</a></li>
                    			<li><a href="{{ url('settings') }}">统计</a></li>
                                <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>登出</a></li>
                            </ul>
                        </li>
                    @endif
                    
                </ul>
            </div>
        </div>
    </nav>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	
    @yield('content')

    <!-- JavaScripts -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
