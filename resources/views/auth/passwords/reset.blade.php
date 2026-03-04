@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8  mx-auto">
                <div class="card">
                    <div class="card-header">Reset Password</div>

                    <div class="card-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}" id="passwordResetForm">
                            {!! csrf_field() !!}

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="email"
                                           value="{{ $email or old('email') }}">

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">Password</label>

                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">Confirm Password</label>
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
                                        <i class="fa fa-btn fa-refresh"></i>Reset Password
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
            var form = document.getElementById('passwordResetForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                if (!window.TaskApiClient || typeof window.TaskApiClient.request !== 'function') {
                    return;
                }

                e.preventDefault();
                var submitBtn = form.querySelector('button[type="submit"]');
                var originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa fa-btn fa-spinner fa-spin"></i>Resetting...';
                }

                window.TaskApiClient.request({
                    method: 'POST',
                    url: '/auth/password/reset',
                    body: {
                        token: form.querySelector('input[name="token"]').value,
                        email: form.querySelector('input[name="email"]').value,
                        password: form.querySelector('input[name="password"]').value,
                        password_confirmation: form.querySelector('input[name="password_confirmation"]').value
                    },
                    skipAuth: true
                }).then(function() {
                    window.location.href = '{{ url('/login') }}';
                }).catch(function() {
                    form.submit();
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
