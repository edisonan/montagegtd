@extends('layouts.app')

@section('title', '登录 - 蒙太奇')
@section('description', '登录蒙太奇，开始您的高效时间管理之旅')

@section('content')
    <style>
        .auth-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .auth-card {
            max-width: 1200px;
            width: 100%;
            border: none;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .auth-logo-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .auth-title {
            font-size: 32px;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-subtitle {
            color: var(--gray-600);
            font-size: 16px;
            margin-top: 8px;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
        }

        @media (min-width: 992px) {
            .auth-grid {
                grid-template-columns: 1fr 1px 1fr;
                gap: 48px;
            }
        }

        .auth-divider {
            background: var(--gray-200);
            position: relative;
        }

        .auth-divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 16px;
            color: var(--gray-500);
            font-size: 14px;
            font-weight: 500;
        }

        .form-card {
            height: 100%;
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            overflow: hidden;
        }

        .form-header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--gray-100);
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }

        .form-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-title i {
            color: var(--primary-color);
        }

        .form-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label i {
            color: var(--gray-500);
            font-size: 12px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 15px;
            color: var(--gray-900);
            transition: all 0.2s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control.has-error {
            border-color: var(--danger-color);
        }

        .form-control.has-error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            font-size: 13px;
            color: var(--danger-color);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .error-message i {
            font-size: 12px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-input {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid var(--gray-300);
            cursor: pointer;
        }

        .checkbox-label {
            font-size: 14px;
            color: var(--gray-700);
            cursor: pointer;
        }

        .form-actions {
            margin-top: 32px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-links {
            margin-top: 20px;
            text-align: center;
        }

        .form-link {
            font-size: 14px;
            color: var(--gray-600);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .form-link:hover {
            color: var(--primary-color);
        }

        .form-link.reset-password {
            color: var(--danger-color);
        }

        .form-link.reset-password:hover {
            color: #dc2626;
        }

        .social-login {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
        }

        .social-title {
            text-align: center;
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 16px;
        }

        .social-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .social-btn {
            padding: 10px 16px;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            background: white;
            color: var(--gray-700);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .social-btn:hover {
            border-color: var(--gray-400);
            background: var(--gray-50);
            transform: translateY(-1px);
        }

        .social-btn.github {
            color: #24292e;
        }

        .social-btn.weibo {
            color: #e6162d;
        }

        .mobile-switch {
            display: none;
        }

        @media (max-width: 768px) {
            .auth-container {
                padding: 20px 16px;
            }

            .form-body {
                padding: 24px;
            }

            .social-buttons {
                flex-direction: column;
            }
        }

        /* 移动端调整顺序 */
        @media (max-width: 991px) {
            .mobile-switch {
                display: block;
            }

            .auth-grid {
                display: flex;
                flex-direction: column;
            }

            .login-form {
                order: 1;
            }

            .register-form {
                order: 2;
            }

            .auth-divider {
                display: none;
            }
        }
    </style>

    <div class="auth-container">
        <div class="auth-card">
            <!-- 页面头部 -->
{{--            <div class="auth-header">--}}
{{--                <div class="auth-logo">--}}
{{--                    <div class="auth-logo-img">M</div>--}}
{{--                    <h1 class="auth-title">蒙太奇</h1>--}}
{{--                </div>--}}
{{--                <p class="auth-subtitle">专注效率，成就非凡</p>--}}
{{--            </div>--}}

            <!-- 登录和注册表单 -->
            <div class="auth-grid">
                <!-- 登录表单 -->
                <div class="login-form">
                    <div class="form-card fade-in">
                        <div class="form-header">
                            <h3 class="form-title">
                                <i class="fas fa-sign-in-alt"></i>
                                登录账号
                            </h3>
                        </div>

                        <div class="form-body">
                            <form method="POST" action="javascript:void(0)" id="loginForm">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        邮箱地址
                                    </label>
                                    <input type="email"
                                           name="email"
                                           class="form-control {{ $errors->has('email') ? 'has-error' : '' }}"
                                           value="{{ old('email') }}"
                                           placeholder="请输入您的邮箱地址"
                                           required
                                           autocomplete="email"
                                           autofocus>
                                    @if ($errors->has('email'))
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock"></i>
                                        密码
                                    </label>
                                    <input type="password"
                                           name="password"
                                           class="form-control {{ $errors->has('password') ? 'has-error' : '' }}"
                                           placeholder="请输入您的密码"
                                           required
                                           autocomplete="current-password">
                                    @if ($errors->has('password'))
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('password') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox"
                                               id="remember"
                                               name="remember"
                                               class="checkbox-input"
                                                {{ old('remember') ? 'checked' : '' }}>
                                        <label for="remember" class="checkbox-label">
                                            记住登录状态
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-sign-in-alt"></i>
                                        立即登录
                                    </button>

                                    <div class="form-links">
                                        <a href="{{ url('/password/reset') }}" class="form-link reset-password">
                                            <i class="fas fa-key"></i>
                                            忘记密码？
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- 第三方登录 -->
                            <div class="social-login">
                                <div class="social-title">或使用以下方式登录</div>
                                <div class="social-buttons">
                                    <a href="{{ url('/login/third/github') }}" class="social-btn github">
                                        <i class="fab fa-github"></i>
                                        GitHub
                                    </a>
                                    <a href="{{ url('/login/third/weibo') }}" class="social-btn weibo">
                                        <i class="fab fa-weibo"></i>
                                        微博
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 分隔线 -->
                <div class="auth-divider">
                    <span class="auth-divider-text">或</span>
                </div>

                <!-- 注册表单 -->
                <div class="register-form">
                    <div class="form-card fade-in" style="animation-delay: 0.1s">
                        <div class="form-header">
                            <h3 class="form-title">
                                <i class="fas fa-user-plus"></i>
                                注册账号
                            </h3>
                        </div>

                        <div class="form-body">
                            <form method="POST" action="javascript:void(0)" id="registerForm">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        用户名
                                    </label>
                                    <input type="text"
                                           name="name"
                                           class="form-control {{ $errors->has('name') ? 'has-error' : '' }}"
                                           value="{{ old('name') }}"
                                           placeholder="请输入用户名"
                                           required
                                           autocomplete="name"
                                           autofocus>
                                    @if ($errors->has('name'))
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        邮箱地址
                                    </label>
                                    <input type="email"
                                           name="email"
                                           class="form-control {{ $errors->has('email') ? 'has-error' : '' }}"
                                           value="{{ old('email') }}"
                                           placeholder="请输入您的邮箱"
                                           required
                                           autocomplete="email">
                                    @if ($errors->has('email'))
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock"></i>
                                        密码
                                    </label>
                                    <input type="password"
                                           name="password"
                                           class="form-control {{ $errors->has('password') ? 'has-error' : '' }}"
                                           placeholder="请输入至少6位密码"
                                           required
                                           autocomplete="new-password">
                                    @if ($errors->has('password'))
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('password') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock"></i>
                                        确认密码
                                    </label>
                                    <input type="password"
                                           name="password_confirmation"
                                           class="form-control {{ $errors->has('password_confirmation') ? 'has-error' : '' }}"
                                           placeholder="请再次输入密码"
                                           required
                                           autocomplete="new-password">
                                    @if ($errors->has('password_confirmation'))
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('password_confirmation') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-user-plus"></i>
                                        立即注册
                                    </button>

                                    <div class="form-links">
                                        <p class="text-sm text-gray-500 mt-4">
                                            <i class="fas fa-shield-alt mr-1"></i>
                                            注册即表示同意我们的
                                            <a href="{{ url('/terms') }}" class="form-link">服务条款</a>
                                            和
                                            <a href="{{ url('/privacy') }}" class="form-link">隐私政策</a>
                                        </p>
                                    </div>
                                </div>
                            </form>

                            <!-- 注册优势 -->
                            <div class="social-login">
                                <div class="social-title">加入蒙太奇，您将获得</div>
                                <div style="font-size: 14px; color: var(--gray-600); line-height: 1.6;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span>专业的专注工作法工具</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span>智能待办事项管理</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span>个性化数据统计</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span>免费使用所有基础功能</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            console.log('登录页面已加载');

            // 自动检测移动端并调整顺序
            function detectMobileAndReorder() {
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                if (isMobile) {
                    // 移动端：登录在上，注册在下
                    $('.auth-grid').css('flex-direction', 'column');
                    $('.login-form').css('order', '1');
                    $('.register-form').css('order', '2');
                } else {
                    // 桌面端：登录在左，注册在右
                    $('.auth-grid').css('flex-direction', '');
                    $('.login-form').css('order', '');
                    $('.register-form').css('order', '');
                }
            }

            // 初始检测
            detectMobileAndReorder();

            // 窗口大小变化时重新检测
            $(window).on('resize', function() {
                detectMobileAndReorder();
            });

            // 表单验证和交互
            $('input').on('focus', function() {
                $(this).parent().find('.form-label').addClass('text-primary-color');
            });

            $('input').on('blur', function() {
                if (!$(this).val()) {
                    $(this).parent().find('.form-label').removeClass('text-primary-color');
                }
            });

            // 错误提示动画
            $('.has-error').each(function() {
                $(this).addClass('shake-animation');
                setTimeout(() => {
                    $(this).removeClass('shake-animation');
                }, 1000);
            });

            // 添加CSS动画
            const style = document.createElement('style');
            style.textContent = `
            .shake-animation {
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }

            .fade-in {
                animation: fadeInUp 0.6s ease-out;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
            document.head.appendChild(style);

            function setButtonLoading($form, text) {
                const $btn = $form.find('.btn-submit');
                if ($btn.length === 0) return;
                if (!$btn.data('original-text')) {
                    $btn.data('original-text', $btn.html());
                }
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin"></i> ' + text);
            }

            function restoreButton($form) {
                const $btn = $form.find('.btn-submit');
                if ($btn.length === 0) return;
                $btn.prop('disabled', false);
                if ($btn.data('original-text')) {
                    $btn.html($btn.data('original-text'));
                }
            }

            function clearApiError($form) {
                $form.find('.api-error-message').remove();
            }

            function showApiError($form, message) {
                clearApiError($form);
                const html = '<div class="error-message api-error-message mt-3">' +
                    '<i class="fas fa-exclamation-circle"></i> ' + $('<div>').text(message).html() +
                    '</div>';
                $form.find('.form-actions').before(html);
            }

            function extractErrorMessage(error) {
                if (!error) return '请求失败，请稍后再试';
                if (error.data) {
                    if (error.data.msg) return error.data.msg;
                    if (error.data.message) return error.data.message;
                    if (error.data.errors) {
                        const firstKey = Object.keys(error.data.errors)[0];
                        if (firstKey && error.data.errors[firstKey] && error.data.errors[firstKey][0]) {
                            return error.data.errors[firstKey][0];
                        }
                    }
                }
                if (error.message) return error.message;
                return '请求失败，请稍后再试';
            }

            function establishWebSessionFromToken() {
                if (!window.TaskApiClient || typeof window.TaskApiClient.getAccessToken !== 'function') {
                    return Promise.resolve();
                }

                var accessToken = window.TaskApiClient.getAccessToken();
                if (!accessToken) {
                    return Promise.reject(new Error('登录令牌缺失，无法建立会话'));
                }

                return fetch('{{ url('/api/v2/auth/session-from-token') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + accessToken
                    }
                }).then(function(resp) {
                    if (!resp.ok) {
                        throw new Error('建立会话失败');
                    }
                    return resp.json();
                });
            }

            $('#loginForm').on('submit', function(e) {
                if (!window.TaskApiClient || typeof window.TaskApiClient.login !== 'function') {
                    return;
                }

                e.preventDefault();
                const $form = $(this);
                clearApiError($form);
                setButtonLoading($form, '登录中...');

                const payload = {
                    email: $form.find('input[name="email"]').val(),
                    password: $form.find('input[name="password"]').val(),
                    client_type: 'web',
                    device_id: navigator.userAgent || 'web'
                };

                window.TaskApiClient.login(payload).then(function() {
                    return establishWebSessionFromToken();
                }).then(function() {
                    window.location.href = '{{ url('/index') }}';
                }).catch(function(error) {
                    showApiError($form, extractErrorMessage(error));
                    restoreButton($form);
                });
            });

            $('#registerForm').on('submit', function(e) {
                if (!window.TaskApiClient || typeof window.TaskApiClient.request !== 'function') {
                    return;
                }

                e.preventDefault();
                const $form = $(this);
                clearApiError($form);
                setButtonLoading($form, '注册中...');

                const payload = {
                    name: $form.find('input[name="name"]').val(),
                    email: $form.find('input[name="email"]').val(),
                    password: $form.find('input[name="password"]').val(),
                    password_confirmation: $form.find('input[name="password_confirmation"]').val(),
                    client_type: 'web',
                    device_id: navigator.userAgent || 'web'
                };

                window.TaskApiClient.request({
                    method: 'POST',
                    url: '/api/v2/auth/register',
                    body: payload,
                    skipAuth: true
                }).then(function(resp) {
                    if (resp && resp.data && resp.data.result && typeof window.TaskApiClient.setTokenPair === 'function') {
                        window.TaskApiClient.setTokenPair(resp.data.result);
                    }
                    return establishWebSessionFromToken();
                }).then(function() {
                    window.location.href = '{{ url('/index') }}';
                }).catch(function(error) {
                    showApiError($form, extractErrorMessage(error));
                    restoreButton($form);
                });
            });
        });
    </script>
@endsection
