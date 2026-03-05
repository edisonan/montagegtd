@extends('layouts.app')

@section('title', '注册 - 蒙太奇')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8  mx-auto">
                <div class="card">
                    <div class="card-header">注册</div>
                    <div class="card-body">
                        <form class="form-horizontal" role="form" method="POST" action="javascript:void(0)" id="registerStandaloneForm">
                            {!! csrf_field() !!}

                            <div class="form-group row {{ $errors->has('name') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">用户名</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">邮箱地址</label>

                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('password') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">密码</label>

                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">确认密码</label>

                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password_confirmation">

                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6  mx-auto">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-user"></i>注册
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            var form = document.getElementById('registerStandaloneForm');
            if (!form) return;

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

            form.addEventListener('submit', function(e) {
                if (!window.TaskApiClient || typeof window.TaskApiClient.request !== 'function') {
                    return;
                }

                e.preventDefault();
                var submitBtn = form.querySelector('button[type="submit"]');
                var originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa fa-btn fa-spinner fa-spin"></i>处理中...';
                }

                var payload = {
                    name: form.querySelector('input[name="name"]').value,
                    email: form.querySelector('input[name="email"]').value,
                    password: form.querySelector('input[name="password"]').value,
                    password_confirmation: form.querySelector('input[name="password_confirmation"]').value,
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
                    var msg = (error && error.data && (error.data.msg || error.data.message))
                        ? (error.data.msg || error.data.message)
                        : '注册失败，请稍后重试';
                    alert(msg);
                }).finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        })();
    </script>
@endsection
